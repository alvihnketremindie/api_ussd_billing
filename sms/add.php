<?php

header('Content-type:text/plain;charset=UTF-8');
include_once ('../global.php');
if (!in_array($_SERVER['REMOTE_ADDR'], $authHost)) {
    $body = "-1 : Host " . $_SERVER['REMOTE_ADDR'] . " is unauthorized";
    logger("smsadd", array("type" => "Unauthorized", "commentaire" => $body, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
} else {
    $db = db_connect();
    if ($db->test_connexion()) {
        $smsadd = new SMSAdd($_REQUEST);
        if ($smsadd->status_mode) {
            $smsqueues = $smsadd->getElements();
            $smsqueues['date_insertion'] = @date('Y-m-d H:i:s');
            $smsqueues['message'] = enleverCaracteresSpeciaux(nettoyerChaine($smsqueues['message']));
            $smsqueues['message_md5'] = md5(preg_replace("/([[:punct:]]|[[:space:]])/i", "", strtolower($smsqueues['message'])) . $smsqueues['sendername'] . $smsqueues['receiver'] . @date('Ymd'));
            $db->db_insert_ignore("smsqueues", $smsqueues);
            $body = "0 : Accepted";
            logger("smsadd", $smsqueues);
        } else {
            $body = "-2 : {$smsadd->commentaire}";
            logger("smsadd", array("type" => "InvalidRequest", "commentaire" => $body, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
        }
        /*
          if ($hour >= 7 and $hour <= 22) {
          //
          } else {
          $body = "-3";
          logger("smsadd", array("type" => "TimeBlock", "commentaire" => "Nous ne sommes pas dans la bonne periode pour envoyer des sms", "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
          }
         */
        $db->db_close();
    } else {
        $body = "-3 : Database problem";
        logger("smsadd", array("type" => "BaseDeDonnees", "commentaire" => ERREUR_CONNEXION_BDD, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
    }
}
print trim($body);
?>