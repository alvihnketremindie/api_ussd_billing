<?php

// header('Content-type:text/plain;charset=UTF-8');
include_once ('../global.php');
//Config SMS
$smsConfig = $iniConfig->return_config("sms");
if (!(isset($smsConfig) and ! empty($smsConfig))) {
    logger("smsmt", array("type" => "smsConfigNotFound", "commentaire" => "Le fichier INI pour les SMS n'a pu etre parse"));
} else {
    $date_process = @date("Y-m-d H:i:s");
    $db = db_connect();
    if ($db->test_connexion()) {
        //Recuperation des infos du Token dans la BDD
        $tokenInfos = getTokenInfos($db, $date_process);
        if (!$tokenInfos) {
            logger("smsmt", array("type" => "tokenInfosNotFound", "commentaire" => "Les informations sur le Token n'ont pu etre trouvé"));
        } else {
            $smsConfig['access_token'] = $tokenInfos['token_type'] . " " . $tokenInfos['access_token'];
            $smsadd = new SMSAdd($_REQUEST);
            if ($smsadd->status_mode) {
                $smsmt = $smsadd->getElements();
                $smsmt['message'] = trim(enleverCaracteresSpeciaux(nettoyerChaine($smsmt['message'])));
                $smsrequest = new SMSRequest($smsmt, $smsConfig);
                $headers = set_headers_sms($smsConfig);
                $body = $smsrequest->send($smsConfig['url_send_sms'], $headers);
            } else {
                $body = "-2 : {$smsadd->commentaire}";
                logger("smsmt", array("type" => "InvalidRequest", "commentaire" => $body, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
            }
        }
        $db->db_close();
    } else {
        $body = "-3 : Database problem";
        logger("smsmt", array("type" => "BaseDeDonnees", "commentaire" => ERREUR_CONNEXION_BDD, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
    }
}
print $body;
?>
