<?php
require_once "../global.php";
/*
$status = $_REQUEST['status'];
$reponse = $_REQUEST['reponse'];
if($status == "1" and preg_match("/DELIVRD/i", $reponse)) {
	$file = "sucess";
} elseif($status == "2") {
	if(preg_match("/UNDELIV/i", $reponse)) {
		$file = "nondelivrable";
	} elseif(preg_match("/EXPIRED/i", $reponse)) {
		$file = "expirer";
	} else {
		$file = "echec";
	}
} elseif($status == "8") {
	$file = "soumis";
} elseif($status == "16") {
	$file = "rejeter";
} else {
	$file = "autre";
}
$dlr = $_REQUEST;
$dlr['file']  = $file;
 */
$log->cdr("dlr", $_REQUEST);
?>