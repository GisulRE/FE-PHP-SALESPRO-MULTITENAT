<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetFinancials extends Command
{
    protected $signature = 'reset:financials {--company_id=} {--yes}';

    protected $description = 'Resetea valores financieros: cajas, ventas, pagos, transferencias y kardex a cero (opcional por company_id)';

    public function handle()
    {
        $companyId = $this->option('company_id');

        if (!$this->option('yes')) {
            $confirm = $this->confirm('¿Estás seguro de que quieres poner a cero cajas, ventas y movimientos? (esto es irreversible)');
            if (!$confirm) {
                $this->info('Operación cancelada. Usa --yes para evitar la confirmación.');
                return 0;
            }
        }

        $whereCompany = function ($query) use ($companyId) {
            if (!empty($companyId)) {
                $query->where('company_id', $companyId);
            }
        };

        // Cashiers
        $cashierQuery = DB::table('cashier');
        if ($companyId) $cashierQuery->where('company_id', $companyId);
        $affected = $cashierQuery->update([
            'amount_start' => 0,
            'amount_end' => 0,
            'is_active' => 0,
        ]);
        $this->info("Cajas actualizadas: $affected");

        // Sales
        $salesQuery = DB::table('sales');
        if ($companyId) $salesQuery->where('company_id', $companyId);
        $affected = $salesQuery->update([
            'total_qty' => 0,
            'total_discount' => 0,
            'total_tax' => 0,
            'total_price' => 0,
            'order_tax' => 0,
            'order_discount' => 0,
            'coupon_discount' => 0,
            'shipping_cost' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
            'total_tips' => 0,
        ]);
        $this->info("Ventas actualizadas: $affected");

        // Payments
        if (DB::getSchemaBuilder()->hasTable('payments')) {
            $paymentsQuery = DB::table('payments');
            if ($companyId) $paymentsQuery->where('company_id', $companyId);
            $affected = $paymentsQuery->update([
                'amount' => 0,
                'change' => 0,
            ]);
            $this->info("Pagos actualizados: $affected");
        } else {
            $this->info('Tabla payments no existe. Omitiendo.');
        }

        // Money transfers
        if (DB::getSchemaBuilder()->hasTable('money_transfers')) {
            $mtQuery = DB::table('money_transfers');
            if ($companyId) $mtQuery->where('company_id', $companyId);
            $affected = $mtQuery->update(['amount' => 0]);
            $this->info("Transferencias de dinero actualizadas: $affected");
        } else {
            $this->info('Tabla money_transfers no existe. Omitiendo.');
        }

        // Accounts
        if (DB::getSchemaBuilder()->hasTable('accounts')) {
            $accQuery = DB::table('accounts');
            if ($companyId) $accQuery->where('company_id', $companyId);
            $affected = $accQuery->update([
                'initial_balance' => 0,
                'total_balance' => 0,
            ]);
            $this->info("Cuentas actualizadas (initial/total): $affected");
        } else {
            $this->info('Tabla accounts no existe. Omitiendo.');
        }

        // Returns (ventas devueltas)
        if (DB::getSchemaBuilder()->hasTable('returns')) {
            $rQuery = DB::table('returns');
            if ($companyId) $rQuery->where('company_id', $companyId);
            $affected = $rQuery->update(['grand_total' => 0]);
            $this->info("Returns actualizados (grand_total): $affected");
        } else {
            $this->info('Tabla returns no existe. Omitiendo.');
        }

        // Return purchases
        if (DB::getSchemaBuilder()->hasTable('return_purchases')) {
            $rpQuery = DB::table('return_purchases');
            if ($companyId) $rpQuery->where('company_id', $companyId);
            $affected = $rpQuery->update(['grand_total' => 0]);
            $this->info("Return purchases actualizados (grand_total): $affected");
        } else {
            $this->info('Tabla return_purchases no existe. Omitiendo.');
        }

        // Expenses
        if (DB::getSchemaBuilder()->hasTable('expenses')) {
            $expQuery = DB::table('expenses');
            if ($companyId) $expQuery->where('company_id', $companyId);
            $affected = $expQuery->update(['amount' => 0]);
            $this->info("Gastos actualizados (amount): $affected");
        } else {
            $this->info('Tabla expenses no existe. Omitiendo.');
        }

        // Payrolls
        if (DB::getSchemaBuilder()->hasTable('payrolls')) {
            $payQuery = DB::table('payrolls');
            if ($companyId) $payQuery->where('company_id', $companyId);
            $affected = $payQuery->update(['amount' => 0]);
            $this->info("Nominas actualizadas (amount): $affected");
        } else {
            $this->info('Tabla payrolls no existe. Omitiendo.');
        }

        // Adjustment accounts
        if (DB::getSchemaBuilder()->hasTable('adjustment_accounts')) {
            $adjQuery = DB::table('adjustment_accounts');
            if ($companyId) $adjQuery->where('company_id', $companyId);
            $affected = $adjQuery->update(['amount' => 0]);
            $this->info("Ajustes de cuenta actualizados (amount): $affected");
        } else {
            $this->info('Tabla adjustment_accounts no existe. Omitiendo.');
        }

        // Kardex (movimientos de inventario)
        if (DB::getSchemaBuilder()->hasTable('kardex')) {
            $kQuery = DB::table('kardex');
            if ($companyId) $kQuery->where('company_id', $companyId);
            $affected = $kQuery->update([
                'entrada' => 0,
                'salida' => 0,
                'warehouse_qty_after' => 0,
                'cost' => 0,
            ]);
            $this->info("Registros de kardex actualizados: $affected");
        } else {
            $this->info('Tabla kardex no existe. Omitiendo.');
        }

        $this->info('Reset financiero completado. Verifica integridad de datos y respaldos.');
        // Debug: mostrar desglose por cuenta para verificar saldos (no modifica datos)
        if (DB::getSchemaBuilder()->hasTable('accounts')) {
            $this->info("\nDesglose por cuenta (componentes de crédito y débito):");
            $accounts = DB::table('accounts')->where('is_active', true)->get();
            foreach ($accounts as $acc) {
                $payment_recieved = DB::table('payments')->whereNotNull('sale_id')->where('account_id', $acc->id)->sum('amount');
                $payment_sent = DB::table('payments')->whereNotNull('purchase_id')->where('account_id', $acc->id)->sum('amount');
                $returns = DB::table('returns')->where('account_id', $acc->id)->sum('grand_total');
                $return_purchase = DB::table('return_purchases')->where('account_id', $acc->id)->sum('grand_total');
                $expenses = DB::table('expenses')->where('account_id', $acc->id)->sum('amount');
                $payrolls = DB::table('payrolls')->where('account_id', $acc->id)->sum('amount');
                $sent_money_via_transfer = DB::table('money_transfers')->where('from_account_id', $acc->id)->sum('amount');
                $recieved_money_via_transfer = DB::table('money_transfers')->where('to_account_id', $acc->id)->sum('amount');
                $adjustment_account_ing = DB::table('adjustment_accounts')->where([['account_id', $acc->id], ['type_adjustment', 'ING']])->sum('amount');
                $adjustment_account_egr = DB::table('adjustment_accounts')->where([['account_id', $acc->id], ['type_adjustment', 'EGR']])->sum('amount');

                $credit = $payment_recieved + $return_purchase + $recieved_money_via_transfer + $adjustment_account_ing + ($acc->initial_balance ?? 0);
                $debit = $payment_sent + $returns + $expenses + $payrolls + $sent_money_via_transfer + $adjustment_account_egr;

                $this->info("Cuenta: {$acc->name} (id: {$acc->id})");
                $this->info("  payment_recieved: $payment_recieved");
                $this->info("  payment_sent: $payment_sent");
                $this->info("  returns: $returns");
                $this->info("  return_purchase: $return_purchase");
                $this->info("  expenses: $expenses");
                $this->info("  payrolls: $payrolls");
                $this->info("  sent_money_via_transfer: $sent_money_via_transfer");
                $this->info("  recieved_money_via_transfer: $recieved_money_via_transfer");
                $this->info("  adjustment_ing: $adjustment_account_ing");
                $this->info("  adjustment_egr: $adjustment_account_egr");
                $this->info("  initial_balance: " . ($acc->initial_balance ?? 0));
                $this->info("  => credit: $credit  |  debit: $debit\n");
            }
        }

        return 0;
    }
}
