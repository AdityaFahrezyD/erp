<?php

namespace App\Filament\Resources\BonusesResource\Pages;

use App\Filament\Resources\BonusesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBonuses extends CreateRecord
{
    protected static string $resource = BonusesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
