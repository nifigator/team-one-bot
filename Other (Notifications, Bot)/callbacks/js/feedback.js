$('#feedback_name').change(function(){
	isError("#feedback_name", 2, "<");
});
$('#feedback_phone').change(function(){
	isError("#feedback_phone", 11, "!=");
});
$('#feedback_msg').change(function(){
	isError("#feedback_msg", 5, "<");
});
	
$('#feedback_submit').click(function(e) {
	var errors = 0;
		
	if (isError("#feedback_name", 2, "<"))
		errors++;
	if (isError("#feedback_phone", 11, "!="))
		errors++;
	if (isError("#feedback_msg", 5, "<"))
		errors++;
		
	if (errors >0)
		return;
			
	var Name = $("#feedback_name").val();
	var Phone = $("#feedback_phone").val();
	var Msg = $("#feedback_msg").val();
		
	$("#feedback").modal('hide');
	
	var Problem = "Обращение через обратную связь с сайта";
	var Descript = "Имя: " + Name + ", телефон: " + Phone + ", обращение: " + Msg;
		
	var request = $.ajax({
		url: "./mt/support/requests.php",
		data: {action: "new", type: "web", phone: Phone, problem: Problem, descript: Descript}
	});
	request.done(function(msg) {
		var text = '<p>Ваше обращение успешно принято!</p>';
		text += '<p>С Вами свяжутся, как только оно будет рассмотрено.</p>';
		ShowText('Служба поддержки', text);
	});
	request.fail(function(jqXHR, textStatus) {
		var text = '<p>Произошла ошибка!</p>';
		text += '<p>Позвоните в Службу поддержки по телефону: <a href="tel:+79333013301">8 93 33 01 33 01</a></p>';
		ShowText('Служба поддержки', text);
	});
});