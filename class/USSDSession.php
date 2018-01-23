<?php

class USSDSession {

    public function __construct($infos, $serviceConfig) {
        $this->serviceConfig = $serviceConfig;
        $this->infoRequete = $infos;
    }

    public function run() {
        if (isset($this->serviceConfig) and ! empty($this->serviceConfig)) {
            $this->get_session_params();
            $service = new Service($this->serviceConfig);
            $this->responseArray = $service->call($this->infoRequete);
            logger(__FUNCTION__, array_merge($this->responseArray, $this->infoRequete, $this->serviceConfig));
            $newInfos = $this->infoRequete;
            $newInfos['previous'] = $this->infoRequete['next'];
            $newInfos['next'] = trim($this->responseArray['next']);
            $r_url = $this->serviceConfig["relative_url"];
        } else {
            $newInfos = $this->infoRequete;
            $newInfos['input'] = "-1";
            $newInfos['previous'] = "aucun";
            $r_url = null;
            $this->responseArray = array('next' => "unknow", 'FreeFlow' => 'FB', 'ussdString' => SERVICE_INDISPONIBLE, 'commentaire' => "Le service n'est pas encore configure");
        }
        new USSDOutput($newInfos, $this->responseArray, $r_url);
    }

    public function get_session_params() {
        $checkSessionParams = new SessionParams($this->infoRequete['msisdn'], $this->infoRequete['sessionid'], $this->infoRequete['pagelevel']);
        $onReturn = $checkSessionParams->get_record();
        if ($onReturn) {
            //Mode Retour ... On envoi un "0" a l'application
            $checkSessionParams->desactivateLastSession();
            $this->get_menu_precedent($onReturn);
        } else {
            //Le cas de retour n'est pas respecte ... On navigue en mode foward dans le menu
            //Parametres precedement envoyes
            $previousSessionParams = new SessionParams($this->infoRequete['msisdn'], $this->infoRequete['sessionid']);
            $previoussession = $previousSessionParams->get_record();
            if ($previoussession) {
                $this->get_next_menu($previoussession);
            } else {
                //Si aucun des cas n'est utilise on part au menu principal
                $this->get_menu_principal();
            }
        }
        logger(__FUNCTION__, $this->infoRequete);
    }

    public function get_menu_precedent($onReturn) {
        $this->infoRequete['input'] = "0";
        $this->infoRequete['previous'] = $onReturn["previous"];
        $this->infoRequete['next'] = $onReturn["next"];
        $this->infoRequete['retour'] = "yes";
        $this->infoRequete['userinput'] = "no";
        $this->infoRequete['inputnumber'] = "0";
        $this->infoRequete['commentaire'] = "Retour au menu precedent";
    }

    public function get_next_menu($previoussession) {
        $this->infoRequete['previous'] = $previoussession["previous"];
        $this->infoRequete['next'] = $previoussession["next"];
        $this->infoRequete['retour'] = "no";
        if (isset($this->infoRequete['userinput']) && !empty($this->infoRequete['userinput'])) {
            $this->infoRequete['input'] = $this->infoRequete['userinput'];
            $this->infoRequete["inputnumber"] = "";
        } else {
            $input = substr($this->infoRequete['pagelevel'], -1);
            $this->infoRequete['input'] = $input;
            $this->infoRequete["userinput"] = '';
            $this->infoRequete["inputnumber"] = $input;
        }
        $this->infoRequete['commentaire'] = "Menu de navigation";
    }

    public function get_menu_principal() {
        $this->infoRequete['input'] = "";
        $this->infoRequete['previous'] = "";
        $this->infoRequete['next'] = "menu";
        $this->infoRequete['retour'] = "no";
        $this->infoRequete['userinput'] = "";
        $this->infoRequete['inputnumber'] = "";
        $this->infoRequete['commentaire'] = "Menu principal";
        desactivateOldSessions($this->infoRequete['msisdn'], $this->infoRequete['sessionid']);
    }

}

?>