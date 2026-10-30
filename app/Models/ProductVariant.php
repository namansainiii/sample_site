<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'sku', 'size', 'pack_qty', 'price', 'mrp', 'stock', 'min_order_qty'])]
class ProductVariant extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'pack_qty' => 'integer',
            'stock' => 'integer',
            'min_order_qty' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<PriceTier, $this>
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(PriceTier::class)->orderBy('min_qty');
    }

    public function label(): string
    {
        return $this->pack_qty > 1
            ? $this->size.' · Pack of '.$this->pack_qty
            : $this->size;
    }

    /**
     * Pre-tax price per pack for the given quantity, applying the best bulk tier reached.
     */
    public function unitPriceFor(int $quantity): float
    {
        $price = (float) $this->price;

        foreach ($this->priceTiers as $tier) {
            if ($quantity >= $tier->min_qty) {
                $price = min($price, (float) $tier->unit_price);
            }
        }

        return $price;
    }

    public function pricePerPiece(): float
    {
        return round((float) $this->price / max(1, $this->pack_qty), 2);
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= config('store.low_stock_threshold');
    }
}
