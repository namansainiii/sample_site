@extends('layouts.admin')

@section('title', 'Quote requests')

@section('content')
<div class="admin-top"><h1>Quote requests</h1></div>

<form class="filters-bar" method="GET">
    <select class="input" name="status" onchange="this.form.submit()">
        <option value="">All</option>
        @foreach(\App\Enums\QuoteStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>From</th><th>Requirement</th><th>Received</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td class="mono">Q{{ $quote->id }}</td>
                        <td><strong>{{ $quote->name }}</strong>@if($quote->company_name)<br><small>{{ $quote->company_name }}</small>@endif</td>
                        <td style="max-width:360px;">{{ Str::limit($quote->requirements, 90) }}</td>
                        <td>{{ $quote->created_at->diffForHumans() }}</td>
                        <td><span class="badge {{ ['new' => 'badge-warn', 'quoted' => 'badge-info', 'closed' => 'badge-ok'][$quote->status->value] }}">{{ $quote->status->label() }}</span></td>
                        <td class="num"><a class="btn btn-sm btn-outline" href="{{ route('admin.quotes.show', $quote) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No quote requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $quotes->links() }}
@endsection
