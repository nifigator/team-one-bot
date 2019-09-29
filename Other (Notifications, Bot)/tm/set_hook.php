<?php
	require_once "/var/www/html/gates/tm/auth_data.php";

	echo file_get_contents('https://api.telegram.org/bot'.$TOKEN.'/setWebhook?url=https://team-one.kras.ru/gates/tm/in.php');
	
?>
