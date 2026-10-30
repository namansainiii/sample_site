<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id', 'name', 'slug', 'brand', 'material', 'finish', 'standard', 'hsn_code',
    'gst_rate', 'description', 'specs', 'image_path', 'is_active', 'is_featured',
])]
class Product extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'gst_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('price');
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('uploads/'.$this->image_path);
        }

        return asset('images/categories/'.($this->category->icon ?? 'bolts').'.svg');
    }

    /**
     * Lowest variant price, used for "from ₹X" on listing cards.
     */
    public function startingPrice(): ?float
    {
        $prices = $this->variants->pluck('price');

        return $prices->isEmpty() ? null : (float) $prices->min();
    }

    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock');
    }
}
