<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Invoice;
use App\Policies\ConversationPolicy;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Conversation::class => ConversationPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | Optional Gates (si los necesitas más adelante)
        |--------------------------------------------------------------------------
        |
        | Gate::define('admin-only', fn (User $user) => $user->isAdmin());
        |
        */
    }
}
