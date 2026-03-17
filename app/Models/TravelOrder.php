<?php

namespace App\Models;

use App\Enums\TravelOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'status' => TravelOrderStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeStatus(Builder $query, TravelOrderStatus|string $status): Builder
    {
        if (is_string($status)) {
            $status = TravelOrderStatus::from($status);
        }
        return $query->where('status', $status);
    }

    public function scopeDestination(Builder $query, string $destination): Builder
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeDepartureBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('departure_date', [$startDate, $endDate]);
    }

    public function scopeReturnBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('return_date', [$startDate, $endDate]);
    }

    public function scopeTravelPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('departure_date', [$startDate, $endDate])
              ->orWhereBetween('return_date', [$startDate, $endDate]);
        });
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function canBeCanceled(): bool
    {
        return $this->status->canBeCanceled();
    }

    public function canBeApproved(): bool
    {
        return $this->status->canBeApproved();
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function approve(): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->status = TravelOrderStatus::APPROVED;
        return $this->save();
    }

    public function cancel(): bool
    {
        if (!$this->canBeCanceled()) {
            return false;
        }

        $this->status = TravelOrderStatus::CANCELED;
        return $this->save();
    }
}
