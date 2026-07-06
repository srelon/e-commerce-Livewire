include .env
export

DOCKER_COMPOSE := $(shell docker compose version > /dev/null 2>&1 && echo "docker compose" || echo "docker-compose")

PROFILES = --profile site --profile phpmyadmin

up:
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.override.yml up -d
	@echo ""
	@echo "  Site:         http://127.0.0.1:$(SITE_PORT)"
	@echo "  Admin:        http://127.0.0.1:$(SITE_PORT)/admin/"
	@echo "  API:          http://127.0.0.1:$(SITE_PORT)/api/"
	@echo ""
	@echo "Waiting for app to start..."

down:
	$(DOCKER_COMPOSE) $(PROFILES) down --remove-orphans

bash:
	docker exec -it filament_app bash

logs:
	$(DOCKER_COMPOSE) logs -f

logs-nginx:
	$(DOCKER_COMPOSE) logs -f nginx

logs-app:
	$(DOCKER_COMPOSE) logs -f app

logs-db:
	$(DOCKER_COMPOSE) logs -f db

logs-websocket:
	$(DOCKER_COMPOSE) logs -f websocket

scheduler-logs:
	docker logs -f filament_scheduler

scheduler-restart:
	$(DOCKER_COMPOSE) restart scheduler

pma:
	$(DOCKER_COMPOSE) --profile phpmyadmin up -d
	@echo ""
	@echo "  phpMyAdmin:  http://127.0.0.1:8080"
	@echo ""

site:
	$(DOCKER_COMPOSE) --profile site up -d
	@echo ""
	@echo "  Site dev:  http://127.0.0.1:5173"
	@echo ""

test:
	docker exec -it filament_app bash -c "cd /var/www/backend && php artisan test"

format:
	docker exec -it filament_app bash -c "cd /var/www/backend && ./vendor/bin/pint"
