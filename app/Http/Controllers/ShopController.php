<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function home(): View
    {
        return view('shop.home', [
            'categories' => Category::withCount(['products' => fn ($query) => $query->active()])->orderBy('sort_order')->get(),
            'featured' => Product::active()->where('is_featured', true)->with(['category', 'variants'])->latest()->take(8)->get(),
        ]);
    }

    public function catalog(Request $request, ?Category $category = null): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string', 'max:60'],
            'sort' => ['nullable', 'in:popular,price_asc,price_desc,newest'],
            'in_stock' => ['nullable', 'boolean'],
        ]);

        $query = Product::active()
            ->with(['category', 'variants'])
            ->addSelect(['min_price' => ProductVariant::selectRaw('MIN(price)')->whereColumn('product_id', 'products.id')]);

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if (filled($filters['q'] ?? null)) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('standard', 'like', $term)
                    ->orWhereHas('variants', fn ($variant) => $variant->where('sku', 'like', $term)->orWhere('size', 'like', $term));
            });
        }

        if (filled($filters['material'] ?? null)) {
            $query->where('material', $filters['material']);
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('variants', fn ($variant) => $variant->where('stock', '>', 0));
        }

        match ($filters['sort'] ?? 'popular') {
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->orderBy('name'),
        };

        $materialsQuery = Product::active()->whereNotNull('material');
        if ($category) {
            $materialsQuery->where('category_id', $category->id);
        }

        return view('shop.catalog', [
            'category' => $category,
            'categories' => Category::orderBy('sort_order')->get(),
            'products' => $query->paginate(12)->withQueryString(),
            'materials' => $materialsQuery->distinct()->orderBy('material')->pluck('material'),
            'filters' => $filters,
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'variants.priceTiers']);

        return view('shop.product', [
            'product' => $product,
            'related' => Product::active()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->with(['category', 'variants'])
                ->inRandomOrder()
                ->take(4)
                ->get(),
        ]);
    }
}
