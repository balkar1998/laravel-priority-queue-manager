<?php

namespace Balkar\PriorityQueue\Facades;

use Illuminate\Support\Facades\Facade;

class Priority extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Balkar\PriorityQueue\PriorityQueueManager::class;
    }
}