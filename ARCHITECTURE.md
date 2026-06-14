# Wholesale ERP Management System — Architecture Blueprint

> **Project:** inventoryandsales  
> **Framework:** Laravel 13 + MySQL 8  
> **Domain:** Wholesale distribution — bulk buying, multi-unit pricing, customer credit, barcode-driven operations  
> **Principle:** Event-driven automation with manager-supervised purchasing  
> **Date:** 2026-06-14  

---

## Table of Contents

- A. Business Analysis
- B. Functional Requirements
- C. Non-Functional Requirements
- D. Dynamic Access Control
- E. Database Architecture
- F. ERD Explanation
- G. Module Architecture
- H. Laravel Application Architecture
- I. Inventory Logic Architecture
- J. Wholesale Analytics & Reporting
- K. Notification Architecture
- L. Security Architecture
- M. API Architecture
- N. Deployment Architecture
- O. Risks and Mitigation

---

## A. Business Analysis

### A.1 Stakeholders

| Stakeholder | Wholesale-Specific Interest | Pain Point Addressed |
|---|---|---|
| Shop Owner | Margins, cash flow, customer credit exposure, bulk pricing | Real-time P&L per product, automated credit checks, price list management |
| Sales Team | Fast checkout for bulk orders, customer-specific pricing, credit validation | Barcode-scan bulk pickup, auto-pricing by customer group, instant credit check |
| Warehouse | Receive bulk shipments, manage multi-unit stock (pcs/cartons/pallets), expiry, batch tracking | Barcode receive, auto-conversion between units, FIFO pick suggestions |
| Accountant | Customer debt tracking, supplier payments, multi-tier pricing audits, tax | Auto-generated debtors report, payment reconciliation, tax summary |
| Shop Customers | Wholesale pricing, credit terms, delivery scheduling | Assigned price group, credit limit visibility, order history |
| Suppliers | Bulk purchase orders, delivery scheduling, payment tracking | Auto-PO suggestion, negotiated prices, delivery date tracking |

### A.2 Dynamic Access Groups (CRUD-Based RBAC)

No hardcoded roles. Groups are created dynamically via CRUD. Each group defines:

- **Assigned users**
- **Menu/permission assignments** — which pages and what actions (view, create, edit, delete, approve)

A `Super Admin` group is bootstrapped on installation (cannot be deleted) and has automatic access to everything.

**Example custom groups a shop would create:**

| Group | Menus Granted |
|---|---|
| **Warehouse** | Inventory (view, receive, adjust), Products (view), Purchases (view, receive) |
| **Sales/Cashier** | POS (create, view), Customers (view, create), Sales (view) |
| **Finance** | Payments (view, create), Reports (view), Customers (view, edit credit) |
| **Manager** | All menus except settings — can approve purchase suggestions |
| **Admin** | Full access including settings and user management |

Permission check: `user → groups → group_menus → menu → route + actions`.

### A.3 Wholesale Business Workflows

**Purchase Suggestion → Approval → PO (Semi-Automated):**

```
System detects stock ≤ reorder_level
  → Creates Purchase Suggestion (not an order)
    → Suggestion shows: product, current stock, suggested qty, preferred supplier, estimated cost
  → Manager reviews suggestions (dashboard)
  → Manager approves/rejects/modifies
  → If approved → Purchase Order auto-generated
  → PO sent to supplier (email)
  → Supplier delivers → barcode scan receives stock
  → Stock updated → Batch created → Payment auto-scheduled
```

**Wholesale Order-to-Cash (Customer Pricing Flow):**

```
Customer selected
  → Load customer_group + price_list
  → Load credit_limit + current_balance + payment_terms
  → Barcode scan item
  → Auto-price from price_list_items (customer group × unit × quantity)
  → Add to cart (bulk qty in bottles, cartons, or pallets)
  → Auto-credit check (balance + invoice ≤ limit)
  → If credit ok → complete sale → invoice generated
  → Stock deducted FIFO
  → Invoice auto-emailed
  → If partial payment → remaining tracked as outstanding
```

**Customer Credit Lifecycle:**

```
Customer created with credit_limit and price_group
  → Every sale: balance += invoice_total
  → Every payment: balance -= amount_paid
  → Before each sale: check (balance + new_sale_total) ≤ credit_limit
  → If exceeded: sale BLOCKED at POS — cannot override
  → If overdue > 30 days: auto-flag, restrict further credit
  → Monthly statement auto-generated and emailed
```

**Barcode-Driven Warehouse Operations:**

```
Receiving:
  Supplier delivers → scan PO barcode → scan each product barcode
    → System auto-matches to PO line items
    → Enter batch/expiry → stock auto-updated

Stock checking:
  Scan product barcode → see real-time stock across all units (pcs, cartons, pallets)
    → See batch details, expiry dates, warehouse location

Picking:
  Sales order → system generates pick list (FIFO)
    → Warehouse scans each picked item → auto-confirm
```

**Auto-Returns Processing:**

```
Scan return item barcode
  → System pulls original sale (auto by SKU/batch)
  → Condition selected: good / damaged / expired
  → If good → auto-restock to original batch
  → If damaged/expired → auto-move to write-off batch
  → Refund auto-calculated per return policy
  → Customer credit auto-adjusted
```

**Auto-Inventory Intelligence:**

```
Every stock movement triggers:
  → Reorder level check (if ≤ reorder → create purchase suggestion)
  → Stock velocity update (fast/slow/dead classification)
  → Expiry check (if ≤ 30 days → alert)
  → Stock valuation recalculation (FIFO)
```

**Auto-Report Generation:**

Daily 00:05 → Sales summary, collections, credit exposure.  
Weekly Sunday 22:00 → Stock intelligence, slow movers, dead stock.  
Monthly 1st 02:00 → P&L, supplier performance, customer aging.

### A.4 Business Rules (All Auto-Enforced)

1. Credit limit check before EVERY sale — cannot be bypassed
2. Negative stock not possible — CHECK constraint + app-level lock
3. Every stock movement creates immutable audit record
4. Expired batches cannot be sold — POS refuses scan
5. Multi-unit conversions enforced at database level (e.g., 1 carton = 24 bottles)
6. Price determined by customer_group + price_list + quantity — no manual price entry at POS
7. Purchase suggestions require Manager approval before PO creation
8. All price changes logged with before/after values
9. Soft deletes on all entities; permanent deletion never occurs
10. Session auto-expires after 30 min inactivity

---

## B. Functional Requirements

### B.1 Authentication (MOD-AUTH)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| AUTH-01 | Email + password login | User action | users table |
| AUTH-02 | Password hashing (Bcrypt 12 rounds) | Auto on create/update | — |
| AUTH-03 | Session management (database driver, 30-min TTL) | Auto on login | sessions table |
| AUTH-04 | Password change | User action | AUTH-01 |
| AUTH-05 | Password reset email | Auto on request | mail config |
| AUTH-06 | Rate-limit login attempts (5/min) | Auto-enforced | cache |
| AUTH-07 | Logout from all devices | User action | sessions table |
| AUTH-08 | 2FA auto-enforced for groups with 2FA flag | Auto on login | 2FA module |
| AUTH-09 | Failed login auto-logged | Auto on failure | audit_logs |
| AUTH-10 | Session auto-terminate after 30min idle | Auto cron job | sessions table |

### B.2 User Management (MOD-USER)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| USR-01 | User invite with group assignment | Admin form | mail, groups |
| USR-02 | Inactive user (90 days) auto-suspended | Cron (daily) | users, audit_logs |
| USR-03 | User activity log auto-recorded | Auto on every action | audit_logs |

### B.3 Group & Menu Management (MOD-GROUP)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| GRP-01 | Create group with menu permissions (view/create/edit/delete/approve per menu) | Form | menus table |
| GRP-02 | Edit group — reassign menus and permissions | Form | GRP-01 |
| GRP-03 | Assign/remove users to/from group | User picker | GRP-01, users |
| GRP-04 | Clone group (copy menu config from existing group) | Clone button | GRP-01 |
| GRP-05 | Delete group (only if no users assigned) | Delete action | GRP-01 |
| GRP-06 | Permission cache auto-flushed when group changes | Auto on save | GRP-01 |
| GRP-07 | Super Admin group bootstrapped on install (cannot delete) | Install command | — |

### B.4 Product Management (MOD-PROD) — Wholesale Multi-Unit

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| PROD-01 | Product created with auto-SKU + auto-barcode | Form or CSV import | categories, units |
| PROD-02 | Barcode image auto-generated (Code128 PNG) | Auto on product create | PROD-01 |
| PROD-03 | Barcode label printable (multiple sizes: 2×1, 4×2 per sheet) | Print button | PROD-02 |
| PROD-04 | Multiple units per product (bottle/carton/pallet) with conversion factors | Form | PROD-01, units |
| PROD-05 | Each unit has its own purchase price, selling price | Form | PROD-04 |
| PROD-06 | Price lists created and assigned to customer groups | Form | price_lists |
| PROD-07 | Price per unit per price list with minimum quantity tiers | Form | PROD-06, units |
| PROD-08 | Product images | Upload | PROD-01 |
| PROD-09 | Product categories (tree, parent-child) | Form | categories |
| PROD-10 | Inactive product auto-flagged (no sale in 6 months) | Cron (monthly) | sales |

