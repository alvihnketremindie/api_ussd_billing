<?php

error_reporting(E_ALL);
#header('Content-type:text/plain;charset=UTF-8');
/*
  set_time_limit(0);
  ini_set('realpath_cache_ttl', '120');
  ini_set('realpath_cache_size', '50M');
  ini_set('memory_limit', '-1');
 */
#date_default_timezone_set("GMT");
define('ROOT', dirname(__FILE__));
define('CLASSES', ROOT . '/class/');
define('INI_CONFIG', ROOT . '/ini/config.ini');
define('INI_SERVICES', ROOT . '/ini/services.ini');
// define('INI_OPERATEURS', ROOT . '/ini/operateur.ini');
define('NUMERO_NON_AUTORISE', "Desole, votre numero n'est pas autorise a utiliser ce service. Contacter le fournisseur de service SVP.");
define('SERVICE_INDISPONIBLE', "Desole le service est actuellement indisponible, Merci de reessayer plus tard.");
define('SERVICE_INDEFINI', "Desole le service est en construction. Contacter le fournisseur de service SVP.");
define('ERREUR_PARAMETRES', "Params absents : ");
define('ERREUR_CONNEXION_BDD', "Connexion a la base de donnes impossible ou interrompu");
define('LOG_PATH', '/var/log/plateforme_orange_cameroun/');
//define('LOG_PATH_NOTIF', LOG_PATH . '/datasync/');
//define('LOG_PATH_SMS', LOG_PATH . '/sms/');

$authHost = array('127.0.0.1', '::1', '192.168.255.251', '192.168.255.250', '178.33.227.9', '178.33.61.111');
$authHostNotif = array("41.137.66.20", "41.137.66.18", "41.137.66.25","80.12.36.250");
$log = new LOG(LOG_PATH);
$iniConfig = new ParseConfig(INI_CONFIG, true);
$db_mysql = $iniConfig->return_config("mysql_params");
#$db_pgsql = $iniConfig->return_config("pgsql_params");

function __autoload($classes) {
    $explodeClasses = explode('|', $classes);
    foreach ($explodeClasses as $explodeClassesFile) {
        $filename = CLASSES . $explodeClassesFile . '.php';
        if (file_exists($filename)) {
            include_once($filename);
        }
    }
}

function db_connect() {
    global $db_mysql;
    $db_params = array('host' => $db_mysql['host'], 'user' => $db_mysql['user'], 'password' => $db_mysql['pass'], 'database' => $db_mysql['database']);
    $db = new DB_MYSQL($db_params);
    return $db;
}

function db_connect2() {
    global $db_pgsql;
    $db_params = array('host' => $db_pgsql['host'], 'user' => $db_pgsql['user'], 'password' => $db_pgsql['pass'], 'database' => $db_pgsql['database']);
    $db = new DB_POSTGRE($db_params);
    return $db;
}

function desactivateOldSessions($msisdn, $sessionid) {
    $dbi = db_connect();
    if ($dbi->test_connexion()) {
        $updateReq = $dbi->db_update("ussd_sessions", array('statut' => 'NO'), "msisdn = '$msisdn' and sessionid = '$sessionid'");
        $affected_rows = $dbi->db_get_affected_rows();
        $dbi->db_close();
        if ($updateReq and $affected_rows > 0) {
            return true;
        }
    }
    return false;
}

function logger($type, $info) {
    global $log;
    $log->cdr($type, $info);
}

function parse_reponse($array) {
    $toReturn = '';
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $toReturn .= ' [ ' . $key . ' => (' . parse_reponse($value) . ')' . ' ] ';
            } else {
                $toReturn .= ' | ' . $key . '=' . $value;
            }
        }
    } else {
        $toReturn .= ' | ' . $array;
    }
    return $toReturn;
}

function exec_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ch_result = utf8_decode(curl_exec($ch));
    curl_close($ch);
    return $ch_result;
}

