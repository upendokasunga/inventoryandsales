# Product & Sub-Product Logic — Complete Data Flow

## 1. Data Model

### Single-table inheritance

There is **no dedicated `sub_products` table**. Sub-products (variants) are `Product` records with a non-null `parent_id`.

| Column | Purpose |
|---|---|
| `id` | Primary key |
| `name` | Product or variant name |
| `product_code` | Auto-generated for parent products: `PRD-000001` |
| `sub_product_code` | Auto-generated for sub-products: `PARENTCODE-001` |
| `product_id` | Friendly ID: `PID-01` (distinct from `product_code`) |
| `parent_id` | `NULL` for top-level products, FK to parent otherwise |
| `product_type` | Enum: `goods`, `service`, `fixed_asset` |
| `material_type` | Enum: `sale`, `production`, `both`, `kitchen_materials` |
| `price` | Base selling price |
| `retail_price` | Optional retail price with store-level trigger qty |
| `standard_cost` | Standard costing value |
| `costing_method` | `moving_average`, `fifo`, `standard` |
| `income_account_id` | FK to `accounts` — revenue account for sales |
| `cost_center` | Cost centre allocation |
| `category` | Category (children inherit from parent) |
| `barcode` | Unique barcode |
| `unit` | Unit of measure (default `pc`) |
| `is_active` | Soft toggle |
| `is_printing_process` | Flag for printing job-order items |
| `expiry_date` | Optional expiry |
| `brand_code` / `brandcode` | Brand identifier |

### Key relationships on Product

```php
// Self-referential parent/children
public function parent()     { $this->belongsTo(Product::class, 'parent_id'); }
public function children()   { $this->hasMany(Product::class, 'parent_id'); }
public function incomeAccount() { $this->belongsTo(Account::class, 'income_account_id'); }
```

### How sub-products are referenced in documents

| Table | Column | Meaning |
|---|---|---|
| `invoice_lines` | `product_id` | Parent product (e.g. "Samsung TV") |
| `invoice_lines` | `sub_product_id` | Specific variant (e.g. "Samsung 43-inch") |
| `invoice_lines` | `store_id` | Store stock is deducted from |
| `purchase_order_items` | `product_id` | Either parent or sub-product ID |
| `purchase_order_items` | `product_make` | Variant name stored as text (legacy) |
| `goods_receipt_items` | `product_id` | Exact product received |

---

## 2. Product / Sub-Product Creation Flow

### Entry points

| Route | Controller Method |
|---|---|
| `GET /products/create` | `ProductController@create` |
| `POST /products` | `ProductController@store` |
| `GET /products/{product}/edit` | `ProductController@edit` |
| `PUT /products/{product}` | `ProductController@update` |
| `DELETE /products/{product}` | `ProductController@destroy` |

### Listing

- **`ProductController@index`** — Lists top-level products only (`WHERE parent_id IS NULL`).
- **`ProductController@subIndex`** — Lists sub-products only (`WHERE parent_id IS NOT NULL`).

### Create form (`ProductController@create`)

- Loads income accounts (`type = income`), cost centres, and all products as parent options.
- Accepts `?parent_id=N` query param to pre-select the parent (used when "register as sub-product" is suggested).
- Default costing method from `AppSetting::get('inventory_costing_method', 'moving_average')`.

### Store logic (`ProductController@store`)

1. **Duplicate check (top-level only):** If a product with the same name exists and has no `parent_id`, the user is redirected back with an error suggesting they register it as a sub-product of the existing product. The form is re-populated with the duplicate's ID as the parent.

2. **Name uniqueness:** Enforced only for top-level products (`parent_id IS NULL`). Sub-product names are unique within a parent by application logic (not DB constraint).

3. **Auto code generation** (in `Product::booted()` `creating` event):
   - `product_code` — `PRD-000001` (incremented globally)
   - `product_id` — `PID-01` (separate friendly ID)
   - `sub_product_code` — `<parent_product_code>-001` (scoped per parent, e.g. `PRD-000001-001`)

