<?php

namespace App\Listeners;

use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Login $event): void
    {
        $user    = $event->user;
        $request = request();

        $this->userRepository->updateLastLogin($user->id, $request->ip());

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("Usuário {$user->name} realizou login no sistema.");

        Log::channel('daily')->info("Login: {$user->name} ({$user->email}) | IP: {$request->ip()}");
    }
}
