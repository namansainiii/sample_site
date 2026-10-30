<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'icon', 'sort_order'])]
class Category extends Model
{
    /**
     * Category artwork shipped in public/images/categories.
     *
     * @var list<string>
     */
    public const ICONS = ['bolts', 'nuts', 'screws', 'washers', 'anchors', 'rods', 'rivets', 'tools'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function iconUrl(): string
    {
        return asset('images/categories/'.$this->icon.'.svg');
    }
}
