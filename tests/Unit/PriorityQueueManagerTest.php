<?php

namespace Balkar\PriorityQueue\Tests\Unit;

use Balkar\PriorityQueue\PriorityQueueManager;
use Balkar\PriorityQueue\Tests\TestCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Queue;

class PriorityQueueManagerTest extends TestCase
{
    private PriorityQueueManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new PriorityQueueManager();
    }

    public function test_critical_dispatches_to_critical_queue(): void
    {
        Queue::fake();

        $job = new FakeJob();
        $this->manager->critical($job);

        Queue::assertPushedOn('critical', FakeJob::class);
    }

    public function test_high_dispatches_to_high_queue(): void
    {
        Queue::fake();

        $job = new FakeJob();
        $this->manager->high($job);

        Queue::assertPushedOn('high', FakeJob::class);
    }

    public function test_normal_dispatches_to_normal_queue(): void
    {
        Queue::fake();

        $job = new FakeJob();
        $this->manager->normal($job);

        Queue::assertPushedOn('normal', FakeJob::class);
    }

    public function test_low_dispatches_to_low_queue(): void
    {
        Queue::fake();

        $job = new FakeJob();
        $this->manager->low($job);

        Queue::assertPushedOn('low', FakeJob::class);
    }
}

class FakeJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void {}
}