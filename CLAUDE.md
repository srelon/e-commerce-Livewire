# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture

Monorepo with three independent services:

- `backend/` — Laravel 13 + FilamentPHP (API + Admin panel at `/admin`)
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
```

**Container names:** `filament_app`, `filament_nginx`, `filament_db`, `filament_redis`, `filament_scheduler`, `filament_websocket`

**Ports:** все на одном порту `8880` (`SITE_PORT` в `.env`) — API `/api`, Admin `/admin`, сайт `/`; Vue dev-сервер (`make site`) — `5173`, phpMyAdmin `8080`, WebSocket `6001`, MySQL `8101`

## Running commands in the backend container

The container WORKDIR is `/var/www` — always `cd /var/www/backend` first.

```bash
# One-off commands
docker exec -it filament_app bash -c "cd /var/www/backend && php artisan migrate"
docker exec -it filament_app bash -c "cd /var/www/backend && composer require vendor/package"

# Interactive shell
docker exec -it filament_app bash
cd /var/www/backend

# Common artisan commands
php artisan migrate
php artisan make:filament-resource ModelName --generate
php artisan make:model ModelName -mfs
php artisan tinker
```

## Shell scripts and permissions

`_docker/app/entrypoint.sh` and `scheduler-entrypoint.sh` are called via `sh script.sh` in docker-compose.yml — **do not** change this to a direct path call, as files created on Windows lose the `+x` bit. The `sh` wrapper bypasses this.

## Filament

After installing (`composer require filament/filament` inside the container):
```bash
php artisan filament:install --panels   # creates AdminPanelProvider
php artisan make:filament-user          # creates first admin user
php artisan make:filament-resource ModelName --generate
```

Filament files live in `backend/app/Filament/` (Resources, Pages, Widgets).

**Every "New X" button defaults to the `info` color, not Filament's stock `primary`** — set once, panel-wide, via `CreateAction::configureUsing(fn (CreateAction $action) => $action->color('info'))` in `AdminPanelProvider::boot()`. This applies automatically to every resource's create button (and any custom page's `CreateAction`, e.g. `MenuTree`) — don't set `->color('info')` per-resource/page, that's already redundant.

### RBAC

Every resource extends `BaseResource` (sets `$accessKey`) and every edit page extends `BaseEditRecord`. No separate View pages — the Edit page is used for both viewing and editing.

```
app/Filament/Resources/
    BaseResource.php      ← abstract, hasAccess() helper, all can* methods
    BaseEditRecord.php    ← abstract, hides Save + shows danger toast on unauthorized save
    Users/
        UserResource.php  ← extends BaseResource, $accessKey = 'users'
        Pages/
            ListUsers.php   ← CreateAction with ->visible(canCreate())
            CreateUser.php
            EditUser.php    ← extends BaseEditRecord, authorizeAccess() checks .view
