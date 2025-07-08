<?php

namespace App\Filament\Resources\DeductionsResource\Pages;

use App\Filament\Resources\DeductionsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeductions extends CreateRecord
{
    protected static string $resource = DeductionsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
