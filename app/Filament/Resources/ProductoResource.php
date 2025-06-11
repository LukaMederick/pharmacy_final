<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('descripcion')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('categoria_id')
                            ->relationship('categoria', 'nombre')
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombre')
                                    ->required(),
                                Forms\Components\Textarea::make('descripcion'),
                                Forms\Components\Toggle::make('activo')
                                    ->default(true),
                            ]),
                        Forms\Components\Select::make('tipo_presentacion_id')
                            ->relationship('tipoPresentacion', 'nombre')
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombre')
                                    ->required(),
                                Forms\Components\Textarea::make('descripcion'),
                                Forms\Components\Toggle::make('activo')
                                    ->default(true),
                            ]),
                        Forms\Components\Select::make('proveedor_id')
                            ->relationship('proveedor', 'nombre')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombre')
                                    ->required(),
                                Forms\Components\TextInput::make('ruc')
                                    ->required(),
                                Forms\Components\TextInput::make('razon_social')
                                    ->required(),
                                Forms\Components\TextInput::make('direccion')
                                    ->required(),
                                Forms\Components\TextInput::make('telefono')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->required(),
                                Forms\Components\Textarea::make('contacto'),
                                Forms\Components\Toggle::make('activo')
                                    ->default(true),
                            ]),
                    ])->columns(3),
                
                Forms\Components\Section::make('Precios y Stock')
                    ->schema([
                        Forms\Components\TextInput::make('precio_compra')
                            ->required()
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01), // Mantén 0.01 para precisión de centavos
                        Forms\Components\TextInput::make('precio_venta')
                            ->required()
                            ->numeric()
                            ->prefix('S/.')
                            ->step(0.01), // Mantén 0.01 para precisión de centavos
                        Forms\Components\TextInput::make('stock_minimo')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('stock_actual')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('unidad_medida')
                            ->required()
                            ->default('unidad')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('activo')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_venta')
                    // Deshabilitamos money() y usamos formatStateUsing para un control manual
                    ->formatStateUsing(fn (float $state): string => 'S/. ' . number_format($state, 2, '.', ','))
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('activo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre'),
                Tables\Filters\SelectFilter::make('proveedor')
                    ->relationship('proveedor', 'nombre'),
                Tables\Filters\TernaryFilter::make('activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }    
}