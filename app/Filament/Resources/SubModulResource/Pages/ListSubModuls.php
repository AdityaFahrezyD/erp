<?php

namespace App\Filament\Resources\SubModulResource\Pages;

use App\Filament\Resources\SubModulResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubModuls extends ListRecords
{
    protected static string $resource = SubModulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
