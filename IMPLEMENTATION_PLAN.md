# Wholesale Distribution ERP — Implementation Plan

> **Goal:** Transform the current Inventory & Sales Management system into a focused Wholesale Distribution ERP following WholesaleTz.md spec, removing non-wholesale verticals, and applying enterprise design principles.

---

## 1. CURRENT STRUCTURE (8 Sidebar Modules)

| Module | Sub-items |
|--------|-----------|
| **Dashboard** | (single page) |
| **Master Data** | Products, Categories, Category Tree, Brands (null), Units, Customers, Customer Dashboard, Customer Groups, Suppliers, Pricing Dashboard, Price Lists, Pricing Simulator |
| **Procurement** | Dashboard, Purchase Requisition, Local Purchase Orders, Goods Receiving, Purchase Returns, Supplier Analytics, Reports |
| **Inventory** | Dashboard, Stock Adjustments, Stock Transfers (null), Reservations, Batch Tracking, Inventory Valuation, Cycle Counts (null), Low Stock |
| **Sales** | Dashboard, Quotations (null), Sales Orders, POS, POS Dashboard, Invoices, Payments, Sales Returns, Credit Notes, Refunds |
| **Finance** | Dashboard (Profit), Accounts Receivable, Accounts Payable (null), Banking (null), Tax, Profit Analysis, Financial Reports (null) |
| **Reports & Analytics** | Sales Reports, Procurement Reports, Inventory Reports, Finance Reports (null), Customer Reports, Supplier Reports, Executive Dashboard |
| **Administration** | Users, Roles & Permissions, Organization (null), Menu Management, System Settings, Audit Logs, Scheduled Jobs |

---

## 2. PROPOSED STRUCTURE (8 Refined Modules)

```
1. DASHBOARD
   └── /dashboard (single hub — all secondary dashboards removed)

2. STAKEHOLDERS                                          ← renamed from Master Data
    ├── Products (with Variants/Sub-Products)
    ├── Categories
    ├── Brands
    ├── Units
    ├── Customers
    ├── Customer Groups
    ├── Suppliers
    ├── Warehouses (Stores)                              ← NEW
    ├── Tax Settings                                     ← NEW
    └── Payment Terms                                    ← NEW

3. PROCUREMENT
    ├── Purchase Requisitions                            ← renamed from Suggestions
    ├── Purchase Orders
    ├── Goods Receiving Notes
    ├── Purchase Returns
    └── Supplier Performance                             ← NEW

4. INVENTORY
    ├── Stock Adjustments                                ← with status tabs
    ├── Stock Transfers                                  ← NEW
    ├── Store Requests                                   ← NEW
    ├── Inventory Valuation
    ├── Batch Tracking
    ├── Cycle Counts
    └── Low Stock

5. SALES
    ├── Proforma                                         ← renamed from Quotations
    ├── Sales Orders (= Invoices)                        ← unified create
    ├── Invoices                                         ← full list with status tabs
    ├── Point of Sale (POS)
    ├── Payments / Receipts
    ├── Sales Returns
    ├── Credit Notes
    ├── Refunds
    ├── Customer Advances                                ← NEW
    ├── Customer Collections                             ← NEW
    └── Sales Targets                                    ← NEW

6. FINANCE
    ├── Accounts Receivable
    ├── Accounts Payable
    ├── Banking                                          ← NEW
    ├── Expenses                                         ← NEW
    ├── Chart of Accounts                                ← NEW
    ├── Journal Entries                                  ← NEW
    ├── Tax
    └── Financial Statements                             ← NEW

7. REPORTS (6 consolidated pages)
    ├── Sales Reports
    ├── Purchase Reports
    ├── Inventory Reports
    ├── Financial Reports
    ├── Customer Reports
    └── Supplier Reports

8. ADMINISTRATION
    ├── Users
    ├── Roles & Permissions
    ├── Menu Access
    ├── Approval Levels                                  ← NEW
    ├── Warehouses
    ├── Branches
    ├── Company Settings
    ├── Document Numbering                               ← NEW
    ├── Dashboard Cards                                  ← NEW
    ├── Audit Logs
    └── Scheduled Jobs
```

