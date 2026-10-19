# KUBO Storefront and Customer Order Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the customer-facing Bolu Hangat MVP from catalog browsing through account-based checkout, manual proof upload, and order tracking.

**Architecture:** Extend the existing CodeIgniter 4 starter as a server-rendered MVC application. Keep pricing, cart, checkout, stock, proof, and status rules in services backed by MySQL; use plain CSS and lightweight JavaScript.

**Tech Stack:** PHP 8.1+, CodeIgniter 4, CodeIgniter Shield, MySQL/MariaDB, PHPUnit 10, CI4 migrations and views.

**Spec:** [../specs/2026-10-18-bolu-hangat-design.md](../specs/2026-10-18-bolu-hangat-design.md)

## Global Constraints

- Preserve the agreed single-outlet MVP and exclude v1.1/v2 features.
- Use `Asia/Jakarta`, integer rupiah amounts, MySQL InnoDB and `utf8mb4`.
- Recompute price, fee, stock, and slot availability on the server.
- Keep proof files private under `writable/`; accept only JPG/PNG content up to 2 MB.
- Guest cart uses session storage; signed-in cart uses the database and merges on login.
- No Node build step; screens target 360, 768, and 1280 px.

## Review Focus

- Tampered client prices/totals must be ignored; pin with checkout totals tests.
- Archived/inactive products and variants must not be purchasable; pin with catalog and checkout tests.
- Stale stock during cart/checkout must produce a recoverable error and never negative stock; pin with checkout tests.
- An expired order must reject proof submission when next accessed; pin with expiry/proof tests.
- Customer access to another account's order or proof must be denied; pin with feature tests.

---

## File Structure

- `app/Database/Migrations/`: timestamped Shield, shop, and order tables.
- `app/Database/Seeds/DemoShopSeeder.php`: demo categories, products, variants, zones, slots, and settings.
- `app/Models/`: focused CI4 models for catalog, cart, customer, address, order, proof, and status log records.
- `app/Services/`: `CatalogService`, `CartService`, `CheckoutService`, `PaymentProofService`, `OrderStatusService`, and `ExpiryService`.
- `app/Controllers/Shop/`, `app/Controllers/Account/`, and `app/Controllers/Auth/`: customer HTTP flows.
- `app/Views/layouts/store.php`, `app/Views/shop/`, `app/Views/account/`, and `app/Views/auth/`: rendered customer screens.
- `public/assets/css/app.css` and `public/assets/js/store.js`: tokens, responsive styles, and progressive interactions.
- `tests/unit/` and `tests/feature/`: service and HTTP behavior tests.

## Interfaces

- `CatalogService::search(array $filters): array` returns active products and result count; `getBySlug(string $slug): ?array` returns an active product with active variants.
- `CartService::get(?int $userId, string $sessionId): array`, `add(?int $userId, string $sessionId, int $variantId, int $quantity, string $note): void`, and `mergeGuestCart(int $userId, string $sessionId): void`.
- `CheckoutService::placeOrder(int $userId, array $input): array` returns `id`, `order_no`, `status`, `subtotal`, `delivery_fee`, `total`, and `payment_due_at`.
- `PaymentProofService::submit(int $orderId, int $userId, UploadedFile $file): int` returns the new proof ID.
- `OrderStatusService::transition(int $orderId, string $toStatus, ?int $actorId, ?string $note = null): void` validates transitions and applies stock/log changes atomically.
- `ExpiryService::expireIfDue(int $orderId, DateTimeImmutable $now): bool` returns whether it changed the order to cancelled.

## Task 1: Foundation, Authentication, and Demo Data

**Files:** Modify `composer.json`, `composer.lock`, `app/Config/Autoload.php`, `app/Config/Security.php`, and `app/Config/Routes.php`; create Shield configuration, shop migrations/models, `DemoShopSeeder.php`, and `tests/feature/DatabaseSetupTest.php`.

