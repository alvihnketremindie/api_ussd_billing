<?php

include_once ('../global.php');
$code = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
$headers = apache_request_headers();
$ussdrequest = new USSDRequest($headers, $_REQUEST, $code);
$iniServices = new ParseConfig(INI_SERVICES, true);
$serviceConfig = $iniServices->return_config($ussdrequest->get_value("code"));
$ussdSession = new USSDSession($ussdrequest->getElements(), $serviceConfig);
$ussdSession->run();
?>