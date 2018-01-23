<?php

class DATASNYC {

    protected $Id;
    protected $Idtransact;
    protected $msisdn;
    protected $spid;
    protected $opid;
    protected $event;
    protected $event_name;
    protected $datetime;
    protected $amount;
    protected $montant;
    protected $idabo;
    protected $externalid;
    protected $souscription;
    protected $url;
    protected $url_called;
    protected $response_app;
    protected $date_operation;
    protected $billing_config;
    public $to_print;

    public function __construct() {
        global $iniConfig;
        $this->billing_config = $iniConfig->return_config("billing");
        $this->request_elements();
        $this->set_value("to_print", "NOK;005");
        $this->set_value("amount", intval($this->get_value("amount")));
    }

    public function request_elements() {
        foreach ($_REQUEST as $key => $value) {
            $this->set_value($key, $value);
        }
    }

    public function validElements($db) {
        $this->check_presence($db);
        $this->check_url_infos($db);
        $this->set_event();
    }

    public function check_presence($db) {
        $subInfos = $db->db_find_record_assoc("*", "billing_sub", array("where" => "msisdn = '{$this->msisdn}' AND spid = '{$this->spid}'", "order" => "id desc", "limit" => "1"));
        if (isset($subInfos) and ! empty($subInfos)) {
            $this->set_value("id", $subInfos['id']);
            $this->set_value("externalid", $subInfos['externalid']);
            $this->set_value("aboid", $subInfos['aboid']);
            $this->set_value("montant", intval($subInfos['montant']));
            logger(__FUNCTION__, $subInfos);
            return TRUE;
        }
        return FALSE;
    }

    public function check_url_infos($db) {
        $urlInfos = $db->db_find_record_assoc("*", "billing_url", array("where" => "spid = '{$this->spid}' AND event = '{$this->event}' AND statut = 'YES'", "order" => "id desc", "limit" => "1"));
        if (isset($urlInfos) and ! empty($urlInfos)) {
            $this->set_value("url", $urlInfos['url']);
            $this->set_value("souscription", $urlInfos['souscription']);
            $this->set_value("event_name", $urlInfos['event_name']);
            if (!isset($this->montant)) {
                $this->set_value("montant", intval($urlInfos['montant']));
            }
            logger(__FUNCTION__, $urlInfos);
            //$thi
            return TRUE;
        }
        return FALSE;
    }

    public function set_event() {
        switch ($this->event) {
            case "1":
                $this->set_value("event_name", "Abonnement");
                break;
            case "2":
                $this->set_value("event_name", "Desabonnement");
                break;
            case "3":
                $this->set_value("event_name", "Billing");
                break;
            default:
                $this->set_value("event_name", "Inconnu");
                $this->set_value("to_print", "NOK;002");
                break;
        }
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

    public function send_to_app($db) {
        $to_print = $this->get_value("to_print");
        if (isset($this->url)) {
            $this->url_params();
            $this->set_value("response_app", exec_url($this->url_called));
            $to_print = "OK;" . $this->get_value("externalid");
        } elseif ($this->event == "2") {
            $to_print = "OK;" . $this->get_value("externalid");
        }
        $this->set_value("to_print", $to_print);
        $this->app_log($db);
        return $this->to_print;
    }

    protected function url_params() {
        $search = array('{msisdn}', '{telephone}', '{spid}', '{souscription}', '{amount}', '{montant}');
        $replace = array(urlencode($this->msisdn), urlencode($this->msisdn), $this->spid, $this->souscription, $this->amount, $this->montant);
        $this->url_called = str_ireplace($search, $replace, $this->url);
    }

    protected function app_log($db) {
        $billing_log = array(
            "Id" => $this->get_value("Id"),
            "Idtransact" => $this->get_value("Idtransact"),
            "msisdn" => $this->msisdn,
            "spid" => $this->spid,
            "opid" => $this->get_value("opid"),
            "event" => $this->event,
            "datetime" => @date('Y-m-d H:i:s'),
            "amount" => $this->amount,
            "idabo" => $this->get_value("idabo"),
            "externalid" => $this->get_value("externalid"),
            "to_print" => $this->to_print,
            "url_called" => $this->url_called,
            "response_app" => $this->response_app
        );
        $db->db_insert("billing_log", $billing_log);
        //logger(__FUNCTION__, $billing_log);
        $nlog = new LOG(LOG_PATH."/datasync/");
        $nlog->cdr("notification", $billing_log);
    }

}

?>
