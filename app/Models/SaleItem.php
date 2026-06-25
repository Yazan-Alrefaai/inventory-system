<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'qty', 'price'];

    protected $casts = [
        'qty'   => 'float',
        'price' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotal(): float
    {
        return round($this->price * $this->qty, 2);
    }
}
