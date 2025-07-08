<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Enkripsi password dan hapus konfirmasi password dari data
        $data['id'] = Str::uuid();
        $data['password'] = Hash::make($data['password']);
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        unset($data['password_confirmation']);

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
