# Repository Guidelines

## Project Overview
This is a Laravel 12 application for pajak (tax) workflows. Core areas include PNL dashboards, reguler/non-reguler pajak keluaran/masukan, master data imports, user management, and access-group based menu control.

## Project Structure & Module Organization
- `app/Http/Controllers/PNL/` handles dashboards and pajak flows (reguler/non-reguler).
- `app/Http/Controllers/Admin/` and `app/Services/AccessControlService.php` manage access groups and menu permissions.
- `app/Imports/` and `app/Exports/` handle Excel/CSV ingestion and reporting.
- `app/Models/` contains domain models like `PajakKeluaranDetail`, `PajakMasukanCoretax`, and access/menu models.
- `resources/views/` for Blade UI, `resources/js/` and `resources/css/` for Vite assets.
- `routes/web.php` defines most endpoints with `AuthnCheck` and `menu.access` middleware.

## Build, Test, and Development Commands
- `composer install` and `npm install` bootstrap PHP and frontend dependencies.
- `composer run dev` starts Laravel server, queue listener, and Vite together.
- `php artisan serve` runs the web server only; `npm run dev` runs Vite only.
- `npm run build` builds production assets into `public/build`.
- `php artisan migrate` applies schema changes.
- `composer test` or `php artisan test` runs the Pest/PHPUnit suite.
- Windows helpers: `start-services.bat`, `start-queue-work.bat`, `start-reverb.bat`.

## Coding Style & Naming Conventions
- `.editorconfig` enforces 4-space indentation; keep LF line endings.
- PHP follows PSR-12 and Laravel naming (`StudlyCase` classes, `camelCase` methods).
- Use `./vendor/bin/pint` for PHP formatting when needed.
- Route names typically follow `pnl.<scope>.<feature>.<action>`; keep slugs aligned with `menu.access:<slug>`.

## Testing Guidelines
- Tests live in `tests/Feature` and `tests/Unit`; files should end with `Test.php`.
- Pest is configured via `tests/Pest.php`; Feature tests extend `Tests\TestCase`.
- Add coverage for new controllers, access rules, and import/export logic.

## Commit & Pull Request Guidelines
- History is mixed (`feat:`, `Refactor:`, short messages). Prefer concise, imperative subjects with a prefix.
- PRs should include a description, linked issue (if any), and UI screenshots when views change.
- Call out new migrations, env vars, or background jobs in the PR.

## Configuration & Security Tips
- Use `.env` for secrets; do not commit API keys (Sentry, Pusher/Reverb, OCR).
- Ensure `storage/` and `bootstrap/cache/` are writable locally.
