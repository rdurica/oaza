# ENV
DOCKER_COMP = docker compose
PHP      = $(PHP_CONT) php
PHP_CONT = $(DOCKER_COMP) exec php-fpm
CERT_DIR  = build/dev/certs
CERT_KEY  = $(CERT_DIR)/tls.key
CERT_CRT  = $(CERT_DIR)/tls.crt
CERT_SUBJ = /C=CZ/ST=Praha/L=Praha/O=LocalDev/CN=localhost
DB_SERVICE = mariadb

## Initialize containers
init:
	@set -e; \
	mkdir -p $(CERT_DIR); \
	if [ ! -f $(CERT_CRT) ] || [ ! -f $(CERT_KEY) ]; then \
		openssl req -x509 -newkey rsa:4096 -keyout $(CERT_KEY) -out $(CERT_CRT) -days 3650 -nodes \
			-subj "$(CERT_SUBJ)"; \
	fi; \
	docker network inspect apps >/dev/null 2>&1 || docker network create apps >/dev/null; \
	$(DOCKER_COMP) build --pull --no-cache; \
	if [ ! -f src/vendor/autoload.php ]; then \
		$(DOCKER_COMP) run --rm --no-deps php-fpm composer install; \
	fi; \
	$(DOCKER_COMP) up --detach $(DB_SERVICE); \
	until $(DOCKER_COMP) exec -T $(DB_SERVICE) sh -lc 'mariadb-admin ping -uroot -p"$$MARIADB_ROOT_PASSWORD" --silent' >/dev/null 2>&1; do \
		sleep 2; \
	done; \
	if ! $(DOCKER_COMP) exec -T $(DB_SERVICE) sh -lc 'mariadb -uroot -p"$$MARIADB_ROOT_PASSWORD" "$$MARIADB_DATABASE" -Nse "SHOW TABLES LIKE '\''news'\''" | grep -q "^news$$"'; then \
		$(DOCKER_COMP) exec -T $(DB_SERVICE) sh -lc 'mariadb -uroot -p"$$MARIADB_ROOT_PASSWORD" "$$MARIADB_DATABASE"' < src/migrations/001_initial.sql; \
	fi; \
	$(DOCKER_COMP) up --detach php-fpm

## Docker
rebuild: ## Builds the Docker images
	@$(DOCKER_COMP) build

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh:
	@$(PHP_CONT) bash

## Utils
cert:
	@set -e; \
	mkdir -p $(CERT_DIR); \
	openssl req -x509 -newkey rsa:4096 -keyout $(CERT_KEY) -out $(CERT_CRT) -days 3650 -nodes \
		-subj "$(CERT_SUBJ)"

## Manifest for k8s
TEMPLATE = build/prod/manifest-template.yaml
OUTPUT_DIR = .
.PHONY: init rebuild up down logs sh cert manifest clean

manifest:
	@echo "Generating manifest for $(app_name) ..."
	cp $(TEMPLATE) $(OUTPUT_DIR)/manifest.yaml && \
	sed -i "s/{{APP_NAME}}/$(app_name)/g" $(OUTPUT_DIR)/manifest.yaml && \
	sed -i "s|{{APP_SECRET}}|$(shell openssl rand -base64 32)|g" $(OUTPUT_DIR)/manifest.yaml
