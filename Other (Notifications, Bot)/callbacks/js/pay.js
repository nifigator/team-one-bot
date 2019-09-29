$(".pay_pass_group").hide();
$(".pay_cards_group").hide();

$('#pay_password').tooltip({
	animation: true,
	placement: 'bottom',
	trigger: 'focus',
	title: 'Укажите пароль, если хотите загрузить список ранее использованных карт'
});

$('#pay_login').change(function(){
	isError("#pay_login", 1, "<");
});
$('#pay_sum').change(function(){
	isError("#pay_sum", 2, "<");
});

$('#pay_sum').on('keyup', function () {
	var sum = $(this).val();
	$('#real_sum').html('К зачислению: ' + (sum - (sum / 100 * 3)) + ' руб.');
});

$('#pay_password').change(function(){
	isError("#pay_password", 1, "<");
});
$('#pay_card').change(function(){
	$("#pay_card").parent().removeClass("has-success");
	if ($("#pay_card :selected").val() != 0)
		$("#pay_card").parent().addClass("has-success");
});

$('#pay_pass_group_open').click(function(e) {
	$("#pay_pass_group_open").hide();
	$("#pay_cards_block").removeClass("text-center");
	$(".pay_pass_group").show();
});

$('#pay_pass_group_close').click(function(e) {
	$(".pay_pass_group").hide();
	$("#pay_cards_block").addClass("text-center");
	$("#pay_pass_group_open").show();
});

$('#pay_cards_load').click(function(e) {
	
	var errors = 0;
		
	if (isError("#pay_login", 1, "<"))
		errors++;
	if (isError("#pay_password", 1, "<"))
		errors++;
	
	if (errors == 0){
		
		$("#pay_password").parent().removeClass("has-success");
		$("#pay_password").prop("disabled", true);
		$("#pay_cards_load").addClass("disabled");
		
		var request = $.ajax({
			type: "GET",
			url: "./js/jquery.md5.js",
			dataType: "script",
			cache: true
		});
		request.done(function(msg) {
			var Login = $("#pay_login").val();
			var Hash = $.md5(Login+$("#pay_password").val()); 
			
			$.getJSON("./sberbank_cards.php", {login: Login, hash: Hash}, function(res){
				if (res.code == 0){
					$("#pay_card").html('<option value="0" selected>Новая карта</option>');
					
					for (var i in res.cards)
						$("#pay_card").append('<option value="'+res.cards[i].id+'">'+res.cards[i].card+'</option>');

					$(".pay_pass_group").hide();
					$(".pay_cards_group").show();
				} else {
					$("#pay_password").prop('disabled', false);
					$("#pay_cards_load").removeClass("disabled");
					alert(res.error); 
				}
			});
			
		});
		request.fail(function(jqXHR, textStatus) {
			alert('Не удалось загрузить скрипт для вычисления md5-хэша!');
		});
		
	}
	return false;
});

$('#pay_submit').click(function(e) {
	var errors = 0;
		
	if (isError("#pay_login", 1, "<"))
		errors++;
	if (isError("#pay_sum", 2, "<"))
		errors++;
	
	if (errors > 0)
		return;
			
	var Login = $("#pay_login").val();
	var Sum = $("#pay_sum").val();
	var Card = $("#pay_card :selected").val();
		
	$("#pay").modal('hide');
	
	if (Card == 0)
		location.href = "//cifra.taxi/sberbank_pay.php?login="+Login+"&sum="+Sum;
	else
		location.href = "//cifra.taxi/sberbank_pay.php?login="+Login+"&sum="+Sum+"&card="+Card;
});