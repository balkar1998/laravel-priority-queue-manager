# Laravel Priority Queue Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/balkar/laravel-priority-queue-manager.svg)](https://packagist.org/packages/balkar/laravel-priority-queue-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/balkar/laravel-priority-queue-manager.svg)](https://packagist.org/packages/balkar/laravel-priority-queue-manager)
[![Tests](https://github.com/YOUR_GITHUB_USERNAME/laravel-priority-queue-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/YOUR_GITHUB_USERNAME/laravel-priority-queue-manager/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/laravel-10%20|%2011%20|%2012%20|%2013-red)](https://laravel.com)

A Laravel package to dispatch and manage jobs across priority queues — **critical**, **high**, **normal**, and **low** — with a clean Facade API and a built-in artisan command to monitor queue health.

---

## The Problem

Laravel's default queue is first-in, first-out. If 500 newsletter jobs are queued ahead of an OTP job, your user waits 30 seconds on the login screen. There's no built-in concept of "this job is more important than that one."

## The Solution

This package gives you a clean priority layer on top of Laravel's existing queue system — no new infrastructure, no Redis required, works with any queue driver.

```php
Priority::critical(new SendOTPJob($user));     // runs first
Priority::high(new SendInvoiceJob($order));
Priority::normal(new SendWelcomeEmail($user));
Priority::low(new SendNewsletterJob($batch));  // runs last
```

---

## Requirements

| Laravel | PHP  | Testbench |
|---------|------|-----------|
| 10.x    | 8.2+ | ^8.0      |
| 11.x    | 8.2+ | ^9.0      |
| 12.x    | 8.2+ | ^10.0     |
| 13.x    | 8.3+ | ^10.0     |

---

## Installation

```bash
composer require balkar/laravel-priority-queue-manager
```

Publish the config file:

```bash
php artisan vendor:publish --tag=priority-queue-config
```

---

## Configuration

After publishing, `config/priority-queue.php` will contain:

```php
return [
    'priorities' => [
        'critical' => ['workers' => 3, 'retry_after' => 30,  'tries' => 5],
        'high'     => ['workers' => 2, 'retry_after' => 60,  'tries' => 4],
        'normal'   => ['workers' => 1, 'retry_after' => 90,  'tries' => 3],
        'low'      => ['workers' => 1, 'retry_after' => 300, 'tries' => 2],
    ],
];
```

Adjust workers, retry timing, and max tries per priority level to match your app's needs.

---

## Usage

### Dispatching jobs

Import the facade and dispatch any `ShouldQueue` job with a priority:

```php
use Balkar\PriorityQueue\Facades\Priority;

Priority::critical(new SendOTPJob($user));
Priority::high(new SendInvoiceJob($order));
Priority::normal(new SendWelcomeEmail($user));
Priority::low(new SendNewsletterJob($batch));
```

### Running workers in priority order

```bash
php artisan queue:work --queue=critical,high,normal,low
```

Laravel will always drain the `critical` queue first before moving to `high`, and so on.

### Running dedicated workers per priority

For high-traffic apps, run separate workers per queue:

```bash
# Terminal 1 — 3 workers for critical
php artisan queue:work --queue=critical &
php artisan queue:work --queue=critical &
php artisan queue:work --queue=critical &

# Terminal 2 — 1 worker for low priority
php artisan queue:work --queue=low
```

---

## Artisan Command

Monitor your queue health at any time:

```bash
php artisan queue:priority-status
```

Output:

Laravel Priority Queue Status
+----------+-------------+---------+-----------+-------------+--------+
| Priority | Queued Jobs | Workers | Max Tries | Retry After | Status |
+----------+-------------+---------+-----------+-------------+--------+
| CRITICAL |      1      |    3    |     5     |     30s     | ACTIVE |
| HIGH     |      5      |    2    |     4     |     60s     | ACTIVE |
| NORMAL   |     23      |    1    |     3     |     90s     | ACTIVE |
| LOW      |    150      |    1    |     2     |    300s     | BUSY   |
+----------+-------------+---------+-----------+-------------+--------+


Status levels:
- `IDLE` — no jobs in queue
- `ACTIVE` — 1–10 jobs queued
- `BUSY` — 10+ jobs queued

---

## Real World Example

This package was born from a real production problem — a student grade calculation engine that processed recursive async jobs across thousands of students. Critical recalculations (triggered by exam updates) were getting stuck behind bulk report generation jobs.

```php
// Exam result updated — must recalculate immediately
Priority::critical(new RecalculateStudentGrade($student, $exam));

// Bulk PDF report generation — can wait
Priority::low(new GenerateBulkReportJob($cohort));
```

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Contributing

Pull requests are welcome. For major changes, please open an issue first.

## License

MIT. Please see [LICENSE](LICENSE) for more information.

## Author

**Balkar Singh** — 

[balkar.co.in](https://balkar.co.in) · 
[GitHub](https://github.com/balkar1998) · 
[LinkedIn](https://linkedin.com/in/balkar-singh-6828a7234)



