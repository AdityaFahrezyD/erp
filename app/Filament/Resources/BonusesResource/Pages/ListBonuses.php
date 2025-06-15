<?php

namespace App\Filament\Resources\BonusesResource\Pages;

use App\Filament\Resources\BonusesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonuses extends ListRecords
{
    protected static string $resource = BonusesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
