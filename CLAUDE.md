# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**This file covers only monorepo-wide conventions (Docker, environment, real-time, shared code style).** Service-specific conventions live in nested files, loaded automatically when working in that directory:
- `backend/CLAUDE.md` — Laravel API / shop-domain / caching conventions
- `backend/app/Livewire/CLAUDE.md` — the `/admin` Livewire panel (PowerGrid, Flux UI, Alpine gotchas)
- `frontend/CLAUDE.md` — Vue 3 site conventions

## Architecture

Monorepo with three independent services:

- `backend/` — Laravel 13 + Livewire (API + Admin panel at `/admin`)
- `frontend/` — Vue 3 + TypeScript + Vite (public site)
- `websocket/` — Node.js WebSocket server (ws + ioredis + pino)

All services run in Docker. The entire stack is mounted as volumes — no image rebuilds needed for code changes, only for dependency changes.

## Docker

```bash
# First run / after Dockerfile changes
docker compose up -d --build

# Normal start
make up

# Stop everything
make down

# Start Vue dev server (port 5173)
make site

# Drop and recreate the database, then reseed the demo catalog
make fresh

# Production: HTTPS via Caddy in front of nginx (needs SSL_DOMAIN set in .env)
make prod
```

**Container names:** `bookstore_app`, `bookstore_nginx`, `bookstore_db`, `bookstore_redis`, `bookstore_scheduler`, `bookstore_websocket`; `bookstore_caddy` in production only.

**Ports:** все на одном порту `8880` (`SITE_PORT` в `.env`) — API `/api`, Admin `/admin`, сайт `/`; Vue dev-сервер (`make site`) — `5173`, phpMyAdmin `8080`, WebSocket `6001`, MySQL `8101`

**Production HTTPS** is `docker-compose.prod.yml`, applied on top of the base file (`make prod`), not a change to `docker-compose.yml` itself — that file stays the plain-HTTP local-dev setup. It adds one `caddy` container (`_docker/caddy/Caddyfile`) that reverse-proxies `443`/`80` to the existing `nginx` service on `SITE_PORT`, obtaining and renewing its own Let's Encrypt certificate automatically — no certbot commands, no nginx template changes needed. Requires `SSL_DOMAIN` set in the root `.env` (a real resolvable domain, e.g. a DuckDNS one — Let's Encrypt's HTTP-01 challenge needs port 80 reachable from the internet) and ports 80/443 open in the host firewall / cloud security group. After first `make prod`, update `backend/.env`'s `APP_URL`/`FRONTEND_URL`/`SANCTUM_STATEFUL_DOMAINS`/`SESSION_DOMAIN` to the `https://` domain (see `backend/CLAUDE.md`'s `SESSION_DOMAIN` gotcha — it must match exactly what's typed in the browser, port included only if non-default). Also update `frontend/.env`'s `VITE_WS_URL` to `wss://<SSL_DOMAIN>/ws` (never a bare `ws://<host>:6001` — an HTTPS page can't open a plain insecure WS connection, and the raw port usually isn't reachable through the cloud firewall anyway) and rebuild (`entrypoint.sh` does this on container start). Caddy proxies that `/ws*` path straight to the `websocket` container over the same TLS connection as the rest of the site — see `_docker/caddy/Caddyfile`.

## Shell scripts and permissions

`_docker/app/entrypoint.sh` and `scheduler-entrypoint.sh` are called via `sh script.sh` in docker-compose.yml — **do not** change this to a direct path call, as files created on Windows lose the `+x` bit. The `sh` wrapper bypasses this.

## Environment

Root `.env` controls Docker (ports, MySQL credentials). Backend has its own `backend/.env` for Laravel (DB_HOST=`db`, REDIS_HOST=`redis`).

**A Makefile recipe that both rewrites a value in `.env`/`backend/.env` and then calls `docker compose` in the same recipe must re-`export` that variable in-shell right before the compose call.** The Makefile's own header (`include .env` / `export`) pre-exports the *old* value into every recipe's environment at `make` startup, and Docker Compose always prefers an already-set shell/process environment variable over reading `.env` from disk — even if the recipe just rewrote the file moments earlier. Skipping the re-export means the container silently ends up one full rotation behind the file (confirmed by direct reproduction, not theoretical) — see `make ws-secret`'s `export WS_TICKET_SECRET=$$NEW_SECRET` immediately before its `docker compose up -d websocket`.

## Real-time

Redis pub/sub connects backend to websocket server. PHP publishes to Redis → `websocket/server.js` subscribes and broadcasts to connected clients via ws.

