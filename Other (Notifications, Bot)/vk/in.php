<?php 
	require_once "/var/www/html/gates/team-one/utils.php";

	if (!isset($_REQUEST)) { 
		return; 
	}
	$input = file_get_contents('php://input');
		
	$data = json_decode($input);
	
	utils_log("INPUT:", $input);
	
	utils_log("VK IN:", $data);
	
	$text = "";
	//$contact = "";
	//$media = "";
	//$owner = 0;

	switch ($data->type) { 
		case 'confirmation':
			echo '6f941be2'; 
			utils_log('Подтверждение адреса');
			break; 
		case 'message_allow': //подписка на сообщения от сообщества
			echo('ok');
			$guid = $data->object->user_id;
			utils_log($guid." подписался на личные сообщения");
			break;	
		case 'message_new': //входящее сообщение
			echo('ok');	
			$guid = $data->object->user_id;
			$text = $data->object->body;
			utils_log($guid." прислал сообщение: ".$text);
			break;
		case 'message_deny': //запрет сообщений от сообщества
			echo('ok');
			$guid = $data->object->user_id;
			utils_log($guid." отписался от личных сообщений");
			break;
	}
		
	if ($text != "")
		file_get_contents('http://cifra.taxi/gates/team-one/bot.php?guid='.$guid.'&gate=vk&text='.urlencode($text).'&contact=&owner=0');

?> 