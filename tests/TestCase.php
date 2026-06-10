<?php

namespace Balkar\PriorityQueue\Tests;

use Balkar\PriorityQueue\PriorityServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PriorityServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('priority-queue.priorities', [
            'critical' => ['workers' => 3, 'retry_after' => 30,  'tries' => 5],
            'high'     => ['workers' => 2, 'retry_after' => 60,  'tries' => 4],
            'normal'   => ['workers' => 1, 'retry_after' => 90,  'tries' => 3],
            'low'      => ['workers' => 1, 'retry_after' => 300, 'tries' => 2],
        ]);
    }
}