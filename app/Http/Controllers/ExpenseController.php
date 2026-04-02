<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $allexpenses = Expense::with('user')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Expenses/Index', [
            'allexpenses' => $allexpenses,
            'totalExpenses' => $allexpenses->count(),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'date'        => 'nullable|date',
        ]);

        Expense::create([
            'user_id'     => Auth::id(),
            'amount'      => $validated['amount'],
            'description' => $validated['description'],
            'date'        => $validated['date'] ?? now()->toDateString(),
        ]);

        return redirect()->route('expenses.index')->banner('Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        //
    }

    public function edit(Expense $expense)
    {
        //
    }

    public function update(Request $request, Expense $expense)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'date'        => 'nullable|date',
        ]);

        $expense->update([
            'amount'      => $validated['amount'],
            'description' => $validated['description'],
            'date'        => $validated['date'] ?? $expense->date,
        ]);

        return redirect()->route('expenses.index')->banner('Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->banner('Expense deleted successfully.');
    }
}
