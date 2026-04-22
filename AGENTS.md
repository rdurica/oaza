# Repository Guidelines

This document provides essential information for AI agents and contributors working on the Oaza project.

## Project Overview

Oaza is a website for a salt cave in Hradec Kralove, Czech Republic. It's a Nette Framework PHP application with Docker-based development environment.

## Project Structure

```
src/
├── app/                    # Main application code
│   ├── Component/          # UI components (Forms, Grids)
│   ├── Dto/                # Data Transfer Objects
│   ├── Exception/          # Custom exceptions
│   ├── Facade/             # Facades
│   ├── Mapper/             # Data mappers
│   ├── Model/              # Business logic (Managers, Services)
│   ├── Modules/            # Admin module (Presenters, templates)
│   ├── Presenter/          # Public presenters
│   ├── Router/             # Routing configuration
│   ├── Translations/       # Language files
│   └── Util/               # Utility classes
├── migrations/             # Database migrations
├── tests/                  # Test files
├── composer.json           # PHP dependencies
├── phpcs.xml.dist          # PHP CodeSniffer configuration
├── phpstan.neon.dist       # PHPStan static analysis config
└── rector.php              # Rector automated upgrades config
```

## Build, Test, and Development Commands

All commands run via Docker using `make`:

| Command | Description |
|---------|-------------|
| `make init` | Initialize development environment (creates certs, starts containers) |
| `make rebuild` | Rebuild Docker images without cache |
| `make up` | Start containers in detached mode |
| `make down` | Stop containers |
| `make logs` | Show live container logs |
| `make php` | Open PHP container shell |
| `make node` | Open Node container shell |
| `make phpcs` | Run PHP CodeSniffer (PSR-12 style checks) |
| `make phpcbf` | Auto-fix coding style issues |
| `make phpstan` | Run static analysis (level 5) |
| `make rector-dry` | Run Rector in dry-run mode |
| `make qa` | Run all quality checks (phpcs + phpstan) |

## Coding Style

- **Standard**: PSR-12 (PHP CodeSniffer)
- **PHP Version**: Minimum 8.5
- **Line endings**: LF
- **Indentation**: 4 spaces
- **Strict types**: All PHP files must declare `declare(strict_types=1);`

### Naming Conventions

- Classes: PascalCase (e.g., `ReservationManager`, `EmailNormalizer`)
- Methods/Properties: camelCase (e.g., `getNextMondayDate`)
- Private properties: Prefixed with `_` (e.g., `$_config`)
- Constants: SCREAMING_SNAKE_CASE
- Files: Match class name (e.g., `EmailNormalizer.php`)

### Key Patterns

- DTOs use suffix `Data` or `Dto` (e.g., `CreateReservationData`, `CalendarEventData`)
- Form components follow pattern `Component/Form/{Name}/{Name}.php`
- Grid components follow pattern `Component/Grid/{Name}/{Name}.php`
- Services in `Model/Service/`, Managers in `Model/Manager/`

## Testing Guidelines

Tests are located in `src/tests/` (if present). Run quality checks instead:

```bash
make qa                    # Run all checks
make phpcs                 # Style only
make phpstan               # Static analysis only
```

## Commit Message Format

Follow conventional commits:

```
<type>: <description>

[optional body]
```

### Types

- `feat:` New feature
- `fix:` Bug fix
- `chore:` Maintenance tasks
- `refactor:` Code refactoring
- `docs:` Documentation changes

### Examples

```
feat: Implement EmailNormalizer utility
chore: prepare 1.1.3 (no release)
chore(release): 1.1.2
```

## Pull Request Guidelines

1. Keep PRs focused and reasonably sized
2. Include description of changes
3. Link related issues
4. Ensure `make qa` passes before requesting review
5. Do not commit directly to `main`

## Security Notes

- Never commit secrets, API keys, or credentials
- Environment variables for sensitive configuration
- TLS certificates generated locally via `mkcert`
