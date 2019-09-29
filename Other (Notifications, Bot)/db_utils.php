<?
	require_once "/var/www/html/gates/team-one/utils.php";

	function db_connect($host, $db, $user, $pswd){
		$code = 0;
		$connection = mysql_connect($host, $user, $pswd);
		if ($connection){
			if (@mysql_select_db($db, $connection)){
				$data = db_query('SET NAMES "utf8"');
				if ($data < 0)
					$code = $data;
			} else
				$code = -2;
		} else
			$code = -1;
		return $code;
	}
	
	function db_query($query){
		utils_log($query);
		$data = mysql_query($query);
		if (!$data){
			utils_log($query);
			utils_log(mysql_error());
			return -3;
		} else 
			return $data;
	}

?>