### B.5 Category Management (MOD-CAT)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| CAT-01 | Category created (name, slug, parent) | Form | — |
| CAT-02 | Category updated | Form | CAT-01 |
| CAT-03 | Empty category auto-archived after 3 months | Cron (weekly) | CAT-01 |
| CAT-04 | Category tree auto-built from parent-child | Auto on access | CAT-01 |

### B.6 Supplier Management (MOD-SUP)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| SUP-01 | Supplier created (name, contact, tax ID, payment terms) | Form | — |
| SUP-02 | Supplier updated | Form | SUP-01 |
| SUP-03 | Negotiated prices per product per supplier tracked | Form | SUP-01, products |
| SUP-04 | Supplier performance auto-computed (on-time delivery %, quality rate) | Auto after each delivery | purchases, purchase_returns |
| SUP-05 | Supplier ranking auto-updated (lead time, cost competitiveness) | Cron (weekly) | purchases |

### B.7 Purchase Management (MOD-PURCHASE) — Suggestion + Approval

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| PUR-01 | Purchase suggestion auto-created when stock ≤ reorder_level | Auto after stock movement | inventory |
| PUR-02 | Suggestion includes: product, current stock, suggested qty, preferred supplier, unit cost, total est. | Auto | suppliers, products |
| PUR-03 | Manager reviews all pending suggestions in dashboard | Page load | PUR-01 |
| PUR-04 | Manager approves → Purchase Order auto-generated | Approve button | PUR-03 |
| PUR-05 | Manager modifies suggestion (qty, supplier) before approval | Edit then approve | PUR-03 |
| PUR-06 | Manager rejects suggestion with reason — stock alert dismissed | Reject button | PUR-03 |
| PUR-07 | PO auto-sent to supplier via email | Auto on PO creation | mail |
| PUR-08 | Supplier can confirm delivery date (future: supplier portal) | Email reply / form | PUR-07 |
| PUR-09 | Stock received via barcode scan → auto-match to PO line items | Barcode scan | PUR-07, products |
| PUR-10 | Batch/lot + expiry captured on receive | Scan or manual | PUR-09 |
| PUR-11 | Partial receiving supported (qty_received tracked per line) | Auto on scan | PUR-09 |
| PUR-12 | PO status workflow: suggestion → approved → sent → partial → received → completed → cancelled | Auto | PUR-01..11 |
| PUR-13 | Supplier payment auto-scheduled based on negotiated payment terms | Auto on receive | PUR-09, suppliers |

### B.8 Inventory Management (MOD-INV) — Wholesale Intelligence

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| INV-01 | Stock received via barcode scan (purchase fulfillment) | Barcode scan | purchases, products |
| INV-02 | Stock displayed in all units (pcs, cartons, pallets) | Page load | products, product_units |
| INV-03 | Real-time stock per batch with FIFO valuation | Page load | inventory, batches |
| INV-04 | Stock adjustment (discrepancy within ±2% auto-approved) | Stocktake | inventory |
| INV-05 | Low stock auto-creates purchase suggestion | Auto on movement | inventory |
| INV-06 | Expiry alerts at 30/14/7 days | Cron (daily) | batches |
| INV-07 | Stock intelligence: fast/slow/dead computed weekly — based on 30/90/180 day sales velocity | Cron (weekly) | stock_movements, sales |
| INV-08 | Stock valuation auto-computed (FIFO method) | Real-time / cron | inventory, batches |
| INV-09 | Stocktake cycle counting per velocity zone (A=weekly, B=monthly, C=quarterly) | Cron (various) | inventory |
| INV-10 | Inventory turnover ratio auto-calculated | Cron (weekly) | inventory, sales |

### B.9 Customer Management (MOD-CUST) — Wholesale Credit

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| CUST-01 | Customer created with customer_group, credit_limit, payment_terms | Form | customer_groups |
| CUST-02 | Customer auto-created at POS (phone number) | POS sale | — |
| CUST-03 | Customer type: Retailer / Wholesaler / Distributor | Form / auto | customer_groups |
| CUST-04 | Price group assignment → determines which price_list applies | Form/auto | price_lists |
| CUST-05 | Credit limit enforced at POS (balance + invoice ≤ limit) | Auto on each sale | CUST-01 |
| CUST-06 | Outstanding balance auto-updated on every sale/payment | Auto | sales, payments |
| CUST-07 | Payment terms (e.g., Net 15, Net 30) tracked | Form | CUST-01 |
| CUST-08 | Customer auto-flagged if overdue > 30 days | Cron (daily) | sales, payments |
| CUST-09 | Customer statement (transactions, balance, aging) auto-generated | Page load / cron | sales, payments |
| CUST-10 | Customer purchase history auto-available | Customer lookup | sales |
| CUST-11 | Top customers auto-ranked by revenue/spend | Cron (monthly) | sales |

### B.10 Sales Order Module (MOD-ORDER)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| ORD-01 | Sales order created (customer, items, quantities, unit prices) | POS | customers, products |
| ORD-02 | Stock availability auto-checked on order creation | Auto | inventory |
| ORD-03 | Order supports status workflow: pending → confirmed → processing → completed → cancelled | Auto | ORD-01 |
| ORD-04 | Pending orders tracked in dashboard | Page load | ORD-01 |
| ORD-05 | Partial fulfillment (backorder) supported | Auto on pick | ORD-03 |
| ORD-06 | Invoice generated on order completion | Auto | ORD-03 |
| ORD-07 | Cancelled orders restore stock reservation | Auto on cancel | ORD-03 |

### B.11 Sales / POS (MOD-POS) — Wholesale Barcode Flow

**Target: scan-to-receipt < 500ms.** Optimized for bulk quantities (cartons/pallets), customer pricing, credit control.

| ID | Description | Speed | Dependencies |
|---|---|---|---|
| POS-01 | Customer selected → load price group, credit limit, terms | <100ms | customers, customer_groups |
| POS-02 | Barcode scan → product + unit + price resolved from Redis cache | <50ms | Redis, products |
| POS-03 | Quantity entered (can be in any unit — bottle/carton/pallet) | instant | product_units |
| POS-04 | Price auto-calculated from price_list_items (customer group × unit × qty) | <10ms | price_lists, price_list_items |
| POS-05 | Credit check: limit ≥ balance + invoice total — auto-block if exceeded | <10ms | customer |
| POS-06 | Cart managed in Redis (TTL 24h for held orders) | <30ms/item | Redis |
| POS-07 | Payment processed (cash/mobile/credit/partial) + change calc | <100ms | payments |
| POS-08 | Invoice auto-generated (pre-compiled template, browser print) | <80ms | — |
| POS-09 | Invoice auto-emailed to customer (queued) | queued | mail |
| POS-10 | Stock auto-deducted FIFO in same DB transaction | <100ms | inventory |
| POS-11 | Sales history auto-displayed per customer | <200ms | sales |
| POS-12 | Void sale (reason logged, credit auto-reversed, stock restored) | <200ms | sales |

**Barcode scanning implementation:**
- USB scanner in keyboard wedge mode — no SDK, no driver, no API
- JS `keypress` capture → Redis cache lookup → 50μs response
- Dedicated barcode input field always auto-focused on POS load
- Preload: on POS boot, warm Redis with all active barcode mappings

### B.12 Payments (MOD-PAY)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| PAY-01 | Payment recorded against sale (full or partial) | POS / form | sales |
| PAY-02 | Payment methods: cash, mobile money, bank transfer, credit | Form | — |
| PAY-03 | Partial payment: invoice remains partially paid, balance tracked | Auto | PAY-01 |
| PAY-04 | Payment receipt auto-generated + emailed | Auto on payment | PAY-01 |
| PAY-05 | Customer outstanding balance auto-updated | Auto | PAY-01 |
| PAY-06 | Payment history per customer/sale auto-available | Page load | payments |

### B.13 Returns (MOD-RET)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| RET-01 | Customer return processed via barcode scan → original sale lookup | Barcode scan | sales |
| RET-02 | Condition: good → auto-restock; damaged/expired → write-off | Button press | RET-01 |
| RET-03 | Refund auto-calculated; credit adjusted or cash refunded | Auto | RET-01 |
| RET-04 | Supplier return processed linking to original purchase | Barcode scan | purchases |

