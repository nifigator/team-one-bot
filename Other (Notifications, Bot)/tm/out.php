<?php
	require_once "/var/www/html/gates/tm/auth_data.php";
	require_once "/var/www/html/gates/utils.php";
	
	$res["code"] = -1;
	
	$guid = $_GET["guid"];
	$text = $_GET["text"];
	$btns = $_GET["btns"];
	
	$text = HighlightPhones($text);
	
	utils_log('Отправка tm-сообщения '.$guid.': '.$text);
	if (($guid != "") && ($text != "")) {
		$link = 'https://api.telegram.org/bot'.$TOKEN.'/sendMessage?';
		$prms = [
			'chat_id' => $guid, 
			'text' => $text,
			'parse_mode' => 'HTML'
		];
	
		if ($btns != "")
			if ($btns != "clear") {
				$btns = explode(",", $btns);
				if (count($btns)) {
					$keyboard = [
						'keyboard' => [],
						'resize_keyboard' => true
					];

					foreach ($btns as $btn) {
						$ext = explode(":", $btn);
						switch ($ext[1]) {
							case "1":
								$keyboard['keyboard'][] = [['text' => $ext[0], 'request_contact' => true]];
								break;
							case "2":
								$keyboard['keyboard'][] = [['text' => $ext[0], 'request_location' => true]];
								break;
							default:
								$keyboard['keyboard'][] = [$ext[0]];
						}
					}
					$prms['reply_markup'] = $keyboard;
				}
			} else
				$prms['reply_markup'] = ['remove_keyboard' => true];
		
		$data_string = json_encode($prms);
		
		//utils_log($data_string);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($data_string))
		); 
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_VERBOSE, True);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
		
		$output = curl_exec($ch);
		utils_log('Результат запроса: '.$output);
		
		if ($output !== false) {
			$obj = json_decode($output);
			if ($obj->ok)
				$res["code"] = 0;
		}
		curl_close($ch);
	}
	
	echo json_encode($res);
?>
