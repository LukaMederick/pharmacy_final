<?php

namespace App\Filament\Resources\MovimientoStockResource\Pages;

use App\Filament\Resources\MovimientoStockResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimientoStocks extends ListRecords
{
    protected static string $resource = MovimientoStockResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
