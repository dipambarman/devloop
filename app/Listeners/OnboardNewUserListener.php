<?php

namespace App\Listeners;

use App\Services\OnboardingService;
use Illuminate\Auth\Events\Registered;

class OnboardNewUserListener
{
    public function __construct(
        protected OnboardingService $onboardingService
    ) {}

    /**
     * Handle the Registered event.
     */
    public function handle(Registered $event): void
    {
        $this->onboardingService->setupForNewUser($event->user);
    }
}
