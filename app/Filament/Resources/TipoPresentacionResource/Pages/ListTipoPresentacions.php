<?php

namespace App\Filament\Resources\TipoPresentacionResource\Pages;

use App\Filament\Resources\TipoPresentacionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoPresentacions extends ListRecords
{
    protected static string $resource = TipoPresentacionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
