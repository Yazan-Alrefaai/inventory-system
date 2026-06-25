<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = ['sale_id', 'amount', 'pay_currency', 'exchange_rate', 'note'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Convert this payment's amount to the sale's native currency.
     */
    public function amountInSaleCurrency(Sale $sale): float
    {
        if ($this->pay_currency === $sale->currency) {
            return (float) $this->amount;
        }
        $rate = (float) $this->exchange_rate;
        if ($rate <= 0) return 0;

        if ($this->pay_currency === 'USD' && $sale->currency === 'SYP') {
            return round((float) $this->amount * $rate, 0);
        }
        // paid SYP, sale is USD
        return round((float) $this->amount / $rate, 2);
    }
}
