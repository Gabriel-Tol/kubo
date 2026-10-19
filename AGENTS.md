# Repository Guidelines

## Project Structure & Module Organization

This is a CodeIgniter 4 application. Application code lives in `app/`: controllers in `app/Controllers`, views in `app/Views`, and framework configuration in `app/Config`. The web entry point and public assets belong in `public/`; only this directory should be exposed by the web server. Tests are in `tests/`, with reusable test support under `tests/_support`. Runtime data such as logs, cache, sessions, and uploads belongs in `writable/`. Framework dependencies are managed in `vendor/` by Composer.

## Build, Test, and Development Commands

- `composer install` installs the PHP dependencies declared by the lockfile.
- `composer test` runs the PHPUnit suite using `phpunit.xml.dist`.
- `vendor/bin/phpunit` runs PHPUnit directly; append a test path to run a subset, for example `vendor/bin/phpunit tests/unit`.
- `php spark serve` starts the local CodeIgniter development server.

Copy `env` to `.env` and configure `app.baseURL` and any database settings for local development. Keep secrets and machine-specific settings out of commits.

## Coding Style & Naming Conventions

Use PHP 8.1 or newer and follow the existing four-space indentation and PSR-4 namespaces (`App\` maps to `app/`). Keep class names in PascalCase and filenames aligned with class names, such as `Home.php`. Use descriptive method and variable names. No formatter or linter is configured in `composer.json`, so match the surrounding code style.

## Testing Guidelines

Add tests under `tests/` and name test classes with the `Test` suffix (for example, `HealthTest`). Test methods must begin with `test` and describe the behavior, such as `testBaseUrlHasBeenSet()`. Database tests may require a running test database configured in `app/Config/Database.php` or `.env`.

## Commit & Pull Request Guidelines

Git history is unavailable in this checkout, so no repository-specific commit format could be confirmed. Use a short, imperative commit subject that describes the change. Pull requests should explain the purpose and notable changes, link any related issue, and include relevant screenshots for visual changes. Mention the tests or manual checks performed.
