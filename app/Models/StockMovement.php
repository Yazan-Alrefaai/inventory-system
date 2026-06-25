<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'type', 'qty', 'price', 'note',
        'currency', 'exchange_rate', 'customer_name', 'amount_paid', 'is_credit',
    ];

    protected $casts = [
        'is_credit'     => 'boolean',
        'qty'           => 'float',
        'price'         => 'float',
        'exchange_rate' => 'float',
        'amount_paid'   => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class, 'movement_id');
    }

    // إجمالي قيمة العملية بالعملة الأصلية
    public function totalAmount(): float
    {
        return round(($this->price ?? 0) * $this->qty, 2);
    }

    // إجمالي ما دُفع (الدفعة الأولى + الدفعات اللاحقة) — كل شي بعملة الحركة
    public function totalPaid(): float
    {
        $initial = (float) ($this->amount_paid ?? 0);
        $later   = $this->debtPayments->sum(fn($dp) => $dp->amountInMovementCurrency($this));
        return round($initial + $later, 2);
    }

    // المبلغ المتبقي
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

    // قيمة الإجمالي بالدولار (للإحصائيات الموحّدة)
    // إذا البيع بالسوري وما في سعر صرف → نرجع 0 تفادياً لتضخيم الإحصائيات
    public function totalAmountUsd(): float
    {
        if ($this->currency === 'SYP') {
            if (!$this->exchange_rate || $this->exchange_rate <= 0) {
                return 0;
            }
            return round($this->totalAmount() / $this->exchange_rate, 2);
        }
        return $this->totalAmount();
    }
}
