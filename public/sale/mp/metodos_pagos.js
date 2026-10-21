// onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
window.pagoMultipleActivo = false;

$(document).on("change", 'select[name="paid_by_id_select"]', function () {
	if (window.pagoMultipleActivo) {
		return;
	}
	$("#number_card").prop("required", false);
	refrescarMontos();
	hideDatosInputTexto();
	alertaTablaItem_o_Empleado_vacio();
	var id = $(this).val();
	$(".payment-form").off("submit");
	if (typeof checkStatusIntervalId !== "undefined" && checkStatusIntervalId) clearInterval(checkStatusIntervalId);
	if (typeof timerIntervalId !== "undefined" && timerIntervalId) clearInterval(timerIntervalId);
	if (id == 27 && typeof giftCard === "function") {
		giftCard();
	}
	if (id == 2 && typeof creditCard === "function") {
		creditCard();
	}
	if (id == 3 && typeof cheque === "function") {
		cheque();
	}
	if (id == 8 && typeof deposits === "function") {
		deposits();
	}
	if (typeof unblockAmounts === "function") {
		unblockAmounts();
	}

	metodosPago($(this));
});

function metodosPago(dom) {
	return new Promise((resolve) => {
		var $dom = $(dom);
		var $opt = $("option:selected", $dom);
		var descripcion = $opt.data("descripcion") || $opt.text() || "";
		var descUpper = String(descripcion).toUpperCase();
		const MPpagos = [
			{ metodo: "TARJETA", fn: MPtarjeta, id: "MP_tarjeta" },
			{ metodo: "GIFT", fn: MPgiftCard, id: "MP_giftCard" },
			{ metodo: "CHEQUE", fn: MPcheque, id: "MP_cheque" },
			{ metodo: "VALE", fn: MPvale, id: "" },
			{ metodo: "OTRO", fn: MPotros, id: "" },
			{ metodo: "PAGO POSTERIOR", fn: MPpagoPosterior, id: "" },
			{ metodo: "TRANSFERENCIA BANCARIA", fn: MPtransferenciaBancaria, id: "" },
			{ metodo: "DEBITO AUTOMATICO", fn: MPdebitoAutomatico, id: "" },
			{ metodo: "DEPOSITO", fn: MPdepositoCuenta, id: "" },
			{ metodo: "SWIFT", fn: MPtransferenciaSwift, id: "" },
			{ metodo: "CANAL", fn: MPcanalPago, id: "" },
			{ metodo: "BILLETERA", fn: MPbilleteraMovil, id: "" },
			{ metodo: "ONLINE", fn: MPpagoOnline, id: "" },
			{ metodo: "EFECTIVO", fn: MPefectivo, id: "" },
		];
		var matched = false;
		MPpagos.forEach((x) => {
			if (descUpper.includes(x.metodo)) {
				matched = true;
				if (typeof x.fn === "function") {
					x.fn();
				}
				if (x.id) $("#" + x.id).show();
			} else {
				if (x.id) $("#" + x.id).hide();
			}
		});
		if (!matched) {
			var val = $dom.val();
			if (val == 1) MPefectivo();
			else if (val == 2 || val == 4) MPtarjeta();
			else if (val == 3 || val == 5) MPcheque();
			else if (val == 8 || val == 6 || val == 7) MPdepositoCuenta();
			else if (val == 27) MPgiftCard();
			else MPefectivo();
		}
		if (typeof unblockAmounts === "function") {
			unblockAmounts();
		}
		ValidacionMetodoPago();
		resolve(true);
	});
}

function metodoPagoFuncion(descripcion) {
	if (!descripcion) return null;
	const txt = String(descripcion).toUpperCase();

	if (txt.includes("TARJETA") || txt.includes("CARD")) return MPtarjeta;
	if (txt.includes("GIFT")) return MPgiftCard;
	if (txt.includes("CHEQUE") || txt.includes("CHECK")) return MPcheque;
	if (txt.includes("VALE")) return MPvale;
	if (txt.includes("OTRO")) return MPotros;
	if (txt.includes("PAGO POSTERIOR")) return MPpagoPosterior;
	if (txt.includes("TRANSFERENCIA BANCARIA")) return MPtransferenciaBancaria;
	if (txt.includes("DEBITO AUTOMATICO") || txt.includes("DÉBITO AUTOMÁTICO"))
		return MPdebitoAutomatico;
	if (
		txt.includes("DEPOSITO") ||
		txt.includes("DEPÓSITO") ||
		txt.includes("QR")
	)
		return MPdepositoCuenta;
	if (txt.includes("SWIFT")) return MPtransferenciaSwift;
	if (txt.includes("CANAL")) return MPcanalPago;
	if (txt.includes("BILLETERA")) return MPbilleteraMovil;
	if (txt.includes("ONLINE")) return MPpagoOnline;
	if (txt.includes("EFECTIVO") || txt.includes("CASH")) return MPefectivo;

	return null;
}

