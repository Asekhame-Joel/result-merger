<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getTitle(): string
    {
        return 'Result Merger Login';
    }

    public function getHeading(): string
    {
        return '';
    }
}