### B.14 Reporting (MOD-REP)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| REP-01 | Sales report (daily/weekly/monthly/custom range, by customer or product) | Cron + auto-push | sales, sale_items |
| REP-02 | Profit report (revenue, COGS, gross profit, margin % per product) | Cron (monthly) | sales, purchases |
| REP-03 | Stock report (current stock, value, turnover, low stock, dead stock) | Cron (weekly) | inventory, batches |
| REP-04 | Inventory valuation (FIFO — total stock value by product) | Real-time | inventory, batches |
| REP-05 | Customer debt/aging report (outstanding balances, overdue days) | Cron (daily) | customers, payments |
| REP-06 | Supplier report (purchase history, lead time, quality, pricing) | Cron (monthly) | purchases, suppliers |
| REP-07 | Purchase report (POs by status, spending by supplier, delivery trends) | Cron (monthly) | purchases |
| REP-08 | Payment report (collections by method, by customer, aging) | Cron (weekly) | payments |
| REP-09 | Tax summary (VAT/sales tax collected, payable) | Cron (monthly/quarterly) | sales |
| REP-10 | Product profitability (revenue, COGS, margin, turnover per product) | Cron (monthly) | sales, purchases |
| REP-11 | Sales trends (day-over-day, week-over-week, year-over-year) | Cron (daily) | sales |

### B.15 Notifications (MOD-NOTIF)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| NOT-01 | Purchase suggestion created (notify Manager) | Auto on suggestion | purchases |
| NOT-02 | Low stock alert (notify Warehouse) | Auto on movement | inventory |
| NOT-03 | Expiry warning at 30/14/7 days | Cron (daily) | batches |
| NOT-04 | Credit limit warning (>80% utilization) | Auto on sale | customers |
| NOT-05 | Customer overdue flag (auto-push to finance) | Cron (daily) | sales, payments |
| NOT-06 | Invoice emailed to customer | Auto on sale | mail |
| NOT-07 | Payment receipt emailed | Auto on payment | mail |
| NOT-08 | Daily sales summary to dashboard + email | Cron (00:05) | sales |
| NOT-09 | Stock intelligence report (slow movers, dead stock) | Cron (weekly) | inventory |

### B.16 Audit Logging (MOD-AUDIT)

| ID | Description | Trigger | Dependencies |
|---|---|---|---|
| AUD-01 | All CRUD operations auto-logged (before/after JSON) | Auto on model events | all tables |
| AUD-02 | Authentication events (login, logout, failure) | Auto on auth | — |
| AUD-03 | Price changes (old price → new price, who changed) | Auto on price update | products, price_list_items |
| AUD-04 | Stock movements (qty_before, qty_after, who, reference) | Auto on stock movement | inventory |
| AUD-05 | Credit limit changes (old limit → new limit, who) | Auto on customer update | customers |
| AUD-06 | Audit logs auto-archived after 12 months | Cron (monthly) | audit_logs |

### B.17 Settings (MOD-SETTINGS)

| ID | Description |
|---|---|
| SET-01 | Business info (name, address, phone, tax ID, logo) |
| SET-02 | Tax rate(s) configuration |
| SET-03 | Currency symbol and format |
| SET-04 | Default payment terms for new customers |
| SET-05 | Reorder level auto-calibration (on/off, safety stock factor) |
| SET-06 | Invoice template settings (logo position, footer, color) |
| SET-07 | Barcode label settings (label size, per-sheet count, margins) |
| SET-08 | Notification preferences (which events send email) |

---

## C. Non-Functional Requirements

### C.1 Security

| Requirement | Enforcement |
|---|---|
| Password hashing | Bcrypt 12 rounds — auto on every password set |
| Session | Database driver, 30-min TTL, secure+httponly cookies |
| CSRF | Laravel tokens on all POST/PUT/DELETE |
| XSS | Blade auto-escaping |
| SQL injection | Eloquent ORM — auto |
| Rate limiting | 5 login/min, 60 API/min |
| Input validation | FormRequest per endpoint |
| Access control | Dynamic group-menu middleware on every route |
| Audit trail | Observer trait on all critical models |
| 2FA | Per-group flag (can be toggled for any group) |
| Encryption at rest | AES-256-CBC for sensitive columns |
| Encryption in transit | TLS 1.3 |

### C.2 Performance

| Target | Mechanism |
|---|---|
| POS scan-to-receipt < 500ms | Redis barcode cache, in-memory pricing, single DB transaction |
| Page load < 2s | Cached queries, eager loading, pagination (50/page) |
| Report < 5s (3mo range) | Pre-generated by cron, results cached |
| DB queries < 100ms (95%) | Auto-indexed FKs, search columns, composite indexes |

### C.3 Reliability

| Mechanism | Detail |
|---|---|
| Data integrity | DB transactions for all stock/sales operations |
| Backup | Auto daily dump + binlog every 10 min |
| Queue | DB driver, auto-retry failed jobs (3 attempts) |
| Idempotency | UUID primary keys |
| Health checks | Auto every 30s — failed instance auto-replaced |

### C.4 Scalability

| Aspect | Approach |
|---|---|
| App servers | Stateless → auto-scaling group behind Nginx LB |
| Database | Master + read replicas for reports |
| Queue | Workers scale with queue depth |
| Cache | Redis cluster (auto-failover) |

### C.5 Maintainability

| Practice | Automation |
|---|---|
| Testing | CI auto-runs PHPUnit + Pint on every push |
| Deployment | CI/CD auto-deploy on main branch push |
| Dependencies | Dependabot auto-PRs |
| Logging | Structured JSON → daily rotated files |

---

## D. Dynamic Access Control (Menu + Permission CRUD)

### D.1 Data Model

```
groups                   group_menus              menus
┌──────────────┐         ┌────────────────┐       ┌──────────────────┐
│ id            │──┐     │ group_id        │──┐    │ id               │
│ name          │  │     │ menu_id         │  └────│ parent_id        │
│ description   │  │     │ can_view (bool) │       │ name             │
│ is_super_admin│  │     │ can_create(bool)│       │ route            │
│ is_active     │  │     │ can_edit (bool) │       │ icon             │
│ created_at    │  │     │ can_delete(bool)│       │ sort_order       │
└──────────────┘  │     │ can_approve(bool)│       │ is_active        │
                  │     │ can_2fa(bool)   │       └──────────────────┘
                  │     └────────────────┘
    group_user    │
    ┌─────────────────┐
    │ group_id         │
    │ user_id          │
    └─────────────────┘
```

### D.2 Access Check

```
User → load all groups → load all group_menus (merged: union of permissions)
  → Cached in Redis key "user.menus.{id}" TTL 1 hour
  → On page load → middleware checks route → menu → can_view
  → UI conditionally shows/hides CRUD buttons based on can_create/edit/delete
```

### D.3 Notes

- `can_approve` is used for purchase suggestion approval and other manager workflows
- `can_2fa` forces TOTP on login for members of that group
- When group permissions change → auto-clear Redis cache for all group members
- Super Admin group is bootstrapped on install and cannot be deleted

---

## E. Database Architecture

### E.1 Design Principles

1. Every table: `id`, `uuid`, `created_at`, `updated_at`, `deleted_at`
2. Monetary values: `decimal(15,2)`
3. Quantities: `decimal(15,3)` — supports fractional units
4. All foreign keys auto-indexed
5. All search columns (name, SKU, barcode, phone) auto-indexed
6. UUIDs for all public-facing IDs (prevents enumeration attacks)
7. Timestamps in UTC (Laravel default)

### E.2 Schema

---

#### `users`

| Column | Type | Constraints | Auto |
|---|---|---|---|
| id | bigint unsigned | PK | auto |
| uuid | char(36) | UNIQUE NOT NULL | auto |
| name | varchar(255) | NOT NULL | |
| email | varchar(255) | UNIQUE NOT NULL | |
| email_verified_at | timestamp | NULLABLE | auto on verify |
| phone | varchar(20) | NULLABLE | |
| password | varchar(255) | NOT NULL | bcrypt 12 |
| is_active | tinyint(1) | DEFAULT 1 | auto-suspend 90d |
| last_login_at | timestamp | NULLABLE | auto |
| last_login_ip | varchar(45) | NULLABLE | auto |
| created_at | timestamp | | auto |
| updated_at | timestamp | | auto |
| deleted_at | timestamp | NULLABLE | |

**Relationships:** BelongsToMany(groups)

---

#### `groups`, `group_user`, `menus`, `group_menu`

(Same schema as Section D.1)

---

#### `categories`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| parent_id | bigint unsigned | FK → categories.id NULLABLE |
| name | varchar(150) | NOT NULL |
| slug | varchar(150) | UNIQUE NOT NULL |
| description | text | NULLABLE |
| is_active | tinyint(1) | DEFAULT 1 |
| sort_order | int | DEFAULT 0 |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** BelongsTo(parent), HasMany(children), HasMany(products)

---

#### `units`

