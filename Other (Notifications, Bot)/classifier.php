<?php
	header('Access-Control-Allow-Origin: *');
	header('Content-type:application/json;charset=utf-8');

	require_once "/var/www/html/gates/utils.php";
	
	include("./LinguaStemRu.php");			
	use Stem\LinguaStemRu;
	
	function cmp($a, $b) {
		if ($a["weight"] == $b["weight"]) {
			return 0;
		}
    return ($a["weight"] < $b["weight"]) ? 1 : -1;
}
	
	$res["code"] = -1;
	
	$guid = $_GET["guid"];
	$text = $_GET["text"];
	
	$return_data = (bool) $_GET["data"];
	
	$res["code"] = -1;
	$res["class"] = "unknown";
	$res["class_num"] = -1;
	
	utils_log('Классификация сообщения от '.$guid.': '.$text);
	if (($guid != "") && ($text != "")) {
		$link = 'https://api.dialogflow.com/v1/query?v=20150910';
		$prms = array(
			"lang" => "ru",
			"query" => $text,
			"sessionId" => $guid,
			"timezone" => "Europe/Moscow"
		);
		
		$data_string = json_encode($prms);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer 91781d313a7f46669cb94e534f749736',
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
			$obj = json_decode($output, true);

			$res["query"] = $text;
		
			if (count($obj["result"]["metadata"]) > 0) {
				$res["class"] = $obj["result"]["metadata"]["intentName"];
				
				//echo $obj["result"];
				
				$tmp = preg_replace('/[^0-9]+/', '', $res["class"]);
				
				utils_log('$tmp='.$tmp);
				
				if ($tmp != "")
					$t = intval($tmp, 10);
				else
					$t = -1;
				
				$classes = array(
					1 => "Холодное водоснабжение",
					2 => "Лифт",
					3 => "Газовые сети",
					4 => "Техническое обслуживание",
					5 => "Текущий ремонт",
					6 => "Платные услуги",
					7 => "Санитарное состояние/содержание",
					8 => "Отопление",
					9 => "Вентканалы, дымоходы",
					10 => "Канализация",
					11 => "Горячее водоснабжение",
					12 => "Электрические сети",
					13 => "Кровля",
					14 => "Фасад",
					15 => "Стены",
					17 => "Нарушения",
					19 => "Иное",
					20 => "Уличные сети водоснабжения",
					21 => "Внутриквартальные сети водоснабжения",
					22 => "Насосное оборудование",
					23 => "Электротехническое оборудование",
					24 => "Платные услуги",
					25 => "Качество воды",
					26 => "Перерывы в предоставлении воды",
					27 => "Узел учета",
					28 => "Уличные сети водоотведения",
					29 => "Внутриквартальные сети водоотведения",
					30 => "Насосное оборудование",
					31 => "Электротехническое оборудование",
					32 => "Платные услуги",
					33 => "Качество стоков",
					34 => "Перерывы в приеме стоков",
					35 => "Узел учета");
				
				if (($t >= 0) && ($t <= 35)) {
					$res["class_info"] = $classes[$t];
					$res["class_num"] = $t;
					$res["code"] = 0;
					
					if ($return_data) {
						$stemmer = new LinguaStemRu();
						
						$s = mb_strtolower($text, 'UTF-8');
						$s = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $s);
						$s = str_replace('  ', ' ', $s);

						$words = explode(" ", $stemmer->stem_text($s));
						
						$res["data"] = [];
						$data = file_get_contents("./data/".$res["class_num"].".txt");
						$items = explode(PHP_EOL, $data);
						$k = 0;
						foreach ($items as $item) {
							$k++;
							$el["id"] = $t.".".$k;
							$el["text"] = trim($item);
							
							$tmp = mb_strtolower($item, 'UTF-8');
							$tmp = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $tmp);
							$tmp = str_replace('  ', ' ', $tmp); 
							
							$ints = array_intersect(explode(" ", $stemmer->stem_text($tmp)), $words);
							
							if (count($ints) > 0)
								$w = mb_strlen(implode(" ", $ints), 'UTF-8') / count($ints);
							else 
								$w = 0;

							//echo "1: ".$s."<br />";
							//echo "2: ".$tmp."<br />";
							//echo "3: ".implode(" ", $ints)."<br />";
							//echo "WEIGHT: ".$w."<br />";
							//echo "<hr />";
							
							$el["weight"] = $w;
							$res["data"][] = $el;
						}
						
						usort($res["data"], "cmp");
					}
					
				}
			} 
			
			if ($obj["status"]["errorType"] == "success")
				$res["code"] = 0;
			else
				$res["code"] = (int) $obj["status"]["code"];
		}
		curl_close($ch);
	}
	
	echo json_encode($res);
?>
