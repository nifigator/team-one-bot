<?php
	error_reporting(0);

	$res["code"] = -1;
	$res["msg"] = "Неизвестная ошибка!";

	require_once "/var/www/html/gates/team-one/bindings/access.php";
	
	if ($ACCESS_RESULT < 2)
		exit('Access denied!');
	
	$phone = $_GET["phone"];
	$forced = (int) $_GET["forced"];
		
	require_once "/var/www/html/gates/team-one/utils.php";
	require_once "/var/www/html/gates/team-one/db_utils.php";
	
	if ($forced != 1)
		utils_log("Создание связки пользователя ".$UID.": ".$phone." -> ".$GUID.", ".$GATE);
	else
		utils_log("Принудительное создание связки пользователя ".$UID.": ".$phone." -> ".$GUID.", ".$GATE);

	if (strlen($phone) == 11){
		$f = db_connect("localhost", "team-one", "root", "test@123");
		if ($f == 0){
			$GATE = mysql_real_escape_string($GATE);
			$GUID = mysql_real_escape_string($GUID);
			$phone = mysql_real_escape_string($phone);

			utils_log("Поиск связок по номеру телефона...");
			$query = 'SELECT * FROM `bindings` WHERE `phone` = "'.$phone.'"';	
			$data = db_query($query);
			if (!($data < 0)){
				if (mysql_num_rows($data) == 0) { //1. Номер не зарегистрирован
					utils_log("Номер ".$phone." не зарегистрирован. Добавляем пользователя...");
					$query = 'INSERT INTO `users`(`create_date`,`gate`,`step`) VALUES (NOW(),"'.$GATE.'",0)';
					$data = db_query($query);
					if (!($data < 0)){
						$UID = mysql_insert_id();
						if ($UID > 0) {
							if ($ACCESS_TYPE == 2)
								$_SESSION['uid'] = $UID;
							else 
								$res["uid"] = $UID;
							utils_log("Добавляем связку...");
							
							if ($forced != 1) {
								$code = rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
								$query = 'INSERT INTO `bindings`(`create_date`,`user_id`,`phone`,`guid`,`gate`,`code`,`allowed`,`main`) VALUES (NOW(),'.$UID.',"'.$phone.'","'.$GUID.'","'.$GATE.'","'.$code.'",1,1)';
								$data = db_query($query);
								if (!($data < 0)){
									utils_log("Cвязка ".$phone." c ".$GUID." добавлена.");
									//require_once "/var/www/html/garant_sms.php";
									//SMSSend($phone, TextToURL("Код подтверждения: ".$code));
									utils_log("Выслан код:", $code);
									$res["verification"] = $code;
									$res["msg"] = "На номер ".$phone." выслано смс с кодом подтверждения.";
									$res["code"] = 0;
								} else {
									utils_log("Ошибка при добавлении связки:", $data);
									$res["msg"] = "Ошибка при добавлении номера!";
								}
							} else {
								$query = 'INSERT INTO `bindings`(`create_date`,`user_id`,`phone`,`guid`,`gate`,`code`,`allowed`,`main`,`confirmed`,`last_use`) VALUES (NOW(),'.$UID.',"'.$phone.'","'.$GUID.'","'.$GATE.'","",1,1,1,NOW())';
								$data = db_query($query);
								if (!($data < 0)){
									utils_log("Cвязка ".$phone." c ".$GUID." подтверждена.");
									$res["msg"] = "Номер ".$phone." успешно подтвержден!";
									$res["code"] = 0;
								} else {
									utils_log("Ошибка при добавлении связки:", $data);
									$res["msg"] = "Ошибка при добавлении номера!";
								}
							}
							
						} else {
							utils_log("Ошибка определения идентификатора пользователя!");
							$res["msg"] = "Ошибка при определении идентификатора созданного аккаунта!";
						}
					} else {
						utils_log("Ошибка при добавлении пользователя:", $data);
						$res["msg"] = "Ошибка при создании аккаунта!";
					}
				} else { //номер зарегистрирован

					$binded = false; //привязан к данном гейту
					while ($binding = mysql_fetch_array($data, MYSQL_ASSOC)) {
						if ($binding["confirmed"])
							$UID = $binding["user_id"];
						
						if ($binding["gate"] == $GATE) {
							$binded = true;
							break;
						}
					}
					
					if (!$binded) { //2. Номер зарегистрирован, но не привязан к данному гейту
						utils_log("Номер зарегистрирован, но не привязан к данному гейту.");
						utils_log("Добавляем связку...");

						if ($forced != 1) {
							$code = rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
							$query = 'INSERT INTO `bindings`(`create_date`,`user_id`,`phone`,`guid`,`gate`,`code`,`allowed`,`main`) VALUES (NOW(),'.$UID.',"'.$phone.'","'.$GUID.'","'.$GATE.'","'.$code.'",1,1)';
							$data = db_query($query);
							if (!($data < 0)){
								utils_log("Cвязка ".$phone." c ".$GUID." добавлена.");
								//require_once "/var/www/html/garant_sms.php";
								//SMSSend($phone, TextToURL("Код подтверждения: ".$code));
								utils_log("Выслан код:", $code);
								$res["verification"] = $code;
								$res["msg"] = "На номер ".$phone." выслано смс с кодом подтверждения.";
								$res["code"] = 0;
								if ($ACCESS_TYPE == 2)
									$_SESSION['uid'] = $UID;
								else 
									$res["uid"] = $UID;
							} else {
								utils_log("Ошибка при добавлении связки:", $data);
								$res["msg"] = "Ошибка при добавлении номера!";
							}
						} else {
							$query = 'INSERT INTO `bindings`(`create_date`,`user_id`,`phone`,`guid`,`gate`,`code`,`allowed`,`main`,`confirmed`,`last_use`) VALUES (NOW(),'.$UID.',"'.$phone.'","'.$GUID.'","'.$GATE.'","",1,1,1,NOW())';
							$data = db_query($query);
							if (!($data < 0)){
								utils_log("Cвязка ".$phone." c ".$GUID." подтверждена.");
								$res["msg"] = "Номер ".$phone." успешно подтвержден!";
								$res["code"] = 0;
								if ($ACCESS_TYPE == 2)
									$_SESSION['uid'] = $UID;
								else 
									$res["uid"] = $UID;
							} else {
								utils_log("Ошибка при добавлении связки:", $data);
								$res["msg"] = "Ошибка при добавлении номера!";
							}
						}
						
					} else if (!$binding["confirmed"]) { //3. Номер зарегистрирован, привязан к данному гейту, но не подтвержден
						utils_log("Номер зарегистрирован, привязан к данному гейту, но не подтвержден. Связка от ".$binding["create_date"]);
						utils_log("Обновляем связку...");
						$dt = time() - strtotime($binding["create_date"]); 
						if ($dt >= 60 * 5) {//код высылался более 5 минут назад
							$code = rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
							$query = 'UPDATE `bindings` SET `create_date` = NOW(),`user_id` = '.$UID.',`guid`= "'.$GUID.'",`code` = "'.$code.'" WHERE `id` = '.$binding["id"];
						} else 
							$query = 'UPDATE `bindings` SET `user_id` = '.$UID.',`guid`= "'.$GUID.'" WHERE `id` = '.$binding["id"];
						$data = db_query($query);
						if (!($data < 0)) {
							utils_log("Cвязка ".$phone." c ".$GUID." обновлена.");
							if ($dt >= 60 * 5) {
								//require_once "/var/www/html/garant_sms.php";
								//SMSSend($phone, TextToURL("Код подтверждения ".$code));
								utils_log("Выслан код:", $code);
								$res["verification"] = $code;
								$res["msg"] = "На номер ".$phone." выслано смс с кодом подтверждения.";
							} else
								$res["msg"] = "Код подтверждения номера ".$phone." был выслан менее 5 минут назад!";
							$res["code"] = 0;
							if ($ACCESS_TYPE == 2)
								$_SESSION['uid'] = $UID;
							else 
								$res["uid"] = $UID;
						} else {
							utils_log("Ошибка при обновлении связки:", $data);
							$res["msg"] = "Ошибка при обновлении данных о номере!";
						}
					} else if ($binding["guid"] == $GUID) { //4. Номер зарегистрирован, привязан к данному гейту и подтвержден данным пользователем
						utils_log("Номер зарегистрирован, привязан к данному гейту и подтвержден данным пользователем.");
						$res["msg"] = "Номер ".$phone." уже привязан к Вашему аккаунту и подтвержден!";
						$res["code"] = 0;
						if ($ACCESS_TYPE == 2)
							$_SESSION['uid'] = $UID;
						else 
							$res["uid"] = $UID;
					} else { //5. Номер зарегистрирован, привязан к данному гейту и подтвержден другим пользователем
						utils_log("Номер зарегистрирован, привязан к данному гейту и подтвержден другим пользователем.");
						$res["msg"] = "Номер ".$phone." уже привязан к другому аккаунту!";
					}
				}
			} else {
				utils_log("Ошибка при запросе связок:", $data);
				$res["msg"] = "Ошибка при проверке привязок номера ".$phone." к другим аккаунтам!";
			}			
		} else {
			utils_log("Ошибка подключения к БД (phones):", $f);
			$res["msg"] = "Ошибка подключения к базе данных!";
		}
	} else {
		utils_log("Номер телефона некорректный!");
		$res["msg"] = "Номер телефона некорректный!";
	}
		
	echo json_encode($res);
?>