<?php

$urlArrayParams = array(
	'from' => "303",
	'to' => "acr:USDSHP-200-9LxHHc1ByPNr2jVtMQ/kELhDEcoiv1B4FCTzpQ2QFMc=",
	'text' => "test",
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_store/sendsms.php" . "?" . $urlStringParams;
// logger(__FUNCTION__,  array("url" => $url));
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
$reply = curl_exec($ch);
curl_close($ch);
echo $reply."\n";
?>
