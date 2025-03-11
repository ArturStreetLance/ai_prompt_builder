<?php

namespace App\Providers;

use App\Contracts\Services\PromptHistoryServiceInterface;
use App\Models\Prompt;
use App\Models\PromptHistory;
use App\Observers\PromptHistoryObserver;
use App\Observers\PromptObserver;
use App\Services\PromptHistoryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PromptHistoryServiceInterface::class, PromptHistoryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PromptHistory::observe(PromptHistoryObserver::class);
        Prompt::observe(PromptObserver::class);
    }
}
