@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="admin-top"><h1>Categories</h1></div>

<div class="grid" style="grid-template-columns:minmax(0,2fr) minmax(0,1fr); align-items:start;">
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Category</th><th>Icon</th><th>Order</th><th>Products</th><th></th></tr></thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td colspan="5" style="padding:0;">
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" style="display:grid; grid-template-columns:minmax(0,2fr) 120px 80px 80px auto; gap:8px; align-items:center; padding:10px 14px;">
                                    @csrf @method('PUT')
                                    <div>
                                        <input class="input" name="name" value="{{ $category->name }}" required>
                                        <input class="input" name="description" value="{{ $category->description }}" placeholder="Short description" style="margin-top:6px; font-size:.85rem;">
                                    </div>
                                    <select class="input" name="icon">
                                        @foreach(\App\Models\Category::ICONS as $icon)
                                            <option value="{{ $icon }}" @selected($category->icon === $icon)>{{ ucfirst($icon) }}</option>
                                        @endforeach
                                    </select>
                                    <input class="input" type="number" min="0" name="sort_order" value="{{ $category->sort_order }}">
                                    <span class="badge">{{ $category->products_count }}</span>
                                    <div class="row" style="flex-wrap:nowrap;">
                                        <button class="btn btn-sm btn-dark" type="submit">Save</button>
                                        <button class="btn btn-sm btn-danger" type="submit" form="del-{{ $category->id }}">✕</button>
                                    </div>
                                </form>
                                <form id="del-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete {{ $category->name }}?" hidden>
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3>Add category</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="fields">
                @csrf
                <div class="field"><label for="c-name">Name</label><input class="input" id="c-name" name="name" required></div>
                <div class="field"><label for="c-desc">Description</label><input class="input" id="c-desc" name="description"></div>
                <div class="field">
                    <label for="c-icon">Illustration</label>
                    <select class="input" id="c-icon" name="icon">
                        @foreach(\App\Models\Category::ICONS as $icon)
                            <option value="{{ $icon }}">{{ ucfirst($icon) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label for="c-order">Display order</label><input class="input" type="number" min="0" id="c-order" name="sort_order" value="{{ $categories->max('sort_order') + 1 }}"></div>
                <button class="btn btn-primary btn-block" type="submit">Add category</button>
            </form>
        </div>
    </div>
</div>
@endsection
