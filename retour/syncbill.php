<?php

require_once "../global.php";
#if(false) {
if (!in_array($_SERVER['REMOTE_ADDR'], $authHost) and ! in_array($_SERVER['REMOTE_ADDR'], $authHostNotif)) {
    logger("synchronisation", array("type" => "Unauthorized", "commentaire" => "Host " . $_SERVER['REMOTE_ADDR'] . " is unauthorized", "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
    exit("NOK;004");
} else {
    $db = db_connect();
    if ($db->test_connexion()) {
        $syncOp = new DATASNYC();
        $syncOp->validElements($db);
        $response = $syncOp->send_to_app($db);
        $db->db_close();
        echo $response;
    } else {
        logger("synchronisation", array("type" => "BaseDeDonnees", "commentaire" => ERREUR_CONNEXION_BDD, "requete" => $_REQUEST, "ip" => $_SERVER['REMOTE_ADDR']));
        exit("NOK;005");
    }
}
?>
