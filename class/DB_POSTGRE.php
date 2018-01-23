<?php

class DB_POSTGRE {

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
            $dbAccess = pg_connect("host=" . $this->dbParams['host'] . " dbname=" . $this->dbParams['database'] . " user=" . $this->dbParams['user'] . " password=" . $this->dbParams['password']);
        } catch (Exception $e) {
            $dbAccess = null;
            $errorPush = "Tentative de connexion a la BDD avec les parametres : " . $this->dbParams['host'] . ", " . $this->dbParams['user'] . ", " . $this->dbParams['password'] . ", " . $this->dbParams['database'];
            $this->db_log("connexion_bdd", $e->getCode(), $e->getMessage(), $errorPush);
        }
        if (pg_connection_status($dbAccess) === PGSQL_CONNECTION_OK) {
            /* Modification du jeu de résultats en utf8 */
            try {
                pg_set_client_encoding($dbAccess, "UNICODE");
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
        return pg_ping($this->connexion);
    }

    public function db_close() {
        pg_close($this->connexion);
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
        if (get_magic_quotes_runtime()) {
            $string = stripslashes($string);
        }
        if (!preg_match("/^adddate\\(/i", $string)) {
            $string = pg_escape_string($string);
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
        $sqlQuery = "INSERT INTO $inserTable";
        $sqlQuery .= $this->buildAttributes($insertData);
        return pg_last_oid($this->db_query($sqlQuery));
    }

    public function db_insert_ignore($inserTable, $insertData, $keysyntaxe) {
        $this->db_query("SELECT 1 FROM $inserTable WHERE $keysyntaxe");
        if (!empty($this->db_get_affected_rows())) {
            return $this->db_insert($inserTable, $insertData);
        } else {
            return 0;
        }
    }

    public function db_on_duplicate_key($inserTable, $insertData, $keysyntaxe) {
        $this->db_update($inserTable, $insertData, $keysyntaxe);
        if (!empty($this->db_get_affected_rows())) {
            return $this->db_insert($inserTable, $insertData);
        } else {
            return 0;
        }
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
        $str = " ";
        $strn = "(";
        $strv = " VALUES (";
        while (list($name, $value) = each($attributes)) {
            if (is_bool($value)) {
                $strn .= "$name,";
                $strv .= ($value ? "true" : "false") . ",";
                continue;
            }
            if (is_string($value)) {
                $strn .= "$name,";
                $strv .= "'$value',";
                continue;
            }
            if (!is_null($value) and ( $value != "")) {
                $strn .= "$name,";
                $strv .= "$value,";
                continue;
            }
        }
        $strn[strlen($strn) - 1] = ")";
        $strv[strlen($strv) - 1] = ")";
        $str .= $strn . $strv;
        return $str;
    }

    public function db_query($sqlQuery) {
        if (!$this->test_connexion()) {
            $this->db_reconnect();
        }
        // print $sqlQuery.";".PHP_EOL;
        try {
            $this->query_id = pg_query($this->connexion, $sqlQuery);
            $this->affected_rows = pg_affected_rows($this->connexion);
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
            pg_free_result($this->query_id);
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
            $record = pg_fetch_array($this->query_id);
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
            $record = pg_fetch_assoc($this->query_id);
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
            $row = pg_num_rows($this->query_id);
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
