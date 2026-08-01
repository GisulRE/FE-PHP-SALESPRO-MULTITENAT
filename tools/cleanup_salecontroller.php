<?php
$path = __DIR__ . '/../app/Http/Controllers/SaleController.php';
$content = file_get_contents($path);
$originalSize = strlen($content);

// Backup
copy($path, $path . '.bak');
echo "Backup created: " . $path . ".bak\n";

$replacements = 0;

// 1. Remove commented join line in saleData()
$old = "                    //->join('customer_sales', 'sales.id', '=', 'customer_sales.sale_id')\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 1 done\n";
    $replacements++;
}

// 2. Remove block comment orWhere in saleData()
$old = "                    /*->orwhere([\n                        ['customer_sales.codigofijo', \$search],\n                        ['sales.user_id', Auth::id()],\n                    ])\n                    ->orwhere([\n                        ['customer_sales.numero_medidor', 'LIKE', \"%{\$search}%\"],\n                        ['sales.user_id', Auth::id()],\n                    ])*/\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 2 done\n";
    $replacements++;
}

// 3. Remove commented delete call in store()
$old = "                //\$lims_sale_data->delete();\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 3 done\n";
    $replacements++;
}

// 4. Remove dead if block with commented printPre_Order (3 lines)
$old = "            if (\$lims_pos_setting_data->print_order != null || \$lims_pos_setting_data->print_order != 0) {\n                //\$this->printPre_Order(\$lims_pos_setting_data->print_order, \$lims_sale_data->id);\n            }\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 4 done\n";
    $replacements++;
}

// 5. Remove commented file_put_contents in printPre_Order
$old = "            //file_put_contents(\"order.txt\", \$strprint);\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 5 done\n";
    $replacements++;
}

// 6. Remove commented fopen/fwrite/fclose block (3 lines)
$old = "            //\$file = fopen(\"order.txt\", \"w\") or die(\"Unable to open file!\");\n            //fwrite(\$file, \$strprint);\n            //fclose(\$file);\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 6 done\n";
    $replacements++;
}

// 7. Remove commented fopen test.txt
$old = "            //\$file = fopen(\"test.txt\",\"r\");\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 7 done\n";
    $replacements++;
}

// 8. Remove printer_open try-catch block comment (10 lines)
$old = "            /*try {\n            \$enlace=printer_open(\$printer_name);\n            printer_set_option(\$enlace, PRINTER_MODE, \"RAW\");\n            printer_write(\$enlace, \$strprint);\n            printer_close(\$enlace);\n            return json_encode(true);\n            }\n            catch(Exception \$e) {\n            \$arr['message'] = 'Mensaje: ' .\$e->getMessage();\n            return json_encode(\$arr);\n            }*/\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 8 done\n";
    $replacements++;
}

// 9. Remove commented fread block in try (5 lines)
$old = "                ///\$filer = fopen(\$file_path, \"r\") or die(\"Unable to open file!\");\n                //\$data = fread(\$filer,filesize(\$file_path));\n                //fclose(\$filer);\n                //\$printer_name = \"//JCCM-17/\".\$printer_name;\n                //copy(\$file_path, \$printer_name);\n                //exec(\"copy \$file_path \\\\\\\\\\\\JCCM-17\\\\\\\\Virtual Print Test\");\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 9 done\n";
    $replacements++;
}

// 10. Remove WindowsPrintConnector block comment (7 lines)
$old = "                /*\$connector = new WindowsPrintConnector(\"smb://JCCM-17/\".\$printer_name);\n                \$printer_server = new Printer(\$connector);\n                \$printer_server->text(\"hello world\");\n                \$printer_server->cut();\n                \$printer_server->close();*/\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 10 done\n";
    $replacements++;
}

// 11. Remove pfsockopen block comment (9 lines)
$old = "            /*try{\n            \$fp=pfsockopen(\"192.168.1.109\",9100);\n            fputs(\$fp,\$strprint);\n            fclose(\$fp);\n            return json_encode(true);\n            }catch (Exception \$e) {\n            \$arr['message'] = 'Mensaje: ' .\$e->getMessage();\n            return json_encode(\$arr);\n            }*/\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 11 done\n";
    $replacements++;
}

