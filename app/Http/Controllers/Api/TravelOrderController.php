<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTravelOrdersRequest;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Http\Resources\TravelOrderCollection;
use App\Http\Resources\TravelOrderResource;
use App\Services\TravelOrderService;
use Illuminate\Http\JsonResponse;

class TravelOrderController extends Controller
{
    public function __construct(
        private TravelOrderService $travelOrderService
    ) {}

    public function index(ListTravelOrdersRequest $request): JsonResponse
    {
        $result = $this->travelOrderService->list([
            'user_id' => auth()->id(),
            'status' => $request->input('status'),
            'destination' => $request->input('destination'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'per_page' => $request->input('per_page', 15),
        ]);

        return response()->json([
            'success' => true,
            'data' => new TravelOrderCollection($result),
        ]);
    }

    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $travelOrder = $this->travelOrderService->create([
            'user_id' => auth()->id(),
            'destination' => $request->input('destination'),
            'departure_date' => $request->input('departure_date'),
            'return_date' => $request->input('return_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem criado com sucesso.',
            'data' => new TravelOrderResource($travelOrder),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $travelOrder = $this->travelOrderService->find([
            'id' => $id,
            'user_id' => auth()->id(),
        ]);

        if (!$travelOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TravelOrderResource($travelOrder),
        ]);
    }

    public function updateStatus(UpdateTravelOrderStatusRequest $request, int $id): JsonResponse
    {
        $result = $this->travelOrderService->updateStatus([
            'id' => $id,
            'status' => $request->input('status'),
            'updater_id' => auth()->id(),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => new TravelOrderResource($result['data']),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $result = $this->travelOrderService->cancel([
            'id' => $id,
            'canceler_id' => auth()->id(),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => new TravelOrderResource($result['data']),
        ]);
    }
}
