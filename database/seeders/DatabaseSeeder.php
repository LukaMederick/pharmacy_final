<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\TipoPresentacion;
use App\Models\Proveedor;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\Producto;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. LLAMAR A OTROS SEEDERS (incluyendo el usuario que ya creaste, si es un seeder separado)
        $this->call(UserSeeder::class);
        // Crear categorías
        $categorias = [
            ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas alcohólicas y no alcohólicas', 'activo' => true],
            ['nombre' => 'Abarrotes', 'descripcion' => 'Productos de primera necesidad', 'activo' => true],
            ['nombre' => 'Limpieza', 'descripcion' => 'Productos de limpieza e higiene', 'activo' => true],
            ['nombre' => 'Lácteos', 'descripcion' => 'Productos lácteos y derivados', 'activo' => true],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        // Crear tipos de presentación
        $tiposPresentacion = [
            ['nombre' => 'Unidad', 'descripcion' => 'Venta por unidad', 'activo' => true],
            ['nombre' => 'Caja', 'descripcion' => 'Venta por caja', 'activo' => true],
            ['nombre' => 'Docena', 'descripcion' => 'Venta por docena', 'activo' => true],
            ['nombre' => 'Litro', 'descripcion' => 'Venta por litro', 'activo' => true],
            ['nombre' => 'Kilogramo', 'descripcion' => 'Venta por kilogramo', 'activo' => true],
        ];

        foreach ($tiposPresentacion as $tipo) {
            TipoPresentacion::create($tipo);
        }

        // Crear proveedores
        $proveedores = [
            [
                'nombre' => 'Distribuidora Lima SAC',
                'ruc' => '20123456789',
                'razon_social' => 'Distribuidora Lima Sociedad Anónima Cerrada',
                'direccion' => 'Av. Argentina 1234, Lima',
                'telefono' => '01-234-5678',
                'email' => 'ventas@distlima.com',
                'contacto' => 'Juan Pérez',
                'activo' => true
            ],
            [
                'nombre' => 'Comercial del Norte EIRL',
                'ruc' => '20987654321',
                'razon_social' => 'Comercial del Norte Empresa Individual de Responsabilidad Limitada',
                'direccion' => 'Jr. Comercio 567, Trujillo',
                'telefono' => '044-123-456',
                'email' => 'info@comnorte.com',
                'contacto' => 'María García',
                'activo' => true
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }

        // Crear empleados
        $empleados = [
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Rodríguez López',
                'dni' => '12345678',
                'cargo' => 'Administrador',
                'salario' => 2500.00,
                'telefono' => '987-654-321',
                'email' => 'carlos@sistema.com',
                'direccion' => 'Av. Principal 123, Lima',
                'fecha_ingreso' => '2024-01-15',
                'activo' => true
            ],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Martínez Silva',
                'dni' => '87654321',
                'cargo' => 'Vendedora',
                'salario' => 1500.00,
                'telefono' => '987-123-456',
                'email' => 'ana@sistema.com',
                'direccion' => 'Jr. Los Olivos 456, Lima',
                'fecha_ingreso' => '2024-02-01',
                'activo' => true
            ],
        ];

        foreach ($empleados as $empleado) {
            Empleado::create($empleado);
        }

        // Crear clientes
        $clientes = [
            [
                'nombres' => 'Pedro',
                'apellidos' => 'González Vega',
                'tipo_documento' => 'DNI',
                'numero_documento' => '11223344',
                'telefono' => '999-888-777',
                'email' => 'pedro@email.com',
                'direccion' => 'Calle Las Flores 789, Lima',
                'fecha_nacimiento' => '1985-05-20',
                'activo' => true
            ],
            [
                'nombres' => 'Empresa ABC',
                'apellidos' => 'SAC',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20556677889',
                'telefono' => '01-555-0123',
                'email' => 'compras@abc.com',
                'direccion' => 'Av. Industrial 1000, Lima',
                'activo' => true
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        // Crear productos
        $productos = [
            [
                'codigo' => 'BEB001',
                'nombre' => 'Coca Cola 500ml',
                'descripcion' => 'Gaseosa Coca Cola de 500ml',
                'categoria_id' => 1,
                'tipo_presentacion_id' => 1,
                'proveedor_id' => 1,
                'precio_compra' => 1.50,
                'precio_venta' => 2.50,
                'stock_minimo' => 20,
                'stock_actual' => 100,
                'unidad_medida' => 'unidad',
                'activo' => true
            ],
            [
                'codigo' => 'ABR001',
                'nombre' => 'Arroz Superior 1kg',
                'descripcion' => 'Arroz superior de 1 kilogramo',
                'categoria_id' => 2,
                'tipo_presentacion_id' => 5,
                'proveedor_id' => 2,
                'precio_compra' => 3.00,
                'precio_venta' => 4.50,
                'stock_minimo' => 50,
                'stock_actual' => 200,
                'unidad_medida' => 'kilogramo',
                'activo' => true
            ],
            [
                'codigo' => 'LIM001',
                'nombre' => 'Detergente Ariel 1kg',
                'descripcion' => 'Detergente en polvo Ariel de 1 kilogramo',
                'categoria_id' => 3,
                'tipo_presentacion_id' => 1,
                'proveedor_id' => 1,
                'precio_compra' => 8.00,
                'precio_venta' => 12.00,
                'stock_minimo' => 15,
                'stock_actual' => 50,
                'unidad_medida' => 'unidad',
                'activo' => true
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
