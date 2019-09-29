<?php
	//error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";
	
	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 3)
		exit('Access denied!');
	
	$step = (int) $_GET["step"];
	
		
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	utils_log("Обновление шага ".$UID.": ".$step);
	
	if ($step >= 0){
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$text = "";
			if (isset($_GET["text"]))
				$text = mysql_real_escape_string($_GET["text"]);

			$query = 'UPDATE `users` SET `step` = '.$step.', `last_text` = "'.$text.'",`last_use` = NOW() WHERE `id` = '.$UID;
			$data = db_query($query);
			if (!($data < 0)){
				if (mysql_affected_rows() == 1){
					utils_log("Шаг изменен.");
					$res["msg"] = "Шаг изменен!";
					$res["code"] = 0;
				} else {
					utils_log("Шаг не был обновлен!");
					$res["msg"] = "Шаг не был обновлен!";
				}
			} else {
				utils_log("Ошибка при обновлении шага:", $data);
				$res["msg"] = "Ошибка при обновлении шага!";
			}
		} else  {
			utils_log("Ошибка подключения к БД:", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
	} else {
		utils_log("Некорректный шаг!");
		$res["msg"] = "Некорректный шаг!";
	}
		
	echo json_encode($res);
?>