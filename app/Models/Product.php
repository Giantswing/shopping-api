<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['name', 'basket_id', 'times_added'];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }
}