function cargarOpcionesPagoMultiple() {
	const $source = $('select[name="paid_by_id_select"] option');
	const targets = [
		"#multi_pay_method_1",
		"#multi_pay_method_2",
		"#multi_pay_method_3",
	];

	targets.forEach((target) => {
		const $target = $(target);
		$target.empty();
		$target.append('<option value="">Sin seleccionar</option>');
		$source.each(function () {
			const value = $(this).val();
			const descripcion = $(this).data("descripcion") || $(this).text() || "";
			if (!metodoPagoFuncion(descripcion)) return;
			const option = $("<option></option>")
				.val(value)
				.attr("data-descripcion", descripcion)
				.text(descripcion);
			$target.append(option);
		});
	});
	$("#multi_pay_method_1, #multi_pay_method_2, #multi_pay_method_3").selectpicker("refresh");
}

function obtenerMetodosMultiplesSeleccionados() {
	const ids = [
		"#multi_pay_method_1",
		"#multi_pay_method_2",
		"#multi_pay_method_3",
	];
	const seleccionados = [];

	ids.forEach((id) => {
		const value = $(id).val();
		if (!value) return;
		const descripcion = $(`${id} option:selected`).data("descripcion") || "";
		seleccionados.push({ value, descripcion });
	});

	const seen = new Set();
	return seleccionados.filter((x) => {
		if (seen.has(x.value)) return false;
		seen.add(x.value);
		return true;
	});
}

function aplicarPagoMultiple() {
	const metodos = obtenerMetodosMultiplesSeleccionados().slice(0, 3);

	$("#MP_tarjeta").hide();
	$('input[name="number_card"]').val("");
	$("#MP_cheque").hide();
	$('input[name="cheque_no"]').val("");
	$("#MP_giftCard").hide();
	$('#add-payment input[name="balance_gift_card"]').val(0);
	$(".qrsimple").hide();
	$("#html_montos_metodos_de_pago").empty();
	$("#number_card").prop("required", false);

	if (metodos.length === 0) {
		ValidacionMetodoPago();
		return;
	}

	const primeraForma = metodos[0].value;
	$('select[name="paid_by_id_select"]').val(primeraForma);
	$('input[name="paid_by_id"]').val(primeraForma);

	const renderizados = new Set();
	metodos.forEach((metodo) => {
		const fn = metodoPagoFuncion(metodo.descripcion);
		if (!fn) return;
		const fnName = fn.name;
		if (renderizados.has(fnName)) return;
		renderizados.add(fnName);
		fn();
	});

	$('select[name="paid_by_id_select"]').selectpicker("refresh");
	ValidacionMetodoPago();
}

window.activarPagoMultiple = function () {
	window.pagoMultipleActivo = true;
	const $paidBySelect = $('select[name="paid_by_id_select"]');
	$paidBySelect.data("mp-original-disabled", $paidBySelect.prop("disabled"));
	$paidBySelect.prop("disabled", true);
	$("#multiple-pay-selector").show();
	cargarOpcionesPagoMultiple();

	const currentVal = $paidBySelect.val();
	if (currentVal && $(`#multi_pay_method_1 option[value="${currentVal}"]`).length) {
		$("#multi_pay_method_1").val(currentVal);
	} else {
		const firstOptionVal = $("#multi_pay_method_1 option:not([value='']):first").val();
		if (firstOptionVal) {
			$("#multi_pay_method_1").val(firstOptionVal);
		}
	}
	$("#multi_pay_method_2").val("");
	$("#multi_pay_method_3").val("");
	$('select[name="paid_by_id_select"], #multi_pay_method_1, #multi_pay_method_2, #multi_pay_method_3').selectpicker("refresh");
	aplicarPagoMultiple();
	if (typeof unblockAmounts === "function") {
		unblockAmounts();
	}
};

window.desactivarPagoMultiple = function () {
	if (!window.pagoMultipleActivo) return;
	window.pagoMultipleActivo = false;
	const $paidBySelect = $('select[name="paid_by_id_select"]');
	const originalDisabled = $paidBySelect.data("mp-original-disabled");
	$paidBySelect.prop("disabled", !!originalDisabled);
	$("#multiple-pay-selector").hide();
	$("#multi_pay_method_1").val("");
	$("#multi_pay_method_2").val("");
	$("#multi_pay_method_3").val("");
	$('select[name="paid_by_id_select"], #multi_pay_method_1, #multi_pay_method_2, #multi_pay_method_3').selectpicker("refresh");
	if (typeof unblockAmounts === "function") {
		unblockAmounts();
	}
};

