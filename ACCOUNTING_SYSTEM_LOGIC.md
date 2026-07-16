# ERP Accounting System Logic

## Table of Contents
1. [Account Creation Logic](#1-account-creation-logic)
2. [Journal Entries System](#2-journal-entries-system)
3. [Bank Accounts Creation & Operations](#3-bank-accounts-creation--operations)

---

## 1. Account Creation Logic

### 1.1 Database Schema — `accounts` Table

The `accounts` table is the core of the Chart of Accounts (CoA), built through a base migration plus 12+ incremental migrations.

**Key Columns:**

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `code` | VARCHAR UNIQUE | Auto-generated numeric string (e.g., `1000`, `1001`) |
| `account_number` | VARCHAR(100) NULLABLE | User-provided external number (e.g., bank acct no.) |
| `name` | VARCHAR | Human-readable account name |
| `type` | ENUM | `asset`, `liability`, `equity`, `income`, `expense`, `contra-asset`, `contra-liability`, `contra-equity` |
| `ifrs_category` | VARCHAR NULLABLE | IFRS classification: `cash`, `bank`, `inventory`, `ar`, `ap`, etc. |
| `category` | VARCHAR NULLABLE | Alternate column for `ifrs_category` (legacy compat) |
| `current_noncurrent` | ENUM NULLABLE | `current` or `non_current` |
| `presentation_order` | INT NULLABLE | Display ordering |
| `function_of_expense` | ENUM NULLABLE | `cogs`, `selling`, `admin` |
| `parent_id` | FK → accounts NULLABLE | Parent account for tree hierarchy |
| `is_active` | BOOLEAN | Default: true |
| `reportable` | BOOLEAN | Default: true — included in reports |
| `current_balance` | DECIMAL(15,2) | Denormalized cached balance |
| `user_id` | FK → users NULLABLE | Assigns cash account to a user |
| `cost_center_id` | FK → cost_centers NULLABLE | Cost center association |
| `branch_id` | BIGINT NULLABLE | NULL = global, integer = branch-scoped |
| `currency_code` | VARCHAR(10) NULLABLE | Multi-currency support |
| `allow_overdraft` | BOOLEAN | Default: false |
| `overdraft_limit` | DECIMAL(18,2) | Default: 0 |
| `bank_name` | VARCHAR NULLABLE | Bank institution name |
| `bank_swift_code` | VARCHAR(50) NULLABLE | SWIFT/BIC code |
| `bank_branch` | VARCHAR(100) NULLABLE | Bank branch name |
| `include_in_income_statement` | BOOLEAN | Default: false |

**Account Number Uniqueness:** Scoped per `(branch_id, account_number)` — a global account (`branch_id = NULL`) and a branch-specific account can share the same `account_number`.

### 1.2 Account Types Table (`account_types`)

A configuration/lookup table used by the "Open Account" form to determine which kinds of accounts users can create.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `key` | VARCHAR UNIQUE | Machine key, e.g., `asset_cash`, `asset_bank` |
| `label` | VARCHAR | Human label, e.g., "Cash Account", "Bank Account" |
| `base_type` | ENUM | `asset`, `liability`, `equity`, `income`, `expense` |
| `asset_class` | ENUM NULLABLE | `current` or `non_current` |
| `is_active` | BOOLEAN | Default: true |
| `display_order` | UNSIGNED INT | Default: 0 |

**Default Seeded Types:**

*Assets:*
- `asset_cash` — Cash Account (current)
- `asset_bank` — Bank Account (current)
- Accounts Receivable, Inventory, Prepaid Expenses, Marketable Securities, Short-term Investments, Other Current Assets
- PPE, Intangible Assets, Long-term Investments, Deferred Tax Assets, Other Long-term Assets (non-current)

*Liabilities:*
- Accounts Payable, Accrued Expenses, Short-term Loans, Current Portion of Long-term Debt, Taxes Payable, Unearned Revenue (current)
- Long-term Loans, Bonds Payable, Lease Obligations, Pension Liabilities, Deferred Tax Liabilities, Other Long-term Liabilities (non-current)

### 1.3 Account Creation Flows

There are **four distinct code paths** for creating accounts:

#### Flow A: "Open Account" Form (Simplified — Cash/Bank Only)
- **Controller:** `AccountController::open()` + `AccountController::openStore()`
- **View:** `resources/views/accounts/open.blade.php`
- **Routes:** `GET /accounts/open` + `POST /accounts/open`

**Logic:**
1. User selects an `account_type_key` (e.g., `asset_cash` or `asset_bank`).
2. System auto-generates a numeric `code` by finding `MAX(CAST(code AS UNSIGNED)) + 1`, starting from 1000.
3. For cash accounts: if no `account_number` provided, auto-generates one as `CASH-{branch_id|G}-{seq}` (e.g., `CASH-1-001` or `CASH-G-001`).
4. Bank accounts require `account_number`, `bank_name`, `bank_branch`, and `bank_swift_code`.
5. Bank accounts also upsert into the `banks` table.
6. Account is created with `type = 'asset'` and `ifrs_category` set to `'cash'` or `'bank'`.

#### Flow B: Generic "Create Account" Form
- **Controller:** `AccountController::store()`
- **View:** `resources/views/accounts/create.blade.php`

Same bank field validation. Also **upserts into the `banks` table** when `bank_name` + `bank_branch` are provided.

#### Flow C: Route-Based Inline Creation
- **Location:** `routes/web.php` (lines ~2624-2718)

Uses `AccountType` model to determine type, validates bank fields when the type is `asset_bank`, and upserts into the `banks` table.

#### Flow D: Seeded Chart of Accounts
- **File:** `database/seeders/ChartOfAccountsSeeder.php`

Pre-seeds base accounts:
- Code `1000` = "Cash" (asset)
- Code `1100` = "Bank" (asset)

These root codes are **excluded** from user-facing payment account selection (they are control accounts, not operational accounts).

### 1.4 Account Code Generation

Auto-generated by finding the maximum existing numeric code and incrementing:

```
MAX(CAST(code AS UNSIGNED)) + 1
```

Starting point: `1000`. This ensures every account has a unique GL code.

### 1.5 Parent-Child Tree

Accounts support a hierarchical tree structure via the `parent_id` foreign key. This allows grouping of accounts (e.g., all bank accounts under a "Bank" parent, all expense accounts under "Operating Expenses").

---

## 2. Journal Entries System

### 2.1 Database Schema

#### `journal_entries` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `reference` | string(100) UNIQUE | e.g., `INV-123`, `RCPT-456`, `DEPR-1-2026-01`, `JE-20260115-123456-abcd` |
| `date` | date | Posting date |
| `description` | string(500) NULLABLE | Human-readable description |
| `posted_by` | FK → users | User who posted |
| `type` | string(20) | `GENJ` (General), `BNKJ` (Bank), `ADJ` (Adjustment) |
| `is_adjustment` | boolean | Default: false |
| `status` | string(20) | Default: `posted`. Values: `draft`, `submitted`, `approved`, `posted`, `reversed` |
| `approved_by` | bigint NULLABLE | FK → users |
| `approved_at` | timestamp NULLABLE | |
| `submitted_by` | bigint NULLABLE | FK → users |
| `tags` | json NULLABLE | e.g., `["sales"]`, `["adjustment"]` |
| `payload` | json NULLABLE | Freeform metadata |
| `project_id` | FK NULLABLE | Links to projects |
| `branch_id` | FK NULLABLE | Links to branches |

#### `journal_lines` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `journal_entry_id` | FK → journal_entries | Cascade on delete |
| `account_id` | FK → accounts | Restrict on delete |
| `description` | string(255) NULLABLE | Line-level description |
| `debit` | decimal(15,2) | Default: 0 |
| `credit` | decimal(15,2) | Default: 0 |
| `balance_sheet_item_id` | FK NULLABLE | Links to balance sheet items |
| `project_id` | FK NULLABLE | Links to projects |
| `branch_id` | FK NULLABLE | Links to branches |

#### `journal_entry_audits` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `journal_entry_id` | bigint | Indexed |
| `action` | string(50) | `created`, `submitted`, `approved`, `posted`, `updated` |
| `performed_by` | bigint NULLABLE | FK → users |
| `metadata` | text | JSON-encoded audit data |

### 2.2 Double-Entry Bookkeeping Enforcement

The system enforces double-entry accounting through **three layers**:

#### Layer 1: Input Validation (Controller + Helper)

In `JournalEntryController::store()` and `AccountingHelper::postToGL()`:

1. **Minimum 2 lines** required (`'lines' => ['required','array','min:2']`).
2. **Each line must have an `account_id`** that exists in the `accounts` table.
3. **A line cannot have both debit and credit** set:
   ```php
   if ($debit > 0 && $credit > 0) {
       return back()->withErrors(['lines' => 'A line cannot have both debit and credit.']);
   }
   ```
4. **Total debits must equal total credits** (balanced entry):
   ```php
   if (!$hasAmount || round($sumDebit, 2) !== round($sumCredit, 2)) {
       return back()->withErrors(['lines' => 'Entry must be balanced (total debit equals total credit).']);
   }
   ```

#### Layer 2: Observer-Based Balance Check (`JournalLineObserver`)

Runs on every `JournalLine` create/update/delete:

- **On `creating`:** If a line has `credit > 0` on a cash/bank account, checks that the account has sufficient balance:
  ```php
  $bal = AccountingHelper::accountBalanceAsOf($accountId, now());
  $allowedMin = AccountingHelper::allowedMinimumBalance($accountId);
  if (round($bal - $credit, 2) < $allowedMin - 0.0001) {
      throw new RuntimeException('Insufficient balance on account...');
  }
  ```
- Can be bypassed with `JournalLineObserver::bypassBalanceCheck(true)` (used in backfill/migration commands).

#### Layer 3: Pre-Posting Balance Check (`AccountingHelper::postToGL`)

Before creating journal lines, `postToGL()` iterates all credit lines targeting cash/bank accounts and validates the balance would not go below the allowed minimum:

```php
foreach ($credits as $aid => $amtCredit) {
    $bal = self::accountBalanceAsOf($aid, $asOf);
    $allowedMin = self::allowedMinimumBalance($aid);
    if (round($bal - $amtCredit, 2) < $allowedMin - 0.0001) {
        throw ValidationException::withMessages([...]);
    }
}
```

The `allowedMinimumBalance()` method respects the account's `allow_overdraft` and `overdraft_limit` settings.

### 2.3 Two Paths for Creating Journal Entries

#### Path A: Manual Entry via Controller (`JournalEntryController::store()`)

Used from the web UI:

1. User fills in reference, date, description, type, and lines.
2. **Adjustment journals** (`is_adjustment=true` or `type=ADJ`):
   - Restricted to `SeniorAccountant` or `FinanceManager` roles.
   - Created with `status = 'submitted'`.
   - Goes through: `submitted` → `approved` → `posted`.
3. **Non-adjustment journals** (GENJ/BNKJ):
   - Created directly with `status = 'posted'`.
   - Account balances are updated immediately.
4. An audit record is created.
5. If posted immediately, `accounts.current_balance` and `balance_sheet_items.current_balance` are recalculated.

#### Path B: Programmatic via Helper (`AccountingHelper::postToGL()`)

Used by all other controllers (InvoiceController, SalesController, etc.):

1. Validates input (same rules as Path A).
2. Runs cash/bank balance sufficiency checks.
3. Creates `JournalEntry` with `status = 'posted'`.
4. Creates all `JournalLine` records.
5. The `JournalLineObserver` fires on each line creation, updating `accounts.current_balance` incrementally.
6. Reference auto-generates as `JE-{Ymd-His}-{4-char-uniqid}` if not provided.
7. Tags the entry with the `module` name (e.g., `["sales"]`, `["purchases"]`).

### 2.4 Approval / Posting Workflow

The workflow is specifically for **adjustment journals** (`type=ADJ`):

```
submitted ──(FinanceManager)──▶ approved ──(FinanceManager)──▶ posted
```

**`approve()` action:**
- Requires `FinanceManager` role.
- Only works on adjustment journals with status `submitted`.
- Sets `status = 'approved'`, `approved_by`, `approved_at`.
- Creates audit record with action `approved`.
- Does **NOT** update account balances yet.

**`post()` action:**
- For adjustment journals: requires `FinanceManager` role, status must be `approved` or `submitted`.
- For non-adjustment: can be posted by anyone.
- Sets `status = 'posted'`.
- Recalculates `accounts.current_balance` for all affected accounts.
- Recalculates `balance_sheet_items.current_balance` from linked accounts.
- Creates audit record with action `posted`.

**Non-adjustment journals skip the approval workflow** — they go directly to `posted` on creation.

### 2.5 Account Balance Maintenance

Two mechanisms keep balances in sync:

#### Real-Time (Observer)

`JournalLineObserver` updates `accounts.current_balance` incrementally:

| Event | Behavior |
|-------|----------|
| **On create** | Adds `debit - credit` to account balance |
| **On update** | Calculates the diff between old and new deltas, applies only the difference |
| **On delete** | Subtracts the delta from account balance |

Only operates on entries with `status = 'posted'`. Also refreshes linked `balance_sheet_items.current_balance`.

#### Batch Recalculation (Controller/Post)

Both `store()` and `post()` run a full recalculation for affected accounts:

```sql
SELECT jl.account_id, ROUND(COALESCE(SUM(jl.debit - jl.credit), 0), 2) as bal
FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
WHERE jl.account_id IN (...)
  AND je.status = 'posted'
GROUP BY jl.account_id
```

#### Balance Calculation for Reports

`AccountingHelper::accountBalanceAsOf()` computes balance as of a specific date:

- Joins `journal_lines` with `journal_entries`.
- Filters to entries where `status = 'posted'` OR `is_posted = true` OR `posted_by IS NOT NULL`.
- Excludes entries with `status = 'reversed'`.
- Returns `SUM(debit) - SUM(credit)` for the account.

### 2.6 How Journal Entries Relate to Accounts

Each `JournalLine` points to exactly one `Account` (via `account_id`). The `Account` model has a `current_balance` column kept in sync by the observer.

```
JournalEntry (1) ──hasMany──▶ JournalLine (N) ──belongsTo──▶ Account
```

Additional relationships:
- `journal_lines.balance_sheet_item_id` → links to `BalanceSheetItems`
- `journal_entries.project_id` and `journal_lines.project_id` → links to projects
- `journal_entries.branch_id` → links to branches

### 2.7 Representative Journal Entry Examples

| Transaction | Debit | Credit |
|-------------|-------|--------|
| **Invoice** | Accounts Receivable ($Total) | Revenue ($Subtotal) + VAT Payable ($Tax) |
| **Receipt** | Cash/Bank ($Paid) | Accounts Receivable ($Paid) |
| **COGS** | Cost of Goods Sold ($COGS) | Inventory ($COGS) |
| **Depreciation** | Depreciation Expense ($X) | Accumulated Depreciation ($X) |
| **Money Transfer** | To Account ($Amount) | From Account ($Amount) |
| **Capital Injection** | Cash/Bank ($Amount) | Equity Capital ($Amount) |
| **Capital Withdrawal** | Equity Capital ($Amount) | Cash/Bank ($Amount) |

---

## 3. Bank Accounts Creation & Operations

### 3.1 Two Separate Concepts

The system has **two separate but related** database entities:

#### A. `banks` Table — Directory of Physical Bank Institutions

- **Model:** `app/Models/Bank.php`
- **Migration:** `database/migrations/2025_10_12_000000_create_banks_table.php`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `name` | varchar | Bank institution name |
| `branch` | varchar | Branch name |
| `swift_code` | varchar | SWIFT/BIC code |
| `timestamps` | | created_at, updated_at |

**Unique constraint:** `(name, branch, swift_code)`.

**Purpose:** A reference/directory table. Not tied to any account directly.

#### B. `accounts` Table — Where Bank Accounts Actually Live

A bank account in this system is an `Account` row with:
- `type = 'asset'`
- `ifrs_category = 'bank'`
- Non-null `bank_name`, `bank_branch`, `bank_swift_code`
- A unique `account_number`

#### C. Additional Related Tables

| Table | Purpose |
|-------|---------|
| `bank_reconciliations` | Tracks reconciliation sessions per bank account |
| `bank_reconciliation_items` | Individual reconciliation adjustments |
| `money_transfers` | Inter-account transfers (cash/bank → cash/bank) |
| `cash_transfers` | Cash transfers with FX/cross-currency support |

### 3.2 Bank Account Creation Paths

#### Path A: "Open Account" Form (Primary)
- **Controller:** `AccountController::openStore()`
- **View:** `resources/views/accounts/open.blade.php`
- **Route:** `POST /accounts/open`

**Logic:**
1. Determines `branch_id` from the form, session, or falls back to "Head Office".
2. Normalizes `account_number` (strips non-alphanumerics, uppercases).
3. Reads `account_type_key` — either `asset_cash` or `asset_bank`.
4. **For bank accounts (`asset_bank`):**
   - `bank_name`, `bank_branch`, `bank_swift_code` are **required**.
   - A unique `account_number` is **required**.
   - Uniqueness is scoped to the branch.
5. **For cash accounts (`asset_cash`):**
   - If no account number provided, auto-generates `CASH-{branchPart}-{seq}`.
   - Optionally assigns a `cash_user_id`.
6. Auto-generates the GL `code` (starting from `1000`, incrementing).
7. Sets `ifrs_category` to `'bank'` or `'cash'`.
8. Calls `Account::create($payload)`.
9. Handles unique constraint violations gracefully with retry for cash accounts.

#### Path B: Generic "Create Account" Form
- **Controller:** `AccountController::store()`
- Same bank field validation. Also **upserts into the `banks` table** when `bank_name` + `bank_branch` are provided.

#### Path C: Route-Based Inline Creation
- Uses `AccountType` model to determine type, validates bank fields, and upserts into the `banks` table.

**Key creation rule:** When a bank account is created with `bank_name` and `bank_branch`, the system also creates (or finds) a matching row in the `banks` reference table via `Bank::firstOrCreate()`.

### 3.3 Bank Account Classification Detection

A multi-layer heuristic is used throughout the codebase (`AccountingHelper::isCashOrBankAccount()`, `PostingAccess`, `InventoryAccounts`, and inline in controllers):

1. Check `ifrs_category` column: `'cash'` or `'bank'`
2. Fallback to `category` column: `'cash'` or `'bank'`
3. Fallback to `name` heuristic: contains `'cash'`, `'bank'`, `'petty'`, `'till'`
4. Fallback to bank metadata: `bank_name` or `bank_branch` is non-null

### 3.4 Bank Operations

#### A. Money Transfers (Inter-Account)
- **Controller:** `app/Http/Controllers/Accounts/MoneyTransferController.php`

**Workflow:**
1. **Create** (`store()`): Validates both accounts are cash/bank asset accounts, creates `MoneyTransfer` with `status = 'pending'`.
2. **Approve** (`doApprove()`): Calls `postAccounting()` which creates a journal entry:
   - Debit: `to_account_id` (receiving account increases)
   - Credit: `from_account_id` (sending account decreases)
   - Reference: `MT-{transfer_id}`
3. **Reject** (`doReject()`): Sets `status = 'rejected'`.
4. **Reverse** (`reverse()`): Moves approved back to `pending`.
5. **Branch transfers** (`branchStore()`): Same as above but validates that accounts belong to the correct branches.

> **Note:** GL posting for money transfers may be disabled in some configurations — transfers are tracked in the `money_transfers` table with balance sheet adjustments applied externally.

#### B. Cash Transfers (With FX Support)
- **Controller:** `app/Http/Controllers/Accounts/CashTransferController.php`

**Workflow:**
1. **Create** (`store()`): Validates source/destination are Asset accounts with `ifrs_category` of `cash` or `bank`. Checks available balance. Respects `allow_overdraft`. Handles cross-currency via `exchange_rate`.
2. **Approve** (`approve()`): Creates journal entry `CT-YYYYMMDD-###`:
   - Debit: destination account (receiving amount)
   - Credit: source account (sending amount)
   - If cross-currency and amounts differ: creates additional FX gain/loss line to an Exchange Gain/Loss income/expense account.
3. **Reject** (`reject()`): Sets status to `rejected`.

#### C. Bank Reconciliation
- **Controller:** `app/Http/Controllers/BankReconciliationController.php`

**Workflow:**
1. **Create**: User selects a bank account, enters statement end date and ending balance, adds reconciliation items.
2. **Item types:** `outstanding_cheque`, `deposit_in_transit`, `bank_charge`, `interest`, `other_adjustment`, `receipts_not_cleared`, `payments_not_cleared`, `erp_adjustment`.
3. **Balance calculation:**

   ```
   adjustedBank = statement_ending_balance
                 + deposits_in_transit
                 - outstanding_cheques
                 + interest
                 - bank_charges
                 + other_adjustment

   adjustedBook = book_balance
                 + receipts_not_cleared
                 - payments_not_cleared
                 + erp_adjustments

   difference = adjustedBank - adjustedBook
   ```

4. **Book balance** is computed as `SUM(debit - credit)` from `journal_lines` for the account up to the statement end date.
5. **Submit**: Difference must be zero. Status moves from `draft` to `pending_approval`.
6. **Approve**: Difference must be zero. Status moves to `approved`.

#### D. Payments (Invoice Receipts Involving Bank Accounts)
- **Controller:** `app/Http/Controllers/InvoicePaymentController.php`

When a customer pays an invoice, the journal entry debits a cash/bank account and credits Accounts Receivable.

**Balance check:** Before posting, `AccountingHelper::postToGL()` checks each cash/bank credit line against the account's current balance, respecting `allow_overdraft` and `overdraft_limit`.

#### E. Capital Injection (Deposit)
- **Controller:** `AccountController::capitalStore()` and `capitalStorePost()`

Creates journal entry:
- Debit: receiving asset account (cash/bank) — money enters
- Credit: equity capital account — capital increases
- Reference: `CAP-YYYYMMDD-###`

#### F. Capital Withdrawal
- **Controller:** `AccountController::withdrawalStore()`

Validates that the equity account has remaining capital (credit > debit) and sufficient cash in the paying account.

Creates journal entry:
- Credit: paying asset account (cash/bank) — money leaves
- Debit: equity capital account — capital reduces
- Reference: `CAPW-YYYYMMDD-###`

### 3.5 Access Control

- **File:** `app/Support/PostingAccess.php`

| Method | Description |
|--------|-------------|
| `PostingAccess::groupAllowsBank(User)` | Checks if the user's group has the `allow_post_bank` special permission |
| `PostingAccess::canPostTo(User, Account)` | Cash accounts are restricted to the assigned `user_id`. Bank accounts require `allow_post_bank` group permission |

### 3.6 Balance Computation

All balances are computed from the general ledger (`journal_lines` + `journal_entries`):

```sql
balance = SUM(journal_lines.debit - journal_lines.credit)
WHERE journal_lines.account_id = {account_id}
  AND journal_entries.date <= {as_of_date}
  AND journal_entries.status = 'posted'
  AND journal_entries.status != 'reversed'
```

Implemented in:

| Location | Purpose |
|----------|---------|
| `AccountController::balances()` | Balances list |
| `AccountController::bankStatement()` / `statement()` | Account statements |
| `AccountController::balanceJson()` | AJAX balance endpoint |
| `BankReconciliationController::bookBalance()` | Reconciliation book balance |
| `AccountingHelper::accountBalanceAsOf()` | Shared helper used throughout |

---

## File Reference

### Models
| File | Purpose |
|------|---------|
| `app/Models/Account.php` | Chart of Accounts model |
| `app/Models/AccountType.php` | Account type configuration |
| `app/Models/Bank.php` | Bank institution directory |
| `app/Models/JournalEntry.php` | Journal entry model |
| `app/Models/JournalLine.php` | Journal line model (debit/credit per account) |
| `app/Models/JournalEntryAudit.php` | Audit trail model |
| `app/Models/MoneyTransfer.php` | Inter-account transfer model |
| `app/Models/CashTransfer.php` | Cash transfer with FX model |
| `app/Models/BankReconciliation.php` | Bank reconciliation model |
| `app/Models/BankReconciliationItem.php` | Reconciliation line items |

### Controllers
| File | Purpose |
|------|---------|
| `app/Http/Controllers/AccountController.php` | Account CRUD, open, capital, withdrawal |
| `app/Http/Controllers/BanksController.php` | Bank institution management |
| `app/Http/Controllers/JournalEntryController.php` | Manual journal entries, approval workflow |
| `app/Http/Controllers/BankReconciliationController.php` | Bank reconciliation |
| `app/Http/Controllers/Accounts/MoneyTransferController.php` | Inter-account transfers |
| `app/Http/Controllers/Accounts/CashTransferController.php` | Cash transfers with FX |

### Helpers & Support
| File | Purpose |
|------|---------|
| `app/Helpers/AccountingHelper.php` | Core GL posting helper (`postToGL`, balance checks) |
| `app/Support/PostingAccess.php` | Access control for posting to accounts |
| `app/Observers/JournalLineObserver.php` | Real-time balance updates + overdraft protection |
| `app/Services/DepreciationPostingService.php` | Depreciation journal posting |
| `app/Services/AccountSetupCloner.php` | Account setup cloning |

### Key Migrations
| File | Purpose |
|------|---------|
| `database/migrations/2025_08_21_000100_create_accounts_table.php` | Base accounts table |
| `database/migrations/2025_08_21_000110_create_journal_entries_table.php` | Base journal entries |
| `database/migrations/2025_08_21_000120_create_journal_lines_table.php` | Base journal lines |
| `database/migrations/2025_08_25_115100_add_bank_fields_to_accounts_table.php` | Bank fields on accounts |
| `database/migrations/2025_09_06_001000_add_bank_and_meta_columns_to_accounts_table.php` | account_number, overdraft, user_id |
| `database/migrations/2025_09_25_193500_add_ifrs_fields_to_accounts_table.php` | IFRS classification |
| `database/migrations/2025_10_11_000000_create_account_types_table.php` | Account types lookup |
| `database/migrations/2025_10_11_180000_add_adjustment_fields_to_journal_entries.php` | Adjustment/approval fields |
| `database/migrations/2025_10_11_181000_create_journal_entry_audits_table.php` | Audit trail |
| `database/migrations/2025_10_12_000000_create_banks_table.php` | Bank directory |
| `database/migrations/2025_10_14_140000_create_bank_reconciliations_table.php` | Reconciliation sessions |
| `database/migrations/2025_10_14_140100_create_bank_reconciliation_items_table.php` | Reconciliation items |
| `database/migrations/2025_10_15_113900_create_money_transfers_table.php` | Money transfers |
| `database/migrations/2025_09_08_152915_create_cash_transfers_table.php` | Cash transfers |

### Seeders
| File | Purpose |
|------|---------|
| `database/seeders/ChartOfAccountsSeeder.php` | Seeds base chart of accounts |
| `database/seeders/PaymentAccountsSeeder.php` | Seeds payment accounts |

### Views
| File | Purpose |
|------|---------|
| `resources/views/accounts/open.blade.php` | Simplified account opening form |
| `resources/views/accounts/create.blade.php` | Generic account creation form |
