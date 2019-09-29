<?php
	//error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	
	$phone = $_GET["phone"];
	$text = $_GET["text"];
	$btns = $_GET["btns"];
	$priority = (int) $_GET["priority"];
		
	require_once "/var/www/html/utils.php";
	require_once "/var/www/html/db_utils.php";
	
	utils_log("Уведомление для ".$phone.", приоритет ".$priority.": ".$text);
		
	if ((strlen($phone) == 11) && ($text != "")) {
		$f = db_connect("localhost", "gates", "root", "test@123");
		if ($f == 0){
			$phone = mysql_real_escape_string($phone);
			$phone[0] = "8";

			$query = 'SELECT `id`,`gate`,`guid` FROM `bindings` WHERE `phone` = "'.$phone.'" AND `confirmed` = 1 AND `allowed` = 1 ORDER BY `last_use` DESC LIMIT 1';
			$data = db_query($query);
			if (!($data < 0)){
				if (mysql_num_rows($data) == 1) {
					$binding = mysql_fetch_array($data, MYSQL_ASSOC);
					switch ($binding["gate"]) {
						case "vb":
							utils_log("Отправка сообщения в Viber.");
							$s = file_get_contents("//gates/vb/out.php?guid=".urlencode(trim($binding["guid"]))."&text=".urlencode(trim($text))."&btns=".urlencode($btns));
							$r = json_decode($s, true);
							if ($r) {
								if ($r["code"] == 0) {
									utils_log("Сообщение отправленно в Viber.");
									$res["code"] = 0;
									$res["msg"] = "Сообщение отправленно в Viber.";
								} else
									utils_log("Ошибка при отправке сообщения в Viber!");
							} else 
								utils_log("Ошибка при отправке сообщения в Viber!");
							break;
						case "tm":
							utils_log("Отправка сообщения в Telegram.");
							$s = file_get_contents("//gates/tm/out.php?guid=".urlencode(trim($binding["guid"]))."&text=".urlencode(trim($text))."&btns=".urlencode($btns));
							$r = json_decode($s, true);
							if ($r) {
								if ($r["code"] == 0) {
									utils_log("Сообщение отправленно в Telegram.");
									$res["code"] = 0;
									$res["msg"] = "Сообщение отправленно в Telegram.";
								} else
									utils_log("Ошибка при отправке сообщения в Telegram!");
							} else 
								utils_log("Ошибка при отправке сообщения в Telegram!");
							break;
						case "vk":
							utils_log("Отправка сообщения во ВКонтакте.");
							$s = file_get_contents("//gates/vk/out.php?guid=".urlencode(trim($binding["guid"]))."&text=".urlencode(trim($text))."&btns=".urlencode($btns));
							$r = json_decode($s, true);
							if ($r) {
								if ($r["code"] == 0) {
									utils_log("Сообщение отправленно в ВКонтакте.");
									$res["code"] = 0;
									$res["msg"] = "Сообщение отправленно в ВКонтакте.";
								} else
									utils_log("Ошибка при отправке сообщения в ВКонтакте!");
							} else 
								utils_log("Ошибка при отправке сообщения в ВКонтакте!");
							break;
						case "ok":
							utils_log("Отправка сообщения в Одноклассники.");
							$s = file_get_contents("//gates/ok/out.php?guid=".urlencode(trim($binding["guid"]))."&text=".urlencode(trim($text))."&btns=".urlencode($btns));
							$r = json_decode($s, true);
							if ($r) {
								if ($r["code"] == 0) {
									utils_log("Сообщение отправленно в Одноклассники.");
									$res["code"] = 0;
									$res["msg"] = "Сообщение отправленно в Одноклассники.";
								} else
									utils_log("Ошибка при отправке сообщения в Одноклассники!");
							} else 
								utils_log("Ошибка при отправке сообщения в Одноклассники!");
							break;
						case "fb":
							utils_log("Отправка сообщения в Facebook.");
							break;
						default:
							utils_log("Неизвестный шлюз: ".$binding["gate"].". Отправка невозможна!");
					}
				} else {
					utils_log("Не найдено ни одной связки!");
					$res["msg"] = "Не найдено ни одной связки!";
				}
			} else {
				utils_log("Ошибка при определении связки:", $data);
				$res["msg"] = "Ошибка при определении связки!";
			}
		} else {
			utils_log("Ошибка подключения к БД:", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
		
		if ($res["code"] != 0) { //нет связки или произошла ошибка
			switch ($priority) {
				case 0:
					utils_log("Сообщение не было отправленно!");
					break;
				case 1:
					utils_log("Сообщение не было отправленно!");
					break;
				case 2:
					utils_log("Отправка sms.");
					require_once "/var/www/html/api/sms.php";
					if (sendAlphaSMS($phone, $text)) {
						utils_log("Сообщение отправленно как sms.");
						$res["code"] = 0;
						$res["msg"] = "Сообщение отправленно как sms.";
					} else
						utils_log("Ошибка при отправке sms!");
					break;
				default:
					utils_log("Неизвестный приоритет: ".$priority.". Отправка невозможна!");
			}
		}
		
	} else{
		utils_log("Переданы не все обязательные параметры!");
		$res["msg"] = "Переданы не все обязательные параметры!";
	}
		
	echo json_encode($res);
?>