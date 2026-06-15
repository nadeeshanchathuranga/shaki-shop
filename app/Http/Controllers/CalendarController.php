<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\EventCommission;
use App\Models\RentalBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        // 1. Fetch Custom Tasks/Operations
        $customEvents = CalendarEvent::with('user')->get()->map(function($e) {
            return [
                'id' => 'custom_' . $e->id,
                'real_id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'date' => $e->event_date,
                'type' => 'task',
                'status' => $e->status,
                'color' => $e->type === 'operational' ? 'bg-indigo-500' : 'bg-blue-500'
            ];
        });

        // 2. Fetch Event Commissions
        $commissions = EventCommission::all()->map(function($e) {
            return [
                'id' => 'comm_' . $e->id,
                'real_id' => $e->id,
                'title' => 'Event: ' . $e->event_title,
                'description' => 'Customer: ' . $e->customer_name,
                'date' => $e->event_date,
                'type' => 'commission',
                'status' => $e->payment_status,
                'color' => 'bg-orange-500'
            ];
        });

        // 3. Fetch Rental Bookings (start and end dates)
        $rentals = RentalBooking::with('rentalItem')->get()->flatMap(function($e) {
            return [
                [
                    'id' => 'rent_start_' . $e->id,
                    'real_id' => $e->id,
                    'title' => '📦 Dispatch: ' . ($e->rentalItem->item_name ?? 'Rental'),
                    'description' => 'Customer: ' . $e->customer_name,
                    'date' => $e->rental_date_from,
                    'type' => 'rental_start',
                    'status' => $e->status,
                    'color' => 'bg-purple-500'
                ],
                [
                    'id' => 'rent_end_' . $e->id,
                    'real_id' => $e->id,
                    'title' => '🔙 Return: ' . ($e->rentalItem->item_name ?? 'Rental'),
                    'description' => 'Customer: ' . $e->customer_name,
                    'date' => $e->rental_date_to,
                    'type' => 'rental_end',
                    'status' => $e->status,
                    'color' => 'bg-emerald-500'
                ]
            ];
        });

        return Inertia::render('Calendar/Index', [
            'events' => $customEvents->concat($commissions)->concat($rentals)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'type' => 'required|in:task,operational,reminder',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        CalendarEvent::create($validated);

        return back()->with('success', 'Task added to calendar.');
    }

    public function updateStatus(Request $request, $id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->update(['status' => $request->status]);
        return back();
    }

    public function destroy($id)
    {
        CalendarEvent::findOrFail($id)->delete();
        return back();
    }
}
