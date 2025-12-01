<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\MovimientoStock;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * Registra un movimiento de stock y actualiza el stock actual del producto
     */
    public function registrarMovimiento(
        int $productoId,
        string $tipoMovimiento,
        int $cantidad,
        string $motivo,
        int $empleadoId,
        ?string $referencia = null,
        ?string $observaciones = null
    ): MovimientoStock {
        return DB::transaction(function () use (
            $productoId,
            $tipoMovimiento,
            $cantidad,
            $motivo,
            $empleadoId,
            $referencia,
            $observaciones
        ) {
            $producto = Producto::findOrFail($productoId);
            $stockAnterior = $producto->stock_actual;

            // Calcular nuevo stock según el tipo de movimiento
            $stockNuevo = match ($tipoMovimiento) {
                'ENTRADA' => $stockAnterior + $cantidad,
                'SALIDA' => $stockAnterior - $cantidad,
                'AJUSTE' => $cantidad, // En ajuste, la cantidad es el nuevo stock total
                default => throw new Exception("Tipo de movimiento no válido: {$tipoMovimiento}")
            };

            // Validar que el stock no sea negativo
            if ($stockNuevo < 0) {
                throw new Exception("Stock insuficiente. Stock actual: {$stockAnterior}, cantidad solicitada: {$cantidad}");
            }

            // Crear el movimiento de stock n
            $movimiento = MovimientoStock::create([
                'producto_id' => $productoId,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => $tipoMovimiento === 'AJUSTE' ? ($stockNuevo - $stockAnterior) : $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $motivo,
                'referencia' => $referencia,
                'empleado_id' => $empleadoId,
                'fecha_movimiento' => now()->toDateString(),
                'observaciones' => $observaciones,
            ]);

            // Actualizar el stock del producto
            $producto->update(['stock_actual' => $stockNuevo]);

            return $movimiento;
        });
    }

    /**
     * Registra una entrada de stock (compra)
     */
    public function registrarEntrada(
        int $productoId,
        int $cantidad,
        int $empleadoId,
        string $referencia,
        ?string $observaciones = null
    ): MovimientoStock {
        return $this->registrarMovimiento(
            $productoId,
            'ENTRADA',
            $cantidad,
            'Compra de mercadería',
            $empleadoId,
            $referencia,
            $observaciones
        );
    }

    /**
     * Registra una salida de stock (venta)
     */
    public function registrarSalida(
        int $productoId,
        int $cantidad,
        int $empleadoId,
        string $referencia,
        ?string $observaciones = null
    ): MovimientoStock {
        return $this->registrarMovimiento(
            $productoId,
            'SALIDA',
            $cantidad,
            'Venta de producto',
            $empleadoId,
            $referencia,
            $observaciones
        );
    }

    /**
     * Registra un ajuste de stock
     */
    public function registrarAjuste(
        int $productoId,
        int $nuevoStock,
        int $empleadoId,
        string $motivo,
        ?string $observaciones = null
    ): MovimientoStock {
        return $this->registrarMovimiento(
            $productoId,
            'AJUSTE',
            $nuevoStock,
            $motivo,
            $empleadoId,
            null,
            $observaciones
        );
    }

    /**
     * Verifica si hay stock suficiente para una venta
     */
    public function verificarStock(int $productoId, int $cantidadRequerida): bool
    {
        $producto = Producto::findOrFail($productoId);
        return $producto->stock_actual >= $cantidadRequerida;
    }

    /**
     * Obtiene productos con stock bajo
     */
    public function getProductosStockBajo()
    {
        return Producto::stockBajo()
            ->activos()
            ->with(['categoria', 'proveedor'])
            ->get();
    }

    /**
     * Obtiene el historial de movimientos de un producto
     */
    public function getHistorialMovimientos(int $productoId, int $limite = 50)
    {
        return MovimientoStock::where('producto_id', $productoId)
            ->with(['empleado'])
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }
}

