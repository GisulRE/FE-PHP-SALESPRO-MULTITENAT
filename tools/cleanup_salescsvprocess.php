<?php
$path = __DIR__ . '/../app/Jobs/SalesCsvProcess.php';
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

// 1. Remove commented data_siat assignment (first occurrence - line ~147)
$old = "                            // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$data_row['detalleOtrosPagosNoSujetoIva'];\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 1 done\n";
    $replacements++;
} else {
    $old2 = "                            // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$data_row['detalleOtrosPagosNoSujetoIva'];\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 1 done\n";
        $replacements++;
    } else {
        echo "Replacement 1: NOT FOUND\n";
    }
}

// 2. Remove commented data_siat assignment (second occurrence - line ~234)
$old = "                            // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$data_row['detalleOtrosPagosNoSujetoIva'];\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 2 done\n";
    $replacements++;
} else {
    $old2 = "                            // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$data_row['detalleOtrosPagosNoSujetoIva'];\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 2 done\n";
        $replacements++;
    } else {
        echo "Replacement 2: NOT FOUND\n";
    }
}

// 3. Remove commented $total = 0
$old = "        //\$total = 0;\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "        //\$total = 0;\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 3 done\n";
    $replacements++;
} else {
    echo "Replacement 3: NOT FOUND\n";
}

// 4. Remove commented $total calculation
$old = "            //\$total =  \$total + \$value['total'];\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "            //\$total =  \$total + \$value['total'];\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 4 done\n";
    $replacements++;
} else {
    echo "Replacement 4: NOT FOUND\n";
}

// 5. Remove commented block with grand_total/log (7 lines)
$old = "        /*\$lims_sale_data->grand_total = \$total;\r\n        \$lims_sale_data->total_price = \$total;\r\n        \$lims_sale_data->paid_amount = \$total;\r\n        \$lims_sale_data->save();\r\n        log::info(\"Venta Masiva Nro: \".\$lims_sale_data->reference_no);\r\n        log::info(\$dataHead);\r\n        log::info(\$dataDetails);*/\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "        /*\$lims_sale_data->grand_total = \$total;\n        \$lims_sale_data->total_price = \$total;\n        \$lims_sale_data->paid_amount = \$total;\n        \$lims_sale_data->save();\n        log::info(\"Venta Masiva Nro: \".\$lims_sale_data->reference_no);\n        log::info(\$dataHead);\n        log::info(\$dataDetails);*/\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 5 done\n";
    $replacements++;
} else {
    echo "Replacement 5: NOT FOUND\n";
}

// 6. Remove commented Unit::firstOrCreate
$old = "        // \$lims_unit_data = Unit::firstOrCreate(['unit_code' => \$data['unitcode'], 'is_active' => true]);\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "        // \$lims_unit_data = Unit::firstOrCreate(['unit_code' => \$data['unitcode'], 'is_active' => true]);\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 6 done\n";
    $replacements++;
} else {
    echo "Replacement 6: NOT FOUND\n";
}

// 7. Remove commented obj_cliente assignment
$old = "            // \$obj_cliente->detalle_otros_pagos_no_sujeto_iva = \$data['detalle_otros_pagos_no_sujeto_iva'];\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "            // \$obj_cliente->detalle_otros_pagos_no_sujeto_iva = \$data['detalle_otros_pagos_no_sujeto_iva'];\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 7 done\n";
    $replacements++;
} else {
    echo "Replacement 7: NOT FOUND\n";
}

// 8. Remove commented str_replace in formatNumber
$old = "        //(float) \$number = str_replace('.', '', \$number);\r\n";
$found = false;
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    $found = true;
} else {
    $old2 = "        //(float) \$number = str_replace('.', '', \$number);\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        $found = true;
    }
}
if ($found) {
    echo "Replacement 8 done\n";
    $replacements++;
} else {
    echo "Replacement 8: NOT FOUND\n";
}

// Clean up excessive blank lines
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
