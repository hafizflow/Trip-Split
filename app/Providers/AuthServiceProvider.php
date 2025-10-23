<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Trip;
use App\Policies\ExpensePolicy;
use App\Policies\TripPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Trip::class => TripPolicy::class,
        Expense::class => ExpensePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
