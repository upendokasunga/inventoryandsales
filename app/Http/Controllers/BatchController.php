<?php

namespace App\Http\Controllers;

use App\Services\BatchService;
use App\Services\ExpiryMonitoringService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct(
        protected BatchService $batchService,
        protected ExpiryMonitoringService $expiryService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'product_id', 'expiring_before']);
        $batches = $this->batchService->getAllPaginated(20, $filters);
        $expiryData = $this->expiryService->getExpiringBatches(30);

        return view('inventory.batches', compact('batches', 'filters', 'expiryData'));
    }
}
