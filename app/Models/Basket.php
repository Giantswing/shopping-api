<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Basket extends Model implements HasFactory
{
    protected $table = 'baskets';

    protected $fillable = ['name', 'slug', 'password'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function basketProducts(): HasMany
    {
        return $this->hasMany(BasketProduct::class);
    }
}