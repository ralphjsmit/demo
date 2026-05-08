<?php

namespace App\Models;

use App\Models\Blog\Author;
use App\Models\Blog\Category as BlogCategory;
use App\Models\Blog\Post;
use App\Models\Shop\Brand;
use App\Models\Shop\Category as ShopCategory;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model implements HasCurrentTenantLabel
{
    public function getCurrentTenantLabel(): string
    {
        return 'Demo';
    }

    protected static function booted(): void
    {
        static::creating(function (Team $team): void {
            $team->uuid ??= Str::uuid()->toString();
        });
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getStoragePrefix(): string
    {
        return $this->uuid;
    }

    /** @return HasMany<Author, $this> */
    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    /** @return HasMany<BlogCategory, $this> */
    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    /** @return HasMany<Brand, $this> */
    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<ShopCategory, $this> */
    public function shopCategories(): HasMany
    {
        return $this->hasMany(ShopCategory::class);
    }
}
