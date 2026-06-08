<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAfterProductTransferTrigger extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `AFTER_PRODUCT_TRANSFER_TRIGGER`;
CREATE TRIGGER `AFTER_PRODUCT_TRANSFER_TRIGGER` AFTER INSERT ON `product_transfer` FOR EACH ROW
BEGIN
DECLARE trans_id INT;
DECLARE warehouse_id_f INT;
DECLARE warehouse_id_t INT;
DECLARE product_qty_before INT;
DECLARE warehouse_qty_before_f INT;
DECLARE warehouse_qty_before_t INT;
DECLARE reference_no_v VARCHAR(50);
DECLARE product_type VARCHAR(50);
SELECT id, from_warehouse_id, to_warehouse_id, reference_no INTO trans_id, warehouse_id_f, warehouse_id_t, reference_no_v FROM transfers WHERE id = NEW.transfer_id;
SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id;
SELECT qty INTO warehouse_qty_before_f FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_f;
SELECT qty INTO warehouse_qty_before_t FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_t;
IF NEW.variant_id IS NOT NULL THEN
    SELECT qty INTO product_qty_before FROM product_variants WHERE id = NEW.variant_id;
END IF;
IF product_type = 'digital' THEN
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_f, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, warehouse_qty_before_f, warehouse_qty_before_f);
ELSE
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_f, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (warehouse_qty_before_f + NEW.qty), warehouse_qty_before_f), (trans_id, warehouse_id_t, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (warehouse_qty_before_t - NEW.qty), warehouse_qty_before_t);
END IF;
END;
SQL
            );
        } elseif ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS "AFTER_PRODUCT_TRANSFER_TRIGGER" ON product_transfer;');
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION after_product_transfer_trigger_fn()
RETURNS trigger AS $$
DECLARE
    trans_id integer;
    warehouse_id_f integer;
    warehouse_id_t integer;
    product_qty_before integer;
    warehouse_qty_before_f integer;
    warehouse_qty_before_t integer;
    reference_no_v varchar(50);
    product_type varchar(50);
BEGIN
    SELECT id, from_warehouse_id, to_warehouse_id, reference_no INTO trans_id, warehouse_id_f, warehouse_id_t, reference_no_v FROM transfers WHERE id = NEW.transfer_id;
    SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id;
    SELECT qty INTO warehouse_qty_before_f FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_f;
    SELECT qty INTO warehouse_qty_before_t FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_t;
    IF NEW.variant_id IS NOT NULL THEN
        SELECT qty INTO product_qty_before FROM product_variants WHERE id = NEW.variant_id;
    END IF;
    IF product_type = 'digital' THEN
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES (trans_id, warehouse_id_f, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, warehouse_qty_before_f, warehouse_qty_before_f);
    ELSE
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES
            (trans_id, warehouse_id_f, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (warehouse_qty_before_f + NEW.qty), warehouse_qty_before_f),
            (trans_id, warehouse_id_t, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (warehouse_qty_before_t - NEW.qty), warehouse_qty_before_t);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER "AFTER_PRODUCT_TRANSFER_TRIGGER"
AFTER INSERT ON product_transfer
FOR EACH ROW
EXECUTE FUNCTION after_product_transfer_trigger_fn();
SQL
            );
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS `AFTER_PRODUCT_TRANSFER_TRIGGER`;');
        } elseif ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS "AFTER_PRODUCT_TRANSFER_TRIGGER" ON product_transfer;');
            DB::unprepared('DROP FUNCTION IF EXISTS after_product_transfer_trigger_fn();');
        }
    }
}
