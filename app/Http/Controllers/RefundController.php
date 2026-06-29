<?php

namespace App\Http\Controllers;

use App\Http\Requests\Refund\StoreRefundRequest;
use App\Models\CreditNote;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(
        protected RefundService $refundService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['refund_method', 'customer_id', 'date_from', 'date_to']);
        $refunds = $this->refundService->getAllPaginated(20, $filters);
        $stats = $this->refundService->getStats();
        return view('refunds.index', compact('refunds', 'stats'));
    }

    public function process(StoreRefundRequest $request): RedirectResponse
    {
        $creditNote = CreditNote::findOrFail($request->credit_note_id);

        try {
            $this->refundService->processRefund($creditNote, $request->refund_method);
            return redirect()->route('credit-notes.show', $creditNote)
                ->with('success', 'Refund processed successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
