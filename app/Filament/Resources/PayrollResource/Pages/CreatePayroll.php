<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['user_id'] = $user->id;


        if (in_array($user->role, ['owner', 'admin'])) {
            $data['approve_status'] = 'pending';
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Ambil data form
        $data['net_salary'] = $this->form->getState()['net_salary']; // Ambil net_salary dari form
        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirect ke halaman list setelah create
    }

    protected function afterCreate(): void
    {
        // Pastikan record tersimpan
        if (!$this->record) {
            throw new \Exception('Failed to create payroll record.');
        }
    }
}
