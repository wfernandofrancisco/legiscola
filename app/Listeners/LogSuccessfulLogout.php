<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        $user    = $event->user;
        $request = request();

        if (! $user) {
            return;
        }

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("Usuário {$user->name} realizou logout do sistema.");

        Log::channel('daily')->info("Logout: {$user->name} ({$user->email}) | IP: {$request->ip()}");
    }
}
