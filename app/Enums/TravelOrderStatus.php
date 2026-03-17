<?php

namespace App\Enums;

enum TravelOrderStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Solicitado',
            self::APPROVED => 'Aprovado',
            self::CANCELED => 'Cancelado',
        };
    }

    public function canBeCanceled(): bool
    {
        return $this === self::REQUESTED || $this === self::APPROVED;
    }

    public function canBeApproved(): bool
    {
        return $this === self::REQUESTED;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
