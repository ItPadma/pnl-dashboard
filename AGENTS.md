# Repository Guidelines for Agentic Tools

This document tells coding agents how to work in this repo. It is optimized for
quick onboarding, safe edits, and consistent output.

## Project overview
- Laravel 12 app for pajak (tax) workflows.
- Core modules: PNL dashboards, reguler/non-reguler pajak, master data imports,
  user management, access-group menu control.
- UI: Blade in `resources/views`, Vite assets in `resources/js` and
  `resources/css`.

## Build, lint, and test commands

### Install & bootstrap
- `composer install`
- `npm install`
- `cp .env.example .env` then `php artisan key:generate`
- `php artisan migrate` (or `php artisan migrate --graceful` on fresh setup)

### Dev server
- `composer run dev` (runs Laravel server, queue listener, and Vite together)
- `php artisan serve` (server only)
- `npm run dev` (Vite only)

### Build assets
- `npm run build`

### PHP formatting
- `./vendor/bin/pint`

### Tests (Pest/PHPUnit)
- `composer test`
- `php artisan test`

### Run a single test
- `php artisan test --filter <TestName>`
- `php artisan test tests/Feature/SomeTest.php`
- `php artisan test --testsuite=Feature`

### Notes
- `phpunit.xml` sets sqlite in-memory DB and other testing env values.
- Windows helpers: `start-services.bat`, `start-queue-work.bat`,
  `start-reverb.bat`.

## Code style guidelines

### General formatting
- `.editorconfig` is authoritative: 4 spaces, LF, trim trailing whitespace.
- Use PSR-12 for PHP; run Pint when formatting is needed.
- Keep files ASCII unless the file already uses non-ASCII and needs it.

### Imports
- Prefer fully-qualified `use` statements at top of file.
- Group imports by vendor (App, Illuminate, third-party). Keep them sorted.
- Avoid unused imports; remove when refactoring.

### Naming conventions
- Classes: `StudlyCase`, interfaces and traits follow Laravel conventions.
- Methods and variables: `camelCase`.
- Routes: typically `pnl.<scope>.<feature>.<action>`.
- Route slugs must match `menu.access:<slug>` middleware naming.
- DB fields follow existing schema naming; avoid renaming without migrations.

### Types and strictness
- Use type hints where available (Request types, return types for new methods).
- Use Laravel collections and helpers consistently with existing code.
- Use `@var` only when types are not obvious to static tools.

### Error handling
- Follow existing pattern: try/catch with JSON error responses.
- Use `response()->json([...], <status>)` for API endpoints.
- Prefer `422` for validation errors, `404` for missing resources, `500` for
  unexpected errors.
- Log errors with `Log::error()` when catching unexpected exceptions.

### Controllers and services
- Keep controllers thin when possible; heavy logic should move to services.
- Reuse `AccessControlService` for access-group/menu logic.
- Use `AuthnCheck` and `menu.access` middleware consistently with routes.

### Validation
- Use `$request->validate([...])` or `Validator::make(...)` for APIs.
- Ensure validation errors are returned cleanly and consistently.

### Database access
- Use Eloquent for model-based work; query builder for read-heavy tables.
- Be careful with `->count()` usage on builder (it executes queries).
- For long-running jobs, prefer queues (`php artisan queue:listen`).

### Frontend assets
- Vite config is in `vite.config.js` and uses `laravel-vite-plugin`.
- Tailwind v4 is in use via `@tailwindcss/vite`.
- Keep JS modules ES module style (package.json `type: module`).

## Testing guidelines
- Tests live in `tests/Feature` and `tests/Unit`.
- Pest is configured in `tests/Pest.php`.
- Feature tests extend `Tests\TestCase` via `pest()->extend(...)`.
- Add tests for new controllers, access rules, and import/export logic.

## Repo-specific behaviors
- Menu access is enforced with `menu.access:<slug>` middleware.
- Access groups and menu hierarchy live under `app/Services` and `app/Models`.
- Imports/exports are under `app/Imports` and `app/Exports`.

## Security and secrets
- Secrets live in `.env`; never commit credentials.
- Ensure `storage/` and `bootstrap/cache/` are writable.
- Use Laravel's built-in helpers for encryption and hashing.

## Cursor/Copilot rules
- No `.cursor/rules/`, `.cursorrules`, or
  `.github/copilot-instructions.md` files found in this repo.

## Commit & PR notes (if needed)
- History uses mixed styles (`feat:`, `Refactor:`, short messages).
- Prefer concise, imperative subjects with a prefix.
- Mention new migrations, env vars, or background jobs in PRs.
