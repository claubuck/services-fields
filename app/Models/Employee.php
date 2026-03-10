<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_id',
        'category_id',
        'liquidation_modality_id',
        'nombre_apellido',
        'cuil',
        'dni',
        'direccion',
        'telefono',
        'estado',
        'fecha_inicio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public const ESTADOS = ['activo', 'inactivo', 'suspendido', 'pendiente_de_baja'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function liquidationModality(): BelongsTo
    {
        return $this->belongsTo(LiquidationModality::class);
    }
}
