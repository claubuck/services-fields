<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Liquidation extends Model
{
    use HasFactory;

    protected $fillable = ['mes', 'anio', 'fecha_pago'];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
        ];
    }

    public function payrollReceipts(): HasMany
    {
        return $this->hasMany(PayrollReceipt::class);
    }

    public function getPeriodoAttribute(): string
    {
        return str_pad((string) $this->mes, 2, '0', STR_PAD_LEFT) . '*' . $this->anio;
    }
}
