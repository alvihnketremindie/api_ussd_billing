<?php
include_once ('../global.php');
$urlArrayParams = array(
	'from' => "303",
#	'to' => "acr:USDSHP-200-9LxHHc1ByPNr2jVtMQ/kELhDEcoiv1B4FCTzpQ2QFMc=",
       'to' => "acr:PDKSUB-200-nDMW/YJU2icT72cq5U2DsdOiiWbEIfRLleBsR9vIkDg=",
	'text' => "test",
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_cameroun/sms/mt.php" . "?" . $urlStringParams;
testModule($url);
?>