function execute_post_method($http_header, $url, $body) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $ch_result = curl_exec($ch);
    if (curl_errno($ch)) {
        $ch_result = curl_error($ch);
    }
    curl_close($ch);
    logger(__FUNCTION__, array("reponse" => $ch_result, "url" => $url, "header" => $http_header, "postfields" => $body));
    return $ch_result;
}

function post_request($headers, $postfields, $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    $data['body'] = trim(curl_exec($ch));
    $info = curl_getinfo($ch);
    $data['code'] = $info['http_code'];
    if (curl_errno($ch)) {
        $data['body'] = 'Erreur Curl : ' . curl_error($ch);
    }
    curl_close($ch);
    $jsonErrorCode = json_last_error();
    if ($jsonErrorCode !== JSON_ERROR_NONE) {
        $jsonErrorMessage = json_last_error_msg();
        $data['body'] = 'API response not well-formed (json error code: ' . $jsonErrorCode . ' | json error message: ' . $jsonErrorMessage . ')';
    }
    logger(__FUNCTION__, array("reponse" => $data, "url" => $url, "header" => $headers, "postfields" => $postfields));
    return $data;
}

function enleverCaracteresSpeciaux($text) {
    $utf8 = array(
        '/[áàâãªä]/u' => 'a',
        '/[ÁÀÂÃÄ]/u' => 'A',
        '/[ÍÌÎÏ]/u' => 'I',
        '/[íìîï]/u' => 'i',
        '/[éèêë]/u' => 'e',
        '/[ÉÈÊË]/u' => 'E',
        '/[óòôõºö]/u' => 'o',
        '/[ÓÒÔÕÖ]/u' => 'O',
        '/[úùûü]/u' => 'u',
        '/[ÚÙÛÜ]/u' => 'U',
        '/ç/' => 'c', '/Ç/' => 'C', '/ñ/' => 'n', '/Ñ/' => 'N',
        '/Œ/' => 'OE', '/œ/' => 'oe', '/æ/' => 'ae', '/Æ/' => 'AE',
        '/–/' => '-', '/[‹«]/u' => '<', '/[›»]/u' => '>', '/[“‘‚”’‚“”„"]/u' => "'", '/ /' => ' '
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

function nettoyerChaine($string) {
    $dict = array("\r" => '', "\t" => ' ', '{CR}' => "\n", "\n\n" => "\n");
    $string = str_ireplace(array_keys($dict), array_values($dict), $string);
    $string = str_ireplace("\n\n", "\n", $string);
    $string = str_ireplace("\n\n", "\n", $string);
    return $string;
}

function set_headers_sms($smsConfig) {
    $headers = array(
        "Authorization: " . $smsConfig['access_token'],
        "X-Orange-ISE2: {X-Orange-ISE2}",
        "X-Orange-MCO: " . $smsConfig['X-Orange-MCO'],
        "Cache-Control: no-cache",
        "Postman-Token: " . $smsConfig['postman_token'],
        "Content-Type: application/json"
    );
    logger(__FUNCTION__, $headers);
    return $headers;
}

function getTokenInfos($dbi, $date_process) {
    return $dbi->db_find_record_assoc("*", "tokens", array("where" => "expires_in_date > '$date_process' AND statut = 'YES'", "order" => "id desc", "limit" => "1"));
}

function decoupeSMS($smsinfos) {
    $text = $smsinfos["message"];
    $lenght = 160;
    $longueur_chaine = strlen($text);
    $part = ceil($longueur_chaine / $lenght);
    for ($i = 0; $i < $part; $i++) {
        $start = $i * $lenght;
        $rest = substr($text, $start, $lenght);
        $smslist[$i] = $smsinfos;
        $smslist[$i]["message"] = $rest;
    }
    return $smslist;
}

function testModule($url) {
    echo $url . "|" . exec_url($url) . "\n";
}

?>