---

## 3. FEATURES TO MERGE

| Current Items | Merge Into | Rationale |
|---------------|-----------|-----------|
| Price Lists + Pricing Dashboard + Pricing Simulator | **Stakeholders > Price Lists** (single page with tabs) | 3 related pages → 1 intelligent page |
| Customer Dashboard + Customer List | **Stakeholders > Customers** (search + profile drawer) | Dashboard is redundant |
| POS + POS Dashboard | **Sales > POS** (dashboard cards embedded at top) | POS page has summary + transaction |
| Supplier Analytics + Supplier Performance | **Procurement > Supplier Performance** | One analytics page |
| Inventory Dashboard + Low Stock + all dashboards | **Main Dashboard** (cards: low stock, pending counts, charts) | Single hub |
| Finance Dashboard + Profit Analysis | **Main Dashboard** + **Finance > Financial Statements** | Separate concerns |
| Sales Dashboard + Dashboard | **Main Dashboard** (revenue chart, pending orders) | Single hub |

---

## 4. FEATURES TO REMOVE (Non-Wholesale)

| Feature | Reason |
|---------|--------|
| Inventory > Reservations | Hotel-style, not wholesale. Remove |
| Sales > Coupons / Coupon Config / Package Config | Retail promotion, not wholesale |
| CRM / Leads / Prospects / Sales Funnel / Cold Calls | Out of scope |
| HR / Payroll / Leave / Attendance / Salary Advances | Out of scope |
| Hospitality (Hotel, Restaurant, Bar, Tables) | Out of scope |
| Gym Management | Out of scope |
| Production / BOM / Stock Conversion | Light manufacturing, not pure wholesale |
| Printing / Job Orders / Departments | Out of scope |
| Marketing / Campaigns / Events | Out of scope |
| Loyalty Program | Retail feature |
| SMS / Bulk SMS | Utility, optional |
| Projects / Programs / Budget | Out of scope |
| Customer Care / Complaints | Out of scope |
| E-Commerce / Shop Config | Out of scope |
| Website CMS / Pages / Blog | Out of scope |
| Education: Course, Exam, Registration, Student | Out of scope |
| Task Management | Out of scope |
| Support / Tickets | Out of scope |
| Hostel, Transport, Canteen, Library, Visitor | Out of scope |
| Fixed Assets / Depreciation | Enterprise — defer |
| Loan Management | Out of scope |
| API / Integration | ✅ KEEP — essential |

---

## 5. FEATURES TO KEEP

| Module | Status | Notes |
|--------|--------|-------|
| Products | ✅ Keep | Add sub-product (variant) parent-child |
| Categories | ✅ Keep | Flat hierarchy view |
| Brands | ✅ Keep | Inline management |
| Units | ✅ Keep | UOM management |
| Customers | ✅ Keep | Add credit limit, salesperson |
| Customer Groups | ✅ Keep | Pricing, discount groups |
| Suppliers | ✅ Keep | Add price history, performance |
| Purchase Orders | ✅ Keep | Add multi-level approval |
| Purchase Requisitions | ✅ Keep | Rename from Suggestions |
| Goods Receiving | ✅ Keep | Add partial receipt |
| Purchase Returns | ✅ Keep | Already implemented |
| Stock Adjustments | ✅ Keep | Add journal entry integration + status tabs |
| Inventory Valuation | ✅ Keep | FIFO costing |
| Batch Tracking | ✅ Keep | Add expiry tracking |
| Inventory Transactions | ✅ Keep | Audit trail |
| Sales Orders | ✅ Keep | Merge with Invoice creation |
| POS | ✅ Keep | Core wholesale + retail |
| Invoices | ✅ Keep | Full list page with status tabs |
| Payments | ✅ Keep | Multi-invoice allocation |
| Sales Returns | ✅ Keep | Already implemented |
| Credit Notes | ✅ Keep | Already implemented |
| Refunds | ✅ Keep | Already implemented |
| Price Lists | ✅ Keep | TZS only, customer-specific |
| Pricing Simulator | ✅ Keep | TZS only |
| Users | ✅ Keep | Already implemented |
| Groups (Roles) | ✅ Keep | Add missing permissions |
| Menu Management | ✅ Keep | Already implemented |
| Settings | ✅ Keep | Already implemented |
| Audit Logs | ✅ Keep | Already implemented |
| Reports (existing) | ✅ Keep | Consolidate into 6 pages |
| Profit Analysis | ✅ Keep | Essential for wholesale |
| Supplier Analytics | ✅ Keep | Merge into Performance |
| Scheduled Reports | ✅ Keep | Already implemented |

