<?php

namespace Balkar\PriorityQueue\Tests\Feature;

use Balkar\PriorityQueue\Facades\Priority;
use Balkar\PriorityQueue\PriorityQueueManager;
use Balkar\PriorityQueue\Tests\TestCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Queue;

class PriorityDispatchTest extends TestCase
{
    public function test_service_provider_registers_manager(): void
    {
        $manager = $this->app->make(PriorityQueueManager::class);
        $this->assertInstanceOf(PriorityQueueManager::class, $manager);
    }

    public function test_facade_resolves_correctly(): void
    {
        $this->assertInstanceOf(PriorityQueueManager::class, Priority::getFacadeRoot());
    }

    public function test_facade_dispatches_critical_job(): void
    {
        Queue::fake();

        Priority::critical(new FakeTestJob());

        Queue::assertPushedOn('critical', FakeTestJob::class);
    }

    public function test_facade_dispatches_low_job(): void
    {
        Queue::fake();

        Priority::low(new FakeTestJob());

        Queue::assertPushedOn('low', FakeTestJob::class);
    }

    public function test_config_is_loaded_correctly(): void
    {
        $config = config('priority-queue.priorities');

        $this->assertArrayHasKey('critical', $config);
        $this->assertArrayHasKey('high', $config);
        $this->assertArrayHasKey('normal', $config);
        $this->assertArrayHasKey('low', $config);
    }

    public function test_critical_config_has_most_workers(): void
    {
        $config = config('priority-queue.priorities');

        $this->assertGreaterThan(
            $config['low']['workers'],
            $config['critical']['workers']
        );
    }
}

class FakeTestJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void {}
}