<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
//Route::post('login', 'Auth\LoginController@credentials');
/** disabled route register */
Route::get('/register', function () {
	return redirect('/login');
});

Route::post('/register', function () {
	return redirect('/login');
});
/** disabled route register */

Route::group(['middleware' => 'web'], function () {
	Route::get('/dashboard', 'HomeController@index');
});

Route::group(['middleware' => ['auth', 'active']], function () {

	// Ruta de prueba: setea muchas notificaciones de ejemplo y redirige a home
	Route::get('/test-notifications', function () {
		$sampleTransfers = [];
		// Generar 25 transferencias de ejemplo
		for ($i = 0; $i < 25; $i++) {
			$sampleTransfers[] = (object) [
				'id' => 10000 + $i,
				'status' => 2,
				'fromWarehouse' => (object) ['name' => 'Almacén ' . ($i + 1)],
				'created_at' => now()->subMinutes($i * 5),
			];
		}

		// Contadores para que el badge muestre un número alto también
		$alert_product = 2;
		$alert_lote = 1;
		$alert_cuis = 0;

		session(['test_notifications' => [
			'alert_product' => $alert_product,
			'alert_lote' => $alert_lote,
			'alert_cuis' => $alert_cuis,
			'pendingTransfers' => $sampleTransfers,
		]]);

		// Forzar apertura del modal
		session()->flash('show_notifications_modal', true);
		return redirect('/');
	})->name('test.notifications');


	Route::get('/', 'HomeController@index');
	Route::get('/dashboard-filter/{start_date}/{end_date}', 'HomeController@dashboardFilter');

	Route::get('language_switch/{locale}', 'LanguageController@switchLanguage');

	Route::get('role/permission/{id}', 'RoleController@permission')->name('role.permission');
	Route::post('role/set_permission', 'RoleController@setPermission')->name('role.setPermission');
	Route::resource('role', 'RoleController');

	Route::post('importunit', 'UnitController@importUnit')->name('unit.import');
	Route::post('unit/deletebyselection', 'UnitController@deleteBySelection');
	Route::get('unit/lims_unit_search', 'UnitController@limsUnitSearch')->name('unit.search');
	Route::resource('unit', 'UnitController');

	Route::get('category/get/{estatus}', 'CategoryController@getProductosServicios')->name('category-get');
	Route::post('category/import', 'CategoryController@import')->name('category.import');
	Route::post('category/deletebyselection', 'CategoryController@deleteBySelection');
	Route::post('category/category-data', 'CategoryController@categoryData');
	Route::resource('category', 'CategoryController');

	Route::post('importbrand', 'BrandController@importBrand')->name('brand.import');
	Route::post('brand/deletebyselection', 'BrandController@deleteBySelection');
	Route::get('brand/lims_brand_search', 'BrandController@limsBrandSearch')->name('brand.search');
	Route::resource('brand', 'BrandController');

	Route::post('importsupplier', 'SupplierController@importSupplier')->name('supplier.import');
	Route::post('supplier/deletebyselection', 'SupplierController@deleteBySelection');
	Route::get('supplier/lims_supplier_search', 'SupplierController@limsSupplierSearch')->name('supplier.search');
	Route::resource('supplier', 'SupplierController');

	Route::post('importwarehouse', 'WarehouseController@importWarehouse')->name('warehouse.import');
	Route::post('warehouse/deletebyselection', 'WarehouseController@deleteBySelection');
	Route::get('warehouse/lims_warehouse_search', 'WarehouseController@limsWarehouseSearch')->name('warehouse.search');
	Route::resource('warehouse', 'WarehouseController');

	Route::post('importtax', 'TaxController@importTax')->name('tax.import');
	Route::post('tax/deletebyselection', 'TaxController@deleteBySelection');
	Route::get('tax/lims_tax_search', 'TaxController@limsTaxSearch')->name('tax.search');
	Route::resource('tax', 'TaxController');

	//Route::get('products/getbarcode', 'ProductController@getBarcode');
	Route::post('products/product-data', 'ProductController@productData');
	Route::get('products/gencode', 'ProductController@generateCode');
	Route::get('products/search', 'ProductController@search');
	Route::get('products/saleunit/{id}', 'ProductController@saleUnit');
	Route::get('products/getdata/{id}', 'ProductController@getData');
	Route::get('products/getproducts/{filter}', 'ProductController@getProductByFilter');
	Route::get('products/product_warehouse/{id}', 'ProductController@productWarehouseData');
	Route::post('importproduct', 'ProductController@importProduct')->name('product.import');
	Route::post('exportproduct', 'ProductController@exportProduct')->name('product.export');
	Route::get('products/print_barcode', 'ProductController@printBarcode')->name('product.printBarcode');
	Route::get('products/getprice/{id}/{type}', 'ProductController@getPrice')->name('product.getPrice');
	Route::post('products/list_gallery', 'ProductController@listGallery');
	Route::get('products/delete_image/{id}/{pos}', 'ProductController@deleteImage');
	Route::get('products/lims_product_search', 'ProductController@limsProductSearch')->name('product.search');
	Route::post('products/deletebyselection', 'ProductController@deleteBySelection');
	Route::post('products/update', 'ProductController@updateProduct');
	Route::post('products/update_image', 'ProductController@updateImage');
	Route::get('products/export-excel/{id_category}', 'ProductController@downloadExcel')->name('product.export-excel');
	Route::post('products/import-excel', 'ProductController@importProductUpdate')->name('product.import-excel');
	Route::resource('products', 'ProductController');

	Route::post('importcustomer_group', 'CustomerGroupController@importCustomerGroup')->name('customer_group.import');
	Route::post('customer_group/deletebyselection', 'CustomerGroupController@deleteBySelection');
	Route::get('customer_group/lims_customer_group_search', 'CustomerGroupController@limsCustomerGroupSearch')->name('customer_group.search');
	Route::resource('customer_group', 'CustomerGroupController');

	Route::post('importar_cliente', 'CustomerController@importarClienteDetallado')->name('customer.importar_cliente');
	Route::post('importcustomer', 'CustomerController@importCustomer')->name('customer.import');
	Route::get('customer/getDeposit/{id}', 'CustomerController@getDeposit');
	Route::post('customer/add_deposit', 'CustomerController@addDeposit')->name('customer.addDeposit');
	Route::post('customer/update_deposit', 'CustomerController@updateDeposit')->name('customer.updateDeposit');
	Route::post('customer/deleteDeposit', 'CustomerController@deleteDeposit')->name('customer.deleteDeposit');
	Route::post('customer/deletebyselection', 'CustomerController@deleteBySelection');
	Route::get('customer/lims_customer_search', 'CustomerController@limsCustomerSearch')->name('customer.search');
	Route::get('customer/customer_search', 'CustomerController@searchCustomer')->name('customer.searchs');
	Route::get('customer/verificar_nit/{nit}', 'CustomerController@verificarNIT')->name('customer.verificar_nit');
	Route::post('customer/list-data', 'CustomerController@listData');
	Route::resource('customer', 'CustomerController');

	Route::post('importbiller', 'BillerController@importBiller')->name('biller.import');
	Route::post('biller/deletebyselection', 'BillerController@deleteBySelection');
	Route::get('biller/lims_biller_search', 'BillerController@limsBillerSearch')->name('biller.search');
	Route::get('biller/warehouses/{id}', 'BillerController@warehouseAuthorizate')->name('biller.warehouses');
	Route::resource('biller', 'BillerController');

	// Pruebas enlace para facturacion	
	// 
	Route::get('sales/get_correo_biller/{biller_id}', 'SaleController@getCorreoBiller')->name('get_correo_biller');
	Route::get('sales/get_punto_venta/{sucursal}', 'SaleController@getPuntoVentaxSucursal')->name('getPuntoVentaxSucursal');
	Route::post('sales/consultar_fecha_manual_cafc', 'SaleController@consultaFechaManualCafc')->name('consultar_fecha_manual_cafc');
	Route::post('sales/consultar_nro_factura_manual', 'SaleController@consultaNroFacturaManual')->name('consultar_nro_factura_manual');
	Route::get('sales/tipo_evento_contingencia/{biller_id}', 'SaleController@getTipoEventoContingenciaPuntoVenta')->name('get_tipo_evento_contingencia');
	Route::get('sales/get_motivo_anulacion', 'SaleController@getMotivoAnulacion')->name('sales.get_motivo_anulacion');
	Route::get('sales/buscador_documento', 'SaleController@searchNit')->name('searchNit');
	Route::get('sales/estado_vigencia_cufd/{biller_id}', 'SaleController@getEstadoCufd')->name('estado_vigencia_cufd');
	Route::get('sales/obtener_bytes_factura/{venta_id}', 'SaleController@getBytesFactura')->name('sales.obtener_bytes_factura');
	Route::get('sales/imprimir_factura/{venta_id}', 'SaleController@getFactura')->name('sales.imprimir_factura');
	// PDF directo para WhatsApp
	Route::get('sales/download-factura-pdf/{venta_id}', 'SaleController@downloadFacturaPdf')->name('sales.download-factura-pdf');
	Route::get('sales/get_estado_p_venta/{biller_id}', 'SaleController@estadoContingenciaPuntoVenta')->name('estado_punto_venta_contingencia');
	Route::get('sales/get_estado_sin', 'SaleController@getEstadoServiciosSiat')->name('estado_servicios_sin');
	Route::post('sales/anular_factura', 'SaleController@anularVentaFacturada')->name('sales.anular_factura');
	Route::post('sales/get_customer_phone', 'SaleController@getCustomerPhone')->name('sales.get_customer_phone');
	Route::post('sales/get_customer_phone_by_cuf', 'SaleController@getCustomerPhoneByCuf')->name('sales.get_customer_phone_by_cuf');
	Route::get('sales/getcliente/{id}', 'SaleController@getCliente');
	Route::post('sales/sale-data', 'SaleController@saleData');
	Route::post('sales/sendmail', 'SaleController@sendMail')->name('sale.sendmail');
	Route::get('sales/sale_by_csv', 'SaleController@saleByCsv');
	Route::get('sales/product_sale/{id}', 'SaleController@productSaleData');
	Route::post('importsale', 'SaleController@importSale')->name('sale.import');
	// WhatsApp: Enviar factura por WhatsApp
	Route::post('sales/send-invoice-whatsapp', 'SaleController@sendInvoiceWhatsApp')->name('sales.send-invoice-whatsapp');
	Route::get('pos', 'SaleController@posSale')->name('sale.pos');
	// Endpoint para obtener datos de factura por CUF (SIAT / local fallback)
	Route::get('factura.venta/datos-factura', 'SaleController@datosFactura');
	Route::get('sales/lims_sale_search', 'SaleController@limsSaleSearch')->name('sale.search');
	Route::get('sales/lims_product_search', 'SaleController@limsProductSearch')->name('product_sale.search');
	Route::get('sales/getcustomergroup/{id}', 'SaleController@getCustomerGroup')->name('sale.getcustomergroup');
	Route::get('sales/getproduct_customer/{id}/{id_customer}', 'SaleController@getProduct')->name('sale.getproduct');
	Route::get('sales/getstockprofinish/{id}/{warehouse_id}', 'SaleController@productFinish_Stock')->name('sale.finishpro');
	Route::get('sales/getproduct/{category_id}/{brand_id}', 'SaleController@getProductByFilter');
	Route::get('sales/getfeatured', 'SaleController@getFeatured');
	Route::get('sales/get_gift_card/{customer_id}', 'SaleController@getGiftCard');
	Route::get('sales/paypalSuccess', 'SaleController@paypalSuccess');
	Route::get('sales/paypalPaymentSuccess/{id}', 'SaleController@paypalPaymentSuccess');
	Route::get('sales/gen_invoice/{id}', 'SaleController@genInvoice')->name('sale.invoice');
	Route::post('sales/add_payment', 'SaleController@addPayment')->name('sale.add-payment');
	Route::get('sales/getpayment/{id}', 'SaleController@getPayment')->name('sale.get-payment');
	Route::post('sales/updatepayment', 'SaleController@updatePayment')->name('sale.update-payment');
	Route::post('sales/deletepayment', 'SaleController@deletePayment')->name('sale.delete-payment');
	Route::get('sales/{id}/create', 'SaleController@createSale');
	Route::post('sales/deletebyselection', 'SaleController@deleteBySelection');
	Route::get('sales/libro-ventas', 'SaleController@libroVentas')->name('sale.libro-ventas');
	Route::post('sales/list_booksales', 'SaleController@listBooksales');
	Route::get('sales/imprimir_factura_cuf/{cuf}', 'SaleController@getPrintFactura')->name('sales.print-factura');
	Route::post('sales/pagar_factura', 'SaleController@paymentFactura')->name('sales.pagar-factura');
	Route::post('sales/revertir_pago_factura', 'SaleController@revertirPaymentFactura')->name('sales.revertirpago-factura');
	Route::get('sales/factura/{cuf}', 'SaleController@getFacturaCufd')->name('sales.factura');
	Route::post('sales/reporte_cobranza', 'SaleController@reporteCobranza')->name('sales.reporte.cobranza');
	Route::post('sales/reporte_revertido', 'SaleController@reporteRevertidos')->name('sales.reporte.revertido');
	Route::post('sales/reporte_arqueogral', 'SaleController@reporteArqueoC')->name('sales.reporte.arqueogral');
	Route::post('sales/reporte_arqueogralcateg', 'SaleController@reporteArqueoCateg')->name('sales.reporte.arqueogralcateg');
	Route::post('sales/reporte_libroventa_pdf', 'SaleController@reporteLVPDF')->name('sales.reporte.reporteLVPDF');
	Route::post('sales/reporte_libroventa_excel', 'SaleController@reporteLVEXCEL')->name('sales.reporte.reporteLVEXCEL');
	Route::get('sales/search_product', 'SaleController@searchProduct')->name('sales.reporte.search.product');
	Route::resource('sales', 'SaleController');

	/** Routes Pre-Sales */
	Route::resource('presales', 'PreSaleController');
	Route::get('presales/gen_invoice/{id}', 'PreSaleController@genInvoice')->name('presale.invoice');
	Route::get('prepos', 'PreSaleController@preSale')->name('presale.prepos');
	Route::get('presales/list/{filter}', 'PreSaleController@listPresale');

	Route::get('delivery', 'DeliveryController@index')->name('delivery.index');
	Route::get('delivery/create/{id}', 'DeliveryController@create');
	Route::post('delivery/store', 'DeliveryController@store')->name('delivery.store');
	Route::get('delivery/{id}/edit', 'DeliveryController@edit');
	Route::post('delivery/update', 'DeliveryController@update')->name('delivery.update');
	Route::post('delivery/deletebyselection', 'DeliveryController@deleteBySelection');
	Route::post('delivery/delete/{id}', 'DeliveryController@delete')->name('delivery.delete');

	Route::get('quotations/product_quotation/{id}', 'QuotationController@productQuotationData');
	Route::get('quotations/lims_product_search', 'QuotationController@limsProductSearch')->name('product_quotation.search');
	Route::get('quotations/getcustomergroup/{id}', 'QuotationController@getCustomerGroup')->name('quotation.getcustomergroup');
	Route::get('quotations/getproduct/{id}/{id_customer}', 'QuotationController@getProduct')->name('quotation.getproduct');
	Route::get('quotations/{id}/create_sale', 'QuotationController@createSale')->name('quotation.create_sale');
	Route::get('quotations/{id}/create_purchase', 'QuotationController@createPurchase')->name('quotation.create_purchase');
	Route::post('quotations/sendmail', 'QuotationController@sendMail')->name('quotation.sendmail');
	Route::post('quotations/deletebyselection', 'QuotationController@deleteBySelection');
	Route::get('quotations/gen_invoice/{id}', 'QuotationController@genInvoice')->name('quotations.invoice');
	Route::post('quotations/list-data', 'QuotationController@listData');
	Route::resource('quotations', 'QuotationController');

	Route::post('purchases/purchase-data', 'PurchaseController@purchaseData');
	Route::get('purchases/product_purchase/{id}', 'PurchaseController@productPurchaseData');
	Route::get('purchases/lims_product_search', 'PurchaseController@limsProductSearch')->name('product_purchase.search');
	Route::post('purchases/add_payment', 'PurchaseController@addPayment')->name('purchase.add-payment');
	Route::get('purchases/getpayment/{id}', 'PurchaseController@getPayment')->name('purchase.get-payment');
	Route::post('purchases/updatepayment', 'PurchaseController@updatePayment')->name('purchase.update-payment');
	Route::post('purchases/deletepayment', 'PurchaseController@deletePayment')->name('purchase.delete-payment');
	Route::get('purchases/purchase_by_csv', 'PurchaseController@purchaseByCsv');
	Route::post('importpurchase', 'PurchaseController@importPurchase')->name('purchase.import');
	Route::post('purchases/deletebyselection', 'PurchaseController@deleteBySelection');
	Route::get('purchases/inactive_item/{id}/{id_pro}', 'PurchaseController@changeStatuItem');
	Route::get('purchases/delete_item/{id}/{id_pro}', 'PurchaseController@destroyItem')->name('purchase.delete-item');
	Route::get('purchases/getproducts', 'PurchaseController@getProduct')->name('purchases.getproducts');
	Route::resource('purchases', 'PurchaseController');

	Route::get('transfers/product_transfer/{id}', 'TransferController@productTransferData');
	Route::get('transfers/requests', 'TransferController@transferRequest');
	Route::get('transfers/transfer_by_csv', 'TransferController@transferByCsv');
	Route::post('importtransfer', 'TransferController@importTransfer')->name('transfer.import');
	Route::get('transfers/getproduct/{id}', 'TransferController@getProduct')->name('transfer.getproduct');
	Route::get('transfers/lims_product_search', 'TransferController@limsProductSearch')->name('product_transfer.search');
	Route::post('transfers/deletebyselection', 'TransferController@deleteBySelection');
	Route::resource('transfers', 'TransferController');
	Route::get('transfers/{id}/details', 'TransferController@showTransferDetails');
	Route::post('transfers/{id}/approve', 'TransferController@approve')->name('transfers.approve');
	Route::post('transfers/{id}/reject', 'TransferController@reject')->name('transfers.reject');
	Route::get('transfer-logs', 'TransferLogController@index')->name('transfer-logs.index');
	Route::get('transfer-logs/{transfer_id}', 'TransferLogController@show')->name('transfer-logs.show');

	Route::get('qty_adjustment/getproduct/{id}', 'AdjustmentController@getProduct')->name('adjustment.getproduct');
	Route::get('qty_adjustment/getproduct-data/{idwh}/{idpro}', 'AdjustmentController@getInfoProduct')->name('adjustment.getproduct-data');

	Route::get('qty_adjustment/lims_product_search', 'AdjustmentController@limsProductSearch')->name('product_adjustment.search');
	Route::post('qty_adjustment/deletebyselection', 'AdjustmentController@deleteBySelection');
	Route::resource('qty_adjustment', 'AdjustmentController');

	Route::get('return-sale/getcustomergroup/{id}', 'ReturnController@getCustomerGroup')->name('return-sale.getcustomergroup');
	Route::post('return-sale/sendmail', 'ReturnController@sendMail')->name('return-sale.sendmail');
	Route::get('return-sale/getproduct/{id}', 'ReturnController@getProduct')->name('return-sale.getproduct');
	Route::get('return-sale/lims_product_search', 'ReturnController@limsProductSearch')->name('product_return-sale.search');
	Route::get('return-sale/product_return/{id}', 'ReturnController@productReturnData');
	Route::post('return-sale/deletebyselection', 'ReturnController@deleteBySelection');
	Route::post('return-sale/list_invoice', 'ReturnController@listarFacturas');
	Route::get('return/obtener_bytes_factura/{return_id}', 'ReturnController@getBytesFactura')->name('return.obtener_bytes_factura');
	Route::get('return-sale/anula_nota/{id}/{id_motivo}', 'ReturnController@anularNotaFiscal')->name('return-sale.anula_nota');
	Route::resource('return-sale', 'ReturnController');

	Route::get('return-purchase/getcustomergroup/{id}', 'ReturnPurchaseController@getCustomerGroup')->name('return-purchase.getcustomergroup');
	Route::post('return-purchase/sendmail', 'ReturnPurchaseController@sendMail')->name('return-purchase.sendmail');
	Route::get('return-purchase/getproduct/{id}', 'ReturnPurchaseController@getProduct')->name('return-purchase.getproduct');
	Route::get('return-purchase/lims_product_search', 'ReturnPurchaseController@limsProductSearch')->name('product_return-purchase.search');
	Route::get('return-purchase/product_return/{id}', 'ReturnPurchaseController@productReturnData');
	Route::post('return-purchase/deletebyselection', 'ReturnPurchaseController@deleteBySelection');
	Route::resource('return-purchase', 'ReturnPurchaseController');

	Route::get('report/product_quantity_alert', 'ReportController@productQuantityAlert')->name('report.qtyAlert');
	Route::get('report/warehouse_stock', 'ReportController@warehouseStock')->name('report.warehouseStock');
	Route::post('report/warehouse_stock', 'ReportController@warehouseStockById')->name('report.warehouseStockId');
	Route::get('report/daily_sale/{year}/{month}', 'ReportController@dailySale');
	Route::post('report/daily_sale/{year}/{month}', 'ReportController@dailySaleByWarehouse')->name('report.dailySaleByWarehouse');
	Route::get('report/monthly_sale/{year}', 'ReportController@monthlySale');
	Route::post('report/monthly_sale/{year}', 'ReportController@monthlySaleByWarehouse')->name('report.monthlySaleByWarehouse');
	Route::get('report/daily_purchase/{year}/{month}', 'ReportController@dailyPurchase');
	Route::post('report/daily_purchase/{year}/{month}', 'ReportController@dailyPurchaseByWarehouse')->name('report.dailyPurchaseByWarehouse');
	Route::get('report/monthly_purchase/{year}', 'ReportController@monthlyPurchase');
	Route::post('report/monthly_purchase/{year}', 'ReportController@monthlyPurchaseByWarehouse')->name('report.monthlyPurchaseByWarehouse');
	Route::get('report/best_seller', 'ReportController@bestSeller');
	Route::post('report/best_seller', 'ReportController@bestSellerByWarehouse')->name('report.bestSellerByWarehouse');
	Route::post('report/profit_loss', 'ReportController@profitLoss')->name('report.profitLoss');
	Route::post('report/product_report', 'ReportController@productReport')->name('report.product');
	Route::get('report/product_detail_report', 'ReportController@reportProducts')->name('report.productdetail');
	Route::post('report/purchase', 'ReportController@purchaseReport')->name('report.purchase');
	Route::post('report/sale_report', 'ReportController@saleReport')->name('report.sale');
	Route::post('report/payment_report_by_date', 'ReportController@paymentReportByDate')->name('report.paymentByDate');
	Route::post('report/warehouse_report', 'ReportController@warehouseReport')->name('report.warehouse');
	Route::post('report/user_report', 'ReportController@userReport')->name('report.user');
	Route::post('report/customer_report', 'ReportController@customerReport')->name('report.customer');
	Route::post('report/supplier', 'ReportController@supplierReport')->name('report.supplier');
	Route::post('report/due_report_by_date', 'ReportController@dueReportByDate')->name('report.dueByDate');
	Route::post('report/sale_biller_report', 'ReportController@saleBillerReport')->name('report.saleBiller');
	Route::post('report/sale_customer_report', 'ReportController@saleCustomerReport')->name('report.saleCustomer');
	Route::post('report/sale_product_report', 'ReportController@saleProductReport')->name('report.saleProduct');
	Route::post('report/sale_courtesy_report', 'ReportController@saleCourtesyReport')->name('report.saleCourtesy');
	Route::post('report/service_employee_report', 'ReportController@saleServiceReport')->name('report.employeeService');
	Route::post('report/service_employeecomission_report', 'ReportController@saleServiceComissionReport')->name('report.employeeComissionService');
	Route::get('report/general_report/{date_s}/{date_e}/{category}/{biller}', 'ReportController@generalallReport')->name('report.generalAll');
	Route::get('report/generalutil_report/{date_s}/{date_e}/{category}/{biller}', 'ReportController@generalallUtilReport')->name('report.generalUtil');
	Route::post('report/productfinish_report', 'ReportController@productFinishReport')->name('report.productFinish');
	Route::get('report/alert_expiration/{filter}/{days}', 'ReportController@lote_expirationReport')->name('report.alertExpiration');
	Route::get('report/productlote_report', 'ReportController@products_lotesReport')->name('report.productsLotes');
	Route::post('report/sale_detail_report', 'ReportController@salesByProductReport')->name('report.saleByProduct');
	Route::post('report/sale_renueve_report', 'ReportController@reportProductRenueve')->name('report.saleRenueve');
	Route::get('report/holiday-employee/{date_s}/{date_e}/{id_employee}', 'ReportController@reportHolidayEmployee')->name('report.holidayEmployee');
	Route::get('report/attendance-employee/{date_s}/{date_e}/{id_employee}', 'ReportController@reportAttendanceEmployee')->name('report.attendanceEmployee');
	Route::get('report/sales_report/{date_s}/{date_e}/{sucursal}/{biller_id}', 'ReportController@reportSales')->name('report.salerp');
	Route::get('report/resumen_account/{date_s}/{date_e}/{account_id}/{sucursal}', 'ReportController@reportResumenSaleAccount')->name('report.resumenaccount');

	Route::get('user/profile/{id}', 'UserController@profile')->name('user.profile');
	Route::put('user/update_profile/{id}', 'UserController@profileUpdate')->name('user.profileUpdate');
	Route::put('user/changepass/{id}', 'UserController@changePassword')->name('user.password');
	Route::get('user/genpass', 'UserController@generatePassword');
	Route::post('user/deletebyselection', 'UserController@deleteBySelection');
	Route::get('user/permission-category/{id}', 'UserController@permissionCategory')->name('user.permissionCategory');
	Route::put('user/update_permission', 'UserController@permission')->name('user.updatePermission');
	Route::resource('user', 'UserController');

	// Companies routes
	Route::resource('companies', 'CompanyController');

	Route::get('setting/general_setting', 'SettingController@generalSetting')->name('setting.general');
	Route::post('setting/general_setting_store', 'SettingController@generalSettingStore')->name('setting.generalStore');
	Route::get('setting/general_setting/change-theme/{theme}', 'SettingController@changeTheme');
	Route::get('setting/mail_setting', 'SettingController@mailSetting')->name('setting.mail');
	Route::get('setting/sms_setting', 'SettingController@smsSetting')->name('setting.sms');
	Route::get('setting/createsms', 'SettingController@createSms')->name('setting.createSms');
	Route::post('setting/sendsms', 'SettingController@sendSms')->name('setting.sendSms');
	Route::get('setting/hrm_setting', 'SettingController@hrmSetting')->name('setting.hrm');
	Route::post('setting/hrm_setting_store', 'SettingController@hrmSettingStore')->name('setting.hrmStore');
	Route::post('setting/mail_setting_store', 'SettingController@mailSettingStore')->name('setting.mailStore');
	Route::post('setting/sms_setting_store', 'SettingController@smsSettingStore')->name('setting.smsStore');
	Route::get('setting/pos_setting', 'SettingController@posSetting')->name('setting.pos');
	Route::get('setting/pos_settingjson', 'SettingController@posSettingJSON');
	Route::post('setting/pos_setting_store', 'SettingController@posSettingStore')->name('setting.posStore');
	Route::post('setting/pos_setting_update', 'SettingController@posSettingUpdate');
	Route::get('setting/empty-database', 'SettingController@emptyDatabase')->name('setting.emptyDatabase');
	Route::get('setting/backup-database', 'SettingController@backupDatabase')->name('setting.backupDatabase');
	Route::get('setting/module_qr', 'SettingController@moduleQr')->name('setting.qrsimple');
	Route::get('setting/module_siat', 'SiatController@index')->name('setting.siat');

	Route::get('expense_categories/gencode', 'ExpenseCategoryController@generateCode');
	Route::post('expense_categories/import', 'ExpenseCategoryController@import')->name('expense_category.import');
	Route::post('expense_categories/deletebyselection', 'ExpenseCategoryController@deleteBySelection');
	Route::resource('expense_categories', 'ExpenseCategoryController');

	Route::post('expenses/deletebyselection', 'ExpenseController@deleteBySelection');
	Route::resource('expenses', 'ExpenseController');

	Route::get('gift_cards/gencode', 'GiftCardController@generateCode');
	Route::post('gift_cards/recharge/{id}', 'GiftCardController@recharge')->name('gift_cards.recharge');
	Route::post('gift_cards/deletebyselection', 'GiftCardController@deleteBySelection');
	Route::resource('gift_cards', 'GiftCardController');

	Route::get('coupons/gencode', 'CouponController@generateCode');
	Route::post('coupons/deletebyselection', 'CouponController@deleteBySelection');
	Route::resource('coupons', 'CouponController');
	//accounting routes
	Route::get('accounts/make-default/{id}', 'AccountsController@makeDefault');
	Route::get('accounts/balancesheet', 'AccountsController@balanceSheet')->name('accounts.balancesheet');
	Route::get('accounts/list', 'AccountsController@listaccounts');
	Route::post('accounts/balancesheet_account', 'AccountsController@balanceSheetAccount')->name('accounts.balancesheetaccount');
	Route::post('accounts/account-statement', 'AccountsController@accountStatement')->name('accounts.statement');
	Route::resource('accounts', 'AccountsController');
	Route::resource('money-transfers', 'MoneyTransferController');
	//HRM routes
	Route::post('departments/deletebyselection', 'DepartmentController@deleteBySelection');
	Route::resource('departments', 'DepartmentController');
	//Employees routes
	Route::post('employees/deletebyselection', 'EmployeeController@deleteBySelection');
	Route::resource('employees', 'EmployeeController');
	//adjustment account routes (guarded by blocked-module middleware)
	Route::middleware(['web', 'blocked.module:adjustment-account'])->group(function () {
		Route::post('adjustment_account/deletebyselection', 'AdjustmentAccountController@deleteBySelection');
		Route::post('adjustment_account/save', 'AdjustmentAccountController@store')->name('adjustmentAccount.store');
		Route::resource('adjustment_account', 'AdjustmentAccountController');
	});

	Route::post('payroll/payroll-data', 'PayrollController@payrollData');
	Route::post('payroll/deletebyselection', 'PayrollController@deleteBySelection');
	Route::resource('payroll', 'PayrollController');

	Route::post('attendance/attendance-data', 'AttendanceController@attendanceData');
	Route::get('attendance/checked/{id}', 'AttendanceController@checkin_out');
	Route::post('attendance/deletebyselection', 'AttendanceController@deleteBySelection');
	Route::resource('attendance', 'AttendanceController');

	/** shift attendance */
	Route::resource('attentionshift', 'AttentionShiftController');
	Route::get('attentionshift/list-data/{filter}', 'AttentionShiftController@list_Data');
	Route::post('attentionshift/birthday', 'AttentionShiftController@verifyBirthday')->name('attentionshift.birthday');

	// Reservations routes
	Route::post('reservations/list-data', 'ReservationController@listData');
	Route::post('reservations/send-reminders', 'ReservationController@sendReminders');
	Route::post('reservations/deletebyselection', 'ReservationController@deleteBySelection');
	// Acciones: marcar ausencia y cancelar
	Route::post('reservations/{id}/mark-absence', 'ReservationController@markAbsence');
	Route::post('reservations/{id}/cancel', 'ReservationController@cancelReservation');
	// Marcar asistencia (check-in) via AJAX
	Route::post('reservations/{id}/mark-attendance', 'ReservationController@markAttendance');
	Route::resource('reservations', 'ReservationController');

	Route::get('attention/employeefirst', 'AttentionShiftController@employeeFirst');
	Route::get('attention/employeelist', 'AttentionShiftController@employeeAll');
	Route::get('attention/listsimple', 'AttentionShiftController@list_cbx');
	Route::get('attention/verifyemp/{id}', 'AttentionShiftController@findEmpShift');
	Route::get('attention/list-enable-emp', 'AttentionShiftController@listemployeEnable');

	Route::resource('stock-count', 'StockCountController');
	Route::post('stock-count/finalize', 'StockCountController@finalize')->name('stock-count.finalize');
	Route::get('stock-count/stockdif/{id}', 'StockCountController@stockDif');
	Route::get('stock-count/{id}/qty_adjustment', 'StockCountController@qtyAdjustment')->name('stock-count.adjustment');

	Route::post('holidays/deletebyselection', 'HolidayController@deleteBySelection');
	Route::get('approve-holiday/{id}', 'HolidayController@approveHoliday')->name('approveHoliday');
	Route::get('holidays/my-holiday/{year}/{month}', 'HolidayController@myHoliday')->name('myHoliday');
	Route::resource('holidays', 'HolidayController');

	Route::get('/home', 'HomeController@index')->name('home');
	Route::get('my-transactions/{year}/{month}', 'HomeController@myTransaction');
	// Cashier routes
	Route::get('cashier/verified/{id}', 'CashierController@verified_amount');
	Route::get('cashier/amountold/{id}', 'CashierController@old_amount');
	Route::get('accounts/cashier/data/{id}', 'CashierController@getdata');
	Route::get('accounts/cashier/total/{id}', 'AccountsController@total_caja');
	Route::put('accounts/cashier/close', 'CashierController@close_cashier')->name('accounts-cashier.close');
	Route::resource('cashier', 'CashierController');

	//Printer Routes
	Route::resource('printer', 'PrinterController');

	//Receivable Routes
	Route::resource('receivable', 'ReceivableController');
	Route::post('receivable', 'ReceivableController@index')->name('receivable.filter');
	Route::post('receivable/payment', 'ReceivableController@processing_payment')->name('receivable.pay');
	Route::get('receivable/report/{id}', 'ReceivableController@report')->name('receivable.report');
	Route::get('receivable/due/{id}', 'ReceivableController@dueCustomer')->name('receivable.due');
	Route::post('receivable/paydue', 'ReceivableController@payDueCustomer')->name('receivable.payDue');


	//Receivable Routes
	Route::get('lote/findbyproduct/{id}', 'ProductController@lotesforProduct')->name('product.bylotes');

	//Route::get('clear/route', 'SettingController@clearRoute');
	Route::get('run/tarea_programada', 'SettingController@runTareaProgramada')->name('run_tarea_programada');
	Route::get('run/forzar_renovar_cufd', 'SettingController@forzarRenovarCUFD')->name('forzar_renovar_cufd');
	Route::get('run/vigencia_renovar_cufd/{biller_id}', 'SettingController@vigenciaRenovarCUFD')->name('vigencia_renovar_cufd');
	Route::get('setting/lista_puntos_venta', 'SettingController@listaPuntoVenta')->name('setting.lista_puntos_venta');
	Route::get('setting/vigencia_renovar_cufd_pv/{id}', 'SettingController@vigenciaRenovarCUFDPuntoVenta')->name('setting.renovar_puntoventa');

	//proceso de Sincronización
	Route::get('siat_panel/renovar_cufd', 'SiatPanelController@renovarCUFD')->name('siat_panel.renovar_cufd'); //
	Route::get('activities/update', 'SiatActividadEconomicaController@siat')->name('activities.siat');
	Route::get('documentsector/update', 'SiatDocumentoSectorController@siat')->name('documentsector.siat');
	Route::get('productservice/update', 'SiatProductoServicioController@siat')->name('productservice.siat');
	Route::get('legends/update', 'SiatLeyendaFacturaController@siat')->name('legends.siat');
	Route::get('parametric/update', 'SiatParametricaVarioController@siat')->name('parametric.siat');
	Route::get('siat_panel/doc', 'SiatPanelController@documentosector')->name('siat_panel.documento_sector');
	Route::get('siat_panel/doc/{id}', 'SiatPanelController@getDocumentoSectorById')->name('siat_panel.documento_buscar');
	Route::get('siat_panel/act/{id}', 'SiatPanelController@getActividadById')->name('siat_panel.actividad_buscar');
	Route::get('siat_panel/par', 'SiatPanelController@parametros')->name('siat_panel.parametros');
	Route::get('siat_panel/par/{id}', 'SiatPanelController@getParametroById')->name('siat_panel.parametro_buscar');
	Route::get('siat_panel/prod', 'SiatPanelController@productoservicio')->name('siat_panel.productoservicio');
	Route::get('siat_panel/prod/{id}', 'SiatPanelController@getProductoServicioById')->name('siat_panel.productoservicio_buscar');
	Route::get('siat_panel/leyenda', 'SiatPanelController@leyenda')->name('siat_panel.leyenda');
	Route::get('siat_panel/leyenda/{id}', 'SiatPanelController@getLeyendaById')->name('siat_panel.leyenda_buscar');
	Route::get('siat_panel/registros-siat', 'SiatPanelController@logSiat')->name('siat_panel.log_siat');
	Route::get('siat_panel/p_venta/{id}', 'SiatPanelController@getPuntoVenta'); //operaciones
	Route::get('siat_panel/cuis/{sucursal}/{codigo_punto_venta}', 'SiatPanelController@getCuis'); //operaciones


	//obtener vista de Registros de Sincronización de: Sucursal + PuntoVenta
	Route::get('siat_panel/registros-siat/suc/{sucursal_id}/pv/{p_venta_id}', 'SiatPanelController@consultaRegistros');

	//CRUD SIAT
	Route::resource('activities', 'SiatActividadEconomicaController');
	Route::resource('legends', 'SiatLeyendaFacturaController');
	Route::resource('documentsector', 'SiatDocumentoSectorController');
	Route::resource('productservice', 'SiatProductoServicioController');
	Route::resource('parametric', 'SiatParametricaVarioController');
	Route::resource('siat_panel', 'SiatPanelController');
	Route::resource('sucursal', 'SiatSucursalController');
	Route::resource('url-ws', 'UrlWsController');
	Route::get('/autorizacion/{estatus}/modalidad/{modalidad_id}', 'AutorizacionFacturacionController@getUrls')->name('getUrls');
	Route::post('/autorizacion/buscar_documento_sector', 'AutorizacionFacturacionController@getDocumentoSector')->name('buscar_documento_sector');
	Route::get('autorizacion/cambiar_estado/{idautorizacion}', 'AutorizacionFacturacionController@cambiarEstadoAutorizacion')->name('autorizacion.cambiar_estado');
	Route::resource('autorizacion', 'AutorizacionFacturacionController');
	Route::get('punto_venta/renovar-cuis/{id}/{idPuntoVenta}/{idSucursal}', 'SiatPuntoVentaController@renovarCuis')->name('puntoventa.renovar_cuis');
	Route::get('punto_venta/renovacion-masiva-cuis', 'SiatPuntoVentaController@renovacionMasivaCuis');
	Route::get('punto_venta/estado/{id}', 'SiatPuntoVentaController@estadoPuntoVenta')->name('puntoventa.estado');
	Route::resource('punto_venta', 'SiatPuntoVentaController');

	//Método de Pago
	Route::resource('method_payment', 'MethodPaymentController');

	// Modo contingencia
	Route::get('contingencia/obtener_logs_errores/{contingencia_detalle_id}', 'ControlContingenciaController@obtenerLogsErrores')->name('contingencia.obtener_logs_errores');
	Route::post('contingencia/confirmar_excepcion_todos_nit', 'ControlContingenciaController@confirmarExcepcionTodosNit')->name('contingencia.confirmar_excepcion_todos_nit');
	Route::get('contingencia/confirmar_excepcion_nit/{sale_id}', 'ControlContingenciaController@confirmarExcepcionNit')->name('contingencia.confirmar_excepcion_nit');
	Route::get('contingencia/validar_datos_paquete/{contingencia_detalle_id}', 'ControlContingenciaController@validarDatosPaquete')->name('contingencia.validar_datos_paquete');
	Route::get('contingencia/verificar_paquete/{contingencia_detalle_id}', 'ControlContingenciaController@verificarEstadoPaqueteEnviado')->name('contingencia.verificar_paquete');
	Route::get('contingencia/obtener_arreglo_ventas_paquete/{contingencia_detalle_id}', 'ControlContingenciaController@obtenerArregloVentasxPaquete')->name('contingencia.obtener_arreglo_ventas_paquete');
	Route::get('contingencia/cerrar_evento/{contingencia_id}', 'ControlContingenciaController@cerrarModoContingencia')->name('contingencia.cerrar_evento');
	Route::get('contingencia/enviar_paquetes/{contingencia_detalle_id}', 'ControlContingenciaController@enviarPaquetes')->name('contingencia.enviar_paquetes');
	Route::get('contingencia/obtener_paquetes/{contingencia_id}', 'ControlContingenciaController@obtenerVentasxPaquetes')->name('contingencia.obtener_paquetes');
	Route::post('contingencia/cargar_archivo', 'ControlContingenciaController@cargarArchivo')->name('contingencia.cargar_archivo');
	Route::post('contingencia/verificar_archivo', 'ControlContingenciaController@verificarArchivoExcel')->name('contingencia.verificar_archivo');
	Route::get('contingencia/registrar_evento/{contingencia_id}', 'ControlContingenciaController@registrarEvento')->name('contingencia.registrar-evento');
	Route::get('contingencia/registrar_evento_auto/{contingencia_id}', 'ControlContingenciaController@registrarEventoAuto')->name('contingencia.registrar-evento-auto');
	Route::get('contingencia/get_punto_venta/{sucursal}', 'ControlContingenciaController@getPuntoVenta')->name('getPuntosVentas');
	Route::resource('contingencia', 'ControlContingenciaController');

	// Modo Masivo
	Route::get('factura-masiva/obtener_logs_errores/{factura_masiva_paquete_id}', 'FacturaMasivaController@obtenerLogsErrores')->name('factura-masiva.obtener_logs_errores');
	Route::post('factura-masiva/confirmar_excepcion_todos_nit', 'FacturaMasivaController@confirmarExcepcionTodosNit')->name('factura-masiva.confirmar_excepcion_todos_nit');
	Route::get('factura-masiva/confirmar_excepcion_nit/{sale_id}', 'FacturaMasivaController@confirmarExcepcionNit')->name('factura-masiva.confirmar_excepcion_nit');
	Route::get('factura-masiva/verificar_paquete/{factura_masiva_paquete_id}', 'FacturaMasivaController@verificarEstadoPaqueteEnviado')->name('factura-masiva.verificar_paquete');
	Route::get('factura-masiva/obtener_arreglo_ventas_paquete/{factura_masiva_paquete_id}', 'FacturaMasivaController@obtenerArregloVentasxPaquete')->name('factura-masiva.obtener_arreglo_ventas_paquete');
	Route::get('factura-masiva/anular_ventas_paquete/{factura_masiva_paquete_id}', 'FacturaMasivaController@obtenerArregloVentasxPaqueteAnular')->name('factura-masiva.anular_ventas_paquete');
	Route::get('factura-masiva/cerrar_evento/{factura_masiva_id}', 'FacturaMasivaController@cerrarFacturaMasiva')->name('factura-masiva.cerrar_evento');
	Route::get('factura-masiva/enviar_paquetes/{factura_masiva_paquete_id}', 'FacturaMasivaController@enviarPaqueteFacturaMasiva')->name('factura-masiva.enviar_paquetes');
	Route::get('factura-masiva/validar_datos_paquete/{factura_masiva_paquete_id}', 'FacturaMasivaController@validarDatosPaquete')->name('factura-masiva.validar_datos_paquete');
	Route::get('factura-masiva/obtener_paquetes/{factura_masiva_id}', 'FacturaMasivaController@obtenerPaquetes')->name('factura-masiva.obtener_paquetes');
	Route::get('factura-masiva/cargar_archivo/{factura_masiva_id}', 'FacturaMasivaController@cargarArchivo')->name('factura-masiva.cargar_archivo');
	Route::post('factura-masiva/verificar_archivo', 'FacturaMasivaController@verificarArchivoExcel')->name('factura-masiva.verificar_archivo');
	Route::get('factura-masiva/validar_registro/{sucursal_id}/{punto_venta_id}', 'FacturaMasivaController@validarNuevaFacturaMasiva')->name('factura-masiva.validar_registro');
	Route::resource('factura-masiva', 'FacturaMasivaController');

	// Bitácora
	Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('logs');

	Route::get('attentionshift/test-event', 'AttentionShiftController@testEvent')->name('attentionshift.event');

	Route::resource('credencial-cafc', 'CredencialCafcController');

	Route::match(['get', 'post'], 'kardex', 'KardexController@index')->name('kardex.index');
	Route::post('kardex', 'KardexController@search')->name('kardex.search');
	Route::post('kardex/control', 'KardexController@controlPoint')->name('kardex.control');
	Route::post('kardex/warehousecontrol', 'KardexController@warehouseControlPoint')->name('kardex.warehouseControl');
	Route::get('kardex/{type}/{id}', 'KardexController@transactionDetails')->name('kardex.details');
});


//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
