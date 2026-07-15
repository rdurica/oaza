# ENV
DOCKER_COMP = docker compose
PHP      = $(PHP_CONT) php
PHP_CONT = $(DOCKER_COMP) exec --user=robbyte frankenphp
SRC_DIR = /app/src
RECTOR_PHAR = /tmp/rector.phar

## Initialize containers
init:
	rm -f src/.gitkeep
	docker network inspect apps >/dev/null 2>&1 || docker network create apps;
	@$(DOCKER_COMP) build --pull --no-cache;
	@$(DOCKER_COMP) up --detach;

## Docker
rebuild: ## Builds the Docker images (no cache)
	@$(DOCKER_COMP) build --pull --no-cache
	@$(DOCKER_COMP) up --detach

reload: ## Builds the Docker images
	@$(DOCKER_COMP) build
	@$(DOCKER_COMP) up --detach

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

php: ## Open shell in FrankenPHP container
	@$(PHP_CONT) bash

setup-githooks:
	git config core.hooksPath .githooks
	chmod +x .githooks/pre-commit

trust-cert: ## Trust Caddy's local CA certificate (Fedora/Debian)
	@echo "Extracting Caddy root CA certificate..."
	@$(DOCKER_COMP) exec frankenphp cat /data/caddy/pki/authorities/local/root.crt > /tmp/caddy-root.crt 2>/dev/null || (echo "Error: Failed to extract certificate. Is FrankenPHP running?" && exit 1)
	@if command -v update-ca-certificates >/dev/null 2>&1; then \
		echo "Detected Debian/Ubuntu-based system..."; \
		sudo cp /tmp/caddy-root.crt /usr/local/share/ca-certificates/caddy-root.crt; \
		sudo update-ca-certificates; \
	elif command -v update-ca-trust >/dev/null 2>&1; then \
		echo "Detected Fedora/RHEL-based system..."; \
		sudo cp /tmp/caddy-root.crt /etc/pki/ca-trust/source/anchors/caddy-root.crt; \
		sudo update-ca-trust extract; \
	else \
		echo "Error: Could not detect certificate management tool."; \
		echo "Please manually install /tmp/caddy-root.crt into your system trust store."; \
		exit 1; \
	fi
	@echo "Certificate trusted successfully. Restart Chrome: chrome://restart"
	@rm -f /tmp/caddy-root.crt

phpcs:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && ./vendor/bin/phpcs"

phpcbf:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && ./vendor/bin/phpcbf"

phpstan:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && XDEBUG_MODE=off ./vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --no-progress --memory-limit=512M"

phpunit:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && ./vendor/bin/phpunit --configuration phpunit.xml.dist"

rector-dry:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && if [ ! -f $(RECTOR_PHAR) ]; then curl -LsS https://github.com/rectorphp/rector/releases/latest/download/rector.phar -o $(RECTOR_PHAR); fi && XDEBUG_MODE=off php $(RECTOR_PHAR) process --config=rector.php --dry-run"

qa:
	@$(DOCKER_COMP) exec -T frankenphp sh -lc "cd $(SRC_DIR) && composer validate --no-check-publish && find app -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null && php -l www/index.php >/dev/null && ./vendor/bin/phpcs"
	@$(MAKE) phpstan
	@$(MAKE) phpunit
