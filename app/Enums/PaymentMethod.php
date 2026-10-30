<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cod';
    case Razorpay = 'razorpay';

    public function label(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'Cash on Delivery',
            self::Razorpay => 'Online (UPI / Card / Netbanking)',
        };
    }
}