Seeded: Piece, Bottle, Carton, Pallet, Box, Kg, Liter, Meter

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar(50) | NOT NULL (e.g., "Carton") |
| short_code | varchar(10) | UNIQUE NOT NULL (e.g., "ctn") |
| is_base | tinyint(1) | DEFAULT 0 (smallest sellable unit) |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

---

#### `products`

| Column | Type | Constraints | Auto Behavior |
|---|---|---|---|
| id | bigint unsigned | PK | |
| uuid | char(36) | UNIQUE NOT NULL | auto |
| category_id | bigint unsigned | FK → categories.id | |
| name | varchar(255) | NOT NULL | |
| slug | varchar(255) | UNIQUE NOT NULL | auto from name |
| sku | varchar(100) | UNIQUE NOT NULL | auto (CAT-0001) |
| barcode | varchar(100) | UNIQUE NULLABLE | auto (Code128 numeric) |
| barcode_image | varchar(255) | NULLABLE | auto-generated PNG path |
| description | text | NULLABLE | |
| tax_rate | decimal(5,2) | DEFAULT 0.00 | auto from settings |
| tax_inclusive | tinyint(1) | DEFAULT 1 | |
| is_active | tinyint(1) | DEFAULT 1 | auto-flag 6mo no sale |
| track_stock | tinyint(1) | DEFAULT 1 | |
| reorder_level | decimal(15,3) | DEFAULT 0 | auto-calibrated |
| image | varchar(255) | NULLABLE | |
| weight | decimal(10,3) | NULLABLE (kg) | |
| created_at | timestamp | | auto |
| updated_at | timestamp | | auto |
| deleted_at | timestamp | NULLABLE | |

**Indexes:** UNIQUE(uuid, sku, barcode, slug), INDEX(category_id), FULLTEXT(name, description)

**Relationships:** BelongsTo(category), HasMany(product_units), HasMany(batches), HasMany(inventory)

---

#### `product_units`

Each product has one or more units with conversion factors.

*Example: Product "Beverage Water" — 1 Carton = 24 Bottles, 1 Pallet = 48 Cartons*

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| product_id | bigint unsigned | FK → products.id CASCADE |
| unit_id | bigint unsigned | FK → units.id |
| conversion_factor | decimal(15,3) | NOT NULL (how many base units = 1 of this) |
| purchase_price | decimal(15,2) | NULLABLE (cost to buy this unit from supplier) |
| selling_price | decimal(15,2) | NULLABLE (default sell price for this unit) |
| wholesale_price | decimal(15,2) | NULLABLE |
| bulk_price | decimal(15,2) | NULLABLE |
| is_default_sale | tinyint(1) | DEFAULT 0 |
| is_default_purchase | tinyint(1) | DEFAULT 0 |
| barcode | varchar(100) | UNIQUE NULLABLE (per-unit barcode if different) |
| created_at | timestamp | |
| updated_at | timestamp | |

**Relationships:** BelongsTo(product), BelongsTo(unit)

---

#### `customer_groups`

*Examples: Retailer, Wholesaler, Distributor, VIP*

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| name | varchar(100) | NOT NULL |
| description | text | NULLABLE |
| default_credit_limit | decimal(15,2) | DEFAULT 0 |
| default_payment_terms | varchar(50) | NULLABLE (e.g., "Net 15") |
| is_active | tinyint(1) | DEFAULT 1 |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** HasMany(customers), HasMany(price_lists)

---

#### `price_lists`

Named price lists tied to customer groups.

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| name | varchar(150) | NOT NULL (e.g., "Wholesale Standard") |
| customer_group_id | bigint unsigned | FK → customer_groups.id |
| is_active | tinyint(1) | DEFAULT 1 |
| valid_from | date | NULLABLE |
| valid_to | date | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** BelongsTo(customer_group), HasMany(price_list_items)

---

#### `price_list_items`

Per-unit pricing per price list with quantity tiers.

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| price_list_id | bigint unsigned | FK → price_lists.id CASCADE |
| product_id | bigint unsigned | FK → products.id |
| product_unit_id | bigint unsigned | FK → product_units.id |
| minimum_quantity | decimal(15,3) | NOT NULL DEFAULT 1 (tier start) |
| price | decimal(15,2) | NOT NULL |
| created_at | timestamp | |
| updated_at | timestamp | |

**Unique:** UNIQUE(price_list_id, product_id, product_unit_id, minimum_quantity)

**Indexes:** INDEX(price_list_id, product_id)

**Relationships:** BelongsTo(price_list), BelongsTo(product), BelongsTo(product_unit)

*Pricing engine logic: SELECT price FROM price_list_items WHERE price_list_id = X AND product_id = Y AND product_unit_id = Z AND minimum_quantity ≤ qty ORDER BY minimum_quantity DESC LIMIT 1*

---

#### `suppliers`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| name | varchar(255) | NOT NULL |
| contact_person | varchar(255) | NULLABLE |
| email | varchar(255) | NULLABLE |
| phone1 | varchar(20) | NOT NULL |
| phone2 | varchar(20) | NULLABLE |
| address | text | NULLABLE |
| city | varchar(100) | NULLABLE |
| tax_id | varchar(50) | NULLABLE |
| payment_terms | varchar(100) | NULLABLE |
| is_active | tinyint(1) | DEFAULT 1 |
| performance_score | decimal(5,2) | DEFAULT 0 — auto-computed |
| avg_lead_time_days | int | DEFAULT 0 — auto-computed |
| notes | text | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** HasMany(purchases)

---

#### `purchase_suggestions`

Created automatically when stock ≤ reorder_level. Requires manager approval.

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| product_id | bigint unsigned | FK → products.id |
| product_unit_id | bigint unsigned | FK → product_units.id |
| supplier_id | bigint unsigned | FK → suppliers.id |
| suggested_quantity | decimal(15,3) | NOT NULL |
| current_stock | decimal(15,3) | NOT NULL |
| estimated_unit_cost | decimal(15,2) | NOT NULL |
| estimated_total | decimal(15,2) | NOT NULL |
| status | varchar(20) | DEFAULT 'pending' — pending → approved → rejected → ordered |
| reviewed_by | bigint unsigned | FK → users.id NULLABLE |
| reviewed_at | timestamp | NULLABLE |
| manager_notes | text | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Indexes:** INDEX(product_id, status), INDEX(status)

**Relationships:** BelongsTo(product), BelongsTo(supplier), BelongsTo(user, reviewed_by)

---

#### `purchases`

Created when a purchase suggestion is approved.

| Column | Type | Constraints | Auto |
|---|---|---|---|
| id | bigint unsigned | PK | |
| uuid | char(36) | UNIQUE NOT NULL | auto |
| reference_no | varchar(50) | UNIQUE NOT NULL | auto (PO-2026-0001) |
| purchase_suggestion_id | bigint unsigned | FK → purchase_suggestions.id NULLABLE | |
| supplier_id | bigint unsigned | FK → suppliers.id | |
| approved_by | bigint unsigned | FK → users.id | |
| status | varchar(20) | DEFAULT 'pending' | pending → sent → partial → received → completed → cancelled |
| order_date | date | NOT NULL | auto today |
| expected_date | date | NULLABLE | auto from supplier |
| received_date | date | NULLABLE | auto on receive |
| subtotal | decimal(15,2) | NOT NULL | auto |
| tax_amount | decimal(15,2) | DEFAULT 0 | auto |
| discount_amount | decimal(15,2) | DEFAULT 0 | auto |
| total_amount | decimal(15,2) | NOT NULL | auto |
| paid_amount | decimal(15,2) | DEFAULT 0 | auto |
| notes | text | NULLABLE | |
| created_at | timestamp | | auto |
| updated_at | timestamp | | auto |
| deleted_at | timestamp | NULLABLE | |

**Indexes:** UNIQUE(uuid, reference_no), INDEX(supplier_id, status)

**Relationships:** BelongsTo(supplier), HasMany(purchase_items)

---

#### `purchase_items`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| purchase_id | bigint unsigned | FK → purchases.id CASCADE |
| product_id | bigint unsigned | FK → products.id |
| product_unit_id | bigint unsigned | FK → product_units.id |
| quantity_ordered | decimal(15,3) | NOT NULL |
| quantity_received | decimal(15,3) | DEFAULT 0 |
| unit_cost | decimal(15,2) | NOT NULL |
| subtotal | decimal(15,2) | NOT NULL |
| created_at | timestamp | |
| updated_at | timestamp | |

**Relationships:** BelongsTo(purchase), BelongsTo(product)

---

#### `batches`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| product_id | bigint unsigned | FK → products.id |
| purchase_item_id | bigint unsigned | FK → purchase_items.id NULLABLE |
| batch_no | varchar(100) | NULLABLE |
| expiry_date | date | NULLABLE |
| manufactured_date | date | NULLABLE |
| initial_quantity | decimal(15,3) | NOT NULL |
| remaining_quantity | decimal(15,3) | NOT NULL |
| unit_cost | decimal(15,2) | NOT NULL |
| is_expired | tinyint(1) | DEFAULT 0 — auto-set by cron |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** BelongsTo(product), HasMany(inventory)

