<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * The fulfilment steps shown on the customer's order tracking timeline.
     *
     * @return list<self>
     */
    public static function timeline(): array
    {
        return [self::Pending, self::Confirmed, self::Packed, self::Shipped, self::Delivered];
    }
}
