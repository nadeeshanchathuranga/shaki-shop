<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\RentalItem;
use App\Models\Size;
use App\Models\StockTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $allemployee = Employee::orderBy('created_at', 'desc')->get();


        // Render the page for GET requests
        return Inertia::render('Pos/Index', [
            'product' => null,
            'error' => null,
            'loggedInUser' => Auth::user(),
            'allcategories' => $allcategories,
            'allemployee' => $allemployee,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    public function getProduct(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required',
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('code', $request->barcode)
            ->first();

        return response()->json([
            'product' => $product,
            'error' => $product ? null : 'Product not found',
        ]);
    }

    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }

    public function submit(Request $request)
    {

        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }
        // Combine countryCode and contactNumber to create the phone field


        $customer = null;

        $products = $request->input('products');
        $totalAmount = collect($products)->reduce(function ($carry, $product) {
            return $carry + ($product['quantity'] * ($product['selling_price'] ?? 0));
        }, 0);

        $totalCost = collect($products)->reduce(function ($carry, $product) {
            // Rental items don't have cost_price, default to 0
            return $carry + ($product['quantity'] * ($product['cost_price'] ?? 0));
        }, 0);

        $productDiscounts = collect($products)->reduce(function ($carry, $product) {
            if (isset($product['discount']) && $product['discount'] > 0 && isset($product['apply_discount']) && $product['apply_discount'] != false) {
                $discountAmount = ($product['selling_price'] - $product['discounted_price']) * $product['quantity'];
                return $carry + $discountAmount;
            }
            return $carry;
        }, 0);

        // Get coupon discount if applied
        $couponDiscount = isset($request->input('appliedCoupon')['discount']) ?
            floatval($request->input('appliedCoupon')['discount']) : 0;


        // Calculate total combined discount
        $totalDiscount = $productDiscounts + $couponDiscount ;

        DB::beginTransaction(); // Start a transaction

        try {
            // Save the customer data to the database
            if ($request->input('customer.contactNumber') || $request->input('customer.name') || $request->input('customer.email')) {

                $phone = $request->input('customer.countryCode') . $request->input('customer.contactNumber');
                $customer = Customer::where('email', $request->input('customer.email'))->first();
                $customer1 = Customer::where('phone', $phone)->first();

                if ($customer) {
                    $email = '';
                } else {
                    $email = $request->input('customer.email');
                }

                if ($customer1) {
                    $phone = '';
                }

                if (!empty($phone) || !empty($email) || !empty($request->input('customer.name'))) {
                    $customer = Customer::create([
                        'name' => $request->input('customer.name'),
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $request->input('customer.address', ''), // Optional address
                        'member_since' => now()->toDateString(), // Current date
                        'loyalty_points' => 0, // Default value
                    ]);
                }
            }

            // Create the sale record
            $sale = Sale::create([
                'customer_id' => $customer ? $customer->id : null, // Nullable customer_id
                'employee_id' => $request->input('employee_id'),
                'user_id' => $request->input('userId'), // Logged-in user ID
                'order_id' => $request->input('orderid'),
                'total_amount' => $totalAmount, // Total amount of the sale
                'discount' => $totalDiscount, // Default discount to 0 if not provided
                'total_cost' => $totalCost,
                'payment_method' => $request->input('paymentMethod'), // Payment method from the request
                'sale_date' => now()->toDateString(), // Current date
                'cash' => $request->input('cash'),
                'custom_discount' => $request->input('custom_discount'),
                'rental_date_from' => $request->input('rental_date_from'),
                'rental_date_to' => $request->input('rental_date_to'),
                'advance_amount' => $request->input('advance_amount', 0),

            ]);

            foreach ($products as $product) {
                // Check if this is a rental item
                if (isset($product['is_rental']) && $product['is_rental']) {
                    $rentalItemModel = RentalItem::find($product['original_rental_id'] ?? str_replace('rental_', '', $product['id']));
                    
                    if ($rentalItemModel) {
                        $newStockQuantity = $rentalItemModel->rental_quantity - $product['quantity'];

                        if ($newStockQuantity < 0) {
                            DB::rollBack();
                            return response()->json([
                                'message' => "Insufficient availability for rental item: {$rentalItemModel->customer_name} ({$rentalItemModel->rental_quantity} available)",
                            ], 423);
                        }

                        // Create sale item for rental item
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'rental_item_id' => $rentalItemModel->id,
                            'quantity' => $product['quantity'],
                            'unit_price' => $product['selling_price'],
                            'total_price' => $product['quantity'] * $product['selling_price'],
                        ]);

                        // Update rental quantity
                        $rentalItemModel->update([
                            'rental_quantity' => $newStockQuantity,
                        ]);
                    }
                    continue; // Skip the regular product logic
                }

                // Check stock before saving normal sale items
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    $newStockQuantity = $productModel->stock_quantity - $product['quantity'];

                    // Prevent stock from going negative
                    if ($newStockQuantity < 0) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for product: {$productModel->name}
                            ({$productModel->stock_quantity} available)",
                        ], 423);
                    }

                    if ($productModel->expire_date && now()->greaterThan($productModel->expire_date)) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "The product '{$productModel->name}' has expired (Expiration Date: {$productModel->expire_date->format('Y-m-d')}).",
                        ], 423); // HTTP 422 Unprocessable Entity
                    }

                    // Create sale item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['selling_price'],
                        'total_price' => $product['quantity'] * $product['selling_price'],
                    ]);

                    StockTransaction::create([
                        'product_id' => $product['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $product['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $productModel->supplier_id ?? null,
                    ]);

                    // Update stock quantity
                    $productModel->update([
                        'stock_quantity' => $newStockQuantity,
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Sale recorded successfully!'], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch rental-confirmed sales for the Return Rental modal.
     */
    public function getRentalSales(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Sale::whereNotNull('rental_date_from')
            ->where('advance_amount', '>', 0)
            ->where('is_rental_returned', false)
            ->with(['saleItems.rentalItem', 'customer']);

        // Search by order_id
        if ($request->has('search') && $request->search != '') {
            $query->where('order_id', 'like', '%' . $request->search . '%');
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'sales' => $sales
        ]);
    }

    /**
     * Process a rental item return.
     */
    public function submitRentalReturn(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $saleId = $request->input('sale_id');
        $cashReceived = $request->input('cash_received', 0);
        $paymentMethod = $request->input('payment_method', 'cash');

        $sale = Sale::with(['saleItems.rentalItem', 'customer'])->findOrFail($saleId);

        // Calculate late days
        $returnDate = now()->startOfDay();
        $agreedReturnDate = \Carbon\Carbon::parse($sale->rental_date_to)->startOfDay();
        $lateDays = 0;
        $lateFee = 0;

        if ($returnDate->greaterThan($agreedReturnDate)) {
            $lateDays = $returnDate->diffInDays($agreedReturnDate);
            $lateFee = $lateDays * 200;
        }

        // Calculate the amount due after advance (from original sale total)
        $originalTotal = $sale->total_amount - $sale->discount - ($sale->custom_discount ?? 0);
        $amountAfterAdvance = $originalTotal - $sale->advance_amount;
        $totalToPay = $amountAfterAdvance + $lateFee;
        $balance = $cashReceived - $totalToPay;

        // Restore rental quantities (items returned)
        foreach ($sale->saleItems as $saleItem) {
            if ($saleItem->rental_item_id && $saleItem->rentalItem) {
                $saleItem->rentalItem->increment('rental_quantity', $saleItem->quantity);
            }
        }

        // Mark the sale as returned and update financial totals if there are late fees
        $sale->is_rental_returned = true;
        if ($paymentMethod) {
            $sale->payment_method = $paymentMethod;
        }
        if ($lateFee > 0) {
            $sale->total_amount += $lateFee;
            $sale->cash += $totalToPay;
        } else {
            // Just add the remaining balance (if any) to cash
            $sale->cash += $totalToPay;
        }
        $sale->save();

        return response()->json([
            'message' => 'Rental return processed successfully!',
            'sale' => $sale,
            'late_days' => $lateDays,
            'late_fee' => $lateFee,
            'amount_after_advance' => $amountAfterAdvance,
            'total_to_pay' => $totalToPay,
            'cash_received' => $cashReceived,
            'payment_method' => $sale->payment_method,
            'balance' => $balance,
        ]);
    }
}
