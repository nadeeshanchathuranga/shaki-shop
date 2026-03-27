<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\SupplierCommissionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;



class SupplierController extends Controller
{

    public function index()
    {
        $allsuppliers = Supplier::orderBy('created_at', 'desc')->get();
        return Inertia::render('Suppliers/Index', [
            'allsuppliers' => $allsuppliers,
            'totalSuppliers' => $allsuppliers->count()
        ]);
    }

    // public function create()
    // {
    //     $categories = Category::all();

    //     return Inertia::render('Categories/Create', [
    //         'categories' => $categories,
    //     ]);
    // }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:191|regex:/^[a-zA-Z\s]+$/',
           'contact' => 'required|string|regex:/^\d{10}$/',
            'email' => 'required|email|regex:/^[\w\.-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,6}$/|max:255|unique:suppliers,email',
            'address' => 'required|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);



        // if ($request->hasFile('image')) {
        //     $fileExtension = $request->file('image')->getClientOriginalExtension();
        //     $fileName = 'supplier' . date("YmdHis") . '.' . $fileExtension;
        //     $destinationPath = "images/uploads/supplier/";
        //     $request->file('image')->move(public_path($destinationPath), $fileName);
        //     $validated['image'] = $destinationPath . $fileName;
        // }

        if ($request->hasFile('image')) {
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->banner('Supplier created successfully.');
    }


    public function update(Request $request, Supplier $supplier)
    {

        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        // Validate incoming data
        $validated = $request->validate([
            'name' => 'nullable|string|max:191',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
            }

            // Save the new image
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        } else {
            // Retain the old image if no new image is uploaded
            $validated['image'] = $supplier->image;
        }


        $supplier->update($validated);


        // Redirect back with success message
        return redirect()->route('suppliers.index')->banner('Supplier updated successfully.');
    }





    public function destroy(Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->banner('Supplier deleted successfully.');
    }

    public function invoice(Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $invoiceData = $this->getSupplierCommissionInvoiceData($supplier->id);

        return Inertia::render('Suppliers/Invoice', [
            'supplier' => $supplier,
            'commissionRows' => $invoiceData['commissionRows'],
            'paymentRows' => $invoiceData['paymentRows'],
            'totals' => $invoiceData['totals'],
        ]);
    }

    public function storeInvoicePayment(Request $request, Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoiceData = $this->getSupplierCommissionInvoiceData($supplier->id);
        $outstanding = $invoiceData['totals']['outstanding_amount'];
        $amount = round((float) $validated['amount'], 2);

        if ($amount > $outstanding) {
            return back()->withErrors([
                'amount' => 'Payment exceeds outstanding amount (' . number_format($outstanding, 2) . ').',
            ]);
        }

        SupplierCommissionPayment::create([
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'amount' => $amount,
            'notes' => $validated['notes'] ?? null,
            'paid_at' => now(),
        ]);

        return back()->banner('Supplier commission payment recorded successfully.');
    }

    private function getSupplierCommissionInvoiceData(int $supplierId): array
    {
        $commissionRows = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('rental_items', 'rental_items.id', '=', 'sale_items.rental_item_id')
            ->where('rental_items.supplier_id', $supplierId)
            ->whereNotNull('sale_items.rental_item_id')
            ->orderByDesc('sale_items.created_at')
            ->get([
                'sale_items.id',
                'sale_items.created_at',
                'sales.order_id',
                'sales.rental_type',
                'sale_items.quantity',
                'sale_items.unit_price',
                'sale_items.total_price',
                'rental_items.item_name',
                'rental_items.commission_amount_shop',
                'rental_items.commission_amount_supplier',
            ])
            ->map(function ($row) {
                $quantity = (float) $row->quantity;
                $supplierCommission = round($quantity * (float) $row->commission_amount_supplier, 2);
                $shopCommission = round($quantity * (float) $row->commission_amount_shop, 2);

                return [
                    'id' => $row->id,
                    'date' => optional($row->created_at)->format('Y-m-d H:i:s'),
                    'order_id' => $row->order_id,
                    'rental_type' => $row->rental_type,
                    'item_name' => $row->item_name,
                    'quantity' => $quantity,
                    'unit_price' => (float) $row->unit_price,
                    'total_price' => (float) $row->total_price,
                    'supplier_commission' => $supplierCommission,
                    'shop_commission' => $shopCommission,
                ];
            })
            ->values();

        $paymentRows = SupplierCommissionPayment::query()
            ->where('supplier_id', $supplierId)
            ->with('user:id,name')
            ->orderByDesc('paid_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => optional($payment->paid_at)->format('Y-m-d H:i:s'),
                    'amount' => (float) $payment->amount,
                    'notes' => $payment->notes,
                    'paid_by' => optional($payment->user)->name,
                ];
            })
            ->values();

        $totalRentAmount = (float) $commissionRows->sum('total_price');
        $totalSupplierCommission = (float) $commissionRows->sum('supplier_commission');
        $totalShopCommission = (float) $commissionRows->sum('shop_commission');
        $totalPaid = (float) $paymentRows->sum('amount');

        return [
            'commissionRows' => $commissionRows,
            'paymentRows' => $paymentRows,
            'totals' => [
                'total_rent_amount' => round($totalRentAmount, 2),
                'total_supplier_commission' => round($totalSupplierCommission, 2),
                'total_shop_commission' => round($totalShopCommission, 2),
                'total_paid' => round($totalPaid, 2),
                'outstanding_amount' => round(max($totalSupplierCommission - $totalPaid, 0), 2),
            ],
        ];
    }
}
