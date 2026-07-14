# Invoice, Proforma, LPO & Payment Logic — Goods for Sale Only

## Table of Contents
1. [Invoice Creation (Sales Controller)](#1-invoice-creation-sales-controller)
2. [Invoice Create View](#2-invoice-create-view)
3. [Sales New View (POS-style)](#3-sales-new-view-pos-style)
4. [Proforma Flow](#4-proforma-flow)
5. [Invoice Posting Logic (InvoiceController)](#5-invoice-posting-logic-invoicecontroller)
6. [Draft Submission](#6-draft-submission)
7. [Returns, Discounts & Credit Notes](#7-returns-discounts--credit-notes)
8. [LPO (Purchase Order) Creation](#8-lpo-purchase-order-creation)
9. [LPO Approval Workflow](#9-lpo-approval-workflow)
10. [Goods Receipt (Stock Receive)](#10-goods-receipt-stock-receive)
11. [Supplier Payment Flow](#11-supplier-payment-flow)
12. [End-to-End Payment Cycle](#12-end-to-end-payment-cycle)
13. [Key Code Files](#13-key-code-files)

---

> **Scope:** This document covers only **goods for sale** (finished goods held for resale). Excluded: fixed assets, raw materials, kitchen supplies, services, and internal-use items.

## 1. Invoice Creation (Sales Controller)

There are **two entry points** for creating invoices:

### 1A. Simplified POS-style: `SalesController@new` + `SalesController@store`

| Route | Method | Purpose |
|---|---|---|
| `GET /sales/new` | `SalesController@new` | POS-style sale form (Alpine.js driven) |
| `POST /sales` | `SalesController@store` | Submit the sale (creates Invoice) |
| `POST /sales/drafts` | `SalesController@storeDraft` | Save as draft |
| `POST /sales/{invoice}/pay-send` | `SalesController@payAndSend` | Pay & send receipt |

### 1B. Traditional form: `InvoiceController@create` + `InvoiceController@store`

| Route | Method | Purpose |
|---|---|---|
| `GET /invoices/create` | aborts 404 (replaced by `/sales/new`) | |
| `POST /invoices` | aborts 404 (replaced by `/sales`) | |
| `GET /invoices/{invoice}/edit` | `InvoiceController@edit` | Edit invoice |
| `PUT /invoices/{invoice}` | `InvoiceController@update` | Update invoice |

### `SalesController@new` — Data Loaded

```
SalesController@new()
├─ $customers        ← Customer::orderBy('name')->get()
├─ $products         ← Product::whereNull('parent_id')
│                        ->where('product_type','!=','fixed_asset')
│                        ->whereIn('material_type',['', 'sale', 'both'])
│                        ->orderBy('name')->get()
├─ $subProducts      ← Product::whereNotNull('parent_id') [same filters]
├─ $stores           ← Store::where('is_active',true)->orderBy('name')
├─ $paymentAccounts  ← Account::where('type','asset')->where('is_active',true)
│                        ->whereNotNull('parent_id')  // leaf accounts only
│                        ->where(function($q){ $q->like('name','%cash%')->orLike('%bank%'); })
├─ $costCenters      ← CostCenter::pluck('name')
├─ $paymentTerms     ← PaymentTerm::where('is_active',true)->orderBy('sort')
├─ $projects         ← Project::where('status','!=','closed')
├─ $userStores       ← auth()->user()->stores()->pluck('id')
├─ $salespersons     ← Employee::salespeople (via role/permission)
├─ $advanceBalances  ← Customer advance balances per customer
└─ $loyaltyInfo      ← Loyalty card points per customer
```

---

## 2. Invoice Create View

**File:** `resources/views/invoices/create.blade.php` (2306 lines)

**Design:** Floating glass-morphism cards (`floating-card` class), `Times New Roman` serif font throughout, decorative background blobs, backdrop blur.

**Sections in order:**

### 2.1 Proforma Mode Banner
- Yellow info banner: "This invoice will be saved as a draft proforma. No journal entries or payments will be posted."
- Hidden input `proforma=1` when in proforma mode.

### 2.2 Header Fields Row
| Field | Control | Notes |
|---|---|---|
| **Currency** | Select (TZS/USD/EUR/GBP/KES/UGX/ZAR/Custom) | Custom shows hidden text input |
| **Exchange Rate** | Number input | To base currency |
| **Date** | Date picker | Backdate controlled by `Approvals::userCanBackdate()`. Max = today. |
| **Store** | Select | User's assigned stores. "All Stores" option available. |
| **Customer** | Select | All customers |
| **Sales Person** | Select | Optional, from salespeople list |
| **Cost Center** | Select | From cost centers |
| **Project** | Select | From active projects |
| **Payment Terms** | Select | From active payment terms |

### 2.3 Lines Table
| Column | Control |
|---|---|
| **Product** | Smart select (searchable dropdown, shows parent products with sub-products as indented options) |
| **Sub Product** | Conditional dropdown (appears when selected product has children) |
| **Description** | Text input |
| **Store** | Per-line store select (shown when header store is "All Stores") |
| **Qty** | Number input |
| **Unit Price** | Number input (auto-filled via AJAX from `SalesController@available`) |
| **Available** | Read-only badge (computed on the fly, live stock check) |
| **Line Total** | Computed read-only |
| **Actions** | Remove button |

### 2.4 Totals & Options Section
Left column: **VAT** (checkbox + rate), **Discount** (type: percent/amount + value), **Payment Terms**, **Remark**

Middle column: **Running totals** — Subtotal, Discount, Redeem (loyalty), VAT, Total, Equivalent in base currency

Right column: **Payment** — Payment account select, Paid amount, Redeem loyalty points, Customer advance balance

### 2.5 Footer Actions
- **Cancel** button
- **Save Draft** button (submits with `status=draft` or via `sales.drafts.store`)
- **Save & Post** button (main submit)

---

## 3. Sales New View (POS-style)

**File:** `resources/views/sales/new.blade.php` (1428 lines)

**Design:** Responsive POS layout with Alpine.js `x-data="newSalePage()"`. Sticky action bar on mobile.

### 3.1 Left Panel: Header
| Component | Implementation |
|---|---|
| **Customer Search** | Alpine.js `customerSearch()` — live search with dropdown, arrow-key navigation, "Add New Customer" inline modal |
| **Store Selection** | Select with "All Stores" option. Filters visible products by store assignment |
| **Sales Person** | Select |
| **Currency** | Select + Exchange rate input |
| **Cost Center** | Select |
| **Project** | Select |
| **Sales Date** | Date picker |

### 3.2 Product Search
- Search-as-you-type input with `@input="filterProducts()"`
- Displays suggestions in a scrollable dropdown (max-h-56)
- Each suggestion shows product name + type badge
- Clicking a product calls `addProduct(p)` which adds a line

### 3.3 Lines Table
| Column | Control | Notes |
|---|---|---|
| `#` | Row number | Auto |
| **Product** | Read-only text | From product search selection |
| **Variant** | Read-only text or select | Auto-resolved from sub-products |
| **Description** | Text input | Optional |
| **Store** | Per-line select | Only when `header.store_id === 'all'` |
| **Qty** | Number input | `@input="onQtyInput()"` triggers availability check |
| **Unit Price** | Number input | Auto-filled from price resolution |
| **Available** | Badge | `fetchAvailForLine()` — live stock query |
| **Line Total** | Computed | `qty * unit_price` |
| **Actions** | Remove button | Red delete button |

### 3.4 Totals Panel (Right Column)
- **Subtotal**, **Discount**, **Redeem** (loyalty points), **VAT**, **Total**
- Equivalent in base currency shown when foreign currency

### 3.5 Payment & Actions
- **Loyalty Redemption** (shown when customer has loyalty card)
- **Customer Advance** (shown when customer has positive advance balance)
- **Payment Account** select
- **Paid Amount** input
- **Submit** button (all inline JS validation before POST)

---

## 4. Proforma Flow

### 4.1 Routes

| Route | Method | Purpose |
|---|---|---|
| `GET /sales/proforma/new` | `SalesController@proformaNew` | Proforma create form |
| `POST /sales/proforma` | `SalesController@storeProforma` | Submit proforma |
| `GET /invoices/proformas` | `InvoiceController@proformas` | List open proformas |
| `GET /invoices/proformas/closed` | `InvoiceController@proformasClosed` | List closed proformas |
| `GET /invoices/proformas/{invoice}/edit` | `InvoiceController@editProforma` | Edit proforma |
| `GET /invoices/proformas/{invoice}/generate` | `InvoiceController@generateProforma` | Convert proforma → real invoice |
| `POST /invoices/{invoice}/close-proforma` | `InvoiceController@archiveProforma` | Archive/close proforma |
| `POST /invoices/proformas/{invoice}/update-json` | `InvoiceController@updateProformaJson` | Save proforma JSON |
| `POST /invoices/proformas/{invoice}/generate-json` | `InvoiceController@generateProformaJson` | Generate proforma JSON |

### 4.2 Proforma vs Real Invoice

| Aspect | Proforma | Real Invoice |
|---|---|---|
| **Status** | `draft` | `posted` |
| **Number prefix** | `PRO-YYYY-SEQ` | `INV-YYYY-SEQ` |
| **Stock deduction** | ❌ No | ✅ Yes |
| **Journal posting** | ❌ No | ✅ Yes (AR, Revenue, VAT, COGS) |
| **Payment** | Allowed (stored on invoice) | Required if paid_amount > 0 |
| **Approval required** | No | Configurable (level 0 = immediate) |

### 4.3 Proforma Creation Flow (`storeProforma`)

1. Simple validation (customer, date, lines with product+qty+price)
2. Creates invoice with `status = 'draft'` and proforma number (`PRO-YYYY-SEQ`)
3. Stores `proforma_json` field with serialized line data (for print templates)
4. No stock validation, no journals, no payments processed
5. Redirects to proforma list with success message

### 4.4 Proforma → Invoice (`generateProforma`)

1. Loads proforma with `proforma_json`
2. Shows editable preview (same as invoice create view but pre-filled)
3. On confirm, follows the same posting routine as `InvoiceController@store`:
   - Validates stock availability
   - Generates real invoice number (`INV-YYYY-SEQ`)
   - Posts journals (AR, Revenue, VAT, COGS, Discounts)
   - Deducts stock
   - Creates auto Store Request (delivery note)

---

## 5. Invoice Posting Logic (InvoiceController)

**File:** `InvoiceController@store` (lines 1910–3303, ~1394 lines)

### 5.1 Input Normalization (lines ~1910-1970)

```php
// Encoded format "sub_id|store_id" is parsed
// Commas stripped from numbers
// 'all' store mapped to null
// Per-line store_id derived when header store missing
```

### 5.2 Validation (lines ~1970-2060)

```php
$data = $request->validate([
    'customer_id' => 'required|exists:customers,id',
    'date' => 'required|date|before_or_equal:today',
    'currency_code' => 'nullable|string|max:10',
    'exchange_rate' => 'nullable|numeric|min:0.00000001',
    'discount_type' => 'nullable|in:amount,percent',
    'discount_value' => 'nullable|numeric|min:0',
    'vat_rate' => 'nullable|numeric|min:0|max:100',
    'paid_amount' => 'required|numeric|min:0',
    'payment_account_id' => 'nullable|exists:accounts,id',
    'store_id' => 'nullable|integer|exists:stores,id',
    'lines' => 'required|array|min:1',
    'lines.*.product_id' => 'nullable|exists:products,id',
    'lines.*.sub_product_id' => 'nullable|exists:products,id',
    'lines.*.quantity' => 'required|numeric|min:0.01',
    'lines.*.unit_price' => 'nullable|numeric|min:0',
    'lines.*.store_id' => 'nullable|integer|exists:stores,id',
]);
```

Key rules:
- Backdate allowed only with `Approvals::userCanBackdate()` permission
- Paid amount required for real invoices (optional for proformas)
- At least 1 line required

### 5.3 Price Resolution & Line Processing (lines ~2060-2220)

For each line:
1. If `unit_price` is 0 or missing, auto-fill from latest PO `selling_price` (via `ProductController@priceJson` fallback chain)
2. Compute line total: `qty × unit_price`
3. Apply discount, compute VAT
4. Convert all amounts to base currency via exchange rate
5. Resolve `sub_product_id`:
   - If `product_id` is actually a child (has `parent_id`), move to `sub_product_id`
   - If parent has children, sub-product **must** be selected
   - Name-based matching against `description` or `product_make`

### 5.4 Stock Validation (inline `$availableFor` closure, lines 2227-2263)

```php
$availableFor = function (int $parentId, int $subId) use ($storeId): float {
    if ($subId === 0) {
        $hasChildren = Product::where('parent_id', $parentId)->exists();
    }
    if ($hasChildren) {
        // Aggregate all children stock
        $childIds = Product::where('parent_id', $parentId)->pluck('id');
        $received = GoodsReceiptItem::join('goods_receipts', ...)
            ->whereIn('product_id', $childIds)->sum('quantity_received');
        $moved = StockMovement::whereIn('product_id', $childIds)
            ->selectRaw("SUM(CASE WHEN reference_type='goods_receipt' THEN 0 ELSE quantity END)")->value();
        return $received + $moved;
    } else {
        // Single product stock
        $pid = $subId ?: $parentId;
        $received = GoodsReceiptItem::join('goods_receipts', ...)->where('product_id',$pid)->sum('quantity_received');
        $moved = StockMovement::where('product_id',$pid)
            ->selectRaw("SUM(CASE WHEN reference_type='goods_receipt' THEN 0 ELSE quantity END)")->value();
        return $received + $moved;
    }
};
```

**Available = SUM(goods_receipt_items.quantity_received) + SUM(stock_movements.quantity)**

Note: Stock movements of type `goods_receipt` are excluded to avoid double-counting (they mirror GR items).

Family-level aggregation: When parent has children and no sub selected, aggregates all children's stock.

### 5.5 Approval Level Check

- `Approvals::level('sales') === 0` → immediate posting
- Level > 0 → save as pending, no journals/stock movements

### 5.6 Invoice Number Generation

```php
$invPrefix = DocumentPrefixes::get('inv_prefix', 'INV');
// Format: INV-YYYY-SEQ (e.g., INV-2026-0042)
// Branch-specific: when user has branch, prefix may include branch code
```

### 5.7 Journal Posting (Level 0)

For each posted invoice, the following journal entry is created:

```
DR  Accounts Receivable (1200)         → invoice.total
CR  Sales Revenue (per income_account)  → line.line_total (per line)
CR  VAT Output (2100)                   → vat_amount (if applicable)
DR  Sales Discounts (4050)              → discount_amount (if applicable)
DR  COGS (5100)                         → computeIssueCost() per line
CR  Inventory (1300)                    → computeIssueCost() per line
```

### 5.8 COGS Computation (`computeIssueCost`)

**Method resolution order:**

| Method | Logic |
|---|---|
| **Standard** | `qty × product.standard_cost` |
| **FIFO** | Consumes `goods_receipt_items` in chronological order (oldest first), takes `qty × unit_price` per batch until qty exhausted |
| **Moving Average** | Weighted avg: `SUM(qty × unit_price) / SUM(qty)` across all receipts in store |

**Fallback chain** (when no receipts exist):
1. Latest PO item's `unit_price`
2. `product.standard_cost`
3. `0`

### 5.9 Stock Movement OUT

For each goods-type line:
```php
StockMovement::create([
    'store_id' => $lineStoreId,
    'product_id' => $pidForMove,  // sub_product_id ?? product_id
    'quantity' => -1 * $qty,
    'type' => 'out',
    'reference_type' => 'invoice',
    'reference_id' => $invoice->id,
    'reference_number' => $invoice->number,
]);
```

Service and fixed_asset types are skipped.

### 5.10 Receipt Journal (if paid_amount > 0)

```php
DR  Cash/Bank (payment_account_id)     → paid_amount
CR  Accounts Receivable (1200)          → paid_amount
```

Reference: `RCPT-INV-YYYY-SEQ`

### 5.11 Auto Store Request (Delivery Note)

For every posted invoice, an auto-issued StoreRequest is created:
- Type: delivery note
- Auto-approved and auto-issued
- Links stock movements to the store request

---

## 6. Draft Submission

### `InvoiceController@submitDraft` (lines 92–308)

**Flow:**
1. Validates invoice is in `draft` status
2. Requires store selection if not already present
3. Requires payment account if `paid_amount > 0`
4. Generates new invoice number (`INV-YYYY-SEQ`)
5. Posts journal (AR, Revenue, VAT, Discount)
6. Posts receipt journal if paid_amount > 0
7. Creates stock movements OUT for goods lines
8. Updates status to `posted`, sets `posted_by`

### `SalesController@storeDraft`

Alternative draft saving path from the POS UI. Same logic — creates invoice with `status = 'draft'`.

---

## 7. Returns, Discounts & Credit Notes

### 7.1 Return Flow

| Route | Method | Purpose |
|---|---|---|
| `GET /invoices/{invoice}/return` | `InvoiceController@returnCreate` | Return form |
| `POST /invoices/{invoice}/return` | `InvoiceController@returnStore` | Process return |

**`returnStore` logic (lines 853–969):**
1. Validates: store, items (line_id + quantity), reason
2. Validates quantities don't exceed original invoice line quantities
3. Pro-rates discount and VAT relative to returned amount
4. Creates StockMovement IN for goods lines (stock back to store)
5. Creates a Credit Note journal entry (`CRN-INV-{number}-{timestamp}`)
   - No journal lines (document-only)
   - Links stock movements to CRN reference
6. Invoice amount remains unchanged (apply discount separately if needed)

### 7.2 Discount Flow

| Route | Method | Purpose |
|---|---|---|
| `GET /invoices/{invoice}/discount` | `InvoiceController@discountCreate` | Discount form |
| `POST /invoices/{invoice}/discount` | `InvoiceController@discountStore` | Apply discount |

**`discountStore` logic (lines 334–578):**
1. Only for `unpaid` or `partial` invoices
2. Recomputes: discount (amount or percent), VAT, total
3. Updates invoice record with new values
4. Creates Credit Note (`CRN-...`) for the discount amount
5. Optional write-off of remaining outstanding balance (bad debt expense)

### 7.3 Credit Notes

| Route | Method | Purpose |
|---|---|---|
| `GET /invoices/credit-notes` | `InvoiceController@creditNotes` | List credit notes |
| `GET /invoices/credit-notes/{ref}/print` | `InvoiceController@creditNotePrint` | Print credit note |

Credit notes are `JournalEntry` records with reference starting with `CRN-`.

---

## 8. LPO (Purchase Order) Creation

### 8.1 Routes

| Route | Method | Purpose |
|---|---|---|
| `GET /purchase-orders` | `index` | List pending POs |
| `GET /purchase-orders/create` | `create` | Create form |
| `POST /purchase-orders` | `store` | Submit PO |
| `GET /purchase-orders/drafts` | `drafts` | List drafts |
| `GET /purchase-orders/drafts/{po}/edit` | `editDraft` | Edit draft |
| `PUT /purchase-orders/drafts/{po}` | `updateDraft` | Update draft |
| `POST /purchase-orders/drafts/{po}/submit` | `submitDraft` | Submit draft → pending |
| `GET /purchase-orders/{po}` | `show` | View PO detail |
| `POST /purchase-orders/{po}/approve` | `approve` | Approve PO |
| `POST /purchase-orders/{po}/reject` | `reject` | Reject PO |
| `POST /purchase-orders/{po}/reverse` | `reverse` | Reverse PO |
| `GET /purchase-orders/approved` | `approved` | List approved POs |
| `GET /purchase-orders/rejected` | `rejected` | List rejected POs |
| `GET /purchase-orders/reversed` | `reversed` | List reversed POs |

### 8.2 LPO Create View

**File:** `resources/views/purchase_orders/create.blade.php` (1143 lines)

**Structure:**

**Section 1 — Purchase Order Details:**
| Field | Control |
|---|---|
| **Date** | Date picker |
| **Supplier** | Select with "Not in list..." option |
| **Currency** | Select (TZS/USD/EUR) |
| **Exchange Rate** | Number input (shown when currency ≠ TZS) |
| **Description** | Text input |
| **Cost Center** | Select |
| **Project** | Select |

**Section 2 — VAT & Discount Settings:**
| Field | Control |
|---|---|
| **VAT Applicable** | Checkbox (toggles VAT rate input) |
| **VAT %** | Number input |
| **Discount** | Select (None/Percent/Amount) + value input |

**Section 3 — Order Items Table:**
| Column | Control |
|---|---|
| **Product** | Smart select with searchable dropdown (Alpine.js `productPicker`) |
| **Sub Product** | Smart select (Alpine.js `subProductPicker`, disabled until product selected) |
| **Qty** | Number input |
| **Unit Cost** | Number input |
| **Selling Price** | Number input (for goods-for-sale items, used as the resale price) |
| **Total** | Read-only computed |
| **Action** | Remove button |

**Section 4 — Order Summary:**
- Subtotal, VAT, Discount, Total (in document currency)
- Equivalent in TZS (shown when foreign currency)

**Section 5 — Actions:**
- **Cancel** | **Save Draft** | **Create Purchase Order** (main submit)

**Section 6 — Level 0 Auto-Approval Modal:**
When `Approvals::level('purchase_orders') === 0`, the "Create Purchase Order" button opens a modal requiring:
- Budget selection
- Sub-budget line selection
- Payment account selection
- Receiving store selection

### 8.3 LPO Store Logic

**`PurchaseOrderController@store` (lines 126–305):**

1. **Validation** — supplier, date, items (product+qty+unit_price+optional selling_price), VAT, discount
2. **Number generation**:
   - Draft: `DRAFT-YYYYMMDD-HHMMSS-RANDOM6`
   - Pending: `PO-YYYY-0001` (via `generateNextPoNumber`, uses max ID + 1)
3. **Multi-currency**: All amounts stored in base currency (TZS) via exchange rate conversion:
   ```php
   $subtotalTzs = round($subtotal * $rate, 2);
   $totalTzs = round($total * $rate, 2);
   ```
4. **Item syncing** (`syncPoItems`): Stores each item with resolved product_id (sub_product_id ?? product_id), product_make as variant name text
5. **Level-0 immediate finalization** (when `Approvals::level('purchase_orders') === 0`):
   - Validates budget line
   - Calls `finalizePurchaseOrder`:
     - Creates auto-paid SupplierPayment
     - Creates PaymentVoucher
     - Posts journal entry: DR Accounts Payable (2100), CR Bank/Cash
     - Commits budget line amount
   - Redirects to goods receipt page
6. **Draft**: Saved as `status=draft`, no budget checks
7. **Prefill from base64**: `?prefill=...` decodes JSON payload of items (used by printing job orders)

---

## 9. LPO Approval Workflow

### States
```
Draft ──→ Pending ──→ Approved ──→ Reversed
                    ──→ Rejected
```

### Approval Steps (`PurchaseOrderController@approve`, lines 813–1042)

1. **Configurable 1–3 steps** via `Approvals` support class
2. Each step requires user group to have corresponding permission
3. Same user cannot approve multiple steps unless `allowSameUserMultipleApprovalSteps()` returns true
4. **Budget validation**: Before final approval, validates:
   - Budget line exists and has sufficient remaining balance
   - `remaining = line_total - committed_amount - spent_amount`
   - PO total must not exceed remaining

5. **Final approval does** (inside DB transaction):
   - Validates payment account
   - Creates **auto-paid** SupplierPayment
   - Creates PaymentVoucher (status = paid)
   - Posts journal entry: **DR Accounts Payable, CR Bank/Cash**
   - Commits budget line amount
   - Updates PO status to `approved`
   - Creates AuditLog entry

6. **Pessimistic locking**: `lockForUpdate()` prevents concurrent approval race conditions

### Rejection (`PurchaseOrderController@reject`, lines 1294–1307)
- Sets status to `rejected`
- Records reason, rejected_by, rejected_at

### Reversal (`PurchaseOrderController@reverse`, lines 1309–1437)
- Blocks if goods have been received (any non-reversed GoodsReceipt)
- Blocks if supplier payments exist beyond `pending` status
- Reverses journal entries: `PO-{number}` → `PO-REV-{number}`, `GRN-{number}` → `GRN-REV-{number}`, `GR-LIB-{receiptId}` → `GR-LIB-REV-{id}`
- Removes pending supplier payments
- Reverts budget commitment

---

## 10. Goods Receipt (Stock Receive)

### 10.1 Routes

| Route | Method | Purpose |
|---|---|---|
| `GET /stock/receive` | `StockReceiveController@index` | List POs ready to receive |
| `GET /stock/receive/{purchase_order}` | `StockReceiveController@show` | Receive form |
| `POST /stock/receive/{purchase_order}` | `StockReceiveController@store` | Save receipt |
| `GET /stock/received` | `ReceivedGoodsController@index` | List past receipts |
| `GET /stock/received/{receipt}` | `ReceivedGoodsController@show` | View receipt |
| `POST /stock/received/{receipt}/reverse` | `ReceivedGoodsController@reverse` | Reverse receipt |

### 10.2 Receive Form View

**File:** `resources/views/stock/receive/show.blade.php` (176 lines)

| Field | Control |
|---|---|
| **Store** | Select (locked to PO's store if PO has one) |
| **Reference** | Text input |
| **Remarks** | Text input |

**Items Table:**
| Column | Content |
|---|---|
| **Product** | Product name (parent or sub) |
| **Sub Product** | Select (if PO line has variants) or read-only "Fixed by LPO" badge |
| **Ordered** | Read-only |
| **Received** | Read-only |
| **Remaining** | Read-only |
| **Receive Now** | Number input (capped at remaining) |
| **Unit Price** | Read-only (from LPO) |

**Actions:** "Receive Full Balance" button, "Clear All" button

### 10.3 Receive Store Logic

**`StockReceiveController@store` (lines 221–682):**

1. **Validation**:
   - PO must be `approved`
   - Store must match PO's store (if PO specified one)
   - No over-receiving (remaining qty check)
   - Product must match PO line exactly
   - Store type must match item type

2. **Pessimistic locking**: Re-validates remaining quantities under `lockForUpdate()`

3. **Creates records**:
   ```php
   GoodsReceipt::create([...]);
   GoodsReceiptItem::create([...]);  // per line
   ```

4. **Stock Movement IN** (for goods-type products):
   ```php
   StockMovement::create([
       'store_id' => $storeId,
       'product_id' => $productId,
       'quantity' => $qtyReceived,  // positive
       'type' => 'in',
       'reference_type' => 'goods_receipt',
       ...
   ]);
   ```

5. **Supplier Payment Request**: Creates a `SupplierPayment` (status = `pending`) for the received value:
   ```php
   SupplierPayment::create([
       'purchase_order_id' => $po->id,
       'goods_receipt_id' => $receipt->id,
       'amount' => $totalValue,
       'supplier_id' => $po->supplier_id,
       'currency' => $po->currency_code,
       'exchange_rate' => $po->exchange_rate,
       'status' => 'pending',
   ]);
   ```

6. **Journal Entry** (`GR-LIB-{receiptId}`):
   ```
   DR  Inventory (1300)                             → line total
   CR  Accounts Payable - Suppliers (2100)          → line total
   ```

---

## 11. Supplier Payment Flow

### 11.1 Routes

| Route | Method | Purpose |
|---|---|---|
| `GET /supplier-payments` | `SupplierPaymentController@index` | List all payments |
| `GET /supplier-payments/{payment}` | `SupplierPaymentController@show` | View payment |
| `GET /supplier-payments/statement` | `supplierStatement` | Supplier statement |
| `GET /supplier-payments/advance` | `advanceForm` | Advance payment form |
| `POST /supplier-payments/advance` | `storeAdvance` | Submit advance |
| `POST /supplier-payments/{payment}/approve` | `approve` | Approve payment |
| `POST /supplier-payments/{payment}/reject` | `reject` | Reject payment |
| `POST /supplier-payments/{payment}/create-expense` | `createExpense` | Convert to expense |
| `POST /supplier-payments/{payment}/process-payment` | `processPayment` | Process actual payment |
| `GET /supplier-payments/voucher/{voucher}/print` | `printVoucher` | Print payment voucher |

### 11.2 Payment States

```
pending → approved → paid
       → rejected
```

### 11.3 Index View

**File:** `resources/views/supplier-payments/index.blade.php` (253 lines)

Summary cards: Total Suppliers, Total Payments Made, Total Orders, Total Advance Payments

Payment list table with columns: Payment #, Supplier, Amount, Currency, Status (badge), Date, Actions

### 11.4 Show View

**File:** `resources/views/supplier-payments/show.blade.php` (550 lines)

**Payment Details card:**
| Field | Display |
|---|---|
| Supplier | Supplier name |
| Amount | Formatted with currency |
| Due Date | Formatted date |
| Purchase Order | Link to PO (if linked) |
| Description | Text |

**Action buttons per status:**

| Status | Available Actions |
|---|---|
| `pending` | **Approve Payment** (green), **Reject Payment** (red) |
| `approved` | **Request Payment** (opens payment modal), **Convert to Expense** |
| `paid` | **Voucher Print**, **View Expense** (if linked) |

### 11.5 Payment Approval (`SupplierPaymentController@approve`)

```php
$supplierPayment->update([
    'status' => 'approved',
    'approved_by' => Auth::id(),
    'approved_at' => now(),
]);
```

### 11.6 Process Payment (`SupplierPaymentController@processPayment`, lines 192–362)

**Pre-validation:**
1. Payment must be `approved`
2. Budget enforcement:
   - PO/GRN payments: budget already set at PO creation
   - Standalone payments: must be converted to Expense first (which requires budget line)

3. **Advance deduction**: For non-advance payments with a linked PO, advances applied against the same PO are deducted:
   ```php
   $netDue = $payment->amount - $advancesApplied;
   $maxPayable = $netDue;
   ```

4. **Balance check**: Validates payment account has sufficient funds (respects overdraft limits)

**Processing (inside DB transaction):**

1. Creates **PaymentVoucher**:
   ```php
   PaymentVoucher::create([
       'number' => $this->generateVoucherNumber(),
       'payee' => $supplier->name,
       'amount' => $paymentAmount,
       'account_id' => $account->id,
       'supplier_payment_id' => $payment->id,
       ...
   ]);
   ```

2. Updates payment status to `paid`

3. **Journal Entries** (`createPaymentJournalEntries`):

   ```
   DR  Accounts Payable - Suppliers (2100)     → amount
   CR  Cash/Bank (payment account)              → amount
   ```
   Reference: `SP-PAY-{payment_number}`

### 11.7 Advances to Suppliers (`storeAdvance`)

1. Validates: PO must be `approved`
2. Creates SupplierPayment with `is_advance = true`
3. Status set to `pending`
4. Advances reduce the net payable when processing the main PO payment

### 11.8 Convert to Expense (`createExpense`, lines 375–471)

When a supplier payment has no linked PO or GRN (standalone payment):
1. Creates an `Expense` record
2. Links the supplier payment to the expense
3. Expense must have budget line before payment processing

---

## 12. End-to-End Payment Cycle

```
1. LPO CREATION
   └─ Status: pending (or draft)
   └─ Level-0: auto-approves + creates SupplierPayment + PaymentVoucher + journal
   └─ Values stored in TZS (base currency)

2. LPO APPROVAL (if level > 0)
   └─ 1-3 step approval
   └─ Final step: creates SupplierPayment (auto-paid) + PaymentVoucher
   └─ Commits budget: project_budget_lines.committed_amount += PO total
   └─ Journal: DR AP (2100), CR Bank/Cash

3. GOODS RECEIPT
   └─ Stock IN via StockMovement
   └─ Creates SupplierPayment (pending) for received value
   └─ Journal: DR Inventory (1300), CR AP (2100)  [GR-LIB-{receiptId}]

4. SUPPLIER PAYMENT
   └─ Status: pending → approved → paid
   └─ Approval: simple single-step
   └─ Process payment:
        ├─ Creates PaymentVoucher
        └─ Journal: DR AP (2100), CR Bank/Cash  [SP-PAY-{number}]
        └─ Deducts any advances applied

5. REVERSAL
   └─ PO reversal: reverses PO-*, GRN-*, GR-LIB-* journals
   └─ Deletes pending payments
   └─ Reverses budget commitment
   └─ GR reversal: reverses stock movement + journal
```

---

## 13. Key Code Files

| File | Purpose |
|---|---|
| `app/Http/Controllers/SalesController.php` | POS-style new sale, store, proforma, drafts |
| `app/Http/Controllers/InvoiceController.php` | Invoice CRUD, returns, discounts, credit notes, draft submit |
| `app/Http/Controllers/PurchaseOrderController.php` | LPO CRUD, approval (1-3 steps), reject, reverse |
| `app/Http/Controllers/StockReceiveController.php` | Goods receipt against approved PO |
| `app/Http/Controllers/SupplierPaymentController.php` | Supplier payment lifecycle (pending→approved→paid) |
| `app/Http/Controllers/InvoicePaymentController.php` | Invoice payment receipt |
| `app/Http/Controllers/InvoiceReversalController.php` | Invoice reversal |
| `app/Support/DocumentPrefixes.php` | Document numbering (INV, PRO, PO, etc.) |
| `app/Support/Approvals.php` | Multi-level approval configuration |
| `app/Support/InventoryAccounts.php` | Inventory account resolution (1300) |
| `app/Models/Invoice.php` | Invoice model |
| `app/Models/InvoiceLine.php` | Invoice lines (product_id, sub_product_id, store_id) |
| `app/Models/PurchaseOrder.php` | Purchase Order model |
| `app/Models/PurchaseOrderItem.php` | PO items (product_id, product_make, unit_price, selling_price) |
| `app/Models/GoodsReceipt.php` | Goods receipt |
| `app/Models/GoodsReceiptItem.php` | Received items |
| `app/Models/SupplierPayment.php` | Supplier payment |
| `app/Models/PaymentVoucher.php` | Payment voucher |
| `app/Models/StockMovement.php` | Stock in/out tracking |
| `resources/views/sales/new.blade.php` | POS-style sale form (Alpine.js) |
| `resources/views/invoices/create.blade.php` | Invoice/proforma create form |
| `resources/views/purchase_orders/create.blade.php` | LPO create form |
| `resources/views/stock/receive/show.blade.php` | Goods receiving form |
| `resources/views/supplier-payments/show.blade.php` | Payment detail + actions |