$(document).on("change", ".multi-pay-method", function () {
	if (!window.pagoMultipleActivo) return;
	aplicarPagoMultiple();
});

function ValidacionMetodoPago() {
	var suma = parseFloat(
		$('#add-payment input[name="balance_gift_card"]').val(),
	);
	if (isNaN(suma)) {
		suma = 0;
	}
	var montoTotal = parseFloat($("#grand-total").text().replace(/,/g, ""));
	if (isNaN(montoTotal)) {
		montoTotal = 0;
	}
	var hasMontoCambio = $("#montoCambio").length > 0;
	var arrayID = [];
	$("#html_montos_metodos_de_pago")
		.find("input")
		.each(function () {
			if (
				$(this).attr("id") !== "numeroCheque" &&
				$(this).attr("id") !== "montoCambio"
			) {
				const monto = parseFloat($(this).val());
				suma = suma + (isNaN(monto) ? 0 : monto);
			}
			arrayID.push($(this).attr("id"));
		});
	var total_balance = suma;
	var currentTc = (typeof tc !== "undefined" && tc > 0) ? tc : (parseFloat($("#tc").text()) || 1);
	var totalus = currentTc > 0 ? (total_balance / currentTc) : 0;
	$('input[name="paying_amount"]').val(total_balance.toFixed(2));
	$('input[name="paying_amount_us"]').val(totalus.toFixed(2));
	$("#monto_total_pagado").val(total_balance.toFixed(2));

	if (hasMontoCambio) {
		let diferencia = suma - montoTotal;
		if (diferencia <= 0) {
			diferencia = 0;
		}
		suma = suma - diferencia;
		$("#montoCambio").val(parseFloat(diferencia).toFixed(2));
		$("#change").text(parseFloat(total_balance - montoTotal > 0 ? total_balance - montoTotal : 0).toFixed(2));
	} else {
		$("#change").text("0.00");
	}
	if (Math.abs(suma - montoTotal) < 0.001) {
		// Todo correcto
		guardarMetodosPagos();
		arrayID.forEach((x) => {
			$("#" + x).removeClass("is-invalid");
			$("#" + x).addClass("is-valid");
		});
		if ($("input[name='bandera_factura_hidden']").val() == 1 || $("input[name='bandera_factura_hidden']").val() == true) {
			$("#segundoTabContinue").removeClass("disabled noselect");
			$('#myTab a[href="#segundoTab"]').removeClass("disabled noselect");
		}
		$("#submit-btn").removeClass("disabled noselect");
	} else {
		// Todo incorrecto
		arrayID.forEach((x) => {
			$("#" + x).removeClass("is-valid");
			$("#" + x).addClass("is-invalid");
		});
		if ($("input[name='bandera_factura_hidden']").val() == 1 || $("input[name='bandera_factura_hidden']").val() == true) {
			$("#segundoTabContinue").addClass("disabled noselect");
			$('#myTab a[href="#segundoTab"]').addClass("disabled noselect");
		}
		if ($('.tab-pane#primerTab').hasClass('show') || !$('#myTab').length) {
			$("#submit-btn").addClass("disabled noselect");
		}
	}
}

function guardarMetodosPagos() {
	$('input[name="monto_efectivo"]').val($("#montoEfectivo").val() || "");
	$('input[name="monto_tarjeta"]').val($("#montoTarjeta").val() || "");
	$('input[name="monto_cheque"]').val($("#montoCheque").val() || "");
	$('input[name="monto_vale"]').val($("#montoVale").val() || "");
	$('input[name="monto_otros"]').val($("#montoOtros").val() || "");
	$('input[name="monto_pago_posterior"]').val($("#montoPagoPosterior").val() || "");
	$('input[name="monto_transferencia_bancaria"]').val(
		$("#montoTransferenciaBancaria").val() || "",
	);
	$('input[name="monto_deposito"]').val($("#montoDepositoCuenta").val() || "");
	$('input[name="monto_swift"]').val($("#montoSwift").val() || "");
	$('input[name="monto_cambio"]').val($("#montoCambio").val() || "");

	$('input[name="monto_canal_pago"]').val($("#montoCanalPago").val() || "");
	$('input[name="monto_billetera"]').val($("#montoBilleteraMovil").val() || "");
	$('input[name="monto_pago_online"]').val($("#montoPagoOnline").val() || "");
	$('input[name="monto_debito_automatico"]').val(
		$("#montoDebitoAutomatico").val() || "",
	);
}

