<?php

class Billing {

    protected $type;
    protected $billing_config;
    protected $url;
    protected $externalid;

    public function __construct($array, $type) {
        global $iniConfig;
        $this->billing_config = $iniConfig->return_config("billing");
        foreach ($array as $key => $value) {
            $this->set_value($key, $value);
        }
        $this->type = $type;
    }

    public function validateRequest() {
        $this->status_mode = false;
        if (!isset($this->msisdn)) {
            $this->commentaire = 'msisdn is Missing';
        } elseif (!isset($this->spid)) {
            $this->commentaire = 'spid is Missing';
        } else {
            $this->status_mode = true;
        }
    }

    public function check_sub($db) {
        $subInfos = $db->db_find_record_assoc("*", "billing_sub", array("where" => "msisdn = '{$this->msisdn}' AND spid = '{$this->spid}'", "order" => "id desc", "limit" => "1"));
        if (isset($subInfos) and ! empty($subInfos)) {
            $this->set_value("id", @$subInfos['id']);
            $this->set_value("externalid", @$subInfos['externalid']);
            $this->set_value("aboid", @$subInfos['aboid']);
            logger(__FUNCTION__, $subInfos);
        }
    }

    public function insert_sub($db) {
        $inscrit["msisdn"] = $this->get_value("msisdn");
        $inscrit["spid"] = $this->get_value("spid");
        $inscrit["montant"] = $this->get_value("montant");
        $inscrit['date_operation'] = @date('Y-m-d H:i:s');
        $inscrit['statut'] = "on-confirm";
        $externalid = $db->db_on_duplicate_key("billing_sub", $inscrit);
        $this->set_value("externalid", $externalid);
        $db->db_update("billing_sub", array('externalid' => $externalid), "id = ".$externalid);
    }

    private function format_url($base_url) {
        $search = array("[CLE-SECURITE]", "[MSISDN]", "[SPID]", "[OPID]", "[EXTERNALID]", "[RefCode]", "[ABOID]");
        $replace = array($this->billing_config['key'], $this->get_value("msisdn"), $this->get_value("spid"), $this->billing_config['opid'], $this->get_value("externalid"), $this->get_value("refcode"), $this->get_value("aboid"));
        $this->url = str_ireplace($search, $replace, $base_url);
        $result = exec_url($this->url);
        return $result;
    }

    public function subscribe_request($db) {
        $this->insert_sub($db);
        $result = $this->format_url($this->billing_config['oneclick']);
        return $this->analyse_response($db,$result);
    }

    public function unsubscribe_request($db) {
        $this->check_sub($db);
        $result = $this->format_url($this->billing_config['unsubscribe']);
        return $this->analyse_response($db,$result);
    }

    public function analyse_response($db,$result) {
        $updateData['date_operation'] = @date("Y-m-d H:i:s");
        $response = explode(";", $result);
        if ($response[1] == "000") {
            $toPrint = "0";
            if (isset($response[2]) and ! empty($response[2])) {
                $updateData['aboid'] = $response[2];
            }
            $updateData['statut'] = $this->type;
            $db->db_update("billing_sub", $updateData, "id = ".$this->get_value("id"));
        } else {
            $toPrint = "-100";
            $updateData['statut'] = "echec_" . $this->type;
            $db->db_update("billing_sub", $updateData, "id = ".$this->get_value("id"));
        }
        $this->app_log($result, $toPrint);
        return $toPrint;
    }

    public function get_value($name) {
        if (isset($this->{$name}) and ! empty($this->{$name})) {
            return $this->{$name};
        } else {
            return null;
        }
    }

    protected function set_value($key, $value) {
        $this->{$key} = $value;
    }

    public function app_log($reponse, $toPrint) {
        $billing_infos['msisdn'] = $this->get_value("msisdn");
        $billing_infos['spid'] = $this->get_value("spid");
        $billing_infos['externalid'] = $this->get_value("externalid");
        $billing_infos['montant'] = $this->get_value("montant");
        $billing_infos['url'] = $this->url;
        $billing_infos['reponse'] = $reponse;
        $billing_infos['print'] = $toPrint;
        $app_log = new LOG(LOG_PATH . "/billing/");
        $app_log->cdr($this->type, $billing_infos);
    }

}

?>
