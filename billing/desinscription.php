<?php

include_once ('../global.php');
if (!in_array($_SERVER['REMOTE_ADDR'], $authHost)) {
    $body = "-1";
    logger("desinscription", array("type" => "Unauthorized", "commentaire" => "Host " . $_SERVER['REMOTE_ADDR'] . " is unauthorized", "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
} else {
    $db = db_connect();
    if ($db->test_connexion()) {
        $desinscription = new Billing($_REQUEST, "desinscription");
        $body = $desinscription->unsubscribe_request($db);
        $db->db_close();
    } else {
        $body = "-3";
        logger("desinscription", array("type" => "BaseDeDonnees", "commentaire" => ERREUR_CONNEXION_BDD, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
    }
}
print trim($body);
?>
