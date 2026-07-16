<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Row as SpoutRow;

class DataMigrationController extends Controller
{
    public function index()
    {
        return view('data-migration.index');
    }

    // ── Products ──────────────────────────────────────────────────────
    public function productsUpload()
    {
        $categories = Category::orderBy('name')->get();
        $warehouses = Warehouse::active()->where('type', 'goods')->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('data-migration.products-upload', compact('categories', 'warehouses', 'suppliers'));
    }

    public function productsPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $rows = $this->readExcelFile($request->file('file'));
        $products = [];
        $errors = [];
        $categories = Category::pluck('name', 'name')->toArray();
        $existingSku = Product::pluck('sku')->filter()->map(fn($s) => strtolower($s))->toArray();
        $existingBarcode = Product::whereNotNull('barcode')->pluck('barcode')->map(fn($b) => strtolower($b))->toArray();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 1;
            $rowErrors = [];

            $name = trim($row['name'] ?? '');
            $sku = trim($row['sku'] ?? '');
            $barcode = trim($row['barcode'] ?? '');
            $price = $row['price'] ?? null;
            $retailPrice = $row['retail_price'] ?? null;
            $costPrice = $row['cost_price'] ?? null;
            $category = trim($row['category'] ?? '');
            $unit = trim($row['unit'] ?? 'PCS');
            $stock = $row['opening_stock'] ?? null;
            $reorderLevel = $row['reorder_level'] ?? null;
            $productType = strtolower(trim($row['product_type'] ?? 'goods'));

            if (empty($name)) {
                $rowErrors[] = 'Product name is required';
            }
            if (!is_numeric($price) || $price < 0) {
                $rowErrors[] = 'Selling price must be a valid positive number';
            }
            if ($costPrice !== null && $costPrice !== '' && (!is_numeric($costPrice) || $costPrice < 0)) {
                $rowErrors[] = 'Cost price must be a valid positive number';
            }
            if (!in_array($productType, ['goods', 'service'])) {
                $rowErrors[] = 'Product type must be goods or service';
            }
            if ($sku && in_array(strtolower($sku), $existingSku)) {
                $rowErrors[] = "SKU '{$sku}' already exists";
            }
            if ($barcode && in_array(strtolower($barcode), $existingBarcode)) {
                $rowErrors[] = "Barcode '{$barcode}' already exists";
            }

            $products[] = [
                'row' => $rowNum,
                'name' => $name,
                'sku' => $sku,
                'barcode' => $barcode,
                'price' => $price,
                'retail_price' => $retailPrice,
                'cost_price' => $costPrice,
                'category' => $category,
                'unit' => $unit,
                'opening_stock' => $stock,
                'reorder_level' => $reorderLevel,
                'product_type' => $productType,
                'errors' => $rowErrors,
            ];

