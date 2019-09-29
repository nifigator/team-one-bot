<?php
    function utils_log($hint, $err = null){
		$LOGPATH = '/var/www/html/logs/';

		$text = $hint;
		if ((is_object($err)) && (!is_null($err)))
			$text .= json_encode($err);
		else
			$text .= $err;
		
		file_put_contents($LOGPATH.'log.txt', date('H:i:s d.m.Y',time())." ".$text."\n", FILE_APPEND);
		if (filesize($LOGPATH.'log.txt') >= 5242880){
			$t = time();
			rename($LOGPATH.'log.txt', $LOGPATH.'log_'.$t.'.txt');
			$f = fopen($LOGPATH.'log.txt', 'w+');
			fwrite($f, 'Предыдущий лог: '.$LOGPATH.'log_'.$t.'.txt'."\n");
			fclose($f);
		}
    }
    
    function parse_phone($s){
		$phone = "";
		for($i = 0; $i < strlen($s); $i++)
			if (is_numeric($s[$i]))
				$phone .= $s[$i];
		if ((strlen($phone) == 11) && (($phone[0] == "7") || ($phone[0] == "8")))
			$phone[0] = "8";
		else if ((strlen($phone) == 10) && ($phone[0] == "9"))
			$phone = "8".$phone;
		else
			$phone = "";
		return $phone;
    }
	
	function HighlightPhones($text) {
		preg_match_all('[(?:\89)?\\d{11}]u', $text, $phones, PREG_SET_ORDER + PREG_OFFSET_CAPTURE);
		foreach($phones as $item) {
			//$item[0][0] номер телефона
			//$item[0][1] позиция в строке
			$phone = $item[0][0];
			$phone[0] = '7';
			$phone = '+'.$phone;
			$text = str_replace($item[0][0], '<a href="tel:'.$phone.'">'.$phone.'</a>', $text);
		}
		return $text;
	}
	
	function DownloadFile($url, $path) {
		$newfname = $path;
		$file = fopen ($url, 'rb');
		if ($file) {
			$newf = fopen ($newfname, 'wb');
			if ($newf) {
				while(!feof($file)) {
					fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
				}
			}
		}
		if ($file) {
			fclose($file);
		}
		if ($newf) {
			fclose($newf);
		}
	}
    
?>