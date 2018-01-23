<?php

include_once ('../global.php');
//Config SMS
$smsConfig = $iniConfig->return_config("sms");
if (!(isset($smsConfig) and ! empty($smsConfig))) {
    logger("smsprocess", array("type" => "smsConfigNotFound", "commentaire" => "Le fichier INI pour les SMS n'a pu etre parse"));
}
$dbi = db_connect();
if ($dbi->test_connexion()) {
	$url = $smsConfig['url_token'];
	$http_header = array('Authorization: '.$smsConfig['authorization_creditentials']);
	$body = "grant_type=client_credentials";
	$result = execute_post_method($http_header, $url, $body);
	$json_tab = json_decode($result, true);
	$tokens['date_recuperation'] = @date("Y-m-d H:i:s");
	$tokens['token_type'] = $json_tab['token_type'];
	$tokens['access_token'] = $json_tab['access_token'];
	$tokens['expires_in_secondes'] = $json_tab['expires_in'];
	$tokens['expires_in_date'] = @date("Y-m-d H:i:s", strtotime("+ ".$json_tab['expires_in']." second"));
	// print_r($tokens);
	$dbi->db_insert_ignore("tokens", $tokens);
	$loginof = array("tokensInfos" =>$tokens, "url" => $url, "header" => $http_header, "body" => $body, "result" => $result);
	print_r($loginof);
	logger("get_tokens", $loginof);
	$dbi->db_close();
}
else {
	print "Erreur de connexion a la BD...";	
}



?>
