<?php

class USSDOutput {

    public function __construct($infos, $responseArray, $relative_url = null) {
        $this->relative_url = $relative_url;
        $this->pagelevel = $infos["pagelevel"];
        $this->next = $responseArray["next"];
        $this->FreeFlow = $responseArray["FreeFlow"];
        $this->ussdString = $responseArray["ussdString"];
        $this->msisdn = $infos["msisdn"];
        $this->sessionid = $infos["sessionid"];
        $this->pay_token = @$infos["payement"];
        $this->pay_number = intval(@$responseArray["payement"]);
        $ussd_response = $this->getResponse();
        print $ussd_response;
        $url_called = $responseArray["url_called"];
        $this->enregParams($infos["previous"], $ussd_response, $url_called);
    }

    protected function enregParams($previous, $ussd_response, $url_called) {
        $insertData = array(
            "date" => @date("Y-m-d H:i:s"),
            "msisdn" => $this->msisdn,
            "sessionid" => $this->sessionid,
            "pagelevel" => $this->pagelevel,
            "next" => $this->next,
			"previous" => $previous
        );
        $dbi = db_connect();
        if ($dbi->test_connexion()) {
            $dbi->db_insert("ussd_sessions", $insertData);
            $dbi->db_close();
        }
        $insertData["pay_number"] = $this->pay_number;
        $insertData["pay_token"] = $this->pay_token;
        $insertData["ussdString"] = $this->ussdString;
        $insertData["FreeFlow"] = $this->FreeFlow;
        $insertData["url_called"] = $url_called;
        $insertData["ussd_response"] = $ussd_response;
        $insertData["relative_url"] = $this->relative_url;
        logger("USSDOutput", $insertData);
        $ussd_log = new LOG(LOG_PATH . '/ussd/');
        $ussd_log->cdr("ussd_output",$insertData);
    }

    public function getResponse() {
        if ($this->FreeFlow == "FB") {
            $toPrint = $this->getResponseEnd();
            desactivateOldSessions($this->msisdn, $this->sessionid);
        } elseif ($this->FreeFlow == "FC") {
            $toPrint = $this->getResponseMenu();
        } else {
            $toPrint = $this->getResponseUserInput();
        }
        return $toPrint;
    }

    protected function getResponseEnd() {
        $element = '';
        $element .= '<?xml version="1.0" encoding="UTF-8"?>';
        $element .= "<html>";
        $element .='<head><meta name="nav" content="end"/></head>';
        $element .= "<body>";
        $menu = preg_replace("#<br>|\\r\\n|\\n|\\r|\\t|<br \/>|{CR}#i", "<br />", $this->ussdString);
        $element .= $menu;
        $element .= "</body>";
        $element .= "</html>";
        return $element;
    }

    protected function getResponseMenu() {
        $element = '';
        $element .= '<?xml version="1.0" encoding="UTF-8"?> ';
        $element .= '<html>';
        $element .= '<body>';
        $menu = preg_replace("#<br>|\r\n|\n|\r|\t|<br />|{CR}#i", "{CR}", $this->ussdString);
        $tableau = explode("{CR}", $menu);
        foreach ($tableau as $value) {
            $value = trim($value);
            $first = @$value[0];
            if (!ctype_digit($first) and $first != "*" and $first != "#") {
                $entete = preg_replace("#<br>|\r\n|\n|\r|\t|<br />|{CR}#i", "<br/>", $value);
                $element .= $entete . '<br/>';
            } elseif (!in_array($first, array("0", "00"))) {
                $reste = trim(substr($value, 1));
                $firstC = @$reste[0];
                while (!ctype_alpha($firstC)) {
                    if (ctype_digit($firstC))
                        $first .= $firstC;
                    $reste = substr($reste, 1);
                    $firstC = @$reste[0];
                }
                $rubrique = preg_replace("#<br>|\r\n|\n|\r|\t|<br />|{CR}#i", " ", $reste);
				$relative_url = "";
				if(isset($this->pay_number) and !empty($this->pay_number) and $first == $this->pay_number) {
					$relative_url .="/buy/"; //Redirection configurer dans proxy_ajp.conf
				}
				$relative_url.= $this->relative_url."?" . "pagelevel=" . urlencode($this->pagelevel."_" . $first);
                $element .= '<a href="' . $relative_url . '" accesskey="' . $first . '">' . $rubrique . '</a>' . '<br/>';
            }
        }
        $element .= '</body>';
        $element .= '</html>';
        return $element;
    }

    protected function getResponseUserInput() {
        $element = '';
        $element .= '<?xml version="1.0" encoding="UTF-8"?>';
        $element .= '<html>';
        $element .= '<body>';
        $element .= preg_replace("#<br>|\r\n|\n|\r|\t|<br />|{CR}#i", "<br/>", $this->ussdString);
        $element .= '<form action="' . $this->relative_url . '"><input type="text" name="userinput"/></form>';
        $element .= '</body>';
        $element .= '</html>';
        return $element;
    }

}

?>