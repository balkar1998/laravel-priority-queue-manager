<?php

namespace Balkar\PriorityQueue;

use Illuminate\Support\ServiceProvider;

class PriorityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/priority-queue.php', 'priority-queue');

        $this->app->singleton(PriorityQueueManager::class, fn () => new PriorityQueueManager());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/priority-queue.php' => config_path('priority-queue.php'),
            ], 'priority-queue-config');
        }
    }
}