<?php

class DB_MYSQL {

    public $dbParams;
    private $connexion;
    private $query_id = 0;
    private $affected_rows = 0;

    /**
     * Constructor
     */
    function __construct($dbParams) {
        $this->dbParams = $dbParams;
        $this->connexion = $this->getDBConnection();
    }

    private function db_connexion() {
        try {
            $dbAccess = mysqli_connect($this->dbParams['host'], $this->dbParams['user'], $this->dbParams['password'], $this->dbParams['database']);
        } catch (Exception $e) {
            $dbAccess = null;
            $errorPush = "Tentative de connexion a la BDD avec les parametres : " . $this->dbParams['host'] . ", " . $this->dbParams['user'] . ", " . $this->dbParams['password'] . ", " . $this->dbParams['database'];
            $this->db_log("connexion_bdd", $e->getCode(), $e->getMessage(), $errorPush);
        }
        if (isset($dbAccess)) {
            /* Modification du jeu de résultats en utf8 */
            try {
                mysqli_set_charset($dbAccess, "utf8");
            } catch (Exception $ex) {
                $sqlQuery = "Erreur lors du chargement du jeu de caractères utf8";
                $this->db_log("bdd_utf8", $ex->getCode(), $ex->getMessage(), $sqlQuery);
                $dbAccess = null;
            }
        }
        return $dbAccess;
    }

    private function getDBConnection() {
        $dbAccess = $this->db_connexion();
        return $dbAccess;
    }

    public function test_connexion() {
        return mysqli_ping($this->connexion);
    }

    public function db_close() {
        mysqli_close($this->connexion);
    }

    public function db_reconnect() {
        $this->db_close();
        try {
            $this->connexion = $this->getDBConnection();
        } catch (Exception $e_reco) {
            $to_log = "Tentative de Reconnexion a la BDD avec les parametres : " . $this->dbParams['host'] . ", " . $this->dbParams['user'] . ", " . $this->dbParams['password'] . ", " . $this->dbParams['database'];
            $this->db_log("bdd_reconnect", $e_reco->getCode(), $e_reco->getMessage(), $to_log);
        }
    }

    public function db_get_affected_rows() {
        return $this->affected_rows;
    }

    private function db_escape_string($string) {
        #$dbAccess = $this->connexion;
        if (get_magic_quotes_runtime()) {
            $string = stripslashes($string);
        }
        if (!preg_match("/^adddate\\(/i", $string)) {
            $string = mysqli_real_escape_string($this->connexion, $string);
        }
        return $string;
    }

    protected function db_log($nom_fichier_log, $code, $syntaxe, $requete) {
        global $log;
        $action = "erreur_bdd";
        $info['name'] = $nom_fichier_log;
        $info['code'] = $code;
        $info['syntaxe'] = str_ireplace(PHP_EOL, '{CR}', $syntaxe);
        $info['requete'] = str_ireplace(PHP_EOL, '{CR}', $requete) . ";";
        $log->cdr($action, $info);
    }

    /** reqLog enregistre la requte SQL transmise
     * @param string $rows le nombre de ligne affectées par la requete
     * @param string $requete contenu de la requête
     */
    protected function reqLog($rows, $requete) {
        global $log;
        $action = "requete_bdd";
        $info['affected_rows'] = $rows;
        $info['requete'] = str_ireplace(PHP_EOL, '{CR}', $requete) . ";";
        $log->cdr($action, $info);
    }

    public function db_insert($inserTable, $insertData) {
        $this->db_query("INSERT INTO $inserTable SET " . $this->buildAttributes($insertData));
        return mysqli_insert_id($this->connexion);
    }

    public function db_insert_ignore($inserTable, $insertData) {
        $this->db_query("INSERT IGNORE INTO $inserTable SET " . $this->buildAttributes($insertData));
        return mysqli_insert_id($this->connexion);
    }

    public function db_on_duplicate_key($inserTable, $insertData) {
        $i = 0;
        $complete = "";
        foreach ($insertData as $field => $value) {
            $complete .= ($i > 0 ? ', ' : '') . $field . '=' . "'" . $this->db_escape_string($value) . "'";
            $i++;
        }
        $this->db_query("INSERT INTO $inserTable SET " . $complete . " ON DUPLICATE KEY UPDATE " . $complete);
        return mysqli_insert_id($this->connexion);
    }

    public function db_update($updateTable, $updateData, $clauseWhere = '1') {
        $sqlQuery = "UPDATE " . $updateTable . " SET ";
        foreach ($updateData as $key => $value) {
            if (strtolower($value) == 'null') {
                $sqlQuery.= "$key = NULL";
            } elseif (strtolower($value) == 'now()') {
                $sqlQuery.= "$key = NOW(), ";
            } elseif (preg_match("/^increment\((\-?\d+)\)$/i", $value, $m)) {
                $sqlQuery.= "$key = $key + $m[1], ";
            } elseif (preg_match("/^decrement\((\-?\d+)\)$/i", $value, $m)) {
                $sqlQuery.= "$key = $key - $m[1], ";
            } elseif (preg_match("/^adddate\\(/i", $value)) {
                $sqlQuery.= "$key = " . $value . ", ";
            } else {
                $sqlQuery.= "$key='" . $this->db_escape_string($value) . "', ";
            }
        }
        $sqlQuery = rtrim($sqlQuery, ', ') . ' WHERE ' . $clauseWhere;
        return $this->db_query($sqlQuery);
    }

