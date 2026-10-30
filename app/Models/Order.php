<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'status', 'payment_method', 'payment_status', 'razorpay_order_id',
    'razorpay_payment_id', 'subtotal', 'gst_amount', 'shipping_fee', 'total', 'shipping_name',
    'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_pincode',
    'company_name', 'gstin', 'notes', 'tracking_number', 'paid_at', 'shipped_at', 'delivered_at',
])]
class Order extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Intra-state supplies split GST into CGST + SGST; inter-state supplies use IGST.
     */
    public function isIntraState(): bool
    {
        return strcasecmp($this->shipping_state, config('store.state')) === 0;
    }

    /**
     * An online order is only "placed" for the customer once the payment succeeds.
     */
    public function awaitingOnlinePayment(): bool
    {
        return $this->payment_method === PaymentMethod::Razorpay
            && $this->payment_status !== PaymentStatus::Paid
            && $this->status !== OrderStatus::Cancelled;
    }
}
