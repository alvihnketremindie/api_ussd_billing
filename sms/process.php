<?php

// header('Content-type:text/plain;charset=UTF-8');
include_once ('../global.php');
//Config SMS
$smsConfig = $iniConfig->return_config("sms");
if (!(isset($smsConfig) and ! empty($smsConfig))) {
    logger("smsprocess", array("type" => "smsConfigNotFound", "commentaire" => "Le fichier INI pour les SMS n'a pu etre parse"));
} else {
    $date_process = @date("Y-m-d H:i:s");
    $dbi = db_connect();
    if ($dbi->test_connexion()) {
        //Recuperation des infos du Token dans la BDD
        $tokenInfos = getTokenInfos($dbi, $date_process);
        if (!$tokenInfos) {
            logger("smsprocess", array("type" => "tokenInfosNotFound", "commentaire" => "Les informations sur le Token n'ont pu etre trouvé"));
        } else {
            $updateReq = $dbi->db_update("smsqueues", array('date_process' => $date_process, 'statut' => 'sending'), "date_process = '0000-00-00 00:00:00' AND statut = 'pending'");
            $affected_rows = $dbi->db_get_affected_rows();
            if (!$updateReq) {
                logger("smsprocess", array("type" => "noUpdate", "commentaire" => "La requete d'update a rencontre un probleme"));
            } elseif ($affected_rows <= 0) {
                logger("smsprocess", array("type" => "noLinesFound", "commentaire" => "Aucun SMS trouve"));
            } else {
                $findParams = array("where" => "date_process = '$date_process' AND statut = 'sending'", "order" => "id");
                $record = $dbi->db_find_record_assoc("*", "smsqueues", $findParams, true);
                $smsConfig["authorization_token"] = $tokenInfos['token_type']." ".$tokenInfos['access_token'];
                $headers = set_headers_sms($smsConfig);
                foreach ($record as $smsinfos) {
                    $smsrequest = new SMSRequest($smsinfos, $smsConfig);
                    $send_sms_result = $smsrequest->send($smsConfig['url_send_sms'], $headers);
                    $code = @$send_sms_result['code'];
                    $message = @$send_sms_result['body'];
                    if ($code == '201') {
                        $statut = "sent";
                    } else {
                        $statut = "failed";
                    }
                    $dbi->db_update("smsqueues", array('date_envoi_sms' => @date("Y-m-d H:i:s"), 'statut' => $statut), "id = '" . $smsinfos["id"] . "'");
                }
            }
        }
    }
    $dbi->db_close();
}

?>