    protected function buildAttributes($attributes) {
        $keys = array_keys($attributes);
        $sqlQuery = '';
        $nb_keys = count($keys);
        for ($index = 0; $index < $nb_keys; $index++) {
            $key = $keys[$index];
            $value = $this->db_escape_string($attributes[$key]);
            if (strtolower($value) == 'null') {
                $value = "NULL";
            } elseif (strtolower($value) == 'now()') {
                $value = @date("Y-m-d H:i:s");
            }
            if (preg_match("/^adddate\\(/i", $value)) {
                $sqlQuery.= "$key = $value";
            } else {
                $sqlQuery .= "$key='$value'";
            }
            //We need to add a comma if not our last param
            if ($index !== count($keys) - 1) {
                $sqlQuery .= ', ';
            }
        }
        return($sqlQuery);
    }

    public function db_query($sqlQuery) {
        if (!$this->test_connexion()) {
            $this->db_reconnect();
        }
        // print $sqlQuery.";".PHP_EOL;
        try {
            $this->query_id = mysqli_query($this->connexion, $sqlQuery);
            $this->affected_rows = mysqli_affected_rows($this->connexion);
            $this->reqLog($this->affected_rows, $sqlQuery);
        } catch (Exception $e_query) {
            $this->query_id = -1;
            $this->affected_rows = 0;
            $this->db_log("bdd_requete", $e_query->getCode(), $e_query->getMessage(), $sqlQuery);
        }
        return $this->query_id;
    }

    private function db_free_result($query_id = -1) {
        if ($query_id !== -1) {
            $this->query_id = $query_id;
        }
        try {
            mysqli_free_result($this->query_id);
        } catch (Exception $ex) {
            $to_log = "Probleme dans la Liberation de resultat";
            $this->db_log("bdd_free_result", $ex->getCode(), $ex->getMessage(), $to_log);
        }
    }

    public function db_fetch_array($query_id = -1) {
        if ($query_id !== -1) {
            $this->query_id = $query_id;
        }
        try {
            $record = mysqli_fetch_array($this->query_id);
        } catch (Exception $ex) {
            $to_log = "Probleme dans la recherche de resultat sous forme de tableau indexe";
            $this->db_log("bdd_fetch_array", $ex->getCode(), $ex->getMessage(), $to_log);
            $record = null;
        }
        return $record;
    }

    public function db_fetch_assoc($query_id = -1) {
        if ($query_id !== -1) {
            $this->query_id = $query_id;
        }
        try {
            $record = mysqli_fetch_assoc($this->query_id);
        } catch (Exception $ex) {
            $to_log = "Probleme dans la recherche de resultat sous forme de tableau associatif";
            $this->db_log("bdd_fetch_array", $ex->getCode(), $ex->getMessage(), $to_log);
            $record = null;
        }
        return $record;
    }

    public function db_fetch_all($sql) {
        $query_id = $this->db_query($sql);
        $out = array();
        while ($row = $this->db_fetch_array($query_id)) {
            $out[] = $row;
        }
        return $out;
    }

    public function db_fetch_all_assoc($sql) {
        $query_id = $this->db_query($sql);
        $out = array();
        while ($row = $this->db_fetch_assoc($query_id)) {
            $out[] = $row;
        }
        return $out;
    }

    public function db_num_rows($query_id = -1) {
        if ($query_id !== -1) {
            $this->query_id = $query_id;
        }
        try {
            $row = mysqli_num_rows($this->query_id);
        } catch (Exception $ex) {
            $to_log = "Probleme dans le compte des numeros de lignes";
            $this->db_log("bdd_nums_rows", $ex->getCode(), $ex->getMessage(), $to_log);
            $row = 0;
        }
        return $row;
    }

    protected function parse_params($params) {
        $return = '';
        if ($params != null) {
            if (array_key_exists('where', $params)) {
                $return.= ' WHERE ' . $params['where'];
            }
            if (array_key_exists('order', $params)) {
                $return .= ' ORDER BY ' . $params['order'];
            }
            if (array_key_exists('orderdesc', $params)) {
                $return .= ' ORDER BY ' . $params['orderdesc'] . ' DESC';
            }
            if (array_key_exists('group', $params)) {
                $return .= ' GROUP BY ' . $params['group'];
            }
            if (array_key_exists('limit', $params)) {
                $return .= ' LIMIT ' . $params['limit'];
            }
        }
        return $return;
    }

    public function db_find_record($find, $findTable, $findParams = array(), $all = false, $read = NULL) {
        $sqlQuery = "SELECT $find FROM $findTable" . $this->parse_params($findParams);
        $result = $this->db_query($sqlQuery);
        if (isset($read) && $read == 1) {
            return $result;
        } else {
            $out = null;
            if ($all) {
                while ($row = $this->db_fetch_array($result)) {
                    $out[] = $row;
                }
                return $out;
            } else {
                return $this->db_fetch_array($result);
            }
        }
    }

    public function db_find_record_assoc($find, $findTable, $findParams = array(), $all = false, $read = NULL) {
        $sqlQuery = "SELECT $find FROM $findTable" . $this->parse_params($findParams);
        $result = $this->db_query($sqlQuery);
        if (isset($read) && $read == 1) {
            return $result;
        } else {
            $out = null;
            if ($all) {
                while ($row = $this->db_fetch_assoc($result)) {
                    $out[] = $row;
                }
                return $out;
            } else {
                return $this->db_fetch_assoc($result);
            }
        }
    }

}

?>