```

- `canViewAny/canView` → requires `{key}.view`
- `canCreate/canEdit` → requires `{key}.edit`
- Form `->disabled(!static::hasAccess('edit'))` makes fields read-only for view-only users
- Never use `abort(403)` inside `beforeSave()` — use `Notification::make()->danger()->send()` + `$this->halt()` to show a toast instead of an error modal
- New access keys (any new `$accessKey`) must be added to `database/seeders/AccessesSeeder.php` in the same change, or the permission can never be assigned to a role
- Standalone `Filament\Pages\Page` subclasses (not a `Resource`, e.g. a page from a third-party package) can't extend `BaseResource` — replicate its `hasAccess(string $type)` helper locally instead, and gate visibility via `canAccess()` (the `Page`-level equivalent of `canViewAny`)

### Tree / nested UI (menus, and any future drag-and-drop hierarchy)

Use `solution-forest/filament-tree` — don't hand-roll drag-and-drop nesting. Column names are remapped in `config/filament-tree.php` (`order → sort_order`, `title → name`; `parent` stays `parent_id`) to match this project's naming conventions.

**`parent_id` must be `integer default(-1)`, not a nullable FK** — the package uses `-1` as its root sentinel (`ModelTree::defaultParentKey()`), and `-1` never has to match a real row so there's no FK constraint on the column. This is a deliberate deviation from the nullable-self-FK pattern used elsewhere; don't nullable-FK-ify it to match convention, it breaks the package's `isRoot()`/`scopeIsRoot()`/cascading-delete logic.

Model: `use SolutionForest\FilamentTree\Concern\ModelTree;`. Page: generate with `php artisan make:filament-tree-page {Name} --model={Model}`, then wire RBAC (see above) and `getFormSchema()` manually — the generated stub ships empty. See `Menu` / `App\Filament\Pages\MenuTree` for the reference implementation.

**Custom panel-wide CSS** (e.g. the drag-and-drop nesting highlight) goes in a small Blade partial under `resources/views/filament/`, wired via `->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => view('filament.xxx'))` in `AdminPanelProvider` — don't edit anything under `vendor/`, it gets wiped on `composer update`/`vendor:publish --force`. See `resources/views/filament/menu-tree-styles.blade.php` (targets the tree package's `.dd-*` classes from `solution-forest/filament-tree`, which ships dbushell's Nestable JS — nesting happens by dragging an item right past a horizontal threshold, not by hovering over a target).

**Restructuring a package's own Blade markup** (not just adding CSS/JS around it — e.g. moving the tree's Save button to the page bottom, adding a Cancel button, relabeling "Save" → "Save changes") uses Laravel's standard package-view-override mechanism instead of a render hook: drop a same-named file at `resources/views/vendor/{package-view-namespace}/{path}.blade.php` — Laravel's view finder checks there before falling back to the package's own view. `solution-forest/filament-tree` registers its views under the `filament-tree::` namespace, so `filament-tree::components.tree.index` is overridden at `resources/views/vendor/filament-tree/components/tree/index.blade.php` (copy of the vendor original with the Save button moved below `.filament-tree.dd` into a `.menu-tree-bottom-actions` bar alongside a Cancel button using `x-on:click="$wire.$refresh()"` — Livewire 3's own "re-render from server state" magic method, discards any unsaved client-side drag state without any custom JS). Run `php artisan view:clear` after adding/editing an override, since Blade caches compiled views and won't otherwise notice a new override file exists.

For a Filament `Action`'s color (e.g. making "New menu" green), use the action's own `->color('success')` (Filament's semantic palette) rather than CSS — see `MenuTree::getCreateAction()`.

**Menu tree requires a manual Save click** — a client-side JS "auto-save on drop" was attempted and abandoned (2026-07-02) after five increasingly-careful implementations all failed in the real browser (wrong event, then debounce firing mid-drag, then firing on unrelated clicks, then a listener-accumulation bug plus a MutationObserver/mouseup race causing duplicate requests) despite the last version passing a 6-scenario jsdom test harness. **Don't re-attempt this without the user explicitly asking again** — see [[feedback-filament-tree]] for the full history of what was tried and why each attempt failed, so a future try doesn't repeat the same dead ends.

**Dynamic route params on the menu form** — when a field's options depend on another field picked in the same form (here: which model to search depends on the selected `route`), use a `Select::make('json_column.key')->searchable()->getSearchResultsUsing(fn (string $search, $get) => ...)->getOptionLabelUsing(fn ($value, $get) => ...)`, with the driving field marked `->live()`. This queries through the panel's existing Livewire round-trip (capped with `->limit(10)`, filtered by `$search`) — don't build a bespoke API endpoint + JS autocomplete for internal-admin-only lookups like this, `searchable()` already does it.

## Shop domain (backend database)

Full table/column/status reference: **`backend/docs/database.md`** — read it before writing migrations, models, or Filament resources for these tables. Migrations: `backend/database/migrations/2026_07_02_*`. Models: `backend/app/Models/*`.

Facts that aren't obvious from the schema alone:

- **Manual ordering columns are always named `sort_order`**, never `sort`/`order`/`position` — one name across the whole schema, including `menus` (remapped from the tree package's `order` config key).
- **Price/stock live in `product_stocks`, not `products`.** A product can have several stock rows (batches at different prices); `status` tracks which batch is active/queued/finished. `order_items.product_stock_id` pins an order line to the exact batch it was sold from.
- **`orders.status` and `order_items.status` are independent** — one order can have some items delivered and others cancelled. Admin flow (not built yet): set `order_items.status` first, then `orders.status`, which back-fills any untouched items.
- **`reviews`/`review_likes`/`review_reports`/`user_notifications` are ported 1:1 from another existing project** (its `comments`/`comment_likes`/`comment_reports`/`user_notifications` tables), with `comment_id → review_id`. Don't redesign this structure — the moderation/notification logic behind it already works there and will be ported later.
- **`reviews` is polymorphic (`type`/`record_id`), not product-specific** — `Product::reviews()` and `NewsPost::reviews()` are both `morphMany(Review::class, 'reviewable', 'type', 'record_id')`, sharing the same morph map as `seo_meta`. Don't add a `product_id`-only reviews table for a new content type — reuse this one.
- **`orders.public_id` and `orders.txid` are auto-generated** in `Order::booted()` (`static::creating`) — never set them manually.
- **Status convention:** across every status enum in this domain, `4` means cancelled/deleted, never `2`/`3`. Keep new statuses consistent with this.
- **Guest support differs by table.** Cart, checkout, orders all support guests (`user_id` nullable). `reviews` does **not** — reviews require authentication, no guest name/email fields.
- **`User::notifications()` is taken by Laravel's `Notifiable` trait** — the shop notifications relation is `User::userNotifications()`.
- **Several shop tables are prefixed `products_` for disambiguation, not their bare name** — `products_categories` (model `ProductsCategory`, vs. `news_categories`), `products_authors` (model `ProductsAuthor`), `products_favorites` (model `ProductsFavorite`). Don't reintroduce `categories`/`authors`/`favorites` as table or class names.
- **Image columns (`icon`, `image`, `photo`) are JSON, not a path string** — cast to `array`, storing size/format variants. Applies to `products_categories`, `products_authors`, `product_images`, `news_posts`.
- **SEO fields live in one shared `seo_meta` table**, not duplicated per content table — `Product::seo()`, `ProductsCategory::seo()`, `NewsPost::seo()` are `morphOne(SeoMeta::class, 'seo', 'type', 'record_id')`, with the morph map (`AppServiceProvider::boot()`) aliasing `type` to the target's table name instead of its class name.
- **Customer auth is Sanctum SPA (cookie-based)**, guard `web` / provider `users` — separate from the `admins` guard used by the Filament panel. `SANCTUM_STATEFUL_DOMAINS` in `.env` must stay bare `host:port` (no scheme); `config/cors.php` derives its own scheme-prefixed origins from that same variable.
- **`php artisan migrate:fresh --seed` gives a working demo catalog** — every shop table has a seeder in `database/seeders/`, sourced from the frontend's hardcoded mock data (not invented), with the referenced images copied into `storage/app/public/{products,products_categories,news}/`. See `backend/docs/database.md` § Seed data for the two placeholder exceptions (stock quantity, delivery branch hash).

## Backend API conventions — ALWAYS follow these

**Keep `backend/docs/database.md` current.** Any migration, model, or other change to the database (new table, new column, changed status meaning, dropped field) must update that doc in the same change — it's part of the change, not a follow-up task. Never let it drift out of sync with the actual schema.

**A new/changed column goes directly in the table's own `create_*_table` migration, never a separate `Schema::table` + data-backfill migration.** There's no real production data to migrate around here — `migrate:fresh --seed` (see § Setup above) is the only supported reset path, everything comes from seeders. Edit the original `create_*_table` migration in place instead of bolting on an `add_column_to_x_table` migration with a `do { ... } while (exists)`-style backfill loop, then verify with `migrate:fresh --seed`. Note `database/seeders/DatabaseSeeder.php` uses `WithoutModelEvents`, which silences every model's `creating`/`saving` hooks during `db:seed` — a seeder creating a row that depends on a model-event-generated column (e.g. a `HasNumericPublicId`-style trait) must set that column explicitly itself rather than relying on the hook.

**Flag near-duplicate methods proactively, ask before merging.** When reading or writing a method and a near-duplicate is already visible in context (a sibling method just read/written, differing only in 1-2 parameters — model class, order-by columns, a status-scoping flag, etc.), point it out and ask whether to merge rather than waiting to be asked. Don't run a dedicated codebase-wide sweep hunting for duplication as its own task — that's real overhead and risks false positives (two methods that look similar today but are conceptually independent and likely to diverge), and cuts against the "don't add abstractions beyond what the task requires" rule below. `backend/docs/database.md` is a good cheap cross-reference point for this, since it's meant to stay current and complete for the whole query/schema domain.

**Tests ship with the API code that needs them.** When adding or changing an API endpoint, create or update its Pest feature test in the same change — don't leave that for a later pass. If a request/response contract changes, update the existing test alongside it so it never goes stale.

**Tests are class-based, not Pest's functional `it()`/`test()` style** — `class XTest extends TestCase { use RefreshDatabase; public function test_snake_case_description(): void { ... } }`, matching `ExampleTest.php` and the user's other project (`srelon/demo-news/backend/tests`). Factor repeated test-data setup into a reusable trait under `tests/Helpers/` (e.g. `TestDataHelper::createCategory()/createProduct()/createMenuItem()/createContact()`) instead of duplicating `Model::create([...])` inline in every test method. **The same applies when an entire test body (not just its setup) is identical across test classes bar a couple of parameters** — e.g. every listing/page endpoint has its own `test_X_includes_seo_for_the_X_page()` across `HomeTest`/`AboutTest`/`ContactTest`/`ProductFilterTest`/`AuthorFilterTest`/`NewsFilterTest`, which only differed by endpoint/slug/title, so they call one shared `TestDataHelper::assertPageSeoIncluded(string $endpoint, string $slug, string $title, ?string $seo_title = null)` instead of each repeating the full create-page/create-seo-meta/assert body — each class still keeps its own one-line test method (for discoverability/reporting in the test run), only the duplicated body moves into the trait. Same reasoning again for the 8 `test_X_cache_is_invalidated_on_Y_write()` tests across `LayoutTest`/`HomeTest`/`ContactTest`/`AboutTest` (create a model → assert a JSON path → update the model → assert the path again) — merged into `TestDataHelper::assertCacheInvalidatedOnWrite(string $endpoint, Model $model, string $json_path, string $before, array $update, string $after)`. **Not every look-alike test is worth merging** — `test_X_are_sorted_by_date_added()` in `ProductFilterTest`/`AuthorFilterTest`/`NewsFilterTest` looks similar but was deliberately left alone: each sorts by a different date column (`published_at` vs `created_at`) and checks a different number of variants (2 vs 3 requests), so forcing a shared helper would abstract over real differences rather than genuine duplication.

**A test switching `actingAs($userA)` → real HTTP call → `actingAs($userB)` → real HTTP call, within the same test method, needs `$this->app['auth']->forgetGuards();` between the two `actingAs()` calls** — otherwise the second call's `$request->user()` silently keeps resolving to `$userA` (the session/sanctum guard is memoized across the two simulated requests in one test, and `actingAs()` alone doesn't reset it). Only comes up when a test asserts "user B was notified/affected by user A's action" (e.g. `NotificationTest.php`); tests that reuse the *same* user across multiple calls in one method (the common case) never hit this.

**Validation lives in Form Requests, never in controllers.** One request class per resource, shared between create and update instead of two near-duplicate classes — read the route's id param inside `rules()` to adjust rules that differ between the two (e.g. `Rule::unique(...)->ignore($id)` for edit vs. plain `unique` for create).

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

**No business logic in controllers.** A controller method only resolves a Request, calls a Service (`app/Services/`) or another dedicated layer (e.g. an Action), and returns the response — nothing else. All business logic belongs in the service layer.

**Response array shape belongs in `app/Http/Resources/`, not inline in a service method.** Every model→array transformation (what CLAUDE.md used to call a service's "format" method — `formatReview()`, `formatProduct()`, `formatUser()`, etc.) is a standard Laravel `JsonResource` (`class XResource extends JsonResource { public function toArray($request): array { ... } }`) — see `ReviewResource`/`ProductResource`/`ProductsAuthorResource`/`NewsResource`/`MenuResource`/`UserResource`/`UserNotificationResource`/`PageResource`/`PerkResource`/`FaqResource`/`TeamMemberResource`/`BestAuthorResource` — this covers every page-bundle sub-model too (`PageService::getPage()`, `AboutService`'s perks, `ContactService`'s FAQs, `TeamService`'s members, `ProductService::getBestAuthor()`), not just the "main" list/detail resources — don't let a small bundle-only formatter hide back in a service method just because it's a minor part of a larger response. The service method keeps its existing name/signature (`ReviewService::formatReview(Review $review, ?User $viewer): array` etc., since callers all over the codebase — `->through()`, `->map()`, `array_merge()` for websocket payloads — expect a plain `array` back, not a Resource object) but its body becomes a one-liner: `return (new ReviewResource($review, $viewer))->resolve();`. This keeps the *orchestration* (which shape to use, extra params like a paginator's per-item `$includeStock` flag) in the service, while the *shape definition* itself lives in one inspectable file per shape instead of buried in a service method body.
- **One resource *class* per model/domain, not one per shape variant — a list-vs-detail (or any other) shape difference is a constructor flag inside the same file, not a second `XDetailResource`/`XStockResource` file.** `ProductResource(Product $product, bool $detailed = false, bool $includeStock = true)` covers both the product-card list shape and the full product-page shape (and folds in what used to be a standalone `ProductStockResource`, now a protected `formatStock()` method on the same class); `NewsResource(NewsPost $post, bool $detailed = false)` covers both the news-card and full-article shapes. Extra-only-on-detail fields use `$this->when($this->detailed, fn () => ...)` so the key is omitted entirely (not `null`) on the list shape — this was a deliberate consolidation (2026-07-05) after separate `ProductDetailResource`/`ProductStockResource`/`NewsPostDetailResource` files made the "which file has the field I need" jump annoying; don't re-split a resource back into per-shape files just because a new variant shows up — add another constructor flag instead.
- **A Resource needing context beyond the model itself takes it as an extra constructor param**, not a second method argument — `ReviewResource(Review $review, ?User $viewer)` (drives `can_edit`/`can_delete`/`my_reaction`) — always call `parent::__construct($resource)` first. Recursive shapes (`ReviewResource`'s `replies`, `MenuResource`'s `children`) build the nested array via `(new self($child, ...))->resolve($request)`, not a second inline array shape.
- **Access the wrapped model as `$this->resource->relationName` for a relation, not the magic `$this->relationName`** when the relation might be null/not loaded and you need a *null-safe* chain (`$this->resource->seo?->seo_title`) — `JsonResource`'s `__get` proxies straight to the underlying model for plain attributes (`$this->slug`, `$this->rating_avg` work fine via magic), but chaining through a nullable relation needs the explicit `$this->resource` to get PHP's `?->` to apply to the right object.
- **A Resource's `toArray()` should read as plain attribute mapping — any actual computation (interpreting a raw column value, searching a relation for a match) belongs on the model as a method, not inlined in the Resource.** `Review::canEditBy(User $user): bool`, `Review::reactionOf(?User $user): ?string` (interprets `ReviewLike.opp_type` into `'like'`/`'dislike'`/`null` for a given viewer), `Review::likesCount()`/`dislikesCount()` all live on the model; `ReviewResource` just calls `$this->resource->reactionOf($this->viewer)` etc. — no `match`/`firstWhere` inside the Resource itself. This was a deliberate correction (2026-07-05): an earlier pass had the `opp_type`-matching `match` block and the `likes->firstWhere(...)` lookup written directly in `ReviewResource::toArray()`, which read as business logic living in the wrong layer. The same rule is why `canEditBy()` is shared between `ReviewController::update()`'s actual 403 check and `ReviewResource`'s `can_edit` display flag instead of being duplicated in each — one model method, called from both a real authorization decision and a display flag.
- **Paginated results still call `->through(fn ($model) => (new XResource($model))->resolve())` inside the service**, never `XResource::collection($paginator)` — the latter wraps the paginator in an `AnonymousResourceCollection`, which breaks `RespondTrait::respondWithJson()`'s `$content['items'] instanceof LengthAwarePaginator` detection that builds the `{data, pagination}` envelope.

**Group routes by resource, don't declare flat separate `Route::` calls.** One `Route::prefix('resource')->controller(Controller::class)->group(function () { ... })` block per resource area, e.g.:

```php
Route::prefix('news/{category}')->controller(NewsController::class)->group(function () {
    Route::get('/', 'category');
    Route::get('/{subcategory}', 'subcategoryNews');
    Route::get('/{subcategory}/articles', 'subcategoryArticles');
    Route::get('/{subcategory}/{slug}', 'article');
});
```

**Response shape comes from `App\Traits\RespondTrait`** (already `use`d in the base `Controller`) — never build the JSON envelope by hand in a controller:
- `$this->respondWithJson($content, $status = 200)` → `{data, status}`
- `$this->respondWithError($message, $code = 400)` → `{status, errors}`
- `$this->paginationMeta($paginated)` → `{current_page, last_page, total, prev_page_url, next_page_url}` (the last two are `'prev'`/`'next'`/`null` flags, not real URLs)

## Frontend (Vue 3 — `frontend/`)

### Structure

```
src/
  assets/scss/        ← global styles only
    _variables.scss   ← design tokens ($color-primary, $color-accent, $color-dark, $color-danger, etc.)
    _mixins.scss      ← shared mixins (form-field-label, form-field-base, form-field-error-text)
    _reset.scss       ← Google Fonts import, base reset
    _helpers.scss     ← .container, .section, .section__title, .dots
    main.scss         ← @forward variables, @use reset/helpers
  components/
    layout/           ← AppHeader, AppFooter, Layout
    ui/base/          ← BaseButton, BaseInput, BaseSelect, BaseRadioGroup, BaseTabs, BaseSlider, SortSelect
    ui/shop/          ← all page-section components (PageHero, ProductCard, etc.)
    ui/cart/          ← CheckoutStep, CartPopup (shared across cart/checkout views)
    ui/forms/         ← standalone form components (ContactForm, NewsletterForm) — vee-validate + yup, reusable across pages
  composables/        ← useCheckoutForm (localStorage-backed cart form state), useWizardStep (step draft/confirm pattern)
  views/Pages/        ← Home.vue (fetches its own page-scoped data once, passes it down to sections as props — see "Pinia store" rule below; otherwise assembles components only, no UI logic)
  views/Pages/Cart/   ← CartPage.vue (checkout wizard) + ContactStep/DeliveryStep/PaymentStep.vue
  stores/shop.ts      ← Pinia: cart_count
  stores/layout.ts    ← Pinia: categories/menu/contacts from GET /api/layout, fetched once in App.vue's onMounted
  types/shop.ts       ← plain TS interfaces for shop-domain display shapes (ProductSummary/AuthorSummary/BlogPostSummary) — not a store, see below
  types/global.ts     ← plain TS interfaces for content shapes that aren't shop-domain (TeamMember, Perk, FaqItem — the kind of thing backing static/about-us-style pages)
  routes/router.ts    ← Vue Router history mode
public/images/        ← static images served at /images/*.png
public/favicon.svg    ← square book-logo icon, matches AppHeader logo colors
```

### SCSS rules

- Vite `additionalData` auto-injects `@use "@/assets/scss/variables" as *;` and `@use "@/assets/scss/mixins" as *;` into every component
- **Never redeclare `$color-*` variables inside component `<style>`** — they come from global injection
- Form-field components (`BaseInput`, `BaseSelect`) share label/field/error styling via `@include form-field-label`, `@include form-field-base`, `@include form-field-error-text` from `_mixins.scss` — don't re-declare padding/border/focus styles per component
- `_reset.scss` and `_helpers.scss` each start with `@use 'variables' as *;` to access variables; `main.scss` pulls them together with `@forward 'variables'; @use 'reset'; @use 'helpers';`
- All component styles: `<style lang="scss" scoped>` with BEM naming

### Component rules

- All reusable components in `src/components/ui/` — never tie components to a specific page
- `ui/base/` — generic (BaseButton, BaseInput, BaseTabs, BaseSlider)
- `ui/shop/` — shop-specific but still reusable (ProductCard, ProductSlider, PageHero, etc.)
- `ui/forms/` — standalone forms usable from more than one place (ContactForm, NewsletterForm) — full vee-validate/yup form + submit logic, not just a field
- Views (`views/`) only assemble components — no inline styles, no UI logic
- Static images referenced in JS data → `public/images/` (Vite can't resolve dynamic `src/assets` paths)
- **Never hand-roll a plain `<button>` (or a `<router-link>` styled to look like one) that acts as a site action button (CTA, form submit, "load more", etc.) — always use `BaseButton`.** If an existing variant (`primary`/`outline`/`text`/`dark`/`primary-outline`) doesn't fit, extend `BaseButton` itself (a new variant, a new prop) rather than duplicating its look in a one-off class — a variant can override geometry (radius/padding/font-size), not just color, if that's what the look needs (see `dark`, the pill-shaped newsletter-signup button). A hand-rolled duplicate is invisible until a design change means re-finding every copy across the project by hand — real instances found and fixed: `ProductCard.vue`'s "View Cart" state (re-implemented `primary` via `.book-card__cart--in`), `ReviewsPanel.vue`'s "Load More Reviews" (hand-written hover/disabled CSS), `NewsletterForm.vue`'s pill-shaped dark submit button (→ new `dark` variant), `Sidebar.vue`'s and `AboutPage.vue`'s newsletter blocks (were dead `<form @submit.prevent>` stubs with no handler at all — replaced with `<NewsletterForm />`, the same component `HeroSection.vue` already used, instead of writing a third copy of the subscribe logic), `AboutCta.vue`'s "Explore Collection" `router-link` (wrap `BaseButton` inside the link, don't restyle the link itself — see `ProductPage.vue`'s `product__view-link` for the original precedent), and `CartPopup.vue`'s Checkout/Continue-Shopping pair (→ new `primary-outline` variant for the primary-bordered pill look, geometry applied via an external `.cart-popup__btn` class same as the `ProductCard` case). Buttons that are legitimately not a BaseButton (icon-only controls, tab/accordion/menu toggles, filter chips, pagination page-number buttons) are the exception — this rule is about buttons that already are, in effect, a CTA.
- `BaseButton`'s `loading?: boolean` prop (default `false`) shows a spinner in place of the slot content — pass the component's own loading ref (`:loading="is_submitting"`) instead of manually toggling the slot text (`{{ is_loading ? 'Saving...' : 'Save' }}`).

### BaseSlider

Single slider component used by PageHero and ProductSlider. Never duplicate slider logic.

```ts
// Props
count: number          // total slides/pages
dot_style: 'rect' | 'diamond' | 'circle'
auto_play_ms: number   // 0 = disabled; PageHero uses 30000
model_value?: number   // optional v-model for parent to track active index

// Slot
#default="{ active }"  // scoped slot, active = current index
```

Handles: drag/swipe (threshold 30px), auto-play, dot clicks, v-model sync.

### BaseTabs

Generic tab nav + content component — shared by `ProductPage.vue` (Description/Reviews) and `AuthModal.vue` (Sign In/Sign Up). Never re-implement a tab-button row by hand (same reasoning as the `BaseButton` rule above).

```ts
// Props
tabs: string[]        // tab keys, in display order
modelValue: string     // v-model — the active tab key (standard modelValue/update:modelValue, not model_value)

// Slots
#label="{ tab }"   // optional — customize a tab button's displayed text (defaults to the raw key); used by ProductPage for the dynamic "Reviews (N)" label
#default           // tab content — the parent's own v-if/v-else against its modelValue decides what renders, same as it would without BaseTabs
```

Page-specific framing (`AuthModal`'s no border-top and `ProductPage.vue`'s `border-top` separator + spacing before Related Products) stays external, applied via a `class` prop on the `<BaseTabs>` instance itself — `BaseTabs` only owns the tab-button-row look (centered, active-tab underline) and the content wrapper, not surrounding page layout. If a caller needs a real v-model over something other than a plain ref (e.g. `AuthModal`'s tab lives in `useAuthStore()`, changed via `open_modal()`, not a plain assignment), bind through a writable `computed({ get, set })` rather than adding an escape hatch to `BaseTabs` itself.

### Key design decisions

**Book store (current project):**
- **HeroSection** — `hero__top` (grid 1fr 1fr: H1 left, desc right) + `hero__bottom` (grid 2fr 1fr 1fr, min-height 500px); the 3 `hero__card`s are 3 random categories from `useLayoutStore().categories` (reshuffled each page load via a `computed`, not re-shuffled on every re-render), each linking to `{ name: 'products', query: { category } }`, image = category's `image` field via `to_storage_url()`; newsletter block in mid column uses `NewsletterForm.vue` (`ui/forms/`), not inline markup
- **AppHeader** — book SVG logo + "BookStore" text; Categories mega-menu is a separate element LEFT of Home nav link, controlled by `cats_open` ref + mouseenter/mouseleave on wrapper. Nav links, mega-menu categories, and the phone contact are all driven by `useLayoutStore()`, not hardcoded — hovering a mega-menu category swaps `header__mega-promo`'s image to that category's `image` (`hovered_category` ref, falls back to the first category); top-nav items with `children` get a `header__nav-dropdown` flyout (same mouseenter/mouseleave pattern as the categories button)
- **CategoryStrip** — CSS carousel (transform translateX), shows 8 items, auto-advances every 10s; reset without jump uses double `requestAnimationFrame` to skip transition for one frame; categories come from `useLayoutStore().categories` — `total`/`max_index`/`track_width`/`item_width` are `computed`, not plain values derived once, since the category list starts empty and populates async after `fetch_layout()` resolves
- **ProductCard** — extracted reusable component; hover slides action icons in from right (`translateX(60px) → 0`); `aspect-ratio: 2/3` on figure
- **BestAuthorSection** — award badges are 72px circles with `border: 2px solid $color-primary` and text inside — NO SVG icons. Takes the `products_authors` row (highest `SUM(bestseller)` across its products, see `backend/docs/database.md` § API response format) as an `author` prop from `Home.vue` — falls back to the static `/images/best-author-1.webp` when `author.photo` is null, which it is for every seeded author right now (no real author photos exist yet)
- **BestsellersSection** / **BestRatedSection** / **BlogSection** — title and description configurable via props with defaults; product/post data comes in as a `products`/`posts` prop from `Home.vue`, which fetches `GET /api/home` once in its own `onMounted` (bundles all 4 Home sections in one call, same shape as `useLayoutStore()`'s single `layout` fetch, just page-scoped state instead of a store — see the "Pinia store" rule above) and distributes slices down. These 4 sections stay purely presentational — no store, no fetch of their own.
- **`ui/about/` (`TeamSection`/`AboutPerks`/`AboutCta`)** — same split as Home's sections: `AboutPage.vue` fetches `GET /api/pages/about` once and passes `team`/`perks` + `loading` down to `TeamSection`/`AboutPerks` as props; they stay presentational, no store/fetch of their own. `AboutCta` takes no props at all (fully static copy + a `NewsletterForm`) but is still its own component rather than inline markup in `AboutPage.vue`, matching the same "one section = one component" convention regardless of whether it happens to need data.

**SVG fill in scoped styles** — CSS `fill` on `<svg>` does not reliably cascade to `<path>` in Vue scoped CSS. Always target child elements directly:
```scss
&__icon {
    path, circle { fill: $color-primary; }
}
```

**Card overlays** — always use `position: absolute; inset: 0` on the overlay, never `position: relative; height: 100%` — the latter collapses when the parent gets its height from flex.

**Pinia stores use the Composition API form** (`defineStore('name', () => { ... return {...} })` with `ref`/`computed`), matching `stores/shop.ts` — not the Options API form (`defineStore('name', { state, actions })`). Keep this even when porting a pattern from another project that used the Options form (e.g. `stores/layout.ts`'s `fetch_layout()` mirrors an old project's `fetchLayout()` action, but rewritten Composition-style).

**A Pinia store is only for data genuinely global across pages** (menu/categories/contacts in `stores/layout.ts`, cart count in `stores/shop.ts`, later: logged-in user) — data that many unrelated components need without a prop chain, and that shouldn't be re-fetched every time a component mounts. **Data scoped to a single page is not a store, even if several sibling components need it** — fetch it once in the page's `views/` component (an `onMounted` + `ref`s there is orchestration, not the "UI logic" the views-stay-pure-assemblers rule is about — see [[project-shop-backend]] for the concrete Home-page correction) and pass it down to each section via props. Shared TS interfaces for these display shapes go in a domain-named file under `types/` (e.g. `types/shop.ts` — not `types/home.ts`, since `ProductSummary`/`AuthorSummary`/`BlogPostSummary` aren't Home-specific and other pages listing products/authors/posts will want the same shapes) — not re-declared per component, but also not smuggled into a store just to have somewhere to export them from. **This applies just as much to content shapes that aren't shop-domain at all** (`TeamMember`, `Perk`, `FaqItem` — About/Contact page content) — those go in `types/global.ts`, not inline in the `.vue` file that happens to use them first and not re-exported from a component (`FaqSection.vue` imports `FaqItem` from `types/global`, it doesn't declare/export its own copy). A component's own `Props` interface is the one exception that stays local — this rule is about reusable *content* shapes, not a component's own prop contract. Example: `views/Pages/Home.vue` fetches `GET /api/home` once and passes `bestsellers`/`best_author`/`best_rated`/`blog` (typed via `types/shop.ts`) down to `BestsellersSection`/`BestAuthorSection`/`BestRatedSection`/`BlogSection` as props — those 4 sections stay presentational (no store, no fetch of their own). `bestsellers`/`best_rated` are kept as separate top-level refs in `Home.vue` (not one combined `home_data` blob) so that a future websocket handler patching a single product's live price/stock (planned, not built yet) can find and mutate that product by `slug` in place, in whichever array(s) contain it, without restructuring.

**Products page (`views/Pages/Products/ProductList.vue`)** — `filter_groups` come entirely from the backend (`GET /api/products`'s `filter_groups`, typed as `FilterGroup`/`FilterGroupItem` in `types/shop.ts`). `ProductList.vue` owns *all* `route`/`router` access for the page — it derives `selected` (per-group checkbox/rating state) and `price_min`/`price_max` as plain `computed()`s off `route.query` + `filter_groups` (no watcher needed for derivation, `computed` already re-runs on change) and passes everything down as props. `ProductSidebar.vue` is fully presentational: no `route`/`router` import at all, just renders what it's given and `emit('filter', patch)`s a single-key partial query object on any change; `ProductList.vue`'s `on_sidebar_filter()` is the one place that merges `{ ...route.query, ...patch, page: undefined }` into a `router.replace()`. This replaced an earlier version where `ProductSidebar` had its own `watch(route.query, ...)` mirroring the parent's, plus a `sync_in_progress` flag whose only job was stopping that watcher from fighting itself — once "derive from URL" and "write to URL" no longer both happen in the same component, the flag has nothing left to guard against. A single `watch(() => route.query, fetch_products, { immediate: true, deep: true })` in `ProductList.vue` still handles the initial load + every subsequent change for the product/filter_groups fetch itself. **Changing a filter or the sort dropdown always drops `page` from the query** (`{ ...route.query, ...changes, page: undefined }` — Vue Router omits `undefined` query values from the URL entirely, same trick `BasePagination.vue` already used for its own page links) — `ProductCard`'s in-card category-badge click is a separate, legitimate direct-navigation case (different UI location, not part of the sidebar form) and does the same `page: undefined` drop independently. `PriceFilter.vue` never actually emitted its `filter` event before this feature (a pre-existing gap) — fixed by emitting on the range inputs' `change` (drag release, not `input`/every drag tick — that would fire far too often) and on the number inputs' existing `change` handler. Checkbox filter items (`category`/`author`/`status`) with a `count` of `0` are hidden unless that exact item is the one currently selected (so an active filter that combines with others into zero results never becomes impossible to un-check) — see `backend/docs/database.md` § API response format. `ProductList.vue`'s `sanitize_query()` runs once `filter_groups` has loaded and strips any `category`/`author`/`status` value not present in that group's real `items` (a hand-edited URL typo, since every genuinely-selected real value is guaranteed to appear per the rule above) and any non-numeric `price_min`/`price_max`/`page`, via a `patch_query(..., { reset_page: false })` — cleanup, not a real filter change, so it doesn't reset pagination. **`page` gets no backend validation rule at all** — a garbage `?page=asdasd` is already handled for free by Laravel's own `AbstractPaginator::isValidPageNumber()` (falls back to page 1 for anything that isn't a valid positive integer), so `ProductFilterRequest` has no `'page'` rule; adding one (tried briefly, reverted same session) just reintroduces the exact 422-the-whole-request bug the `price_min`/`price_max` handling exists to avoid. **On the frontend, `page` is included in `query_key_order` explicitly** (pushed last, after `sort_by`) and in `sanitize_query()`'s non-numeric check, matching `price_min`/`price_max` — pure consistency/hygiene, not the actual fix for the disappearing-page bug below.

**The real cause of `page=` vanishing right after a pagination click was a watcher echo loop in `FilterGroup.vue`, not `sanitize_query()` at all.** `FilterGroup.vue` mirrors `props.modelValue` into an internal `selected` ref via two watchers (`watch(selected, (val) => emit('update:modelValue', val))` and `watch(() => props.modelValue, (val) => { selected.value = val ?? ... })`). Every fetch — including a plain page navigation with no filter change — reassigns `filter_groups.value` to a **new array from the API response**, which recomputes `ProductList.vue`'s `selected` computed with **new array references** for every checkbox/rating group (`query_to_array()` always returns a fresh array), even when the actual string contents haven't changed. That new-but-equal reference flows down as `props.modelValue`, triggers the second watcher, reassigns `selected.value` (new reference again), which triggers the *first* watcher, which re-emits `update:modelValue` even though nothing the user did actually changed — cascading up through `ProductSidebar.vue`'s `on_checkbox_change` to `ProductList.vue`'s `on_sidebar_filter()`, which calls `patch_query()` **without `reset_page: false`** (its default is `reset_page: true`), wiping the `page` that pagination had just set moments earlier. This only fires once `FilterGroup` is already mounted with active watchers — a fresh `/products?page=2` link works fine because `filter_groups` (and the `FilterGroup` components themselves) don't exist yet on first render, so `selected`'s *initial* value is set directly from props at setup time, bypassing the watcher entirely. Fixed at the source: the `props.modelValue` watcher in `FilterGroup.vue` now does a `JSON.stringify` content comparison before reassigning `selected.value`, so an equal-but-new-reference update is a no-op and never re-triggers the emit side — don't re-add reference-equality assumptions to this pair of watchers without the same guard.

**`BasePagination.vue`'s scroll-on-page-change targets the enclosing `<section>`, not the pagination nav itself** (`container.value?.closest('section')`, scrolled to `offsetTop - 120` for the sticky header) — it used to scroll to the pagination controls' own position (`container.value.offsetTop - 200`), which put the just-clicked page-number buttons near the viewport top instead of scrolling back up to the start of the content the user is now looking at. `BasePagination` is shared by `ProductList.vue`/`NewsList.vue`/`AuthorList.vue`, all following the same `section.xxx > .container > ...` markup shape, so targeting the ancestor `section` generically works for all three without a per-page prop.

**`RatingFilter.vue` toggles off on a second click of the same star** (`emit('update:modelValue', props.modelValue === i ? null : i)`) — clicking the already-active rating deselects it, matching the same "click again to remove" expectation as everywhere else in this filter UI.

**`ActiveFilters.vue`** (new, `ui/filters/`) renders the currently-active filters as removable chips + a "Clear All" button, reusing `ProductList.vue`'s existing `selected`/`price_min`/`price_max`/`filter_groups` props (no new state) — a chip's whole clickable area removes it (not just an `×` glyph), price collapses to one combined chip (`$28 - $30`) rather than two, and removing it resets both `price_min`/`price_max` to the group's own bounds (by just clearing the query keys, same as `PriceFilter.vue`'s own fallback-to-bounds behavior). Both `remove` and `clear` emit the same `Record<string, string | undefined>` patch shape `on_sidebar_filter()` already handles, so `ProductList.vue` wires both events straight to it — no new handler needed. **Deliberately excludes `sort_by`** — sorting isn't a filter, so it doesn't get a chip or get touched by "Clear All".

**Query params always render in a fixed order** (price, category, author, rating, status, then `sort_by`, `page` last) regardless of which filter the user touches first — previously each interaction only appended whatever key it touched to wherever it happened to sit in `route.query`, so the URL's key order visibly reshuffled as you clicked around. `useQueryPatch`'s `patch_query()` takes an optional `order: string[]`; when given, it rebuilds the *entire* merged query into that key order (anything not in the list, like `page`, keeps falling to the end). `ProductList.vue` computes this order directly off `filter_groups` (`group.query_key` per group, `'price'` expanding to `['price_min', 'price_max']`, `sort_by` appended) rather than a hardcoded array — the order is already correct in the backend's response shape, no reason to duplicate it by hand on the frontend too.

**`ui/base/SortSelect.vue`** — the "Sort by..." dropdown, extracted out of `ProductList.vue` (`options: {value, label}[]` prop instead of hardcoded `<option>` tags) since Authors/News want the same sort control with their own different option list (books/bestseller/date-added, not price-related). Its field styling (`@include form-field-base`, `appearance: none`, dropdown-arrow SVG) matches `BaseSelect.vue`/`ContactForm.vue`'s hand-rolled select — one consistent look for every select on the site, not a SortSelect-specific style. **Still not the same component as `ui/base/BaseSelect.vue`** — `BaseSelect` is a labeled form field (validation error slot, meant for actual forms like `ContactForm.vue`) and takes its options via a `<slot>`; `SortSelect` is unlabeled and takes a plain `options` array — don't merge the two just because they're both "a select" and now share the same CSS mixin.

**Site title comes from `VITE_APP_NAME` (`.env`), not a hardcoded string in two places.** `index.html`'s `<title>` uses Vite's built-in `%VITE_APP_NAME%` HTML env-replacement (no plugin needed, works in both dev and build), and `routes/router.ts`'s per-page `document.title = "{page} | {APP_NAME}"` reads the same `import.meta.env.VITE_APP_NAME` — one source of truth instead of a literal `'BookStore'` duplicated in both places. `index.html`'s static title also matters on its own: it's what briefly shows before the JS bundle finishes loading and Vue Router's `beforeEach` guard sets the real per-page title, so it should never be left at Vite's generic scaffold default (`Site`).

**Checkout wizard (`views/Pages/Cart/CartPage.vue`)** — 3-step flow (Contact → Delivery → Payment) via `CheckoutStep.vue` wrapper (`step_number`, `active`, `done`, `#default`/`#summary` slots, emits `edit`).
- `useCheckoutForm()` holds the three step data refs, persisted to `localStorage`.
- Each step component takes `initial_data` prop, emits `change` (live draft, drives the sidebar `#summary` slot) and `complete` (fires only on explicit "Continue" click, advances `current_step` in `CartPage.vue`).
- `ContactStep.vue` uses vee-validate (`useForm`/`useField`, matches `ContactForm.vue` pattern) — **not** part of `useWizardStep`.
- `DeliveryStep.vue`/`PaymentStep.vue` use the `useWizardStep<T>(initial_data, emit, validator?)` composable instead (plain `reactive` draft + `is_valid` + `on_continue`) — do not hand-roll this pattern again, only these two non-vee-validate steps need it.
- Radio-style method pickers (delivery method, payment method) → `ui/base/BaseRadioGroup.vue` (generic `<script setup generic="T extends string">` component) — never re-duplicate the label/input markup or `__method`/`__method--active` styles per step.
- `BaseButton` has a third `variant="text"` (no background/border, small font, color-only hover) for inline actions like "Edit"/"Edit Items" — use it instead of ad-hoc `<button>` + custom SCSS.

**Reviews: rating is nullable, and notifications deep-link to the exact review/reply.** `rating_avg`/`product.rating` is `null`, not `0`, when a product has no reviews yet (see `backend/docs/database.md` § products) — every rating display (`StarRating.vue`, `book-card__rating-badge`, `RatedCard.vue`'s stars) hides itself on a falsy/null rating rather than rendering a 0-star state. `StarRating.vue`'s `v-if` is `rating && count !== 0` specifically (not just `rating`) so it stays correct even if a future caller passes a `count` of `0` alongside a stale non-null rating — `count` is only meaningful when the caller actually passes it (e.g. `ReviewItem.vue`'s per-review `<StarRating>` never passes `count`, so that check is a no-op there). `StarRating.vue`'s `(N reviews)` text is a real `<button>` emitting `click-count`; `ProductPage.vue` wires it to switch `active_tab` to `'Reviews'` and `scrollIntoView` a wrapping `tabs_wrap` ref — the click behavior lives in the page, not the base component.
- **Notification → review deep link**: `useNotificationActions.ts::build_link()` builds `query: { tab: 'reviews', pin: <root review id> }` + `hash: '#review-<target id>'` (root id = `notification.parent_id ?? notification.review_id`, so a root-level notification pins itself and a reply notification pins its thread's root). Backend: `ReviewController@index` reads `?pin=`, `ReviewService::getPaginated()` adds `ORDER BY CASE WHEN id = ? THEN 0 ELSE 1 END` ahead of the normal sort so the pinned root is always page-1-first regardless of pagination — pin only reorders, the existing "exclude viewer's own root reviews" `WHERE` is untouched, so a pin pointing at the viewer's own review still surfaces only in the "Your review" panel, not duplicated into the general list.
- `ReviewsPanel.vue` reads `pin`/`hash` off the route, passes `pin` through on every page fetch (not just page 1 — pagination must stay stable against the same pin across "Load more"), and on load/route-change calls `scroll_to_anchor()`: if `#review-<id>` is already in the DOM, scroll+flash it (`useReviewAnchor.ts`'s `scroll_to_review`, the same scroll+`review-item--flash`-class+`setTimeout` mechanism `ReviewItem.vue` already used for its own "Replying to X" jump — don't reintroduce a second copy of this). If not in the DOM (a reply hidden behind "View replies"), dispatch `window` `CustomEvent('review:open-replies', { parent_id, scroll_to })` — every root (non-`is_reply`) `ReviewItem.vue` listens, and the one whose own id matches `parent_id` sets `show_replies = true` and then scrolls/flashes the target on `nextTick`. This works identically whether the matching root is a general-list item or the viewer's own `my_review` panel, since both render through the same `ReviewItem.vue`.
- **`ReviewsPanel.vue` must react to its `product_slug` prop, not just `onMounted`.** Vue Router reuses the same `ProductPage.vue`/`ReviewsPanel.vue` instances when navigating between two `product` routes that only differ by the `slug` param (e.g. clicking a Related Products card) — no remount happens, so anything that only fetches in `onMounted` goes stale. `ProductPage.vue` already handled this for its own product fetch (`watch(() => props.slug, fetch_product, { immediate: true })`); `ReviewsPanel.vue` needed the same treatment — `watch(() => props.product_slug, ...)` re-subscribes the websocket channel, resets local state (`reviews`/`my_review`/pagination/rating breakdown/`is_editing`), and refetches. `reset_state()`/`load_reviews()` are factored out so `onMounted` and this watcher share one path instead of two copies. Any future per-product child component fetching its own data off a route-derived prop needs this same watcher — it will not get a free remount just because the visible product changed.

## Environment

Root `.env` controls Docker (ports, MySQL credentials). Backend has its own `backend/.env` for Laravel (DB_HOST=`db`, REDIS_HOST=`redis`).

## Real-time

Redis pub/sub connects backend to websocket server. PHP publishes to Redis → `websocket/server.js` subscribes and broadcasts to connected clients via ws.

## Caching

`CACHE_STORE=redis` (`predis` client) is already configured — TTLs/tags for cached "page bundle" endpoints and the tag lists each write trigger flushes both live in `config/cacheable.php` (`areas` / `flush_triggers`), not in `app/Services/CacheService.php` itself. `CacheService` is just two generic methods reading that config: `CacheService::remember(string $area, string $key, Closure $callback)` for the read path and `CacheService::flush(string $trigger)` for invalidation — see `LayoutService::getLayout()` / `HomeService::getHome()`. **Adding a new cached endpoint or a new write source that should invalidate it means adding an entry to `config/cacheable.php`, never touching `CacheService.php` itself** — that file was a growing list of one-off constants/methods (one pair per endpoint, one method per write source) before this was extracted (2026-07-07).

**Cache invalidation goes on the model, via the shared `App\Models\Concerns\FlushesCacheOnWrite` trait**, not a manual call from a controller/service — admin writes go through Filament's own generated CRUD with no custom service layer in front of it, so a model-level hook is the one place guaranteed to fire regardless of what triggered the write (Filament, Tinker, a future API endpoint). The trait's `bootFlushesCacheOnWrite()` wires `static::saved()`/`static::deleted()` to `CacheService::flush(static::$cacheFlushTrigger)` for you (Eloquent auto-calls `boot{TraitName}()` per trait, so it doesn't need or collide with a model's own `booted()`) — a model just adds `use FlushesCacheOnWrite;` and `protected static string $cacheFlushTrigger = 'x';` naming which `flush_triggers` key in `config/cacheable.php` to fire, instead of duplicating the `booted()` block. See `backend/docs/database.md` § Caching for exactly which models flush which tag.

**Never cache a raw `Collection` or Eloquent model — always `->toArray()` before returning from the cached closure.** This project's `config/cache.php` has Laravel 13's `'serializable_classes' => false` default (a real security control against object-injection via a compromised cache backend) — Redis's `unserialize()` under that setting silently returns `__PHP_Incomplete_Class` for **any** object read back from cache, so a cached `Collection` looks fine on the first request (computed fresh, never round-tripped through serialization) and then silently breaks on every request after that. Fix by not serializing objects into the cache in the first place — don't "fix" this by loosening `serializable_classes`, that setting should stay as-is.

**Redis is already wired up — reach for it proactively on new endpoints, don't wait to be told.** Any new read endpoint that returns general, non-personalized data (aggregates, reference/listing data, anything shaped like "same response for every visitor") should get the same `CacheService` treatment as `layout`/`home` as part of building it, not as a follow-up once someone notices it's missing. Endpoints that are inherently per-user or highly parameterized (cart, orders, filtered/paginated search with many query combinations) are the exception — caching those needs a deliberate key strategy, not the same blanket `remember()` call, so it's fine to skip caching there and just say so rather than force it.

## Code Style Rules — ALWAYS follow these

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

**No explanatory/rationale comments in code, even for non-obvious decisions.** Context about *why* something was built a certain way belongs in `backend/docs/database.md` / `CLAUDE.md`, not in a `//` line next to the code — an inline comment almost always just duplicates what's already documented there. If a decision is worth flagging, say it in the chat reply so it can be routed to docs deliberately, not left as a stray comment.

## Validation — ALWAYS use vee-validate + yup

All form validation in the frontend must use **vee-validate** with **yup** schemas. Never write manual validation logic (custom `computed` flags like `field !== ''`, manual error refs, etc.).

Pattern (matches `ContactForm.vue`):
- `useForm({ validationSchema: object({...}) })` at the component or page level
- Field components use `useField(() => props.name)` internally (like `BaseInput.vue`)
- Schema built with `yup`: use `.min(1, 'message')` for required text fields — **never** `string().required()` alone, because yup v1 passes `''` (empty string) through `required()`. Only `min(1)` reliably catches empty string. For email: `.min(1, '...').email('...')`. For numbers/phones: `.min(N, '...')`.
- Submit fires only when schema is valid (`handleSubmit` from `useForm`)
- Validation messages in English — matches every other UI string: all frontend-facing text (templates, labels, placeholders, validation/error messages) must be in English, regardless of what language the request that produced it was written in

## Notifications — ALWAYS use vue-toastification for API request feedback

Every API call's error feedback goes through `vue-toastification`, never a `console.error`/`alert()`/inline-only error state.

- Plugin registered once in `main.ts` (`app.use(Toast, { position: POSITION.TOP_RIGHT, timeout: 4000 })`), CSS imported there too (`vue-toastification/dist/index.css`)
- **Errors are handled globally, once** — `plugins/axios.ts`'s response interceptor calls `useToast().error(...)` on every failed request (extracts the first message from `{errors: "..."}` or `{errors: {field: [...]}}`, falls back to a generic message for non-JSON/network failures) — don't add a second error toast in the component that made the call, the interceptor already covers it
- **Success messages are per-component** — call `useToast().success('...')` explicitly after an action that should confirm success (e.g. `NewsletterForm.vue` after a successful subscribe). Not every successful request needs one (e.g. the `layout` fetch on app load shouldn't toast) — only ones the user directly triggered and would expect confirmation for
- `useToast()` works outside component setup too (e.g. inside `axios.ts`, not just `<script setup>`) — it's backed by a global event bus, not Vue's `inject()`

## Skeleton loading states — ALWAYS follow this

Any page/component that fetches its own data asynchronously must ship its loading skeleton in the same change — not as a follow-up once someone notices content jumping. `Home.vue`, `ProductList.vue`, `AuthorList.vue`, and `NewsList.vue` all do this now (all four are wired to real endpoints) — the moment a page gains a real fetch, it gains a skeleton in the same change, no exceptions.

- **Skeleton markup lives inside the real content component via a `loading?: boolean` prop — never a separate `XSkeleton.vue` file.** See `ProductCard.vue`/`RatedCard.vue`/`BlogCard.vue`: the `loading` branch reuses the component's own real wrapper classes (`.book-card__figure`, `.book-card__title-row`, etc.) with `ui/base/BaseSkeleton.vue` standing in for image/text content, so a future dimension change to the real card can't silently desync from its skeleton. A parallel `ProductCardSkeleton.vue`-style file was tried and reverted for exactly this reason — don't reintroduce that pattern.
- **Push the `loading` prop down to the smallest reusable child that already renders that data shape — a card/list-item component (`ui/product/*Card.vue`, `ui/blog/BlogCard.vue`, a future `ui/author/AuthorCard.vue`, etc.) — rather than writing skeleton markup in the page/view or in a one-off section component.** A page (`views/Pages/*.vue`) or section (`ui/home/*Section.vue`) only owns the `is_loading` ref and passes `loading` straight through to the child it already renders in a loop (`v-for="n in 9"` in place of `v-for="product in products"`, same component either way — see `ProductList.vue`). Only write page/section-local skeleton markup (via `BaseSkeleton` directly) for the odd bit of chrome that has no reusable child component of its own, e.g. the results-count text or a filter-sidebar block in `ProductList.vue`/`ProductSidebar.vue` — that's the exception, not the default.
- `ui/base/BaseSkeleton.vue` is the one shimmer primitive (`width`/`height`/`radius`/`circle` props) — build every skeleton out of it rather than hand-rolling a new shimmer animation.
- **A section gated by `v-if="data"` because its prop starts out `null`/empty must become `v-if="loading || data"`.** Otherwise the whole section pops into existence once the fetch resolves instead of already being on-screen as a skeleton. See `BestAuthorSection.vue` / `BlogSection.vue`.
- **Data owned by a Pinia store that's fetched once keys its skeleton off the store's own loaded flag** (e.g. `useLayoutStore().loaded`), not a separate page-local ref — see `CategoryStrip.vue` / `HeroSection.vue`.
- The page/view component owns an `is_loading` ref (`true` initially, set `false` in the fetch's `.finally()`) and passes it down to sections as a `loading` prop, the same shape as its other data props — see `Home.vue`. For a page whose fetch re-runs on filter/sort/pagination changes (`ProductList.vue`), reset `is_loading = true` at the start of every fetch, not just the first one, so the skeleton reappears on every refetch.

## Routing — ALWAYS use named routes, never hardcoded path strings

Every internal link/navigation goes through `routes/router.ts`'s route **names** (`{ name: 'products', query: {...} }`, `{ name: 'product', params: { slug } }`), never a literal path string (`'/products'`, `` `/product/${slug}` ``, `{ path: '/products', query: {...} }`). If a route's path ever changes, a hardcoded string means grepping the whole project for every place that guessed at the URL by hand; a route name means changing one line in `router.ts`.

- **Reusable card components compute their own route internally from a plain identifier prop (`id`/`slug`) — they don't accept an `href`/path prop from the caller at all.** `ProductCard.vue`/`RatedCard.vue` take an `id`/`slug` prop (already needed for other things, e.g. cart identity) and compute `const to = computed(() => ({ name: 'product', params: { slug } }))` once; `BlogCard.vue` does the same for `{ name: 'post', params: { slug } }`. Callers (`ProductList.vue`, `BestsellersSection.vue`, `Sidebar.vue`'s book list, etc.) just pass the slug — they never build a path string themselves.
- **`useShopFilterNav.ts`'s `go_to_filter(route_name, key, value)` takes a route *name*, not a path** — `route.name === route_name` for the "already there, just patch the query" branch, `router.push({ name: route_name, query: { [key]: value } })` otherwise. `ProductCard`/`RatedCard`'s category/author badges pass `'products'`; `BlogCard`'s category badge passes `'news'`.
- **A `view_all_href`/similar "where does this section's CTA link to" prop is typed `RouteLocationRaw` (from `vue-router`), not `string`** — see `BestsellersSection.vue`/`BestRatedSection.vue`/`BlogSection.vue`. Default via a factory function (`view_all_href: () => ({ name: 'products', query: { status: 'Bestseller' } })`), same reason object/array prop defaults always need a factory in `withDefaults`.
- **`CartItem.href` (`stores/shop.ts`) is a `RouteLocationRaw`, not a string** — even though nothing currently renders a link from it, it's assembled the same way (`to.value` from whichever card added it to the cart), so it can never drift back into a hand-built path string later.
- **`router.ts`'s own route *definitions*, and `redirect`/`beforeEnter` targets, are the one legitimate place path strings still appear** (they're what a route name resolves to) — e.g. the `cart` route's `beforeEnter` returns `{ name: 'home' }`, not `'/'`, once the route names are the established source of truth, but the routes array itself still declares `path: 'products'` etc. Don't confuse "defining what a name maps to" with "hardcoding a link to that name" — only the latter is the anti-pattern this rule targets.
- **A `beforeEnter` guard that reads `useAuthStore().is_authenticated` must `await` the store's `ensure_ready()` first.** `auth_store.user` starts `null` until `fetch_user()` (kicked off from `App.vue`'s `onMounted`, alongside `useLayoutStore().fetch_layout()`) resolves — on a hard page load/refresh, a synchronous guard checking `is_authenticated` right away always reads the pre-hydration `false`, regardless of whether the session cookie is actually valid, and bounces the user to `home`. Clicking a link to the same route from elsewhere in the SPA doesn't show this, since `user` is already hydrated from the earlier page load by then — this is exactly why the `notifications` route's guard looked fine in normal use but redirected home on every refresh. `stores/auth.ts`'s `ensure_ready()` memoizes the initial `fetch_user()` call in a module-level promise so both `App.vue`'s boot call and any guard awaiting it share the same in-flight request instead of double-fetching; `beforeEnter: async () => { await useAuthStore().ensure_ready(); if (!useAuthStore().is_authenticated) return { name: 'home' } }` is the pattern — any future route gated on auth state needs the same `await`, not a bare synchronous check.

## SEO — every page loads its own `seo` block, ALWAYS follow this

**Backend:** one `seo_meta` table for everything (`type`/`record_id` polymorphic, see `backend/docs/database.md` § SEO) — never a second, page-specific SEO table. A page with no natural content record of its own (Home, the Products/Authors/News *listings*, Contact, About, Cart) gets a row in the `pages` table instead, so it plugs into the exact same `Model::seo()` relation as `Product`/`NewsPost`/`ProductsCategory` — no type-sniffing branch, no special case. `PageService::getPage(string $slug)` is the one place that resolves a page's full bundle — `{title, content, excerpt, image, seo: {title, description, keywords}}` (the nested `seo` falls back to the `pages` row's own `title`/`excerpt` when no `seo_meta` row exists yet).

**All static pages (Home, Contact, About, Cart, and any future one) are grouped behind a single `StaticController`** (`Route::prefix('pages')->controller(StaticController::class)->group(...)`, same convention as `Products`/`Authors`/`News`) — a dedicated method per page that needs extra composed data (`home()` → `HomeService::getHome()`, `contact()` → `ContactService::getContact()`, `about()` → `AboutService::getAbout()`, each already folding `'page' => $this->pageService->getPage('<own slug>')` into their own response) plus one catch-all `show(string $slug)` registered **last** in the route group (Laravel matches in registration order, so the explicit routes win first) that returns the bare `getPage()` result directly for anything else (currently just `cart`). **New page with no extra data of its own needs nothing — `show()` already covers it. New page that needs extra composed data → one more Service method + one more explicit route/controller method here, not a new controller file.**

**Frontend:** `useSeo(seo)` (`composables/useSeo.ts`) is called **once, centrally, in `plugins/axios.ts`'s response interceptor** — never per-page. The interceptor checks `response.data.data.page?.seo ?? response.data.data.seo` (bundle endpoints nest the page's content under a `page` key alongside their own other data — `Home`/`Products`/`Authors`/`News`/`Contact`; the generic `GET /api/pages/{slug}` used by pages with no other backend data of their own — About, Cart — returns the page bundle flat, so `seo` is already top-level) and calls `useSeo()` unconditionally; it's a no-op when a response has neither shape (e.g. `POST /newsletter`). This mirrors the existing global error-toast interceptor in the same file — don't add a second, per-component `useSeo()` call next to a fetch, the interceptor already covers every request made through `api`. `useSeo()` sets `document.title` and upserts `<meta name="description">`/`<meta name="keywords">` in `document.head` — there's no vue-meta/unhead dependency, so this is the only mechanism; don't reach for a meta-tag library without discussing it first. **`router.ts`'s `beforeEach` does not set any fallback `document.title`** — it was tried (a synchronous `"{route.meta.title} | {APP_NAME}"` fallback, immediately overwritten once `useSeo()`'s async fetch resolved) and reverted (2026-07-04): it made the title visibly jump twice per navigation (fallback → real value) instead of once, so `document.title` now only ever changes when `useSeo()` actually has real data — same "don't reset, just let the next real value overwrite it" behavior already accepted for meta description/keywords below. `index.html`'s static `%VITE_APP_NAME%` title is what shows until the first `useSeo()` call resolves (page load or navigation alike) — this is intentional, not a gap to patch with a router-level fallback. A page's meta description/keywords are **not** reset on navigation away from it — they just get overwritten once the next page's own fetch resolves, a known/accepted gap, not something to fix reactively per-page.