function getMontoInicialAutoFill() {
	var autoFill = $('#pos_setting_auto_fill_amount').val();
	if (autoFill == '1' || autoFill === undefined) {
		var total = parseFloat($('#grand-total').text().replace(/,/g, ''));
		return isNaN(total) ? '0' : total.toFixed(2);
	}
	return '0';
}

function appendMontoItemHtml(label, inputId) {
	if ($("#" + inputId).length > 0) return "";
	let initialVal = getMontoInicialAutoFill();
	return `
		<div class="payment-list-item">
			<div class="row">
				<div class="col-md-12 form-group mb-0">
					<label>${label}</label>
					<input id="${inputId}" class="form-control payment-list-input" oninput="ValidacionMetodoPago()" onkeyup="ValidacionMetodoPago()" onclick="this.select()" onfocus="this.select()" type="number" step="0.01" min="0" max="1000000" value="${initialVal}"/>
				</div>
			</div>
		</div>`;
}

function MPtarjeta() {
	$("#MP_tarjeta").show();
	let html = appendMontoItemHtml("Monto Tarjeta", "montoTarjeta");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	$("#number_card").prop("required", true);
	ValidacionMetodoPago();
}

function MPgiftCard() {
	giftCard();
	ValidacionMetodoPago();
}

function MPcheque() {
	$("#MP_cheque").show();
	let html = appendMontoItemHtml("Monto Cheque", "montoCheque");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPefectivo() {
	if ($("#montoEfectivo").length === 0) {
		let initialVal = getMontoInicialAutoFill();
		let html = `
		<div class="payment-list-item">
			<div class="row">
				<div class="col-md-7 form-group mb-0">
					<label>Monto Efectivo</label>
					<input id="montoEfectivo" class="form-control payment-list-input" oninput="ValidacionMetodoPago()" onkeyup="ValidacionMetodoPago();" onclick="this.select()" onfocus="this.select()" type="number" step="0.01" min="0" max="1000000" value="${initialVal}"/>
				</div>
				<div class="col-md-5 form-group mb-0">
					<label>Monto Cambio</label>
					<input readonly id="montoCambio" class="form-control payment-list-input" type="number" step="0.01" min="0" max="1000000" value="0" />
				</div>
			</div>
		</div>`;
		$("#html_montos_metodos_de_pago").append(html);
	}
	ValidacionMetodoPago();
}

function MPvale() {
	let html = appendMontoItemHtml("Monto Vale", "montoVale");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPotros() {
	let html = appendMontoItemHtml("Monto Otros", "montoOtros");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPpagoPosterior() {
	let html = appendMontoItemHtml("Monto Pago Posterior", "montoPagoPosterior");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPtransferenciaBancaria() {
	let html = appendMontoItemHtml(
		"Monto Transferencia Bancaria",
		"montoTransferenciaBancaria",
	);
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPdepositoCuenta() {
	let html = appendMontoItemHtml("Monto Dep. en Cuenta", "montoDepositoCuenta");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPtransferenciaSwift() {
	let html = appendMontoItemHtml("Monto Transferencia Swift", "montoSwift");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPcanalPago() {
	let html = appendMontoItemHtml("Monto Canal de Pago", "montoCanalPago");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPbilleteraMovil() {
	let html = appendMontoItemHtml(
		"Monto Billetera Móvil",
		"montoBilleteraMovil",
	);
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPpagoOnline() {
	let html = appendMontoItemHtml("Monto Pago Online", "montoPagoOnline");
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

function MPdebitoAutomatico() {
	let html = appendMontoItemHtml(
		"Monto Débito Automático",
		"montoDebitoAutomatico",
	);
	if (html) $("#html_montos_metodos_de_pago").append(html);
	ValidacionMetodoPago();
}

window.MPtarjeta = MPtarjeta;
window.MPgiftCard = MPgiftCard;
window.MPcheque = MPcheque;
window.MPefectivo = MPefectivo;
window.MPvale = MPvale;
window.MPotros = MPotros;
window.MPpagoPosterior = MPpagoPosterior;
window.MPtransferenciaBancaria = MPtransferenciaBancaria;
window.MPdepositoCuenta = MPdepositoCuenta;
window.MPtransferenciaSwift = MPtransferenciaSwift;
window.MPcanalPago = MPcanalPago;
window.MPbilleteraMovil = MPbilleteraMovil;
window.MPpagoOnline = MPpagoOnline;
window.MPdebitoAutomatico = MPdebitoAutomatico;
window.ValidacionMetodoPago = ValidacionMetodoPago;
window.metodosPago = metodosPago;
window.aplicarPagoMultiple = aplicarPagoMultiple;
