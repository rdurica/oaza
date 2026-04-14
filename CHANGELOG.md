# Changelog

## [1.1.3] - Unreleased

### Changed
- Removed GitHub Actions quality workflow from repository
- Cleaned up duplicate `secrets.prod.neon` ignore entry

## [1.1.2] - 2026-04-14

Maintenance release focused on CI/CD workflow cleanup.

### Changed
- Disabled automatic Docker image build on GitHub Release events
- Removed duplicate workflow definitions to keep `.github/workflows` as the single source of truth

## [1.1.1] - 2026-04-14

Bugfix release focused on homepage content consistency.

### Fixed
- Removed a redundant "Relaxační hudba" feature item from the homepage features list

## [1.1.0] - 2026-04-11

Introduces a complete password reset flow with security hardening and content clarity updates.

### Added
- Password reset flow via secure email links
- Dedicated password reset form rendered from tokenized links

### Changed
- Updated authentication mail flow to send reset links instead of temporary passwords
- Improved templates and localization strings for better clarity and accuracy across public pages

### Security
- Added rate limiting for password reset requests to reduce abuse risk

### Database
- Added `password_reset_token` table for token lifecycle management
- Added `password_reset_request_log` table for reset request logging and throttling

## [1.0.0] - 2026-03-28

First stable release — a complete rewrite of the original Oaza website (2017).

### Added
- Public reservation calendar with available capacity indicator (FullCalendar v6)
- Children presence tracking in reservations
- Session storage persistence for selected calendar date
- Auto-selection of the nearest upcoming Monday in the public calendar
- Cloudflare Turnstile CAPTCHA protection on forms
- Custom toast notification system with enhanced styling
- Reservation form with radio buttons and toggle switch
- Custom checkbox for terms of use acceptance in registration
- Admin dashboard with statistics (users, reservations, daily capacity)
- User management (enable/disable accounts, role assignment)
- News management (create, edit, visibility control, homepage feature toggle)
- Reservation management with cancellation and email notifications
- Restriction management (operational closures with time range and message)
- Email notifications for registration, reservation confirmation, cancellation, and password reset
- Password reset via email token
- Sentry integration for production error tracking
- `.htaccess` with URL rewriting and security rules
- Docker Compose environment with MariaDB for local development
- Makefile for common dev operations (`init`, `phpcs`, `phpstan`, `rector`)

### Tech Stack
- PHP 8.5
- Nette Framework 3.2
- Latte 3.0
- MariaDB
- FullCalendar v6
- Cloudflare Turnstile
- Sentry
- Docker / Docker Compose