**Private channels require a signed ticket, not a bare `user_id`.** Any channel named `{domain}.users.{public_id}` (e.g. `notifications.users.{public_id}`) is private — `websocket/channels/index.js::subscribe()` only relays it to a socket whose bound identity matches, rejecting (and logging) any subscribe attempt where it doesn't; public channels (`reviews.products.*`) are unaffected. A client can't just claim a `user_id` — the native browser `WebSocket` API has no way to send custom headers, so identity is proven with a short-lived signed ticket instead of trusting the connection request:
- Frontend calls `POST /api/broadcasting/auth` right before opening the socket (see `backend/CLAUDE.md` § Real-time for the signing details). The ticket is passed as `?ticket=` on the WS connection URL, since browsers can't set WS headers — `websocket/ticket.js::verifyTicket()` recomputes the HMAC (timing-safe compare) and checks `expires_at`, binding `ws.public_id` for the life of that connection. No ticket / invalid / expired → the socket stays anonymous (`ws.public_id = null`), which still works for public channels.
- `WS_TICKET_SECRET` must be the exact same value in `backend/.env` and in the `websocket` container's environment (`docker-compose.yml`'s `websocket.environment`, sourced from the root `.env`) — rotate both together with `make ws-secret`, never edit just one side. No one-time-use enforcement (Redis `jti` tracking) yet — TTL alone is the current replay-window mitigation; add it if a real need shows up.
- **Known gap:** `frontend/src/stores/websocket.ts::connect()` only fetches a fresh ticket when there's no already-open socket. If a guest already has an anonymous connection open and then logs in on the same page without a reload, the private `notifications.users.*` subscribe is sent over the *existing* anonymous socket and gets rejected server-side. Not fixed yet — would need `connect()` to actually reconnect on an auth-state transition, not just when there's no socket at all.
- **`connect()` only POSTs `/api/broadcasting/auth` when `useAuthStore().is_authenticated`** — a guest has no ticket to fetch, and always got a guaranteed 401 hitting that endpoint pre-login. Reconnect on `onclose` uses exponential backoff (`RECONNECT_BASE_MS` 3000ms, doubling to a `RECONNECT_MAX_MS` cap of 30000ms, reset to base on a successful `onopen`) rather than a fixed retry interval — matters when the WS endpoint is unreachable (bad `VITE_WS_URL`, firewall) so a stuck tab doesn't hammer the backend indefinitely.

## Code Style Rules — ALWAYS follow these

Applies across PHP/TS/Vue — the whole codebase, not just one service.

**NO alignment spaces.** Single space before `=`, `=>`, `:` — never pad to align columns. This applies to code only (PHP/TS/Vue) — in Markdown docs, alignment/padding for readability (e.g. lining up `|` columns in a table) is fine, since it isn't code.

```php
// WRONG
$output   = trim(...);
'title'   => $this->title,

// RIGHT
$output = trim(...);
'title' => $this->title,
```

```ts
// WRONG
const is_loading   = ref(true)
const page_title   = ref('')

// RIGHT
const is_loading = ref(true)
const page_title = ref('')
```

**NO objects/arrays on one line.** Every property on its own line, always — no exceptions, even for short objects.

```ts
// WRONG
{ name: 'name', model: null, placeholder: 'Name', type: 'text' },
{ key: 'id', text: 'ID' },

// RIGHT
{
    name: 'name',
    model: null,
    placeholder: 'Name',
    type: 'text',
},
{
    key: 'id',
    text: 'ID',
},
...(condition ? [{
    key: 'actions',
    text: '',
}] : []),
```

**Same rule for multi-argument function/method calls, not just object/array literals** — a call with 2+ arguments where any argument is non-trivial (an object/array literal, or a callback with a block body `{ ... }` rather than a single-expression arrow) goes one argument per line, closing paren on its own line. A short single-expression callback with no trailing options object (`watch(selected, (val) => emit('update:modelValue', val))`) can stay inline — nothing in it needs its own line.

```ts
// WRONG
watch(() => route.query, fetch_products, { immediate: true, deep: true })
watch(() => props.model_min, (v) => { if (v !== undefined && v !== price_min.value) price_min.value = v })

// RIGHT
watch(
    () => route.query,
    fetch_products,
    {
        immediate: true,
        deep: true,
    },
)
watch(
    () => props.model_min,
    (v) => {
        if (v !== undefined && v !== price_min.value) price_min.value = v
    },
)
```

**snake_case** for all variables, object keys, props, interface fields. camelCase/PascalCase only for functions, components, file names.

**English only** — all code comments, docblocks, and inline notes must be in English. This also applies to every documentation file in the repo (README, `docs/*.md`, CLAUDE.md itself) — nothing checked into the repo should be in a non-English language, regardless of what language is used to address Claude in conversation.

**No explanatory/rationale comments in code, even for non-obvious decisions.** Context about *why* something was built a certain way belongs in `backend/docs/database.md` / a service's `CLAUDE.md`, not in a `//` line next to the code — an inline comment almost always just duplicates what's already documented there. If a decision is worth flagging, say it in the chat reply so it can be routed to docs deliberately, not left as a stray comment.
