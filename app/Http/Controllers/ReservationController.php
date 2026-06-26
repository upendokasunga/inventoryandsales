<?php

namespace App\Http\Controllers;

use App\Models\StockReservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
    ) {}

    public function index(Request $request)
    {
        $query = StockReservation::with(['salesOrder.customer', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(20);

        return view('sales.reservations.index', compact('reservations'));
    }

    public function show(StockReservation $stockReservation)
    {
        $stockReservation->load(['salesOrder.customer', 'items.product', 'creator']);

        return view('sales.reservations.show', compact('stockReservation'));
    }

    public function release(StockReservation $stockReservation)
    {
        try {
            $this->reservationService->release($stockReservation);
            return redirect()->route('sales.reservations.show', $stockReservation)
                ->with('success', 'Reservation released.');
        } catch (\Exception $e) {
            return redirect()->route('sales.reservations.show', $stockReservation)
                ->with('error', $e->getMessage());
        }
    }
}
