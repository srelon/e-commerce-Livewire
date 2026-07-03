# Database Schema — Shop Domain

Reference for the tables added on top of the initial RBAC scaffold (`users`, `admins`, `admin_roles`, `admin_accesses`). All shop-domain tables use `timestamps()` + `softDeletes()`.

**Manual ordering always uses `sort_order`** (`unsignedInteger`, default `999`) — never `sort`, `order`, or `position`. Every table with a manual-order column uses this exact name (`products_categories`, `product_images`, `product_stocks`, `menus`), including `menus` where it's remapped from the `solution-forest/filament-tree` package's own `order` config key (see § Navigation).

Migrations: `backend/database/migrations/2026_07_02_*`. Models: `backend/app/Models/*`.

## Status field reference

Project convention: **`4` is reserved for cancel/delete-type states** across enums (not `2`/`3`), so a sort/filter on "is this dead" only ever has to check one value. Fields marked "not explicitly defined" got a plain `status` column added on request but the exact value meaning was never pinned down in discussion — they default to the same 0/inactive / 1/active pattern as the rest of the project; confirm before building a Filament form around them.

| Table.column                    | Values                                                                                                                                                                                                             |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `products_categories.status`    | not explicitly defined — 0 inactive / 1 active (by analogy)                                                                                                                                                     |
| `news_categories.status`        | not explicitly defined — 0 inactive / 1 active                                                                                                                                                                  |
| `products.status`               | `0` created / `1` active (shown in search and filters) / `2` archived (reachable only via direct link, not searchable) / `3` reserved for future use / `4` deleted                                                |
| `products.bestseller`           | `0`/`1` — a flat flag, not a lifecycle status                                                                                                                                                                   |
| `product_stocks.status`         | `0` created / `1` active / `2` fulfilled / `3` queued / `4` cancelled                                                                                                                                             |
| `reviews.status`                | inherited from the source project (comments), `default 1`; no exact value list survived there either (before `recreate_comments_table` it was `pending/approved/rejected`) — confirm before building moderation |
| `reviews.deleted_by`            | `null` = deleted normally (by user/admin) / `1` = deleted by a moderator (shows a placeholder in its place)                                                                                                       |
| `review_likes.opp_type`         | `0` cancelled / `1` dislike / `2` like                                                                                                                                                                            |
| `carts.status`                  | `0` created / `1` completed (after checkout)                                                                                                                                                                      |
| `cart_items.status`             | `0` created / `1` ordered / `4` deleted                                                                                                                                                                           |
| `products_favorites.status`     | `0` cancelled (clicking again toggles, doesn't delete the row) / `1` active                                                                                                                                       |
| `delivery_services.status`      | not explicitly defined — 0 inactive / 1 active                                                                                                                                                                  |
| `delivery_branches.status`      | `0` inactive / `1` active                                                                                                                                                                                         |
| `payments.status`               | not explicitly defined — 0 inactive / 1 active                                                                                                                                                                  |
| `orders.status`                 | `0` placed / `1` shipped / `2` delivered / `3` paid / `4` cancelled                                                                                                                                               |
| `order_items.status`            | `0` pending / `1` shipped / `2` delivered / `3` completed / `4` cancelled                                                                                                                                         |
| `news_posts.status`             | `0` inactive / `1` active                                                                                                                                                                                         |
| `contact_messages.status`       | not explicitly defined — 0 new / 1 handled                                                                                                                                                                      |
| `newsletter_subscribers.status` | `0` unsubscribed / `1` subscribed (default `1`)                                                                                                                                                                   |
| `contact_info.status`           | not explicitly defined — 0 inactive / 1 active                                                                                                                                                                  |

## Catalog

**products_categories** *(model `ProductsCategory`, table renamed from `categories` to avoid confusion with `news_categories`)* — `name, slug, icon, image, sort_order, status`. Product count is never stored, always `products()->count()`.

**products_authors** *(model `ProductsAuthor`, table renamed from `authors` for the same reason as `products_categories`)* — `name, slug, avatar_color, photo, content`. Same idea — `books` is `products()->count()`, not a column.

**products** — `category_id → products_categories`, `author_id → products_authors`, `title, slug, short_description, description, rating_avg, reviews_count, bestseller, status, published_at`.
Price and stock are **not here** — see `product_stocks`. For product cards/pages, always read price/availability off `Product::activeStock` (the row with `status = 1`) — never add a price column back onto `products`.

**product_images** — `product_id → products, image, sort_order`.

**Image columns are JSON, not a single path string** — `products_categories.icon`, `products_categories.image`, `products_authors.photo`, `product_images.image`, `news_posts.image` all store an object of size/format variants (e.g. `{"original": "...", "thumb": "..."}`) cast to `array` on the model, rather than one plain path. Exact variant keys aren't finalized yet — decide when the upload/image-processing service gets built.

**product_stocks** — a batch/lot system for inventory, not a single column on the product:
`product_id → products, quantity, price, before_price (nullable, pre-discount price), real_price (purchase/cost price), sort_order (default 999), status`.
A product can have several rows at once: one `active` (currently on sale), others `queued` (next batch at a different price), old ones `fulfilled`/`cancelled`. `order_items` points at a specific row (`product_stock_id`), so price and stock are decremented against the exact batch, not the product in general.

## Reviews and notifications

Ported almost 1:1 from another existing project (its `comments`/`comment_likes`/`comment_reports`/`user_notifications` tables), with `comment_id → review_id`. The moderation/likes/notifications logic for that structure is already built and working there, so it wasn't redesigned here.

Unlike the source project, `reviews` is **not** product-specific — it's polymorphic (`type`/`record_id`, aliased through the same morph map as `seo_meta`) so it can be reused for any content, not just `products`. `Product::reviews()` and `NewsPost::reviews()` are both `morphMany(Review::class, 'reviewable', 'type', 'record_id')`, sharing one table instead of duplicating the whole structure per content type.

**reviews** *(= `comments`, model `Review`)* — `type, record_id (the reviewed record — e.g. type "products" + a product id), user_id (nullable, nullOnDelete — but a row can only be created while authenticated, there is no guest path), parent_id (self, replies), replied_to_comment_id, rating (nullable, only on the root review — the one field that wasn't in the original), body, status, deleted_by`.

**review_likes** *(= `comment_likes`, model `ReviewLike`)* — `review_id, user_id, opp_type`, unique(`review_id, user_id`).

**review_reports** *(= `comment_reports`, model `ReviewReport`)* — `review_id, user_id, reason`, unique(`review_id, user_id`).

**user_notifications** *(= `user_notifications`, logic not implemented yet, structure only)* — `user_id, from_user_id (nullable, no FK), type (system/reply/like/dislike), data (json), product_id (nullable), review_id (nullable), parent_id (nullable, root review, no FK), read_at`.

## Cart / Favorites

**carts** — `user_id (unique)`, `status`. One cart per user.
**cart_items** — `cart_id → carts, product_id → products, quantity, status`.

Guest carts are **not stored in the DB** — client-side only (Pinia store + localStorage). Only an authenticated user's cart is persisted (so it syncs across devices).

**products_favorites** *(model `ProductsFavorite`, table renamed from `favorites`)* — `user_id → users, product_id → products, status`, unique(`user_id, product_id`). Clicking "favorite" again toggles `status` (0/1) instead of deleting the row.

## Delivery / Payment

**delivery_services** — carrier directory (Nova Poshta, self-pickup, etc.): `name, key (unique — for API lookups), status, price (rough estimate, informational only)`. Self-pickup is just a row here, not a `null` sentinel.

**delivery_branches** — actual branches/pickup points, synced from a carrier API: `delivery_id → delivery_services, city, branch, status, hash (unique — dedupes API data)`.

**payments** — payment method directory: `name, key, status`. Extensible for future payment gateways.

## Orders

**order_contacts** — `first_name, last_name, phone, email`. Reused across a customer's orders instead of duplicating the fields directly on `orders`.

**orders** — `public_id (unique — used in the order URL/status page instead of the real id), user_id (nullable — guest checkout), contact_id → order_contacts, delivery_id → delivery_services (required), delivery_branch_id → delivery_branches (nullable — not needed for self-pickup), payment_id → payments, status, paid_amount (nullable — a separate lump-sum amount for card payment of the whole order), txid (unique, auto-generated), tracking_number (nullable)`.

`public_id` and `txid` are **auto-generated** in `Order::booted()` (`static::creating`) — never set them manually on create.

**order_items** — `order_id → orders, product_id → products, product_stock_id → product_stocks, price (snapshot at order time), quantity (ordered), fact_quantity (actually shipped), paid_amount (actually paid for this line), status`. Title/author/image are not duplicated — pulled through `product_id`.

`orders.status` and `order_items.status` are **independent**: one order can have some items cancelled and others delivered. Planned admin flow (not implemented yet): set `order_items.status` first, then setting `orders.status` back-fills any untouched items.

## Content

**news_categories** — `name, slug, status`.
**news_posts** — `title, slug, category_id → news_categories, excerpt, content, image, author_id → admins (nullable), published_at, status`.
**contact_messages** — `first_name, last_name, email, subject, message, status`.
**newsletter_subscribers** — `email (unique), status`.

## Navigation

**menus** *(model `Menu`, no `status`, no `softDeletes` — unlike the rest of this doc's tables)* — `name, route (nullable), parent_id, type (link = external URL / route = a Vue Router route name from frontend/src/routes/router.ts, e.g. "products", "about" — not a path, and not a Laravel route), params (json, nullable — route params for dynamic segments, e.g. {"slug": "..."} for the "product"/"post" routes, only relevant when type=route), sort_order, location (header/footer)`.

The frontend resolves a `type=route` item as `:to="{ name: menu.route, params: menu.params }"` (vue-router's named-route object form), not as a raw path string.

In the admin (`MenuTree::routeSlugModels()`), the `product`/`post` routes get a searchable "Target" select instead of a free-text params box — it queries `Product`/`NewsPost` by `title` (last 10 by id, filtered as you type) and writes the picked record's `slug` into `params.slug`, so an editor never hand-types a slug that could drift from the real record.

`MenuSeeder` seeds the 6 top-level `header` items straight from `AppHeader.vue`'s `nav_links` (Home/Products/Authors/About Us/News/Contact Us) — the "Categories" mega-menu in the same header is driven by `products_categories`, not `menus`, so it isn't duplicated here. `footer` isn't seeded yet — its layout isn't finalized in the frontend.

Powers a WordPress-style drag-and-drop menu editor at `/admin/menu-tree` (`App\Filament\Pages\MenuTree`, gated by the `menus` access key like every other resource), built on `solution-forest/filament-tree` rather than a hand-rolled tree — see `config/filament-tree.php` for the column-name mapping (`order → sort_order`, `title → name`, `parent → parent_id`).

`parent_id` is **not** a nullable self-referencing FK like elsewhere in this schema — the package requires `-1` as the sentinel for a root/top-level item (`integer default(-1)`, no FK constraint, since `-1` never matches a real row). `parent_id` being a real menu id means the item renders inside that parent's dropdown. `$maxDepth = 2` on the page caps nesting at one dropdown level (top-level items + their direct children) — matches how the header/footer nav actually renders, not an arbitrary limit.

Deleting a parent cascades to its children (handled by the package's model trait, not application code).

**contact_info** *(model `ContactInfo`, table `contact_info` — explicitly set via `$table`, not the Eloquent-guessed `contact_infos`, and deliberately not named `contacts` to avoid confusion with `contact_messages`, the unrelated contact-form-submissions table)* — `name (label, e.g. "Phone"), content (the value, e.g. "578-393-4937"), icon (plain text, an inline SVG path string like "M7.5 0A5.5...", not JSON — unlike every other icon/image column in this schema, because that's what `ContactPage.vue` actually hands the `<path d>` attribute, not an uploaded file), key (unique — lookup key, e.g. "phone"/"email"/"address"/"working_hours"), status, sort_order`. A directory table like `delivery_services`/`payments`. `ContactInfoSeeder` seeds the 4 rows straight from `ContactPage.vue`'s `contact_info` array — the `href` (`mailto:`/`tel:`) shown there isn't a column, the frontend derives it from `key` (`email`/`phone`) instead of storing it.

**faqs** *(model `Faq`)* — `title, content (text — answers run to a paragraph, same as `products.description`), type (plain string, not an enum — which page/section it belongs to, e.g. "contact" for `ContactPage.vue`'s FAQ block; add new type values freely as more pages get FAQs), status, sort_order`. `FaqSeeder` seeds the 6 `type=contact` rows straight from `ContactPage.vue`'s `faq` array (`question`/`answer` → `title`/`content`).

## Seed data

`database/seeders/` has one seeder per table group (`ProductsCategorySeeder`, `ProductsAuthorSeeder`, `ProductSeeder`, `NewsCategorySeeder`, `NewsPostSeeder`, `DeliveryServiceSeeder`, `DeliveryBranchSeeder`, `PaymentSeeder`, `MenuSeeder`, `ContactInfoSeeder`, `FaqSeeder`), all wired into `DatabaseSeeder`. Every value is taken from the frontend's hardcoded mock data (`ProductList.vue`, `AuthorList.vue`, `NewsList.vue`/`NewsPage.vue`, `DeliveryStep.vue`, `PaymentStep.vue`, `AppHeader.vue`, `ContactPage.vue`) rather than invented — the exceptions are `product_stocks.quantity` (frontend has no stock numbers at all, so every seeded product gets a flat placeholder of 50), `delivery_branches.hash` (synthesized as `md5("{city}|{branch}")` since there's no real carrier API yet), and `products_categories.image` (the frontend never had a per-category promo image — the mega-menu always showed one static image regardless of category — so `ProductsCategorySeeder` uses one distinct royalty-free photo per category, downloaded from Picsum Photos and stored at `products_categories/promo-{slug}.jpg`, loosely matched by subject — e.g. cutting board + vegetables for Cooking, a pier at sunset for Adventure. Not licensed/curated for production use, just enough visual variety for local dev — swap in real per-category photography before shipping).

`ProductList.vue`'s mock data references two authors (Theodore Langley, Autumn Journey's Samuel Wright) that `AuthorList.vue` never gives a bio/avatar color to. Rather than seed authors with no real data, `ProductsAuthorSeeder` only has the 6 authors `AuthorList.vue` actually describes, and `ProductSeeder`'s original 7 entries dropped the 2 products that only those missing authors wrote (Anxiety Unmasked, Autumn Journey) — 7 products, 6 authors from the frontend mock data, not 9/8.

Images referenced by the original 7-product seed were copied from `frontend/public/images/` into `backend/storage/app/public/{products,products_categories,news}/` (served through the existing `public/storage` symlink) and are stored as `{"original": "products/book-image-19.webp"}` — relative to the `public` disk root, only the `original` variant populated (see the image-JSON note above).

**`ProductSeeder` was later extended with 40 more products** (2026-07-03, explicitly requested — not the frontend-mock-sourced data the rest of this file otherwise follows), bringing the catalog to 47 products total, ~4-5 per category, distributed round-robin across the same 6 authors. These titles/prices/ratings are invented (there was no more frontend mock data to source from at this volume) — plausible book-store filler, not meant to be taken as real catalog content. Their cover images (`products/cover-{picsum-id}.jpg`) are royalty-free photos from Picsum Photos, same sourcing method as the category promo images above, visually spot-checked before use — generic landscape/lifestyle photography, not real book cover art, purely for local-dev visual variety (this catalog size exists mainly to make pagination/filtering on the Products page meaningful to test — 7 products barely exercised more than 1 page).

## SEO

**seo_meta** — one shared table for SEO fields instead of duplicating `seo_title`/`seo_description`/`seo_keywords` on every content table: `type, record_id, seo_title, seo_description, seo_keywords`, unique(`type, record_id`).

`type` stores a short alias (the target's table name — `products`, `products_categories`, `news_posts`), not a PHP class name. This is wired through Eloquent's morph map (`Relation::enforceMorphMap` in `AppServiceProvider::boot()`), so `record_id`/`type` still work as a normal polymorphic relation (`Product::seo()`, `ProductsCategory::seo()`, `NewsPost::seo()` are all `morphOne(SeoMeta::class, 'seo', 'type', 'record_id')`) while keeping the column human-readable.

## Auth

Customer-facing auth (the `users` guard/table) uses **Sanctum SPA (cookie) authentication** — matches the frontend's `axios.ts` (`withCredentials: true`). Admin panel auth (the `admins` guard) is untouched and stays separate.

Infra wired up (no login/register endpoints yet):
- `routes/api.php` registered in `bootstrap/app.php`, with `$middleware->statefulApi()` enabled
- `config/sanctum.php` — `guard => ['web']` (the `web` guard already points at the `users` provider)
- `config/cors.php` — `supports_credentials: true`, `allowed_origins` derived from `SANCTUM_STATEFUL_DOMAINS` (scheme added automatically: `http://` in local, `https://` otherwise)
- `.env` — `SANCTUM_STATEFUL_DOMAINS` must be bare `host:port` entries (no scheme) — Sanctum matches against the request's Origin/Referer host directly; CORS derives its own scheme-prefixed origins from the same variable

## API response format

Ported from the same source project as the reviews structure: `App\Traits\RespondTrait` (used by the base `Controller`), giving every controller `respondWithJson($content, $status = 200)` → `{data, status}`, `respondWithError($message, $code = 400)` → `{status, errors}`, and `paginationMeta($paginated)` for a compact pagination envelope (`current_page, last_page, total, prev_page_url, next_page_url` — the latter two are just `'prev'`/`'next'`/`null` flags, not real URLs).

**Form Request validation failures also match this envelope** — `bootstrap/app.php` renders `ValidationException` on `api/*` routes as `{status: 422, errors: $e->errors()}` (a `{field: [messages]}` map) instead of Laravel's default `{message, errors}` shape, so the frontend's error handling doesn't need a special case for validation vs. any other `respondWithError()` failure.

**`GET /api/layout`** *(`LayoutController` → `App\Services\LayoutService`, first real endpoint, fetched once on app load by the frontend's `useLayoutStore().fetch_layout()`)* — `{categories, menu, contacts}`:
- `categories` — delegated to `App\Services\ProductService::getCategories()` (moved out of `LayoutService`, same "other pages will need this too" reasoning as `bestsellers`/`best_rated`/`blog`) — active `products_categories`, `icon`/`image` are both raw relative storage paths (e.g. `"products_categories/category-icon-1.svg"`, not a full URL — the frontend's `to_storage_url()` helper in `stores/layout.ts` prepends `VITE_APP_URL`/`storage/`), `count` is `products()->count()`, not a stored column. `image` drives `AppHeader.vue`'s mega-menu promo panel — it swaps to the hovered category's `image` (falls back to the first category when nothing's hovered).
- `menu` — `location=header` items only, root items (`parent_id = -1`) with one level of `children` nested inline (matches `MenuTree`'s `$maxDepth = 2`). Each item is `{id, name, type, route, params, children}` — a `route` item's `route` is a Vue Router name, resolved by the frontend as `{ name: route, params }`, not a path.
- `contacts` — active `contact_info`, ordered by `sort_order`. `icon` is the raw inline SVG path string (see § Navigation) — the frontend feeds it straight into a `<path d>`, no URL resolution needed (unlike `categories.icon`).

**`POST /api/newsletter`** *(`NewsletterController` → `App\Services\NewsletterService`, validated by `NewsletterSubscribeRequest`)* — body `{email}`, creates a `newsletter_subscribers` row with `status = 1`. `email` must be unique (case handled by the DB's default collation, not application code) — a duplicate returns `422 {status: 422, errors: {email: ["This email is already subscribed."]}}`, which `components/ui/shop/NewsletterForm.vue` surfaces via `vue-toastification` (see CLAUDE.md § Frontend for the toast convention). No unsubscribe/resubscribe flow yet — a previously-unsubscribed email (`status = 0`) still collides with the unique check rather than being reactivated.

**`GET /api/home`** *(`HomeController` → `App\Services\HomeService`, fetched once by the Home page)* — `{bestsellers, best_author, best_rated, blog}`:
- `bestsellers`/`best_rated`/`best_author` are delegated to `App\Services\ProductService` (`getBestsellers(int $limit = 8)`, `getBestRated(int $limit = 9)`, `getBestAuthor()`), not built inline in `HomeService` — these are generic product-listing queries, not Home-page-specific, so any future page needing "top N by rating"/"bestsellers"/"best author" reuses this same service rather than re-deriving the query. `bestsellers` is `ORDER BY bestseller DESC, rating_avg DESC LIMIT 8`. `bestseller` is deliberately left as the existing `0`/`1` flag (not converted to a weighted score) — decided back when the demo catalog only had 7 products and 2 flagged (see § Seed data for the later 40-product expansion, 8 flagged of 47 now) — a real weighted score would need actual sales-volume data this project doesn't have yet. `price` comes from `Product::activeStock`, `image` from the `Product::primaryImage` relation (`hasOne(ProductImage::class)->orderBy('sort_order')` — a `hasOne`, not a `hasMany` capped with `->limit(1)` in the eager-load closure, which would silently apply the limit to the *whole* batched query across every loaded product instead of one-per-product; `hasOne` doesn't have that bug since Laravel groups by parent key during hydration instead of at the SQL level). `best_author` is the `products_authors` row whose products have the highest `SUM(bestseller)` (tie-broken by `AVG(rating_avg)`), grouped straight off `products.author_id`. `photo` is `null` for every seeded author (see § Seed data) — the frontend needs a fallback image/avatar for this, there's no real photo data yet. `best_rated` is the same shape as `bestsellers`, `ORDER BY rating_avg DESC LIMIT 9`. Both are thin wrappers around a shared `topProducts(array $orderByDesc, int $limit)` (2026-07-03 — the only real difference between them was which columns to `orderByDesc()` and in what order) — `getBestsellers()` passes `['bestseller', 'rating_avg']`, `getBestRated()` passes `['rating_avg']`.
- `blog` — delegated to `App\Services\BlogService::getLatestPosts(int $limit = 7)`, same reasoning as `ProductService` — active `news_posts`, `ORDER BY published_at DESC`, for reuse by a future full News/Blog listing page.

**`GET /api/products`** *(`ProductController` → `ProductFilterRequest` → `App\Services\ProductService::getFilteredList()`/`getFilterGroups()`, powers the Products listing page)* — query params `category[]`, `author[]`, `price_min`, `price_max`, `rating`, `status[]`, `sort_by`, `page`; response `{products, filter_groups, meta}`:
- `products` — paginated (9 per page, Laravel's own `paginate()`, reads `page` from the query automatically), same shape as `HomeService`'s product cards (`ProductService::formatProduct()`). Every checkbox filter (`category`, `author`, `status`) matches by **display name**, not slug/id — `whereHas('category', fn ($q) => $q->whereIn('name', $filters['category']))` — this deliberately matches the existing convention everywhere else in the frontend (`AppHeader`'s mega-menu and `HeroSection`'s category cards already link via `query: { category: cat.name }`), not a shortcut. Multiple values within one checkbox group are OR'd (`whereIn`/`orWhereHas`); different groups are AND'd together.
- `status` values are the literal strings `"In Stock"` (`activeStock.quantity > 0`) and `"On Sale"` (`activeStock.before_price` is not null) — same name-matching convention as category/author, not a coded enum, so the frontend doesn't need a separate value↔label mapping.
- `sort_by` — `default` (by `id`), `popularity` (`bestseller DESC, rating_avg DESC`), `rating` (`rating_avg DESC`), `price_asc`/`price_desc` (ordered by a correlated subquery on `product_stocks.price` — Eloquent can't `orderBy()` across a relation directly, and joining would multiply rows for products with several stock batches, so this uses `Builder::orderBy($subquery)` instead, scoped to the same `status = 1` + `latest(sort_order)` active-stock rule as `Product::activeStock()`).
- `filter_groups` — same shape as `ProductSidebar.vue`'s old hardcoded `FilterConfig[]`, now server-built. `category`/`author`/`status` are **faceted**: each group's items/counts are computed against the *other* currently-active filters, excluding that group's own filter (`ProductService::applyFilters($query, $filters, except: [...])`). Selecting a category narrows the `author`/`status` groups (and their counts) down to what's actually reachable within that category, while the `category` group itself stays showing every category (so the user can switch categories rather than getting stuck once one is picked) — same reasoning applies to every other faceted group excluding itself. **`price`'s `min`/`max` are deliberately *not* faceted** — `priceFilterGroup()` always returns the real `MIN(price)`/`MAX(price)` across the *entire* active catalog, regardless of any other active filter, not a value that narrows as other filters change. This was tried the faceted way first and reverted (2026-07-03): a price range that shifts every time a category/author gets selected is unpredictable UX (the slider's own boundaries move under the user while they're using it) and was also the direct cause of a cascade of `PriceFilter.vue` bugs (creeping thumbs, both handles moving together, thumbs bunching up when the shifting bounds happened to collapse to a single price) that kept resurfacing no matter how the frontend tried to compensate for it — fixing it at the source (stop the bounds from moving) was simpler and more correct than chasing the frontend symptoms. **Not cached** — once category/author/status counts depend on the active filter combination, `filter_groups` has exactly the same "too many combinations for decent hit rate" problem as the product listing itself (see § Caching), so both are computed fresh per request; only `LayoutService`/`HomeService` stay cached, since those genuinely don't vary per request.
- **`category`/`author`/`status` items with a `count` of `0` are excluded — unless that item is itself currently selected.** No point offering a checkbox that can only ever return zero results, but a selected item that happens to combine with the *other* active filters into zero results must stay visible so the user can actually see and un-check it — hiding it would make an active filter impossible to remove from the UI. `categoryFilterGroup()`/`authorFilterGroup()` are both thin wrappers around a shared `checkboxFilterGroup(filters, model, title, queryKey, orderBy, scopedByStatus)` (2026-07-03 — the two were ~99% identical, merged once that became obvious) that queries the given model with `withCount(['products' => fn ($q) => ...])` (a correlated subquery scoped by the *other* active filters, matching `applyFilters($q, $filters, except: [...])`) rather than an `INNER JOIN` — a join would silently drop zero-count rows from the result set entirely, which is exactly wrong once a selected-but-zero-count item needs to still appear. The keep-it-visible exception is a plain `$item['count'] > 0 || in_array($item['name'], $selected, true)` check in PHP, not a SQL-level filter. **`scopedByStatus` exists because the two aren't fully identical**: `products_categories` has a `status` column (disabled categories excluded via `->where('status', 1)`), `products_authors` does not — passing this as a bool param rather than silently applying `where('status', 1)` unconditionally would have broken authors (no such column to filter on). That keep-it-visible-unless-zero check plus the `{title, type: 'checkbox', query_key, items}` envelope is itself shared a second level down, in `checkboxGroup(title, queryKey, items, selected)` — `checkboxFilterGroup()` maps its Eloquent results into plain `{name, count}` arrays first, and `statusFilterGroup()` builds its two-item `{name, count}` array by hand (via `filteredBaseQuery()` counts), but both then hand that plain array to the same `checkboxGroup()` for the filter+wrap step, rather than each re-implementing the "hide zero-count unless selected" rule and the response shape separately.
- **`category`/`author`/`status` accept three input shapes**: a bare string (`?category=Fantasy` — `AppHeader`'s mega-menu, `HeroSection`'s category cards, `ProductCard`'s in-card category badge all navigate this way), a **comma-separated string** (`?category=Fantasy,Cooking` — this is what `ProductSidebar.vue`'s multi-select checkboxes actually produce now), or a PHP-style bracket array (`?category[]=Fantasy&category[]=Cooking`, kept for backward compatibility/manual testing). `ProductFilterRequest::prepareForValidation()` normalizes all three into a plain array before the `'array'` rule runs — `explode(',', $value)` when the scalar contains a comma, `[$value]` otherwise. **Comma-separated, not repeated `category=A&category=B` keys, is the deliberate choice for multi-select** — Vue Router's own query stringifier serializes an array-valued `route.query` entry as repeated same-name keys (not PHP's `key[]=` bracket convention), which is fine for Vue Router's own parse/stringify round-trip but reads oddly in the address bar and doesn't match how the rest of this app's query params look; joining the selected values into one comma-separated string before writing to `route.query` keeps the URL a single `key=value` pair, matching every other query param here.
- **A `category`/`author`/`status` value that doesn't match anything real (a typo, hand-edited URL) is harmless server-side** — `whereIn('name', [...])` simply never matches it, no error, no effect on the other valid values in the same list. `price_min`/`price_max` are different: a non-numeric value there used to 422 the *entire* request (the `'numeric'` validation rule rejecting it outright), which meant `filter_groups` and `products` both failed to load from a single bad query param. `ProductFilterRequest::prepareForValidation()` now strips a non-numeric `price_min`/`price_max` from the request *before* validation runs (`$this->query->remove($key)` — note this is `$this->query`, not `$this->request`; for a GET request the query string lives in the `query` bag, `request` is the POST body bag, a distinction that silently no-ops if you remove from the wrong one), so it's treated exactly as if the param was never sent, matching the already-lenient behavior of the array filters instead of erroring.
- **`page` deliberately has no validation rule at all** (not even `'integer'`) — Laravel's own `AbstractPaginator::isValidPageNumber()` already does `filter_var($page, FILTER_VALIDATE_INT) !== false`, falling back to page 1 for anything invalid, before the query ever runs. Adding a `'page' => ['integer']` rule (tried briefly, then reverted the same session) just reintroduces the exact 422-on-garbage-input bug the `price_min`/`price_max` fix above exists to avoid — the framework already does the graceful-fallback job for free, no custom `prepareForValidation()` handling needed or wanted here.

## Caching

`LayoutService::getLayout()` and `HomeService::getHome()` are wrapped in `Cache::tags([...])->remember($key, $ttl, fn () => [...])` (Redis, `CACHE_STORE=redis` / `predis` client, both already set in `.env`) — pattern and TTLs modeled on the user's `srelon/demo-news` project's `CacheService`. `App\Services\CacheService` holds the TTL constants (`TTL_LAYOUT = 900s`, `TTL_HOME = 600s`) and tag constants (`TAG_LAYOUT`, `TAG_HOME`), plus one `flushOnXWrite()` static method per write source.

**Neither half of `GET /api/products` is cached** — `ProductService::getFilteredList()` (the paginated listing) never was, and `getFilterGroups()` lost its cache once its counts became faceted (depend on the active filter combination, see § API response format). Both have the same "too many distinct combinations for decent hit rate" problem, so both are computed fresh per request. This matches the general rule (see CLAUDE.md § Caching): cache general/shared data by default, skip it for highly-parameterized endpoints and say so rather than force a blanket `remember()` everywhere.

**Cache invalidation is wired via each model's `booted()` static hooks** (`static::saved()` / `static::deleted()` calling the matching `CacheService::flushOnXWrite()`), not manual calls from a controller/service — this project's admin writes go through Filament's own generated CRUD (no custom `AdminService` layer intercepting every save like the reference project has), so a model-level hook is the one place guaranteed to fire regardless of whether the write came from Filament, Tinker, or a future API endpoint. Wired on: `Menu`, `ContactInfo` (→ `TAG_LAYOUT`), `ProductsCategory` (→ `TAG_LAYOUT`/`TAG_HOME`, since a category rename affects the layout's category list and any product card showing that category name), `Product`, `ProductStock`, `ProductImage`, `ProductsAuthor` (→ `TAG_HOME`), `NewsPost` (→ `TAG_HOME`).

**Only cache plain arrays, never raw `Collection`/Eloquent model objects** — every cached closure calls `->toArray()` on any `Collection` before returning it (see `LayoutService::getLayout()` / `HomeService::getHome()`, and `formatMenuItem()`'s nested `children` which needs its own `->all()` since `Collection::toArray()` does not recurse into a plain array's own values). This isn't just style: this project's `config/cache.php` has Laravel 13's newer `'serializable_classes' => false` default, which makes Redis's `unserialize()` refuse to reconstruct **any** object read back from the cache — a cached `Collection` silently comes back as `__PHP_Incomplete_Class` on every cache-hit request (first request after a flush works fine since it never round-trips through serialization; every request after that is broken) while cached plain arrays/scalars are unaffected. Fix is to never serialize objects into the cache in the first place, not to loosen `serializable_classes` — that setting is a genuine security control against cache-poisoning object injection, and weakening it project-wide to work around one caching call would be the wrong trade.

## Planned business logic (not implemented yet)

These are specified precisely so the eventual implementation matches what was agreed, not a re-guess:

**Stock availability** — for a given `product_stocks` row, available quantity is:

```
available = product_stocks.quantity
    - SUM(order_items.quantity WHERE product_stock_id = X AND status IN (0, 1, 2, 3))
```

Status `4` (cancelled) is excluded — a cancelled order item releases its reservation. This must be checked in two places: when placing an order (reject/adjust if not enough left) and when showing the cart to the user (silently trim items that are no longer available).

**Real-time stock updates** — whenever an `order_items` row is created or its `status` changes, recompute the available quantity for that product and broadcast it to every connected client for that `product_id`, using the existing Redis pub/sub → `websocket/server.js` pipeline (see CLAUDE.md § Real-time). Frontend product cards/pages should update live without a refresh.

**Search** — a dynamic autocomplete dropdown (type-ahead), scoped by the currently-selected category filter if any, matching against `products.title`, `products.short_description`, `products.description`. Each suggestion shows title → category (e.g. "Astral Journey → Fantasy") and links straight to the product page.

## Not implemented yet

- Filament admin resources for the shop domain (following the `BaseResource`/`BaseEditRecord` pattern) — the only Filament admin UI built so far is the menu editor (`MenuTree`, see § Navigation), which uses a different pattern since it's a third-party tree page, not a `Resource`
- Auth endpoints (login/register/logout) and the rest of the frontend API controllers — `layout` (`GET /api/layout`), `newsletter` (`POST /api/newsletter`), `home` (`GET /api/home`), and `products` (`GET /api/products`), see § API response format, are the only ones built so far
- Filament admin resources for `contact_info` and `faqs`
- `faqs` isn't in the `layout` response — nothing consumes it yet, it'll get its own endpoint (or join `layout`) once a page needs it
- Review likes/reports/notifications handlers (structure is ready, no logic yet)
- Real card payment integration (`orders.txid`/`paid_amount` are reserved for it)
- Importing delivery branches from the carrier API (`delivery_branches`)
