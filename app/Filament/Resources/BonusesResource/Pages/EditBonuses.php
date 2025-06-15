<?php

namespace App\Filament\Resources\BonusesResource\Pages;

use App\Filament\Resources\BonusesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonuses extends EditRecord
{
    protected static string $resource = BonusesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
