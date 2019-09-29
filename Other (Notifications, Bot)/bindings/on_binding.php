<?php
	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	
	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 3)
		exit('Access denied!');
	
	$phone = $_GET["phone"];
		
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	utils_log("Включение связки пользователя ".$UID.": ".$phone." -> ".$GUID.", ".$GATE);
	
	if (strlen($phone) == 11){
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$GATE = mysql_real_escape_string($GATE);
			$GUID = mysql_real_escape_string($GUID);
			$phone = mysql_real_escape_string($phone);

			$query = 'UPDATE `bindings` SET `allowed` = 1, `last_use` = NOW() WHERE `user_id` = '.$UID.' AND `phone` = "'.$phone.'" AND `guid` = "'.$GUID.'" AND `gate` = "'.$GATE.'" AND `confirmed` = 1';
			$data = db_query($query);
			if (!($data < 0)) {
				if (mysql_affected_rows() == 1) {
					utils_log("Cвязка ".$phone." c ".$GUID." включена.");
					$res["msg"] = "Связка успешно включена!";
					$res["code"] = 0;
				} else {
					utils_log("Ни одна связка не была включена!");
					$res["msg"] = "Ни одна связка не была включена!";
				}
			} else {
				utils_log("Ошибка при включении связки:", $data);
				$res["msg"] = "Ошибка при включении номера ".$phone."!";
			}
		} else  {
			utils_log("Ошибка подключения к БД:", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
	} else {
		utils_log("Номер телефона некорректный!");
		$res["msg"] = "Номер телефона некорректный!";
	}
		
	echo json_encode($res);
?>