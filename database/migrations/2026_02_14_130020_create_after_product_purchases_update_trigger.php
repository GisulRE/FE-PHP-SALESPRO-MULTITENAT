<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAfterProductPurchasesUpdateTrigger extends Migration
{
    public function up()
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `AFTER_PRODUCT_PURCHASES_UPDATE_TRIGGER`;
CREATE TRIGGER `AFTER_PRODUCT_PURCHASES_UPDATE_TRIGGER` AFTER UPDATE ON `product_purchases` FOR EACH ROW
BEGIN
DECLARE trans_id INT;
DECLARE warehouse_id_val INT;
DECLARE product_qty_before INT;
DECLARE warehouse_qty_before INT;
DECLARE purchase_ref_no VARCHAR(50);
DECLARE product_type VARCHAR(50);
DECLARE p_status INT;
DECLARE prod_warehouse_id INT;
SELECT id, warehouse_id, reference_no, status INTO trans_id, warehouse_id_val, purchase_ref_no, p_status FROM purchases WHERE id = NEW.purchase_id FOR UPDATE;
SELECT id INTO prod_warehouse_id FROM product_warehouse WHERE product_warehouse.product_id = NEW.product_id AND product_warehouse.warehouse_id = warehouse_id_val;
IF prod_warehouse_id IS NULL THEN
    INSERT INTO product_warehouse (product_id, warehouse_id, qty) VALUES (NEW.product_id, warehouse_id_val, 0);
END IF;
IF ABS(NEW.recieved - OLD.recieved) > 0 THEN
    SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id FOR UPDATE;
    SELECT qty INTO warehouse_qty_before FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val FOR UPDATE;
    IF NEW.variant_id IS NOT NULL THEN
        SELECT qty INTO product_qty_before FROM product_variants WHERE id = NEW.variant_id FOR UPDATE;
        IF product_type <> 'digital' THEN
            UPDATE product_variants SET qty = qty + ABS(NEW.recieved - OLD.recieved) WHERE id = NEW.variant_id;
        END IF;
    ELSE
        IF product_type <> 'digital' THEN
            UPDATE products SET qty = qty + ABS(NEW.recieved - OLD.recieved) WHERE id = NEW.product_id;
        END IF;
    END IF;
    UPDATE product_warehouse SET qty = qty + ABS(NEW.recieved - OLD.recieved) WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val;
    IF product_type = 'digital' THEN
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before);
    ELSE
        INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
        VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, (product_qty_before + ABS(NEW.recieved - OLD.recieved)), warehouse_qty_before, (warehouse_qty_before + ABS(NEW.recieved - OLD.recieved)));
    END IF;
END IF;
END;
SQL
        );
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `AFTER_PRODUCT_PURCHASES_UPDATE_TRIGGER`;');
    }
}
