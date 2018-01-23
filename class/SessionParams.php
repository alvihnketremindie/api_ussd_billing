<?php

class SessionParams {

    public function __construct($msisdn, $sessionid, $pagelevel = null) {
        $dbi = db_connect();
        if ($dbi->test_connexion()) {
            $findParams = array("where" => "sessionid = '$sessionid' AND msisdn = '$msisdn' AND statut = 'YES'", "order" => "id desc", "limit" => "1");
            if ($pagelevel) {
                $findParams["where"] .= " AND pagelevel = '$pagelevel'";
            }
            $this->set_values($dbi->db_find_record_assoc("*", "ussd_sessions", $findParams));
            $dbi->db_close();
        }
    }

    private function set_values($tab) {
        $this->record = $tab;
        if (isset($this->record) and ! empty($this->record)) {
            foreach ($this->record as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    public function get_record() {
        return isset($this->record) ? $this->record : null;
    }

    public function get_check_value($key) {
        return isset($this->{$key}) ? $this->{$key} : null;
    }

    public function desactivateLastSession() {
        $dbi = db_connect();
        if ($dbi->test_connexion()) {
            $updateReq = $dbi->db_update("ussd_sessions", array('statut' => 'NO'), "id >= {$this->id} and msisdn = '{$this->msisdn}' and sessionid = '{$this->sessionid}'");
			$affected_rows = $dbi->db_get_affected_rows();
            $dbi->db_close();
            if ($updateReq and $affected_rows > 0) {
                return true;
            }
        }
        return false;
    }
}

?>