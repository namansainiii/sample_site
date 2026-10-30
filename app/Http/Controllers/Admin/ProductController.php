<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'variants'])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['gst_rate' => config('store.default_gst_rate'), 'is_active' => true]),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request): Product {
            [$attributes, $variants] = $this->validated($request);
            $attributes['slug'] = $this->uniqueSlug($attributes['name']);

            $product = Product::create($attributes);
            $this->syncVariants($product, $variants);

            return $product;
        });

        $this->storeImage($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('variants.priceTiers'),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product): void {
            [$attributes, $variants] = $this->validated($request, $product);
            $product->update($attributes);
            $this->syncVariants($product, $variants);
        });

        $this->storeImage($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Order history keeps its own copy of name/SKU/price, so deleting is safe.
        if ($product->image_path) {
            Storage::disk('uploads')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<array<string, mixed>>}
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string', 'max:60'],
            'finish' => ['nullable', 'string', 'max:60'],
            'standard' => ['nullable', 'string', 'max:60'],
            'hsn_code' => ['nullable', 'string', 'max:10'],
            'gst_rate' => ['required', Rule::in([0, 5, 12, 18, 28])],
            'description' => ['nullable', 'string', 'max:5000'],
            'specs_text' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9\-_.\/]+$/'],
            'variants.*.size' => ['required', 'string', 'max:60'],
            'variants.*.pack_qty' => ['required', 'integer', 'min:1'],
            'variants.*.price' => ['required', 'numeric', 'min:0.01'],
            'variants.*.mrp' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.min_order_qty' => ['required', 'integer', 'min:1'],
            'variants.*.tiers' => ['nullable', 'string', 'max:200'],
        ], ['variants.*.sku.regex' => 'SKUs may only contain letters, numbers, - _ . /']);

        $variants = array_values($data['variants']);
        $skus = array_map(fn (array $variant) => strtoupper($variant['sku']), $variants);

        if (count($skus) !== count(array_unique($skus))) {
            throw ValidationException::withMessages(['variants' => 'Each size must have a unique SKU.']);
        }

        $taken = DB::table('product_variants')
            ->whereIn(DB::raw('UPPER(sku)'), $skus)
            ->when($product, fn ($query) => $query->where('product_id', '!=', $product->id))
            ->pluck('sku');

        if ($taken->isNotEmpty()) {
            throw ValidationException::withMessages(['variants' => 'SKU already used by another product: '.$taken->implode(', ')]);
        }

        $specs = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($data['specs_text'] ?? '')) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                if ($key !== '' && $value !== '') {
                    $specs[$key] = $value;
                }
            }
        }

        $attributes = collect($data)->only([
            'category_id', 'name', 'brand', 'material', 'finish', 'standard', 'hsn_code', 'gst_rate', 'description',
        ])->all();

        $attributes['specs'] = $specs;
        $attributes['is_active'] = $request->boolean('is_active');
        $attributes['is_featured'] = $request->boolean('is_featured');

        return [$attributes, $variants];
    }

    /**
     * Upsert the submitted size/pack rows; rows removed in the form are deleted.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $values = [
                'sku' => strtoupper($row['sku']),
                'size' => $row['size'],
                'pack_qty' => $row['pack_qty'],
                'price' => $row['price'],
                'mrp' => ($row['mrp'] ?? null) ?: null,
                'stock' => $row['stock'],
                'min_order_qty' => $row['min_order_qty'],
            ];

            $variant = filled($row['id'] ?? null)
                ? $product->variants()->whereKey($row['id'])->first()
                : null;

            $variant ? $variant->update($values) : $variant = $product->variants()->create($values);
            $keptIds[] = $variant->id;

            $variant->priceTiers()->delete();
            foreach ($this->parseTiers((string) ($row['tiers'] ?? '')) as $minQty => $unitPrice) {
                $variant->priceTiers()->create(['min_qty' => $minQty, 'unit_price' => $unitPrice]);
            }
        }

        $product->variants()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Parse "10=450, 50=420" into [10 => 450.0, 50 => 420.0].
     *
     * @return array<int, float>
     */
    private function parseTiers(string $input): array
    {
        $tiers = [];

        foreach (preg_split('/[,;]/', $input) as $pair) {
            if (preg_match('/^\s*(\d+)\s*[=:]\s*(\d+(?:\.\d{1,2})?)\s*$/', $pair, $match) && (int) $match[1] > 1) {
                $tiers[(int) $match[1]] = (float) $match[2];
            }
        }

        ksort($tiers);

        return $tiers;
    }

    private function storeImage(Request $request, Product $product): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $path = $request->file('image')->store('products', 'uploads');

        if ($path) {
            if ($product->image_path) {
                Storage::disk('uploads')->delete($product->image_path);
            }

            $product->update(['image_path' => $path]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
