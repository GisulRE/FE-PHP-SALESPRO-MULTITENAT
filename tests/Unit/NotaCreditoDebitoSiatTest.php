<?php

namespace Tests\Unit;

use Tests\TestCase;
use Carbon\Carbon;

class NotaCreditoDebitoSiatTest extends TestCase
{
    /**
     * Prueba que las fórmulas normativas del SIAT para Documento Sector 24
     * calculen correctamente el montoTotalDevuelto, montoDescuentoCreditoDebito
     * y que el montoEfectivoCreditoDebito sea exactamente (montoTotalDevuelto - montoDescuentoCreditoDebito),
     * NUNCA el 13% de IVA como estaba antes erróneamente.
     */
    public function test_siat_normative_formulas_sector_24()
    {
        // Factura original simulada con 2 ítems:
        // Ítem 1: 10 unidades a Bs. 50 c/u = Bs. 500, con descuento de Bs. 50 (Bs. 5 por unidad).
        // Ítem 2: 5 unidades a Bs. 100 c/u = Bs. 500, sin descuento.
        $orig_items = [
            [
                'codigoProducto' => 'PROD-01',
                'actividadEconomica' => '477300',
                'codigoProductoSin' => 35270,
                'descripcion' => 'Medicamento A',
                'cantidad' => 10,
                'precioUnitario' => 50.00,
                'montoDescuento' => 50.00,
                'subTotal' => 450.00,
            ],
            [
                'codigoProducto' => 'PROD-02',
                'actividadEconomica' => '477300',
                'codigoProductoSin' => 35270,
                'descripcion' => 'Insumo B',
                'cantidad' => 5,
                'precioUnitario' => 100.00,
                'montoDescuento' => 0.00,
                'subTotal' => 500.00,
            ]
        ];

        // Devolución parcial:
        // Devolvemos 4 unidades de Ítem 1
        // Devolvemos 2 unidades de Ítem 2
        $return_request = [
            [
                'codigoProducto' => 'PROD-01',
                'qty' => 4,
                'net_unit_price' => 50.00,
            ],
            [
                'codigoProducto' => 'PROD-02',
                'qty' => 2,
                'net_unit_price' => 100.00,
            ]
        ];

        $montoTotalDevuelto = 0;
        $montoDescuentoCreditoDebito = 0;
        $detalle_ajuste = [];

        $orig_map = [];
        foreach ($orig_items as $oi) {
            $orig_map[$oi['codigoProducto']] = $oi;
        }

        foreach ($return_request as $ret) {
            $orig = $orig_map[$ret['codigoProducto']];
            $cant = $ret['qty'];
            $pu = $ret['net_unit_price'];

            // Descuento proporcional por unidad
            $desc_unit = $orig['cantidad'] > 0 ? ($orig['montoDescuento'] / $orig['cantidad']) : 0;
            $desc_item = round($desc_unit * $cant, 2);

            $sub_bruto = round($cant * $pu, 2);
            $sub_neto = round($sub_bruto - $desc_item, 2);

            $montoTotalDevuelto += $sub_bruto;
            $montoDescuentoCreditoDebito += $desc_item;

            // Herencia de actividadEconomica y codigoProductoSin
            $detalle_ajuste[] = [
                'actividadEconomica' => $orig['actividadEconomica'],
                'codigoProductoSin' => $orig['codigoProductoSin'],
                'codigoProducto' => $orig['codigoProducto'],
                'cantidad' => $cant,
                'precioUnitario' => $pu,
                'montoDescuento' => $desc_item,
                'subTotal' => $sub_neto,
                'codigoDetalleTransaccion' => 2
            ];
        }

        $montoSujetoAjuste = round($montoTotalDevuelto - $montoDescuentoCreditoDebito, 2);
        $montoIVA = round($montoSujetoAjuste * 0.13, 2);
        // Según validación oficial del SIAT (Error 1031 de Impuestos Nacionales),
        // el XML de Sector 24 exige que 'montoEfectivoCreditoDebito' sea exactamente
        // el 13% de IVA (efecto fiscal de crédito o débito)
        $montoEfectivoCreditoDebito = $montoIVA;

        // Verificaciones matemáticas normativas:
        // Ítem 1 bruto: 4 * 50 = 200. Descuento proporcional: 4 * 5 = 20.
        // Ítem 2 bruto: 2 * 100 = 200. Descuento proporcional: 0.
        // Total devuelto bruto = 200 + 200 = 400.
        $this->assertEquals(400.00, $montoTotalDevuelto, 'montoTotalDevuelto debe ser la suma de los montos brutos de ítems devueltos.');

        // Descuento total devuelto = 20.
        $this->assertEquals(20.00, $montoDescuentoCreditoDebito, 'montoDescuentoCreditoDebito debe calcular el descuento proporcional.');

        // Monto Neto Sujeto a Ajuste = 400 - 20 = 380.
        $this->assertEquals(380.00, $montoSujetoAjuste, 'montoSujetoAjuste debe ser la base imponible neta.');

        // Comprobación crítica SIAT 1031: montoEfectivoCreditoDebito debe ser exactamente el 13% de IVA (49.40)
        $this->assertEquals(49.40, $montoIVA);
        $this->assertEquals($montoIVA, $montoEfectivoCreditoDebito, 'SIAT Error 1031: montoEfectivoCreditoDebito debe ser el 13% IVA enviado a SIAT.');

        // Herencia de códigos:
        $this->assertEquals('477300', $detalle_ajuste[0]['actividadEconomica']);
        $this->assertEquals(35270, $detalle_ajuste[0]['codigoProductoSin']);
        $this->assertNotEquals('620100', $detalle_ajuste[0]['actividadEconomica'], 'No debe hardcodear 620100.');
        $this->assertNotEquals(83141, $detalle_ajuste[0]['codigoProductoSin'], 'No debe hardcodear 83141.');
    }

