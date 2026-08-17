# CLAUDE.md (backend)

Backend-specific conventions for `backend/` (Laravel 13 + Livewire admin panel + API). See the root `CLAUDE.md` for Docker/monorepo-wide rules, `app/Livewire/CLAUDE.md` for the admin panel (Livewire/PowerGrid/Flux/Alpine), and `frontend/CLAUDE.md` for the Vue app.

## Running commands in the backend container

The container WORKDIR is `/var/www` — always `cd /var/www/backend` first.

```bash
docker exec -it bookstore_app bash -c "cd /var/www/backend && php artisan migrate"
docker exec -it bookstore_app bash -c "cd /var/www/backend && composer require vendor/package"
docker exec -it bookstore_app bash
cd /var/www/backend
php artisan migrate
php artisan make:model ModelName -mfs
php artisan tinker
```

**No dev watcher for the backend's own Vite assets** (`resources/css/app.css`, used by the Livewire admin panel) — unlike the Vue frontend, there's no `npm run dev` service and no `public/hot` marker. `@vite(...)` falls back to the last `npm run build` output in `public/build/`, which silently goes stale across Tailwind/Blade edits. After changing a Tailwind class or admin Blade markup, rebuild before checking in a browser: `docker exec bookstore_app bash -c "cd /var/www/backend && npm run build"`. Check `public/build/manifest.json`'s timestamp before assuming a CSS/layout bug is real — a missing rebuild looks identical to "the change had no effect."

## Admin panel (Livewire)

**Moved to `app/Livewire/CLAUDE.md`** — everything about the `/admin` Livewire panel (PowerGrid tables, Flux UI forms, schema-driven forms, Alpine/Livewire gotchas, RBAC, modal-form components) lives there now, loaded when you're working under `app/Livewire/` or `resources/views/livewire/`. Public API / shop-domain conventions stay in this file.

## Shop domain (backend database)

Full table/column/status reference: **`backend/docs/database.md`** — read it before writing migrations, models, or admin pages for these tables. Migrations: `backend/database/migrations/2026_07_02_*`. Models: `backend/app/Models/*`.

Facts that aren't obvious from the schema alone:

