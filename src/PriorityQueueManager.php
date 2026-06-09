<?php

namespace Balkar\PriorityQueue;

use Balkar\PriorityQueue\Enums\Priority;
use Illuminate\Contracts\Queue\ShouldQueue;

class PriorityQueueManager
{
    public function dispatch(ShouldQueue $job, Priority $priority): void
    {
        dispatch($job)->onQueue($priority->queue());
    }

    public function critical(ShouldQueue $job): void
    {
        $this->dispatch($job, Priority::Critical);
    }

    public function high(ShouldQueue $job): void
    {
        $this->dispatch($job, Priority::High);
    }

    public function normal(ShouldQueue $job): void
    {
        $this->dispatch($job, Priority::Normal);
    }

    public function low(ShouldQueue $job): void
    {
        $this->dispatch($job, Priority::Low);
    }
}