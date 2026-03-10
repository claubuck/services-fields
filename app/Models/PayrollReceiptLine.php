<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_receipt_id',
        'codigo',
        'concepto',
        'remuneracion',
        'retencion',
        'no_remunerativo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'remuneracion' => 'decimal:2',
            'retencion' => 'decimal:2',
            'no_remunerativo' => 'decimal:2',
        ];
    }

    public function payrollReceipt(): BelongsTo
    {
        return $this->belongsTo(PayrollReceipt::class);
    }
}
