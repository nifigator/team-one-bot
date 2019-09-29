<?php
	//error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	$res["user"] = [];
	
	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 2)
		exit('Access denied!');
	
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	utils_log("Запрос профиля пользователя ".$GUID.", ".$GATE);
		
	if (($GUID != "") && ($GATE != "")) {
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$GATE = mysql_real_escape_string($GATE);
			$GUID = mysql_real_escape_string($GUID);
			
			$query = 'SELECT `user_id`,`phone`,`confirmed`,`code`,`voted_order` FROM `bindings` WHERE `guid` = "'.$GUID.'" AND `gate` = "'.$GATE.'"';
			$data = db_query($query);
			if (!($data < 0)){
				if (mysql_num_rows($data) > 0){
					$binding = mysql_fetch_array($data, MYSQL_ASSOC);
					
					$query = 'SELECT `place`,`step`,`blocked`,`last_use`,`last_order`,`last_text` FROM `users` WHERE `id` = '.$binding["user_id"];
					$data = db_query($query);
					if (!($data < 0)){
						if (mysql_num_rows($data) > 0){
							$user = mysql_fetch_array($data, MYSQL_ASSOC);
							
							$res["code"] = 0;
							$res["msg"] = "Успешно!";
							
							$res["user"]["id"] = $binding["user_id"];
							$res["user"]["place"] = $user["place"];
							$res["user"]["phone"] = $binding["phone"];
							$res["user"]["confirmed"] = $binding["confirmed"];
							$res["user"]["step"] = $user["step"];
							$res["user"]["code"] = $binding["code"];
							$res["user"]["last_use"] = $user["last_use"];
							$res["user"]["last_order"] = $user["last_order"];
							$res["user"]["voted_order"] = $binding["voted_order"];
							$res["user"]["last_text"] = $user["last_text"];
							$res["user"]["blocked"] = $user["blocked"];
							
							if ($res["user"]["voted_order"] != "")
								$res["user"]["step"] = 5;
							
						} else {
							utils_log("Пользователя не существует!");
							$res["msg"] = "Пользователя ".$GUID.", ".$GATE." не существует!";
						}
					} else {
						utils_log("Ошибка существования пользователя:", $data);	
						$res["msg"] = "Ошибка существования пользователя!";
					}
					
				} else {
					utils_log("Связки не существует!");
					$res["msg"] = "Связки ".$GUID.", ".$GATE." не существует!";
				}
			} else {
				utils_log("Ошибка существования связки:", $data);	
				$res["msg"] = "Ошибка существования связки!";
			}
		} else {
			utils_log("Ошибка подключения к БД:", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
	} else{
		utils_log("Переданы не все обязательные параметры!");
		$res["msg"] = "Переданы не все обязательные параметры!";
	}
		
	echo json_encode($res);
?>