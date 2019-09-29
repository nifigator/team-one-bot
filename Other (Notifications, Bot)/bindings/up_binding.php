<?php
	//error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	
	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 3)
		exit('Access denied!');
	
	$phone = $_GET["phone"];
	
	$place = "";
	if (isset($_GET["place"]))
		$place = $_GET["place"];
		
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	utils_log("Обновление связки пользователя ".$UID.": ".$phone." -> ".$GUID.", ".$GATE);
	
	if (strlen($phone) == 11){
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$GATE = mysql_real_escape_string($GATE);
			$GUID = mysql_real_escape_string($GUID);
			$phone = mysql_real_escape_string($phone);
			$place = mysql_real_escape_string($place);
			
			$query = 'UPDATE `bindings` SET `last_use` = NOW() WHERE `user_id` = '.$UID.' AND `phone` = "'.$phone.'" AND `guid` = "'.$GUID.'" AND `gate` = "'.$GATE.'"'; // AND `confirmed` = 1';
			$data = db_query($query);
			if (!($data < 0)) {
				if (mysql_affected_rows() == 1) {
					if ($place != "") {
						$query = 'UPDATE `users` SET `place` = "'.$place.'" WHERE `id` = '.$UID;
						$data = db_query($query);
						if (!($data < 0)) {
							if (mysql_affected_rows() == 1) {
								utils_log("Cвязка ".$phone." c ".$GUID." обновлена.");
								$res["msg"] = "Связка успешно обновлена!";
								$res["code"] = 0;
							} else {
								utils_log("Ни один пользователь не был обновлен!");
								$res["msg"] = "Ни один пользователь не был обновлен!";
							}
						} else {
							utils_log("Ошибка при обновлении города:", $data);
							$res["msg"] = "Ошибка при обновлении города ".$phone."!";
						}
					} else {
						utils_log("Cвязка ".$phone." c ".$GUID." обновлена.");
						$res["msg"] = "Связка успешно обновлена!";
						$res["code"] = 0;
					}
				} else {
					utils_log("Ни одна связка не была обновлена!");
					$res["msg"] = "Ни одна связка не была обновлена!";
				}
			} else {
				utils_log("Ошибка при обновлении связки:", $data);
				$res["msg"] = "Ошибка при обновлении номера ".$phone."!";
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