<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Filament\Resources\VentaResource\RelationManagers;
use App\Models\Venta;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Ventas';
    protected static ?string $pluralModelLabel = 'Ventas';
    protected static ?string $modelLabel = 'Venta';
    protected static ?string $navigationGroup = 'Transacciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Venta')
                    ->schema([
                        Forms\Components\TextInput::make('numero_venta')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'V-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente', 'nombres')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombres')
                                    ->required(),
                                Forms\Components\TextInput::make('apellidos')
                                    ->required(),
                                Forms\Components\Select::make('tipo_documento')
                                    ->options([
                                        'DNI' => 'DNI',
                                        'RUC' => 'RUC',
                                        'CE' => 'Carnet de Extranjería',
                                        'PASAPORTE' => 'Pasaporte',
                                    ])
                                    ->default('DNI'),
                                Forms\Components\TextInput::make('numero_documento')
                                    ->required(),
                                Forms\Components\TextInput::make('telefono'),
                                Forms\Components\TextInput::make('email'),
                                Forms\Components\Toggle::make('activo')
                                    ->default(true),
                            ]),
                        Forms\Components\Select::make('empleado_id')
                            ->relationship('empleado', 'nombres')
                            ->required()
                            ->default(1),
                        Forms\Components\DatePicker::make('fecha_venta')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Comprobante')
                    ->schema([
                        Forms\Components\Select::make('tipo_comprobante')
                            ->options([
                                'BOLETA' => 'Boleta',
                                'FACTURA' => 'Factura',
                                'TICKET' => 'Ticket',
                                'NOTA' => 'Nota de Venta',
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

                Forms\Components\Section::make('Detalles de la Venta')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $producto = \App\Models\Producto::find($state);
                                            if ($producto) {
                                                $set('precio_unitario', $producto->precio_venta);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('cantidad')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $precio = $get('precio_unitario') ?: 0;
                                        $descuento = $get('descuento') ?: 0;
                                        $subtotal = ($state * $precio) - $descuento;
                                        $set('subtotal', $subtotal);
                                    }),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/.')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $cantidad = $get('cantidad') ?: 0;
                                        $descuento = $get('descuento') ?: 0;
                                        $subtotal = ($cantidad * $state) - $descuento;
                                        $set('subtotal', $subtotal);
                                    }),
                                Forms\Components\TextInput::make('descuento')
                                    ->numeric()
                                    ->prefix('S/.')
                                    ->step(0.01)
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $cantidad = $get('cantidad') ?: 0;
                                        $precio = $get('precio_unitario') ?: 0;
                                        $subtotal = ($cantidad * $precio) - $state;
                                        $set('subtotal', $subtotal);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/.')
                                    ->step(0.01)
                                    ->disabled(),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->createItemButtonLabel('Agregar Producto'),
                    ]),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (callable $set, $state, $record) {
                                if ($record) {
                                    $totalDetalles = $record->detalles->sum('subtotal');
                                    $set('subtotal', $totalDetalles);
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $detalles = $get('detalles');
                                $totalDetalles = collect($detalles)->sum(function ($item) {
                                    $cantidad = $item['cantidad'] ?? 0;
                                    $precio = $item['precio_unitario'] ?? 0;
                                    $descuento = $item['descuento'] ?? 0;
                                    return ($cantidad * $precio) - $descuento;
                                });
                                $impuesto = $get('impuesto') ?: 0;
                                $descuentoGlobal = $get('descuento_global') ?: 0;
                                $total = $totalDetalles + $impuesto - $descuentoGlobal;
                                $set('subtotal', $totalDetalles);
                                $set('total', $total);
                            }),
                        Forms\Components\TextInput::make('impuesto')
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $detalles = $get('detalles');
                                $totalDetalles = collect($detalles)->sum(function ($item) {
                                    return ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);
                                });
                                $impuesto = $get('impuesto') ?: 0;
                                $descuentoGlobal = $get('descuento_global') ?: 0;
                                $total = $totalDetalles + $impuesto - $descuentoGlobal;
                                $set('total', $total);
                            }),
                        Forms\Components\TextInput::make('descuento_global')
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $detalles = $get('detalles');
                                $totalDetalles = collect($detalles)->sum(function ($item) {
                                    return ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);
                                });
                                $impuesto = $get('impuesto') ?: 0;
                                $descuentoGlobal = $get('descuento_global') ?: 0;
                                $total = $totalDetalles + $impuesto - $descuentoGlobal;
                                $set('total', $total);
                            }),
                        Forms\Components\TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01)
                            ->disabled(),
                        Forms\Components\Textarea::make('observaciones')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(4),
            ]); // ELIMINADA LA LÍNEA ->afterStateUpdated() DE AQUÍ.
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_venta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.nombres')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_venta')
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
                Tables\Columns\TextColumn::make('empleado.nombres')
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
                Tables\Filters\SelectFilter::make('cliente')
                    ->relationship('cliente', 'nombres'),
                Tables\Filters\Filter::make('fecha_venta')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_venta', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_venta', '<=', $date),
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
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
        ];
    }
}