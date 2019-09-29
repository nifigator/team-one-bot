$('#reg_ref').tooltip({
	animation: true,
	placement: 'top',
	trigger: 'focus',
	title: 'Если Вы пришли по рекомендации нашего пользователя, укажите его логин и он получит бонус'
});

$('#reg_name').change(function(){
	isError("#reg_name", 10, "<");
});
$('#reg_phone').change(function(){
	isError("#reg_phone", 11, "!=");
});
$('#reg_auto').change(function(){
	isError("#reg_auto", 5, "<");
});
$('#reg_num').change(function(){
	isError("#reg_num", 6, "<");
});
$( "#reg_place" ).change(function() {
	$("#reg_place").parent().removeClass("has-error").addClass("has-success");
});
$( "#reg_insurance" ).change(function() {
	$("#reg_insurance").parent().removeClass("has-error").addClass("has-success");
});
$('#reg_ref').change(function(){
	if ($('#reg_ref').val() != "")
		$("#reg_ref").parent().removeClass("has-success").addClass("has-success");
	else
		$("#reg_ref").parent().removeClass("has-success");
});
$( "#reg_accept" ).change(function() {
	$("#reg_accept_group").removeClass("has-error has-success");
	if (!$("#reg_accept").prop('checked'))
		$("#reg_accept_group").addClass("has-error");
	else
		$("#reg_accept_group").addClass("has-success");
});

$('#reg_submit').click(function(e) {
	var errors = 0;
		
	if (isError("#reg_name", 10, "<"))
		errors++;
	if (isError("#reg_phone", 11, "!="))
		errors++;
	if (isError("#reg_auto", 5, "<"))
		errors++;
	if (isError("#reg_num", 6, "<"))
		errors++;
	if ($("#reg_place :selected").val() == "0"){
		$("#reg_place").parent().addClass("has-error");
		errors++;
	}
	if ($("#reg_insurance :selected").val() == "0"){
		$("#reg_insurance").parent().addClass("has-error");
		errors++;
	}
	if (!$("#reg_accept").prop('checked')){
		$("#reg_accept_group").removeClass("has-error has-success").addClass("has-error");
		errors++;
	}
		
	if (errors >0)
		return;
			
	var Name = $("#reg_name").val();
	var Phone = $("#reg_phone").val();
	var From = $("#reg_place :selected").val();
	var Car = $("#reg_auto").val();
	var Num = $("#reg_num").val();
	var Ref = $("#reg_ref").val();
	var Ins = $("#reg_insurance :selected").val();
	var Accept = $("#reg_accept").prop('checked');
		
	Name += ' (' + Ins+ ')';
	Car += ' ' + Num;

	//$("#reg").modal('hide');
		
	$.getJSON("../ct/registration.php", {name: Name, phone: Phone, from: From, car: Car, ref: Ref, frmt: "json"}, function(data){
		//console.log(JSON.stringify(data));
		if (data.result == "SUCCESS"){
			var text = '<p>Регистрация прошла успешно! ';
			text += 'Скоро на телефон '+Phone+' поступит смс с логином, паролем и ссылкой для загрузки программы. ';
			text += 'Если смс не приходит дольше 5 минут, обратитесь в <a data-dismiss="modal" data-toggle="modal" data-target="#feedback">Службу поддержки</a>: <a href="tel:+79333013301">8 93 33 01 33 01</a></p>';
			text += '<p>После получения логина и пароля, будьте на связи на указанном номере. ';
			text += 'Вам позвонят в течении 24 часов, чтобы договориться о собеседовании.</p>';
		} else {
			var text = '<p>Произошла ошибка: "' + data.result + '".</p>';
			text += '<p>Обратитесь в <a data-dismiss="modal" data-toggle="modal" data-target="#feedback">Службу поддержки</a>: <a href="tel:+79333013301">8 93 33 01 33 01</a></p>';
		}
		ShowText('Регистрация', text);
	});
});