var code_sended = false;

$('.subscribe_code_group').hide();
$('.subscribe_state_group').hide();

$('#subscribe_phone').change(function(){
	isError('#subscribe_phone', 11, "<");
});

$('#subscribe_code').change(function(){
	isError('#subscribe_code', 4, "<");
});

$('#subscribe_code').change(function(){
	isError('#subscribe_code', 4, "<");
});

$('input:radio[name="subscribe_state"]').change(function(){
	$('.subscribe_state_group').removeClass('has-error').addClass('has-success');
});

$('#subscribe_submit').click(function(e) {
	if (!isError('#subscribe_phone', 11, "<")){

		var Phone = $('#subscribe_phone').val();
		
		if (!code_sended) {
			$.getJSON('./subscribe_code.php', {phone: Phone}, function(res){
				if (res.code == 0) {
					code_sended = true;
					$('.subscribe_code_group').show();
					$('.subscribe_state_group').show();
					$('#subscribe_submit').html('Продолжить');
				}
				alert(res.error); 
			});
		} else if (!isError('#subscribe_code', 4, "<")) {
			var Code = $('#subscribe_code').val();
			var State = $('input:radio[name="subscribe_state"]:checked').val();

			if (typeof(State) === "undefined"){
				$('.subscribe_state_group').removeClass('has-error').addClass('has-error');
				alert('Укажите "Включить" или "Отключить" уведомления!');
				return;
			}
			
			$("#subscribe").modal('hide');
			
			$.getJSON('./subscribe_change.php', {phone: Phone, code: Code, state: State}, function(res){
				if (res.code == 0){
					code_sended = false;
					$('#subscribe_code').val('')
					$('#subscribe_code').parent().removeClass("has-error has-success");
					$('.subscribe_state_group').removeClass('has-error has-success');
					$('.subscribe_code_group').hide();
					$('.subscribe_state_group').hide();
					var text = '<p>'+res.error+'</p>';
				} else {
					var text = '<p>Произошла ошибка: "' + res.error + '".</p>';
					text += '<p>Обратитесь в <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#feedback">Службу поддержки</a>: <a href="tel:+79333013301">8 93 33 01 33 01</a></p>';
				}
				ShowText('Sms-уведомления', text);
			});
		}
	}
});