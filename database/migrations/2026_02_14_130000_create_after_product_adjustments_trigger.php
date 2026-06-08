<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAfterProductAdjustmentsTrigger extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `after_product_adjustments_trigger`;
CREATE TRIGGER `after_product_adjustments_trigger` BEFORE INSERT ON `product_adjustments` FOR EACH ROW
BEGIN
DECLARE trans_id INT;
DECLARE warehouse_id_val INT;
DECLARE product_qty_before INT;
DECLARE warehouse_qty_before INT;
DECLARE purchase_ref_no VARCHAR(50);
DECLARE product_type VARCHAR(50);
SELECT id, warehouse_id, reference_no INTO trans_id, warehouse_id_val, purchase_ref_no FROM adjustments WHERE id = NEW.adjustment_id;
SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id;
SELECT qty INTO warehouse_qty_before FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val;
IF NEW.action = '+' THEN
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before - NEW.qty), product_qty_before, warehouse_qty_before - NEW.qty, warehouse_qty_before);
ELSE
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before + NEW.qty), product_qty_before, (warehouse_qty_before + NEW.qty), warehouse_qty_before);
END IF;
END;
SQL
            );
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: create a function and trigger using PL/pgSQL
            DB::unprepared("DROP TRIGGER IF EXISTS after_product_adjustments_trigger ON product_adjustments;");
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION after_product_adjustments_trigger_fn()
RETURNS trigger AS $$
DECLARE
    trans_id integer;
    warehouse_id_val integer;
    product_qty_before integer;
    warehouse_qty_before integer;
    purchase_ref_no varchar(255);
    product_type varchar(50);
BEGIN
    SELECT id, warehouse_id, reference_no INTO trans_id, warehouse_id_val, purchase_ref_no FROM adjustments WHERE id = NEW.adjustment_id;
    SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id;
    SELECT qty INTO warehouse_qty_before FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val;
    IF NEW.action = '+' THEN
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before - NEW.qty), product_qty_before, warehouse_qty_before - NEW.qty, warehouse_qty_before);
    ELSE
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before + NEW.qty), product_qty_before, (warehouse_qty_before + NEW.qty), warehouse_qty_before);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_product_adjustments_trigger
BEFORE INSERT ON product_adjustments
FOR EACH ROW
EXECUTE FUNCTION after_product_adjustments_trigger_fn();
SQL
            );
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS `after_product_adjustments_trigger`;');
        } elseif ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS after_product_adjustments_trigger ON product_adjustments;');
            DB::unprepared('DROP FUNCTION IF EXISTS after_product_adjustments_trigger_fn();');
        }
    }
}