---

#### `inventory`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| product_id | bigint unsigned | FK → products.id CASCADE |
| batch_id | bigint unsigned | FK → batches.id NULLABLE |
| warehouse_location | varchar(100) | NULLABLE |
| quantity | decimal(15,3) | NOT NULL DEFAULT 0 — CHECK (quantity >= 0) |
| created_at | timestamp | |
| updated_at | timestamp | |

**Relationships:** BelongsTo(product), BelongsTo(batch)

---

#### `stock_movements`

Immutable audit of every stock change.

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| product_id | bigint unsigned | FK → products.id |
| batch_id | bigint unsigned | FK → batches.id NULLABLE |
| inventory_id | bigint unsigned | FK → inventory.id |
| reference_type | varchar(50) | NOT NULL (sale, purchase, return, adjustment, transfer) |
| reference_id | bigint unsigned | NOT NULL |
| type | varchar(10) | NOT NULL (in / out) |
| quantity | decimal(15,3) | NOT NULL |
| quantity_before | decimal(15,3) | NOT NULL |
| quantity_after | decimal(15,3) | NOT NULL |
| unit_cost | decimal(15,2) | NULLABLE |
| description | text | NULLABLE |
| created_at | timestamp | |

**Indexes:** INDEX(product_id, batch_id), INDEX(reference_type, reference_id)

---

#### `customers`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| customer_group_id | bigint unsigned | FK → customer_groups.id NULLABLE |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | NULLABLE |
| phone | varchar(20) | UNIQUE NOT NULL |
| address | text | NULLABLE |
| city | varchar(100) | NULLABLE |
| credit_limit | decimal(15,2) | DEFAULT 0 |
| current_balance | decimal(15,2) | DEFAULT 0 |
| payment_terms | varchar(50) | NULLABLE (overrides group default) |
| total_spent | decimal(15,2) | DEFAULT 0 — auto-computed |
| last_purchase_at | timestamp | NULLABLE — auto-set |
| is_active | tinyint(1) | DEFAULT 1 |
| is_overdue | tinyint(1) | DEFAULT 0 — auto-set by cron |
| notes | text | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Indexes:** UNIQUE(phone), INDEX(customer_group_id), INDEX(is_active, is_overdue)

**Relationships:** BelongsTo(customer_group), HasMany(sales), HasMany(payments)

---

#### `sales_orders`

Order before invoicing. Supports pending/processing/completed lifecycle.

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| order_no | varchar(50) | UNIQUE NOT NULL — auto (ORD-2026-00001) |
| customer_id | bigint unsigned | FK → customers.id |
| user_id | bigint unsigned | FK → users.id |
| status | varchar(20) | DEFAULT 'pending' — pending → confirmed → processing → completed → cancelled |
| subtotal | decimal(15,2) | NOT NULL |
| discount_amount | decimal(15,2) | DEFAULT 0 |
| tax_amount | decimal(15,2) | DEFAULT 0 |
| total_amount | decimal(15,2) | NOT NULL |
| notes | text | NULLABLE |
| ordered_at | datetime | NOT NULL |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Relationships:** BelongsTo(customer), HasMany(sale_items) (shared through sale)

---

#### `sales`

Invoice record — created when order is completed or direct POS sale.

| Column | Type | Constraints | Auto |
|---|---|---|---|
| id | bigint unsigned | PK | |
| uuid | char(36) | UNIQUE NOT NULL | auto |
| invoice_no | varchar(50) | UNIQUE NOT NULL | auto (INV-2026-00001) |
| sales_order_id | bigint unsigned | FK → sales_orders.id NULLABLE | |
| customer_id | bigint unsigned | FK → customers.id NULLABLE | |
| user_id | bigint unsigned | FK → users.id | auto |
| status | varchar(20) | DEFAULT 'completed' | completed / voided / refunded |
| subtotal | decimal(15,2) | NOT NULL | auto |
| discount_amount | decimal(15,2) | DEFAULT 0 | auto |
| tax_amount | decimal(15,2) | DEFAULT 0 | auto |
| total_amount | decimal(15,2) | NOT NULL | auto |
| paid_amount | decimal(15,2) | DEFAULT 0 | auto |
| balance_due | decimal(15,2) | DEFAULT 0 | auto (total - paid) |
| sale_date | datetime | NOT NULL | auto |
| notes | text | NULLABLE | |
| created_at | timestamp | | auto |
| updated_at | timestamp | | auto |
| deleted_at | timestamp | NULLABLE | |

**Relationships:** BelongsTo(customer), BelongsTo(sales_order), HasMany(sale_items), HasMany(payments)

---

#### `sale_items`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| sale_id | bigint unsigned | FK → sales.id CASCADE |
| product_id | bigint unsigned | FK → products.id |
| product_unit_id | bigint unsigned | FK → product_units.id |
| batch_id | bigint unsigned | FK → batches.id NULLABLE |
| quantity | decimal(15,3) | NOT NULL |
| unit_price | decimal(15,2) | NOT NULL (from price_list at time of sale) |
| cost_price | decimal(15,2) | NULLABLE (FIFO cost at sale time) |
| discount_amount | decimal(15,2) | DEFAULT 0 |
| tax_amount | decimal(15,2) | DEFAULT 0 |
| subtotal | decimal(15,2) | NOT NULL |
| created_at | timestamp | |

**Relationships:** BelongsTo(sale), BelongsTo(product), BelongsTo(batch)

---

#### `payments`

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK |
| uuid | char(36) | UNIQUE NOT NULL |
| sale_id | bigint unsigned | FK → sales.id NULLABLE |
| customer_id | bigint unsigned | FK → customers.id NULLABLE |
| user_id | bigint unsigned | FK → users.id |
| payment_method | varchar(50) | NOT NULL (cash, mobile_money, bank_transfer, credit) |
| reference_no | varchar(100) | NULLABLE |
| amount | decimal(15,2) | NOT NULL |
| payment_date | datetime | NOT NULL |
| notes | text | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | NULLABLE |

**Indexes:** INDEX(sale_id), INDEX(customer_id), INDEX(payment_date)

**Relationships:** BelongsTo(sale), BelongsTo(customer)

---

#### `sale_returns`, `sale_return_items`, `purchase_returns`, `purchase_return_items`

(Same schema as previous architecture — unchanged, already wholesale-appropriate.)

---

#### `stock_adjustments`, `stock_adjustment_items`

(Same schema as previous architecture — unchanged.)

---

#### `notifications`

(Laravel standard — unchanged.)

---

#### `audit_logs`

(Same schema as previous architecture.)

---

#### `settings`

(Same key-value schema.)

---

## F. ERD Explanation

### F.1 Core Wholesale Relationships

```
customer_groups ──< customers ──< sales ──< sale_items ──> products ──> categories
       │               │            │                            │
       │               │            └──< payments                ├──< product_units ──> units
       │               │                                        │
       │               └──< sales_orders                        ├──< batches ──< inventory
       │                                                        │
       └──< price_lists ──< price_list_items ──> product_units  ├──< stock_movements
                                                                │
                                          suppliers ──< purchases ──< purchase_items
                                                                │
                                              purchase_suggestions
```

### F.2 Key Wholesale Relationships

| Relationship | Type | Behavior |
|---|---|---|
| Customer → Customer Group | Many-to-One | Determines price list and default credit terms |
| Customer Group → Price List | One-to-Many | Each group can have multiple price lists by validity period |
| Product → Product Unit | One-to-Many | Each product has multiple sellable/purchasable units |
| Price List → Price List Items | One-to-Many | Per-unit pricing with quantity tiers |
| Purchase Suggestion → Purchase | One-to-One | Suggestion becomes PO on approval |
| Sales Order → Sales | One-to-One | Order becomes invoice on completion |
| Sale → Payment | One-to-Many | Supports partial payments (balance_due tracked) |

---

## G. Module Architecture

### G.1 Auth Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Login/logout, 2FA enforcement, rate limiting, session management |
| **Inputs** | Credentials, TOTP code |
| **Outputs** | Authenticated session, audit log |
| **Dependencies** | users table |

### G.2 User Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Invite users, assign groups, auto-suspend inactive |
| **Inputs** | Invite email, group selection |
| **Outputs** | User account with group permissions |
| **Dependencies** | groups, group_menu |

### G.3 Group & Menu Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | CRUD groups, assign menus with view/create/edit/delete/approve/2fa permissions |
| **Inputs** | Group name, menu tree, user selection |
| **Outputs** | Permission configuration, cached access matrix |
| **Dependencies** | menus, users |

