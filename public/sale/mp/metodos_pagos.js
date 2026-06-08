// onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
window.pagoMultipleActivo = false;

$('select[name="paid_by_id_select"]').on("change", function () {
	if (window.pagoMultipleActivo) {
		return;
	}
	$("#number_card").prop("required", false);
	refrescarMontos();
	hideDatosInputTexto();
	alertaTablaItem_o_Empleado_vacio();
	var id = $(this).val();
	$(".payment-form").off("submit");
	if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
	if (timerIntervalId) clearInterval(timerIntervalId);
	if (id == 27) {
		giftCard();
	}
	if (id == 2) {
		creditCard();
	}
	if (id == 3) {
		cheque();
	}
	if (id == 1) {
		unblockAmounts();
	}
	if (id == 8) {
		deposits();
	}

	metodosPago($(this));
});

function metodosPago(dom) {
	return new Promise((resolve) => {
		var descripcion = $("option:selected", dom).data("descripcion");
		const MPpagos = [
			{ metodo: "TARJETA", id: "MPtarjeta" },
			{ metodo: "GIFT", id: "MPgiftCard" },
			{ metodo: "CHEQUE", id: "MPcheque" },
			{ metodo: "VALE", id: "MPvale" },
			{ metodo: "OTRO", id: "MPotros" },
			{ metodo: "PAGO POSTERIOR", id: "MPpagoPosterior" },
			{ metodo: "TRANSFERENCIA BANCARIA", id: "MPtransferenciaBancaria" },
			{ metodo: "DEBITO AUTOMATICO", id: "MPdebitoAutomatico" },
			{ metodo: "DEPOSITO", id: "MPdepositoCuenta" },
			{ metodo: "SWIFT", id: "MPtransferenciaSwift" },
			{ metodo: "CANAL", id: "MPcanalPago" },
			{ metodo: "BILLETERA", id: "MPbilleteraMovil" },
			{ metodo: "ONLINE", id: "MPpagoOnline" },
			{ metodo: "EFECTIVO", id: "MPefectivo" },
		];
		MPpagos.map((x) => {
			if (descripcion.includes(x.metodo)) {
				window[x.id]();
			} else {
				$("#" + x.id).hide();
			}
		});
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
	$(".selectpicker").selectpicker("refresh");
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

	$(".selectpicker").selectpicker("refresh");
	ValidacionMetodoPago();
}

window.activarPagoMultiple = function () {
	window.pagoMultipleActivo = true;
	const $paidBySelect = $('select[name="paid_by_id_select"]');
	$paidBySelect.data("mp-original-disabled", $paidBySelect.prop("disabled"));
	$paidBySelect.prop("disabled", true);
	$("#multiple-pay-selector").show();
	cargarOpcionesPagoMultiple();
	$("#multi_pay_method_1").val("");
	$("#multi_pay_method_2").val("");
	$("#multi_pay_method_3").val("");
	$(".selectpicker").selectpicker("refresh");
	aplicarPagoMultiple();
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
	$(".selectpicker").selectpicker("refresh");
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
	var montoTotal = parseFloat($("#grand-total").text());
	var montoCambio = false;
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
			if ($(this).attr("id") !== "montoCambio") {
				montoCambio = true;
			}
			arrayID.push($(this).attr("id"));
		});
	var total_balance = suma;
	var totalus = total_balance / tc;
	$('input[name="paying_amount"]').val(total_balance.toFixed(2));
	$('input[name="paying_amount_us"]').val(totalus.toFixed(2));
	$("#monto_total_pagado").val(total_balance.toFixed(2));

	if (montoCambio) {
		let diferencia = suma - montoTotal;
		if (diferencia <= 0) {
			diferencia = 0;
		}
		suma = suma - diferencia;
		$("#montoCambio").val(parseFloat(diferencia).toFixed(2));
		$("#change").text(parseFloat(total_balance - montoTotal).toFixed(2));
	}

	if (suma === montoTotal) {
		// Todo correcto
		arrayID.map((x) => {
			$("#" + x).removeClass("is-invalid");
			$("#" + x).addClass("is-valid");
			if ($("input[name='bandera_factura_hidden']").val() == true) {
				$("#segundoTabContinue").removeClass("disabled noselect");
				$('#myTab a[href="#segundoTab"]').removeClass("disabled noselect");
			}
			guardarMetodosPagos();
		});
	} else {
		// Todo incorrecto
		arrayID.map((x) => {
			$("#" + x).removeClass("is-valid");
			$("#" + x).addClass("is-invalid");
			if ($("input[name='bandera_factura_hidden']").val() == true) {
				$("#segundoTabContinue").addClass("disabled noselect");
				$('#myTab a[href="#segundoTab"]').addClass("disabled noselect");
			}
		});
	}
}

