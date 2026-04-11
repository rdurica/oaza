# Changelog

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
