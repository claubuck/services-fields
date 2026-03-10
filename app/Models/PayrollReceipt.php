<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'liquidation_id',
        'employee_id',
        'categoria',
        'total_bruto',
        'total_retencion',
        'total_no_remunerativo',
        'neto_a_cobrar',
    ];

    protected function casts(): array
    {
        return [
            'total_bruto' => 'decimal:2',
            'total_retencion' => 'decimal:2',
            'total_no_remunerativo' => 'decimal:2',
            'neto_a_cobrar' => 'decimal:2',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollReceiptLine::class)->orderBy('orden');
    }
}