- [ ] Add failing migration/seed assertions: `testShopMigrationsCreateRequiredTables` and `testDemoSeederCreatesSixProductsAndTwoZones`, and `testDemoSeederCreatesAdminFromEnvironment`.
- [ ] Run `composer test -- --filter DatabaseSetupTest`; expected failure because shop schema/seed are absent.
- [ ] Install Shield with `composer require codeigniter4/shield`, run `php spark shield:setup`, and configure `customer`/`admin` groups, Indonesian locale/timezone, and CSRF for state-changing requests. Add migrations for profiles, addresses, catalog, variants/images, zones/slots/settings, cart, orders/items/proofs/logs with the spec's snapshots and indexes.
- [ ] Seed six PRD products, two delivery zones, four slots, demo settings, and categories. Create the demo admin from local `.env` values `DEMO_ADMIN_EMAIL` and `DEMO_ADMIN_PASSWORD`; never commit a fixed password or `.env`.
- [ ] Run the focused test, then `composer test`; expected all existing and new tests pass.

## Task 2: Customer Catalog

**Files:** Create `app/Services/CatalogService.php`, catalog models, `app/Controllers/Shop/{Home,Catalog,Product}.php`, `app/Views/layouts/store.php`, `app/Views/shop/{home,catalog,product}.php`, CSS/JS assets, routes, and `tests/unit/CatalogServiceTest.php` plus `tests/feature/CatalogPagesTest.php`.

- [ ] Add tests `testSearchFiltersSortsAndCountsActiveProducts`, `testArchivedProductIsHidden`, `testProductPageShowsActiveVariants`, and `testUnknownSlugReturns404`.
- [ ] Run focused tests and confirm expected failures.
- [ ] Implement `search(array $filters)` for case-insensitive name search, category, min/max price and name sort; render home, catalog, and product detail with PRD ingredients/allergens, variant price/stock, note counter, and accessible empty/out-of-stock states.
- [ ] Apply the agreed palette/type/spacing and responsive catalog columns; use text status labels and visible keyboard focus.
- [ ] Run focused tests and `composer test`; expected all pass.

## Task 3: Customer Account, Address, and Cart

**Files:** Create account/auth controllers and views, `CartService.php`, profile/address/cart models and routes; modify `app/Config/Routes.php` and `app/Config/Filters.php`; create `tests/feature/CustomerAccountCartTest.php`.

- [ ] Add `testRegistrationRequiresEightCharacterPassword`, `testCustomerCanSaveAddress`, `testGuestCartPersistsAndMergesOnLogin`, and `testCartRejectsInactiveVariantAndExcessQuantity`.
- [ ] Run focused tests and confirm failures.
- [ ] Customize Shield customer login/register/logout; implement profile and address CRUD and cart add/update/remove/clear with a session guest cart, database account cart, and merge-on-login behavior.
- [ ] Render cart empty state, stepper, line totals, note limit, stock-change errors, and login return-to-checkout behavior.
- [ ] Run focused tests and `composer test`; expected all pass.

## Task 4: Checkout, Payment Proof, Tracking, and Expiry

**Files:** Create `CheckoutService.php`, `PaymentProofService.php`, `OrderStatusService.php`, `ExpiryService.php`, checkout/payment/order controllers and views, private upload handler, and `tests/unit/{PricingService,OrderStatusService}Test.php` plus `tests/feature/CustomerOrderFlowTest.php`.

- [ ] Add tests `testPickupHasZeroFee`, `testDeliveryTotalAddsConfiguredZoneFee`, `testClientSubmittedPriceIsIgnored`, `testInsufficientStockDoesNotCreateOrder`, `testProofRejectsInvalidMimeAndOversizeFile`, `testExpiredOrderRejectsProof`, and `testCustomerCannotViewAnotherCustomersOrderOrProof`.
- [ ] Run focused tests and confirm failures.
- [ ] Implement server-side checkout snapshots, payment deadline, private randomized uploads, owner-checked proof delivery, status timeline/history, and lazy expiry. Define the deadline and slots as configurable demo settings.
- [ ] Use database transactions and row-level stock rechecks for all stock/status transitions; rejection requires a reason and permits re-upload before expiry.
- [ ] Run focused tests and `composer test`; expected all pass.

---

**Execution order:** Complete this storefront plan before the Admin and Hosting plan. The repository currently has no Git metadata; commit steps can be added after a Git repository is initialized.

