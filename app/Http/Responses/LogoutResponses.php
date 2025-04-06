<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;

class LogoutResponses extends BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        if (Filament::getCurrentPanel()->getId() !== 'user') {
            return redirect()->to(Filament::getPanel('user')->getLoginUrl());
        }

        return parent::toResponse($request);
    }
}