---

## 6. NEW FEATURES TO IMPLEMENT

### Priority 1 (Foundation)

| Feature | Module | Description |
|---------|--------|-------------|
| Chart of Accounts | Finance | Account types: Asset, Liability, Equity, Income, Expense. Hierarchical CRUD. Base type + group code |
| Journal Entries | Finance | Double-entry engine. Debit = Credit validation. Configurable numbering (GENJ/ADJ). Approval for adjustments |
| Trial Balance | Finance | Account balances summary with period date range filters |
| Income Statement | Finance | Revenue - COGS - Expenses = Net Profit. Period comparison |
| Balance Sheet | Finance | Assets = Liabilities + Equity. As-of-date snapshot |
| Expense Management | Finance | Expense entry (employee/operating), category CRUD, multi-level approval, payment processing, journal posting |
| Store Requests | Inventory | Internal requisition: Create → Approve (Level 0-3) → Issue Goods → Receive at Destination → Stock Movement |
| Stock Transfers | Inventory | Inter/intra-warehouse: Create → Pending → Approve → Issue → Receive |
| Warehouses CRUD | Stakeholders | Store types: goods, fixed_asset. Branch assignment. One-page CRUD |
| Approval Configuration | Administration | Module key (sales, po, store, expense), Level (0-3), per-level approver groups/roles |
| Sub-Products (Variants) | Products | `parent_product_id` nullable FK, `has_variants` boolean, variant attributes JSON, unique SKU per variant |
| Sales Order = Invoice | Sales | Unified create page. Submit creates SO + Invoice in one transaction. Proforma/Draft/Posted states |
| Invoices List Page | Sales | Full invoice listing with status tabs: All | Draft | Proforma | Posted | Paid | Partial | Overdue | Cancelled | Reversed. Filters: date range, customer, salesperson, store. Export: PDF, Excel, CSV |
| TZS-Only Pricing | Global | Remove currency/exchange_rate from all models, views, controllers, price lists |

### Priority 2 (Core Operations)

| Feature | Module | Description |
|---------|--------|-------------|
| Banking | Finance | Bank account CRUD, bank transactions, reconciliation workflow |
| Accounts Receivable | Finance | Aging report (30/60/90+), collection tracking |
| Accounts Payable | Finance | Aging report, payment scheduling |
| Supplier Payments | Finance | Pay PO, advance payment, voucher printing |
| Supplier Performance | Procurement | Lead time, fulfillment rate, defect rate, price trends |
| Payment Terms | Stakeholders | Net 30/60/90, COD, due date auto-calculation |
| Tax Settings | Stakeholders | VAT rates, WHT rates, tax groups per product |
| Document Numbering | Administration | Per-doc-type prefix + sequence config |
| Multi-Level Approval (refactor) | All | Centralized ApprovalService replacing per-module hardcoded approval |
| Dashboard Cards Config | Administration | Per-group card toggle for main dashboard |

### Priority 3 (Sales Enhancement)

