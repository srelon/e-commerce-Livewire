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

prod:
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.prod.yml up -d
	@echo ""
	@echo "  Site: https://$(SSL_DOMAIN)"
	@echo ""

down:
	$(DOCKER_COMPOSE) $(PROFILES) down --remove-orphans

bash:
	docker exec -it bookstore_app bash

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
	docker logs -f bookstore_scheduler

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
	docker exec -it bookstore_app bash -c "cd /var/www/backend && php artisan test"

format:
	docker exec -it bookstore_app bash -c "cd /var/www/backend && ./vendor/bin/pint"

fresh:
	docker exec -it bookstore_app bash -c "cd /var/www/backend && php artisan migrate:fresh --seed"

ws-secret:
	@NEW_SECRET=$$(openssl rand -hex 32); \
	sed -i.bak "s#^WS_TICKET_SECRET=.*#WS_TICKET_SECRET=$$NEW_SECRET#" .env; \
	sed -i.bak "s#^WS_TICKET_SECRET=.*#WS_TICKET_SECRET=$$NEW_SECRET#" backend/.env; \
	rm -f .env.bak backend/.env.bak; \
	export WS_TICKET_SECRET=$$NEW_SECRET; \
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.override.yml up -d websocket; \
	docker exec bookstore_app bash -c "cd /var/www/backend && php artisan config:clear" > /dev/null 2>&1 || true; \
	echo "WS_TICKET_SECRET rotated in .env and backend/.env, websocket container restarted."
