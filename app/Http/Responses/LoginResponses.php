<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;

class LoginResponses extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        \Log::info('User Role:', ['role' => $user->role]);

        return match ($user->role) {
            'admin' => redirect()->to(Filament::getPanel('admin')->getUrl()),
            'finance' => redirect()->to(Filament::getPanel('finance')->getUrl()),
            'owner' => redirect()->to(Filament::getPanel('owner')->getUrl()),
            default => redirect()->to(Filament::getPanel('user')->getUrl()),
        };
    }
}
