<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $fillable = ['movement_id', 'amount', 'pay_currency', 'exchange_rate', 'note'];

    // Amount converted to the movement's currency for balance calculations
    public function amountInMovementCurrency(\App\Models\StockMovement $movement): float
    {
        if ($this->pay_currency === $movement->currency) {
            return (float) $this->amount;
        }
        // Paid in USD, movement in SYP → multiply
        if ($this->pay_currency === 'USD' && $movement->currency === 'SYP') {
            return round((float) $this->amount * (float) ($this->exchange_rate ?? $movement->exchange_rate ?? 1), 2);
        }
        // Paid in SYP, movement in USD → divide
        $rate = (float) ($this->exchange_rate ?? $movement->exchange_rate ?? 1);
        return $rate > 0 ? round((float) $this->amount / $rate, 2) : 0;
    }

    public function movement()
    {
        return $this->belongsTo(StockMovement::class, 'movement_id');
    }
}