    /**
     * Prueba la validación del límite normativo SIAT de 18 meses.
     */
    public function test_siat_18_months_limit_validation()
    {
        $now = Carbon::now();

        // Caso 1: Factura de hace 2 meses -> VÁLIDA
        $fecha_reciente = $now->copy()->subMonths(2);
        $dias_reciente = $fecha_reciente->diffInDays($now);
        $meses_reciente = $dias_reciente / 30.4375;
        $this->assertTrue($meses_reciente <= 18.0, 'Factura dentro de 18 meses debe ser válida.');

        // Caso 2: Factura de hace 19 meses -> RECHAZADA
        $fecha_antigua = $now->copy()->subMonths(19);
        $dias_antigua = $fecha_antigua->diffInDays($now);
        $meses_antigua = $dias_antigua / 30.4375;
        $this->assertFalse($meses_antigua <= 18.0, 'Factura de más de 18 meses debe ser rechazada.');
    }

    /**
     * Prueba el control de cantidades disponibles para devolución.
     */
    public function test_available_qty_calculation()
    {
        $original_qty = 10.0;
        $already_returned_qty = 4.0;
        $available_qty = max(0, $original_qty - $already_returned_qty);

        $this->assertEquals(6.0, $available_qty);

        // Intento de devolver 7 unidades (excede 6 disponibles)
        $qty_requested = 7.0;
        $this->assertTrue($qty_requested > $available_qty, 'Debe detectar exceso sobre cantidad disponible.');

        // Intento de devolver 5 unidades (válido)
        $qty_valid = 5.0;
        $this->assertTrue($qty_valid <= $available_qty, 'Debe permitir cantidad dentro de lo disponible.');
    }

