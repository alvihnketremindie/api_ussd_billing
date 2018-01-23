<?php

class LOG {

    var $chemin;

    function __construct($chemin) {
        $this->chemin = $chemin;
    }

    function getLog($params) {
        $log = '';
        foreach ($params as $key => $value) {
            $log .= "|" . $key . "=" . $value;
        }
        return $log;
    }

    function cdr($type, $to_log) {
        $log_chemin = $this->chemin . "/" . @date("Ymd");
        $log_chemin_fichier = $log_chemin . "-" . $type . ".log";
        $aLogger = @date("Y-m-d H:i:s") . parse_reponse($to_log) . PHP_EOL;
        //file_put_contents($log_chemin_fichier, $aLogger, FILE_APPEND);
        file_put_contents($log_chemin, "[" . $type . "]" . $aLogger, FILE_APPEND);
        //echo $log_chemin_fichier . " [" . $type . "] " . $aLogger;
    }

}

?>
