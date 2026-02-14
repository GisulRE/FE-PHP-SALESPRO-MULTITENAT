<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToAllTables extends Migration
{
    public function up()
    {
        // STEP 1: Normalize column types to match PKs (all should be BIGINT UNSIGNED)
        echo "Normalizing column types...\n";
        $this->normalizeColumnTypes();
        
        echo "Adding foreign keys...\n";
        // STEP 2: Add foreign keys
        // PRODUCTS
        $this->tryAddFK('products', 'category_id', 'categories', 'id', 'set null');
        $this->tryAddFK('products', 'brand_id', 'brands', 'id', 'set null');
        $this->tryAddFK('products', 'unit_id', 'units', 'id', 'set null');

        // PRODUCT_WAREHOUSE
        $this->tryAddFK('product_warehouse', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_warehouse', 'warehouse_id', 'warehouses', 'id', 'cascade');
        $this->tryAddFK('product_warehouse', 'variant_id', 'product_variants', 'id', 'cascade');

        // PRODUCT_VARIANTS
        $this->tryAddFK('product_variants', 'product_id', 'products', 'id', 'cascade');

        // PRODUCT_LOT
        $this->tryAddFK('product_lot', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_lot', 'warehouse_id', 'warehouses', 'id', 'cascade');

        // SALES
        $this->tryAddFK('sales', 'customer_id', 'customers', 'id', 'set null');
        $this->tryAddFK('sales', 'biller_id', 'billers', 'id', 'set null');
        $this->tryAddFK('sales', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('sales', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_SALES
        $this->tryAddFK('product_sales', 'sale_id', 'sales', 'id', 'cascade');
        $this->tryAddFK('product_sales', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_sales', 'variant_id', 'product_variants', 'id', 'set null');

        // PURCHASES
        $this->tryAddFK('purchases', 'supplier_id', 'suppliers', 'id', 'set null');
        $this->tryAddFK('purchases', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('purchases', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_PURCHASES
        $this->tryAddFK('product_purchases', 'purchase_id', 'purchases', 'id', 'cascade');
        $this->tryAddFK('product_purchases', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_purchases', 'variant_id', 'product_variants', 'id', 'set null');

        // RETURNS
        $this->tryAddFK('returns', 'customer_id', 'customers', 'id', 'set null');
        $this->tryAddFK('returns', 'biller_id', 'billers', 'id', 'set null');
        $this->tryAddFK('returns', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('returns', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_RETURNS
        $this->tryAddFK('product_returns', 'return_id', 'returns', 'id', 'cascade');
        $this->tryAddFK('product_returns', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_returns', 'variant_id', 'product_variants', 'id', 'set null');

        // RETURN_PURCHASES
        $this->tryAddFK('return_purchases', 'supplier_id', 'suppliers', 'id', 'set null');
        $this->tryAddFK('return_purchases', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('return_purchases', 'user_id', 'users', 'id', 'set null');

        // PURCHASE_PRODUCT_RETURN
        $this->tryAddFK('purchase_product_return', 'return_id', 'return_purchases', 'id', 'cascade');
        $this->tryAddFK('purchase_product_return', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('purchase_product_return', 'variant_id', 'product_variants', 'id', 'set null');

        // TRANSFERS
        $this->tryAddFK('transfers', 'from_warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('transfers', 'to_warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('transfers', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_TRANSFER
        $this->tryAddFK('product_transfer', 'transfer_id', 'transfers', 'id', 'cascade');
        $this->tryAddFK('product_transfer', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_transfer', 'variant_id', 'product_variants', 'id', 'set null');

        // ADJUSTMENTS
        $this->tryAddFK('adjustments', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('adjustments', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_ADJUSTMENTS
        $this->tryAddFK('product_adjustments', 'adjustment_id', 'adjustments', 'id', 'cascade');
        $this->tryAddFK('product_adjustments', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_adjustments', 'variant_id', 'product_variants', 'id', 'set null');

        // QUOTATIONS
        $this->tryAddFK('quotations', 'customer_id', 'customers', 'id', 'set null');
        $this->tryAddFK('quotations', 'biller_id', 'billers', 'id', 'set null');
        $this->tryAddFK('quotations', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('quotations', 'user_id', 'users', 'id', 'set null');

        // PRODUCT_QUOTATION
        $this->tryAddFK('product_quotation', 'quotation_id', 'quotations', 'id', 'cascade');
        $this->tryAddFK('product_quotation', 'product_id', 'products', 'id', 'cascade');

        // PAYMENTS
        $this->tryAddFK('payments', 'sale_id', 'sales', 'id', 'cascade');
        $this->tryAddFK('payments', 'purchase_id', 'purchases', 'id', 'cascade');
        $this->tryAddFK('payments', 'user_id', 'users', 'id', 'set null');

        // PAYMENT DETAILS
        $this->tryAddFK('payment_with_cheque', 'payment_id', 'payments', 'id', 'cascade');
        $this->tryAddFK('payment_with_credit_card', 'payment_id', 'payments', 'id', 'cascade');
        $this->tryAddFK('payment_with_gift_card', 'payment_id', 'payments', 'id', 'cascade');
        $this->tryAddFK('payment_with_gift_card', 'gift_card_id', 'gift_cards', 'id', 'set null');

        // EXPENSES
        $this->tryAddFK('expenses', 'expense_category_id', 'expense_categories', 'id', 'set null');
        $this->tryAddFK('expenses', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('expenses', 'user_id', 'users', 'id', 'set null');

        // CUSTOMERS
        $this->tryAddFK('customers', 'customer_group_id', 'customer_group', 'id', 'set null');

        // GIFT CARDS
        $this->tryAddFK('gift_card_recharges', 'gift_card_id', 'gift_cards', 'id', 'cascade');
        $this->tryAddFK('gift_card_recharges', 'user_id', 'users', 'id', 'set null');

        // EMPLOYEES & HR
        $this->tryAddFK('employees', 'user_id', 'users', 'id', 'cascade');
        $this->tryAddFK('employees', 'department_id', 'departments', 'id', 'set null');
        $this->tryAddFK('payrolls', 'employee_id', 'employees', 'id', 'cascade');
        $this->tryAddFK('attendances', 'employee_id', 'employees', 'id', 'cascade');
        $this->tryAddFK('shift_employee', 'employee_id', 'employees', 'id', 'cascade');

        // BILLERS
        $this->tryAddFK('biller_warehouses', 'biller_id', 'billers', 'id', 'cascade');
        $this->tryAddFK('biller_warehouses', 'warehouse_id', 'warehouses', 'id', 'cascade');

        // RECORD (inventory audit)
        $this->tryAddFK('record', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('record', 'warehouse_id', 'warehouses', 'id', 'cascade');

        // LOTE_SALE
        $this->tryAddFK('lote_sale', 'sale_id', 'sales', 'id', 'cascade');
        $this->tryAddFK('lote_sale', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('lote_sale', 'lot_id', 'product_lot', 'id', 'set null');

        // PRODUCT_ASSOCIATED
        $this->tryAddFK('product_associated', 'product_id', 'products', 'id', 'cascade');
        $this->tryAddFK('product_associated', 'associated_product_id', 'products', 'id', 'cascade');

        // RESERVATIONS
        $this->tryAddFK('reservations', 'customer_id', 'customers', 'id', 'set null');
        $this->tryAddFK('reservations', 'warehouse_id', 'warehouses', 'id', 'set null');

        // OTHERS
        $this->tryAddFK('coupons', 'user_id', 'users', 'id', 'set null');
        $this->tryAddFK('deposits', 'customer_id', 'customers', 'id', 'cascade');
        $this->tryAddFK('money_transfers', 'user_id', 'users', 'id', 'set null');

        // PRE_SALE
        $this->tryAddFK('pre_sale', 'customer_id', 'customers', 'id', 'set null');
        $this->tryAddFK('pre_sale', 'warehouse_id', 'warehouses', 'id', 'set null');
        $this->tryAddFK('pre_sale', 'user_id', 'users', 'id', 'set null');
        $this->tryAddFK('product_pre_sale', 'pre_sale_id', 'pre_sale', 'id', 'cascade');
        $this->tryAddFK('product_pre_sale', 'product_id', 'products', 'id', 'cascade');

        echo "\n✓ Foreign keys migration completed (some constraints may have been skipped due to type mismatches)\n";
    }

    public function down()
    {
        // Reverse order drop - child tables first
        $this->tryDropFK('product_pre_sale', 'product_pre_sale_pre_sale_id_foreign');
        $this->tryDropFK('product_pre_sale', 'product_pre_sale_product_id_foreign');
        $this->tryDropFK('pre_sale', 'pre_sale_customer_id_foreign');
        $this->tryDropFK('pre_sale', 'pre_sale_warehouse_id_foreign');
        $this->tryDropFK('pre_sale', 'pre_sale_user_id_foreign');
        
        $this->tryDropFK('money_transfers', 'money_transfers_user_id_foreign');
        $this->tryDropFK('deposits', 'deposits_customer_id_foreign');
        $this->tryDropFK('coupons', 'coupons_user_id_foreign');
        $this->tryDropFK('reservations', 'reservations_customer_id_foreign');
        $this->tryDropFK('reservations', 'reservations_warehouse_id_foreign');
        
        $this->tryDropFK('product_associated', 'product_associated_product_id_foreign');
        $this->tryDropFK('product_associated', 'product_associated_associated_product_id_foreign');
        $this->tryDropFK('lote_sale', 'lote_sale_sale_id_foreign');
        $this->tryDropFK('lote_sale', 'lote_sale_product_id_foreign');
        $this->tryDropFK('lote_sale', 'lote_sale_lot_id_foreign');
        
        $this->tryDropFK('record', 'record_product_id_foreign');
        $this->tryDropFK('record', 'record_warehouse_id_foreign');
        $this->tryDropFK('biller_warehouses', 'biller_warehouses_biller_id_foreign');
        $this->tryDropFK('biller_warehouses', 'biller_warehouses_warehouse_id_foreign');
        
        $this->tryDropFK('shift_employee', 'shift_employee_employee_id_foreign');
        $this->tryDropFK('attendances', 'attendances_employee_id_foreign');
        $this->tryDropFK('payrolls', 'payrolls_employee_id_foreign');
        $this->tryDropFK('employees', 'employees_user_id_foreign');
        $this->tryDropFK('employees', 'employees_department_id_foreign');
        
        $this->tryDropFK('gift_card_recharges', 'gift_card_recharges_gift_card_id_foreign');
        $this->tryDropFK('gift_card_recharges', 'gift_card_recharges_user_id_foreign');
        $this->tryDropFK('customers', 'customers_customer_group_id_foreign');
        $this->tryDropFK('expenses', 'expenses_expense_category_id_foreign');
        $this->tryDropFK('expenses', 'expenses_warehouse_id_foreign');
        $this->tryDropFK('expenses', 'expenses_user_id_foreign');
        
        $this->tryDropFK('payment_with_gift_card', 'payment_with_gift_card_payment_id_foreign');
        $this->tryDropFK('payment_with_gift_card', 'payment_with_gift_card_gift_card_id_foreign');
        $this->tryDropFK('payment_with_credit_card', 'payment_with_credit_card_payment_id_foreign');
        $this->tryDropFK('payment_with_cheque', 'payment_with_cheque_payment_id_foreign');
        $this->tryDropFK('payments', 'payments_sale_id_foreign');
        $this->tryDropFK('payments', 'payments_purchase_id_foreign');
        $this->tryDropFK('payments', 'payments_user_id_foreign');
        
        $this->tryDropFK('product_quotation', 'product_quotation_quotation_id_foreign');
        $this->tryDropFK('product_quotation', 'product_quotation_product_id_foreign');
        $this->tryDropFK('quotations', 'quotations_customer_id_foreign');
        $this->tryDropFK('quotations', 'quotations_biller_id_foreign');
        $this->tryDropFK('quotations', 'quotations_warehouse_id_foreign');
        $this->tryDropFK('quotations', 'quotations_user_id_foreign');
        
        $this->tryDropFK('product_adjustments', 'product_adjustments_adjustment_id_foreign');
        $this->tryDropFK('product_adjustments', 'product_adjustments_product_id_foreign');
        $this->tryDropFK('product_adjustments', 'product_adjustments_variant_id_foreign');
        $this->tryDropFK('adjustments', 'adjustments_warehouse_id_foreign');
        $this->tryDropFK('adjustments', 'adjustments_user_id_foreign');
        
        $this->tryDropFK('product_transfer', 'product_transfer_transfer_id_foreign');
        $this->tryDropFK('product_transfer', 'product_transfer_product_id_foreign');
        $this->tryDropFK('product_transfer', 'product_transfer_variant_id_foreign');
        $this->tryDropFK('transfers', 'transfers_from_warehouse_id_foreign');
        $this->tryDropFK('transfers', 'transfers_to_warehouse_id_foreign');
        $this->tryDropFK('transfers', 'transfers_user_id_foreign');
        
        $this->tryDropFK('purchase_product_return', 'purchase_product_return_return_id_foreign');
        $this->tryDropFK('purchase_product_return', 'purchase_product_return_product_id_foreign');
        $this->tryDropFK('purchase_product_return', 'purchase_product_return_variant_id_foreign');
        $this->tryDropFK('return_purchases', 'return_purchases_supplier_id_foreign');
        $this->tryDropFK('return_purchases', 'return_purchases_warehouse_id_foreign');
        $this->tryDropFK('return_purchases', 'return_purchases_user_id_foreign');
        
        $this->tryDropFK('product_returns', 'product_returns_return_id_foreign');
        $this->tryDropFK('product_returns', 'product_returns_product_id_foreign');
        $this->tryDropFK('product_returns', 'product_returns_variant_id_foreign');
        $this->tryDropFK('returns', 'returns_customer_id_foreign');
        $this->tryDropFK('returns', 'returns_biller_id_foreign');
        $this->tryDropFK('returns', 'returns_warehouse_id_foreign');
        $this->tryDropFK('returns', 'returns_user_id_foreign');
        
        $this->tryDropFK('product_purchases', 'product_purchases_purchase_id_foreign');
        $this->tryDropFK('product_purchases', 'product_purchases_product_id_foreign');
        $this->tryDropFK('product_purchases', 'product_purchases_variant_id_foreign');
        $this->tryDropFK('purchases', 'purchases_supplier_id_foreign');
        $this->tryDropFK('purchases', 'purchases_warehouse_id_foreign');
        $this->tryDropFK('purchases', 'purchases_user_id_foreign');
        
        $this->tryDropFK('product_sales', 'product_sales_sale_id_foreign');
        $this->tryDropFK('product_sales', 'product_sales_product_id_foreign');
        $this->tryDropFK('product_sales', 'product_sales_variant_id_foreign');
        $this->tryDropFK('sales', 'sales_customer_id_foreign');
        $this->tryDropFK('sales', 'sales_biller_id_foreign');
        $this->tryDropFK('sales', 'sales_warehouse_id_foreign');
        $this->tryDropFK('sales', 'sales_user_id_foreign');
        
        $this->tryDropFK('product_lot', 'product_lot_product_id_foreign');
        $this->tryDropFK('product_lot', 'product_lot_warehouse_id_foreign');
        $this->tryDropFK('product_variants', 'product_variants_product_id_foreign');
        $this->tryDropFK('product_warehouse', 'product_warehouse_product_id_foreign');
        $this->tryDropFK('product_warehouse', 'product_warehouse_warehouse_id_foreign');
        $this->tryDropFK('product_warehouse', 'product_warehouse_variant_id_foreign');
        
        $this->tryDropFK('products', 'products_category_id_foreign');
        $this->tryDropFK('products', 'products_brand_id_foreign');
        $this->tryDropFK('products', 'products_unit_id_foreign');
    }

    /**
     * Normalize all FK column types to INT UNSIGNED to match PK types
     */
    private function normalizeColumnTypes()
    {
        // Define all FK columns that need normalization
        $normalizations = [
            'products' => ['category_id', 'brand_id', 'unit_id'],
            'product_warehouse' => ['product_id', 'warehouse_id', 'variant_id'],
            'product_variants' => ['product_id'],
            'product_lot' => ['product_id', 'warehouse_id'],
            'sales' => ['customer_id', 'biller_id', 'warehouse_id', 'user_id'],
            'product_sales' => ['sale_id', 'product_id', 'variant_id'],
            'purchases' => ['supplier_id', 'warehouse_id', 'user_id'],
            'product_purchases' => ['purchase_id', 'product_id', 'variant_id'],
            'returns' => ['customer_id', 'biller_id', 'warehouse_id', 'user_id'],
            'product_returns' => ['return_id', 'product_id', 'variant_id'],
            'return_purchases' => ['supplier_id', 'warehouse_id', 'user_id'],
            'purchase_product_return' => ['return_id', 'product_id', 'variant_id'],
            'transfers' => ['from_warehouse_id', 'to_warehouse_id', 'user_id'],
            'product_transfer' => ['transfer_id', 'product_id', 'variant_id'],
            'adjustments' => ['warehouse_id', 'user_id'],
            'product_adjustments' => ['adjustment_id', 'product_id', 'variant_id'],
            'quotations' => ['customer_id', 'biller_id', 'warehouse_id', 'user_id'],
            'product_quotation' => ['quotation_id', 'product_id'],
            'payments' => ['sale_id', 'purchase_id', 'user_id'],
            'payment_with_cheque' => ['payment_id'],
            'payment_with_credit_card' => ['payment_id'],
            'payment_with_gift_card' => ['payment_id', 'gift_card_id'],
            'expenses' => ['expense_category_id', 'warehouse_id', 'user_id'],
            'customers' => ['customer_group_id'],
            'gift_card_recharges' => ['gift_card_id', 'user_id'],
            'employees' => ['user_id', 'department_id'],
            'payrolls' => ['employee_id'],
            'attendances' => ['employee_id'],
            'shift_employee' => ['employee_id'],
            'biller_warehouses' => ['biller_id', 'warehouse_id'],
            'record' => ['product_id', 'warehouse_id'],
            'lote_sale' => ['sale_id', 'product_id', 'lot_id'],
            'product_associated' => ['product_id', 'associated_product_id'],
            'reservations' => ['customer_id', 'warehouse_id'],
            'coupons' => ['user_id'],
            'deposits' => ['customer_id'],
            'money_transfers' => ['user_id'],
            'pre_sale' => ['customer_id', 'warehouse_id', 'user_id'],
            'product_pre_sale' => ['pre_sale_id', 'product_id'],
        ];

        foreach ($normalizations as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT UNSIGNED NULL");
                    echo "  ✓ Normalized {$table}.{$column}\n";
                } catch (\Exception $e) {
                    echo "  ⚠ Could not normalize {$table}.{$column}: {$e->getMessage()}\n";
                }
            }
        }
    }

    /**
     * Try to add a foreign key, skip if it fails (type mismatch, already exists, etc.)
     */
    private function tryAddFK($table, $column, $refTable, $refColumn, $onDelete = 'cascade')
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($refTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($refTable, $refColumn)) {
            return;
        }

        $fkName = "{$table}_{$column}_foreign";

        // Check if FK already exists
        if ($this->foreignKeyExists($table, $fkName)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $refColumn, $onDelete) {
                $fk = $blueprint->foreign($column)->references($refColumn)->on($refTable);
                
                if ($onDelete === 'cascade') {
                    $fk->onDelete('cascade');
                } elseif ($onDelete === 'set null') {
                    $fk->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Skip this FK - likely type mismatch or other constraint issue
            echo "  ⚠ Skipped FK: {$table}.{$column} -> {$refTable}.{$refColumn} ({$e->getMessage()})\n";
        }
    }

    /**
     * Try to drop a foreign key, ignore if it doesn't exist
     */
    private function tryDropFK($table, $fkName)
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
                $blueprint->dropForeign($fkName);
            });
        } catch (\Exception $e) {
            // Ignore - FK doesn't exist
        }
    }

    /**
     * Check if a foreign key exists
     */
    private function foreignKeyExists($table, $foreignKey)
    {
        try {
            $conn = Schema::getConnection();
            $dbSchemaManager = $conn->getDoctrineSchemaManager();
            $foreignKeys = $dbSchemaManager->listTableForeignKeys($table);

            foreach ($foreignKeys as $fk) {
                if ($fk->getName() === $foreignKey) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