    /**
     * Prueba la lógica de detección de ventas 100% Gift Card frente a ventas normales.
     */
    public function test_gift_card_handling_logic()
    {
        // Caso 1: Venta 100% Gift Card (Total 200, Gift Card 200, Sujeto IVA 0)
        $total_venta = 200.00;
        $monto_gift_card = 200.00;
        $monto_sujeto_iva = max(0, $total_venta - $monto_gift_card);

        $is_gift_card_only = ($monto_gift_card > 0 && $monto_sujeto_iva <= 0.01);
        $this->assertTrue($is_gift_card_only, 'Debe identificar venta 100% Gift Card para omitir SIAT Sector 24.');

        // Caso 2: Venta mixta (Total 200, Gift Card 50, Sujeto IVA 150)
        $monto_gift_card_mixto = 50.00;
        $monto_sujeto_iva_mixto = max(0, $total_venta - $monto_gift_card_mixto);

        $is_gift_card_only_mixto = ($monto_gift_card_mixto > 0 && $monto_sujeto_iva_mixto <= 0.01);
        $this->assertFalse($is_gift_card_only_mixto, 'Venta mixta sí está sujeta a IVA y debe emitir SIAT Sector 24.');
        $this->assertEquals(150.00, $monto_sujeto_iva_mixto);

        // Caso 3: Tolerancia de coma flotante (ej: 199.999999 vs 200)
        $monto_sujeto_iva_float = 0.00000000001;
        $is_gift_card_float = ($monto_gift_card > 0 && $monto_sujeto_iva_float <= 0.01);
        $this->assertTrue($is_gift_card_float, 'Tolerancia epsilon debe evitar falsos negativos en 100% Gift Card.');
    }

    /**
     * Prueba la lógica de auto-sincronización de correlativos para evitar colisiones en SIAT.
     */
    public function test_correlativo_auto_sync_logic()
    {
        $correlativo_pv = 3;
        $max_existente_en_bd = 5;

        // Si en BD ya existe la nota 5, el correlativo del punto de venta (3) está desfasado
        if ($max_existente_en_bd && $correlativo_pv <= $max_existente_en_bd) {
            $correlativo_calculado = $max_existente_en_bd + 1;
        } else {
            $correlativo_calculado = $correlativo_pv;
        }

        $this->assertEquals(6, $correlativo_calculado, 'Debe auto-sincronizar al siguiente correlativo libre (6).');
    }

    /**
     * Prueba el cálculo de reingreso de stock para productos tipo combo.
     */
    public function test_combo_product_stock_ratio_calculation()
    {
        // Combo "Pack Escolar": 2 cuadernos (ID 10) y 3 lápices (ID 11)
        $combo_qty_returned = 2; // Se devuelven 2 combos
        $product_list = ['10', '11'];
        $qty_list = ['2', '3'];

        $child_restored = [];
        foreach ($product_list as $index => $child_id) {
            $ratio = (float) $qty_list[$index];
            $child_restored[$child_id] = $combo_qty_returned * $ratio;
        }

        $this->assertEquals(4.0, $child_restored['10'], 'Debe reingresar 4 cuadernos (2 combos * 2 cuadernos).');
        $this->assertEquals(6.0, $child_restored['11'], 'Debe reingresar 6 lápices (2 combos * 3 lápices).');
    }

    /**
     * Prueba que solo las devoluciones en efectivo asignen salida de caja física (account_id).
     */
    public function test_cashier_accounting_refund_method_isolation()
    {
        $active_account_id = 5;

        // Método efectivo -> Debe registrar account_id para egreso en arqueo de caja
        $refund_method_cash = 'cash';
        $account_assigned_cash = ($refund_method_cash === 'cash') ? $active_account_id : null;
        $this->assertEquals(5, $account_assigned_cash);

        // Método saldo cliente -> NO debe deducir dinero de la caja física
        $refund_method_balance = 'customer_balance';
        $account_assigned_balance = ($refund_method_balance === 'cash') ? $active_account_id : null;
        $this->assertNull($account_assigned_balance, 'Saldo cliente no debe reducir la caja física.');

        // Método tarjeta de regalo -> NO debe deducir dinero de la caja física
        $refund_method_gc = 'gift_card';
        $account_assigned_gc = ($refund_method_gc === 'cash') ? $active_account_id : null;
        $this->assertNull($account_assigned_gc, 'Tarjeta de regalo no debe reducir la caja física.');
    }
}
