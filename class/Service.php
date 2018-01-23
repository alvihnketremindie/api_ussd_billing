<?php

class Service {

    public function __construct($tab) {
        foreach ($tab as $key => $value) {
            $this->{$key} = $value;
        }
    }

    private function isAllowed($telephone) {
        if ($this->check_blacklist($telephone)) {
            return FALSE;
        } elseif ($this->status == "TEST" && preg_match('/' . $telephone . '/', $this->testlist)) {
            logger(__FUNCTION__, array("commentaire" => "Numero dans la liste de test", "telephone" => $telephone, "service" => $this->libelle));
            return TRUE;
        } elseif ($this->status == 'PROD') {
            return TRUE;
        } else {
            logger(__FUNCTION__, array("commentaire" => "Autre erreur", "telephone" => $telephone, "service" => $this->libelle));
            return FALSE;
        }
    }

    private function check_blacklist($telephone) {
        if (file_exists($this->blacklist)) {
            $cmd = 'cat "' . $this->blacklist . '" | grep ' . $telephone . ' | wc -l';
            $res = intval(exec($cmd));
            if ($res > 0) {
                logger(__FUNCTION__, array("commentaire" => "Numero dans la blacklist", "telephone" => $telephone, 'blacklist' => $this->blacklist, "commande" => $cmd, "occurence" => $res, "service" => $this->libelle));
                return TRUE;
            }
        }
        return FALSE;
    }

    private function check_nouvelle_url($telephone, $servicecode) {
        $dbi = db_connect();
        if ($dbi->test_connexion()) {
            $findParams = array("where" => "telephone = '$telephone' AND servicecode = '$servicecode'", "order" => "id", "limit" => "1");
            $record = $dbi->db_find_record_assoc("*", "test_app", $findParams);
            if (is_array($record)) {
                $this->url = $record['url'];
                logger(__FUNCTION__, array("commentaire" => "Nouvelle URL trouver", "telephone" => $telephone, 'servicecode' => $servicecode, "URL" => $this->url, "service" => $this->libelle));
            }
            $dbi->db_close();
        } else {
            logger(__FUNCTION__, array("commentaire" => ERREUR_CONNEXION_BDD, "service" => $this->libelle));
        }
    }

    public function call($info) {
        $this->check_nouvelle_url($info['msisdn'], $info['code']);
        $urlArrayParams = array(
            'SOA' => $info['msisdn'],
            'DA' => @$this->syntaxe,
            'canal' => @$this->canal,
            'Content' => $info['input'],
            'next' => $info['next'],
            'sessionId' => $info['sessionid'],
            'payement' => $info['payement']
        );
        $urlStringParams = http_build_query($urlArrayParams);
        $url = $this->url . "?" . $urlStringParams;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = trim(strip_tags(curl_exec($ch)));
        $headersize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $tab = array('FreeFlow' => 'FC', 'next' => 'menu', 'ussdString' => '');
        $header = substr($response, 0, $headersize);
        $body = substr($response, $headersize);
        $headers = explode("\r\n", $header);
        foreach ($headers as $explodeHeader) {
            if (preg_match('/payement/', $explodeHeader) or preg_match('/next/', $explodeHeader) or preg_match('/FreeFlow/', $explodeHeader) or preg_match('/ussdString/', $explodeHeader)) {
                list($key, $value) = explode(':', $explodeHeader, 2);
                $key = trim($key);
                $tab[$key] = trim($value);
            }
        }
        if (isset($body) && !empty($body)) {
            $body = str_replace("ussdString:", "", $body);
            $tab['ussdString'] = $body;
        }
        $search = array("<br>", "<br />", "\r\n", "\n\r", "\n");
        $replace = array("{CR}", "{CR}", "{CR}", "{CR}", "{CR}");
        $tab['ussdString'] = str_replace(array("<br>", "<br />", "\r\n", "\n\r", "\n"), array("{CR}", "{CR}", "{CR}", "{CR}", "{CR}"), $tab['ussdString']);
        $tab['ussdString'] = str_replace(PHP_EOL, "{CR}", $tab['ussdString']);
        $tab["url_called"] = $url;
		logger("callUssdService", array("payement" => @$tab["payement"], "next" => $tab["next"], "FreeFlow" => $tab["FreeFlow"], "url" => $url, "service" => $this->libelle));
        return $tab;
    }

}

?>