### G.4 Product Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Product CRUD with multi-unit support, auto-SKU, auto-barcode + barcode image generation, label printing, price list management |
| **Inputs** | Name, category, units with conversions, cost/price per unit |
| **Outputs** | Product with barcode, SKU, per-unit pricing, barcode image file |
| **Auto behavior** | SKU and barcode auto-generated on create; barcode image rendered as Code128 PNG; price list items define customer-specific pricing; label PDF generated for printing |
| **Dependencies** | Categories, Units, PriceLists |

### G.5 Customer Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Customer CRUD with group assignment, credit limit, payment terms, balance tracking, overdue detection |
| **Inputs** | Customer data, customer_group, credit_limit |
| **Outputs** | Customer with credit-controlled purchasing, auto-statement |
| **Auto behavior** | Balance auto-updated on every sale/payment; credit limit enforced at POS; overdue flag set by daily cron; statement auto-generated |
| **Dependencies** | customer_groups, price_lists, payments |

### G.6 Purchase Module (Suggestion + Approval)

| Aspect | Detail |
|---|---|
| **Responsibilities** | Auto-create purchase suggestions when stock low; manager review/approve; auto-PO; barcode receive; supplier payment scheduling |
| **Inputs** | Reorder level trigger, manager approval action, barcode scan |
| **Outputs** | Purchase suggestion → approved PO → received stock → scheduled payment |
| **Auto behavior** | `CheckReorderLevels` after every stock movement → `AutoCreatePurchaseSuggestion` (product, suggested qty = avg daily sales × lead time × 1.5, preferred supplier by performance score, estimated cost) → Suggestion appears in Manager dashboard → Manager approves/modifies/rejects → `AutoGeneratePurchaseOrder` on approval → `SendPOToSupplier` (email) → Barcode scan matches PO → `AutoReceiveStock` (batch creation, inventory update) → `AutoScheduleSupplierPayment` |
| **Dependencies** | Products, Suppliers, Inventory, User (approval) |

### G.7 Inventory Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Real-time stock tracking (multi-unit), batch/FIFO management, expiry alerts, stock intelligence (fast/slow/dead), stock valuation, cycle counting |
| **Inputs** | Stock movement events, barcode scans |
| **Outputs** | Stock levels, velocity classification, valuation, purchase suggestions |
| **Auto behavior** | Stock movement → update inventory + batch → check reorder → check expiry → update velocity metrics. Weekly: classify products as fast (sold in 30d), slow (sold in 90d), dead (>180d). Stocktake suggestions auto-generated by velocity zone. |
| **Dependencies** | Products, Batches, StockMovements |

### G.8 POS / Sales Module — Wholesale Barcode Flow

| Aspect | Detail |
|---|---|
| **Responsibilities** | Customer selection → price list load → barcode scan → credit check → sale → invoice — all sub-second |
| **Inputs** | Customer selection, barcode scan, quantity + unit, payment method |
| **Outputs** | Completed sale with FIFO deduction, invoice (print + email), payment record, customer balance update |
| **Barcode** | Keyboard wedge — any USB scanner works. JS captures in <1ms. Redis cache lookup in 50μs. |
| **Pricing** | Price determined by: customer_group → price_list → price_list_items WHERE product + unit + min_qty ≤ qty ORDER BY min_qty DESC |
| **Credit** | `balance + invoice_total ≤ credit_limit` — enforced before payment step. Cannot override. |
| **Dependencies** | Products, Customers, PriceLists, Inventory, Redis |

### G.9 Sales Order Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Order lifecycle (pending → confirmed → processing → completed → cancelled), stock reservation, backorder |
| **Inputs** | Customer order (manual or POS), items with quantities |
| **Outputs** | Order record, pick list, invoice on completion |
| **Auto behavior** | Stock checked on creation; reserved until completion or cancellation; cancelled orders release reservation |
| **Dependencies** | Customers, Products, Inventory |

### G.10 Returns Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Customer/supplier return processing via barcode, restock or write-off decision, refund calculation |
| **Inputs** | Barcode scan, condition selection |
| **Outputs** | Return record, stock adjustment, refund/credit |
| **Dependencies** | Sales, Purchases, Inventory |

### G.11 Reporting & Analytics Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Auto-generate all reports on schedule; push to dashboards and email; no manual request needed |
| **Inputs** | Cron triggers |
| **Outputs** | Pre-generated reports, pushed notifications, dashboard widgets |
| **Auto schedule** | Daily 00:05, Weekly Sun 22:00, Monthly 1st 02:00 |
| **Dependencies** | All core modules |

### G.12 Notifications Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Auto-push in-app + email notifications on events |
| **Inputs** | System events |
| **Outputs** | Notifications (DB + mail channels — both queued) |
| **Dependencies** | Queue, Mail |

### G.13 Audit Module

| Aspect | Detail |
|---|---|
| **Responsibilities** | Auto-log all mutations with before/after, tag by category (stock, price, credit), auto-archive after 12 months |
| **Inputs** | Model events (Observer) |
| **Outputs** | Immutable audit trail |
| **Dependencies** | All models |

---

## H. Laravel Application Architecture

### H.1 Directory Structure

```
app/
├── Actions/
│   ├── Product/
│   │   ├── AutoGenerateSkuBarcode.php
│   │   ├── AutoGenerateBarcodeImage.php
│   │   └── GenerateProductLabels.php
│   ├── Purchase/
│   │   ├── AutoCreatePurchaseSuggestion.php
│   │   ├── AutoGeneratePurchaseOrder.php
│   │   ├── AutoReceiveStock.php
│   │   ├── AutoSelectSupplier.php
│   │   ├── AutoCalculateOrderQuantity.php
│   │   └── AutoScheduleSupplierPayment.php
│   ├── Inventory/
│   │   ├── CheckReorderLevels.php
│   │   ├── UpdateStockVelocity.php
│   │   ├── ClassifyStockIntelligence.php
│   │   ├── CalculateStockValuation.php
│   │   └── AutoAdjustStock.php
│   ├── Sale/
│   │   ├── AutoProcessSale.php
│   │   ├── AutoApplyPricing.php
│   │   ├── AutoCheckCredit.php
│   │   ├── AutoGenerateInvoice.php
│   │   ├── AutoVoidSale.php
│   │   └── DeductInventoryFifo.php
│   ├── SalesOrder/
│   │   ├── CreateSalesOrder.php
│   │   ├── ConfirmSalesOrder.php
│   │   └── CancelSalesOrder.php
│   ├── Customer/
│   │   ├── AutoCreateCustomerAtPos.php
│   │   ├── AutoUpdateCreditBalance.php
│   │   ├── AutoFlagOverdue.php
│   │   └── AutoGenerateStatement.php
│   ├── Return/
│   │   ├── AutoProcessCustomerReturn.php
│   │   └── AutoProcessSupplierReturn.php
│   ├── Report/
│   │   ├── AutoGenerateDailyReport.php
│   │   ├── AutoGenerateWeeklyIntelligence.php
│   │   └── AutoGenerateMonthlyReport.php
│   └── System/
│       ├── AutoSuspendInactiveUsers.php
│       ├── AutoArchiveAuditLogs.php
│       ├── AutoCalibrateReorderLevels.php
│       └── AutoGenerateBarcodePreloadCache.php
├── Enums/
│   ├── SaleStatus.php
│   ├── PurchaseStatus.php
│   ├── OrderStatus.php
│   ├── StockMovementType.php
│   ├── ReturnCondition.php
│   ├── PaymentMethod.php
│   └── StockVelocity.php               (fast / slow / dead)
├── Events/
│   ├── ProductCreated.php
│   ├── SaleCompleted.php
│   ├── StockMoved.php
│   ├── StockLow.php                     → creates PurchaseSuggestion
│   ├── BatchExpiring.php
│   ├── CreditLimitExceeded.php
│   ├── PaymentReceived.php
│   ├── ReturnProcessed.php
│   ├── PurchaseSuggestionCreated.php
│   └── PurchaseOrderApproved.php
├── Exceptions/
│   ├── InsufficientStockException.php
│   ├── CreditLimitExceededException.php
│   ├── ExpiredBatchException.php
│   └── InvalidPriceListException.php
├── Jobs/
│   ├── CheckReorderLevels.php
│   ├── SendPurchaseOrderToSupplier.php
│   ├── SendLowStockNotification.php
│   ├── SendBatchExpiryAlerts.php
│   ├── AutoGeneratePurchaseSuggestions.php
│   ├── GenerateProductBarcodeImage.php
│   ├── UpdateStockVelocity.php
│   ├── AutoGenerateReports.php
│   ├── AutoFlagOverdueCustomers.php
│   └── AutoArchiveAuditLogs.php
├── Listeners/
│   ├── DeductInventoryOnSale.php
│   ├── NotifyLowStock.php
│   ├── CreatePurchaseSuggestion.php
│   ├── UpdateCustomerBalance.php
│   ├── UpdateStockVelocityOnMovement.php
│   ├── LogAuditTrail.php
│   └── NotifyManagersOfSuggestion.php
├── Console/
│   └── Kernel.php (scheduled jobs)
├── Models/
│   ├── User.php
│   ├── Group.php
│   ├── Menu.php
│   ├── Category.php
│   ├── Unit.php
│   ├── Product.php
│   ├── ProductUnit.php
│   ├── CustomerGroup.php
│   ├── PriceList.php
│   ├── PriceListItem.php
│   ├── Supplier.php
│   ├── PurchaseSuggestion.php
│   ├── Purchase.php
│   ├── PurchaseItem.php
│   ├── Batch.php
│   ├── Inventory.php
│   ├── StockMovement.php
│   ├── Customer.php
│   ├── SalesOrder.php
│   ├── Sale.php
│   ├── SaleItem.php
│   ├── Payment.php
│   ├── SaleReturn.php
│   ├── SaleReturnItem.php
│   ├── PurchaseReturn.php
│   ├── PurchaseReturnItem.php
│   ├── StockAdjustment.php
│   ├── StockAdjustmentItem.php
│   ├── Setting.php
│   └── AuditLog.php
├── Observers/
│   ├── ProductObserver.php             (auto-SKU, barcode, barcode image)
│   ├── SaleObserver                    (auto invoice no, audit)
│   ├── StockMovementObserver           (immutable enforcement)
│   └── AuditObserver                   (auto-log all mutations)
├── Services/
│   ├── PosService.php                  (orchestrate sub-second POS flow)
│   ├── BarcodeService.php              (generate Code128 PNG + parse)
│   ├── BarcodeLookupService.php        (Redis-first barcode → product)
│   ├── PricingService.php              (resolve price from price_list_items)
│   ├── StockService.php                (single gate for all stock changes)
│   ├── CreditService.php               (check/reduce/restore credit)
│   ├── ReportService.php               (auto-generate and cache)
│   └── SupplierSelectionService.php    (auto-pick best supplier)
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── GroupController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── SupplierController.php
│   │   ├── PurchaseSuggestionController.php
│   │   ├── PurchaseController.php
│   │   ├── PosController.php           (ultra-fast wholesale POS)
│   │   ├── SalesOrderController.php
│   │   ├── InventoryController.php
│   │   ├── CustomerController.php
│   │   ├── PriceListController.php
│   │   ├── CustomerGroupController.php
│   │   ├── ReturnController.php
│   │   ├── ReportController.php
│   │   ├── DashboardController.php     (analytics widgets)
│   │   ├── NotificationController.php
│   │   └── SettingController.php
│   ├── Middleware/
│   │   ├── CheckMenuAccess.php
│   │   └── CheckTwoFactor.php
│   └── Requests/
│       └── ... (FormRequest per create/update endpoint)
├── Traits/
│   ├── AutoGeneratesSku.php
│   ├── AutoGeneratesBarcode.php
│   ├── AutoLogsAudit.php
│   ├── AutoHasUuid.php
│   └── HasDynamicPermissions.php
└── Helpers/
    └── BarcodeGenerator.php            (standalone Code128 image renderer)
```

