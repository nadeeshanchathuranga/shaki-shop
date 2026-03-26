<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;

class CheckRentalReturns extends Command
{
    protected $signature = 'check:rental-returns';
    protected $description = 'Check rental returns data quality';

    public function handle()
    {
        $returns = Sale::where('is_rental_returned', true)
            ->select('id', 'order_id', 'late_fee', 'late_days', 'damage_amount', 'deposit_refund', 'deposit')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        if ($returns->isEmpty()) {
            $this->warn('No returned rentals found!');
            return;
        }

        $this->info("\n Returned Rental Sales (Last 10)\n");
        $this->info('=====================================');
        
        foreach ($returns as $sale) {
            $this->line("Order ID: {$sale->order_id}");
            $this->line("  Late Fee: " . ($sale->late_fee ?? 'NULL'));
            $this->line("  Late Days: " . ($sale->late_days ?? 'NULL'));
            $this->line("  Damage Amount: " . ($sale->damage_amount ?? 'NULL'));
            $this->line("  Deposit: {$sale->deposit}");
            $this->line("  Deposit Refund: " . ($sale->deposit_refund ?? 'NULL'));
            $this->line(" ---");
        }

        $this->info(' Total returned rentals: ' . Sale::where('is_rental_returned', true)->count());
    }
}
