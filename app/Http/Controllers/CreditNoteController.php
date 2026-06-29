<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Services\CreditNoteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function __construct(
        protected CreditNoteService $creditNoteService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $creditNotes = $this->creditNoteService->getAllPaginated(20, $filters);
        $stats = $this->creditNoteService->getStats();
        return view('credit-notes.index', compact('creditNotes', 'stats'));
    }

    public function show(CreditNote $creditNote): View
    {
        $creditNote->load(['customer', 'salesReturn', 'invoice', 'creator']);
        return view('credit-notes.show', compact('creditNote'));
    }
}
