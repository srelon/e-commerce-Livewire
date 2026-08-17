# E-commerce Livewire

**Live demo:** https://livewire-demo.duckdns.org

Book store. Laravel + Livewire admin, Vue 3 public site, Node.js WebSocket server for real-time stock/notification updates.

## Stack

| Layer          | Technology                                    |
|----------------|------------------------------------------------|
| Backend        | Laravel 13, PHP 8.3                             |
| Admin panel    | Livewire (Alpine.js + Flux UI + Tailwind CSS)   |
| Site frontend  | Vue 3 + TypeScript, Vite                        |
| Database       | MySQL 8.0                                       |
| Cache/Pub-Sub  | Redis 7                                         |
| WebSocket      | Node.js 20 (ws + ioredis)                       |
| Infrastructure | Docker Compose, Nginx, PHP-FPM                  |

## Requirements

- Docker + Docker Compose
- Make
- WSL2 (Ubuntu)

## Setup

```bash
cp .env.example .env && cp backend/.env.example backend/.env
docker compose up -d --build
docker exec -it bookstore_app sh -c 'cd /var/www/backend && php artisan db:seed'
```

## Commands

```bash
make up    # Start core services
make down  # Stop everything
make site  # Start Vue dev server → http://127.0.0.1:5173
make bash  # Enter the backend container shell
```

## Key URLs

| Service         | URL                          |
|-----------------|-------------------------------|
| API             | http://127.0.0.1:8880/api    |
| Admin           | http://127.0.0.1:8880/admin  |
| Site            | http://127.0.0.1:8880        |
| phpMyAdmin      | http://127.0.0.1:8080        |
| WebSocket       | ws://127.0.0.1:6001          |

**phpMyAdmin credentials:** `root` / `root`

## Real-time Architecture

PHP publishes → Redis subscribes → Node.js broadcasts → Browser. Infrastructure is wired up (`websocket/redis.js`, `websocket/server.js`); no channels are implemented yet — see `backend/docs/database.md` § "Planned business logic" for the spec (stock availability broadcast per product, user notifications).
