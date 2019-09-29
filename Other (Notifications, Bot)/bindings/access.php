<?php
	$ACCESS_RESULT = 0;
	$ACCESS_TYPE = 0;
	
	$GATE = "";
	$GUID = 0;
	$UID = 0;

	if (isset($_GET['token'])) {
		$ACCESS_TYPE = 1;
		if ($_GET['token'] === "45s224GsdfFH9U-jkn456DFG)452sdf!jdggdfw3")
			if (isset($_GET['gate']) && strlen($_GET['gate']) <= 3){
				$GATE = $_GET['gate'];
				$ACCESS_RESULT = 1;
				if (isset($_GET['guid']) && $_GET['guid'] != ""){
					$GUID = $_GET['guid'];
					$ACCESS_RESULT = 2;
					if (isset($_GET['uid']) && $_GET['uid'] > 0){
						$UID = (int) $_GET['uid'];
						$ACCESS_RESULT = 3;
					}
				}
			}
	} else if (isset($_REQUEST[session_name()])) {
		$ACCESS_TYPE = 2;
		session_start(); 
		if (isset($_SESSION['gate']) && strlen($_SESSION['gate']) <= 3){
			$GATE = $_SESSION['gate'];
			$ACCESS_RESULT = 1;
			if (isset($_SESSION['guid']) && $_SESSION['guid'] != ""){
				$GUID = $_SESSION['guid'];
				$ACCESS_RESULT = 2;
				if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0){
					$UID = (int) $_SESSION['uid'];
					$ACCESS_RESULT = 3;
				}
			}
		}
	}
?>