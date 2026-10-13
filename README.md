# Appointment Booking API — Laravel 13

Portfolio project: a REST API for appointment scheduling. The project deliberately goes beyond CRUD: dynamic slot generation, concurrency control, temporary booking holds, payment webhooks, idempotency, queues, notifications, scheduled lifecycle jobs, Redis caching/locks, and PostgreSQL overlap protection.

## Stack
- PHP 8.3+
- Laravel 13
- Laravel Sanctum token authentication
- PostgreSQL
- Redis (cache, atomic locks, queue)
- Laravel Queue / Notifications / Scheduler
- PHPUnit feature tests
- OpenAPI + Bruno collection

## Core domain
`User -> Provider -> Services -> Availability -> Slots -> Appointment -> Payment`

A Provider can offer many Services. `provider_service` stores provider-specific price and active state. Weekly `availabilities` are local-time rules. `blocked_times` and `appointments` are concrete UTC timestamps.

## Concurrency / double-booking strategy
Booking uses three layers:
1. `Cache::lock("booking:provider:{id}")` — distributed atomic lock (Redis in normal configuration).
2. `DB::transaction()` plus PostgreSQL `pg_advisory_xact_lock(provider_id)` — serializes booking decisions per provider inside the DB transaction.
3. PostgreSQL `EXCLUDE USING gist` constraint on `[starts_at, ends_at)` for `pending|confirmed` appointments — database-level last line of defense.

The booking service re-generates availability inside the lock/transaction before inserting, so a stale slot shown to a client cannot simply be trusted.

## Appointment lifecycle
- `pending`: slot is held for `BOOKING_HOLD_MINUTES` (default 10).
- `confirmed`: payment success webhook arrived before hold expiry.
- `expired`: payment failed / hold timed out.
- `cancelled`: explicitly cancelled within policy window.
- `completed`: confirmed appointment ended.

Cancellation/reschedule default deadline: 6 hours before the appointment.

## Payment / webhook
A fake payment provider is included so the architecture can be tested without real credentials. Every appointment has one Payment with an `external_reference`.

Webhook:
`POST /api/webhooks/payments/fake`
Header: `X-Webhook-Secret: <FAKE_PAYMENT_WEBHOOK_SECRET>`

Example payload:
```json
{
  "event_id": "evt_1001",
  "type": "payment.succeeded",
  "external_reference": "fake_...",
  "appointment_id": 1
}
```
`webhook_events.event_id` plus an atomic lock makes webhook processing idempotent. A late success after hold expiry is recorded as refunded instead of confirming an unavailable appointment.

## Redis / cache
Slot lists are cached for 5 minutes. Cache keys include a per-provider version. Any relevant mutation (availability, block, booking, cancellation, reschedule, payment/expiry) increments the version, invalidating old slot caches without expensive wildcard deletion.

## Queue / notifications
Confirmation, cancellation and reminder emails/database notifications are performed through queued Jobs. Start a worker:
```bash
php artisan queue:work
```
For local testing, `MAIL_MAILER=log` writes mail to `storage/logs/laravel.log`.

## Scheduler
`routes/console.php` schedules:
- `appointments:expire-holds` every minute
- `appointments:dispatch-reminders` every minute
- `appointments:complete` every five minutes

Local development:
```bash
php artisan schedule:work
```
Production cron only needs Laravel's `schedule:run` every minute.

## Setup (existing Windows/PostgreSQL)
```bat
composer install
copy .env.example .env
php artisan key:generate
```
Create PostgreSQL database `appointment_api`, set `.env` credentials, then:
```bat
php artisan migrate
php artisan db:seed
php artisan serve
```
Run Redis locally, via WSL/Docker, or use the included `docker-compose.yml`. For Docker infrastructure only:
```bash
docker compose up -d postgres redis
```
If you already have PostgreSQL on port 5432, start only Redis or change the compose port.

### Required PHP extension for default Redis configuration
The example uses `REDIS_CLIENT=phpredis`. Ensure `phpredis` is installed/enabled, or install/configure Predis yourself.

## Local test accounts (seeder)

After configuring your local database, run `php artisan db:seed` to create the following test accounts. This is a local development setup, not a hosted online demo.

Password for newly created accounts: `password123`
- `admin@example.com`
- `provider@example.com`
- `customer@example.com`

The seeder uses `firstOrCreate`: existing accounts keep their current passwords. Change/remove local test credentials before deployment.

## Main API flow
1. `POST /api/auth/login`
2. `GET /api/providers`
3. `GET /api/providers/{provider}/slots?date=YYYY-MM-DD&service_id=1`
4. `POST /api/appointments` using the exact ISO `starts_at` returned by the slots endpoint
5. Receive pending Appointment + Payment reference
6. Send fake payment webhook
7. Appointment becomes `confirmed`; confirmation Job is queued

Use an `Idempotency-Key` header on appointment creation to make client retries safe.

## Provider management
Authenticated provider users can manage:
- `/api/provider/services`
- `/api/provider/availabilities`
- `/api/provider/blocked-times`

`day_of_week` convention: `0=Sunday ... 6=Saturday`.

## Admin
Admin-only endpoints create/update/deactivate the global service catalog under `/api/admin/services`.

## Tests
```bash
php artisan test
```
Feature coverage includes registration/provider creation, slot blocking, pending booking/payment creation, duplicate-slot rejection, payment confirmation, webhook idempotency, preservation of paid/refunded states across later webhook events, and repeatable demo seeding without resetting existing passwords.

SQLite is used by PHPUnit for portable tests; the PostgreSQL-only GIST exclusion constraint is conditionally installed only when using `pgsql`. Production concurrency protection should be verified against PostgreSQL as well.

## PostgreSQL note
The overlap migration enables `btree_gist`:
```sql
CREATE EXTENSION IF NOT EXISTS btree_gist;
```
Some managed PostgreSQL platforms may require enabling this extension in the provider dashboard or via an elevated database role.

## Docs
- `docs/openapi.yaml`
- `bruno/Appointment API/`

## Production checklist
- `APP_DEBUG=false`
- real APP_KEY and secrets outside Git
- real SMTP provider
- Redis available
- queue worker managed by Supervisor/systemd/container platform
- scheduler configured
- HTTPS
- rotate fake webhook secret / replace fake gateway with a real adapter
- run migrations and PostgreSQL concurrency tests before traffic


## PostgreSQL and Redis verification (Day 3)

The existing feature suite was verified with PostgreSQL and Redis: 10 tests, 50 assertions. See [docs/DAY-03-POSTGRES-REDIS.md](docs/DAY-03-POSTGRES-REDIS.md) for setup, evidence and limits. Copy `.env.testing.example` to `.env.testing`, configure a disposable `appointment_api_test` database and isolated Redis, then run `php vendor/phpunit/phpunit/phpunit -c phpunit.postgres.xml`. The default phpunit.xml remains the portable SQLite configuration.
