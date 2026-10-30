@extends('layouts.admin')

@section('title', 'Quote Q'.$quote->id)

@section('content')
<div class="admin-top">
    <div>
        <a href="{{ route('admin.quotes.index') }}" class="muted">← Quote requests</a>
        <h1>Quote request Q{{ $quote->id }}</h1>
        <small>Received {{ $quote->created_at->format('d M Y, h:i A') }}</small>
    </div>
</div>

<div class="layout-2">
    <div class="stack">
        <div class="card card-body">
            <h3>Requirement</h3>
            <p style="white-space:pre-line; margin:0;">{{ $quote->requirements }}</p>
        </div>
        <div class="card card-body">
            <h3>Your reply</h3>
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="fields">
                @csrf @method('PATCH')
                <div class="form-grid">
                    <div class="field">
                        <label for="status">Status</label>
                        <select class="input" id="status" name="status">
                            @foreach(\App\Enums\QuoteStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($quote->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="quoted_amount">Quoted amount (₹, excl. GST)</label>
                        <input class="input" type="number" step="0.01" min="0" id="quoted_amount" name="quoted_amount" value="{{ $quote->quoted_amount }}">
                    </div>
                </div>
                <div class="field">
                    <label for="admin_reply">Quote details / internal notes</label>
                    <textarea class="input" id="admin_reply" name="admin_reply" rows="6" placeholder="Rates, delivery time, validity…">{{ $quote->admin_reply }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </div>
    </div>
    <aside class="card card-body">
        <h3>Contact</h3>
        <p>
            <strong>{{ $quote->name }}</strong><br>
            @if($quote->company_name){{ $quote->company_name }}<br>@endif
            <a href="tel:{{ $quote->phone }}">📞 {{ $quote->phone }}</a><br>
            <a href="mailto:{{ $quote->email }}">{{ $quote->email }}</a>
        </p>
        @php($reply = 'Hi '.$quote->name.', thank you for your enquiry (Q'.$quote->id.') with '.config('store.name').'.'.($quote->quoted_amount ? ' Our quote: '.\App\Support\Money::inr($quote->quoted_amount).' + GST.' : '').($quote->admin_reply ? "\n\n".$quote->admin_reply : ''))
        <a class="btn btn-whatsapp btn-block" target="_blank" rel="noopener" href="https://wa.me/91{{ $quote->phone }}?text={{ rawurlencode($reply) }}">Send via WhatsApp</a>
        <a class="btn btn-outline btn-block" style="margin-top:8px;" href="mailto:{{ $quote->email }}?subject={{ rawurlencode('Quotation Q'.$quote->id.' – '.config('store.name')) }}&body={{ rawurlencode($reply) }}">Send via email</a>
    </aside>
</div>
@endsection
