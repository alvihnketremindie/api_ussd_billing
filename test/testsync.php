<?php
include_once ('../global.php');
header('Content-type:text/plain;charset=UTF-8');
$urlArrayParams = array(
        'msisdn' => "PDKSUB-200-0O7RnUaY4GMDeRJc3VCBfkO0XscbIg2Z0XDmmvu/Nfc=",
        'spid' => "324",
        'event' => "1",
        'opid' => "71",
    "datetime"=> @date("YmdHis"),
    "idabo" => "21897326",
    "externalid" => "e91a282c",
    "amount" => "50"
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_cameroun/retour/syncbill.php?".$urlStringParams;
testModule($url);
?>