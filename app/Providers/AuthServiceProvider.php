<?php

namespace App\Providers;

use App\Models\Estimation;
use App\Models\Game;
use App\Models\Vote;
use App\Policies\EstimationPolicy;
use App\Policies\GamePolicy;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Game::class => GamePolicy::class,
        Estimation::class => EstimationPolicy::class,
        Vote::class => VotePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
