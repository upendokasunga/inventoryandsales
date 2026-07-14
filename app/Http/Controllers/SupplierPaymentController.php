<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentVoucher;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Support\DocumentPrefixes;
use App\Support\InventoryAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupplierPayment::with(['supplier', 'purchaseOrder', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $payments = $query->latest()->paginate(20);
        $stats = [
            'total' => SupplierPayment::count(),
            'pending' => SupplierPayment::where('status', 'pending')->count(),
            'approved' => SupplierPayment::where('status', 'approved')->count(),
            'paid' => SupplierPayment::where('status', 'paid')->count(),
        ];

        return view('supplier-payments.index', compact('payments', 'stats'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received', 'completed'])
            ->whereHas('supplier')
            ->with('supplier')
            ->get();

        return view('supplier-payments.create', compact('suppliers', 'purchaseOrders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['purchase_order_id'])) {
            $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
            $remaining = (float) ($po->total_amount ?: $po->total) - (float) $po->amount_paid;
            if ($validated['amount'] > $remaining) {
                return back()->withErrors(['amount' => "Payment amount exceeds remaining balance of {$remaining}."])->withInput();
            }
        }

        $payment = SupplierPayment::create([
            'purchase_order_id' => $validated['purchase_order_id'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'amount' => $validated['amount'],
            'status' => 'approved',
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('supplier-payments.show', $payment)
            ->with('success', 'Supplier payment created. Process payment to complete.');
    }

    public function show(SupplierPayment $supplierPayment): View
    {
        $supplierPayment->loadMissing(['supplier', 'purchaseOrder', 'goodsReceipt', 'creator']);

        $paymentHistory = [];
        if ($supplierPayment->purchaseOrder) {
            $paymentHistory = SupplierPayment::where('purchase_order_id', $supplierPayment->purchase_order_id)
                ->with('creator')
                ->latest()
                ->get();
        }

        return view('supplier-payments.show', compact('supplierPayment', 'paymentHistory'));
    }

    public function approve(SupplierPayment $supplierPayment): RedirectResponse
    {
        if ($supplierPayment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be approved.');
        }

        $supplierPayment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('supplier-payments.show', $supplierPayment)
            ->with('success', 'Payment approved.');
    }

    public function reject(SupplierPayment $supplierPayment): RedirectResponse
    {
        if ($supplierPayment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be rejected.');
        }

        $supplierPayment->update(['status' => 'rejected']);

        return redirect()->route('supplier-payments.show', $supplierPayment)
            ->with('success', 'Payment rejected.');
    }

    public function processPayment(Request $request, SupplierPayment $supplierPayment): RedirectResponse
    {
        if ($supplierPayment->status !== 'approved') {
            return back()->with('error', 'Only approved payments can be processed.');
        }

        $request->validate([
            'payment_account_id' => 'required|exists:accounts,id',
            'payment_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $supplierPayment) {
            $account = Account::findOrFail($request->payment_account_id);
            $amount = $supplierPayment->amount;

            $voucherNumber = DocumentPrefixes::formatWithYear('payment_voucher', PaymentVoucher::max('id') + 1);
            PaymentVoucher::create([
                'number' => $voucherNumber,
                'payee' => $supplierPayment->supplier?->name ?? 'Supplier',
                'amount' => $amount,
                'account_id' => $account->id,
                'supplier_payment_id' => $supplierPayment->id,
                'status' => 'paid',
                'description' => "Payment for PO #{$supplierPayment->purchaseOrder?->po_number}",
                'created_by' => auth()->id(),
            ]);

            $supplierPayment->update([
                'status' => 'paid',
                'payment_method' => $account->name,
                'payment_date' => $request->payment_date ?? now(),
            ]);

            if ($supplierPayment->purchaseOrder) {
                $po = $supplierPayment->purchaseOrder;
                $poTotal = (float) ($po->total_amount ?: $po->total);
                $newAmountPaid = (float) $po->amount_paid + $amount;
                $newBalance = $poTotal - $newAmountPaid;

                $po->update([
                    'amount_paid' => $newAmountPaid,
                    'balance_due' => max(0, $newBalance),
                ]);
            }

            $je = JournalEntry::create([
                'entry_date' => $request->payment_date ?? now(),
                'type' => 'payment',
                'status' => 'posted',
                'description' => "Supplier payment #{$supplierPayment->id}",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'reference_type' => SupplierPayment::class,
                'reference_id' => $supplierPayment->id,
                'created_by' => auth()->id(),
            ]);

            $apAccount = InventoryAccounts::accountsPayable();
            if ($apAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $apAccount->id,
                    'description' => 'Accounts Payable',
                    'debit' => $amount,
                    'credit' => 0,
                ]);
            }

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $account->id,
                'description' => 'Payment',
                'debit' => 0,
                'credit' => $amount,
            ]);
        });

        return redirect()->route('supplier-payments.show', $supplierPayment)
            ->with('success', 'Payment processed successfully.');
    }
}
