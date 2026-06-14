<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): View
    {
        $query = $request->get('search');
        $event = $request->get('event');

        if ($query) {
            $logs = $this->auditService->search($query);
        } elseif ($event) {
            $logs = $this->auditService->getByEvent($event);
        } else {
            $logs = $this->auditService->getAll();
        }

        return view('audit-logs.index', compact('logs'));
    }
}
