# Changelog

## [1.0.0] - 2026-06-10

### Added
- `Priority` facade with `critical()`, `high()`, `normal()`, `low()` methods
- `PriorityQueueManager` core class
- `Priority` enum with queue name resolution
- `queue:priority-status` artisan command
- Config file with per-priority worker, retry, and tries settings
- Full test suite — 13 tests, 13 assertions
- Laravel 10, 11, 12, 13 support