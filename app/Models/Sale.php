<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_name', 'currency', 'exchange_rate',
        'is_credit', 'amount_paid', 'note',
    ];

    protected $casts = ['is_credit' => 'boolean'];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function totalAmount(): float
    {
        return round($this->items->sum(fn($i) => $i->price * $i->qty), 2);
    }

    public function totalPaid(): float
    {
        return round((float) $this->amount_paid, 2);
    }

    /**
     * Sum of follow-up payments (excludes the initial amount_paid at sale time).
     */
    public function totalFollowupPaid(): float
    {
        return round($this->salePayments->sum(fn($p) => $p->amountInSaleCurrency($this)), 2);
    }

    public function remaining(): float
    {
        return round(max(0, $this->totalAmount() - $this->totalPaid()), 2);
    }

    public function isFullyPaid(): bool
    {
        return $this->remaining() <= 0;
    }

    public function currencySymbol(): string
    {
        return $this->currency === 'SYP' ? 'ل.س' : '$';
    }

    public function totalAmountUsd(): float
    {
        if ($this->currency === 'SYP') {
            if (!$this->exchange_rate || $this->exchange_rate <= 0) { return 0; }
            return round($this->totalAmount() / $this->exchange_rate, 2);
        }
        return $this->totalAmount();
    }

    public function invoiceNumber(): string
    {
        return 'INV-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
