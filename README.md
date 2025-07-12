# Canalizador

A Laravel-based project for managing and processing YouTube video data, metrics, and recommendations.

## Features
- Fetch and process YouTube video data via the YouTube Data API
- Domain-driven design structure (Application, Domain, Infrastructure layers)
- Metrics and recommendations modules
- PSR-12 code style enforced with PHP CS Fixer
- Unit and feature tests with PHPUnit

## Requirements
- PHP 8.2+
- Composer
- Node.js & npm (for frontend assets)
- YouTube Data API key (for video features)

## Setup

1. **Clone the repository:**
   ```sh
   git clone <repo-url>
   cd canalizador
   ```

2. **Install PHP dependencies:**
   ```sh
   composer install
   ```

3. **Install Node.js dependencies:**
   ```sh
   npm install
   ```

4. **Copy and configure environment:**
   ```sh
   cp .env.example .env
   # Edit .env and set your database and YouTube API credentials
   ```

5. **Generate application key:**
   ```sh
   php artisan key:generate
   ```

6. **Run migrations:**
   ```sh
   php artisan migrate
   ```

7. **Run the development server:**
   ```sh
   php artisan serve
   ```

## Code Style

Format your code using PHP CS Fixer:
```sh
composer cs-fix
```

## Testing

Run all tests:
```sh
composer test
```

## Static Analysis

Run PHPStan for static analysis:
```sh
vendor/bin/phpstan analyse
```

## Continuous Integration (CI)

This project uses GitHub Actions for CI. On every push or pull request to the `main` branch:
- Composer dependencies are installed
- Database migrations are run
- PHPStan static analysis is executed
- PHP CS Fixer checks code style (dry-run)
- The test suite is executed

You can find the workflow in `.github/workflows/ci.yml`.

## Branch Protection

To protect your `main` branch:
1. Go to your repository on GitHub > Settings > Branches.
2. Add a branch protection rule for `main`.
3. Enable required status checks (like CI, code review, etc.).

This ensures all changes go through pull requests and pass CI before merging.

## Directory Structure
- `src/` - Domain, Application, and Infrastructure code
- `app/` - Laravel application code (controllers, models, providers)
- `routes/` - Route definitions
- `tests/` - Unit and feature tests

## License

MIT