4. **Sub-products inherit parent category** on create and update.

5. **Default values:**
   - `unit` defaults to `'pc'`
   - `material_type` defaults to `'sale'`
   - `costing_method` defaults for `goods`-type products from `AppSetting`
   - `costing_method` and `standard_cost` are cleared for non-goods types

6. **Schema-safe:** Only columns that exist in the DB are written (supports migrations in progress).

### Update logic (`ProductController@update`)

- Name editing requires permission `products.edit-name`.
- Self-parenting is prevented.
- Category changes on a parent propagate to all children (`updated` event).
- Costing fields cleared when product type is not `goods`.

### Delete logic (`ProductController@destroy`)

- Checks on-hand stock across stores via `StockMovement` aggregation.
- Blocks deletion if any store has positive quantity.

### Price resolution (`ProductController@priceJson`)

For AJAX auto-fill on invoice forms. Priority order:
1. **Retail price** — Only if customer belongs to a retail group AND store has `retail_trigger_qty` met.
2. **Latest approved LPO line's `selling_price`** — Exact product match.
3. **Latest approved LPO line's `unit_price`** — Exact product match.
4. **Legacy: parent + `product_make` match** — When the exact product has no PO history, looks up parent's PO lines with matching `product_make` (= variant name).
5. **Parent PO lines (any make)** — Broader fallback.
6. **`Product.price`** — Static price from products table.
7. **`0`** — Last resort.

---

## 3. Sub-Product Selection Rules

### In Purchase Orders (LPOs)

