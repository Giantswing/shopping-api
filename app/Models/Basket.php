<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
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
