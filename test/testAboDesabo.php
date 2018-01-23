<?php
include_once ('../global.php');
header('Content-type:text/plain;charset=UTF-8');
$refcode = md5("acr:PDKSUB-200-nDMW/YJU2icT72cq5U2DsdOiiWbEIfRLleBsR9vIkDg="."324"."250".@date("YmdHis"));
$urlArrayParams = array(
        'msisdn' => "acr:PDKSUB-200-nDMW/YJU2icT72cq5U2DsdOiiWbEIfRLleBsR9vIkDg=",
        'spid' => "324",
        'montant' => "250",
        'refcode' => $refcode
);
$urlStringParams = http_build_query($urlArrayParams);
$url = "http://localhost/plateforme_orange_cameroun/billing/inscription.php?".$urlStringParams;
testModule($url);
echo PHP_EOL."-----------------".PHP_EOL;
sleep(5);
$url = "http://localhost/plateforme_orange_cameroun/billing/desinscription.php?".$urlStringParams;
testModule($url);
echo PHP_EOL."-----------------".PHP_EOL;

?>
