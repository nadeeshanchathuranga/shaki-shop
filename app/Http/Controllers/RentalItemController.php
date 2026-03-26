<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\RentalItem;
use App\Models\RentalBooking;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RentalItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rentalItems = RentalItem::with('category', 'color', 'supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        $categories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string;
            return $category;
        });

        $colors = Color::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        return Inertia::render('RentalItems/Index', [
            'rentalItems' => $rentalItems,
            'categories' => $categories,
            'colors' => $colors,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:255',
            'color_id' => 'nullable|exists:colors,id',
            'size' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'rental_quantity' => 'required|integer|min:1',
            'rent_price' => 'required|numeric|min:0',
            'commission_percentage_shop' => 'required|numeric|min:0|max:100',
            'commission_percentage_supplier' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            // Calculate commission amounts
            $rentPrice = $validated['rent_price'];
            $validated['commission_amount_shop'] = round(
                $rentPrice * ($validated['commission_percentage_shop'] ?? 0) / 100,
                2
            );
            $validated['commission_amount_supplier'] = round(
                $rentPrice * ($validated['commission_percentage_supplier'] ?? 0) / 100,
                2
            );

            // Handle image upload
            if ($request->hasFile('image')) {
                $fileExtension = $request->file('image')->getClientOriginalExtension();
                $fileName = 'rental_' . date("YmdHis") . '.' . $fileExtension;
                $path = $request->file('image')->storeAs('rental_items', $fileName, 'public');
                $validated['image'] = 'storage/' . $path;
            }

            RentalItem::create($validated);

            return redirect()->route('rental-items.index')->banner('Rental item created successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating rental item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while creating the rental item. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RentalItem $rentalItem)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:255',
            'color_id' => 'nullable|exists:colors,id',
            'size' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'rental_quantity' => 'required|integer|min:1',
            'rent_price' => 'required|numeric|min:0',
            'commission_percentage_shop' => 'required|numeric|min:0|max:100',
            'commission_percentage_supplier' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            // Calculate commission amounts
            $rentPrice = $validated['rent_price'];
            $validated['commission_amount_shop'] = round(
                $rentPrice * ($validated['commission_percentage_shop'] ?? 0) / 100,
                2
            );
            $validated['commission_amount_supplier'] = round(
                $rentPrice * ($validated['commission_percentage_supplier'] ?? 0) / 100,
                2
            );

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($rentalItem->image && \Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('storage/', '', $rentalItem->image))) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('storage/', '', $rentalItem->image));
                }

                $fileExtension = $request->file('image')->getClientOriginalExtension();
                $fileName = 'rental_' . date("YmdHis") . '.' . $fileExtension;
                $path = $request->file('image')->storeAs('rental_items', $fileName, 'public');
                $validated['image'] = 'storage/' . $path;
            } else {
                // Keep the existing image
                $validated['image'] = $rentalItem->image;
            }

            $rentalItem->update($validated);

            return redirect()->route('rental-items.index')->banner('Rental item updated successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating rental item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the rental item. Please try again.');
        }
    }

    /**
     * API Endpoint to fetch rental items for the POS modal with optional filtering.
     */
    public function apiIndex(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = RentalItem::with(['category', 'color', 'supplier']);

        // Filter by search string (matches customer_name or size)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('size', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by Category
        if ($request->has('selectedCategory') && $request->selectedCategory != '') {
            $query->where('category_id', $request->selectedCategory);
        }

        // Filter by Color
        if ($request->has('color') && $request->color != '') {
            $query->whereHas('color', function ($q) use ($request) {
                $q->where('name', $request->color);
            });
        }

        // Filter by Stock Status
        if ($request->has('stockStatus') && $request->stockStatus != '') {
            if ($request->stockStatus == 'in') {
                $query->where('rental_quantity', '>', 0);
            } elseif ($request->stockStatus == 'out') {
                $query->where('rental_quantity', '<=', 0);
            }
        }

        // Default sorting
        $sortColumn = 'created_at';
        $sortDirection = 'desc';

        // Custom sort by price
        if ($request->has('sort') && $request->sort != '') {
            $sortColumn = 'rent_price';
            $sortDirection = ($request->sort == 'asc') ? 'asc' : 'desc';
        }

        $rentalItems = $query->orderBy($sortColumn, $sortDirection)->paginate(10);

        return response()->json([
            'products' => $rentalItems
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalItem $rentalItem)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $rentalItem->delete();

        return redirect()->route('rental-items.index')->banner('Rental item deleted successfully.');
    }

    /**
     * Display the Rental Summary page with booked, rented, and returned items.
     */
    public function rentalSummary(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search', '');

        // Booked items (status = 'booked')
        $bookedQuery = RentalBooking::where('status', 'booked')
            ->with('rentalItem');
        if ($search) {
            $bookedQuery->where(function ($q) use ($search) {
                $q->where('booking_order_id', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }
        $bookedItems = $bookedQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'booked_page');

        // Active rentals (rental sales that haven't been returned)
        $rentedQuery = Sale::whereNotNull('rental_type')
            ->where('is_rental_returned', false)
            ->with(['saleItems.rentalItem', 'customer']);
        if ($search) {
            $rentedQuery->where('order_id', 'like', '%' . $search . '%');
        }
        $rentedItems = $rentedQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'rented_page');

        // Returned rentals
        $returnedQuery = Sale::whereNotNull('rental_type')
            ->where('is_rental_returned', true)
            ->with(['saleItems.rentalItem', 'customer']);
        if ($search) {
            $returnedQuery->where('order_id', 'like', '%' . $search . '%');
        }
        $returnedItems = $returnedQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'returned_page');

        return Inertia::render('RentalSummary/Index', [
            'bookedItems' => $bookedItems,
            'rentedItems' => $rentedItems,
            'returnedItems' => $returnedItems,
            'search' => $search,
        ]);
    }
}
