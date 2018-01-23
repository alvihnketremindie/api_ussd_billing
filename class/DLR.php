<?php

class DLR {

    public function __construct($array) {
        $this->hydrate($array);
    }

    private function hydrate($array) {
        foreach ($array as $key => $value) {
            $method = 'set_' . strtolower($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function set_msisdn($value) {
        $this->_msisdn = $value;
    }

    public function get_msisdn() {
        return $this->_msisdn;
    }

    public function set_code($value) {
        $this->_code = $value;
    }

    public function get_code() {
        return $this->_code;
    }

    public function set_msg($value) {
        $this->_msg = $value;
    }

    public function get_msg() {
        return $this->_msg;
    }

    public function set_sessionid($value) {
        $this->_sessionid = $value;
    }

    public function get_sessionid() {
        return $this->_sessionid;
    }

    public function __toString() {
        return "|msisdn:" . $this->get_msisdn() . '|code:' . $this->get_code() . '|msg:' . $this->get_msg() . '|sessionid:' . $this->get_sessionid();
    }

}

?>