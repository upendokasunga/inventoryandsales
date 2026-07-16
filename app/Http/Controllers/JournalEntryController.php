<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Http\Requests\JournalEntry\StoreJournalEntryRequest;
use App\Services\CentralApprovalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function __construct(
        protected CentralApprovalService $centralApproval,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $query = JournalEntry::with('creator');

        if ($tab === 'adjustment') {
            $query->where('is_adjustment', true);
        } elseif ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $entries = $query->latest()->paginate(20);
        return view('journal-entries.index', compact('entries', 'tab'));
    }

    public function create(): View
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        return view('journal-entries.create', compact('accounts'));
    }

    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $lines = $data['lines'] ?? [];
        unset($data['lines']);

        $totalDebit = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Debit and credit totals must be equal.');
        }

        $data['entry_number'] = 'GENJ-' . strtoupper(\Illuminate\Support\Str::random(8));
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        $entry = DB::transaction(function () use ($data, $lines) {
            $entry = JournalEntry::create($data);
            foreach ($lines as $line) {
                $entry->lines()->create($line);
            }
            return $entry;
        });

        return redirect()->route('journal-entries.show', $entry)
            ->with('success', 'Journal entry created successfully.');
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load('lines.account', 'creator', 'approver', 'reverser', 'audits.user');
        return view('journal-entries.show', compact('journalEntry'));
    }

    public function approve(JournalEntry $journalEntry): RedirectResponse
    {
        try {
            if ($journalEntry->status === 'draft') {
                $this->centralApproval->submit($journalEntry);
            }
            $this->centralApproval->approve($journalEntry);
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('success', 'Journal entry posted successfully.');
        } catch (\InvalidArgumentException $e) {
            if ($journalEntry->fresh()->status === 'pending_approval') {
                $journalEntry->update(['status' => 'draft']);
            }
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('error', $e->getMessage());
        }
    }

    public function submit(JournalEntry $journalEntry): RedirectResponse
    {
        if ($journalEntry->status !== 'draft') {
            return back()->with('error', 'Only draft journal entries can be submitted for approval.');
        }

        try {
            $this->centralApproval->submit($journalEntry);
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('success', 'Journal entry submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('error', $e->getMessage());
        }
    }

    public function reject(JournalEntry $journalEntry): RedirectResponse
    {
        if ($journalEntry->status !== 'pending_approval') {
            return back()->with('error', 'Only pending journal entries can be rejected.');
        }

        try {
            $this->centralApproval->reject($journalEntry, 'Rejected by ' . auth()->user()?->name);
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('success', 'Journal entry rejected and returned to draft.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('error', $e->getMessage());
        }
    }

    public function reverse(JournalEntry $journalEntry): RedirectResponse
    {
        if (!in_array($journalEntry->status, ['posted', 'approved'])) {
            return back()->with('error', 'Only posted or approved journal entries can be reversed.');
        }
        $journalEntry->update([
            'status' => 'reversed',
            'reversed_by' => auth()->id(),
            'reversed_at' => now(),
        ]);
        return redirect()->route('journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry reversed successfully.');
    }

    public function print(JournalEntry $journalEntry): Response
    {
        $journalEntry->load(['lines.account', 'creator', 'approver', 'reverser']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['journalEntry'] = $journalEntry;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.journal-entry', $data, "je-{$journalEntry->entry_number}.pdf");
    }

    public function destroy(JournalEntry $journalEntry): RedirectResponse
    {
        if (in_array($journalEntry->status, ['posted', 'approved', 'reversed'])) {
            return back()->with('error', 'Cannot delete a ' . $journalEntry->status . ' journal entry.');
        }
        $journalEntry->lines()->delete();
        $journalEntry->delete();
        return redirect()->route('journal-entries.index')->with('success', 'Journal entry deleted successfully.');
    }
}
