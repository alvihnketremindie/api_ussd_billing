<?php

class ParseConfig {

    public function __construct($config_file = NULL, $mode = FALSE) {
        $this->mode = $mode;
        $this->errorIni = NULL;
        if ($config_file == NULL) {
            $this->errorIni = 'Aucun fichier de config fourni en parametre';
            return;
        }
        if (!file_exists($config_file)) {
            $this->errorIni = "Le fichier de config {$config_file} n'existe pas";
            return;
        }
        $this->iniConfig = parse_ini_file($config_file, $mode);
    }

    public function get_error() {
        return $this->errorIni;
    }

    public function return_config($section_name) {
        // print_r($section_name);
        if (!isset($this->iniConfig[$section_name])) {
            logger("iniConfError", "La section {$section_name} n'existe pas");
            return NULL;
        } else {
 #           logger(__FUNCTION__, $this->iniConfig[$section_name]);
        }
        return $this->iniConfig[$section_name];
    }

}

?>
