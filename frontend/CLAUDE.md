# CLAUDE.md (frontend)

Frontend-specific conventions for `frontend/` (Vue 3 + TypeScript + Vite public site). See root `CLAUDE.md` for Docker/monorepo-wide rules, and `backend/CLAUDE.md` / `backend/app/Livewire/CLAUDE.md` for the Laravel side.

## Structure

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
    ui/base/           ← BaseButton, BaseInput, BaseSelect, BaseRadioGroup, BaseTabs, BaseSlider, SortSelect
    ui/shop/           ← all page-section components (PageHero, ProductCard, etc.)
    ui/cart/           ← CheckoutStep, CartPopup (shared across cart/checkout views)
    ui/forms/          ← standalone form components (ContactForm, NewsletterForm) — vee-validate + yup, reusable across pages
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

## SCSS rules

- Vite `additionalData` auto-injects `@use "@/assets/scss/variables" as *;` and `@use "@/assets/scss/mixins" as *;` into every component
- **Never redeclare `$color-*` variables inside component `<style>`** — they come from global injection
- Form-field components (`BaseInput`, `BaseSelect`) share label/field/error styling via `@include form-field-label`, `@include form-field-base`, `@include form-field-error-text` from `_mixins.scss` — don't re-declare padding/border/focus styles per component
- `_reset.scss` and `_helpers.scss` each start with `@use 'variables' as *;` to access variables; `main.scss` pulls them together with `@forward 'variables'; @use 'reset'; @use 'helpers';`
- All component styles: `<style lang="scss" scoped>` with BEM naming

## Component rules

