<?php

namespace App\Services;

use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TravelOrderService
{
    public function list(array $data): LengthAwarePaginator
    {
        $userId = $data['user_id'];
        $status = $data['status'] ?? null;
        $destination = $data['destination'] ?? null;
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $perPage = $data['per_page'] ?? 15;

        $query = TravelOrder::query()
            ->with('user:id,name,email')
            ->forUser($userId);

        if ($status) {
            $query->status($status);
        }

        if ($destination) {
            $query->destination($destination);
        }

        if ($startDate && $endDate) {
            $query->travelPeriod($startDate, $endDate);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(array $data): ?TravelOrder
    {
        $id = $data['id'];
        $userId = $data['user_id'];

        return TravelOrder::with('user:id,name,email')
            ->forUser($userId)
            ->find($id);
    }

    public function create(array $data): TravelOrder
    {
        $userId = $data['user_id'];
        $destination = $data['destination'];
        $departureDate = $data['departure_date'];
        $returnDate = $data['return_date'];

        $travelOrder = TravelOrder::create([
            'user_id' => $userId,
            'destination' => $destination,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => TravelOrderStatus::REQUESTED,
        ]);

        return $travelOrder->load('user:id,name,email');
    }

    public function approve(array $data): array
    {
        $id = $data['id'];
        $approverId = $data['approver_id'];

        $travelOrder = TravelOrder::with('user')->find($id);

        if (!$travelOrder) {
            return [
                'success' => false,
                'message' => 'Pedido de viagem não encontrado.',
                'code' => 404,
            ];
        }

        if ($travelOrder->belongsToUser($approverId)) {
            return [
                'success' => false,
                'message' => 'Você não pode aprovar seu próprio pedido de viagem.',
                'code' => 403,
            ];
        }

        if (!$travelOrder->canBeApproved()) {
            return [
                'success' => false,
                'message' => 'Este pedido não pode ser aprovado. Status atual: ' . $travelOrder->status->label(),
                'code' => 422,
            ];
        }

        $travelOrder->approve();

        return [
            'success' => true,
            'message' => 'Pedido de viagem aprovado com sucesso.',
            'data' => $travelOrder->fresh('user:id,name,email'),
        ];
    }

    public function cancel(array $data): array
    {
        $id = $data['id'];
        $cancelerId = $data['canceler_id'];

        $travelOrder = TravelOrder::with('user')->find($id);

        if (!$travelOrder) {
            return [
                'success' => false,
                'message' => 'Pedido de viagem não encontrado.',
                'code' => 404,
            ];
        }

        if ($travelOrder->belongsToUser($cancelerId)) {
            if ($travelOrder->status !== TravelOrderStatus::REQUESTED) {
                return [
                    'success' => false,
                    'message' => 'Você só pode cancelar seus pedidos que ainda estão com status "solicitado".',
                    'code' => 403,
                ];
            }
        }

        if (!$travelOrder->canBeCanceled()) {
            return [
                'success' => false,
                'message' => 'Este pedido não pode ser cancelado. Status atual: ' . $travelOrder->status->label(),
                'code' => 422,
            ];
        }

        $travelOrder->cancel();

        return [
            'success' => true,
            'message' => 'Pedido de viagem cancelado com sucesso.',
            'data' => $travelOrder->fresh('user:id,name,email'),
        ];
    }

    public function updateStatus(array $data): array
    {
        $status = $data['status'];

        if ($status === 'approved') {
            return $this->approve([
                'id' => $data['id'],
                'approver_id' => $data['updater_id'],
            ]);
        }

        if ($status === 'canceled') {
            return $this->cancel([
                'id' => $data['id'],
                'canceler_id' => $data['updater_id'],
            ]);
        }

        return [
            'success' => false,
            'message' => 'Status inválido. Use "approved" ou "canceled".',
            'code' => 422,
        ];
    }
}