| Feature | Module | Description |
|---------|--------|-------------|
| Proforma | Sales | Create → Send (PDF) → Convert to Order → Void. No stock impact |
| Customer Advances | Sales | Receive advance → Apply to invoice → Return balance |
| Sales Targets | Sales | Per-salesperson monthly/quarterly targets, achievement %, commission calc |
| Customer Collections | Sales | Payment schedule, aging follow-up, dunning |
| Cash Flow Statement | Finance | Operating/Investing/Financing |
| General Ledger | Finance | Per-account transaction drill-down |
| Bank Reconciliation | Banking | Statement import, matching, difference=0 approval |
| Printable LPO (PDF) | Procurement | Full letterhead, supplier, items, VAT, signatures, T&Cs |
| Delivery Note (PDF) | Inventory | Company header, customer/store, items, signed by |
| Store Request Note (PDF) | Inventory | SR number, items, approver signature |

### Priority 4 (Optimization)

| Feature | Module | Description |
|---------|--------|-------------|
| Supplier Price History | Procurement | Track price changes per product+supplier |
| Customer Credit Limits | Customers | Credit limit with approval override |
| Cycle Counts | Inventory | Scheduled counts, variance reporting |
| Batch Expiry Tracking | Inventory | Expiry date, FEFO, alerts |
| Saved Report Filters | Reports | Per-user filter presets for 6 report pages |
| Report Scheduling + Email | Reports | Daily/weekly/monthly automated PDF email |
| Special Permissions | Administration | backdate_transactions, allow_sell_without_stock, allow_approve_all_levels, reset_user_passwords |

---

## 7. TRANSACTION PAGE STATUS TABS PATTERN

Every transaction page must replace separate submenu items with a single page containing status tabs. This applies to ALL transaction modules.

### Purchase Orders
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Draft** | `status = draft` |
| **Pending Approval** | `status = pending` |
| **Approved** | `status = approved` |
| **Issued** | `status = issued` |
| **Received** | `status = received` (GRN created) |
| **Completed** | `status = completed` |
| **Cancelled** | `status = cancelled` |
| **Rejected** | `status = rejected` |
| **Reversed** | `status = reversed` |

### Stock Adjustments
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Draft** | `status = draft` |
| **Pending Approval** | `status = pending` |
| **Approved** | `status = approved` |
| **Completed** | `status = completed` |
| **Rejected** | `status = rejected` |

### Store Requests
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Pending** | `status = pending` |
| **Approved** | `status = approved` |
| **Issued** | `status = issued` |
| **Received** | `status = received` |
| **Rejected** | `status = rejected` |

### Stock Transfers
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Pending** | `status = pending` |
| **Approved** | `status = approved` |
| **Issued** | `status = issued` |
| **Received** | `status = received` |
| **Rejected** | `status = rejected` |

### Sales Orders / Invoices
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Draft** | `status = draft` |
| **Proforma** | `type = proforma` |
| **Pending Approval** | `status = pending` |
| **Posted** | `status = posted` |
| **Paid** | `payment_status = paid` |
| **Partial** | `payment_status = partial` |
| **Overdue** | `payment_status IN (unpaid, partial) AND due_date < today` |
| **Cancelled** | `status = cancelled` |
| **Reversed** | `status = reversed` |

### Purchase Requisitions
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Pending** | `status = pending` |
| **Approved** | `status = approved` |
| **Converted** | `status = converted` (to PO) |
| **Rejected** | `status = rejected` |

### Proforma
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Open** | `status = open` |
| **Converted** | `status = converted` (to invoice) |
| **Void** | `status = void` |

### Expenses
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Pending** | `status = pending` |
| **Approved** | `status = approved` |
| **Paid** | `status = paid` |
| **Rejected** | `status = rejected` |
| **Reversed** | `status = reversed` |

### Payments / Receipts
| Tab | Filter Logic |
|-----|-------------|
| **All** | All records |
| **Today** | `payment_date = today` |
| **This Week** | `payment_date >= start_of_week` |
| **This Month** | `payment_date >= start_of_month` |

---

## 8. INVOICES LIST PAGE (Dedicated)

A full-featured invoice listing page at **Sales > Invoices** (primary sales transaction list).

**Status Tabs:** All | Draft | Proforma | Posted | Paid | Partial | Overdue | Cancelled | Reversed

