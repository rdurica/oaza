# ![](https://oazahb.cz/images/logo.png) Oaza

[![PHP](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-powered-blue.svg)](https://www.docker.com/)
[![Composer](https://img.shields.io/badge/Composer-latest-green.svg)](https://getcomposer.org/)

Oaza is a website for a salt cave in Hradec Kralove, Czech Republic: https://oazahb.cz.

The project started in 2017 as a commercial PHP website and is now being refactored to improve code quality, maintainability, and long-term development workflow.

## Overview

The website provides practical information about salt therapy and Oaza services, including:

- service details and pricing
- photo gallery
- contact options
- online booking flow

The goal is to keep the content clear for visitors while maintaining a modern and maintainable codebase for development.

## Features

- Information pages about salt cave services
- Gallery content for visitors
- Contact form
- Booking workflow
- Responsive frontend for desktop and mobile devices

## Screenshots

<a href="https://github.com/user-attachments/assets/ec887f84-6487-4619-ba13-a545dceb188a">
  <img src="https://github.com/user-attachments/assets/ec887f84-6487-4619-ba13-a545dceb188a" alt="Oaza desktop homepage" width="100%" />
</a>

<a href="https://github.com/user-attachments/assets/1d245095-0e91-478b-9196-49a259beea05">
  <img src="https://github.com/user-attachments/assets/1d245095-0e91-478b-9196-49a259beea05" alt="Oaza mobile view" width="320" />
</a>

Click any screenshot to open the full-size image.

## Local Development

Run the initial setup after cloning the repository:

```bash
make init
```

This command:

- creates a local self-signed TLS certificate in `build/dev/certs`
- installs PHP dependencies into `src/vendor` if missing
- starts a local MariaDB service and imports `src/migrations/001_initial.sql` on first bootstrap
- creates the shared Docker network `apps` if it does not exist yet
- builds the development image and starts the local stack

Host requirements:

- Docker with `docker compose`
- OpenSSL

## Local QA

Run quality checks through Docker:

```bash
make phpcs
make phpstan
make rector-dry
make qa
```

## Security Note

For security reasons, this repository was recreated without historical commits. This avoids carrying over potentially sensitive data or vulnerabilities from the original project history.

## License

This project is licensed under the MIT License. You may use, modify, and distribute it for commercial and non-commercial purposes.
