# Laravel Priority Queue Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/balkar/laravel-priority-queue-manager.svg)](https://packagist.org/packages/balkar/laravel-priority-queue-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/balkar/laravel-priority-queue-manager.svg)](https://packagist.org/packages/balkar/laravel-priority-queue-manager)
[![Tests](https://github.com/balkar1998/laravel-priority-queue-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/balkar1998/laravel-priority-queue-manager/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/laravel-10%20|%2011%20|%2012%20|%2013-red)](https://laravel.com)

A Laravel package that standardises priority queue dispatching across your team — with a clean Facade API, a shared config file, and a built-in artisan command to monitor queue health.

---

## Why This Exists

Laravel already supports named queues. You can do this:

```php
dispatch(new SendOTP($user))->onQueue('high');

php artisan queue:work --queue=high,low
```

That works fine for a solo developer. In a team it breaks down:

- One dev writes `'high'`, another writes `'urgent'`, another forgets `->onQueue()` entirely and it silently hits the default queue
- No shared definition of what priority levels exist or what they mean
- No way to check queue depth per priority without writing a raw DB query
- Retry config and worker counts live in Supervisor files, deployment scripts, or someone's head

This package fixes all of that with a shared standard your whole team uses.

---

## What It Does

**Consistent dispatch API:**

```php
use Balkar\PriorityQueue\Facades\Priority;

Priority::critical(new SendOTPJob($user));
Priority::high(new SendInvoiceJob($order));
Priority::normal(new SendWelcomeEmail($user));
Priority::low(new SendNewsletterJob($batch));
```

**One config file for the whole team:**

```php
// config/priority-queue.php
return [
    'priorities' => [
        'critical' => ['retry_after' => 30,  'tries' => 5],
        'high'     => ['retry_after' => 60,  'tries' => 4],
        'normal'   => ['retry_after' => 90,  'tries' => 3],
        'low'      => ['retry_after' => 300, 'tries' => 2],
    ],
];
```

**Queue health at a glance:**

```bash
php artisan queue:priority-status
```
+----------+-------------+-----------+-------------+--------+

| Priority | Queued Jobs | Max Tries | Retry After | Status |

+----------+-------------+-----------+-------------+--------+

| CRITICAL |      0      |     5     |     30s     | IDLE   |

| HIGH     |      4      |     4     |     60s     | ACTIVE |

| NORMAL   |     23      |     3     |     90s     | ACTIVE |

| LOW      |    150      |     2     |    300s     | BUSY   |

+----------+-------------+-----------+-------------+--------+

---

## Requirements

| Laravel | PHP  |
|---------|------|
| 10.x    | 8.2+ |
| 11.x    | 8.2+ |
| 12.x    | 8.2+ |
| 13.x    | 8.3+ |

---

## Installation

```bash
composer require balkar/laravel-priority-queue-manager
```

Publish the config:

```bash
php artisan vendor:publish --tag=priority-queue-config
```

---

## Configuration

`config/priority-queue.php` after publishing:

```php
return [
    'priorities' => [
        'critical' => ['retry_after' => 30,  'tries' => 5],
        'high'     => ['retry_after' => 60,  'tries' => 4],
        'normal'   => ['retry_after' => 90,  'tries' => 3],
        'low'      => ['retry_after' => 300, 'tries' => 2],
    ],
];
```

`retry_after` is in seconds. `tries` is the maximum number of attempts before a job fails.

---

## Usage

### Dispatching jobs

```php
use Balkar\PriorityQueue\Facades\Priority;

Priority::critical(new SendOTPJob($user));
Priority::high(new SendInvoiceJob($order));
Priority::normal(new SendWelcomeEmail($user));
Priority::low(new SendNewsletterJob($batch));
```

Under the hood this calls Laravel's native `dispatch($job)->onQueue($priority)` — no magic, just a consistent API on top of what Laravel already does.

### Running workers

Start a single worker that respects priority order:

```bash
php artisan queue:work --queue=critical,high,normal,low
```

Laravel processes `critical` first. Only when it is empty does it move to `high`, and so on.

### Production worker management

Use Supervisor to keep workers running. Example config:

```ini
[program:queue-critical]
command=php /var/www/html/artisan queue:work --queue=critical --tries=5 --timeout=30
numprocs=3
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/queue-critical.err.log

[program:queue-low]
command=php /var/www/html/artisan queue:work --queue=low --tries=2 --timeout=300
numprocs=1
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/queue-low.err.log
```

Supervisor is one option — you can also use systemd, Docker, or your platform's native process manager. The package has no dependency on any specific process manager.

---

## Monitoring

```bash
php artisan queue:priority-status
```
+----------+-------------+-----------+-------------+--------+

| Priority | Queued Jobs | Max Tries | Retry After | Status |

+----------+-------------+-----------+-------------+--------+

| CRITICAL |      0      |     5     |     30s     | IDLE   |

| HIGH     |      4      |     4     |     60s     | ACTIVE |

| NORMAL   |     23      |     3     |     90s     | ACTIVE |

| LOW      |    150      |     2     |    300s     | BUSY   |

+----------+-------------+-----------+-------------+--------+

Status is based on current job count in the database queue table:
- `IDLE` — 0 jobs
- `ACTIVE` — 1 to 10 jobs
- `BUSY` — more than 10 jobs

This command reads from the `jobs` table and works with the `database` queue driver out of the box. For Redis, SQS, or other drivers the count will show 0 — driver-specific monitoring support is on the roadmap.

---

## Known Limitations

**Starvation** — strict priority ordering means low priority jobs can wait indefinitely if critical and high queues stay full. The monitoring command gives you visibility into this. Configurable max wait time with automatic job promotion is planned for v1.1.

**Driver support** — `queue:priority-status` job counts currently only work with the `database` driver.

**No automatic worker management** — the package standardises dispatch and config. Starting and managing worker processes is your responsibility via Supervisor, systemd, or your deployment setup.

---

## Real World Context

At my previous company we built a student grade calculation engine that processed recursive async jobs across thousands of students. When teachers triggered bulk mark updates, thousands of recalculation jobs would fill the queue and block time-sensitive jobs like OTP delivery.

The fix was named queues with strict priority ordering — this package came out of making that pattern reusable and consistent across the team.

```php
// triggered by exam update — needs to run immediately
Priority::critical(new RecalculateStudentGrade($student, $exam));

// nightly bulk export — can wait
Priority::low(new GenerateBulkReportJob($cohort));
```

---

## Roadmap

- [ ] Anti-starvation: configurable max wait time with automatic job promotion
- [ ] Redis and SQS driver support for queue:priority-status
- [ ] Lumen support
- [ ] Prometheus metrics export

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Contributing

Pull requests are welcome. For major changes please open an issue first to discuss what you would like to change.

## License

MIT. Please see [LICENSE](LICENSE) for more information.

## Author

**Balkar Singh**

[balkar.co.in](https://balkar.co.in) 
[GitHub](https://github.com/balkar1998) 
[LinkedIn](https://linkedin.com/in/balkar-singh-6828a7234)
