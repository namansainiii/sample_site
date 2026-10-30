@extends('layouts.admin')

@section('title', $product->exists ? 'Edit '.$product->name : 'Add product')

@section('content')
@php
    $rows = old('variants', $product->exists
        ? $product->variants->map(fn ($v) => [
            'id' => $v->id, 'sku' => $v->sku, 'size' => $v->size, 'pack_qty' => $v->pack_qty, 'price' => $v->price,
            'mrp' => $v->mrp, 'stock' => $v->stock, 'min_order_qty' => $v->min_order_qty,
            'tiers' => $v->priceTiers->map(fn ($t) => $t->min_qty.'='.(float) $t->unit_price)->implode(', '),
        ])->all()
        : [['id' => '', 'sku' => '', 'size' => '', 'pack_qty' => 1, 'price' => '', 'mrp' => '', 'stock' => 0, 'min_order_qty' => 1, 'tiers' => '']]);
    $specsText = old('specs_text', collect($product->specs ?? [])->map(fn ($v, $k) => "$k: $v")->implode("\n"));
@endphp

<div class="admin-top">
    <div>
        <a href="{{ route('admin.products.index') }}" class="muted">← Products</a>
        <h1>{{ $product->exists ? $product->name : 'Add product' }}</h1>
    </div>
    @if($product->exists)
        <div class="row">
            <a class="btn btn-outline" href="{{ route('product.show', $product) }}" target="_blank">View on store ↗</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete this product and all its sizes? Past orders are not affected.">
                @csrf @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete</button>
            </form>
        </div>
    @endif
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="stack">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="grid" style="grid-template-columns:minmax(0,2fr) minmax(0,1fr);">
        <div class="card">
            <div class="card-head"><h3>Details</h3></div>
            <div class="card-body form-grid">
                <div class="field span-2">
                    <label for="name">Product name</label>
                    <input class="input" id="name" name="name" required value="{{ old('name', $product->name) }}" placeholder="e.g. Hex Head Bolt SS304 (Full Thread)">
                </div>
                <div class="field">
                    <label for="category_id">Category</label>
                    <select class="input" id="category_id" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="brand">Brand</label>
                    <input class="input" id="brand" name="brand" value="{{ old('brand', $product->brand) }}">
                </div>
                <div class="field">
                    <label for="material">Material / grade</label>
                    <input class="input" id="material" name="material" list="materialList" value="{{ old('material', $product->material) }}" placeholder="Stainless Steel 304">
                    <datalist id="materialList">
                        @foreach(['Stainless Steel 304', 'Stainless Steel 316', 'Mild Steel', 'High Tensile Steel', 'Alloy Steel', 'Brass', 'Aluminium', 'Nylon 6'] as $m)<option value="{{ $m }}">@endforeach
                    </datalist>
                </div>
                <div class="field">
                    <label for="finish">Finish</label>
                    <input class="input" id="finish" name="finish" value="{{ old('finish', $product->finish) }}" placeholder="Zinc Plated">
                </div>
                <div class="field">
                    <label for="standard">Standard</label>
                    <input class="input" id="standard" name="standard" value="{{ old('standard', $product->standard) }}" placeholder="DIN 933">
                </div>
                <div class="field">
                    <label for="hsn_code">HSN code</label>
                    <input class="input" id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code ?? '7318') }}">
                </div>
                <div class="field span-2">
                    <label for="description">Description</label>
                    <textarea class="input" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="field span-2">
                    <label for="specs_text">Specifications <small class="muted">(one per line, "Name: value")</small></label>
                    <textarea class="input mono" id="specs_text" name="specs_text" rows="4" placeholder="Grade: A2-70&#10;Tensile strength: 700 MPa">{{ $specsText }}</textarea>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="card card-body">
                <h3>Visibility</h3>
                <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Live on store</label>
                <label class="check" style="margin-top:8px;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Show in Best sellers</label>
                <div class="field" style="margin-top:14px;">
                    <label for="gst_rate">GST rate</label>
                    <select class="input" id="gst_rate" name="gst_rate">
                        @foreach([0, 5, 12, 18, 28] as $rate)
                            <option value="{{ $rate }}" @selected((float) old('gst_rate', $product->gst_rate) == $rate)>{{ $rate }}%</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card card-body">
                <h3>Photo</h3>
                <img src="{{ $product->exists ? $product->imageUrl() : asset('images/categories/bolts.svg') }}" alt="" style="width:100%; aspect-ratio:1; object-fit:contain; background:var(--bg); border-radius:8px; margin-bottom:10px;">
                <input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <span class="hint">JPG/PNG/WebP up to 4 MB. Without a photo the category illustration is used.</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h3>Sizes, packs &amp; stock</h3>
                <small>Bulk tiers: <code>10=450, 50=420</code> means ₹450/pack from 10 packs, ₹420/pack from 50.</small>
            </div>
            <button type="button" class="btn btn-sm btn-dark" id="addRow">+ Add size</button>
        </div>
        <div class="card-body variant-rows" id="variantRows">
            <div class="vrow head">
                <span>SKU</span><span>Size</span><span>Pack qty</span><span>Price ₹</span><span>MRP ₹</span><span>Stock</span><span>Min</span><span>Bulk tiers</span><span></span>
            </div>
            @foreach(array_values($rows) as $i => $row)
                <div class="vrow">
                    <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                    <input class="input mono" name="variants[{{ $i }}][sku]" value="{{ $row['sku'] }}" placeholder="SKU" required>
                    <input class="input" name="variants[{{ $i }}][size]" value="{{ $row['size'] }}" placeholder="M8 × 40 mm" required>
                    <input class="input" type="number" min="1" name="variants[{{ $i }}][pack_qty]" value="{{ $row['pack_qty'] }}" required>
                    <input class="input" type="number" step="0.01" min="0.01" name="variants[{{ $i }}][price]" value="{{ $row['price'] }}" placeholder="Price" required>
                    <input class="input" type="number" step="0.01" min="0" name="variants[{{ $i }}][mrp]" value="{{ $row['mrp'] }}" placeholder="MRP">
                    <input class="input" type="number" min="0" name="variants[{{ $i }}][stock]" value="{{ $row['stock'] }}" required>
                    <input class="input" type="number" min="1" name="variants[{{ $i }}][min_order_qty]" value="{{ $row['min_order_qty'] }}" required>
                    <input class="input mono" name="variants[{{ $i }}][tiers]" value="{{ $row['tiers'] }}" placeholder="10=450, 50=420">
                    <button type="button" class="btn btn-sm btn-danger" data-remove-row title="Remove size">✕</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <button class="btn btn-primary btn-lg" type="submit">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const container = document.getElementById('variantRows');
    let index = container.querySelectorAll('.vrow:not(.head)').length;

    document.getElementById('addRow').addEventListener('click', () => {
        const template = container.querySelector('.vrow:not(.head)');
        const row = template.cloneNode(true);
        row.querySelectorAll('input').forEach((input) => {
            input.name = input.name.replace(/variants\[\d+\]/, `variants[${index}]`);
            if (input.type === 'hidden' || input.name.endsWith('[sku]') || input.name.endsWith('[size]') || input.name.endsWith('[tiers]') || input.name.endsWith('[mrp]')) {
                input.value = '';
            }
        });
        index++;
        container.appendChild(row);
        row.querySelector('input[name$="[sku]"]').focus();
    });

    container.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (!button) return;
        if (container.querySelectorAll('.vrow:not(.head)').length === 1) {
            window.storeToast && storeToast('A product needs at least one size.');
            return;
        }
        button.closest('.vrow').remove();
    });
})();
</script>
@endpush