// 12. Remove setExpressCheckout PayPal lines (2 occurrences in different methods)
$content = preg_replace(
    '/\n\s*\/\/\$provider = new ExpressCheckout;\n\s*\/\/\$response = \$provider->setExpressCheckout\(\$paypal_data\);\n/',
    "\n",
    $content,
    -1,
    $count
);
echo "Replacement 12 done ($count occurrences)\n";
$replacements += $count;

// 13. Remove remaining PayPal commented lines (getExpressCheckoutDetails, doExpressCheckoutPayment, transaction_id)
$content = preg_replace('/^\s*\/\/\$response = \$provider->getExpressCheckoutDetails\(\$token\);\s*$/m', '', $content, -1, $c1);
$content = preg_replace('/^\s*\/\/\$response = \$provider->doExpressCheckoutPayment\(\$paypal_data, \$token, \$payerID\);\s*$/m', '', $content, -1, $c2);
$content = preg_replace('/^\s*\/\/\$data\[\'transaction_id\'\] = \$response\[\'PAYMENTINFO_0_TRANSACTIONID\'\];\s*$/m', '', $content, -1, $c3);
echo "Replacement 13 done (getDetails: $c1, doPayment: $c2, transId: $c3)\n";
$replacements += ($c1 + $c2 + $c3);

// 14. Remove commented union queries
$old1 = "        //\$query1->union(\$query->toBase())->groupBy('id', 'code',  'name')->orderBy('name', 'ASC')->limit(100);\n";
$old2 = "        //\$list_products_all = \$query->union(\$query1)->get();\n";
if (strpos($content, $old1) !== false) {
    $content = str_replace($old1, "", $content);
    echo "Replacement 14a done\n";
    $replacements++;
}
if (strpos($content, $old2) !== false) {
    $content = str_replace($old2, "", $content);
    echo "Replacement 14b done\n";
    $replacements++;
}

// 15. Remove "Unir" comment
$old = "        // Unir \$list_products_all con \$lims_products\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 15 done\n";
    $replacements++;
}

// 16. Remove commented return $unit;
$old = "        //return \$unit;\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 16 done\n";
    $replacements++;
}

// 17. Remove commented return dd;
$old = "        //return dd(\$data);\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 17 done\n";
    $replacements++;
}

// 18. Remove PayPal refund commented code
$old = "                //\$provider = new ExpressCheckout;\n                //\$response = \$provider->refundTransaction(\$lims_payment_paypal_data->transaction_id);\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 18 done\n";
    $replacements++;
}

// 19. Remove commented Unit::find calls (2 occurrences)
$old = "                //\$lims_sale_unit_data = Unit::find(\$product_sale->sale_unit_id);\n";
$count = 0;
$content = str_replace($old, "", $content, $count);
if ($count > 0) {
    echo "Replacement 19 done ($count occurrences)\n";
    $replacements++;
}

// 20. Remove commented HTML option (2 lines)
$old = "                    // <option value=\"{{ \$sucursal->sucursal }}\">{{ \$sucursal->sucursal}}.- {{ \$sucursal->nombre}} | {{ \$sucursal->direccion }}</option>\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 20a done\n";
    $replacements++;
}
$old = "                // '<option value=\"'+ data[i].codigo_punto_venta +'\">'+ data[i].codigo_punto_venta +' - '+data[i].nombre_punto_venta +'</option>'\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 20b done\n";
    $replacements++;
}

// 21. Remove commented Log::info calls
$old1 = "            //Log::info(\"Purchase get Cost id:\" . \$item->purchase_id);\n";
$old2 = "            //Log::info(\"Product get Cost id:\" . \$producto->id);\n";
if (strpos($content, $old1) !== false) {
    $content = str_replace($old1, "", $content);
    echo "Replacement 21a done\n";
    $replacements++;
}
if (strpos($content, $old2) !== false) {
    $content = str_replace($old2, "", $content);
    echo "Replacement 21b done\n";
    $replacements++;
}

// 22. Remove empty blank lines that might be left from replacements
$content = preg_replace('/\n{3,}/', "\n\n", $content);

// Write the file
file_put_contents($path, $content);
$newSize = strlen($content);
$reduction = $originalSize - $newSize;

echo "\n=== SUMMARY ===\n";
echo "Total replacements: $replacements\n";
echo "Original size: $originalSize bytes\n";
echo "New size: $newSize bytes\n";
echo "Reduction: $reduction bytes (" . round($reduction / $originalSize * 100, 2) . "%)\n";
echo "Done!\n";
