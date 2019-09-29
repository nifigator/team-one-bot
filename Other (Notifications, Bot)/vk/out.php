<?php
	$VK_KEY = '';
	
	require_once "/var/www/html/gates/team-one/utils.php";

	$res["code"] = -1;
	
	$guid = $_GET["guid"];
	$text = strip_tags($_GET["text"]);
	$btns = $_GET["btns"];
	
	utils_log('Отправка vk-сообщения '.$guid.': '.$text);
	if (($guid != "") && ($text != "")) {
		
		$link = 'https://api.vk.com/method/messages.send?access_token='.$VK_KEY;
		$prms = array( 
			'user_id' => $guid,
			'message' => $text,
			'access_token' => $VK_KEY, 
			'v' => '5.80'
		);
				
		if ($btns != "") {
			
			$keyboard = (object) [
				//'one_time' => true,
				'buttons' => array()
			];
			
			if ($btns != "clear") {
				$btns = explode(",", $btns);
				if (count($btns)) {
					foreach ($btns as $btn) {
						$ext = explode(":", $btn);
						$keyboard->buttons[] = array(
							(object) [
								"action" => (object) [ 
									"type" => "text",
									"label" => $ext[0]
								], 
								"color" => "primary" 
							]
						);
					}
				}
			}
			
			$prms['keyboard'] = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
		}
			
		$output = file_get_contents($link, false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($prms)
			)
		)));
		utils_log('Результат запроса: '.$output);
		
		if ($output !== false) {
			$obj = json_decode($output);
			if ($obj->response)
				$res["code"] = 0;
		}
		
	}
		
	echo json_encode($res);
?>