- **Manual ordering columns are always `sort_order`**, never `sort`/`order`/`position` — one name across the whole schema, including `menus` (remapped from the tree package's `order` config key).
- **Price/stock live in `product_stocks`, not `products`.** A product can have several stock rows (batches at different prices); `status` tracks which batch is active/queued/finished. `order_items.product_stock_id` pins an order line to the exact batch sold from.
- **`orders.status` and `order_items.status` are independent** — one order can have some items delivered and others cancelled. Admin flow (not built yet): set `order_items.status` first, then `orders.status`, which back-fills any untouched items.
- **`reviews`/`review_likes`/`review_reports`/`user_notifications` are ported 1:1 from another existing project** (`comments`/`comment_likes`/`comment_reports`/`user_notifications`, `comment_id → review_id`). Don't redesign — the moderation/notification logic behind it already works there.
- **`reviews` is polymorphic (`type`/`record_id`), not product-specific** — `Product::reviews()`/`NewsPost::reviews()` are both `morphMany(Review::class, 'reviewable', 'type', 'record_id')`, sharing the same morph map as `seo_meta`. Reuse this table for a new content type, don't add a `product_id`-only reviews table.
- **`orders.public_id`/`.txid` are auto-generated** in `Order::booted()` (`static::creating`) — never set manually.
- **Status convention:** across every status enum, `4` means cancelled/deleted, never `2`/`3`. Keep new statuses consistent.
- **Guest support differs by table.** Cart, checkout, orders all support guests (`user_id` nullable). `reviews` does not — requires authentication, no guest name/email fields.
- **`User::notifications()` is taken by Laravel's `Notifiable` trait** — the shop notifications relation is `User::userNotifications()`.
- **Several shop tables are prefixed `products_` for disambiguation** — `products_categories` (model `ProductsCategory`, vs. `news_categories`), `products_authors` (model `ProductsAuthor`), `products_favorites`. Don't reintroduce bare `categories`/`authors`/`favorites`.
- **Image columns (`icon`, `image`, `photo`) are JSON, not a path string** — cast to `array`, storing size/format variants. Applies to `products_categories`, `products_authors`, `product_images`, `news_posts`.
- **SEO fields live in one shared `seo_meta` table**, not duplicated per content table — `Product::seo()`, `ProductsCategory::seo()`, `NewsPost::seo()` are `morphOne(SeoMeta::class, 'seo', 'type', 'record_id')`, with the morph map (`AppServiceProvider::boot()`) aliasing `type` to the target's table name instead of its class name.
- **Customer auth is Sanctum SPA (cookie-based)**, guard `web` / provider `users` — separate from the `admins` guard used by the admin panel. `SANCTUM_STATEFUL_DOMAINS` must stay bare `host:port` (no scheme); `config/cors.php` derives its own scheme-prefixed origins from that same variable.
- **`backend/.env`'s `SESSION_DOMAIN=127.0.0.1` means the site must always be accessed via `127.0.0.1:8880`, never `localhost:8880`** — a cookie's `Domain` attribute is matched literally, so a session cookie scoped to `127.0.0.1` is silently dropped by the browser on any request to `localhost`, even though both resolve to the same machine. Symptom if this is missed: login appears to succeed (the POST itself returns 200) but the very next request bounces back to `/admin/login` as if unauthenticated — no error, no exception, nothing in `storage/logs`, since nothing server-side is actually wrong. Applies to manual browser testing, curl with cookies, and Playwright alike.
- **`php artisan migrate:fresh --seed` gives a working demo catalog** — every shop table has a seeder in `database/seeders/`, sourced from the frontend's hardcoded mock data, images copied into `storage/app/public/{products,products_categories,news}/`. See `backend/docs/database.md` § Seed data for placeholder exceptions (stock quantity, delivery branch hash).

## Backend API conventions — ALWAYS follow these

**Keep `backend/docs/database.md` current.** Any migration, model, or DB change (new table/column, changed status meaning, dropped field) must update that doc in the same change — it's part of the change, not a follow-up. Never let it drift.

**A new/changed column goes directly in the table's own `create_*_table` migration, never a separate `Schema::table` + backfill migration.** There's no real production data here — `migrate:fresh --seed` is the only supported reset path. Edit the original migration in place, verify with `migrate:fresh --seed`. Note `database/seeders/DatabaseSeeder.php` uses `WithoutModelEvents`, which silences `creating`/`saving` hooks during `db:seed` — a seeder row depending on a model-event-generated column (e.g. a `HasNumericPublicId`-style trait) must set that column explicitly.

**Flag near-duplicate methods proactively, ask before merging.** When a near-duplicate is already visible in context (a sibling method just read/written, differing only in 1-2 params), point it out and ask whether to merge rather than waiting to be asked. Don't run a dedicated codebase-wide dedup sweep as its own task — real overhead, risks false positives on methods that look similar today but are conceptually independent. `backend/docs/database.md` is a good cheap cross-reference point.

**Tests ship with the API code that needs them.** Adding/changing an endpoint means creating/updating its Pest feature test in the same change. If a request/response contract changes, update the existing test alongside it.

**Tests are class-based, not Pest's functional `it()`/`test()` style** — `class XTest extends TestCase { use RefreshDatabase; public function test_snake_case_description(): void { ... } }`, matching `ExampleTest.php` and the user's other project (`srelon/demo-news/backend/tests`). Factor repeated setup into a reusable trait under `tests/Helpers/` (e.g. `TestDataHelper::createCategory()/createProduct()/createMenuItem()/createContact()`) instead of duplicating `Model::create([...])` inline.
- **The same applies when an entire test body is identical bar a couple of parameters** — e.g. `test_X_includes_seo_for_the_X_page()` across `HomeTest`/`AboutTest`/`ContactTest`/`ProductFilterTest`/`AuthorFilterTest`/`NewsFilterTest` calls one shared `TestDataHelper::assertPageSeoIncluded(...)`; the 8 `test_X_cache_is_invalidated_on_Y_write()` tests merged into `TestDataHelper::assertCacheInvalidatedOnWrite(...)`. Each class still keeps its own one-line test method for discoverability.
- **Not every look-alike test is worth merging** — `test_X_are_sorted_by_date_added()` in `ProductFilterTest`/`AuthorFilterTest`/`NewsFilterTest` was deliberately left alone: each sorts by a different date column and checks a different number of variants, so a shared helper would abstract over real differences.

**A test switching `actingAs($userA)` → real HTTP call → `actingAs($userB)` → real HTTP call, in one test method, needs `$this->app['auth']->forgetGuards();` between the two `actingAs()` calls** — otherwise the second call's `$request->user()` silently keeps resolving to `$userA` (the guard is memoized across simulated requests in one test). Only relevant when a test asserts "user B was notified/affected by user A's action."

**Validation lives in Form Requests, never in controllers.** One request class per resource, shared between create and update — read the route's id param inside `rules()` to adjust (e.g. `Rule::unique(...)->ignore($id)` for edit vs. plain `unique` for create).

```php
class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('products', 'slug')->ignore($id)],
        ];
    }
}
```

**No business logic in controllers.** A controller method only resolves a Request, calls a Service (`app/Services/`) or another dedicated layer, and returns the response.

**Before creating a new one-off Service class, check whether an existing service already owns that domain and just add a method.** `AuthService::issueBroadcastTicket()` was originally scaffolded as a standalone `BroadcastAuthService` — corrected into `AuthService` since ticket issuance is an auth concern with no independent state. Same for controllers: `BroadcastAuthController` lives under `app/Http/Controllers/Auth/`, grouped by domain rather than defaulting to a new top-level file per endpoint.

**Response array shape belongs in `app/Http/Resources/`, not inline in a service method.** Every model→array transformation is a standard `JsonResource` — see `ReviewResource`/`ProductResource`/`ProductsAuthorResource`/`NewsResource`/`MenuResource`/`UserResource`/`UserNotificationResource`/`PageResource`/`PerkResource`/`FaqResource`/`TeamMemberResource`/`BestAuthorResource`, covering every page-bundle sub-model too. The service method keeps its existing name/signature (`ReviewService::formatReview(Review $review, ?User $viewer): array`, since callers expect a plain `array`) but its body becomes `return (new ReviewResource($review, $viewer))->resolve();`.
- **One resource class per model/domain, not one per shape variant.** `ProductResource(Product $product, bool $detailed = false, bool $includeStock = true)` covers both the card and full-page shapes (folding in what used to be a standalone `ProductStockResource` as a protected `formatStock()` method); `NewsResource(NewsPost $post, bool $detailed = false)` likewise. Detail-only fields use `$this->when($this->detailed, fn () => ...)` so the key is omitted (not `null`) on the list shape. Don't re-split a resource into per-shape files for a new variant — add another constructor flag.
- **A Resource needing context beyond the model takes it as an extra constructor param** — `ReviewResource(Review $review, ?User $viewer)` (drives `can_edit`/`can_delete`/`my_reaction`), always calling `parent::__construct($resource)` first. Recursive shapes build nested arrays via `(new self($child, ...))->resolve($request)`.
- **Access the wrapped model as `$this->resource->relationName`, not the magic `$this->relationName`, when you need a null-safe chain** (`$this->resource->seo?->seo_title`) — `JsonResource`'s `__get` proxies plain attributes fine, but `?->` through a nullable relation needs the explicit `$this->resource`.
- **A Resource's `toArray()` should read as plain attribute mapping — real computation belongs on the model, not inlined.** `Review::canEditBy(User $user): bool`, `Review::reactionOf(?User $user): ?string`, `Review::likesCount()`/`dislikesCount()` all live on the model; `ReviewResource` just calls them. One model method shared between a real authorization check and a display flag, instead of duplicated logic in each.
- **Paginated results call `->through(fn ($model) => (new XResource($model))->resolve())` inside the service**, never `XResource::collection($paginator)` — the latter wraps in an `AnonymousResourceCollection`, breaking `RespondTrait::respondWithJson()`'s `LengthAwarePaginator` detection that builds the `{data, pagination}` envelope.

**Group routes by resource, don't declare flat separate `Route::` calls.** One `Route::prefix('resource')->controller(Controller::class)->group(function () { ... })` block per resource area:

```php
Route::prefix('news/{category}')->controller(NewsController::class)->group(function () {
    Route::get('/', 'category');
    Route::get('/{subcategory}', 'subcategoryNews');
    Route::get('/{subcategory}/articles', 'subcategoryArticles');
    Route::get('/{subcategory}/{slug}', 'article');
});
```

**Response shape comes from `App\Traits\RespondTrait`** (already `use`d in the base `Controller`) — never build the JSON envelope by hand:
- `$this->respondWithJson($content, $status = 200)` → `{data, status}`
- `$this->respondWithError($message, $code = 400)` → `{status, errors}`
- `$this->paginationMeta($paginated)` → `{current_page, last_page, total, prev_page_url, next_page_url}` (last two are `'prev'`/`'next'`/`null` flags, not real URLs)

**All static pages (Home, Contact, About, Cart, future ones) are grouped behind a single `StaticController`**, same convention as `Products`/`Authors`/`News` — a dedicated method per page needing extra composed data (`home()` → `HomeService::getHome()`, `contact()` → `ContactService::getContact()`, `about()` → `AboutService::getAbout()`, each folding `'page' => $this->pageService->getPage('<slug>')` into its response), plus one catch-all `show(string $slug)` registered **last** (Laravel matches in registration order) returning the bare `getPage()` result for anything else (currently just `cart`). A new page with no extra data needs nothing — `show()` covers it; one needing composed data gets one more Service method + one more explicit route/controller method here, not a new controller file. `PageService::getPage(string $slug)` resolves the bundle (`{title, content, excerpt, image, seo}`) via a `pages` table row for pages with no natural content record — plugging into the same `Model::seo()` relation as `Product`/`NewsPost`, no type-sniffing branch.

## Caching

`CACHE_STORE=redis` (`predis` client) is already configured — TTLs/tags for cached "page bundle" endpoints and the tag lists each write triggers flushes both live in `config/cacheable.php` (`areas` / `flush_triggers`), not in `app/Services/CacheService.php`. `CacheService` is just two generic methods reading that config: `CacheService::remember(string $area, string $key, Closure $callback)` and `CacheService::flush(string $trigger)` — see `LayoutService::getLayout()` / `HomeService::getHome()`. **Adding a new cached endpoint or write source means adding an entry to `config/cacheable.php`, never touching `CacheService.php` itself.**

**Cache invalidation goes on the model, via `App\Models\Concerns\FlushesCacheOnWrite`**, not a manual controller/service call — admin writes go through plain Livewire components with no service layer in front, so a model-level hook is the one guaranteed place regardless of what triggered the write. `bootFlushesCacheOnWrite()` wires `static::saved()`/`static::deleted()` to `CacheService::flush(static::$cacheFlushTrigger)` — a model just adds `use FlushesCacheOnWrite;` and `protected static string $cacheFlushTrigger = 'x';`. See `backend/docs/database.md` § Caching for which models flush which tag.

**Never cache a raw `Collection` or Eloquent model — always `->toArray()` before returning from the cached closure.** `config/cache.php` has Laravel 13's `'serializable_classes' => false` default (a real anti-object-injection control) — Redis's `unserialize()` silently returns `__PHP_Incomplete_Class` for any object read back, so a cached `Collection` looks fine on the first (freshly-computed, never round-tripped) request and breaks on every request after. Fix by not serializing objects into the cache — don't loosen `serializable_classes` to work around this.

**Gotcha: `FlushesCacheOnWrite`'s hooks never fire for a mass update/delete via the query builder** (`Model::whereKey(...)->update([...])`, or the same via a relation) — those Eloquent events only fire for an individual instance's own `->save()`/`->delete()`, not a query that never hydrates a model (a `SoftDeletes` bulk `->delete()` compiles to one bulk `UPDATE`, same story). Hit this with the product gallery's drag-reorder and per-image delete (documented in `app/Livewire/CLAUDE.md`) — the DB updated correctly but the cached Home bestsellers card image stayed stale until an unrelated write happened to flush the same tag. Fixed by an explicit `CacheService::flush('product')` call right alongside those two bulk-write methods, in addition to the model-level hook. **Same gap exists in `Categories\Form::saveOrder()`/`deleteCategory()` — not fixed yet, flagged but out of scope.** Any future bulk `update()`/`delete()` against a `FlushesCacheOnWrite` model needs the same manual flush.

**Redis is already wired up — reach for it proactively on new endpoints, don't wait to be told.** Any new read endpoint returning general, non-personalized data (aggregates, reference/listing data, same response for every visitor) should get the same `CacheService` treatment as `layout`/`home` as part of building it. Endpoints that are inherently per-user or highly parameterized (cart, orders, filtered/paginated search) are the exception — caching those needs a deliberate key strategy, so it's fine to skip and say so.

## Real-time (backend side)

See root `CLAUDE.md` § Real-time for the full ticket flow. Backend piece: `POST /api/broadcasting/auth` (`auth:sanctum`, `App\Http\Controllers\Auth\BroadcastAuthController`) → `AuthService::issueBroadcastTicket(User $user)` builds `base64({public_id, issued_at, expires_at})` + `.` + `hash_hmac('sha256', payload, WS_TICKET_SECRET)` (`config/websocket.php`, TTL 30s via `WS_TICKET_TTL`). `WS_TICKET_SECRET` must match the `websocket` container's environment exactly — rotate both together with `make ws-secret`, never edit just one side.