**Filters:**
- Date Range (From / To)
- Quick Dates: Today, Yesterday, This Week, This Month, Last Month, This Quarter, This Year
- Customer (searchable dropdown)
- Salesperson
- Store / Warehouse
- Payment Method
- Min / Max Amount

**Columns (sortable):**
- Invoice #
- Date
- Customer
- Type (Invoice / Proforma)
- Status
- Payment Status
- Total (TZS)
- Paid (TZS)
- Balance (TZS)
- Due Date
- Salesperson
- Actions (View, Print, Email, Payment, Edit, Cancel)

**Bulk Actions:** Export Selected, Print Selected

**Summary Cards (top of page):**
- Total Outstanding (TZS)
- Overdue Amount (TZS)
- Paid This Month (TZS)
- Invoice Count (this month)

**Inline Actions per row:**
- Record Payment (opens payment drawer)
- Print Invoice (PDF)
- Email Invoice
- View / Edit

---

## 9. NAVIGATION CHANGES

### Remove from Sidebar
- Stakeholders > Category Tree → merge into Categories
- Stakeholders > Pricing Dashboard → merge into Price Lists
- Stakeholders > Customer Dashboard → merge into Customers
- Inventory > Dashboard → merge into main Dashboard
- Inventory > Low Stock → show as card on main Dashboard
- Inventory > Reservations → remove
- Inventory > Cycle Counts → remove from nav until implemented
- Sales > Dashboard → merge into main Dashboard
- Sales > POS Dashboard → merge into POS page
- Procurement > Dashboard → merge into main Dashboard
- Finance > Dashboard → merge into main Dashboard
- Administration > Organization → remove until implemented

### Add to Sidebar
- Stakeholders > Warehouses ✓
- Stakeholders > Tax Settings ✓
- Stakeholders > Payment Terms ✓
- Inventory > Store Requests ✓
- Inventory > Stock Transfers ✓
- Finance > Chart of Accounts ✓
- Finance > Journal Entries ✓
- Finance > Expenses ✓
- Finance > Banking ✓
- Finance > Financial Statements ✓
- Sales > Proforma ✓
- Sales > Invoices ✓ (dedicated list page)
- Sales > Customer Advances ✓
- Sales > Customer Collections ✓
- Sales > Sales Targets ✓
- Administration > Approval Levels ✓
- Administration > Document Numbering ✓
- Administration > Dashboard Cards ✓

### Rename
- "Master Data" → **Stakeholders**
- "Quotations" → **Proforma**
- "Purchase Requisition" → **Purchase Requisitions** (plural)
- "Supplier Analytics" → **Supplier Performance**

---

## 10. WORKFLOW IMPROVEMENTS

| Current | Proposed | Description |
|---------|----------|-------------|
| PO: Draft → Submit → Approve/Reject → Send | Draft → Submit → Level 0-3 Approval → Approved → Issue → Receive | Configurable multi-level with status tabs |
| SO: Create → Submit → Approve → Fulfill | Create (Draft/Posted) → Pending Issue / Awaiting Approval → Approve → Pack → Ship → Invoice | Full order-to-cash, status tabs |
| Store Request: (not exists) | Create → Pending → Level 0-3 → Issue → Receive | Status tabs: All/Pending/Approved/Issued/Received/Rejected |
| Stock Transfer: (not exists) | Create → Pending → Approve → Issue → Receive | Status tabs: All/Pending/Approved/Issued/Received/Rejected |
| Stock Adjustment: unified list | Single page with tabs: All/Draft/Pending/Approved/Completed/Rejected | No separate menu items per status |
| Expense: (not exists) | Create → Pending → Approve (0-3) → Pay → Journal | Status tabs |
| Invoice Payment: one-per-invoice | Multi-invoice, partial payment, advance apply | Invoices list with inline payment |
| Reports: 11 separate pages | 6 pages with filters + tabs + export | One per domain |
| Roles: SuperAdmin + Admin only | 10+ seeded roles with permission matrix | RBAC alignment |

---

## 11. UI & DATA CHANGES

