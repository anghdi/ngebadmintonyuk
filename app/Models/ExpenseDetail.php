<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseDetail extends Model
{
    protected $fillable = ['name', 'amount', 'note'];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