- All reusable components in `src/components/ui/` — never tie components to a specific page
- `ui/base/` — generic (BaseButton, BaseInput, BaseTabs, BaseSlider)
- `ui/shop/` — shop-specific but still reusable (ProductCard, ProductSlider, PageHero, etc.)
- `ui/forms/` — standalone forms usable from more than one place (ContactForm, NewsletterForm) — full vee-validate/yup form + submit logic, not just a field
- Views (`views/`) only assemble components — no inline styles, no UI logic
- Static images referenced in JS data → `public/images/` (Vite can't resolve dynamic `src/assets` paths)
- **Never hand-roll a plain `<button>` (or a `<router-link>` styled to look like one) that acts as a site action button (CTA, form submit, "load more", etc.) — always use `BaseButton`.** If an existing variant (`primary`/`outline`/`text`/`dark`/`primary-outline`) doesn't fit, extend `BaseButton` itself (a new variant, a new prop) rather than duplicating its look in a one-off class — a variant can override geometry (radius/padding/font-size), not just color, if that's what the look needs (see `dark`, the pill-shaped newsletter-signup button). A hand-rolled duplicate is invisible until a design change means re-finding every copy across the project by hand — real instances found and fixed: `ProductCard.vue`'s "View Cart" state, `ReviewsPanel.vue`'s "Load More Reviews", `NewsletterForm.vue`'s pill-shaped dark submit button (→ new `dark` variant), `Sidebar.vue`'s and `AboutPage.vue`'s newsletter blocks (were dead `<form @submit.prevent>` stubs with no handler — replaced with `<NewsletterForm />`), `AboutCta.vue`'s "Explore Collection" `router-link` (wrap `BaseButton` inside the link, don't restyle the link itself), and `CartPopup.vue`'s Checkout/Continue-Shopping pair (→ new `primary-outline` variant). Buttons that are legitimately not a BaseButton (icon-only controls, tab/accordion/menu toggles, filter chips, pagination page-number buttons) are the exception.
- `BaseButton`'s `loading?: boolean` prop (default `false`) shows a spinner in place of the slot content — pass the component's own loading ref (`:loading="is_submitting"`) instead of manually toggling the slot text.

## BaseSlider

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

## BaseTabs

Generic tab nav + content component — shared by `ProductPage.vue` (Description/Reviews) and `AuthModal.vue` (Sign In/Sign Up). Never re-implement a tab-button row by hand (same reasoning as the `BaseButton` rule above).

```ts
// Props
tabs: string[]        // tab keys, in display order
modelValue: string     // v-model — the active tab key (standard modelValue/update:modelValue, not model_value)

// Slots
#label="{ tab }"   // optional — customize a tab button's displayed text (defaults to the raw key); used by ProductPage for the dynamic "Reviews (N)" label
#default           // tab content — the parent's own v-if/v-else against its modelValue decides what renders, same as it would without BaseTabs
```

Page-specific framing (`AuthModal`'s no border-top and `ProductPage.vue`'s `border-top` separator + spacing before Related Products) stays external, applied via a `class` prop on the `<BaseTabs>` instance itself — `BaseTabs` only owns the tab-button-row look and the content wrapper, not surrounding page layout. If a caller needs a real v-model over something other than a plain ref (e.g. `AuthModal`'s tab lives in `useAuthStore()`, changed via `open_modal()`), bind through a writable `computed({ get, set })` rather than adding an escape hatch to `BaseTabs` itself.

## Key design decisions

**Book store (current project):**
- **HeroSection** — `hero__top` (grid 1fr 1fr: H1 left, desc right) + `hero__bottom` (grid 2fr 1fr 1fr, min-height 500px); the 3 `hero__card`s are 3 random categories from `useLayoutStore().categories` (reshuffled each page load via a `computed`, not re-shuffled on every re-render), each linking to `{ name: 'products', query: { category } }`, image = category's `image` field via `to_storage_url()`; newsletter block in mid column uses `NewsletterForm.vue` (`ui/forms/`), not inline markup
- **AppHeader** — book SVG logo + "BookStore" text; Categories mega-menu is a separate element LEFT of Home nav link, controlled by `cats_open` ref + mouseenter/mouseleave on wrapper. Nav links, mega-menu categories, and the phone contact are all driven by `useLayoutStore()`, not hardcoded — hovering a mega-menu category swaps `header__mega-promo`'s image to that category's `image` (`hovered_category` ref, falls back to the first category); top-nav items with `children` get a `header__nav-dropdown` flyout (same mouseenter/mouseleave pattern as the categories button)
- **CategoryStrip** — CSS carousel (transform translateX), shows 8 items, auto-advances every 10s; reset without jump uses double `requestAnimationFrame` to skip transition for one frame; categories come from `useLayoutStore().categories` — `total`/`max_index`/`track_width`/`item_width` are `computed`, not plain values derived once, since the category list starts empty and populates async after `fetch_layout()` resolves
- **ProductCard** — extracted reusable component; hover slides action icons in from right (`translateX(60px) → 0`); `aspect-ratio: 2/3` on figure
- **BestAuthorSection** — award badges are 72px circles with `border: 2px solid $color-primary` and text inside — NO SVG icons. Takes the `products_authors` row (highest `SUM(bestseller)` across its products, see `backend/docs/database.md` § API response format) as an `author` prop from `Home.vue` — falls back to the static `/images/best-author-1.webp` when `author.photo` is null, which it is for every seeded author right now
- **BestsellersSection** / **BestRatedSection** / **BlogSection** — title and description configurable via props with defaults; product/post data comes in as a `products`/`posts` prop from `Home.vue`, which fetches `GET /api/home` once in its own `onMounted` (bundles all 4 Home sections in one call, same shape as `useLayoutStore()`'s single `layout` fetch, just page-scoped state instead of a store — see the "Pinia store" rule above) and distributes slices down. These 4 sections stay purely presentational — no store, no fetch of their own.
- **`ui/about/` (`TeamSection`/`AboutPerks`/`AboutCta`)** — same split as Home's sections: `AboutPage.vue` fetches `GET /api/pages/about` once and passes `team`/`perks` + `loading` down to `TeamSection`/`AboutPerks` as props; they stay presentational, no store/fetch of their own. `AboutCta` takes no props at all (fully static copy + a `NewsletterForm`) but is still its own component rather than inline markup in `AboutPage.vue`, matching the same "one section = one component" convention regardless of whether it happens to need data.

**SVG fill in scoped styles** — CSS `fill` on `<svg>` does not reliably cascade to `<path>` in Vue scoped CSS. Always target child elements directly:
```scss
&__icon {
    path, circle { fill: $color-primary; }
}
```

**Card overlays** — always use `position: absolute; inset: 0` on the overlay, never `position: relative; height: 100%` — the latter collapses when the parent gets its height from flex.

**Pinia stores use the Composition API form** (`defineStore('name', () => { ... return {...} })` with `ref`/`computed`), matching `stores/shop.ts` — not the Options API form. Keep this even when porting a pattern from another project that used the Options form.

**A Pinia store is only for data genuinely global across pages** (menu/categories/contacts in `stores/layout.ts`, cart count in `stores/shop.ts`, later: logged-in user) — data that many unrelated components need without a prop chain, and that shouldn't be re-fetched every time a component mounts. **Data scoped to a single page is not a store, even if several sibling components need it** — fetch it once in the page's `views/` component and pass it down to each section via props. Shared TS interfaces for these display shapes go in a domain-named file under `types/` (e.g. `types/shop.ts` — not `types/home.ts`, since `ProductSummary`/`AuthorSummary`/`BlogPostSummary` aren't Home-specific) — not re-declared per component, but also not smuggled into a store just to have somewhere to export them from. **This applies just as much to content shapes that aren't shop-domain at all** (`TeamMember`, `Perk`, `FaqItem`) — those go in `types/global.ts`, not inline in the `.vue` file that happens to use them first. A component's own `Props` interface is the one exception that stays local. Example: `views/Pages/Home.vue` fetches `GET /api/home` once and passes `bestsellers`/`best_author`/`best_rated`/`blog` (typed via `types/shop.ts`) down to `BestsellersSection`/`BestAuthorSection`/`BestRatedSection`/`BlogSection` as props. `bestsellers`/`best_rated` are kept as separate top-level refs in `Home.vue` (not one combined `home_data` blob) so that a future websocket handler patching a single product's live price/stock (planned, not built yet) can find and mutate that product by `slug` in place, without restructuring.

**Products page (`views/Pages/Products/ProductList.vue`)** — `filter_groups` come entirely from the backend (`GET /api/products`'s `filter_groups`, typed as `FilterGroup`/`FilterGroupItem` in `types/shop.ts`). `ProductList.vue` owns *all* `route`/`router` access for the page — it derives `selected` (per-group checkbox/rating state) and `price_min`/`price_max` as plain `computed()`s off `route.query` + `filter_groups` and passes everything down as props. `ProductSidebar.vue` is fully presentational: no `route`/`router` import at all, just renders what it's given and `emit('filter', patch)`s a single-key partial query object on any change; `ProductList.vue`'s `on_sidebar_filter()` is the one place that merges `{ ...route.query, ...patch, page: undefined }` into a `router.replace()`. This replaced an earlier version where `ProductSidebar` had its own `watch(route.query, ...)` mirroring the parent's, plus a `sync_in_progress` flag guarding the two watchers against fighting each other — once "derive from URL" and "write to URL" no longer both happen in the same component, the flag has nothing left to guard against. A single `watch(() => route.query, fetch_products, { immediate: true, deep: true })` in `ProductList.vue` still handles the initial load + every subsequent change for the product/filter_groups fetch. **Changing a filter or the sort dropdown always drops `page` from the query** (`{ ...route.query, ...changes, page: undefined }` — Vue Router omits `undefined` query values from the URL entirely) — `ProductCard`'s in-card category-badge click does the same `page: undefined` drop independently, as a separate direct-navigation case. `PriceFilter.vue` emits on the range inputs' `change` (drag release, not `input`) and on the number inputs' existing `change` handler. Checkbox filter items (`category`/`author`/`status`) with a `count` of `0` are hidden unless that exact item is the one currently selected (so an active filter combining with others into zero results never becomes impossible to un-check) — see `backend/docs/database.md` § API response format. `ProductList.vue`'s `sanitize_query()` runs once `filter_groups` has loaded and strips any `category`/`author`/`status` value not present in that group's real `items` (a hand-edited URL typo) and any non-numeric `price_min`/`price_max`/`page`, via a `patch_query(..., { reset_page: false })` — cleanup, not a real filter change, so it doesn't reset pagination. **`page` gets no backend validation rule at all** — a garbage `?page=asdasd` is already handled for free by Laravel's `AbstractPaginator::isValidPageNumber()` (falls back to page 1), so `ProductFilterRequest` has no `'page'` rule; adding one reintroduces the exact 422-the-whole-request bug the `price_min`/`price_max` handling avoids. On the frontend, `page` is included in `query_key_order` explicitly (pushed last, after `sort_by`) and in `sanitize_query()`'s non-numeric check, matching `price_min`/`price_max`.

**The real cause of `page=` vanishing right after a pagination click was a watcher echo loop in `FilterGroup.vue`, not `sanitize_query()`.** `FilterGroup.vue` mirrors `props.modelValue` into an internal `selected` ref via two watchers (`watch(selected, (val) => emit('update:modelValue', val))` and `watch(() => props.modelValue, (val) => { selected.value = val ?? ... })`). Every fetch — including a plain page navigation with no filter change — reassigns `filter_groups.value` to a **new array from the API response**, which recomputes `ProductList.vue`'s `selected` computed with **new array references** for every checkbox/rating group even when the actual contents haven't changed. That new-but-equal reference triggers the second watcher, reassigns `selected.value` (new reference again), triggers the first watcher, re-emits `update:modelValue` with nothing the user actually changed — cascading up to `ProductList.vue`'s `on_sidebar_filter()`, which calls `patch_query()` **without `reset_page: false`** (default is `reset_page: true`), wiping the `page` pagination had just set. This only fires once `FilterGroup` is already mounted with active watchers — a fresh `/products?page=2` link works fine since `selected`'s initial value is set directly from props at setup time, bypassing the watcher. Fixed at the source: the `props.modelValue` watcher in `FilterGroup.vue` now does a `JSON.stringify` content comparison before reassigning `selected.value`, so an equal-but-new-reference update is a no-op and never re-triggers the emit side — don't re-add reference-equality assumptions to this pair of watchers without the same guard.

**`BasePagination.vue`'s scroll-on-page-change targets the enclosing `<section>`, not the pagination nav itself** (`container.value?.closest('section')`, scrolled to `offsetTop - 120` for the sticky header) — scrolling to the pagination controls' own position put the just-clicked buttons near the viewport top instead of scrolling back to the start of the content. Shared by `ProductList.vue`/`NewsList.vue`/`AuthorList.vue`, all following the same `section.xxx > .container > ...` markup shape.

**`RatingFilter.vue` toggles off on a second click of the same star** (`emit('update:modelValue', props.modelValue === i ? null : i)`) — clicking the already-active rating deselects it.

**`ActiveFilters.vue`** (`ui/filters/`) renders the currently-active filters as removable chips + a "Clear All" button, reusing `ProductList.vue`'s existing `selected`/`price_min`/`price_max`/`filter_groups` props (no new state) — a chip's whole clickable area removes it, price collapses to one combined chip (`$28 - $30`) rather than two, and removing it resets both `price_min`/`price_max` by just clearing the query keys. Both `remove` and `clear` emit the same `Record<string, string | undefined>` patch shape `on_sidebar_filter()` already handles. **Deliberately excludes `sort_by`** — sorting isn't a filter.

**Query params always render in a fixed order** (price, category, author, rating, status, then `sort_by`, `page` last) regardless of which filter the user touches first. `useQueryPatch`'s `patch_query()` takes an optional `order: string[]`; when given, it rebuilds the *entire* merged query into that key order (anything not in the list, like `page`, keeps falling to the end). `ProductList.vue` computes this order directly off `filter_groups` (`group.query_key` per group, `'price'` expanding to `['price_min', 'price_max']`, `sort_by` appended) rather than a hardcoded array — the order is already correct in the backend's response shape.

**`ui/base/SortSelect.vue`** — the "Sort by..." dropdown, extracted out of `ProductList.vue` (`options: {value, label}[]` prop instead of hardcoded `<option>` tags) since Authors/News want the same sort control with their own different option list. Its field styling matches `BaseSelect.vue`/`ContactForm.vue`'s hand-rolled select — one consistent look for every select, not a SortSelect-specific style. **Still not the same component as `ui/base/BaseSelect.vue`** — `BaseSelect` is a labeled form field (validation error slot, meant for actual forms like `ContactForm.vue`) and takes its options via a `<slot>`; `SortSelect` is unlabeled and takes a plain `options` array — don't merge the two just because they're both "a select."

**Site title comes from `VITE_APP_NAME` (`.env`), not a hardcoded string in two places.** `index.html`'s `<title>` uses Vite's built-in `%VITE_APP_NAME%` HTML env-replacement, and `routes/router.ts`'s per-page `document.title = "{page} | {APP_NAME}"` reads the same `import.meta.env.VITE_APP_NAME`. `index.html`'s static title is what briefly shows before the JS bundle finishes loading and Vue Router's `beforeEach` guard sets the real per-page title — it should never be left at Vite's generic scaffold default.

**Checkout wizard (`views/Pages/Cart/CartPage.vue`)** — 3-step flow (Contact → Delivery → Payment) via `CheckoutStep.vue` wrapper (`step_number`, `active`, `done`, `#default`/`#summary` slots, emits `edit`).
- `useCheckoutForm()` holds the three step data refs, persisted to `localStorage`.
- Each step component takes `initial_data` prop, emits `change` (live draft, drives the sidebar `#summary` slot) and `complete` (fires only on explicit "Continue" click, advances `current_step` in `CartPage.vue`).
- `ContactStep.vue` uses vee-validate (`useForm`/`useField`, matches `ContactForm.vue` pattern) — **not** part of `useWizardStep`.
- `DeliveryStep.vue`/`PaymentStep.vue` use the `useWizardStep<T>(initial_data, emit, validator?)` composable instead (plain `reactive` draft + `is_valid` + `on_continue`) — do not hand-roll this pattern again, only these two non-vee-validate steps need it.
- Radio-style method pickers (delivery method, payment method) → `ui/base/BaseRadioGroup.vue` (generic `<script setup generic="T extends string">` component) — never re-duplicate the label/input markup or `__method`/`__method--active` styles per step.
- `BaseButton` has a third `variant="text"` (no background/border, small font, color-only hover) for inline actions like "Edit"/"Edit Items" — use it instead of ad-hoc `<button>` + custom SCSS.

**Reviews: rating is nullable, and notifications deep-link to the exact review/reply.** `rating_avg`/`product.rating` is `null`, not `0`, when a product has no reviews yet — every rating display (`StarRating.vue`, `book-card__rating-badge`, `RatedCard.vue`'s stars) hides itself on a falsy/null rating rather than rendering a 0-star state. `StarRating.vue`'s `v-if` is `rating && count !== 0` specifically so it stays correct even if a future caller passes a `count` of `0` alongside a stale non-null rating. `StarRating.vue`'s `(N reviews)` text is a real `<button>` emitting `click-count`; `ProductPage.vue` wires it to switch `active_tab` to `'Reviews'` and `scrollIntoView` a wrapping `tabs_wrap` ref.
- **Notification → review deep link**: `useNotificationActions.ts::build_link()` builds `query: { tab: 'reviews', pin: <root review id> }` + `hash: '#review-<target id>'` (root id = `notification.parent_id ?? notification.review_id`). Backend: `ReviewController@index` reads `?pin=`, `ReviewService::getPaginated()` adds `ORDER BY CASE WHEN id = ? THEN 0 ELSE 1 END` ahead of the normal sort so the pinned root is always page-1-first regardless of pagination — pin only reorders, the existing "exclude viewer's own root reviews" `WHERE` is untouched.
- `ReviewsPanel.vue` reads `pin`/`hash` off the route, passes `pin` through on every page fetch (not just page 1), and on load/route-change calls `scroll_to_anchor()`: if `#review-<id>` is already in the DOM, scroll+flash it (`useReviewAnchor.ts`'s `scroll_to_review`, the same mechanism `ReviewItem.vue` uses for its own "Replying to X" jump). If not in the DOM (a reply hidden behind "View replies"), dispatch `window` `CustomEvent('review:open-replies', { parent_id, scroll_to })` — every root `ReviewItem.vue` listens, and the one whose id matches sets `show_replies = true` and scrolls/flashes on `nextTick`.
- **`ReviewsPanel.vue` must react to its `product_slug` prop, not just `onMounted`.** Vue Router reuses the same `ProductPage.vue`/`ReviewsPanel.vue` instances when navigating between two `product` routes differing only by `slug` (e.g. clicking a Related Products card) — no remount happens. `watch(() => props.product_slug, ...)` re-subscribes the websocket channel, resets local state, and refetches; `reset_state()`/`load_reviews()` are factored out so `onMounted` and this watcher share one path. Any future per-product child component fetching off a route-derived prop needs this same watcher.
- **Gotcha: `on_review_deleted()`'s `ws:review.deleted` handler never checked `viewer_review` for a root-level delete.** It only filtered the general `reviews` array when `!data.parent_id`, so an admin deleting someone's root review left the "Your review" panel stale until a full reload. Fixed by checking `viewer_review.value?.id === data.id` first and clearing it before falling through to the general-list filter; `pagination.value.total` is deliberately not decremented there since the viewer's own review was never counted in it.
- **Moderator badge**: `ReviewItem.vue` shows a small pill next to the author name when `review.user.is_moderator` is true (`review-item__moderator-badge`, hardcoded green — this design system has no shared "success" token), mirroring the admin panel's badge. Driven by `ReviewResource`'s existing `user.is_moderator` field.
- **Root review cards use `bg-base-100`** (same token as the page body itself), distinguished only by their `border-base-300` outline. Nested reply cards keep a `color-mix(...)` tint plus `ml-8` indent so a reply still reads as nested against its page-matching root.

## Routing — ALWAYS use named routes, never hardcoded path strings

Every internal link/navigation goes through `routes/router.ts`'s route **names** (`{ name: 'products', query: {...} }`, `{ name: 'product', params: { slug } }`), never a literal path string. If a route's path ever changes, a hardcoded string means grepping the whole project; a route name means changing one line in `router.ts`.

- **Reusable card components compute their own route internally from a plain identifier prop (`id`/`slug`) — they don't accept an `href`/path prop from the caller at all.** `ProductCard.vue`/`RatedCard.vue` take an `id`/`slug` prop and compute `const to = computed(() => ({ name: 'product', params: { slug } }))`; `BlogCard.vue` does the same for `{ name: 'post', params: { slug } }`. Callers just pass the slug — they never build a path string themselves.
- **`useShopFilterNav.ts`'s `go_to_filter(route_name, key, value)` takes a route *name*, not a path** — `route.name === route_name` for the "already there, just patch the query" branch, `router.push({ name: route_name, query: { [key]: value } })` otherwise. `ProductCard`/`RatedCard`'s category/author badges pass `'products'`; `BlogCard`'s category badge passes `'news'`.
- **A `view_all_href`/similar CTA-target prop is typed `RouteLocationRaw` (from `vue-router`), not `string`** — see `BestsellersSection.vue`/`BestRatedSection.vue`/`BlogSection.vue`. Default via a factory function (`view_all_href: () => ({ name: 'products', query: { status: 'Bestseller' } })`), same reason object/array prop defaults always need a factory in `withDefaults`.
- **`CartItem.href` (`stores/shop.ts`) is a `RouteLocationRaw`, not a string** — assembled the same way as any card's `to`, even though nothing currently renders a link from it, so it can never drift into a hand-built path string later.
- **`router.ts`'s own route *definitions*, and `redirect`/`beforeEnter` targets, are the one legitimate place path strings still appear** — the `cart` route's `beforeEnter` returns `{ name: 'home' }`, not `'/'`, but the routes array itself still declares `path: 'products'` etc. "Defining what a name maps to" is not the same as "hardcoding a link to that name."
- **A `beforeEnter` guard that reads `useAuthStore().is_authenticated` must `await` the store's `ensure_ready()` first.** `auth_store.user` starts `null` until `fetch_user()` (kicked off from `App.vue`'s `onMounted`) resolves — a synchronous guard on a hard refresh always reads the pre-hydration `false` regardless of a valid session cookie, and bounces the user home. Clicking a link from elsewhere in the SPA doesn't show this, since `user` is already hydrated by then. `stores/auth.ts`'s `ensure_ready()` memoizes the initial `fetch_user()` call in a module-level promise so both `App.vue`'s boot call and any guard awaiting it share the same in-flight request; `beforeEnter: async () => { await useAuthStore().ensure_ready(); if (!useAuthStore().is_authenticated) return { name: 'home' } }` is the pattern.

## SEO — every page loads its own `seo` block (frontend side)

See `backend/CLAUDE.md` § Backend API conventions for `PageService`/`StaticController` (the backend half). `useSeo(seo)` (`composables/useSeo.ts`) is called **once, centrally, in `plugins/axios.ts`'s response interceptor** — never per-page. The interceptor checks `response.data.data.page?.seo ?? response.data.data.seo` (bundle endpoints nest content under a `page` key alongside their own other data — `Home`/`Products`/`Authors`/`News`/`Contact`; the generic `GET /api/pages/{slug}` used by pages with no other backend data — About, Cart — returns the bundle flat, so `seo` is already top-level) and calls `useSeo()` unconditionally; it's a no-op when a response has neither shape. This mirrors the existing global error-toast interceptor in the same file — don't add a second, per-component `useSeo()` call next to a fetch. `useSeo()` sets `document.title` and upserts `<meta name="description">`/`<meta name="keywords">` in `document.head` — there's no vue-meta/unhead dependency, so this is the only mechanism; don't reach for a meta-tag library without discussing it first. **`router.ts`'s `beforeEach` does not set any fallback `document.title`** — a synchronous fallback was tried and reverted since it made the title visibly jump twice per navigation instead of once; `document.title` now only changes when `useSeo()` has real data. `index.html`'s static `%VITE_APP_NAME%` title is what shows until the first `useSeo()` call resolves — intentional, not a gap. A page's meta description/keywords are **not** reset on navigation away from it — a known/accepted gap, not something to fix reactively per-page.

## Validation — ALWAYS use vee-validate + yup

All form validation must use **vee-validate** with **yup** schemas. Never write manual validation logic (custom `computed` flags like `field !== ''`, manual error refs, etc.).

Pattern (matches `ContactForm.vue`):
- `useForm({ validationSchema: object({...}) })` at the component or page level
- Field components use `useField(() => props.name)` internally (like `BaseInput.vue`)
- Schema built with `yup`: use `.min(1, 'message')` for required text fields — **never** `string().required()` alone, because yup v1 passes `''` (empty string) through `required()`. Only `min(1)` reliably catches empty string. For email: `.min(1, '...').email('...')`. For numbers/phones: `.min(N, '...')`.
- Submit fires only when schema is valid (`handleSubmit` from `useForm`)
- Validation messages in English — matches every other UI string: all frontend-facing text must be in English, regardless of what language the request that produced it was written in

## Notifications — ALWAYS use vue-toastification for API request feedback

Every API call's error feedback goes through `vue-toastification`, never a `console.error`/`alert()`/inline-only error state.

- Plugin registered once in `main.ts` (`app.use(Toast, { position: POSITION.TOP_RIGHT, timeout: 4000 })`), CSS imported there too
- **Errors are handled globally, once** — `plugins/axios.ts`'s response interceptor calls `useToast().error(...)` on every failed request (extracts the first message from `{errors: "..."}` or `{errors: {field: [...]}}`, falls back to a generic message for non-JSON/network failures) — don't add a second error toast in the component that made the call
- **Success messages are per-component** — call `useToast().success('...')` explicitly after an action that should confirm success (e.g. `NewsletterForm.vue` after a successful subscribe). Not every successful request needs one — only ones the user directly triggered and would expect confirmation for
- `useToast()` works outside component setup too (e.g. inside `axios.ts`) — it's backed by a global event bus, not Vue's `inject()`

## Skeleton loading states — ALWAYS follow this

Any page/component that fetches its own data asynchronously must ship its loading skeleton in the same change — not as a follow-up once someone notices content jumping. `Home.vue`, `ProductList.vue`, `AuthorList.vue`, and `NewsList.vue` all do this — the moment a page gains a real fetch, it gains a skeleton in the same change, no exceptions.

- **Skeleton markup lives inside the real content component via a `loading?: boolean` prop — never a separate `XSkeleton.vue` file.** See `ProductCard.vue`/`RatedCard.vue`/`BlogCard.vue`: the `loading` branch reuses the component's own real wrapper classes with `ui/base/BaseSkeleton.vue` standing in for image/text content, so a future dimension change to the real card can't silently desync from its skeleton. A parallel `ProductCardSkeleton.vue`-style file was tried and reverted for exactly this reason.
- **Push the `loading` prop down to the smallest reusable child that already renders that data shape** (a card/list-item component) **rather than writing skeleton markup in the page/view or a section component.** A page or section only owns the `is_loading` ref and passes `loading` straight through to the child it already renders in a loop (`v-for="n in 9"` in place of `v-for="product in products"`). Only write page/section-local skeleton markup for the odd bit of chrome with no reusable child of its own (results-count text, a filter-sidebar block).
- `ui/base/BaseSkeleton.vue` is the one shimmer primitive (`width`/`height`/`radius`/`circle` props) — build every skeleton out of it.
- **A section gated by `v-if="data"` because its prop starts out `null`/empty must become `v-if="loading || data"`** — otherwise the section pops into existence once the fetch resolves instead of already being on-screen as a skeleton. See `BestAuthorSection.vue` / `BlogSection.vue`.
- **Data owned by a Pinia store that's fetched once keys its skeleton off the store's own loaded flag** (e.g. `useLayoutStore().loaded`), not a separate page-local ref — see `CategoryStrip.vue` / `HeroSection.vue`.
- The page/view component owns an `is_loading` ref (`true` initially, set `false` in the fetch's `.finally()`) and passes it down to sections as a `loading` prop. For a page whose fetch re-runs on filter/sort/pagination changes (`ProductList.vue`), reset `is_loading = true` at the start of every fetch, not just the first one.
