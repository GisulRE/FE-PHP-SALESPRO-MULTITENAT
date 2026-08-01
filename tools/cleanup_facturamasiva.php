<?php
$path = __DIR__ . '/../app/Http/Controllers/FacturaMasivaController.php';
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

// 1. Remove commented data_siat assignment
$old = "                    // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$sheet->detalleOtrosPagosNoSujetoIva;\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 1 done\n";
    $replacements++;
} else {
    // Try with \n only
    $old2 = "                    // \$data_siat['detalle_otros_pagos_no_sujeto_iva'] = \$sheet->detalleOtrosPagosNoSujetoIva;\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 1 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 1: NOT FOUND\n";
    }
}

// 2. Remove block comment in create_customersale
$old = "        /*if (\$obj_cliente->tipo_documento == 5) {\r\n            \$result = \$customer->verificarNIT(\$obj_cliente->valor_documento);\r\n            if (isset(\$result['status']) && \$result['status'] == false) {\r\n                \$codigo_excepcion = 0;\r\n            } else {\r\n                if (\$result != null && \$result['codigo'] == 994) {\r\n                    \$codigo_excepcion = 1;\r\n                } else {\r\n                    \$codigo_excepcion = 0;\r\n                }\r\n            }\r\n\r\n        }*/\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 2 done\n";
    $replacements++;
} else {
    // Try with \n only
    $old2 = "        /*if (\$obj_cliente->tipo_documento == 5) {\n            \$result = \$customer->verificarNIT(\$obj_cliente->valor_documento);\n            if (isset(\$result['status']) && \$result['status'] == false) {\n                \$codigo_excepcion = 0;\n            } else {\n                if (\$result != null && \$result['codigo'] == 994) {\n                    \$codigo_excepcion = 1;\n                } else {\n                    \$codigo_excepcion = 0;\n                }\n            }\n\n        }*/\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 2 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 2: NOT FOUND\n";
    }
}

// 3. Remove commented Unit::firstOrCreate
$old = "        // \$lims_unit_data = Unit::firstOrCreate(['unit_code' => \$data['unitcode'], 'is_active' => true]);\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 3 done\n";
    $replacements++;
} else {
    $old2 = "        // \$lims_unit_data = Unit::firstOrCreate(['unit_code' => \$data['unitcode'], 'is_active' => true]);\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 3 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 3: NOT FOUND\n";
    }
}

// 4. Remove commented 'Hola mundo'
$old = "        // return 'Hola mundo';\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 4 done\n";
    $replacements++;
} else {
    $old2 = "        // return 'Hola mundo';\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 4 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 4: NOT FOUND\n";
    }
}

// 5. Remove commented str_replace
$old = "        //(float) \$number = str_replace('.', '', \$number);\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 5 done\n";
    $replacements++;
} else {
    $old2 = "        //(float) \$number = str_replace('.', '', \$number);\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 5 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 5: NOT FOUND\n";
    }
}

// 6. Remove commented obtenerVentasEnviadasEnPaquetesModoMasivo
$old = "            // \$this->obtenerVentasEnviadasEnPaquetesModoMasivo(\$factura_masiva_paquete_id); // SiatTrait\r\n";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, "", $content);
    echo "Replacement 6 done\n";
    $replacements++;
} else {
    $old2 = "            // \$this->obtenerVentasEnviadasEnPaquetesModoMasivo(\$factura_masiva_paquete_id); // SiatTrait\n";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, "", $content);
        echo "Replacement 6 done (\\n)\n";
        $replacements++;
    } else {
        echo "Replacement 6: NOT FOUND\n";
    }
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
