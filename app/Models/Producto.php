<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($producto) {
            if (isset($producto->precio_compra)) {
                $producto->precio_compra = (float) $producto->precio_compra;
            }
            if (isset($producto->precio_venta)) {
                $producto->precio_venta = (float) $producto->precio_venta;
            }
        });
    }

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria_id',
        'tipo_presentacion_id',
        'proveedor_id',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'stock_actual',
        'unidad_medida',
        'activo'
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean'
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function tipoPresentacion(): BelongsTo
    {
        return $this->belongsTo(TipoPresentacion::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function movimientosStock(): HasMany
    {
        return $this->hasMany(MovimientoStock::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
}
