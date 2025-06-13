<?php

namespace App\Filament\Resources\DeductionsResource\Pages;

use App\Filament\Resources\DeductionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeductions extends ListRecords
{
    protected static string $resource = DeductionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
