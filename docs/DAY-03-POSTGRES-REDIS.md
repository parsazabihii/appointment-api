# Day 3 — Appointment API on PostgreSQL and Redis

Date: 2026-10-13. Source: https://github.com/parsazabihii/appointment-api at main commit e638943eaba5dfaaa8ac47c6b2a241804f5bcb58.

## Verified locally

- PHP 8.4.21, Laravel 13.25.0, PostgreSQL 18, phpredis extension, local Windows Redis 3.0.504.
- Dedicated PostgreSQL instance on localhost:55439 and dedicated Redis instance on localhost:6388, both with random local passwords. These credentials are excluded from the deliverables.
- Development database appointment_day3; disposable test database appointment_day3_test. Redis databases 2/3 for application queue/cache, 4/5 for tests.
- Composer install from the repository lockfile completed; migrations and demo seeding succeeded.
- PostgreSQL btree_gist extension and appointments_no_active_overlap exclusion constraint installed successfully.
- Existing suite executed with DB_CONNECTION=pgsql, CACHE_STORE=redis and QUEUE_CONNECTION=redis: **10 tests passed, 50 assertions**.
- **8 real HTTP checks passed**: customer login, providers, future slots, pending reservation/payment creation, removing the booked slot from cached availability, fake successful payment, confirmed/paid state and duplicate webhook acknowledgment.
- Redis PING, cache read/write and exclusive lock were verified through the Laravel application.
- Confirmation job entered the Redis queue and a real queue worker processed it. Queue returned to zero; a database notification was created and failed_jobs remained zero. Email used the local log driver; no external email was sent.

## Fix discovered by PostgreSQL verification

Provider registration test asserted user_id=1. PostgreSQL sequences advance even when a test transaction rolls back. The provider was created correctly with user_id=2; the assertion was wrong. The test now checks the ID returned by registration. No application behavior was changed.

## Repeatable setup

1. Install PHP with pdo_pgsql and phpredis, Composer, PostgreSQL and a Redis service. Use separate development and disposable test databases. For infrastructure via Docker use the project's docker-compose.yml; Docker Engine was unavailable during this verification.
2. composer install
3. Copy .env.example to .env; fill DB/Redis host, port and credentials. Keep CACHE_STORE=redis, QUEUE_CONNECTION=redis and MAIL_MAILER=log for local development. Use a nondefault FAKE_PAYMENT_WEBHOOK_SECRET.
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed (development only; seeded demo passwords are documented in the README)
7. php artisan serve
8. In another terminal: php artisan queue:work redis
9. In another terminal: php artisan schedule:work

For the PostgreSQL test suite, copy .env.testing.example to .env.testing; create appointment_api_test and configure its credentials. Use a dedicated Redis instance for tests, with DBs 4/5, to avoid touching real queues/cache. Run php vendor/phpunit/phpunit/phpunit -c phpunit.postgres.xml. This suite resets test database tables; never put production data there. Clear cached configuration before running tests.

## Limits and next day

- Redis 3.0.504 is the legacy version installed locally. This result proves the tested operations on that version, not compatibility or production readiness for a modern Redis deployment. Recheck the container environment before production.
- No simultaneous booking test was added on day 3. Reviewing booking lock/transaction paths is day 4; true concurrent same-slot requests are day 5.
- Scheduler lifecycle jobs, real SMTP, real payment gateway and production deployment were not validated here.
- The changes in this bundle are local and have NOT been pushed to GitHub. The repository's current CI still uses its existing SQLite suite.