### Sub-Product (Variant) Schema
```sql
ALTER TABLE products ADD COLUMN parent_product_id BIGINT UNSIGNED NULL REFERENCES products(id);
ALTER TABLE products ADD COLUMN has_variants BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN variant_attributes JSON NULL;
```

Views affected: Product index (expandable rows), Product create/edit (Variants tab), ALL transaction line item selectors, search, barcode lookup, price list assignment, stock balance display.

### TZS-Only Pricing — Columns to Remove
- `price_lists.currency`
- `price_list_items.currency`
- `invoices.currency`, `invoices.exchange_rate`
- `purchase_orders.currency`, `purchase_orders.exchange_rate`
- `products.cost_currency` (if exists)
- Remove `settings/currencies` page

### Printable Documents — Template Requirements
Each PDF template must include:
- **Company header:** Logo, name, address, phone, email, TIN, VRN
- **Document metadata:** Doc number, date, reference
- **Counterparty:** Customer/Supplier name, address, TIN
- **Item table:** #, SKU, Description, Qty, UOM, Unit Price, Total
- **Totals:** Subtotal, Discount, VAT (rate + amount), Grand Total
- **Amount in words** (for invoices, LPOs)
- **Signatures:** Prepared by, Reviewed by, Authorized by (for LPOs)
- **Terms & conditions** (for LPOs, invoices)
- **Footer:** Page X of Y, printed date

---

## 12. IMPLEMENTATION ORDER

### Phase 1 (Foundation)
1. Sub-Product (Variant) logic — `parent_product_id`, UI, search
2. TZS-only pricing — migrations, clean columns, simplify views
3. Chart of Accounts — model + CRUD
4. Journal Entry engine — double-entry service + CRUD
5. Warehouses CRUD — model + migration + CRUD
6. Approval Configuration — model + config UI
7. Store Requests — model + workflow with status tabs
8. Stock Transfers — model + workflow with status tabs
9. Expense Management — model + workflow with status tabs
10. Merge Sales Order + Invoice creation
11. Rewrite Invoices list page with full status tabs + filters

### Phase 2 (Navigation & Dashboard)
1. Rename "Master Data" → **Stakeholders** everywhere
2. Rename "Quotations" → **Proforma** everywhere
3. Remove all secondary dashboards
4. Build main Dashboard KPI cards
5. Rewrite `config/erp-modules.php` with new nav
6. Add missing routes for new features
7. Add missing sidebar items
8. Apply status tabs pattern to all existing transaction pages (Stock Adjustment, Purchase Orders, Sales Orders, etc.)

### Phase 3 (Reports & Print)
1. Consolidate 11 report pages → 6 intelligent pages
2. Build Financial Statements (Trial Balance, P&L, Balance Sheet)
3. Build PDF templates for LPO, Invoice, Delivery Note, GRN, SR, Proforma
4. Add CSV/Excel/Print export to all 6 report pages

### Phase 4 (Enhancement)
1. Multi-level approval refactor (centralize)
2. Proforma workflow
3. Customer Advances
4. Banking + Reconciliation
5. Supplier Performance
6. Dashboard Cards config
7. Document Numbering config
8. Special permissions

### Phase 5 (Optimization)
1. Customer Credit Limits
2. Cycle Counts
3. Batch Expiry
4. Saved Filters
5. Scheduled Report Email
6. Checkbox styling audit + consistency pass

---

## 13. TECHNICAL DEBT / CONSIDERATIONS

- **Checkbox styling fix:** Create `<x-checkbox>` Blade component using Bootstrap `.form-check-input` + `.form-check-label`. Audit all views for raw `<input type="checkbox">`. Fix as early hotfix before Phase 1.
- **Route modularization:** Consider splitting `web.php` into per-module route files (Phase 2).
- **Database migrations:** All new features need down migrations for rollback safety.
- **Existing data:** TZS migration needs careful handling of any multi-currency data in existing records.
- **PDF library:** Already using DomPDF — extend with shared layout partials.
- **Status tabs pattern:** Refactor existing controllers/index views to use `request('tab', 'all')` filtering. No new routes needed — one route + one view per transaction module.