### H.2 Scheduled Job Schedule

```php
// Every 5 min — retry failed stock movements
Schedule::command('inventory:retry-failed')->everyFiveMinutes();

// Every 30 min — check reorder levels, create suggestions
Schedule::job(new AutoGeneratePurchaseSuggestions)->everyThirtyMinutes();

// Hourly — batch expiry alerts
Schedule::job(new SendBatchExpiryAlerts)->hourly();

// Daily 00:05 — generate + push daily sales report
Schedule::job(new AutoGenerateDailyReport)->dailyAt('00:05');

// Daily 02:00 — auto-suspend inactive users (90d)
Schedule::job(new AutoSuspendInactiveUsers)->dailyAt('02:00');

// Daily 03:00 — flag overdue customers
Schedule::job(new AutoFlagOverdueCustomers)->dailyAt('03:00');

// Daily 04:00 — reconcile customer balances
Schedule::job(new ReconcileCustomerBalances)->dailyAt('04:00');

// Weekly Sunday 22:00 — stock intelligence (velocity, dead stock)
Schedule::job(new UpdateStockVelocity)->weeklyOn(0, '22:00');

// Weekly Monday 02:00 — calibrate reorder levels from sales history
Schedule::job(new AutoCalibrateReorderLevels)->weeklyOn(1, '02:00');

// Monthly 1st 02:00 — archive old audit logs
Schedule::job(new AutoArchiveAuditLogs)->monthlyOn(1, '02:00');

// Monthly 1st 03:00 — generate monthly P&L + reports
Schedule::job(new AutoGenerateMonthlyReport)->monthlyOn(1, '03:00');
```

### H.3 Event → Listener Chain

```
SaleCompleted
  → DeductInventoryOnSale (sync — within DB transaction)
  → NotifyCustomer (queued)
  → UpdateCustomerBalance (sync)
  → LogAuditTrail (sync)

StockMoved
  → CreatePurchaseSuggestion if stock ≤ reorder_level (sync)
  → UpdateStockVelocityOnMovement (sync)
  → LogAuditTrail (sync)

PurchaseSuggestionCreated
  → NotifyManagersOfSuggestion (queued)

PurchaseOrderApproved
  → SendPurchaseOrderToSupplier (queued)

PaymentReceived
  → UpdateCustomerBalance (sync)
  → LogAuditTrail (sync)

BatchExpiring
  → SendBatchExpiryAlerts (queued)
  → MarkBatchAsExpired if past expiry (sync)
```

### H.4 Pricing Engine Logic

```php
class PricingService
{
    public function resolvePrice(
        Customer $customer,
        Product $product,
        ProductUnit $unit,
        float $quantity
    ): float {
        // 1. Get customer's price list via customer_group
        $priceList = $customer->customerGroup->activePriceList;

        // 2. Find matching price_list_item
        $item = PriceListItem::where('price_list_id', $priceList->id)
            ->where('product_id', $product->id)
            ->where('product_unit_id', $unit->id)
            ->where('minimum_quantity', '<=', $quantity)
            ->orderBy('minimum_quantity', 'desc')
            ->first();

        // 3. Fallback: product_unit default selling_price
        return $item?->price ?? $unit->selling_price;
    }
}
```

---

## I. Inventory Logic Architecture

### I.1 Single Gate: `StockService::movement()`

All stock changes go through this single method. It:

1. Validates (sufficient stock for 'out', product tracked, batch valid)
2. DB transaction: creates `StockMovement` (immutable) → updates `Inventory.quantity` → updates `Batch.remaining_quantity`
3. Fires `StockMoved` event
4. Returns `StockMovement` record

### I.2 Purchase Suggestion Creation

```
StockMoved event
  → CreatePurchaseSuggestion listener
    → For product: if inventory.quantity ≤ product.reorder_level
      → Check if pending suggestion already exists (avoid duplicates)
      → If not → AutoCreatePurchaseSuggestion action:
        → avg_daily_sales = 90-day average
        → lead_time = supplier avg_lead_time_days
        → suggested_qty = max(min_order_qty, avg_daily_sales × lead_time × 1.5)
        → preferred_supplier = SupplierSelectionService.bestForProduct(product)
        → estimated_cost = negotiated price or last purchase price
        → Create PurchaseSuggestion (status: pending)
        → Notify managers
```

### I.3 Stock Receiving (Barcode)

```
Supplier delivers → scan PO barcode
  → System identifies purchase
  → Scan each product barcode
  → System auto-matches to PurchaseItem
  → Input: received quantity, batch_no, expiry_date
  → AutoReceiveStock action:
    → Create or update Batch
    → StockService::movement('in', qty, batch, 'purchase', purchase_id)
    → Update PurchaseItem.quantity_received
    → If all items fully received → Purchase.status = 'received'
    → AutoScheduleSupplierPayment
```

### I.4 FIFO Deduction (Sales)

```
For each sale_item:
  → batches = Batch.where(product_id).where('remaining_quantity > 0')
      .where('is_expired', 0).orderBy('created_at').get()
  → remaining = sale_item.quantity
  → cost_price_total = 0
  → For batch in batches:
    → take = min(remaining, batch.remaining_quantity)
    → StockService::movement('out', take, batch, 'sale', sale_id)
    → cost_price_total += take × batch.unit_cost
    → remaining -= take
    → if remaining == 0: break
  → sale_item.cost_price = cost_price_total / sale_item.quantity
```

### I.5 Stock Intelligence Classification

```
Weekly cron:
  → For each product with inventory.quantity > 0:
    → qty_30d = SUM(sale_items.quantity WHERE sale_date >= NOW() - 30d)
    → qty_90d = SUM(sale_items.quantity WHERE sale_date >= NOW() - 90d)
    → qty_180d = SUM(sale_items.quantity WHERE sale_date >= NOW() - 180d)
    
    → if qty_30d > 0: velocity = 'fast'
    → else if qty_90d > 0: velocity = 'slow'
    → else if qty_180d > 0: velocity = 'very_slow'
    → else: velocity = 'dead'
    
    → days_of_stock = inventory.quantity / max(avg_daily_90d, 0.001)
```

