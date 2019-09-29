<?php 
	require_once "/var/www/html/gates/utils.php";

	if (!isset($_REQUEST)) { 
		return; 
	}
		
	$data = json_decode(file_get_contents('php://input'));
	
	utils_log("TM IN:", $data);
	
	$guid = $data->message->chat->id;
	$text = "";
	$contact = "";
	$media = "";
	
	if (isset($data->message->text)) {
		$text = $data->message->text;
		$owner = 0;
	}
	
	if (isset($data->message->contact)) {
		$contact = $data->message->contact->phone_number;
		$owner = $data->message->contact->user_id;
	}
	
	if (($text != "") || ($contact != ""))
		file_get_contents('https://team-one.kras.ru/gates/bot.php?guid='.$guid.'&gate=tm&text='.urlencode($text).'&contact='.$contact.'&owner='.$owner);
?> 