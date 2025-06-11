<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimiento_stocks';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'referencia',
        'empleado_id',
        'fecha_movimiento',
        'observaciones'
    ];

    protected $casts = [
        'fecha_movimiento' => 'date'
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo_movimiento', 'ENTRADA');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo_movimiento', 'SALIDA');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tipo_movimiento', 'AJUSTE');
    }
}
