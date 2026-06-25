<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'category_id', 'unit', 'qty', 'min_qty', 'price', 'sell_price', 'notes'];

    protected $casts = [
        'qty'        => 'float',
        'min_qty'    => 'float',
        'price'      => 'float',
        'sell_price' => 'float',
    ];

    public function defaultSellPrice(): float
    {
        return $this->sell_price > 0 ? (float) $this->sell_price : (float) $this->price;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->qty <= $this->min_qty;
    }
}
