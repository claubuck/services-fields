<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiquidationModality extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'precio_por_unidad'];

    protected function casts(): array
    {
        return [
            'precio_por_unidad' => 'decimal:2',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'liquidation_modality_id');
    }
}