            if (!empty($rowErrors)) {
                $errors[$rowNum] = $rowErrors;
            }
        }

        $request->session()->put('migration_products', $products);

        $warehouses = Warehouse::active()->where('type', 'goods')->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('data-migration.products-preview', [
            'products' => $products,
            'errorCount' => count($errors),
            'totalCount' => count($products),
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
        ]);
    }

    public function productsImport(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'import_date' => 'required|date',
            'opening_stock_cost' => 'nullable|numeric|min:0',
        ]);

        $products = $request->session()->get('migration_products');
        if (empty($products)) {
            return back()->with('error', 'No data to import. Please upload a file first.');
        }

        $importErrors = [];
        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $row) {
                if (!empty($row['errors'])) {
                    continue;
                }

                $productData = [
                    'name' => $row['name'],
                    'product_type' => $row['product_type'] ?? 'goods',
                    'price' => $row['price'],
                    'retail_price' => $row['retail_price'] ?? $row['price'],
                    'standard_cost' => $row['cost_price'] ?? 0,
                    'track_stock' => true,
                    'is_active' => true,
                    'reorder_level' => $row['reorder_level'] ?? 0,
                ];

                if (!empty($row['sku'])) {
                    $productData['sku'] = $row['sku'];
                }
                if (!empty($row['barcode'])) {
                    $productData['barcode'] = $row['barcode'];
                }
                if (!empty($row['category'])) {
                    $category = Category::where('name', $row['category'])->first();
                    if ($category) {
                        $productData['category_id'] = $category->id;
                    }
                    $productData['category'] = $row['category'];
                }
                if (!empty($row['unit'])) {
                    $productData['unit'] = $row['unit'];
                }

                $existing = null;
                if (!empty($row['sku'])) {
                    $existing = Product::where('sku', $row['sku'])->first();
                }
                if (!$existing && !empty($row['barcode'])) {
                    $existing = Product::where('barcode', $row['barcode'])->first();
                }

                if ($existing) {
                    $existing->update($productData);
                    $product = $existing;

                    if (isset($row['price']) && $row['price'] > 0) {
                        $product->productUnits()->updateOrCreate(
                            ['product_id' => $product->id, 'is_default_sale' => true],
                            ['selling_price' => $row['price']]
                        );
                    }
                } else {
                    $product = Product::create($productData);

                    if (isset($row['price']) && $row['price'] > 0) {
                        $unit = Unit::firstOrCreate(
                            ['short_code' => $row['unit'] ?? 'PCS'],
                            ['name' => $row['unit'] ?? 'Pieces', 'is_base' => true]
                        );
                        $product->productUnits()->create([
                            'unit_id' => $unit->id,
                            'conversion_factor' => 1,
                            'selling_price' => $row['price'],
                            'purchase_price' => $row['cost_price'] ?? 0,
                            'is_default_sale' => true,
                            'is_default_purchase' => true,
                        ]);
                    }
                }

                $importedCount++;
            }

            DB::commit();
            $request->session()->forget('migration_products');

            return redirect()->route('data-migration.index')
                ->with('success', "Successfully imported {$importedCount} products.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ── Customers ─────────────────────────────────────────────────────
    public function customersUpload()
    {
        $groups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('data-migration.customers-upload', compact('groups'));
    }

    public function customersPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $rows = $this->readExcelFile($request->file('file'));
        $customers = [];
        $errors = [];
        $existingPhone = Customer::whereNotNull('phone')->pluck('phone')->map(fn($p) => strtolower(trim($p)))->toArray();
        $existingEmail = Customer::whereNotNull('email')->pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 1;
            $rowErrors = [];

            $name = trim($row['name'] ?? '');
            $phone = trim($row['phone'] ?? '');
            $email = trim($row['email'] ?? '');
            $address = trim($row['address'] ?? '');
            $city = trim($row['city'] ?? '');
            $region = trim($row['region'] ?? '');
            $group = trim($row['customer_group'] ?? '');
            $creditLimit = $row['credit_limit'] ?? null;
            $paymentTerms = trim($row['payment_terms'] ?? 'Cash');

            if (empty($name)) {
                $rowErrors[] = 'Customer name is required';
            }
            if (empty($phone) && empty($email)) {
                $rowErrors[] = 'At least phone or email is required';
            }
            if ($phone && in_array(strtolower($phone), $existingPhone)) {
                $rowErrors[] = "Phone '{$phone}' already exists";
            }
            if ($email && in_array(strtolower($email), $existingEmail)) {
                $rowErrors[] = "Email '{$email}' already exists";
            }
            if ($creditLimit !== null && $creditLimit !== '' && (!is_numeric($creditLimit) || $creditLimit < 0)) {
                $rowErrors[] = 'Credit limit must be a valid positive number';
            }
            $validTerms = Customer::PAYMENT_TERMS;
            if ($paymentTerms && !in_array($paymentTerms, $validTerms)) {
                $rowErrors[] = "Invalid payment terms. Valid: " . implode(', ', $validTerms);
            }

            $customers[] = [
                'row' => $rowNum,
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'region' => $region,
                'customer_group' => $group,
                'credit_limit' => $creditLimit,
                'payment_terms' => $paymentTerms,
                'errors' => $rowErrors,
            ];

            if (!empty($rowErrors)) {
                $errors[$rowNum] = $rowErrors;
            }
        }

        $request->session()->put('migration_customers', $customers);
        $groups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('data-migration.customers-preview', [
            'customers' => $customers,
            'errorCount' => count($errors),
            'totalCount' => count($customers),
            'groups' => $groups,
        ]);
    }

    public function customersImport(Request $request)
    {
        $validated = $request->validate([
            'default_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $customers = $request->session()->get('migration_customers');
        if (empty($customers)) {
            return back()->with('error', 'No data to import. Please upload a file first.');
        }

        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($customers as $row) {
                if (!empty($row['errors'])) {
                    continue;
                }

                $customerData = [
                    'name' => $row['name'],
                    'phone' => $row['phone'] ?: null,
                    'email' => $row['email'] ?: null,
                    'address' => $row['address'] ?: null,
                    'city' => $row['city'] ?: null,
                    'region' => $row['region'] ?: null,
                    'payment_terms' => $row['payment_terms'] ?: 'Cash',
                    'credit_limit' => $row['credit_limit'] ?? 0,
                    'available_credit' => $row['credit_limit'] ?? 0,
                    'is_active' => true,
                ];

                if (!empty($row['customer_group'])) {
                    $group = CustomerGroup::where('name', $row['customer_group'])->first();
                    if ($group) {
                        $customerData['customer_group_id'] = $group->id;
                    }
                } elseif (!empty($validated['default_group_id'])) {
                    $customerData['customer_group_id'] = $validated['default_group_id'];
                }

                $existing = null;
                if (!empty($row['phone'])) {
                    $existing = Customer::where('phone', $row['phone'])->first();
                }
                if (!$existing && !empty($row['email'])) {
                    $existing = Customer::where('email', $row['email'])->first();
                }

                if ($existing) {
                    $existing->update($customerData);
                } else {
                    Customer::create($customerData);
                }

                $importedCount++;
            }

            DB::commit();
            $request->session()->forget('migration_customers');

            return redirect()->route('data-migration.index')
                ->with('success', "Successfully imported {$importedCount} customers.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ── Sales ─────────────────────────────────────────────────────────
    public function salesUpload()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->where('type', 'goods')->orderBy('name')->get();

        return view('data-migration.sales-upload', compact('customers', 'warehouses'));
    }

    public function salesPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $rows = $this->readExcelFile($request->file('file'));
        $sales = [];
        $errors = [];
        $productCache = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 1;
            $rowErrors = [];

            $customerName = trim($row['customer'] ?? '');
            $productName = trim($row['product'] ?? '');
            $productSku = trim($row['sku'] ?? '');
            $quantity = $row['quantity'] ?? null;
            $unitPrice = $row['unit_price'] ?? null;
            $date = trim($row['date'] ?? '');
            $paymentType = strtolower(trim($row['payment_type'] ?? 'cash'));

            if (empty($customerName)) {
                $rowErrors[] = 'Customer name is required';
            } else {
                $customer = Customer::where('name', $customerName)->first();
                if (!$customer) {
                    $rowErrors[] = "Customer '{$customerName}' not found";
                }
            }

            if (empty($productName) && empty($productSku)) {
                $rowErrors[] = 'Product name or SKU is required';
            } else {
                $cacheKey = $productSku ?: $productName;
                if (!isset($productCache[$cacheKey])) {
                    $productCache[$cacheKey] = $productSku
                        ? Product::where('sku', $productSku)->first()
                        : Product::where('name', $productName)->first();
                }
                if (!$productCache[$cacheKey]) {
                    $rowErrors[] = "Product '" . ($productSku ?: $productName) . "' not found";
                }
            }

            if (!is_numeric($quantity) || $quantity <= 0) {
                $rowErrors[] = 'Quantity must be a positive number';
            }
            if (!is_numeric($unitPrice) || $unitPrice < 0) {
                $rowErrors[] = 'Unit price must be a valid positive number';
            }
            if (empty($date)) {
                $rowErrors[] = 'Date is required';
            } elseif (!$this->isValidDate($date)) {
                $rowErrors[] = 'Invalid date format. Use YYYY-MM-DD';
            }
            if ($paymentType && !in_array($paymentType, ['cash', 'credit', 'bank_transfer', 'mobile_money', 'cheque'])) {
                $rowErrors[] = 'Invalid payment type';
            }

            $sales[] = [
                'row' => $rowNum,
                'customer' => $customerName,
                'product' => $productName,
                'sku' => $productSku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'date' => $date,
                'payment_type' => $paymentType,
                'errors' => $rowErrors,
            ];

            if (!empty($rowErrors)) {
                $errors[$rowNum] = $rowErrors;
            }
        }

        $request->session()->put('migration_sales', $sales);

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->where('type', 'goods')->orderBy('name')->get();

        return view('data-migration.sales-preview', [
            'sales' => $sales,
            'errorCount' => count($errors),
            'totalCount' => count($sales),
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    public function salesImport(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'default_payment_type' => 'required|in:cash,credit,bank_transfer,mobile_money,cheque',
        ]);

        $sales = $request->session()->get('migration_sales');
        if (empty($sales)) {
            return back()->with('error', 'No data to import. Please upload a file first.');
        }

        $importedCount = 0;
        $customerCache = [];

        DB::beginTransaction();
        try {
            foreach ($sales as $row) {
                if (!empty($row['errors'])) {
                    continue;
                }

                $cacheKey = $row['customer'];
                if (!isset($customerCache[$cacheKey])) {
                    $customerCache[$cacheKey] = Customer::where('name', $row['customer'])->first();
                }
                $customer = $customerCache[$cacheKey];
                if (!$customer) {
                    $importErrors[] = "Row {$row['row']}: Customer not found";
                    continue;
                }

                $productCacheKey = $row['sku'] ?: $row['product'];
                $product = $row['sku']
                    ? Product::where('sku', $row['sku'])->first()
                    : Product::where('name', $row['product'])->first();
                if (!$product) {
                    $importErrors[] = "Row {$row['row']}: Product not found";
                    continue;
                }

                $unit = $product->productUnits()->first();

                $subtotal = $row['quantity'] * $row['unit_price'];

                $invoice = \App\Models\Invoice::create([
                    'invoice_number' => 'IMP-' . strtoupper(Str::random(8)),
                    'customer_id' => $customer->id,
                    'invoice_date' => $row['date'],
                    'payment_type' => $row['payment_type'] ?: $validated['default_payment_type'],
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'amount_paid' => $row['payment_type'] === 'cash' ? $subtotal : 0,
                    'balance_due' => $row['payment_type'] === 'cash' ? 0 : $subtotal,
                    'payment_status' => $row['payment_type'] === 'cash' ? 'paid' : 'pending',
                    'status' => 'posted',
                    'created_by' => auth()->id(),
                ]);

                $invoice->items()->create([
                    'product_id' => $product->id,
                    'store_id' => $validated['warehouse_id'],
                    'product_unit_id' => $unit?->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => $subtotal,
                ]);

                $importedCount++;
            }

            DB::commit();
            $request->session()->forget('migration_sales');

            return redirect()->route('data-migration.index')
                ->with('success', "Successfully imported {$importedCount} sales records.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ── Sample Download ───────────────────────────────────────────────
    public function downloadSample(string $type)
    {
        $filename = tempnam(sys_get_temp_dir(), 'sample_') . '.xlsx';

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($filename);

        $headers = match ($type) {
            'products' => ['name', 'sku', 'barcode', 'price', 'retail_price', 'cost_price', 'category', 'unit', 'opening_stock', 'reorder_level', 'product_type'],
            'customers' => ['name', 'phone', 'email', 'address', 'city', 'region', 'customer_group', 'credit_limit', 'payment_terms'],
            'sales' => ['customer', 'product', 'sku', 'quantity', 'unit_price', 'date', 'payment_type'],
        };

        $writer->addRow(SpoutRow::fromValues($headers));

        $samples = match ($type) {
            'products' => [
                ['Coca-Cola 500ml', '', '', 2500, 3000, 1800, 'Beverages', 'PCS', 100, 20, 'goods'],
                ['Samsung Galaxy S24', 'SAM-S24', '8901234567890', 2500000, 2800000, 2000000, 'Electronics', 'PCS', 10, 5, 'goods'],
                ['Consulting Service', '', '', 150000, 150000, 0, 'Services', 'HR', null, null, 'service'],
            ],
            'customers' => [
                ['John Doe', '+255712345678', 'john@example.com', '123 Main St', 'Dar es Salaam', 'Dar', 'Retail', 5000000, 'Net 30'],
                ['Jane Smith', '+255787654321', 'jane@corp.co.tz', '45 Business Ave', 'Arusha', 'Arusha', 'Wholesale', 20000000, 'Net 60'],
                ['Cash Customer', '+255700000000', '', '', '', '', '', 0, 'Cash'],
            ],
            'sales' => [
                ['John Doe', 'Coca-Cola 500ml', '', 50, 2500, '2025-01-15', 'cash'],
                ['Jane Smith', 'Samsung Galaxy S24', 'SAM-S24', 2, 2500000, '2025-01-15', 'credit'],
            ],
        };

        foreach ($samples as $row) {
            $writer->addRow(SpoutRow::fromValues($row));
        }

        $writer->close();

        return response()->download($filename, "sample_{$type}.xlsx")->deleteFileAfterSend(true);
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function readExcelFile($file): array
    {
        $rows = [];
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'csv') {
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                $headers = fgetcsv($handle);
                $headers = array_map(fn($h) => strtolower(trim(str_replace(' ', '_', $h))), $headers);

                while (($data = fgetcsv($handle)) !== false) {
                    $row = array_combine($headers, $data);
                    $rows[] = $row;
                }
                fclose($handle);
            }
        } else {
            $reader = new Reader();
            $reader->open($file->getPathname());

            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = null;
                foreach ($sheet->getRowIterator() as $spoutRow) {
                    $values = array_map(fn($v) => trim((string) $v), $spoutRow->toArray());

                    if ($headers === null) {
                        $headers = array_map(fn($h) => strtolower(trim(str_replace(' ', '_', $h))), $values);
                        continue;
                    }

                    $row = array_combine($headers, $values);
                    $rows[] = $row;
                }
                break;
            }

            $reader->close();
        }

        return $rows;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
