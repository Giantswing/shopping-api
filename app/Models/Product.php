<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model implements HasFactory
{
    protected $table = 'products';  

    protected $fillable = ['name', 'basket_id', 'times_added'];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }
}