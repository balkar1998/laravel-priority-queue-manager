<?php

namespace Balkar\PriorityQueue\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PriorityQueueStatusCommand extends Command
{
    protected $signature = 'queue:priority-status';

    protected $description = 'Display the current status of all priority queues';

    public function handle(): void
    {
        $priorities = Config::get('priority-queue.priorities', []);

        if (empty($priorities)) {
            $this->error('No priority queues configured. Publish config with: php artisan vendor:publish --tag=priority-queue-config');
            return;
        }

        $this->info('');
        $this->info('  Laravel Priority Queue Status');
        $this->info('');

        $rows = [];

        foreach ($priorities as $level => $config) {
            $count = $this->getJobCount($level);

            $rows[] = [
                strtoupper($level),
                $count,
                $config['workers'],
                $config['tries'],
                $config['retry_after'] . 's',
                $this->getStatus($count),
            ];
        }

        $this->table(
            ['Priority', 'Queued Jobs', 'Workers', 'Max Tries', 'Retry After', 'Status'],
            $rows
        );

        $this->info('');
    }

    private function getJobCount(string $queue): int
    {
        try {
            return DB::table('jobs')
                ->where('queue', $queue)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getStatus(int $count): string
    {
        if ($count === 0) return '<fg=green>IDLE</>';
        if ($count <= 10) return '<fg=yellow>ACTIVE</>';
        return '<fg=red>BUSY</>';
    }
}