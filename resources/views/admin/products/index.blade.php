@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="admin-top">
    <h1>Products</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add product</a>
</div>

<form class="filters-bar" method="GET">
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search by name">
    <select class="input" name="category" onchange="this.form.submit()">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-dark" type="submit">Filter</button>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Product</th><th>Category</th><th>Sizes</th><th class="num">Price from</th><th class="num">Stock (packs)</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="row" style="flex-wrap:nowrap;">
                                <img class="thumb" src="{{ $product->imageUrl() }}" alt="">
                                <div><a href="{{ route('admin.products.edit', $product) }}"><strong>{{ $product->name }}</strong></a><br><small>{{ $product->material }}</small></div>
                            </div>
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->variants->count() }}</td>
                        <td class="num">{{ $product->startingPrice() !== null ? \App\Support\Money::inr($product->startingPrice()) : '—' }}</td>
                        <td class="num">
                            {{ $product->totalStock() }}
                            @if($product->variants->contains(fn ($v) => $v->stock <= config('store.low_stock_threshold')))
                                <br><span class="badge badge-warn">low on some sizes</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'badge-ok' : '' }}">{{ $product->is_active ? 'Live' : 'Hidden' }}</span>
                            @if($product->is_featured)<span class="badge badge-accent">Featured</span>@endif
                        </td>
                        <td class="num">
                            <a class="btn btn-sm btn-outline" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <a class="btn btn-sm btn-ghost" href="{{ route('product.show', $product) }}" target="_blank">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $products->links() }}
@endsection
