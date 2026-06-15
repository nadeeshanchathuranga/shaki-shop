<?php

namespace App\Http\Controllers;

use App\Models\EventCommission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventCommissionController extends Controller
{
    public function index()
    {
        return Inertia::render('EventCommissions/Index', [
            'commissions' => EventCommission::with('customer')->orderBy('event_date', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'total_amount' => 'required|numeric|min:0',
            'advance_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:pending,partially_paid,paid',
            'notes' => 'nullable|string',
        ]);

        // Sync with Customer Module
        $customer = \App\Models\Customer::updateOrCreate(
            ['phone' => $validated['customer_phone']],
            [
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'member_since' => now()->toDateString(), // Fix for missing default value
            ]
        );

        $validated['customer_id'] = $customer->id;

        $commission = EventCommission::create($validated);

        // Log the initial payment transaction for daily reports
        if ($commission->advance_amount > 0) {
            \App\Models\EventCommissionPayment::create([
                'event_commission_id' => $commission->id,
                'amount' => $commission->advance_amount,
                'payment_date' => now()->toDateString(),
                'notes' => 'Initial Advance Payment'
            ]);
        }

        return response()->json(['success' => true, 'data' => $commission]);
    }

    public function update(Request $request, EventCommission $eventCommission)
    {
        $validated = $request->validate([
            'event_title' => 'sometimes|string|max:255',
            'event_date' => 'sometimes|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'sometimes|string|max:20',
            'total_amount' => 'sometimes|numeric|min:0',
            'advance_amount' => 'sometimes|numeric|min:0',
            'payment_status' => 'sometimes|in:pending,partially_paid,paid',
            'notes' => 'nullable|string',
        ]);

        // Sync changes back to Customer module if details are provided
        if (isset($validated['customer_phone'])) {
            $customer = \App\Models\Customer::updateOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'] ?? $eventCommission->customer_name,
                    'email' => $validated['customer_email'] ?? $eventCommission->customer_email,
                    'member_since' => now()->toDateString(),
                ]
            );
            $validated['customer_id'] = $customer->id;
        }

        $oldAdvance = (float) $eventCommission->advance_amount;
        $eventCommission->update($validated);
        $newAdvance = (float) $eventCommission->advance_amount;

        // Log the incremental payment for reporting if advance was increased
        if ($newAdvance > $oldAdvance) {
            \App\Models\EventCommissionPayment::create([
                'event_commission_id' => $eventCommission->id,
                'amount' => $newAdvance - $oldAdvance,
                'payment_date' => now()->toDateString(),
                'notes' => 'Subsequent Payment / Settlement'
            ]);
        }

        return response()->json(['success' => true, 'data' => $eventCommission]);
    }

    public function destroy(EventCommission $eventCommission)
    {
        $eventCommission->delete();
        return response()->json(['success' => true]);
    }
}
