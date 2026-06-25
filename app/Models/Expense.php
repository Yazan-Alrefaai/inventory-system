<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date', 'amount', 'currency', 'category', 'note', 'type'];
    protected $casts    = ['date' => 'date'];

    public function currencySymbol(): string
    {
        return $this->currency === 'SYP' ? 'ل.س' : '$';
    }
}
