<?php
$path = __DIR__ . '/../app/Http/Controllers/PurchaseController.php';
$content = file_get_contents($path);
$originalSize = strlen($content);

// Backup
$bak = $path . '.bak';
if (!file_exists($bak)) {
    copy($path, $bak);
    echo "Backup created: $bak\n";
} else {
    echo "Backup already exists: $bak\n";
}

$replacements = 0;

// Helper function
function do_replace(&$content, $patterns, $replacement = "", $label = "") {
    foreach ($patterns as $old) {
        if (strpos($content, $old) !== false) {
            $content = str_replace($old, $replacement, $content);
            echo "Replacement $label done\n";
            return true;
        }
    }
    echo "Replacement $label: NOT FOUND\n";
    return false;
}

// 1. Commented variant save in store()
do_replace($content, [
    "                    //\$lims_product_variant_data->save();\r\n",
    "                    //\$lims_product_variant_data->save();\n"
], "", "1");

// 2. Commented warehouse qty in store()
do_replace($content, [
    "                    // \$lims_product_warehouse_data->qty = \$lims_product_warehouse_data->qty + \$quantity;\r\n",
    "                    // \$lims_product_warehouse_data->qty = \$lims_product_warehouse_data->qty + \$quantity;\n"
], "", "2");

// 3. Commented tax block in importPurchase()
$found = false;
foreach (["\r\n", "\n"] as $nl) {
    $old = "                        /*if(strtolower(\$current_line[5]) != \"no tax\"){" . $nl
         . "                        \$tax[] = Tax::where('name', \$current_line[5])->first();" . $nl
         . "                        if(!\$tax[\$i-1])" . $nl
         . "                        return redirect()->back()->with('message', 'Tax name does not exist!');" . $nl
         . "                        }" . $nl
         . "                        else" . $nl
         . "                        \$tax[\$i-1]['rate'] = 0;" . $nl
         . "                        */" . $nl;
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, "", $content);
        echo "Replacement 3 done ($nl)\n";
        $found = true;
        $replacements++;
        break;
    }
}
if (!$found) echo "Replacement 3: NOT FOUND\n";

// 4. Commented return $request->all() in update()
do_replace($content, [
    "    {       //return \$request->all();\r\n",
    "    {       //return \$request->all();\n"
], "", "4");

// 5. Commented return dd in update()
do_replace($content, [
    "            //return dd(\$data);\r\n",
    "            //return dd(\$data);\n"
], "", "5");

// 6. Commented variant save in update()
do_replace($content, [
    "                    // \$lims_product_variant_data->save();\r\n",
    "                    // \$lims_product_variant_data->save();\n"
], "", "6");

// 7. Commented product qty in update()
do_replace($content, [
    "                //\$lims_product_data->qty += \$new_recieved_value;\r\n",
    "                //\$lims_product_data->qty += \$new_recieved_value;\n"
], "", "7");

// 8. Commented warehouse qty update in update()
do_replace($content, [
    "                    //\$lims_product_warehouse_data->qty += \$new_recieved_value;\r\n",
    "                    //\$lims_product_warehouse_data->qty += \$new_recieved_value;\n"
], "", "8");

// 9. Commented warehouse qty = new in update()
do_replace($content, [
    "                    //\$lims_product_warehouse_data->qty = \$new_recieved_value;\r\n",
    "                    //\$lims_product_warehouse_data->qty = \$new_recieved_value;\n"
], "", "9");

// 10. Commented warehouse qty in revertChanges()
do_replace($content, [
    "                //\$lims_product_warehouse_data->qty += \$old_recieved_value;\r\n",
    "                //\$lims_product_warehouse_data->qty += \$old_recieved_value;\n"
], "", "10");

// 11. Commented warehouse qty = old in revertChanges()
do_replace($content, [
    "                //\$lims_product_warehouse_data->qty = \$old_recieved_value;\r\n",
    "                //\$lims_product_warehouse_data->qty = \$old_recieved_value;\n"
], "", "11");

// 12. Stripe Charge block in addPayment() - REPLACE with uniqid line
$found = false;
foreach (["\r\n", "\n"] as $nl) {
    $old = "            /*\$charge = \\Stripe\\Charge::create([" . $nl
         . "            'amount' => \$amount * 100," . $nl
         . "            'currency' => 'usd'," . $nl
         . "            'source' => \$token," . $nl
         . "            ]);" . $nl
         . "            \$data['charge_id'] = \$charge->id;*/" . $nl;
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, "            \$data['charge_id'] = uniqid();" . $nl, $content);
        echo "Replacement 12 done ($nl)\n";
        $found = true;
        $replacements++;
        break;
    }
}
if (!$found) echo "Replacement 12: NOT FOUND\n";

// 13. Stripe Refund block in updatePayment() (6 lines)
$found = false;
foreach (["\r\n", "\n"] as $nl) {
    $old = "                /*\\Stripe\\Refund::create(array(" . $nl
         . "                \"charge\" => \$lims_payment_with_credit_card_data->charge_id," . $nl
         . "                ));" . $nl
         . "                \$charge = \\Stripe\\Charge::create([" . $nl
         . "                'amount' => \$amount * 100," . $nl
         . "                'currency' => 'usd'," . $nl
         . "                'source' => \$token," . $nl
         . "                ]);*/" . $nl;
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, "", $content);
        echo "Replacement 13 done ($nl)\n";
        $found = true;
        $replacements++;
        break;
    }
}
if (!$found) echo "Replacement 13: NOT FOUND\n";

// 14. Commented charge_id assignment
do_replace($content, [
    "                //\$lims_payment_with_credit_card_data->charge_id = \$charge->id;\r\n",
    "                //\$lims_payment_with_credit_card_data->charge_id = \$charge->id;\n"
], "", "14");

// 15. Second Stripe Charge block in updatePayment() - REPLACE with uniqid line
$found = false;
foreach (["\r\n", "\n"] as $nl) {
    $old = "                /*\$charge = \\Stripe\\Charge::create([" . $nl
         . "                'amount' => \$amount * 100," . $nl
         . "                'currency' => 'usd'," . $nl
         . "                'source' => \$token," . $nl
         . "                ]);" . $nl
         . "                \$data['charge_id'] = \$charge->id;*/" . $nl;
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, "                \$data['charge_id'] = uniqid();" . $nl, $content);
        echo "Replacement 15 done ($nl)\n";
        $found = true;
        $replacements++;
        break;
    }
}
if (!$found) echo "Replacement 15: NOT FOUND\n";

// Clean up method signature
$content = preg_replace('/\{\s+try/', "{\n        try", $content, 1);

// Clean up excessive blank lines
$content = preg_replace('/\n{4,}/', "\n\n", $content);

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
