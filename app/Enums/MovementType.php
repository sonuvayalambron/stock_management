<?php

namespace App\Enums;

enum MovementType: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';
    case SALE_RETURN = 'sale_return';
    case PURCHASE_RETURN = 'purchase_return';
    case ADJUSTMENT = 'adjustment';

    public function getStockImpact(): int
    {
        return match($this) {
            self::PURCHASE, self::SALE_RETURN => 1,
            self::SALE, self::PURCHASE_RETURN => -1,
            self::ADJUSTMENT => 0, // Handled separately
        };
    }

    public function decreasesStock(): bool
    {
        return in_array($this, [self::SALE, self::PURCHASE_RETURN]);
    }

    public function label(): string
    {
        return match($this) {
            self::SALE => 'Sale',
            self::PURCHASE => 'Purchase',
            self::SALE_RETURN => 'Sale Return',
            self::PURCHASE_RETURN => 'Purchase Return',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
}