<?php

namespace App\Filament\Resources\OtherExpenseResource\Pages;

use App\Filament\Resources\OtherExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtherExpense extends EditRecord
{
    protected static string $resource = OtherExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
