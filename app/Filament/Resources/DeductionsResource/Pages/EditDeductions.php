<?php

namespace App\Filament\Resources\DeductionsResource\Pages;

use App\Filament\Resources\DeductionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeductions extends EditRecord
{
    protected static string $resource = DeductionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
