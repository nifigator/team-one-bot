<?php
	//error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	
	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 3)
		exit('Access denied!');
	
	$phone = $_GET["phone"];
	$code = $_GET["code"];
		
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	utils_log("Подтверждение связки пользователя ".$UID.": ".$phone." -> ".$GUID.", ".$GATE);
	
	if (strlen($phone) == 11 && strlen($code) == 4){
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$GATE = mysql_real_escape_string($GATE);
			$GUID = mysql_real_escape_string($GUID);
			$phone = mysql_real_escape_string($phone);
			$code = mysql_real_escape_string($code);
			//нельзя подтвердить уже подтвержденную связку
			$query = 'UPDATE `bindings` SET `confirmed`= 1, `code` = "", `last_use` = NOW() WHERE `user_id` = '.$UID.' AND `phone` = "'.$phone.'" AND `guid` = "'.$GUID.'" AND `gate` = "'.$GATE.'" AND `code` = "'.$code.'" AND `confirmed` = 0';
			$data = db_query($query);
			if (!($data < 0)){
				if (mysql_affected_rows() == 1){
					utils_log("Cвязка ".$phone." c ".$GUID." подтверждена.");
					$res["msg"] = "Номер ".$phone." успешно подтвержден!";
					$res["code"] = 0;
				} else {
					utils_log("Ни одна связка не была подтверждена!");
					$res["msg"] = "Номер ".$phone." не был подтвержден!";
				}
			} else {
				utils_log("Ошибка при подтверждении связки:", $data);
				$res["msg"] = "Ошибка при подтверждении номера ".$phone."!";
			}
		} else  {
			utils_log("Ошибка подключения к БД:", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
	} else {
		utils_log("Номер телефона или код подтверждения некорректны!");
		$res["msg"] = "Номер телефона или код подтверждения некорректны!";
	}
		
	echo json_encode($res);
?>