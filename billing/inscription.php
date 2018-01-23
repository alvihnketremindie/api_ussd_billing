<?php

include_once ('../global.php');
if (!in_array($_SERVER['REMOTE_ADDR'], $authHost)) {
    $body = "-1";
    logger("inscription", array("type" => "Unauthorized", "commentaire" => "Host " . $_SERVER['REMOTE_ADDR'] . " is unauthorized", "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
} else {
    $db = db_connect();
    if ($db->test_connexion()) {
        $inscription = new Billing($_REQUEST, "inscription");
        $body = $inscription->subscribe_request($db);
        $db->db_close();
    } else {
        $body = "-3";
        logger("inscription", array("type" => "BaseDeDonnees", "commentaire" => ERREUR_CONNEXION_BDD, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
    }
}
print trim($body);
?>