### I.6 Reorder Level Auto-Calibration

```
Weekly:
  → For products with ≥90 days sales history:
    → avg_daily = 90-day average daily sales
    → lead_time = supplier avg_lead_time_days
    → safety_stock = avg_daily × lead_time × safety_factor (default 0.5)
    → reorder_level = (avg_daily × lead_time) + safety_stock
    → Update product.reorder_level
  → For new products: reorder_level = category default
```

---

## J. Wholesale Analytics & Reporting

### J.1 Dashboard Widgets (All Auto-Refreshing)

| Widget | Data Source | Refresh |
|---|---|---|
| **Today's Sales (amount)** | sales WHERE date = today | Real-time |
| **Today's Transactions** | sales WHERE date = today (count) | Real-time |
| **Active Customers** | customers WHERE is_active | Daily |
| **Outstanding Credit (total)** | SUM(customers.current_balance) | Real-time |
| **Credit Exposure (>80%)** | customers WHERE balance/limit > 0.8 | Daily |
| **Low Stock Items** | inventory WHERE qty ≤ reorder_level | On movement |
| **Pending Purchase Suggestions** | purchase_suggestions WHERE status=pending | On create |
| **Dead Stock Value** | products WHERE stock_velocity = 'dead' | Weekly |
| **Stock Turnover Ratio** | COGS / avg_inventory_value | Weekly |
| **Monthly Revenue (chart)** | sales GROUP BY day | Hourly |
| **Top 10 Customers (spend)** | payments GROUP BY customer | Daily |
| **Supplier Performance** | purchases + purchase_returns | Monthly |

### J.2 Auto-Generated Reports

| Report | Frequency | Delivery |
|---|---|---|
| **Sales Report** (by product, customer, daily trend) | Daily 00:05 | Dashboard + Email |
| **Profit Report** (revenue, COGS, margin per product) | Monthly 1st 03:00 | Dashboard + Email |
| **Stock Report** (value, turnover, low stock, dead stock) | Weekly Sun 22:00 | Dashboard + Email |
| **Inventory Valuation** (FIFO total by product) | Real-time | Dashboard widget |
| **Customer Debt/Aging** (outstanding, 30/60/90+ days) | Daily 03:00 | Dashboard + Email |
| **Supplier Report** (purchase volume, lead time, quality) | Monthly 2nd 02:00 | Dashboard |
| **Purchase Report** (POs by status, spending by supplier) | Monthly 2nd 02:00 | Dashboard |
| **Payment Report** (collections by method, by customer) | Weekly Mon 02:00 | Dashboard |
| **Tax Summary** (VAT collected) | Monthly 1st 04:00 | Email (Accountant) |
| **Product Profitability** (margin, turnover per product) | Monthly 3rd 02:00 | Dashboard |

### J.3 Calculation Logic

**P&L (Auto-Monthly):**
```
Revenue = SUM(sale_items.subtotal WHERE sale_date IN month)
COGS = SUM(sale_items.cost_price × sale_items.quantity WHERE sale_date IN month)
Gross Profit = Revenue - COGS
Gross Margin % = (Gross Profit / Revenue) × 100
```

**Inventory Valuation (FIFO):**
```
For each product:
  Batches ordered by created_at ASC (oldest first)
  value = SUM(batch.remaining_quantity × batch.unit_cost)
Total = SUM(all products)
```

**Stock Turnover Ratio:**
```
COGS (last 12 months) / Average Inventory Value (last 12 months)
```

**Customer Aging:**
```
Current: invoices with balance_due > 0 AND age ≤ 30 days
30-60 days: invoices with age > 30 AND ≤ 60
60-90 days: invoices with age > 60 AND ≤ 90
90+ days: invoices with age > 90
```

---

## K. Notification Architecture

### K.1 Auto-Triggered Notifications

| Notification | Trigger | Channel |
|---|---|---|
| Purchase Suggestion Created | Stock ≤ reorder_level | DB + Email to Managers |
| Purchase Order Sent | PO approved + generated | DB + Email to Supplier |
| Low Stock Alert | Stock ≤ reorder_level (informational) | DB + Email to Warehouse |
| Batch Expiry Warning | 30/14/7 days before expiry | DB + Email to Warehouse |
| Credit Limit Warning | Customer balance > 80% of limit | DB + Email to Finance |
| Customer Overdue | Overdue > 30 days | DB + Email to Finance |
| Sale Receipt | Sale completed | DB + Email to Customer |
| Payment Received | Payment recorded | DB + Email |
| Daily Sales Summary | Daily 00:05 | DB + Email to Manager |
| Weekly Stock Intelligence | Weekly Sun 22:00 | DB + Email |
| Monthly P&L | Monthly 1st 03:00 | DB + Email to Manager + Accountant |

### K.2 Architecture

All notifications are queued. Database channel for in-app. Mail channel for email. Preferences stored in settings table.

---

## L. Security Architecture (All Auto-Enforced)

| Measure | Enforcement |
|---|---|
| Password | Bcrypt 12 — auto on every set |
| Rate limiting | Throttle middleware — auto on login + API |
| Session | Database driver, 30-min TTL, auto-cleanup cron |
| 2FA | TOTP — auto-required if user's group has can_2fa flag |
| Access control | Dynamic group-menu middleware on every route |
| Input validation | FormRequest per endpoint |
| SQL injection | Eloquent ORM — auto |
| XSS | Blade `{{ }}` — auto |
| CSRF | VerifyCsrfToken — auto |
| Audit | Observer trait on all critical models |
| Data encryption | AES-256-CBC for sensitive columns |
| HTTPS | Redirect middleware — auto in production |

---

## M. API Architecture

### M.1 RESTful v1

```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
GET    /api/v1/products                   (auto-paginated, cached)
GET    /api/v1/products/{uuid}            (cached lookup)
GET    /api/v1/products/{uuid}/barcode    (return barcode image)
GET    /api/v1/customers/{uuid}
GET    /api/v1/customers/{uuid}/statement
GET    /api/v1/customers/{uuid}/credit
GET    /api/v1/purchase-suggestions       (pending suggestions)
POST   /api/v1/purchase-suggestions/{id}/approve
POST   /api/v1/purchase-suggestions/{id}/reject
GET    /api/v1/inventory
GET    /api/v1/inventory/low-stock
GET    /api/v1/inventory/{product}/stock  (stock in all units)
POST   /api/v1/sales                      (create sale)
GET    /api/v1/sales/{uuid}
POST   /api/v1/payments
GET    /api/v1/reports/sales
GET    /api/v1/reports/profit-loss
GET    /api/v1/reports/inventory-valuation
GET    /api/v1/reports/customer-debt
GET    /api/v1/dashboard                  (all KPI widgets)
```

### M.2 Auth

Sanctum tokens with abilities scoped per group permissions.

---

## N. Deployment Architecture

| Component | Tech |
|---|---|
| Web server | Nginx 1.24+ |
| PHP | 8.3+ |
| Database | MySQL 8.0+ |
| Cache | Redis 7+ |
| Queue | Database driver (Redis optional for scale) |
| Node | 20+ (Vite build) |
| OS | Ubuntu 22.04 LTS |

**CI/CD:** GitHub Actions → test → deploy on main push.  
**Backup:** Auto daily dump (30d retention) + binlog (7d retention) → S3.

---

## O. Risks and Mitigation

| # | Risk | Impact | Mitigation |
|---|---|---|---|
| 1 | Data loss | Critical | Auto-backup daily + binlog, RTO 4h RPO 1h |
| 2 | Credit sale that exceeds limit | High | Auto-enforced at POS + DB CHECK, cannot bypass |
| 3 | Wrong pricing applied | High | Auto-pricing from price_list (no manual entry), audit logged |
| 4 | Stock corruption | High | Single StockService gate, DB CHECK(quantity>=0), immutable audit |
| 5 | Concurrent POS race | Medium | Row-level locking (SELECT...FOR UPDATE) in transaction |
| 6 | Expired product sold | High | Auto-block at POS, daily expiry cron, batch flagged is_expired |
| 7 | Supplier fails delivery | Medium | Performance tracking, auto-alternative supplier suggestion |
| 8 | Customer not paying | Medium | Auto-credit check, overdue flag, aging reports |
| 9 | Unit conversion errors | Medium | conversion_factor stored per product_unit, validated on save |
| 10 | Queue failure | Medium | Auto-retry (3 attempts), failed job monitoring |

---

*Wholesale ERP Management System — designed for distribution businesses that buy bulk, sell multi-unit, manage customer credit, and operate barcode-first workflows. All operational decisions are event-driven and automated; purchasing is manager-supervised; pricing is controlled through customer group price lists.*

*Deviations require documented rationale and team review.*
