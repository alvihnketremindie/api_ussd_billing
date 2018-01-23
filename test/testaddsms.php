<?php
include_once ('../global.php');
header('Content-type:text/plain;charset=UTF-8');
$urlArrayParams = array(
        'from' => "303",
        'to' => "PDNEZNJZENZEO32R2d	EZNDS",
        'text' => "test23",
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_cameroun/sms/add.php" . "?" . $urlStringParams;
testModule($url);
?>