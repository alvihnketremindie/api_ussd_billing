<?php

$urlArrayParams = array(
        'from' => "303",
        'to' => "22509256633",
        'text' => "test",
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_store/sms/add.php" . "?" . $urlStringParams;
// logger(__FUNCTION__,  array("url" => $url));
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
$reply = curl_exec($ch);
curl_close($ch);
echo $reply."\n";
?>