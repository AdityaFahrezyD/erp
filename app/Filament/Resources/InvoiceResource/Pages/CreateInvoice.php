<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['user_id'] = $user->id;

        if (in_array($user->role, ['owner', 'admin'])) {
            $data['approve_status'] = 'approved';
        }

        return $data;
    }


}
