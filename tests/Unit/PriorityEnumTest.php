<?php

namespace Balkar\PriorityQueue\Tests\Unit;

use Balkar\PriorityQueue\Enums\Priority;
use Balkar\PriorityQueue\Tests\TestCase;

class PriorityEnumTest extends TestCase
{
    public function test_priority_enum_has_correct_values(): void
    {
        $this->assertEquals('critical', Priority::Critical->value);
        $this->assertEquals('high',     Priority::High->value);
        $this->assertEquals('normal',   Priority::Normal->value);
        $this->assertEquals('low',      Priority::Low->value);
    }

    public function test_priority_enum_queue_method_returns_correct_queue_name(): void
    {
        $this->assertEquals('critical', Priority::Critical->queue());
        $this->assertEquals('high',     Priority::High->queue());
        $this->assertEquals('normal',   Priority::Normal->queue());
        $this->assertEquals('low',      Priority::Low->queue());
    }

    public function test_all_priority_cases_exist(): void
    {
        $cases = Priority::cases();
        $this->assertCount(4, $cases);
    }
}