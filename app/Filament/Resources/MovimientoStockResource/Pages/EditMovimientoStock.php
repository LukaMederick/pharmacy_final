<?php

namespace App\Filament\Resources\MovimientoStockResource\Pages;

use App\Filament\Resources\MovimientoStockResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimientoStock extends EditRecord
{
    protected static string $resource = MovimientoStockResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
