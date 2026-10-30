@php
    $classes = [
        'pending' => 'badge-warn',
        'confirmed' => 'badge-info',
        'packed' => 'badge-info',
        'shipped' => 'badge-info',
        'delivered' => 'badge-ok',
        'cancelled' => 'badge-bad',
    ];
@endphp
<span class="badge {{ $classes[$order->status->value] ?? '' }}">{{ $order->status->label() }}</span>
@if($order->payment_status === \App\Enums\PaymentStatus::Paid)
    <span class="badge badge-ok">Paid</span>
@elseif($order->awaitingOnlinePayment())
    <span class="badge badge-bad">Payment pending</span>
@else
    <span class="badge">{{ $order->payment_method === \App\Enums\PaymentMethod::CashOnDelivery ? 'COD' : $order->payment_status->label() }}</span>
@endif
