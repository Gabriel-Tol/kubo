# KUBO Admin Operations and Hosting Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the protected admin workflow for catalog/order operations and prepare the completed MVP for InfinityFree deployment.

**Architecture:** Build on the storefront plan's Shield roles, models, and services. Admin controllers call the same application services as customer flows; inventory and order transitions remain transactional and auditable.

**Tech Stack:** PHP 8.1+, CodeIgniter 4, CodeIgniter Shield, MySQL/MariaDB, PHPUnit 10, InfinityFree shared hosting.

**Spec:** [../specs/2026-10-18-bolu-hangat-design.md](../specs/2026-10-18-bolu-hangat-design.md)

**Prerequisite:** Complete [KUBO Storefront and Customer Order Flow](2026-10-18-bolu-hangat-storefront.md).

## Global Constraints

- All `/admin/*` routes require the Shield `admin` group; customer users receive 403.
- Reuse storefront services; do not duplicate pricing, upload, stock, or transition rules in controllers.
- Accept only allowed status transitions; stock deduction/restoration and logs are atomic.
- Demo payment information must be visibly marked and never treated as real payment data.
- Verify web root, private uploads, `.env`, HTTPS, sessions, database, and routes on the target host.

## Review Focus

- Customer role on admin routes must return 403; assert with admin access feature tests.
- Two admin submissions must not deduct stock twice; assert repeated transition is rejected/idempotent.
- Payment acceptance after stock has become insufficient must not confirm or make stock negative; assert transaction rollback.
- Rejected proof without a reason must preserve order state and fail validation; assert reason requirement.
- Direct web requests to private paths must not reveal files; include deploy smoke-check steps for each path.

---

## File Structure

- `app/Controllers/Admin/`: dashboard, product/category, and order actions.
- `app/Views/layouts/admin.php` and `app/Views/admin/`: dashboard, forms, order queue/detail.
- `app/Services/OrderStatusService.php` and `PaymentProofService.php`: shared, atomic business rules (from storefront plan).
- `app/Config/Routes.php`, `app/Config/Filters.php`, and `app/Config/Session.php`: explicit routes, role filters, session cookie settings.
- `public/assets/css/app.css`: shared design tokens and responsive admin styles.
- `tests/feature/AdminAccessTest.php`, `AdminCatalogTest.php`, `AdminOrderWorkflowTest.php`.
- `docs/deployment/infinityfree.md` and `docs/testing/mvp-blackbox-checklist.md`: deployment and 25+ black-box scenarios.

## Interfaces

- `DashboardService::summary(DateTimeImmutable $day): array` returns `orders_today`, `confirmed_sales`, `proofs_to_review`, `production_queue`, and `recent_orders`.
- Admin product actions use CI4 models and `CatalogService`; never accept client-provided stock/price snapshots for an existing order.
- `PaymentProofService::review(int $proofId, int $adminId, bool $accepted, ?string $reason): void` accepts/rejects a proof and calls `OrderStatusService` within a single transaction.
- `OrderStatusService::transition(int $orderId, string $toStatus, ?int $actorId, ?string $note = null): void` is the sole status/stock mutation interface.

## Task 1: Admin Access, Layout, and Dashboard

**Files:** Modify `app/Config/Routes.php`, `app/Config/Filters.php`, and `app/Config/Session.php`; create `app/Controllers/Admin/Dashboard.php`, `app/Services/DashboardService.php`, `app/Views/layouts/admin.php`, dashboard view, and `tests/feature/AdminAccessTest.php`.

- [ ] Add `testGuestIsRedirectedFromAdmin`, `testCustomerReceives403FromAdmin`, and `testAdminCanViewDashboard`.
- [ ] Run `composer test -- --filter AdminAccessTest`; expected failures because routes/layout/role rules do not exist.
- [ ] Add explicit admin routes with auto-routing disabled, enforce Shield group filters, set secure cookie attributes for HTTPS, and render responsive admin navigation.
- [ ] Query dashboard values from persisted orders/proofs: count today's orders, confirmed product sales excluding delivery fee/cancelled orders, proofs awaiting review, production queue, latest orders.
- [ ] Run focused tests and `composer test`; expected all pass.

## Task 2: Admin Catalog and Category Management

**Files:** Create admin product/category controllers and views; modify catalog models/service as required; create `tests/feature/AdminCatalogTest.php`.

- [ ] Add `testAdminCreatesProductWithActiveVariantAndPrimaryImage`, `testAdminArchivesProductWithoutDeletingHistory`, `testCustomerCannotManageProduct`, and `testCategoryInUseCannotBeDeleted`.
- [ ] Run focused tests and confirm failures.
- [ ] Implement search/filter/status list, create/edit validation, variant and image handling, archive/restore listing, and category add/rename/delete rules. Preserve all order snapshots.
- [ ] Validate image file content and show field-level errors while retaining submitted values.
- [ ] Run focused tests and `composer test`; expected all pass.

## Task 3: Payment Review, Order Queue, and Status Operations

**Files:** Create admin order controller/list/detail views; complete `PaymentProofService.php` and `OrderStatusService.php`; create `tests/feature/AdminOrderWorkflowTest.php` and service tests.

- [ ] Add `testAcceptingProofConfirmsAndDeductsStockOnce`, `testRejectingProofRequiresReasonAndAllowsReupload`, `testInvalidStatusTransitionIsRejected`, `testInsufficientStockRollsBackAcceptance`, and `testCancellationRestoresOnlyPreviouslyDeductedStock`.
- [ ] Run focused tests and confirm failures.
- [ ] Implement chronological proof queue, authorized preview, accept/reject actions, cancellation reason, pickup/delivery-specific transitions, status logs with actor/time, and stock restoration rules in transactions.
- [ ] Add server-side lazy expiry calls to relevant admin order/dashboard requests and customer payment/order requests.
- [ ] Run focused tests and `composer test`; expected all pass.

## Task 4: Deployment Package and Black-Box Checklist

**Files:** Create `docs/deployment/infinityfree.md` and `docs/testing/mvp-blackbox-checklist.md`; update `.gitignore` only if required.

- [ ] Create at least 25 numbered manual scenarios based on PRD acceptance criteria, covering customer, admin, security, responsive states, and invalid inputs.
- [ ] Document local Composer install/build, generated `vendor/` upload, database export/import with phpMyAdmin, `.env` setup, HTTPS/base URL, demo account setup, and rollback/backup notes.
- [ ] Document the selected InfinityFree `htdocs` to `public/` rewrite layout and deny rules; include direct-access checks for `.env`, `app/`, `vendor/`, `writable/`, and proof uploads.
- [ ] Run the checklist against a local server. Include step-by-step checks for the user to run on InfinityFree; mark host checks unverified until the user has deployed and confirmed them. Record host-specific limitations instead of claiming unverified items passed.

---

**Execution order:** Complete Tasks 1–3 only after the storefront plan; Task 4 packages the combined MVP. The repository currently has no Git metadata; commit steps can be added after a Git repository is initialized.

