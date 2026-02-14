<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateKardexView extends Migration
{
    public function up()
    {
        // Drop table or view if exists
        DB::unprepared('DROP TABLE IF EXISTS `kardex`');
        DB::unprepared('DROP VIEW IF EXISTS `kardex`');
        
        // Create view
        DB::unprepared("
            CREATE ALGORITHM=UNDEFINED DEFINER=`admingisulsrl`@`localhost` SQL SECURITY DEFINER VIEW `kardex` AS
            SELECT record.transaction_id AS transaction_id,
                   record.product_id AS product_id,
                   products.name AS product,
                   CASE WHEN record.transaction_type = 0 THEN 'INIT' WHEN record.transaction_type = 1 THEN 'VENTA' WHEN record.transaction_type = 2 THEN 'COMPRA' WHEN record.transaction_type = 3 THEN 'RETURN' WHEN record.transaction_type = 4 THEN 'TRANSFER' WHEN record.transaction_type = 5 THEN 'AJUSTE' WHEN record.transaction_type = 6 THEN 'COMPRA RETURN' ELSE 'Otro' END AS transaction_type,
                   warehouses.id AS warehouse_id,
                   warehouses.name AS warehouse,
                   record.warehouse_qty_before AS warehouse_qty_before,
                   record.warehouse_qty_after AS warehouse_qty_after,
                   CASE WHEN record.warehouse_qty_after - record.warehouse_qty_before > 0 THEN record.warehouse_qty_after - record.warehouse_qty_before ELSE 0 END AS entrada,
                   CASE WHEN record.warehouse_qty_before - record.warehouse_qty_after > 0 THEN record.warehouse_qty_before - record.warehouse_qty_after ELSE 0 END AS salida,
                   CASE WHEN record.transaction_type = 0 THEN record.warehouse_qty_after WHEN record.transaction_type = 1 THEN product_sales.qty WHEN record.transaction_type = 2 THEN product_purchases.qty WHEN record.transaction_type = 3 THEN product_returns.qty WHEN record.transaction_type = 4 THEN product_transfer.qty WHEN record.transaction_type = 5 THEN product_adjustments.qty WHEN record.transaction_type = 6 THEN purchase_product_return.qty ELSE NULL END AS qty,
                   CASE WHEN record.transaction_type = 0 THEN products.cost WHEN record.transaction_type = 1 THEN IF(record.cb_cost > 0, record.cb_cost, product_sales.cost) WHEN record.transaction_type = 2 THEN product_purchases.net_unit_cost WHEN record.transaction_type = 3 THEN products.cost WHEN record.transaction_type = 4 THEN product_transfer.net_unit_cost WHEN record.transaction_type = 5 THEN products.cost WHEN record.transaction_type = 6 THEN purchase_product_return.net_unit_cost ELSE NULL END AS cost,
                   CASE WHEN record.transaction_type = 0 THEN FORMAT(record.warehouse_qty_after * products.cost,2) WHEN record.transaction_type = 1 THEN FORMAT(product_sales.qty * product_sales.cost,2) WHEN record.transaction_type = 2 THEN FORMAT(product_purchases.qty * product_purchases.net_unit_cost,2) WHEN record.transaction_type = 3 THEN FORMAT(products.cost * product_returns.qty,2) WHEN record.transaction_type = 4 THEN FORMAT(product_transfer.qty * product_transfer.net_unit_cost,2) WHEN record.transaction_type = 5 THEN FORMAT(products.cost * product_adjustments.qty,2) WHEN record.transaction_type = 6 THEN FORMAT(purchase_product_return.qty * purchase_product_return.net_unit_cost,2) ELSE NULL END AS total_cost,
                   CASE WHEN record.transaction_type = 4 AND record.warehouse_qty_before < record.warehouse_qty_after THEN transfers.from_warehouse_id WHEN record.transaction_type = 4 AND record.warehouse_qty_before > record.warehouse_qty_after THEN transfers.to_warehouse_id ELSE NULL END AS from_to,
                   record.action_taken_at AS date
            FROM ((((((((((((((record JOIN products ON (products.id = record.product_id)) JOIN warehouses ON (warehouses.id = record.warehouse_id)) LEFT JOIN product_sales ON (record.transaction_type = 1 AND record.transaction_id = product_sales.sale_id AND record.product_id = product_sales.product_id)) LEFT JOIN product_purchases ON (record.transaction_type = 2 AND record.transaction_id = product_purchases.purchase_id AND record.product_id = product_purchases.product_id)) LEFT JOIN product_returns ON (record.transaction_type = 3 AND record.product_id = product_returns.product_id AND record.transaction_id = product_returns.return_id)) LEFT JOIN product_transfer ON (record.transaction_type = 4 AND record.transaction_id = product_transfer.transfer_id AND record.product_id = product_transfer.product_id)) LEFT JOIN product_adjustments ON (record.transaction_type = 5 AND record.transaction_id = product_adjustments.adjustment_id AND record.product_id = product_adjustments.product_id)) LEFT JOIN purchase_product_return ON (record.transaction_type = 6 AND record.transaction_id = purchase_product_return.return_id AND record.product_id = purchase_product_return.product_id)) LEFT JOIN sales ON (record.transaction_type = 1 AND record.transaction_id = sales.id)) LEFT JOIN purchases ON (record.transaction_type = 2 AND record.transaction_id = purchases.id)) LEFT JOIN returns ON (record.transaction_type = 3 AND record.transaction_id = returns.id)) LEFT JOIN transfers ON (record.transaction_type = 4 AND record.transaction_id = transfers.id)) LEFT JOIN adjustments ON (record.transaction_type = 5 AND record.transaction_id = adjustments.id)) LEFT JOIN return_purchases ON (record.transaction_type = 6 AND record.transaction_id = return_purchases.id))
        ");
    }

    public function down()
    {
        DB::unprepared('DROP VIEW IF EXISTS `kardex`;');
    }
}
