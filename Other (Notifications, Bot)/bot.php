<?php
	require_once "/var/www/html/gates/team-one/utils.php";
	
	$ACCESS_TOKEN = '45s224GsdfFH9U-jkn456DFG)452sdf!jdggdfw3';
	
	$res["code"] = -1;
	
	$guid = $_GET["guid"];
	$gate = $_GET["gate"];
	
	$text = $_GET["text"];
	$contact = $_GET["contact"];
	
	$media = "";
	if (isset($_GET["media"]))
		$media = $_GET["media"];
	
	$owner = $_GET["owner"];
		
	function menu2class($menunum) {
		switch ($menunum) {
			case 1:
				$classnum = 1;
				break;
			case 2:
				$classnum = 11;
				break;
			case 3:
				$classnum = 26;
				break;
			case 4:
				$classnum = 25;
				break;
			case 5:
				$classnum = 12;
				break;
			case 6:
				$classnum = 3;
				break;
			case 7:
				$classnum = 8;
				break;
			case 8:
				$classnum = 9;
				break;
			case 9:
				$classnum = 10;
				break;
			case 10:
				$classnum = 13;
				break;
			case 11:
				$classnum = 14;
				break;
			case 12:
				$classnum = 15;
				break;
			case 13:
				$classnum = 5;
				break;
			case 14:
				$classnum = 2;
				break;
			case 15:
				$classnum = 4;
				break;
			case 16:
				$classnum = 7;
				break;
			case 17:
				$classnum = 20;
				break;
			case 18:
				$classnum = 28;
				break;
			case 19:
				$classnum = 21;
				break;
			case 20:
				$classnum = 29;
				break;
			case 21:
				$classnum = 23;
				break;
			case 22:
				$classnum = 22;
				break;
			case 23:
				$classnum = 27;
				break;
			case 24:
				$classnum = 33;
				break;
			case 25:
				$classnum = 34;
				break;
			case 26:
				$classnum = 6;
				break;
			case 27:
				$classnum = 17;
				break;
			case 28:
				$classnum = 19;
				
		}
		return $classnum;
	}	
	
	function msg($text, $btns = "") {	
		global $guid, $gate;
		file_get_contents('https://cifra.taxi/gates/team-one/'.$gate.'/out.php?guid='.urlencode($guid).'&text='.urlencode($text).'&btns='.urlencode($btns));
	}
	
	function step($uid, $step) {
		global $ACCESS_TOKEN;
		global $guid, $gate;
		file_get_contents('https://cifra.taxi/gates/team-one/bindings/set_step.php?token='.$ACCESS_TOKEN.'&uid='.$uid.'&gate='.$gate.'&guid='.urlencode($guid).'&step='.$step);
	}
			
	utils_log('Обработка сообщения '.$gate.' '.$guid.': '.(($contact == "") ? $text : $contact));
	if (($guid != "") && ($gate != "")) {
		
		identification:
		
		$verified = false;
		
		$data = file_get_contents('https://cifra.taxi/gates/team-one/bindings/get_user.php?token='.$ACCESS_TOKEN.'&gate='.$gate.'&guid='.urlencode($guid));
		if ($data !== False) {
			$tmp = json_decode($data);
			if ($tmp->code == 0) {
				$user = $tmp->user;
				utils_log('Данные пользователя: ',$user);
				
				utils_log('Обновление связки');
				$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/up_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&uid=".$user->id."&phone=".$user->phone);
				$r = json_decode($s, true);
				if ($r)
					utils_log($r["msg"]);
				else
					utils_log('Ошибка при обновлении связки ', $r);
				
			}
		}
		
		if (isset($user)){
			if ($user->phone == "") {
				$phone = (parse_phone($contact) == "") ? parse_phone($text) : parse_phone($contact);
				
				utils_log('Номер телефона '.$phone);
				
				if ($phone != "") {//получен корректный номер телефона
					if ($owner == $guid) {
						$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/new_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&phone=".$phone."&uid=".$user->id."&forced=1");
						$r = json_decode($s, true);
						if ($r) {
							utils_log($r["msg"]);
							$msg = $r["msg"];
							if ($r["code"] == 0) {
								$msg .= ' Пришлите название города:';
								msg($msg, "clear");
							}
						} else {
							$msg = 'Во время привязки профиля произошла ошибка! Пожалуйста, попробуйте еще раз, или позвоните в Службу поддержки по телефону 8800XXXXXXX';
							msg($msg);
						}
					} else {
						$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/new_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&phone=".$phone."&uid=".$user->id);
						$r = json_decode($s, true);
						if ($r) {
							utils_log($r["msg"]);
							$msg = $r["msg"];
							if ($r["code"] == 0) {
								$msg .= ' Пришлите проверочный код ('.$r["verification"].'), который был отправлен на указанный номер.';
								$msg .= ' Если Вы ошиблись в написании номера телефона, пришлите правильный номер.';
								$msg .= ' Если смс с кодом не поступает дольше 5 минут, пришлите "Выслать код повторно".';
								msg($msg, "Выслать код повторно");
							} else {
								$msg = trim($msg.' Пожалуйста, попробуйте отправить номер еще раз или позвоните в Службу поддержки по телефону 8800XXXXXXX');
								msg($msg);
							}
						} else {
							utils_log('Ошибка при создании связки ', $r);
							$msg = 'При создании связки произошла ошибка! Пожалуйста, попробуйте отправить номер еще раз или позвоните в Службу поддержки по телефону 8800XXXXXXX';
							msg($msg);
						}
					}
				} else {
					$msg = 'Пришлите номер мобильного телефона в формате 89XXXXXXXXX:';
					msg($msg);
				}

			} else if (!$user->confirmed) {
				
				if (ctype_digit($text) && (strlen($text) == 4)) {
					utils_log('Обработка кода');
					
					$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/set_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&uid=".$user->id."&phone=".$user->phone."&code=".$text);
					$r = json_decode($s, true);
					if ($r) {
						utils_log($r["msg"]);
						$msg = $r["msg"];
						if ($r["code"] == 0) {
							
							$msg = 'Поздравляю! Вы подключились и теперь можете пользоваться всеми моими функциями 😚';
							msg($msg);
						
							$menu = file_get_contents('/var/www/html/gates/team-one/answers/help/menu.txt');
							$msg = 'Пришлите номер темы:'.PHP_EOL.PHP_EOL.$menu;
						
							$user->step = 3;
							step($user->id, $user->step);
								
							msg($msg, "clear");
						} else {
							$msg .= ' Проверьте правильность кода и пришлите исправленный код.';
							msg($msg);
						}
					} else {
						$msg = 'При подтверждении кода произошла ошибка! Пожалуйста, попробуйте отправить код еще раз, или позвоните в Службу поддержки по телефону 8800XXXXXXX';
						msg($msg);
					}
				} else if ((strpos($text, "Выслать код повторно") !== false) && (strpos($text, "Выслать код повторно") == 0)) {
					utils_log('Запрос на повторную отправку кода');
					
					$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/new_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&phone=".$user->phone."&uid=".$user->id);
					$r = json_decode($s, true);
					if ($r) {
						utils_log($r["msg"]);
						msg($r["msg"], "Выслать код повторно");
					} else {
						utils_log('Ошибка при создании связки ', $r);
						$msg = 'При запросе нового кода произошла ошибка! Пожалуйста, попробуйте запросить код еще раз, или позвоните в Службу поддержки по телефону 8800XXXXXXX';
						msg($msg);
					}
				} else {
					msg("Пришлите проверочный код в формате XXXX:");
					msg($msg);
				}

			} else 
				$verified = true;
		} else {
			
			$phone = (parse_phone($contact) == "") ? parse_phone($text) : parse_phone($contact);
			if ($phone != "") {//получен корректный номер телефона
				utils_log('Номер телефона '.$phone);
				if ($owner == $guid) {
					$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/new_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&phone=".$phone."&forced=1");
					$r = json_decode($s, true);
					if ($r) {
						utils_log($r["msg"]);
						$msg = $r["msg"];
						if ($r["code"] == 0) {
							msg($msg, "clear");
							goto identification;
						}
					} else {
						$msg = 'Во время привязки профиля произошла ошибка! Пожалуйста, попробуйте еще раз, или позвоните в Службу поддержки по телефону 8800XXXXXXX';
						msg($msg);
					}
					
				} else {
					$s = file_get_contents("https://cifra.taxi/gates/team-one/bindings/new_binding.php?token=".$ACCESS_TOKEN."&gate=".$gate."&guid=".urlencode($guid)."&phone=".$phone);
					$r = json_decode($s, true);
					if ($r) {
						utils_log($r["msg"]);
						$msg = $r["msg"];
						if ($r["code"] == 0) {
							$msg .= ' Пришлите проверочный код ('.$r["verification"].'), который был отправлен на указанный номер.';
							$msg .= ' Если Вы ошиблись в написании номера телефона, пришлите правильный номер.';
							$msg .= ' Если смс с кодом не поступает дольше 5 минут, пришлите "Выслать код повторно".';
							msg($msg, "Выслать код повторно");
						} else {
							$msg = trim($msg.' Пожалуйста, попробуйте отправить номер еще раз или позвоните в Службу поддержки по телефону 8800XXXXXXX');
							msg($msg);
						}
					} else {
						utils_log('Ошибка при создании связки ', $r);
						$msg = 'При создании связки произошла ошибка! Пожалуйста, попробуйте отправить номер еще раз или позвоните в Службу поддержки по телефону 8800XXXXXXX';
						msg($msg);
					}
				}
			} else if ($gate == "tm")
				msg("Пришлите номер мобильного телефона в формате 89XXXXXXXXX:", "Выслать номер:1");
			else 
				msg("Пришлите номер мобильного телефона в формате 89XXXXXXXXX:");
			
		}
				
		if ($verified) {
			utils_log('Шаг: '.$user->step);
			
			if ($text == "/start") {
				msg('👂');
				$user->step = 0;
				step($user->id, $user->step);
			} else if ($text == "/menu") {
				$menu = file_get_contents('/var/www/html/gates/team-one/answers/help/menu.txt');
				$msg = 'Пришлите номер темы:'.PHP_EOL.$menu;
				msg($msg);
				$user->step = 3;
				step($user->id, $user->step);
			} else if ($text == "/delete") {
				msg('😲');
			} else if ($text == "/help") {
				$msg = 'Вы можете отправлять запросы прямо здесь.';
				msg($msg);
				
				$menu = file_get_contents('/var/www/html/gates/team-one/answers/help/menu.txt');
				$msg = 'Пришлите номер темы:'.PHP_EOL.$menu;
				msg($msg);
				
				$user->step = 3;
				step($user->id, $user->step);
			} else	
				switch ($user->step) {
					case "0":
					
						step0:
						
						if (!is_numeric($text)) {
					
							msg("Сейчас я попробую определить тему Вашего запроса...");
						
							$r = json_decode(file_get_contents('https://cifra.taxi/gates/team-one/classifier.php?guid='.urlencode($guid).'&text='.urlencode($text)));
						
							$res["code"] = 0;
							$res["text"] = $r->class_info;
							
							utils_log("Классификация:", $r);
						
							$feedback = False;
						
							if ($r->class_num > -1) {
								$info = '';
								$user->step = 1;
								
								msg('<b>"'.$res["text"].'"</b>'.PHP_EOL.$info);
								msg('Если я неправильно определил тему, пришлите <b>0</b>.');
									
								step($user->id, $user->step);
							} else {
								$feedback = True;
								$menu = file_get_contents('/var/www/html/gates/team-one/answers/help/menu.txt');

								msg('Мне не удалось определить тему запроса. Пришлите номер темы:'.PHP_EOL.$menu);
								
								$user->step = 3;
								
								step($user->id, $user->step);
							}
							
							//if ($feedback) {
							//	file_get_contents('http://95.172.132.162/mt/support/requests.php?action=new&phone='.$user->phone.'&problem='.urlencode("Обращение через мессенджер").'&type='.$gate.'&descript='.urlencode($user->place.': '.$text));
							//}
						
						} else 
							goto step3;
						break;
					case "1":
						
						step1:
						
						$DT = abs(time() - strtotime($user->last_use)); 
						
						utils_log('DT: '.$user->last_use.' = '.$DT);
						
						if ($text == "0") { //неверная классификация
							$menu = file_get_contents('/var/www/html/gates/team-one/answers/help/menu.txt');

							msg('Пришлите номер темы:'.PHP_EOL.$menu);
							
							$user->step = 3;
							
							step($user->id, $user->step);
						
						} else if ((!is_numeric($text)) && ($DT < 3)) {
							msg('Если требуется классифицировать сообщение, пришлите <b>0</b>.');
							
							$user->step = 2;
							
							step($user->id, $user->step);
							
						} else if (!is_numeric($text)){
							$user->step = 0;
							
							step($user->id, $user->step);
							goto step0;
						} else {
							$user->step = 3;
							goto step3;
						}
						
						break;
						
					case "2":
					
						step2:
						
						if ($text == "0")
							$text = $user->last_text;
						
						$user->step = 0;
							
						step($user->id, $user->step);
						goto step0;
						
						break;
						
					case "3":
						
						step3:
						
						if (is_numeric($text)) {
							$menu_num = (int) $text;
							$class_num = menu2class($menu_num);

							$info = file_get_contents('/var/www/html/gates/team-one/answers/help/0.txt');
	
							msg($info);
							
							$user->step = 1;
							
							step($user->id, $user->step);
						} else {
							$user->step = 0;
							
							step($user->id, $user->step);
							
							goto step0;
						}
						break;
										}
		}
	}
	
	echo json_encode($res);
?>
