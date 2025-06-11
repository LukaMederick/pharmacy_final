<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompraResource\Pages;
use App\Filament\Resources\CompraResource\RelationManagers;
use App\Models\Compra;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Closure;

class CompraResource extends Resource
{
    protected static ?string $model = Compra::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Compras';
    protected static ?string $pluralModelLabel = 'Compras';
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $navigationGroup = 'Transacciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Compra')
                    ->schema([
                        Forms\Components\TextInput::make('numero_compra')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'C-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        Forms\Components\Select::make('proveedor_id')
                            ->relationship('proveedor', 'nombre')
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('fecha_compra')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('empleado_id')
                            ->relationship('empleado', 'nombres')
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('Comprobante y Pago')
                    ->schema([
                        Forms\Components\Select::make('tipo_comprobante')
                            ->options([
                                'BOLETA' => 'Boleta',
                                'FACTURA' => 'Factura',
                                'TICKET' => 'Ticket',
                                'NOTA' => 'Nota de Compra',
                            ])
                            ->default('BOLETA')
                            ->required(),
                        Forms\Components\TextInput::make('numero_comprobante')
                            ->maxLength(255),
                        Forms\Components\Select::make('metodo_pago')
                            ->options([
                                'EFECTIVO' => 'Efectivo',
                                'TARJETA' => 'Tarjeta',
                                'TRANSFERENCIA' => 'Transferencia',
                                'CREDITO' => 'Crédito',
                            ])
                            ->default('EFECTIVO')
                            ->required(),
                        Forms\Components\Select::make('estado')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'COMPLETADA' => 'Completada',
                                'CANCELADA' => 'Cancelada',
                            ])
                            ->default('PENDIENTE')
                            ->required(),
                    ])->columns(4),

                Forms\Components\Section::make('Detalles de la Compra')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship('detalles') // Asegúrate de que esta relación exista en tu modelo Compra
                            ->schema([
                                Forms\Components\Select::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->required()
                                    ->searchable()
                                    ->reactive() // Importante para que los cambios en el Select disparen actualizaciones
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $producto = \App\Models\Producto::find($state);
                                            if ($producto) {
                                                $precio_compra_float = (float) $producto->precio_compra;
                                                $set('precio_unitario', number_format($precio_compra_float, 2, '.', ''));
                                                $cantidad = (float) $get('cantidad') ?: 1;
                                                $set('subtotal', number_format($cantidad * $precio_compra_float, 2, '.', ''));
                                            }
                                        } else {
                                            $set('precio_unitario', null);
                                            $set('subtotal', null);
                                        }
                                        static::updateCompraTotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('cantidad')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->step(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $cantidad = (float) $state;
                                        $precio = (float) $get('precio_unitario') ?: 0;
                                        $subtotal = $cantidad * $precio;
                                        $set('subtotal', number_format($subtotal, 2, '.', ''));
                                        static::updateCompraTotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/.')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $precio = (float) $state;
                                        $cantidad = (float) $get('cantidad') ?: 0;
                                        $subtotal = $cantidad * $precio;
                                        $set('subtotal', number_format($subtotal, 2, '.', ''));
                                        static::updateCompraTotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/.')
                                    ->step(0.01)
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false) // Esto significa que NO se enviará al estado principal para ser guardado.
                                    ->extraAttributes(['class' => 'font-bold']),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->createItemButtonLabel('Agregar Producto')
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                static::updateCompraTotal($get, $set);
                            })
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                static::updateCompraTotal($get, $set);
                            }),
                    ]),

                Forms\Components\TextInput::make('total')
                    ->label(new HtmlString('<span style="font-weight: bold; font-size: 1.2em;">Total de Compra</span>'))
                    ->required()
                    ->numeric()
                    ->prefix('S/.')
                    ->step(0.01)
                    ->default(0)
                    ->disabled()
                    ->dehydrated(true) // Asegura que el total calculado se guarde en la base de datos
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-right text-lg font-extrabold text-primary-600']),
                
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    // Método estático para calcular y actualizar el total de la compra
    public static function updateCompraTotal(callable $get, callable $set): void
    {
        $detalles = $get('detalles'); // Obtiene todos los ítems del repeater 'detalles'
        $calculatedTotal = 0;

        if (!empty($detalles)) {
            foreach ($detalles as $detalle) {
                // RECALCULAMOS el subtotal aquí mismo, porque el campo 'subtotal' está dehydrated(false)
                // y su valor no se pasa al estado principal de Livewire.
                $cantidad = floatval($detalle['cantidad'] ?? 0);
                $precio_unitario = floatval($detalle['precio_unitario'] ?? 0);
                
                $lineSubtotal = $cantidad * $precio_unitario;
                
                $calculatedTotal += $lineSubtotal;
            }
        }
        
        // Redondea el total final a 2 decimales y lo establece en el campo 'total'
        $set('total', number_format($calculatedTotal, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_compra')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_compra')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_comprobante'),
                Tables\Columns\TextColumn::make('metodo_pago'),
                Tables\Columns\TextColumn::make('total')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'COMPLETADA' => 'success',
                        'CANCELADA' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'COMPLETADA' => 'Completada',
                        'CANCELADA' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->options([
                        'EFECTIVO' => 'Efectivo',
                        'TARJETA' => 'Tarjeta',
                        'TRANSFERENCIA' => 'Transferencia',
                        'CREDITO' => 'Crédito',
                    ]),
                Tables\Filters\SelectFilter::make('proveedor')
                    ->relationship('proveedor', 'nombre'),
                Tables\Filters\Filter::make('fecha_compra')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_compra', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_compra', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompras::route('/'),
            'create' => Pages\CreateCompra::route('/create'),
            'edit' => Pages\EditCompra::route('/{record}/edit'),
        ];
    }
}
