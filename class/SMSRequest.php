<?php

class SMSRequest {

    public function __construct($array) {
        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function send($url, $headers) {
        $this->url = str_replace('{msisdn}', urlencode($this->receiver), $url);
        $this->headers = str_replace('{X-Orange-ISE2}', str_replace('acr:', '', $this->receiver), $headers);
        $fieldsString = json_encode(array('outboundSMSMessageRequest' => array(
                'address' => "acr:X-Orange-ISE2",
                'outboundSMSTextMessage' => array('message' => $this->message),
                'senderAddress' => "tel:+23700000",
                'senderName' => $this->sendername
        )));
        $result = post_request($this->headers, $fieldsString, $this->url);
        $code = @$result['code'];
        if ($code == '201') {
            $reponse = "0 : Accepted";
        } else {
            $reponse = "-2 : Failed";
        }
        $sms_log = new LOG(LOG_PATH . '/sms/');
        $sms_log->cdr("send_sms", array("msidn" => $this->receiver, "message" => $this->message, "sender" => $this->sendername, "reponse" => $reponse));
        logger(__FUNCTION__, array("result" => $result, "json" => $fieldsString, "headers" => $this->headers, "url" => $this->url));
        return $reponse;
    }

    protected function get_value($name) {
        if (isset($this->{$name}) and ! empty($this->{$name})) {
            return $this->{$name};
        } else {
            return null;
        }
    }

}

?>
