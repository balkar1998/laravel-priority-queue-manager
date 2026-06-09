<?php

namespace Balkar\PriorityQueue\Enums;

enum Priority: string
{
    case Critical = 'critical';
    case High     = 'high';
    case Normal   = 'normal';
    case Low      = 'low';

    public function queue(): string
    {
        return $this->value;
    }
}