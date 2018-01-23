<?php

class USSDRequest {

    public function __construct($headers, $request, $code = null) {
        $this->set_value("msisdn", $headers, 'User-MSISDN', 'NO-MSISDN');
        $this->set_value("sessionid", $headers, 'User-SessionId', md5(@date('Ymd').$this->msisdn));
        $this->set_value("payement", $headers, 'User-TOKEN', '');
        $this->set_value("userinput", $request, 'userinput', '');
        $this->set_value("pagelevel", $request, 'pagelevel', '0');
        $this->set_prefixpays();
        $this->code = isset($code) ? $code : 'default';
        $this->arrayElements["code"] = $this->code;
	logger(__FUNCTION__, array("header" => $headers, "request" => $request, "code" => $code));
    }

    public function set_value($value_name, $array, $key, $default_value = null) {
        $value = isset($array[$key]) ? $array[$key] : $default_value;
        $this->arrayElements[$value_name] = $value;
        $this->{$value_name} = $value;
    }

    public function set_prefixpays() {
        $this->msisdn = str_replace('acr:', '', $this->msisdn);
        $this->arrayElements["msisdn"] = $this->msisdn;
    }

    public function get_value($value_name) {
        return isset($this->{$value_name}) ? $this->{$value_name} : null;
    }

    public function getElements() {
        return $this->arrayElements;
    }

}

?>
