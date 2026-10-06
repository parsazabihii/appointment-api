# Validation performed before packaging

- PHP syntax lint: all project PHP files passed `php -l`.
- `composer.json`: parsed as valid JSON.
- `docs/openapi.yaml`: parsed as valid YAML.
- No old Portfolio ProjectInquiry code remains.
- Source archive intentionally excludes `vendor/`, `.env`, credentials and runtime caches.

## Runtime validation still required on your machine
This build environment does not have Composer/vendor dependencies, so Artisan route loading, migrations and PHPUnit were not executed here. After extracting:

```bash
composer install
php artisan migrate:fresh --seed
php artisan route:list
php artisan schedule:list
php artisan test
./vendor/bin/pint
```

For the strongest concurrency check, run the application/tests against PostgreSQL + Redis, not only SQLite test mode.
