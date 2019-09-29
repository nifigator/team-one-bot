$('#order_addr').typeahead({
	autoSelect: true,
	fitToElement: true,
	source: function (query, process) {
		return $.get('//cifra.taxi/address.php', {query: query, place: ""}, function (data) {
			return process(data);
		}, 'json');
	},
	matcher: function(){
		return true;
		}
	}
); 

function getParams(selector, k1, k2){
	var prms = "";
	for (var i = k1; i <= k2; i++)
		if ($(selector + ' [value = "'+i+'"]').prop("selected"))
			prms += '1';
		else
			prms += '0';
	return prms;
}

function isError(selector, length, condition){
	$(selector).parent().removeClass("has-error has-success");
	var Value = $(selector).val();
	var Error = false;
	switch(condition){
		case '<':
			Error = (Value.length < length);
			break;
		case '<=':
			Error = (Value.length <= length);
			break;
		case '>':
			Error = (Value.length > length);
			break;
		case '>=':
			Error = (Value.length >= length);
			break;
		case '==':
			Error = (Value.length == length);
			break;
		case '!=':
			Error = (Value.length != length);
			break;
	}
	if (Error)
		$(selector).parent().addClass("has-error");
	else
		$(selector).parent().addClass("has-success");
	return Error;
}

$('#order_addr').change(function(){
	isError("#order_addr", 3, "<");
});
$('#order_phone').change(function(){
	isError("#order_phone", 11, "!=");
});
$("#order_params").change(function() {
	var Params = getParams('#order_params', 1, 2);
	if ((Params.charAt(0) != "1") && (Params.charAt(1) != "1"))
		$("#order_params").parent().parent().addClass("has-error");
	else
		$("#order_params").parent().parent().removeClass("has-error").addClass("has-success");
});

$('#order_submit').click(function(e) {
	var errors = 0;

	var Params = getParams('#order_params', 1, 2);
	if ((Params.charAt(0) != "1") && (Params.charAt(1) != "1")){
		$("#order_params").parent().parent().addClass("has-error");
		$("#order_params").focus();
		errors++;
	}
	if (isError("#order_phone", 11, "!=")){
		$("#order_phone").focus();
		errors++;
	}
	if (isError("#order_addr", 3, "<")){
		$("#order_addr").focus();
		errors++;
	}
		
	if (errors >0)
		return;
			
	var From = "cifra.taxi";
	
	var Addr = $("#order_addr").val();
	var Phone = $("#order_phone").val();
	Params += getParams('#order_params', 3, 20);
		
	$("#order").modal('hide');
	
	$.getJSON("../order.php", {from: From, addr: Addr, phone: Phone, params: Params, vk_id: 0}, function(res){
		if ((res.code == 0) || (res.code >= 12)){
			var text = '<h4>'+res.error+'</h4>';
		} else {
			var text = '<p>Произошла ошибка: "' + res.error + '".</p>';
			text += '<p>Обратитесь в <a data-dismiss="modal" data-toggle="modal" data-target="#feedback">Службу поддержки</a>: <a href="tel:+79333013301">8 93 33 01 33 01</a></p>';
		}
		ShowText('Заказ', text);
	});
});