- The **create form** lists both `products` (parents) and `subProducts` (children) separately.
- When a parent is selected, a child can be picked from a sub-product dropdown.
- `product_make` stores the variant name as a text fallback (used when a specific variant isn't in the system yet).
- **`syncPoItems`**: Determines the effective product ID:
  ```php
  $lineProductId = $sub_product_id ?? $product_id;
  $productMake = $sub_product_id ? $sub->name : $product_make;
  ```
- Only the resolved `product_id` is stored on `purchase_order_items`.

### In Invoices (Sales)

- The UI uses an **encoded format** `"sub_id|store_id"` in the `sub_product_id` field to carry both variant and store.
- **`InvoiceController@store`** normalisation:
  1. If the selected `product_id` is actually a child (has `parent_id`), it's moved to `sub_product_id`.
  2. If a parent has children, a sub-product **must** be selected — otherwise, validation fails.
  3. Name-based matching attempts to resolve sub-product by comparing `description` or `product_make` against child names.
  4. Per-line `store_id` is resolved: explicit > encoded sub_product_id > header store.

### In Goods Receipt

- Receiving mirrors the approved LPO exactly. **No sub-product selection during receiving.**
- The LPO line's product (whether parent or sub) is received as-is.
- If the LPO line is a sub-product, the received quantity is credited to that variant's stock.

---

## 4. LPO (Purchase Order) Flow

**LPO = PurchaseOrder** — referred to colloquially as "LPO" throughout the codebase.

### States

```
Draft → Pending → Approved → Reversed
                → Rejected
```

### Create flow (`PurchaseOrderController@store`)

1. **Validation**: Supplier, date, items (product + quantity + unit_price + optional selling_price), currency, VAT, discount.
2. **Number generation**: `PO-YYYY-0001` (via `generateNextPoNumber`, uses max ID + 1) or `DRAFT-YYYYMMDD-HHMMSS-RANDOM` for drafts.
3. **Multi-currency**: All amounts stored in base currency (TZS) using exchange rate.
4. **Draft vs Pending**: Drafts skip approval; pending triggers validation.
5. **Level-0 approval**: If `Approvals::level('purchase_orders') === 0`, the PO is automatically finalised immediately:
   - Budget line validation & commitment
   - Auto-paid supplier payment + payment voucher + journal entry (DR AP, CR Bank)
6. **Prefill from base64**: `?prefill=...` decodes a JSON payload of items (used by printing job orders).

### Approval workflow (`PurchaseOrderController@approve`)

- Configurable **1–3 step** approval via `Approvals` support class.
- Each step requires user's group to have the corresponding permission.
- Same user cannot approve multiple steps unless `allowSameUserMultipleApprovalSteps()` returns true.
- **Final approval does:**
  1. Validates budget line (`project_budget_line_id`).
  2. Creates **auto-paid** `SupplierPayment` (status = `paid`).
  3. Creates `PaymentVoucher`.
  4. Posts journal entry: **DR Accounts Payable, CR Bank/Cash**.
  5. Commits budget line amount.
  6. Marks PO as `approved`.

### Reversal (`PurchaseOrderController@reverse`)

- Blocks if goods have been received (any non-reversed `GoodsReceipt`).
- Blocks if supplier payments exist beyond `pending` status.
- Reverses journal entries: `PO-{number}` and `GRN-{number}` and `GR-LIB-{receiptId}`.

---

## 5. Goods Receipt Flow

**StockReceiveController** handles receiving goods against an approved PO.

### Show form (`StockReceiveController@show`)

- Loads PO items with received-to-date and remaining quantities.
- Locks receiving to the PO's designated store (`po.store_id`).
- No sub-product selection — mirrors PO line exactly.

### Store logic (`StockReceiveController@store`)

1. **Validates**: Store, items (must match PO line product), remaining qty, no over-receiving.
2. **Store type validation** (fixed_asset vs goods vs kitchen_materials vs bar) — mixing types is blocked.
3. **Creates `GoodsReceipt` + `GoodsReceiptItem` records**.
4. **Stock Movement IN**: For goods-type products, creates `StockMovement` with type `in`, reference `goods_receipt`.
5. **Bar store price sync**: Updates `Product.price` with PO `selling_price` when receiving into a Bar store.
6. **Supplier Payment Request**: Creates a `SupplierPayment` (status = `pending`) for the received value.
7. **Journal Entry** (`GR-LIB-{receiptId}`):
   - **DR Inventory** (or Fixed Asset account for fixed_asset items)
   - **CR Accounts Payable (Suppliers)**

---

## 6. Sales / Invoice Flow

**Sales flow through Invoices.** There is no separate SalesOrder model.

### Invoice states

```
Draft → Posted (immediate for level 0)
      → Paid
      → Void
      → Reversed
```

### Proforma vs Real Invoice

- **Proforma** (`?proforma=true`): Draft status, no stock deduction, no journal posting, number prefix `PRO-YYYY-SEQ`.
- **Real invoice**: Stock deducted, journal posted, number prefix `INV-YYYY-SEQ`.

### Invoice creation (`InvoiceController@store`)

1. **Line normalisation**: Encoded `sub_product_id|store_id` parsed; child products mapped to sub_product_id.
2. **Store resolution**: Header store or per-line store; user must be assigned to store(s).
3. **Sub-product enforcement**: Parent products with children require variant selection.
4. **Fixed asset blocking**: Cannot be sold through invoices.
5. **Auto-price**: Defaults to latest PO `selling_price` if `unit_price` is 0.
6. **Stock validation (level 0)**:
   - Computes available = `SUM(goods_receipt_items.quantity_received)` + `SUM(stock_movements.quantity)` for the exact (store, product).
   - Family-level aggregation: If a parent has children and no sub selected, aggregates all children's stock.
   - Printing-process items exempted.
   - If stock control is OFF, shortages trigger auto-creation of a `StoreRequest`.
7. **Journal posting (level 0)**:
   - **DR Accounts Receivable** (1200) — total
   - **CR Sales Revenue** (per line's `income_account_id`)
   - **CR VAT Output** (2100) — if applicable
   - **DR Sales Discounts** (4050) — if discount applied
   - **DR COGS** (5100) — using configured costing method (FIFO/Moving Average/Standard)
   - **CR Inventory** (1300)
8. **Stock Movement OUT**: Records stock deduction per line.
9. **Receipt journal**: If `paid_amount > 0`, posts **DR Bank/Cash, CR AR**.
10. **Store Request**: Auto-creates an issued delivery note for every posted invoice.

### Draft submission (`InvoiceController@submitDraft`)

- Converts draft to posted with new `INV-YYYY-SEQ` number.
- Creates journals and stock movements using same logic as `store()`.

### Returns & Credit Notes

- **`InvoiceController@returnCreate/returnStore`**: Customer returns with stock IN + credit note journal.
- **`InvoiceController@discountCreate/discountStore`**: Discount application with credit note (CRN) journal.
- **`InvoiceController@creditNotes`**: Lists credit notes (journal entries with `CRN-` prefix).

### Costing methods (`InvoiceController@computeIssueCost`)

| Method | Logic |
|---|---|
| **Standard** | `qty × product.standard_cost` |
| **FIFO** | Consumes receipts in chronological order, oldest first |
| **Moving Average** | Weighted average: `SUM(qty × unit_price) / SUM(qty)` across all receipts in store |

Fallback chain: latest PO `unit_price` → `product.standard_cost` → 0.

---

## 7. Stock Movement Model

`StockMovement` records every stock in/out transaction with normalised quantities:
- `type = 'in'` → positive quantity
- `type = 'out'` → negative quantity

**Negative stock guard**: On `creating`, checks the projected balance and throws if it would go negative.

Types: `in`, `out`, `receive`, `received`, `adjust_in`, `adjustment_in`, `transfer_in`, `opening`, `issue`, `issued`, `adjust_out`, `adjustment_out`, `transfer_out`, `consumption`.

Reference types: `invoice`, `goods_receipt`, `store_request`, `adjustment`, `transfer`, `production`, etc.

---

## 8. End-to-End Flow Summary

```
                    ┌──────────────────────────────────────────────────────┐
                    │                  PRODUCT CREATION                    │
                    │  ┌───────────────────┐    ┌──────────────────────┐  │
                    │  │  Parent Product    │    │  Sub-Product (child) │  │
                    │  │  parent_id = NULL  │    │  parent_id = parent  │  │
                    │  │  product_code      │    │  sub_product_code    │  │
                    │  │  = PRD-000001      │    │  = PRD-000001-001   │  │
                    │  └────────┬──────────┘    └──────────┬───────────┘  │
                    └───────────┼──────────────────────────┼──────────────┘
                                │                          │
              ┌─────────────────┼──────────────────────────┼──────────────┐
              │   PROCUREMENT   │                          │              │
              │                 ▼                          ▼              │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │         PURCHASE ORDER (LPO)                     │    │
              │  │  - Item: product_id (parent OR sub)              │    │
              │  │  - Product_make stores variant name (text)       │    │
              │  │  - Selling_price for retail price propagation    │    │
              │  └─────────────────────┬────────────────────────────┘    │
              │                        │ approval                        │
              │                        ▼                                 │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │  Approval (1-3 steps)                            │    │
              │  │  → Auto-paid: SupplierPayment + PaymentVoucher   │    │
              │  │  → Journal: DR AP, CR Bank                       │    │
              │  │  → Budget commitment                              │    │
              │  └─────────────────────┬────────────────────────────┘    │
              │                        │ status = approved               │
              │                        ▼                                 │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │  GOODS RECEIPT                                  │    │
              │  │  - Mirrors PO items exactly (no variant switch) │    │
              │  │  - StockMovement IN for goods-type products     │    │
              │  │  - Journal: DR Inventory, CR AP                 │    │
              │  │  - SupplierPayment (pending)                    │    │
              │  └─────────────────────┬────────────────────────────┘    │
              └────────────────────────┼─────────────────────────────────┘
                                       │
              ┌────────────────────────┼─────────────────────────────────┐
              │   SALES                │                                 │
              │                        ▼                                 │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │  INVOICE (Sales)                                 │    │
              │  │  - Line: product_id (parent)                     │    │
              │  │         sub_product_id (variant)                 │    │
              │  │         store_id (deduction source)              │    │
              │  │  - Sub-product MUST be selected if parent has    │    │
              │  │    children                                      │    │
              │  │  - Auto-price from latest PO selling_price       │    │
              │  │  - Stock validation by (store, product)          │    │
              │  └─────────────────────┬────────────────────────────┘    │
              │                        │ post                            │
              │                        ▼                                 │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │  Posting (Level 0 immediate)                     │    │
              │  │  → StockMovement OUT per line (sub_product_id)   │    │
              │  │  → Journal: DR AR, CR Sales (+ VAT, Discount)    │    │
              │  │  → COGS: DR COGS, CR Inventory (FIFO/AVG/STD)   │    │
              │  │  → Receipt Journal: DR Bank, CR AR (if paid)     │    │
              │  │  → Auto Store Request (delivery note)            │    │
              │  └──────────────────────────────────────────────────┘    │
              │                                                          │
              │  Returns:                                                │
              │  ┌──────────────────────────────────────────────────┐    │
              │  │  RETURN / CREDIT NOTE                           │    │
              │  │  → StockMovement IN (stock back to store)        │    │
              │  │  → Journal: DR Sales, CR AR (credit note)       │    │
              │  └──────────────────────────────────────────────────┘    │
              └──────────────────────────────────────────────────────────┘
```

## 9. Key Code Files

| File | Purpose |
|---|---|
| `app/Models/Product.php` | Product model with self-referential parent/children, code generation |
| `app/Http/Controllers/ProductController.php` | CRUD + price JSON + duplicate detection |
| `app/Http/Controllers/PurchaseOrderController.php` | LPO create, approve (multi-step), reject, reverse |
| `app/Http/Controllers/StockReceiveController.php` | Goods receipt against approved PO |
| `app/Http/Controllers/InvoiceController.php` | Invoice create, draft submit, returns, credit notes, discount |
| `app/Http/Controllers/SalesController.php` | Simplified sales UI (new sale form) |
| `app/Models/PurchaseOrder.php` | PurchaseOrder model |
| `app/Models/PurchaseOrderItem.php` | PO line items (product_id, product_make, unit_price, selling_price) |
| `app/Models/Invoice.php` | Invoice model |
| `app/Models/InvoiceLine.php` | Invoice lines (product_id, sub_product_id, store_id) |
| `app/Models/GoodsReceipt.php` | Goods receipt model |
| `app/Models/GoodsReceiptItem.php` | Received items (product_id) |
| `app/Models/StockMovement.php` | Stock in/out tracking with negative balance guard |
| `app/Support/InventoryAccounts.php` | Resolves Inventory account (code 1300) |
| `app/Support/DocumentPrefixes.php` | Document numbering configuration |
| `app/Support/Approvals.php` | Multi-level approval configuration |

## 10. Design Decisions

1. **No separate SalesOrder model** — sales flow directly through Invoices with draft → posted status.
2. **No separate SubProduct model** — sub-products are Products with `parent_id` set. This simplifies queries but requires careful enforcement of variant selection.
3. **LPO line `product_make`** — serves as a text-based variant identifier when structured sub-products aren't available; used for price lookup.
4. **Stock tracking by exact variant** — every `StockMovement` records the exact `product_id`. Invoice validation enforces per-variant availability.
5. **Family-level stock aggregation** — When a parent product has children, stock checking can aggregate across all children. This was changed to strict per-variant to prevent unintended substitution.
6. **Costing method per product** — Each goods product has its own `costing_method` (moving_average, fifo, standard) with a system-wide default.
7. **Multi-currency support** — All monetary values stored in base currency (TZS) with exchange rate recorded on the document.
