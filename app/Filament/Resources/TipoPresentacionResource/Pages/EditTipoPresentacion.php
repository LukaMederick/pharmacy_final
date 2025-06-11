<?php

namespace App\Filament\Resources\TipoPresentacionResource\Pages;

use App\Filament\Resources\TipoPresentacionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoPresentacion extends EditRecord
{
    protected static string $resource = TipoPresentacionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
