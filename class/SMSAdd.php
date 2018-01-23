<?php

class SMSAdd {

    public $status_mode = False;
    public $commentaire = "";

    function __construct($request) {
        $this->set_value("sendername", $request, 'from');
        $this->set_value("message", $request, 'text');
        $this->set_value("receiver", $request, 'to');
        $this->validateRequest();
    }

    public function get_value($value_name) {
        return isset($this->{$value_name}) ? $this->{$value_name} : null;
    }

    public function getElements() {
        return $this->arrayElements;
    }

    public function set_value($value_name, $array, $key, $default_value = null) {
        $value = isset($array[$key]) ? $array[$key] : $default_value;
        $this->arrayElements[$value_name] = $value;
        $this->{$value_name} = $value;
    }

    public function setIniElements(ParseConfig $ini) {
        $this->operateurConfig = $ini->return_config($this->operateur);
    }

    public function validateRequest() {
        $this->status_mode = false;
        if ($this->sendername == null) {
            $this->commentaire = 'SenderAdress is Missing';
        } elseif ($this->message == null) {
            $this->commentaire = 'message is Missing';
        } elseif ($this->receiver == null) {
            $this->commentaire = 'receiver is Missing';
        } else {
            $this->status_mode = true;
        }
    }

}

?>