function guardarMetodosPagos() {
	$('input[name="monto_efectivo"]').val($("#montoEfectivo").val());
	$('input[name="monto_tarjeta"]').val($("#montoTarjeta").val());
	$('input[name="monto_cheque"]').val($("#montoCheque").val());
	$('input[name="monto_vale"]').val($("#montoVale").val());
	$('input[name="monto_otros"]').val($("#montoOtros").val());
	$('input[name="monto_pago_posterior"]').val($("#montoPagoPosterior").val());
	$('input[name="monto_transferencia_bancaria"]').val(
		$("#montoTransferenciaBancaria").val(),
	);
	$('input[name="monto_deposito"]').val($("#montoDepositoCuenta").val());
	$('input[name="monto_swift"]').val($("#montoSwift").val());
	$('input[name="monto_cambio"]').val($("#montoCambio").val());

	$('input[name="monto_canal_pago"]').val($("#montoCanalPago").val());
	$('input[name="monto_billetera"]').val($("#montoBilleteraMovil").val());
	$('input[name="monto_pago_online"]').val($("#montoPagoOnline").val());
	$('input[name="monto_debito_automatico"]').val(
		$("#montoDebitoAutomatico").val(),
	);
}

function appendMontoItemHtml(label, inputId) {
	return `
		<div class="payment-list-item">
			<div class="row">
				<div class="col-md-12 form-group mb-0">
					<label>${label}</label>
					<input id="${inputId}" class="form-control payment-list-input" onkeyup="ValidacionMetodoPago()" type="number" step="0.01" min="0" max="1000000" value="0"/>
				</div>
			</div>
		</div>`;
}

function MPtarjeta() {
	$("#MP_tarjeta").show();
	let html = appendMontoItemHtml("Monto Tarjeta", "montoTarjeta");
	$("#html_montos_metodos_de_pago").append(html);
	$("#number_card").prop("required", true);
}

function MPgiftCard() {
	giftCard();
}

function MPcheque() {
	$("#MP_cheque").show();
	let html = appendMontoItemHtml("Monto Cheque", "montoCheque");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPefectivo() {
	let html = `
	<div class="payment-list-item">
		<div class="row">
			<div class="col-md-7 form-group mb-0">
				<label>Monto Efectivo</label>
				<input id="montoEfectivo" class="form-control payment-list-input" onkeyup="ValidacionMetodoPago();" type="number" step="0.01" min="0" max="1000000" value="0"/>
			</div>
			<div class="col-md-5 form-group mb-0">
				<label>Monto Cambio</label>
				<input readonly id="montoCambio" class="form-control payment-list-input" type="number" step="0.01" min="0" max="1000000" value="0" />
			</div>
		</div>
	</div>`;
	$("#html_montos_metodos_de_pago").append(html);
}

function MPvale() {
	let html = appendMontoItemHtml("Monto Vale", "montoVale");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPotros() {
	let html = appendMontoItemHtml("Monto Otros", "montoOtros");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPpagoPosterior() {
	let html = appendMontoItemHtml("Monto Pago Posterior", "montoPagoPosterior");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPtransferenciaBancaria() {
	let html = appendMontoItemHtml(
		"Monto Transferencia Bancaria",
		"montoTransferenciaBancaria",
	);
	$("#html_montos_metodos_de_pago").append(html);
}

function MPdepositoCuenta() {
	let html = appendMontoItemHtml("Monto Dep. en Cuenta", "montoDepositoCuenta");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPtransferenciaSwift() {
	let html = appendMontoItemHtml("Monto Transferencia Swift", "montoSwift");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPcanalPago() {
	let html = appendMontoItemHtml("Monto Canal de Pago", "montoCanalPago");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPbilleteraMovil() {
	let html = appendMontoItemHtml(
		"Monto Billetera Móvil",
		"montoBilleteraMovil",
	);
	$("#html_montos_metodos_de_pago").append(html);
}

function MPpagoOnline() {
	let html = appendMontoItemHtml("Monto Pago Online", "montoPagoOnline");
	$("#html_montos_metodos_de_pago").append(html);
}

function MPdebitoAutomatico() {
	let html = appendMontoItemHtml(
		"Monto Débito Automático",
		"montoDebitoAutomatico",
	);
	$("#html_montos_metodos_de_pago").append(html);
}
