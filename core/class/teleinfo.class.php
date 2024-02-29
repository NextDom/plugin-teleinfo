<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../../../core/php/core.inc.php';

class teleinfo extends eqLogic
{

    public static function getTeleinfoInfo($_url)
    {
//        $returnSerial = self::deamon_infoSerial();
//        $returnMqtt = self::deamon_infoMqtt();
//        if ($returnSerial['state'] != 'ok' && $returnMqtt['state'] != 'ok') {
        $return = self::deamon_info();
        if ($return['state'] != 'ok'){
            return "";
        }
    }

    public static function cron()
    {
        self::calculatePAPP();
//        self::calculateOtherStats();
//        self::calculateTodayStats();
    }

    public static function cronHourly()
    {
        self::moyLastHour();
        cache::set('teleinfo::regenerateMonthlyStat', '0');
        log::add('teleinfo', 'debug', 'cronhourly ');
    }

    // Fonction pour exclure un sous répertoire de la sauvegarde
    public static function backupExclude() {
		return ['ressources/venv'];
	}

    public static function changeLogLive($level)
    {
        $activation_Modem = (config::byKey('activation_Modem', 'teleinfo') == "") ? 1 : config::byKey('activation_Modem', 'teleinfo');
        $activation_Mqtt = (config::byKey('activation_Mqtt', 'teleinfo') == "") ? 0 : config::byKey('activation_Mqtt', 'teleinfo');
        $productionActivated = (config::byKey('port_modem2', 'teleinfo') == "") ? 0 : config::byKey('port_modem2', 'teleinfo');
        if (($activation_Modem=='0') && ($activation_Mqtt=='0')) {
            log::add('teleinfo', 'info', 'pas d envoi de message faute de configuration');
            return false;
        }
        sleep(1); // attend que le level ait eu le temps de s'écrire dans la bdd
        $value['cmd'] = 'changelog';
        $value['level'] = log::convertLogLevel(log::getLogLevel('teleinfo'));
        $socketport = config::byKey('socketport', __CLASS__, '55062');
        $value['apikey'] = jeedom::getApiKey(__CLASS__);
        if ($activation_Modem==1 && $productionActivated == 0){
            self::sendToDaemon($value,'serial', $socketport);
        }
        if ($activation_Mqtt==1){
            // $value = json_encode($value);
            self::sendToDaemon($value,'mqtt', $socketport + 2);
        }
        if ($activation_Modem==1 && $productionActivated == 1) {
            self::sendToDaemon($value,'prod', $socketport + 1);
        }
}

    public static function sendToDaemon($params,$mode,$socketport) { // le mode peut être serial, mqtt ou prod
        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] != 'ok') {
            throw new Exception("Le démon ". $mode . " n'est pas démarré");
        }
        $params['apikey'] = jeedom::getApiKey('teleinfo');
        $payLoad = json_encode($params);
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, config::byKey('sockethost', 'teleinfo', '127.0.0.1'), $socketport);
        socket_write($socket, $payLoad, strlen($payLoad));
        socket_close($socket);
        return true;
    }

	/**
	 * Test si la version est béta
	 * @param bool $text
	 * @return $isBeta
	 */
    public static function isBeta($text = false) {
        $plugin = plugin::byId('teleinfo');
        $update = $plugin->getUpdate();
        $isBeta = false;
        if (is_object($update)) {
            $version = $update->getConfiguration('version');
            $isBeta = ($version && $version != 'stable');
        }
    
        if ($text) {
          return $isBeta ? 'beta' : 'stable';
        }
        return $isBeta;
      }
    
	/**
	 * Creation objet sur reception de trame
	 * @param string $adco
	 * @return eqLogic
	 */
    public static function createFromDef(string $adco)
    {
        $color = ['#D62828','#001219','#005F73','#0A9396','#94D2BD',
                    '#E9D8A6','#ee9b00','#ca6702','#bb3e03','#ae2012',
                    '#9b2226','#ed9448','#7cb5ec','#d62828','#00FF00'];
        $autorisationCreationObjet = config::byKey('createNewADCO', 'teleinfo');
        if ($autorisationCreationObjet != 1) {
            $teleinfo = teleinfo::byLogicalId($adco, 'teleinfo');
            if (!is_object($teleinfo)) {
                $eqLogic = (new teleinfo())
                        ->setName($adco);
            }
            $eqLogic->setLogicalId($adco)
                    ->setEqType_name('teleinfo')
                    ->setIsEnable(1)
                    ->setIsVisible(1)
                    ->setconfiguration('AutoCreateFromCompteur','1')
                    ->setconfiguration('color0',$color[0])
                    ->setconfiguration('color1',$color[1])
                    ->setconfiguration('color2',$color[2])
                    ->setconfiguration('color3',$color[3])
                    ->setconfiguration('color4',$color[4])
                    ->setconfiguration('color5',$color[5])
                    ->setconfiguration('color6',$color[6])
                    ->setconfiguration('color7',$color[7])
                    ->setconfiguration('color8',$color[8])
                    ->setconfiguration('color9',$color[9])
                    ->setconfiguration('color10',$color[10])
                    ->setconfiguration('color11',$color[11])
                    ->setconfiguration('color12',$color[12])
                    ->setconfiguration('color13',$color[13])
                    ->setconfiguration('color14',$color[14]);
            $eqLogic->save();
            return $eqLogic;
        } else {
            return null;
        }
    }
	/**
	 * Creation commande sur reception de trame
	 * @param $oADCO identifiant compteur
	 * @param $oKey etiquette
	 * @param $oValue valeur
	 * @return Commande
	 */
    public static function createCmdFromDef($oADCO, $oKey, $oValue)
    {
        if (!isset($oKey) || !isset($oADCO)) {
            log::add('teleinfo', 'error', '[TELEINFO]-----Information manquante pour ajouter l\'équipement : ' . print_r($oKey, true) . ' ' . print_r($oADCO, true));
            return false;
        }
        $teleinfo = teleinfo::byLogicalId($oADCO, 'teleinfo');
        if (!is_object($teleinfo)) {
            return false;
        }
        if ($teleinfo->getConfiguration('AutoCreateFromCompteur') == '1') {
            log::add('teleinfo', 'info', 'Création de la commande ' . $oKey . ' sur l\'ADCO ' . $oADCO);
            $cmd = (new teleinfoCmd())
                    ->setName($oKey)
                    ->setLogicalId($oKey)
                    ->setType('info');
            switch ($oKey) {
				case "ADSC":
                case "OPTARIF":
                case "PTEC":
                case "DEMAIN":
                case "MOTDETAT":
                case "HPHC":
                case "PPOT":
                case "NGTF":
                case "LTARF":
                case "STGE":
                case "STGE01":
                case "STGE02":
                case "STGE03":
                case "STGE04":
                case "STGE05":
                case "STGE06":
                case "STGE07":
                case "STGE08":
                case "STGE09":
                case "STGE10":
                case "STGE11":
                case "STGE12":
                case "STGE13":
                case "STGE14":
                case "STGE15":
                case "STGE16":
                case "STGE17":
                case "STGE18":
                case "STGE19":
                case "STGE20":
                case "DPM1":
                case "FPM1":
                case "DPM2":
                case "FPM2":
                case "DPM3":
                case "FPM3":
                case "MSG1":
                case "MSG2":
                case "PRM":
                case "NJOURF":
                case "NJOURF+1":
                case "PJOURF+1":
                case "PPOINTE":
                case "RELAIS":
                case "RELAIS01":
                case "RELAIS02":
                case "RELAIS03":
                case "RELAIS04":
                case "RELAIS05":
                case "RELAIS06":
                case "RELAIS07":
                case "RELAIS08":
                    $cmd->setSubType('string')
                            ->setDisplay('generic_type', 'GENERIC_INFO');
                    break;
                default:
                    $cmd->setSubType('numeric')
                            ->setDisplay('generic_type', 'GENERIC_INFO');
                    break;
            }
			$cmd->setEqLogic_id($teleinfo->id);
            $cmd->setConfiguration('info_conso', $oKey);
            $cmd->setIsHistorized(1)->setIsVisible(1);
            $cmd->save();
            $cmd->event($oValue);
            return $cmd;
        }
    }

	/**
	 * Fonction de détection du type de compteur
	 * @param $port
	 * @return $return
	 */
	public static function findModemType(string $port, string $type)
    {
		$twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
		if ($twoCptCartelectronic == 1) {
			$return['state'] = 'nok';
            $return['message'] = 'Non disponible pour le modem 2 compteurs. Veuillez regarder la zone Configuration avancée afin de configurer le modem.';
			return $return;
		}
        if ($type == "usb") {
            $port = jeedom::getUsbMapping($port);
        }

/*		exec('stty -F ' . $port . ' 1200 sane evenp parenb cs7 -crtscts');
        passthru('timeout 5 sed -n 5,8p ' . $port, $return['data']);
        log::add('teleinfo', 'debug', "retour : " . $return['data']);
		if ($return['data'] > 5){
            $return['state'] = 'ok';
            $return['type'] = 'historique';
            $return['linky'] = false;
            $return['vitesse'] = '1200';
            $return['message'] = 'Il s\'agit d\'un compteur en mode historique.';
        }
        else {
            exec('stty -F ' . $port . ' 9600 sane evenp parenb cs7 -crtscts');
			passthru('timeout 5 sed -n 5,8p ' . $port, $return['data']);
			if ($return['data'] > 5){
				$return['state'] = 'ok';
				$return['type'] = 'standard';
                $return['linky'] = true;
				$return['vitesse'] = '9600';
				$return['message'] = 'Il s\'agit d\'un compteur en mode standard.';
			}
			else {
				$return['state'] = 'nok';
				$return['type'] = '';
				$return['vitesse'] = '';
				$return['message'] = 'Impossible de détecter le type de compteur.';
			}
        }
*/
        // en attendant de faire fonctionner le test de vitesse du modem
        $return['state'] = 'nok';
        $return['type'] = '';
        $return['vitesse'] = '';
        $return['message'] = 'Cette fonction n est pas opérationnelle, configurez la vitesse du port manuellement.';
        // --------------------------------------------------------------
        return $return;
	}

	/**
     *
     * @param type $debug
     * @param type $type
     * @return boolean
     */
    public static function runDeamon($debug = false, $type = 'conso', $mqtt = false)
    {
        $teleinfoPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
        $activation_Modem = (config::byKey('activation_Modem', 'teleinfo') == "") ? 1 : config::byKey('activation_Modem', 'teleinfo');
        if ($activation_Modem==''){
            $activation_Modem = 1;
            log::add('teleinfo', 'info', '---------- Activation Modem 1---------');
        }
        log::add('teleinfo', 'info', '[' . $type . '] Démarrage daemon ');

        if ($type!='rien'){
            log::add('teleinfo', 'info', '[' . $type . '] Démarrage compteur ');
            if ($type == 'conso') {
                $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
                $linky                = config::byKey('linky', 'teleinfo');
                $modemVitesse         = config::byKey('modem_vitesse', 'teleinfo');
                $socketPort			  = config::byKey('socketport', 'teleinfo', '55062');
                if (config::byKey('port', 'teleinfo') == "serie") {
                    $port = config::byKey('modem_serie_addr', 'teleinfo');
                }
                else {
                    $port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
                    if ($twoCptCartelectronic == 1) {
                        $port = '/dev/ttyUSB1';
                    } else {
                        if (is_string($port)) {
                            if (!file_exists($port)) {
                                log::add('teleinfo', 'error', '[TELEINFO]-----[' . $type . '] Le port1 '. $port . ' n\'existe pas');
                                return false;
                            }
                        }
                    }
                }
            }
            if ($type == 'prod') {
                $twoCptCartelectronic = config::byKey('2cpt_cartelectronic_production', 'teleinfo');
                $linky                = config::byKey('linky_prod', 'teleinfo');
                $modemVitesse         = config::byKey('modem_compteur2_vitesse', 'teleinfo');
                $socketPort			  = config::byKey('socketport', 'teleinfo', '55062') + 1;
                if (config::byKey('port_modem2', 'teleinfo') == "serie") {
                    $port = config::byKey('modem_serie_compteur2_addr', 'teleinfo');
                } else {
                    $port = jeedom::getUsbMapping(config::byKey('port_modem2', 'teleinfo'));
                    if ($twoCptCartelectronic == 1) {
                        $port = '/dev/ttyUSB1';
                    } else {
                        if (is_string($port)) {
                            if (!file_exists($port)) {
                                log::add('teleinfo', 'error', '[TELEINFO]-----[' . $type . '] Le port2 '. $port . ' n\'existe pas');
                                return false;
                            }
                        }
                    }
                }
            }
            if ($linky == 1) {
                $mode = 'standard';
                if ($modemVitesse == "") {
                    $modemVitesse = '9600';
                }
            } else {
                $mode = 'historique';
                if ($modemVitesse == "") {
                    $modemVitesse = '1200';
                }
            }

            exec('sudo chmod 777 ' . $port . ' > /dev/null 2>&1');


            log::add('teleinfo', 'info', '---------- Informations de lancement ---------');
            log::add('teleinfo', 'info', 'Port modem : ' . $port);
            log::add('teleinfo', 'info', 'Socket : ' . $socketPort);
            log::add('teleinfo', 'info', 'Type : ' . $type);
            log::add('teleinfo', 'info', 'Mode : ' . $mode);
            log::add('teleinfo', 'info', '---------------------------------------------');

            if ($twoCptCartelectronic == 1) {
                log::add('teleinfo', 'info', '[' . $type . '] Fonctionnement en mode 2 compteur');
                $cmd          = 'sudo nice -n 19 ' . $teleinfoPath . '/venv/bin/python3 ' . $teleinfoPath . '/teleinfo_2_cpt.py';
                //$cmd          = 'sudo nice -n 19 /usr/bin/python3 ' . $teleinfoPath . '/teleinfo_2_cpt.py';
            }
            else {
                log::add('teleinfo', 'info', '[' . $type . '] Fonctionnement en mode 1 compteur');
                $cmd          = 'nice -n 19 ' . $teleinfoPath . '/venv/bin/python3 ' . $teleinfoPath . '/teleinfo.py';
                //$cmd          = 'nice -n 19 /usr/bin/python3 ' . $teleinfoPath . '/teleinfo.py';
                $cmd         .= ' --type ' . $type;
            }
            $cmd         .= ' --port ' . $port;
            $cmd         .= ' --vitesse ' . $modemVitesse;
            $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
            $cmd         .= ' --mode ' . $mode;
            $cmd         .= ' --socketport ' . $socketPort;
            $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
            $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
            $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));
            $cmd         .= ' --cyclesommeil ' . config::byKey('cycle_sommeil', 'teleinfo', '0.5');
            $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));

            log::add('teleinfo', 'info', '[' . $type . '] Exécution du service : ' . $cmd);
            $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('teleinfo_deamon_' . $type) . ' 2>&1 &');
            if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
                log::add('teleinfo', 'error', '[TELEINFO]-----' . $result);
                return false;
            }
            sleep(2);
            if (!self::deamonRunning('')) {
                sleep(10);
                if (!self::deamonRunning('')) {
                    log::add('teleinfo', 'error', '[TELEINFO_' . $type . '] Impossible de lancer le démon téléinfo, vérifiez la configuration.', 'unableStartDeamon');
                    return false;
                }
            }
            message::removeAll('teleinfo', 'unableStartDeamon');
            log::add('teleinfo', 'info', '[' . $type . '] Service OK');
            log::add('teleinfo', 'info', '---------------------------------------------');
        }
    }
    
    public static function runDeamonMqtt($debug = false, $type = 'mqtt'){
  
        $teleinfoPath   = realpath(dirname(__FILE__) . '/../../ressources');
        $socketPort 	= config::byKey('socketport', 'teleinfo', '55062') + 2;
        $socketHost 	= config::byKey('socketHost', 'teleinfo', '127.0.0.1');
        $mqtt_broker 	= config::byKey('mqtt_broker', 'teleinfo', '127.0.0.1');
        $mqtt_port 	    = config::byKey('mqtt_port', 'teleinfo', '1883');
        $mqtt_topic 	= config::byKey('mqtt_topic', 'teleinfo', '#');
        $mqtt_username 	= config::byKey('mqtt_username', 'teleinfo', 'aucun_pour_etre_certain');
        $mqtt_password 	= config::byKey('mqtt_password', 'teleinfo', 'aucun_pour_etre_certain');
        $keep_alive     = 45; # interval en seconde
        log::add('teleinfo', 'info', '---------------------------------------------');
        log::add('teleinfo', 'info', '[MQTT] Démarrage service MQTT ');
        log::add('teleinfo', 'info', "SocketHost : " . $socketHost);
        log::add('teleinfo', 'info', "Socketport : " . $socketport);
        log::add('teleinfo', 'info', "Broker : " . $mqtt_broker);
        log::add('teleinfo', 'info', "Port du Broker : " . $mqtt_port);
        log::add('teleinfo', 'info', "topic : " . '"' . $mqtt_topic . '"');
        log::add('teleinfo', 'info', '---------------------------------------------');
        $cmd          = 'nice -n 19 ' . $teleinfoPath . '/venv/bin/python3 ' . $teleinfoPath . '/teleinfo_mqtt.py';
        $cmd         .= ' --socketport ' . $socketPort;
        $cmd         .= ' --mqtt True';
        $cmd         .= ' --mqtt_broker ' . $mqtt_broker;
        $cmd         .= ' --mqtt_port ' . $mqtt_port;
        $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
        $cmd         .= ' --mqtt_keepalive ' . $keep_alive;
        $cmd         .= ' --mqtt_username ' . $mqtt_username;
        $cmd         .= ' --mqtt_password ' . $mqtt_password;
        $cmd         .= ' --modem aucun';
        $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
        $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));
        $cmd         .= ' --mqtt_topic ' . '"' . $mqtt_topic . '"';
        log::add('teleinfo', 'info', '[découverte MQTT] Exécution du service : ' . $cmd);
        $result = exec($cmd . ' >> ' . log::getPathToLog('teleinfo_deamon_Mqtt') . ' 2>&1 &');
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
            log::add('teleinfo', 'error', $result);
            return false;
        }
        sleep(2);
        if (!self::runningMqtt('non')) {
            sleep(10);
            if (!self::runningMqtt('oui')) {
                log::add('teleinfo', 'error', '[TELEINFO_mqtt] Impossible de lancer le démon téléinfo, vérifiez la configuration.', 'unableStartDeamon');
                return false;
            }
        }
        message::removeAll('teleinfo', 'unableStartDeamon');
        log::add('teleinfo', 'info', '[mqtt] Service OK');
        log::add('teleinfo', 'info', '[mqtt] Voir les logs MQTT dans le fichier correspondant');
        log::add('teleinfo', 'info', '---------------------------------------------');
    }

    /**
     *
     * @return boolean
     */
    public static function deamonRunning()
    {
            $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if ($twoCptCartelectronic == 1) {
                $result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
                if ($result != "") {
                    return true;
                }
                log::add('teleinfo', 'info', '[deamonRunning] Vérification de l\'état du service : NOK ');
                return false;
            } else {
                $result = exec("ps aux | grep teleinfo.py | grep -v grep | awk '{print $2}'");
                if ($result != "") {
                    return true;
                }
                log::add('teleinfo', 'info', '[deamonRunning] Vérification de l\'état du service : NOK ');
                return false;
            }
    }

    public static function deamonRunningMqtt($affiche = 'oui'){
        $result = exec("ps aux | grep teleinfo_mqtt.py | grep -v grep | awk '{print $2}'");
        if ($result != "") {
            return true;
        }
        if ($affiche != 'non'){
            log::add('teleinfo', 'info', '[deamonRunningMqtt] Vérification de l\'état du service MQTT : NOK ');
        }
        return false;
    }

    public static function runningMqtt(){
        $result = exec("ps aux | grep teleinfo_mqtt.py | grep -v grep | awk '{print $2}'");
        if ($result != "") {
            return true;
        }
        log::add('teleinfo', 'info', '[découverte Mqtt] Vérification de l\'état du service de découverte MQTT : NOK ');
        return false;
    }

    /**
     *
     * @return array
     */
    public static function deamon_info()
    {
        $activation_Modem = (config::byKey('activation_Modem', 'teleinfo') == "") ? 1 : config::byKey('activation_Modem', 'teleinfo');
        $activation_Mqtt = (config::byKey('activation_Mqtt', 'teleinfo') == "") ? 0 : config::byKey('activation_Mqtt', 'teleinfo');
        $consoPort = (config::byKey('port', 'teleinfo') == "") ? "" : config::byKey('port', 'teleinfo');
        $productionPort = (config::byKey('port_modem2', 'teleinfo') == "") ? "" : config::byKey('port_modem2', 'teleinfo');
        if ($productionPort != ""){
            $productionActivated = 1;
        } else {
            $productionActivated = 0;
        }
        if ($consoPort != ""){
            $consoActivated = 1;
        } else {
            $consoActivated = 0;
        }
        $return               = array();
        $return['log']        = 'teleinfo';
        $return['state']      = 'nok';
        $returnmodem = 'sans';
        $returnmqtt = 'sans';
        $returnprod = 'sans';
        if ($consoActivated == 1 && $activation_Modem==1){
            log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoserial] test pid');
            $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if ($twoCptCartelectronic == 1) {
                $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo2cpt.pid';
            } else {
                $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_conso.pid';
            }
            if (file_exists($pidFile)) {
                if (posix_getsid(trim(file_get_contents($pidFile)))) {
                    log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoserial] démon port modem 1 ou 2cpt => ok');
                    $returnmodem = 'ok';
                } else {
                    log::add('teleinfo', 'error', "[TELEINFO_deamon_infoserial] le deamon port modem 1 s'est éteint");
                    $returnmodem = 'nok';
                    shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
                }
            }else{
                log::add('teleinfo', 'error', "[TELEINFO_deamon_infoserial] le deamon port modem 1 n'est pas démarré ");
                $returnmodem = 'nok';
            }
        }
        if ($productionActivated == 1 && $activation_Modem==1){
            log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoprod] test pid');
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_prod.pid';
            if (file_exists($pidFile)) {
                if (posix_getsid(trim(file_get_contents($pidFile)))) {
                    log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoserial] démon port modem 2 => ok');
                    $returnprod = 'ok';
                } else {
                    log::add('teleinfo', 'error', "[TELEINFO_deamon_infoserial] le deamon port modem 2 s'est éteint");
                    $returnprod = 'nok';
                    shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
                }
            }else{
                log::add('teleinfo', 'error', "[TELEINFO_deamon_infoserial] le deamon port modem 2 n'est pas démarré");
                $returnprod = 'nok';
            }
            }

        if ($activation_Mqtt==1){
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_Mqtt.pid';
            log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoMqtt] test pid');
            if (file_exists($pidFile)) {
                if (posix_getsid(trim(file_get_contents($pidFile)))) {
                    log::add('teleinfo', 'debug', '[TELEINFO_deamon_infoMqtt] démon Mqtt => ok');
                    $returnmqtt = 'ok';
                } else {
                    $returnmqtt = 'nok';
                    log::add('teleinfo', 'error', "[TELEINFO_deamon_infoMqtt] le deamon MQTT s'est éteint");
                    shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
                }
            }else{
                log::add('teleinfo', 'error', "[TELEINFO_deamon_infoMqtt] le deamon MQTT n'est pas démarré");
                $returnmqtt = 'nok';
            }
        }
        $return['launchable'] = 'ok';
        if (($returnmodem != 'nok' && $returnmqtt != 'nok' && $returnprod != 'nok')&&($returnmodem != 'sans' || $returnmqtt != 'sans' || $returnprod != 'sans')){
            $return['state'] = 'ok';
            $return['deamon_modem'] = $returnmodem;
            $return['deamon_MQTT'] = $returnmqtt;
            $return['deamon_prod'] = $returnprod;
        }else{
            $return['state'] = 'nok';
            $return['deamon_modem'] = $returnmodem;
            $return['deamon_MQTT'] = $returnmqtt;
            $return['deamon_prod'] = $returnprod;
        }
        log::add('teleinfo', 'debug', '[TELEINFO_deamon_modem] état : ' . $returnmodem);
        log::add('teleinfo', 'debug', '[TELEINFO_deamon_MQTT] état : ' . $returnmqtt);
        log::add('teleinfo', 'debug', '[TELEINFO_deamon_prod] état : '. $returnprod);
        log::add('teleinfo', 'debug', '[TELEINFO_deamon] état global => retour: ' . $return['state']);
        return $return;
    }


    /**
     * appelé par jeedom pour démarrer le deamon
     */
    public static function deamon_start($debug = false)
    {
        $activation_Modem = (config::byKey('activation_Modem', 'teleinfo') == "") ? 1 : config::byKey('activation_Modem', 'teleinfo');
        $activation_Mqtt = (config::byKey('activation_Mqtt', 'teleinfo') == "") ? 0 : config::byKey('activation_Mqtt', 'teleinfo');
        $consoPort = (config::byKey('port', 'teleinfo') == "") ? "" : config::byKey('port', 'teleinfo');
        $productionPort = (config::byKey('port_modem2', 'teleinfo') == "") ? "" : config::byKey('port_modem2', 'teleinfo');
        if ($productionPort != ""){
            $productionActivated = 1;
        } else {
            $productionActivated = 0;
        }
        if ($consoPort != ""){
            $consoActivated = 1;
        } else {
            $consoActivated = 0;
        }
        if ($activation_Modem == 1) {
            log::add('teleinfo', 'info', '[deamon_start_modem] Démarrage du service');
            if (config::byKey('port', 'teleinfo') != "" || config::byKey('2cpt_cartelectronic', 'teleinfo') != "") {    // Si un port est sélectionné
                if (!self::deamonRunning()) {
                    log::add('teleinfo', 'info', 'Lancement compteur 1');
                    self::runDeamon($debug, 'conso');
                }
                message::removeAll('teleinfo', 'noTeleinfoPort');
            } else {
                log::add('teleinfo', 'info', 'Pas d\'informations sur le port1 USB (Modem série ?) ');
            }
            if ($productionActivated == 1) {    // Si un port est sélectionné
                //if (!self::deamonRunning()) {
                    log::add('teleinfo', 'info', 'Lancement compteur 2');
                    self::runDeamon($debug, 'prod');
                //}
                //message::removeAll('teleinfo', 'noTeleinfoPort');
            } else {
                log::add('teleinfo', 'info', 'Port2 non configuré ');
            }
        }
        if ($activation_Mqtt == 1){
            log::add('teleinfo', 'info', '[deamon_start_MQTT] Démarrage du service');
            if (!self::deamonRunningMqtt('non')) {
                self::runDeamonMqtt($debug, 'rien');
                message::removeAll('teleinfo', 'noTeleinfoPort');
            }
        }

        if ($activation_Modem == 0 and $activation_Mqtt == 0){
            log::add('teleinfo', 'error', '[TELEINFO_deamon] pas de modem ni de MQTT configuré => pas de démarrage du service');
        }

    }

    public static function start_Mqtt($debug = false, $socketPort, $socketHost, $modem, $mqtt, $mqtt_broker, $mqtt_port, $mqtt_topic, $mqtt_username, $mqtt_password){
        if (!self::deamonRunningMqtt()) {
            self::runDeamonMqtt($debug, 'rien', $socketPort, $socketHost, $modem, $mqtt, $mqtt_broker, $mqtt_port, $mqtt_topic, $mqtt_username, $mqtt_password);
            message::removeAll('teleinfo', 'noTeleinfoPort');
        }
    }
    /**
     * appelé par jeedom pour arrêter le deamon
     */
    public static function deamon_stop()
    {
        $activation_Modem = (config::byKey('activation_Modem', 'teleinfo') == "") ? 1 : config::byKey('activation_Modem', 'teleinfo');
        $activation_Mqtt = (config::byKey('activation_Mqtt', 'teleinfo') == "") ? 0 : config::byKey('activation_Mqtt', 'teleinfo');
        $consoPort = (config::byKey('port', 'teleinfo') == "") ? "" : config::byKey('port', 'teleinfo');
        $productionPort = (config::byKey('port_modem2', 'teleinfo') == "") ? "" : config::byKey('port_modem2', 'teleinfo');
        if ($productionPort != ""){
            $productionActivated = 1;
        } else {
            $productionActivated = 0;
        }
        if ($consoPort != ""){
            $consoActivated = 1;
        } else {
            $consoActivated = 0;
        }
        $deamonKill= false;
        $deamonInfo = self::deamon_info();
        // if ($activation_Modem==1){
        if ($deamonInfo['deamon_modem'] == 'ok' || $deamonInfo['deamon_prod'] == 'ok') {
            log::add('teleinfo', 'info', "[deamon_stop_serial] Tentative d'arrêt du service");
            $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if ($twoCptCartelectronic == 1) {
                $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo2cpt.pid';
                if (file_exists($pidFile)) {
                    $pid  = intval(trim(file_get_contents($pidFile)));
                    $kill = posix_kill($pid, 15);
                    usleep(1000);
                    if ($kill) {
                        $deamonKill= true;
                        log::add('teleinfo', 'info', "[deamon_stop_serial] arrêt du service 2cpt OK");
                    } else {
                        system::kill($pid);
                    }
                }
                //$result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
                //system::kill($result);
                system::kill('teleinfo_2_cpt.py');
            } else {
                if ($deamonInfo['deamon_prod'] == 'ok') {
                    $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_prod.pid';
                    if (file_exists($pidFile)) {
                        $pid  = intval(trim(file_get_contents($pidFile)));
                        $kill = posix_kill($pid, 15);
                        usleep(500);
                        if ($kill) {
                            $deamonKill = true;
                            log::add('teleinfo', 'info', "[deamon_stop_serial] Arrêt du service Prod OK");
                        }else{
                            system::kill($pid);
                        }
                    }
                }
                if ($deamonInfo['deamon_modem'] == 'ok') {
                    $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_conso.pid';
                    if (file_exists($pidFile)) {
                        $pid  = intval(trim(file_get_contents($pidFile)));
                        $kill = posix_kill($pid, 15);
                        usleep(500);
                        if ($kill) {
                            $deamonKill= true;
                            log::add('teleinfo', 'info', "[deamon_stop_serial] Arrêt du service Conso OK");
                        } else {
                            system::kill($pid);
                        }
                    }
                }
                system::kill('teleinfo.py');
                $port = config::byKey('port', 'teleinfo');
                if ($port != "serie") {
                    $port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
                    system::fuserk(jeedom::getUsbMapping($port));
                    sleep(1);
                }
            }
        }
        //}

        //$deamonInfoMqtt = self::deamon_infoMqtt();
        if ($deamonInfo['deamon_MQTT'] == 'ok') {
            log::add('teleinfo', 'info', "[deamon_stop_Mqtt] Tentative d'arrêt du service");
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_Mqtt.pid';
            if (file_exists($pidFile)) {
                $pid  = intval(trim(file_get_contents($pidFile)));
                $kill = posix_kill($pid, 15);
                usleep(500);
                if ($kill) {
                    $deamonKill= true;
                    log::add('teleinfo', 'info', "[deamon_stop_Mqtt] Arrêt du service Mqtt OK");
                } else {
                    system::kill($pid);
                }
                system::kill('teleinfo_mqtt.py');
            }
        }

        return $deamonKill;

    }


    public static function calculateTodayStats()
    {
        $indexConsoHP      = config::byKey('indexConsoHP', 'teleinfo', 'EASF02,EASF04,EASF06,HCHP,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC      = config::byKey('indexConsoHC', 'teleinfo', 'EASF01,EASF03,EASF05,HCHC,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        $indexProduction   = config::byKey('indexProduction', 'teleinfo', 'EAIT');
        $indexConsoTotales   = config::byKey('indexConsoTotales', 'teleinfo', 'BASE,EAST,HCHP,HCHC,BBRHPJB,BBRHPJW,BBRHPJR,BBRHCJB,BBRHCJW,BBRHCJR,EJPHPM,EJPHN');


        log::add('teleinfo', 'info', '----- Calcul des statistiques temps réel -----');
        $startDateToday            = (new DateTime())->setTimestamp(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $endDateToday              = (new DateTime())->setTimestamp(mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        log::add('teleinfo', 'info', 'Date de début : ' . $startDateToday->format('Y-m-d 00:00:00'));
        log::add('teleinfo', 'info', 'Date de fin   : ' . $endDateToday->format('Y-m-d H:i:s'));
        log::add('teleinfo', 'info', 'Liste index HP            : ' . $indexConsoHP);
        log::add('teleinfo', 'info', 'Liste index HC            : ' . $indexConsoHC);
        log::add('teleinfo', 'info', 'Liste index Production    : ' . $indexProduction);
        log::add('teleinfo', 'info', 'Liste index Conso Totale  : ' . $indexConsoTotales);


        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

            log::add('teleinfo', 'info', '----------------------------------------------');
            log::add('teleinfo', 'info', 'Objet : ' . $eqLogic->getName());

            $statTodayHp       = 0;
            $statTodayHc       = 0;
            $statTodayProd     = 0;
            $statYesterdayHp   = 0;
            $statYesterdayHc   = 0;
            $typeTendance      = 0;
            $statToday         = 0;
			$index             = '';
            $statHpToCumul     = array();
            $statHcToCumul     = array();
            $statProdToCumul   = array();
			$statTotalToCumul  = array();
            $statTotalMaxToday = 0;
            $statTotalMinToday = 0;
            $statTodayTotal = 0;
            $statYesterdayTotal = 0;
            $statHcMaxToday = 0;
            $statHcMinToday = 0;
            $statHcTotal = 0;
            $statYesterdayHc = 0;
            $statHpMaxToday = 0;
            $statHpMinToday = 0;
            $statHpTotal = 0;
            $statYesterdayHp = 0;
            $statProdMaxToday = 0;
            $statProdMinToday = 0;
            $statProdTotal = 0;
            $statYesterdayProd = 0;



			// raz des variables
            for ($i=0; $i <= 10; $i++){
				if ($i == 10) {   //affectation des variables index en dynamique
					$a = 'idIndex' . $i;
					$b = 'statTodayIndex' . $i;
					$c = 'statYesterdayIndex' . $i;
                    $d = 'Coutindex' . $i;
                    $e = 'Coutkwhindex' . $i;
                    $f = 'index' . $i;
				} 
				else {
					$a = 'idIndex0' . $i;
					$b = 'statTodayIndex0' . $i;
					$c = 'statYesterdayIndex0' . $i;
                    $d = 'Coutindex0' . $i;
                    $e = 'Coutkwhindex0' . $i;
                    $f = 'index0' . $i;
				}
                $$a = 0;
                $$b = 0;
                $$c = 0;
                $$d = 0;
                $$e = '';
                $$f = '';
            }


            $index01 = $eqLogic->getConfiguration('index01');
			$index02 = $eqLogic->getConfiguration('index02');
			$index03 = $eqLogic->getConfiguration('index03');
			$index04 = $eqLogic->getConfiguration('index04');
			$index05 = $eqLogic->getConfiguration('index05');
			$index06 = $eqLogic->getConfiguration('index06');
			$index07 = $eqLogic->getConfiguration('index07');
			$index08 = $eqLogic->getConfiguration('index08');
			$index09 = $eqLogic->getConfiguration('index09');
			$index10 = $eqLogic->getConfiguration('index10');

            $Coutkwhindex00 = $eqLogic->getConfiguration('Coutindex00');
            $Coutkwhindex01 = $eqLogic->getConfiguration('Coutindex01');
            $Coutkwhindex02 = $eqLogic->getConfiguration('Coutindex02');
            $Coutkwhindex03 = $eqLogic->getConfiguration('Coutindex03');
            $Coutkwhindex04 = $eqLogic->getConfiguration('Coutindex04');
            $Coutkwhindex05 = $eqLogic->getConfiguration('Coutindex05');
            $Coutkwhindex06 = $eqLogic->getConfiguration('Coutindex06');
            $Coutkwhindex07 = $eqLogic->getConfiguration('Coutindex07');
            $Coutkwhindex08 = $eqLogic->getConfiguration('Coutindex08');
            $Coutkwhindex09 = $eqLogic->getConfiguration('Coutindex09');
            $Coutkwhindex10 = $eqLogic->getConfiguration('Coutindex10');

            $linky = config::byKey('linky', 'teleinfo');

			if ($index01 != '') {
				log::add('teleinfo', 'info', 'Index 01     --> ' . $index01);
			}
			if ($index02 != '') {
				log::add('teleinfo', 'info', 'Index 02     --> ' . $index02);
			}
			if ($index03 != '') {
				log::add('teleinfo', 'info', 'Index 03     --> ' . $index03);
			}
			if ($index04 != '') {
				log::add('teleinfo', 'info', 'Index 04     --> ' . $index04);
			}
			if ($index05 != '') {
				log::add('teleinfo', 'info', 'Index 05     --> ' . $index05);
			}
			if ($index06 != '') {
				log::add('teleinfo', 'info', 'Index 06     --> ' . $index06);
			}
			if ($index07 != '') {
				log::add('teleinfo', 'info', 'Index 07     --> ' . $index07);
			}
			if ($index08 != '') {
				log::add('teleinfo', 'info', 'Index 08     --> ' . $index08);
			}
			if ($index09 != '') {
				log::add('teleinfo', 'info', 'Index 09     --> ' . $index09);
			}
			if ($index10 != '') {
				log::add('teleinfo', 'info', 'Index 10     --> ' . $index10);
			}

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    if (!empty($cmd->getConfiguration('info_conso'))) {
                        if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                            array_push($statHpToCumul, $cmd->getId());
                        }
                        if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                            array_push($statHcToCumul, $cmd->getId());
                        }
                        if (strpos($indexProduction, $cmd->getConfiguration('info_conso')) !== false) {
                            array_push($statProdToCumul, $cmd->getId());
                        }
						if (strpos($indexConsoTotales, $cmd->getConfiguration('info_conso')) !== false) {
							log::add('teleinfo', 'debug', 'Id Index Global --> ' . $cmd->getId());
							array_push($statTotalToCumul, $cmd->getId());
						}
                    }
                }
                if ($cmd->getConfiguration('info_conso') == "TENDANCE_DAY") {
                    $typeTendance = $cmd->getConfiguration('type_calcul_tendance');
                }
				if (($cmd->getConfiguration('info_conso') == 'BASE') || ($cmd->getConfiguration('info_conso') == 'EAST')) {
					$index00 = $cmd->getConfiguration('info_conso');
					$idIndex00 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index 00     --> ' . $index00);
					log::add('teleinfo', 'debug', 'Id Index00 ' . $idIndex00);
				}
				if ($cmd->getConfiguration('info_conso') == $index01) {
					$idIndex01 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index01 ' . $idIndex01);
				}
				if ($cmd->getConfiguration('info_conso') == $index02) {
					$idIndex02 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index02 ' . $idIndex02);
				}
				if ($cmd->getConfiguration('info_conso') == $index03) {
					$idIndex03 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index03 ' . $idIndex03);
				}
				if ($cmd->getConfiguration('info_conso') == $index04) {
					$idIndex04 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index04 ' . $idIndex04);
				}
				if ($cmd->getConfiguration('info_conso') == $index05) {
					$idIndex05 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index05 ' . $idIndex05);
				}
				if ($cmd->getConfiguration('info_conso') == $index06) {
					$idIndex06 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index06 ' . $idIndex06);
				}
				if ($cmd->getConfiguration('info_conso') == $index07) {
					$idIndex07 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index07 ' . $idIndex07);
				}
				if ($cmd->getConfiguration('info_conso') == $index08) {
					$idIndex08 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index08 ' . $idIndex08);
				}
				if ($cmd->getConfiguration('info_conso') == $index09) {
					$idIndex09 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index09 ' . $idIndex09);
				}
				if ($cmd->getConfiguration('info_conso') == $index10) {
					$idIndex10 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Index10 ' . $idIndex10);
				}
				log::add('teleinfo', 'debug', 'liste des donnees : ' . $cmd->getConfiguration('info_conso'));
            }

            $startdateyesterday = (new DateTime())->setTimestamp(mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
            if ($typeTendance === 1) {
                $enddateyesterday = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
            } else {
                $enddateyesterday = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 1, date("Y")));
            }

            $Coutindex00 = 0;
			for ($i=0; $i <= 10; $i++){
				if ($i == 10) {   //affectation des variables index en dynamique
					$a = 'idIndex' . $i;
					$b = 'statTodayIndex' . $i;
					$c = 'statYesterdayIndex' . $i;
                    $d = 'Coutindex' . $i;
                    $e = 'Coutkwhindex' . $i;
				} 
				else {
					$a = 'idIndex0' . $i;
					$b = 'statTodayIndex0' . $i;
					$c = 'statYesterdayIndex0' . $i;
                    $d = 'Coutindex0' . $i;
                    $e = 'Coutkwhindex0' . $i;
				}
				if (${$a} >= 1) {
                    log::add('teleinfo', 'debug', 'Index à trouver ' . $i . ' = ' . $a);
					log::add('teleinfo', 'debug', 'Id Index ' . $i . ' = ' . ${$a});
					$cmd = cmd::byId(${$a});
					$statMaxToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['max'];
					$statMinToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['min'];
					log::add('teleinfo', 'debug', ' ==> Valeur Index ' . $i . ' MAX : ' . intval($statMaxToday));
					log::add('teleinfo', 'debug', ' ==> Valeur Index ' . $i . ' MIN : ' . intval($statMinToday));
					$$b = intval($statMaxToday) - intval($statMinToday);
					log::add('teleinfo', 'debug', 'Total Index ' . $i . ' --> ' . ${$b});
                    $$d = $$b * $$e / 1000;
                    if ($i == 0){
                        $Coutindex00Init = $Coutindex00;
                        $statTodayIndex00init = $statTodayIndex00;
                        $Coutindex00 = 0;
                        $statTodayIndex00 = 0;
                    }else{
                        $statTodayIndex00 += ${$b};
                        $Coutindex00 += ${$d};
                        $statTodayIndex00init = 0;
                        $Coutindex00Init = 0;
                    }
                    log::add('teleinfo', 'info', 'Coût Index00 ' . $Coutindex00); 
					log::add('teleinfo', 'info', 'Coût au kWh Index ' . $i . ' --> ' .${$e}. ' coût pour cet index aujourd hui --> ' .${$d});
                }
			}
            $statTodayIndex00 += $statTodayIndex00init;
            $Coutindex00 += $Coutindex00Init;

            
            foreach ($statTotalToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande Conso totale N° ' . $value);
                $cmd            = cmd::byId($value);
                $statTotalMaxToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['max'];
                $statTotalMinToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['min'];
                log::add('teleinfo', 'debug', ' ==> Valeur conso totale MAX : ' . $statTotalMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur consototale MIN : ' . $statTotalMinToday);

                $statTodayTotal     += intval($statTotalMaxToday) - intval($statTotalMinToday);
                $statYesterdayTotal += intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['min']);
                log::add('teleinfo', 'debug', 'Total conso --> ' . $statTodayTotal);
            }
            foreach ($statHcToCumul as $key => $value) {
                $cmd            = cmd::byId($value);
                $statHcMaxToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['max'];
                $statHcMinToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['min'];
                log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
                log::add('teleinfo', 'debug', ' ==> Valeur HC MAX : ' . $statHcMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur HC MIN : ' . $statHcMinToday);

                $statTodayHc     += intval($statHcMaxToday) - intval($statHcMinToday);
                $statYesterdayHc += intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['min']);
                log::add('teleinfo', 'debug', 'Total HC --> ' . $statTodayHc);
            }
            foreach ($statHpToCumul as $key => $value) {
                $cmd            = cmd::byId($value);
                $statHpMaxToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['max'];
                $statHpMinToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['min'];
                log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
                log::add('teleinfo', 'debug', ' ==> Valeur HP MAX : ' . $statHpMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur HP MIN : ' . $statHpMinToday);

                $statTodayHp     += intval($statHpMaxToday) - intval($statHpMinToday);
                $statYesterdayHp += intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday->format('Y-m-d 00:00:00'), $enddateyesterday)['min']);
                log::add('teleinfo', 'debug', 'Total HP --> ' . $statTodayHp);
            }

            foreach ($statProdToCumul as $key => $value) {
                $cmd              = cmd::byId($value);
                $statProdMaxToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['max'];
                $statProdMinToday = $cmd->getStatistique($startDateToday->format('Y-m-d 00:00:00'), $endDateToday->format('Y-m-d H:i:s'))['min'];
                log::add('teleinfo', 'debug', 'Commande Production N°' . $value);
                log::add('teleinfo', 'debug', ' ==> Valeur MAX : ' . $statProdMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur MIN : ' . $statProdMinToday);

                $statTodayProd     += intval($statProdMaxToday) - intval($statProdMinToday);
                log::add('teleinfo', 'debug', 'Total Production --> ' . $statTodayProd);
            }

            
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "stat") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "STAT_TODAY":
                            if (intval($statTodayTotal)!=0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière STAT_TODAY 1 ==> ' . intval($statTodayTotal));
								$cmd->event(intval($statTodayTotal));
							}
							else {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière STAT_TODAY 2 ==> ' . intval($statTodayIndex00));
								$cmd->event(intval($statTodayIndex00));
							}								
                            break;
                        case "STAT_TODAY_HP":
                            log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HP) ==> ' . intval($statTodayHp));
                            $cmd->event(intval($statTodayHp));
                            break;
                        case "STAT_TODAY_HC":
                            log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HC) ==> ' . intval($statTodayHc));
                            $cmd->event(intval($statTodayHc));
                            break;
                        case "STAT_TODAY_PROD":
                            log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (PROD) ==> ' . intval($statTodayProd));
                            $cmd->event(intval($statTodayProd));
                            break;
                        case "STAT_TODAY_INDEX00":
							//if ($statTodayIndex00 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 00 ==> ' . intval($statTodayIndex00));
								$cmd->event(intval($statTodayIndex00));
							//}
							break;
                        case "STAT_TODAY_INDEX01":
							//if ($statTodayIndex01 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 01 ==> ' . intval($statTodayIndex01));
								$cmd->event(intval($statTodayIndex01));
							//}
							break;
                        case "STAT_TODAY_INDEX02":
							//if ($statTodayIndex02 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 02 ==> ' . intval($statTodayIndex02));
								$cmd->event(intval($statTodayIndex02));
							//}
							break;
                        case "STAT_TODAY_INDEX03":
							//if ($statTodayIndex03 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 03 ==> ' . intval($statTodayIndex03));
								$cmd->event(intval($statTodayIndex03));
							//}
							break;
                        case "STAT_TODAY_INDEX04":
							//if ($statTodayIndex04 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 04 ==> ' . intval($statTodayIndex04));
								$cmd->event(intval($statTodayIndex04));
							//}
							break;
                        case "STAT_TODAY_INDEX05":
							//if ($statTodayIndex05 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 05 ==> ' . intval($statTodayIndex05));
								$cmd->event(intval($statTodayIndex05));
							//}
							break;
                        case "STAT_TODAY_INDEX06":
							//if ($statTodayIndex06 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 06 ==> ' . intval($statTodayIndex06));
								$cmd->event(intval($statTodayIndex06));
							//}
							break;
                        case "STAT_TODAY_INDEX07":
							//if ($statTodayIndex07 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 07 ==> ' . intval($statTodayIndex07));
								$cmd->event(intval($statTodayIndex07));
							//}
							break;
                        case "STAT_TODAY_INDEX08":
							//if ($statTodayIndex08 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 08 ==> ' . intval($statTodayIndex08));
								$cmd->event(intval($statTodayIndex08));
							//}
							break;
                        case "STAT_TODAY_INDEX09":
							//if ($statTodayIndex09 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 09 ==> ' . intval($statTodayIndex09));
								$cmd->event(intval($statTodayIndex09));
							//}
							break;
                        case "STAT_TODAY_INDEX10":
							//if ($statTodayIndex10 > 0) {
								log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière Index 10 ==> ' . intval($statTodayIndex10));
								$cmd->event(intval($statTodayIndex10));
							//}
							break;
                            case "STAT_TODAY_INDEX00_COUT":
                                //if ($Coutindex00 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 00 ==> ' . round($Coutindex00,2));
                                    $cmd->event(round($Coutindex00,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX01_COUT":
                                //if ($Coutindex01 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 01 ==> ' . round($Coutindex01,2));
                                    $cmd->event(round($Coutindex01,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX02_COUT":
                                //if ($Coutindex02 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 02 ==> ' . round($Coutindex02,2));
                                    $cmd->event(round($Coutindex02,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX03_COUT":
                                //if ($Coutindex03 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 03 ==> ' . round($Coutindex03,2));
                                    $cmd->event(round($Coutindex03,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX04_COUT":
                                //if ($Coutindex04 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 04 ==> ' . round($Coutindex04,2));
                                    $cmd->event(round($Coutindex04,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX05_COUT":
                                //if ($Coutindex05 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 05 ==> ' . round($Coutindex05,2));
                                    $cmd->event(round($Coutindex05,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX06_COUT":
                                //if ($Coutindex06 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 06 ==> ' . round($Coutindex06,2));
                                    $cmd->event(round($Coutindex06,2,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX07_COUT":
                                //if ($Coutindex07 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 07 ==> ' . round($Coutindex07,2));
                                    $cmd->event(round($Coutindex07,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX08_COUT":
                                //if ($Coutindex08 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 08 ==> ' . round($Coutindex08,2));
                                    $cmd->event(round($Coutindex08,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX09_COUT":
                                //if ($Coutindex09 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 09 ==> ' . round($Coutindex09,2));
                                    $cmd->event(round($Coutindex09,2));
                                //}
                                break;
                            case "STAT_TODAY_INDEX10_COUT":
                                //if ($Coutindex10 > 0) {
                                    log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière coût Index 10 ==> ' . round($Coutindex10,2));
                                    $cmd->event(round($Coutindex10,2));
                                //}
                                break;
                            case "TENDANCE_DAY":
                            log::add('teleinfo', 'debug', 'Mise à jour de la tendance journalière ==> ' . '(Hier : ' . intval($statYesterdayHc + $statYesterdayHp) . ' Aujourd\'hui : ' . intval($statTodayHc + $statTodayHp) . ' Différence : ' . (intval($statYesterdayHc + $statYesterdayHp) - intval($statTodayHc + $statTodayHp)) . ')');
                            $cmd->event(intval($statYesterdayHc + $statYesterdayHp) - intval($statTodayHc + $statTodayHp));
                            break;
                    }
                }
            }
        }
        log::add('teleinfo', 'info', '----------------------------------------------');
    }


    public static function calculateOtherStats()
    {
        $indexConsoHP      = config::byKey('indexConsoHP', 'teleinfo', 'EASF02,EASF04,EASF06,HCHP,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC      = config::byKey('indexConsoHC', 'teleinfo', 'EASF01,EASF03,EASF05,HCHC,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        $indexProduction   = config::byKey('indexProduction', 'teleinfo', 'EAIT');
        $indexConsoTotales   = config::byKey('indexConsoTotales', 'teleinfo', 'BASE,EAST,HCHP,HCHC,BBRHPJB,BBRHPJW,BBRHPJR,BBRHCJB,BBRHCJW,BBRHCJR,EJPHPM,EJPHN');
        log::add('teleinfo', 'info', '----- Calcul des statistiques de la journée -----');
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            $startDay            = (new DateTime())->setTimestamp(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
            $endDay              = (new DateTime())->setTimestamp(mktime(23, 59, 59, date("m"), date("d"), date("Y")));
            $startDay->sub(new DateInterval('P1D'));
            $endDay->sub(new DateInterval('P1D'));
            $statYesterdayHc     = 0;
            $statYesterdayHp     = 0;
            $statYesterdayProd   = 0;
            $statHpToCumul       = 0;
            $statHcToCumul       = 0;
            $statYesterdayCoutProd = 0;
            $statYesterdayTotal  = 0;
            $statYesterdayHp     = 0;
            $statYesterdayHc     = 0;
            $statYesterdayProd  = 0;
            $prod                = 0;
            $statHpToCumul       = array();
            $statHcToCumul       = array();
            $statProdToCumul     = array();
            $statTotalToCumul    = array();
            $idIndexProd         = 0;


            // raz des variables
            for ($i=0; $i <= 10; $i++){
                if ($i == 10) {   //affectation des variables index en dynamique
                    $a = 'idIndex' . $i;
                    $b = 'statYesterdayTotalIndex' . $i;
                    $c = 'statYesterdayCoutTotalIndex' . $i;
                    $d = 'Coutindex' . $i;
                    $e = 'Coutkwhindex' . $i;
                    $f = 'index' . $i;
                    $g = 'idCoutIndex' . $i;
                } 
                else {
                    $a = 'idIndex0' . $i;
                    $b = 'statYesterdayTotalIndex0' . $i;
                    $c = 'statYesterdayCoutTotalIndex0' . $i;
                    $d = 'Coutindex0' . $i;
                    $e = 'Coutkwhindex0' . $i;
                    $f = 'index0' . $i;
                    $g = 'idCoutIndex0' . $i;
                }
                $$a = 0;
                $$b = 0;
                $$c = 0;
                $$d = 0;
                $$e = '';
                $$f = '';
                $$g = 0;
            }
            

            log::add('teleinfo', 'info', '--------------------------------------------------');
            log::add('teleinfo', 'info', '----- Compteur : ' . $eqLogic->getName() . ' -----');
            log::add('teleinfo', 'info', '--------------------------------------------------');
			$index01 = $eqLogic->getConfiguration('index01');
			$index02 = $eqLogic->getConfiguration('index02');
			$index03 = $eqLogic->getConfiguration('index03');
			$index04 = $eqLogic->getConfiguration('index04');
			$index05 = $eqLogic->getConfiguration('index05');
			$index06 = $eqLogic->getConfiguration('index06');
			$index07 = $eqLogic->getConfiguration('index07');
			$index08 = $eqLogic->getConfiguration('index08');
			$index09 = $eqLogic->getConfiguration('index09');
			$index10 = $eqLogic->getConfiguration('index10');
            $prod    = intval($eqLogic->getConfiguration('ActivationProduction'));
            $linky = config::byKey('linky', 'teleinfo');

            if ((floatval($eqLogic->getConfiguration('CoutindexProd')) <> 0) && ($prod == 1)) {
                $CoutIndexProd = floatval($eqLogic->getConfiguration('CoutindexProd'));
                log::add('teleinfo', 'info', 'EAIT revenus au kWh = ' . strval($CoutIndexProd));
            }else{
                $CoutIndexProd = 0;
            }

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHpToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHcToCumul, $cmd->getId());
                    }
                    if ((strpos($indexProduction, $cmd->getConfiguration('info_conso')) !== false) && ($prod <> 1)){
                        array_push($statProdToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoTotales, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statTotalToCumul, $cmd->getId());
                    }
                }
				if (($cmd->getConfiguration('info_conso') == 'EAIT') && ($prod == 1)) {
					$IndexProd = $cmd->getId();
					log::add('teleinfo', 'info', 'EAIT = ' . $IndexProd);
				}
				if (($cmd->getConfiguration('info_conso') == 'BASE') || ($cmd->getConfiguration('info_conso') == 'EAST')) {
					$index00 = $cmd->getConfiguration('info_conso');
					$idIndex00 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index 00 --> ' . $index00);
					log::add('teleinfo', 'debug', 'Id Index00 ' . $idIndex00);
				}
				if ($cmd->getConfiguration('info_conso') == $index01) {
					$idIndex01 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index01 ' . $idIndex01);
				}
				if ($cmd->getConfiguration('info_conso') == $index02) {
					$idIndex02 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index02 ' . $idIndex02);
				}
				if ($cmd->getConfiguration('info_conso') == $index03) {
					$idIndex03 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index03 ' . $idIndex03);
				}
				if ($cmd->getConfiguration('info_conso') == $index04) {
					$idIndex04 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index04 ' . $idIndex04);
				}
				if ($cmd->getConfiguration('info_conso') == $index05) {
					$idIndex05 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index05 ' . $idIndex05);
				}
				if ($cmd->getConfiguration('info_conso') == $index06) {
					$idIndex06 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index06 ' . $idIndex06);
				}
				if ($cmd->getConfiguration('info_conso') == $index07) {
					$idIndex07 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index07 ' . $idIndex07);
				}
				if ($cmd->getConfiguration('info_conso') == $index08) {
					$idIndex08 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index08 ' . $idIndex08);
				}
				if ($cmd->getConfiguration('info_conso') == $index09) {
					$idIndex09 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index09 ' . $idIndex09);
				}
				if ($cmd->getConfiguration('info_conso') == $index10) {
					$idIndex10 = $cmd->getId();
					log::add('teleinfo', 'info', 'Index10 ' . $idIndex10);
				}
				if (($cmd->getLogicalId() == 'STAT_TODAY_PROD')) {
					$idIndexProd = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id STAT_TODAY_PROD ' . $idIndexProd);
				}
				if (($cmd->getLogicalId() == 'STAT_TODAY_INDEX00_COUT')) {
					$idCoutIndex00 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index00 ' . $idCoutIndex00);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX01_COUT') {
					$idCoutIndex01 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index01 ' . $idCoutIndex01);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX02_COUT') {
					$idCoutIndex02 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index02 ' . $idCoutIndex02);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX03_COUT') {
					$idCoutIndex03 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index03 ' . $idCoutIndex03);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX04_COUT') {
					$idCoutIndex04 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index04 ' . $idCoutIndex04);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX05_COUT') {
					$idCoutIndex05 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index05 ' . $idCoutIndex05);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX06_COUT') {
					$idCoutIndex06 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index06 ' . $idCoutIndex06);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX07_COUT') {
					$idCoutIndex07 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index07 ' . $idCoutIndex07);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX08_COUT') {
					$idCoutIndex08 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index08 ' . $idCoutIndex08);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX09_COUT') {
					$idCoutIndex09 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index09 ' . $idCoutIndex09);
				}
				if ($cmd->getLogicalId() == 'STAT_TODAY_INDEX10_COUT') {
					$idCoutIndex10 = $cmd->getId();
					log::add('teleinfo', 'debug', 'Id Cout Index10 ' . $idCoutIndex10);
				}
            }

            if ($prod = 1){
                //log::add('teleinfo', 'debug', 'Index EAIT = ' . $idIndexProd);
				$cmd = cmd::byId($idIndexProd);
                $statYesterdayProd = intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']);
                log::add('teleinfo', 'debug', 'Total PROD hier --> ' . $statYesterdayProd);
                $statYesterdayCoutProd = $statYesterdayProd * $CoutIndexProd / 1000;
                log::add('teleinfo', 'debug', 'Total Revenus Prod hier --> ' . $statYesterdayCoutProd);
            }

            

			for ($i=0; $i <= 10; $i++){
				if ($i == 10) {   //affectation des variables index en dynamique
					$a = 'idIndex' . $i;
					$c = 'statYesterdayTotalIndex' . $i;
                    $d = 'idCoutIndex' . $i;
                    $e = 'statYesterdayCoutTotalIndex' . $i;
				}
				else {
					$a = 'idIndex0' . $i;
					$c = 'statYesterdayTotalIndex0' . $i;
                    $d = 'idCoutIndex0' . $i;
                    $e = 'statYesterdayCoutTotalIndex0' . $i;
				}
				if (${$a} >= 1) {
					log::add('teleinfo', 'debug', 'Index à trouver ' . $i . ' = ' . $a);
					log::add('teleinfo', 'debug', 'Id Index ' . $i . ' = ' . ${$a});
					$cmd = cmd::byId(${$a});
					$$c = intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['min']);
					log::add('teleinfo', 'debug', 'Total Index ' . $i . ' hier --> ' . ${$c});
                    $cmd = cmd::byId(${$d});
                    $$e = floatval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']);
					if ($i==0){ 
                        $statYesterdayTotalIndex00Init = $statYesterdayTotalIndex00;
                        $statYesterdayCoutTotalIndex00Init = $statYesterdayCoutTotalIndex00;
                        $statYesterdayTotalIndex00 = 0;
                        $statYesterdayCoutTotalIndex00 = 0;
                    }else{
                        $statYesterdayTotalIndex00 += ${$c};
                        $statYesterdayCoutTotalIndex00 += ${$e};
                        $statYesterdayTotalIndex00Init = 0;
                        $statYesterdayCoutTotalIndex00Init = 0;
                    }

                    log::add('teleinfo', 'debug', 'Total Cout Index ' . $i . ' hier --> ' . ${$e} . ' id numéro: ' . ${$d} . ' Index 00 ' . $statYesterdayTotalIndex00 . ' Coût Index 00 ' . $statYesterdayCoutTotalIndex00);
                }
			}
            $statYesterdayTotalIndex00 += $statYesterdayTotalIndex00Init;
            $statYesterdayCoutTotalIndex00 += $statYesterdayCoutTotalIndex00Init;


            foreach ($statTotalToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande Totale N°' . $value);
                $cmd               = cmd::byId($value);
                $statYesterdayTotal	 += intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['min']);
            }
            foreach ($statHcToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
                $cmd               = cmd::byId($value);
                $statYesterdayHc	 += intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['min']);
            }
            foreach ($statHpToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
                $cmd               = cmd::byId($value);
                $statYesterdayHp 	 += intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['min']);
            }

            if ($prod <> 1){
                foreach ($statProdToCumul as $key => $value) {
                    log::add('teleinfo', 'debug', 'Commande Prod N°' . $value);
                    $cmd                  = cmd::byId($value);
                    $statYesterdayProd 	 += intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d 00:00:00'), $endDay->format('Y-m-d 23:59:59'))['min']);
                }
            }

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "stat" || $cmd->getConfiguration('type') == "panel") {
                    //$history = new history();
                    //$history->setCmd_id($cmd->getId());
                    //$history->setDatetime($startDay->format('Y-m-d 00:00:00'));
                    //$history->setTableName('historyArch');
                    $test = $cmd->getConfiguration('info_conso');
                            switch (true) {
                        case ($test==="STAT_YESTERDAY"):
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier ==> ' . intval($statYesterdayTotal));
                            $cmd->event(intval($statYesterdayTotal), $startDay->format('Y-m-d 00:00:00'));
                            //$history->setValue(intval($statYesterdayHc) + intval($statYesterdayHp));
                            //$history->save();
                            break;
                        case ($test==="STAT_YESTERDAY_HP"):
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HP) ==> ' . intval($statYesterdayHp));
                            $cmd->event((intval($statYesterdayHp)), $startDay->format('Y-m-d 00:00:00'));
                            //$history->setValue(intval($statYesterdayHp));
                            //$history->save();
                            break;
                        case ($test==="STAT_YESTERDAY_HC"):
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HC) ==> ' . intval($statYesterdayHc));
                            $cmd->event((intval($statYesterdayHc)), $startDay->format('Y-m-d 00:00:00'));
                            //$history->setValue(intval($statYesterdayHc));
                            //$history->save();
                            break;
                        case ($test==="STAT_YESTERDAY_PROD"):
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (PROD) ==> ' . intval($statYesterdayProd));
                            $cmd->event($statYesterdayProd, $startDay->format('Y-m-d 00:00:00'));
                            //$history->setValue(intval($statYesterdayProd));
                            //$history->save();
                            break;
						case ($test==="STAT_YESTERDAY_INDEX00"):
                            //if ($statYesterdayTotalIndex00 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index00) ==> ' . intval($statYesterdayTotalIndex00));
								$cmd->event((intval($statYesterdayTotalIndex00)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX01"):
                            //if ($statYesterdayTotalIndex01 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index01) ==> ' . intval($statYesterdayTotalIndex01));
								$cmd->event((intval($statYesterdayTotalIndex01)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX02"):
                            //if ($statYesterdayTotalIndex02 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index02) ==> ' . intval($statYesterdayTotalIndex02));
								$cmd->event((intval($statYesterdayTotalIndex02)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX03"):
                            //if ($statYesterdayTotalIndex03 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index03) ==> ' . intval($statYesterdayTotalIndex03));
								$cmd->event((intval($statYesterdayTotalIndex03)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX04"):
                            //if ($statYesterdayTotalIndex04 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index04) ==> ' . intval($statYesterdayTotalIndex04));
								$cmd->event((intval($statYesterdayTotalIndex04)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX05"):
                            //if ($statYesterdayTotalIndex05 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index05) ==> ' . intval($statYesterdayTotalIndex05));
								$cmd->event((intval($statYesterdayTotalIndex05)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX06"):
                            //if ($statYesterdayTotalIndex06 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index06) ==> ' . intval($statYesterdayTotalIndex06));
								$cmd->event((intval($statYesterdayTotalIndex06)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX07"):
                            //if ($statYesterdayTotalIndex07 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index07) ==> ' . intval($statYesterdayTotalIndex07));
								$cmd->event((intval($statYesterdayTotalIndex07)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX08"):
                            //if ($statYesterdayTotalIndex08 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index08) ==> ' . intval($statYesterdayTotalIndex08));
								$cmd->event((intval($statYesterdayTotalIndex08)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX09"):
                            //if ($statYesterdayTotalIndex09 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index09) ==> ' . intval($statYesterdayTotalIndex09));
								$cmd->event((intval($statYesterdayTotalIndex09)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
						case ($test==="STAT_YESTERDAY_INDEX10"):
                            //if ($statYesterdayTotalIndex10 != 0) {
								log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (Index10) ==> ' . intval($statYesterdayTotalIndex10));
								$cmd->event((intval($statYesterdayTotalIndex10)), $startDay->format('Y-m-d 00:00:00'));
							//}
							break;
                        
                        case ($test==="STAT_YESTERDAY_PROD_COUT"):
                            //if ($statYesterdayCoutProd != 0) {
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique cout hier (PROD) ==> ' . strval($statYesterdayCoutProd));
                                $cmd->event((floatval($statYesterdayCoutProd)), $startDay->format('Y-m-d 00:00:00'));
                            //}
                            break;
                        
                        case (strpos($test,'YESTERDAY_INDEX')!=0 && strpos($test,'COUT')!=0):
                            $indexyy = (int)(substr($test,20,2));
                            if($indexyy == 0){
                                $cmd->event((floatval($statYesterdayCoutTotalIndex00)), $startDay->format('Y-m-d 00:00:00'));
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index00) ==> ' . floatval($statYesterdayCoutTotalIndex00));
                            }
                            if($indexyy == 1){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index01) ==> ' . floatval($statYesterdayCoutTotalIndex01));
								$cmd->event((floatval($statYesterdayCoutTotalIndex01)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 2){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index02) ==> ' . floatval($statYesterdayCoutTotalIndex02));
								$cmd->event((floatval($statYesterdayCoutTotalIndex02)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 3){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index03) ==> ' . floatval($statYesterdayCoutTotalIndex03));
								$cmd->event((floatval($statYesterdayCoutTotalIndex03)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 4){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index04) ==> ' . floatval($statYesterdayCoutTotalIndex04));
								$cmd->event((floatval($statYesterdayCoutTotalIndex04)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 5){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index05) ==> ' . floatval($statYesterdayCoutTotalIndex05));
								$cmd->event((floatval($statYesterdayCoutTotalIndex05)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 6){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index06) ==> ' . floatval($statYesterdayCoutTotalIndex06));
								$cmd->event((floatval($statYesterdayCoutTotalIndex06)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 7){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index07) ==> ' . floatval($statYesterdayCoutTotalIndex07));
								$cmd->event((floatval($statYesterdayCoutTotalIndex07)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 8){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index08) ==> ' . floatval($statYesterdayCoutTotalIndex08));
								$cmd->event((floatval($statYesterdayCoutTotalIndex08)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 9){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index09) ==> ' . floatval($statYesterdayCoutTotalIndex09));
								$cmd->event((floatval($statYesterdayCoutTotalIndex09)), $startDay->format('Y-m-d 00:00:00'));
                            }
                            if($indexyy == 10){
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique coût hier (Index10) ==> ' . floatval($statYesterdayCoutTotalIndex10));
								$cmd->event((floatval($statYesterdayCoutTotalIndex10)), $startDay->format('Y-m-d 00:00:00'));
                            }
                        break;
                    }
                }
            }
        }
    log::add('teleinfo', 'info', 'other stats -------------------------------------');
}
    public static function copyVersIndex($compteur, $startDate, $endDate,
                                            $indexcopy01,$indexcopy02,$indexcopy03,$indexcopy04,$indexcopy05,$indexcopy06,$indexcopy07,$indexcopy08,$indexcopy09,$indexcopy10,
                                            $coutcopy00,$coutcopy01,$coutcopy02,$coutcopy03,$coutcopy04,$coutcopy05,$coutcopy06,$coutcopy07,$coutcopy08,$coutcopy09,$coutcopy10,$coutcopyprod){
        //fonction pour copier les données issues des anciens index vers les nouveaux
        $date1 = new DateTime($startDate);
        $date2 = new DateTime($endDate);
        $diff = $date2->diff($date1)->format("%a");
        
        $p1j = new DateInterval('P1D');

        event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => 'teleinfo',
                'message' => __(' Copie des anciennes donnéés vers les nouveaux index pour la période du '.$startDate.' au '.$endDate.' soit '.$diff.' jours à traiter, cela peut prendre un peu de temps veuillez patienter ...', __FILE__),
        ));
        $indexcopy = array(' ',$indexcopy01,$indexcopy02,$indexcopy03,$indexcopy04,$indexcopy05,$indexcopy06,$indexcopy07,$indexcopy08,$indexcopy09,$indexcopy10,'EAIT');
        $coutcopy = array($coutcopy00,$coutcopy01,$coutcopy02,$coutcopy03,$coutcopy04,$coutcopy05,$coutcopy06,$coutcopy07,$coutcopy08,$coutcopy09,$coutcopy10,$coutcopyprod);
        if (($coutcopy[0] == 0)||($coutcopy[0] == '')){
            $indexcout00 = false;
        }else{
            $indexcout00 = true;
        }
        
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            if ($compteur == $eqLogic->getlogicalId()){
                $indexdestination = array($eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX00'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX01'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX02'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX03'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX04'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX05'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX06'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX07'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX08'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX09'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX10'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_PROD')
                                        );
                $coutdestination = array($eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX00_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX01_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX02_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX03_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX04_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX05_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX06_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX07_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX08_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX09_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_INDEX10_COUT'),
                                            $eqLogic->getCmd('info', 'STAT_YESTERDAY_PROD_COUT')
                                        );
                $iddestination = array();
                $idcoutdest = array();
                for ($i=0;$i<12;$i++){
                    $iddestination[$i] = $indexdestination[$i]->getId();
                    $idcoutdest[$i] = $coutdestination[$i]->getId();
                }                        
                //$linky = config::byKey('linky', 'teleinfo');
                $statIndex00 = 0;

                $indexoriginebase=$eqLogic->getCmd('info', 'BASE');
                $indexorigineeast=$eqLogic->getCmd('info', 'EAST');
                
                // mise dans index origine pour recalcul de tous sauf 00 et prod
                for ($i=1;$i<12;$i++){
                    $indexorigine[$i] = $eqLogic->getCmd('info', $indexcopy[$i]);
                }

                
                for($i=1; $i < ($diff+1); $i++){

                    if ($diff > 9){
                        if (($i % ($diff/10)) == 0){
                                event::add('jeedom::alert', array(
                                        'level' => 'warning',
                                        'page' => 'teleinfo',
                                        'message' => __('Les statistiques sont en cours de création, cela peut prendre un peu de temps veuillez patienter ... ('. intval($i/($diff/100)) .' %)', __FILE__),
                                ));
                        }
                    }
                    
                    $statTotal1 = 0;
                    $coutotal1 = 0;
                    $statTotal2 = 0;
                    $coutotal2 = 0;
                    $aboBase = false;
                    $aboBaseCouts = false;

                    
                    //recalcul des index + coûts base
                    if ($indexoriginebase<>''){
                        $statTotal1 = intval($indexoriginebase->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['max'])
                                            - intval($indexoriginebase->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['min']);
                        if ($statTotal1 <> 0 && $indexcout00){ 
                            $coutotal1 = floatval($statTotal1) * floatval($coutcopy[0]) / 1000;
                        }
                    }
                    //recalcul des index + coûts EAST
                    if ($indexorigineeast<>''){
                        $statTotal2 = intval($indexorigineeast->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['max'])
                                            - intval($indexorigineeast->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['min']);
                        if ($statTotal2 <> 0 && $indexcout00){ 
                            $coutotal2 = floatval($statTotal2) * floatval($coutcopy[0]) / 1000;
                        }
                    }
                    //remise en place des index et coûts base + EAST
                    if (($statTotal1 + $statTotal2) <> 0){
                        $aboBase = true;
                        $history = new history();
                        $history->setCmd_id($iddestination[0]);
                        $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                        $history->setTableName('historyArch');
                        $history->setValue(intval($statTotal1 + $statTotal2));
                        $history->save();
                        if (($coutotal1 + $coutotal2) <> 0){ 
                            //$coutotal2 += $coutotal;
                            $aboBaseCouts = true;
                            $historycout = new history();
                            $historycout->setCmd_id($idcoutdest[0]);
                            $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $historycout->setTableName('historyArch');
                            $historycout->setValue(($coutotal1 + $coutotal2));
                            $historycout->save();
                        }else{
                            $historycout = new history();
                            $historycout->setCmd_id($idcoutdest[0]);
                            $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $historycout->setTableName('historyArch');
                            $historycout->setValue(' ');
                            $historycout->save();
                        }
                    }else{
                        $history = new history();
                        $history->setCmd_id($iddestination[0]);
                        $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                        $history->setTableName('historyArch');
                        $history->setValue(intval(' '));
                        $history->save();
                        $historycout = new history();
                        $historycout->setCmd_id($idcoutdest[0]);
                        $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                        $historycout->setTableName('historyArch');
                        $historycout->setValue(' ');
                        $historycout->save();
                    }


                    //recalcul des couts prod
                    $statotal2 = 0;
                    $coutotal2 = 0;
                    if ($indexorigine[11]<>''){
                        $statTotal2 = intval($indexorigine[11]->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['max'])
                                                - intval($indexorigine[11]->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['min']);
                        if ($statTotal2 <> 0){ 
                            $coutotal2 = floatval($statTotal2) * floatval($coutcopy[11]) / 1000;
                            $history = new history();
                            $history->setCmd_id($iddestination[11]);
                            $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $history->setTableName('historyArch');
                            $history->setValue(intval($statTotal2));
                            $history->save();
                            if ($coutotal2 <> 0){ 
                                //$coutotal2 += $coutotal;
                                
                                $historycout = new history();
                                $historycout->setCmd_id($idcoutdest[11]);
                                $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                $historycout->setTableName('historyArch');
                                $historycout->setValue($coutotal2);
                                $historycout->save();
                            }else{
                                $historycout = new history();
                                $historycout->setCmd_id($idcoutdest[11]);
                                $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                $historycout->setTableName('historyArch');
                                $historycout->setValue(' ');
                                $historycout->save();
                            }
                        }else{
                            $history = new history();
                            $history->setCmd_id($iddestination[11]);
                            $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $history->setTableName('historyArch');
                            $history->setValue(' ');
                            $history->save();
                            $historycout = new history();
                            $historycout->setCmd_id($idcoutdest[11]);
                            $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $historycout->setTableName('historyArch');
                            $historycout->setValue(' ');
                            $historycout->save();
                        }
                    }



                    
                    $statTotal1 = 0;
                    $coutotal1 = 0;
                    $statTotal2 = 0;
                    $coutotal2 = 0;
                    $coutotal = 0;

                    //recalcul des index de 1 à 10
                    for ($j=1;$j<11;$j++){
                        if ($indexcopy[$j]<>''){
                            if(floatval($coutcopy[$j])<>0){
                                $statTotal1 = intval($indexorigine[$j]->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['max'])
                                            - intval($indexorigine[$j]->getStatistique($date2->format('Y-m-d 00:00:00'), $date2->format('Y-m-d 23:59:59'))['min']);
                                

                                if ($statTotal1 <> 0){ 
                                    $statTotal2 += $statTotal1;
                                    $coutotal = floatval($statTotal1) * floatval($coutcopy[$j]) / 1000;
                                    $history = new history();
                                    $history->setCmd_id($iddestination[$j]);
                                    $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                                    $history->setTableName('historyArch');
                                    $history->setValue(intval($statTotal1));
                                    $history->save();
                                    if ($coutotal <> 0){ 
                                        $coutotal2 += $coutotal;
                                        $historycout = new history();
                                        $historycout->setCmd_id($idcoutdest[$j]);
                                        $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                        $historycout->setTableName('historyArch');
                                        $historycout->setValue(($coutotal));
                                        $historycout->save();
                                    }else{
                                        $historycout = new history();
                                        $historycout->setCmd_id($idcoutdest[$j]);
                                        $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                        $historycout->setTableName('historyArch');
                                        $historycout->setValue(' ');
                                        $historycout->save();
                                    }
                                }else{
                                    $history = new history();
                                    $history->setCmd_id($iddestination[$j]);
                                    $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                                    $history->setTableName('historyArch');
                                    $history->setValue(' ');
                                    $history->save();
                                    $historycout = new history();
                                    $historycout->setCmd_id($idcoutdest[$j]);
                                    $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                    $historycout->setTableName('historyArch');
                                    $historycout->setValue(' ');
                                    $historycout->save();   
                                    }
                            }else{
                                $history = new history();
                                $history->setCmd_id($iddestination[$j]);
                                $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                                $history->setTableName('historyArch');
                                $history->setValue(' ');
                                $history->save();
                                $historycout = new history();
                                $historycout->setCmd_id($idcoutdest[$j]);
                                $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                                $historycout->setTableName('historyArch');
                                $historycout->setValue(' ');
                                $historycout->save();   
                            }
                        }
                    }
                    // s'il n'y a pas d'index de base (BASE ou EAST) sur la période on prend la somme des index
                    if (!$aboBase){
                        if ($statTotal2 <> 0){
                            $history = new history();
                            $history->setCmd_id($iddestination[0]);
                            $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $history->setTableName('historyArch');
                            $history->setValue($statTotal2);
                            $history->save();
                        }else{
                            $history = new history();
                            $history->setCmd_id($iddestination[0]);
                            $history->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $history->setTableName('historyArch');
                            $history->setValue(' ');
                            $history->save();
                        }
                    }
                
                    // s'il n'y a pas de tarif au kwh alors on prend la somme des index
                    if (!$aboBaseCouts){
                        if ($coutotal2 <> 0){
                            $historycout = new history();
                            $historycout->setCmd_id($idcoutdest[0]);
                            $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $historycout->setTableName('historyArch');
                            $historycout->setValue($coutotal2);
                            $historycout->save();
                        }else{
                            $historycout = new history();
                            $historycout->setCmd_id($idcoutdest[0]);
                            $historycout->setDatetime($date2->format('Y-m-d 00:00:00'));
                            $historycout->setTableName('historyArch');
                            $historycout->setValue(' ');
                            $historycout->save();
                        }
                    }
                    $date2->sub($p1j);
                }
                foreach ($iddestination as $key => $destination){
                    try{
                        $sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdId) AND (value=' ' OR value = 0)";
                        $values = array(
                            'cmdId' => $destination,
                        );
                        $sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdId) AND (value=' ' OR value = 0)";
                        DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
                    } catch (\Exception $e) {
                        log::add('teleinfo', 'error', '[TELEINFO]-----' . $e) ;
                    }
                }
                foreach ($idcoutdest as $destination){
                    try{
                        $sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdId) AND (value=' ' OR value = 0)";
                        $values = array(
                            'cmdId' => $destination,
                        );
                        $sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdId) AND (value=' ' OR value = 0)";
                        DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
                    } catch (\Exception $e) {
                        log::add('teleinfo', 'error', '[TELEINFO]-----' . $e) ;
                    }
                }
            }    
        }
    

    
    
        event::add('jeedom::alert', array(
            'level' => 'success',
            'page' => 'teleinfo',
            'message' => __('Les index ont bien été constitués.', __FILE__),
        ));

    } 


    public static function regenerateMonthlyStat(){
        cache::set('teleinfo::regenerateMonthlyStat', '1', 86400);
        $indexConsoHP      = config::byKey('indexConsoHP', 'teleinfo', 'EASF02,EASF04,EASF06,HCHP,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC      = config::byKey('indexConsoHC', 'teleinfo', 'EASF01,EASF03,EASF05,HCHC,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        $indexProduction   = config::byKey('indexProduction', 'teleinfo', 'EAIT');
        $indexConsoTotales   = config::byKey('indexConsoTotales', 'teleinfo', 'BASE,EAST,HCHP,HCHC,BBRHPJB,BBRHPJW,BBRHPJR,BBRHCJB,BBRHCJW,BBRHCJR,EJPHPM,EJPHN');
        event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'teleinfo',
				'message' => __('Les statistiques sont en cours de regénérations, cela peut prendre un peu de temps veuillez patienter ...', __FILE__),
		));
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            $startDay = (new DateTime())->setTimestamp(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
            $endDay   = (new DateTime())->setTimestamp(mktime(23, 59, 59, date("m"), date("d"), date("Y")));
            $statHpToCumul       = array();
            $statHcToCumul       = array();
            $statProdToCumul     = array();
            $statTotalToCumul    = array();

            try{
                $cmdYesterdayHP     = $eqLogic->getCmd('info', 'STAT_YESTERDAY_HP');
                $cmdYesterdayHC     = $eqLogic->getCmd('info', 'STAT_YESTERDAY_HC');
                $cmdYesterdayProd   = $eqLogic->getCmd('info', 'STAT_YESTERDAY_PROD');
                $cmdYesterdayTotal     = $eqLogic->getCmd('info', 'STAT_YESTERDAY');
                $sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdIdHP OR cmd_id=:cmdIdHC OR cmd_id=:cmdIdPROD OR cmd_id=:cmdIdTotal) AND MINUTE(datetime) <> '0'";
                $values = array(
                    'cmdIdHP' => $cmdYesterdayHP->getId(),
                    'cmdIdHC' => $cmdYesterdayHC->getId(),
                    'cmdIdPROD' => $cmdYesterdayProd->getId(),
					'cmdIdTotal' => $cmdYesterdayTotal->getId(),
                );
				$sql = "DELETE FROM historyArch WHERE (cmd_id=:cmdIdHP OR cmd_id=:cmdIdHC OR cmd_id=:cmdIdPROD OR cmd_id=:cmdIdTotal) AND SECOND(datetime) <> '0'";
				DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
            } catch (\Exception $e) {
                log::add('teleinfo', 'error', '[TELEINFO]-----' . $e) ;
            }

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHpToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHcToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoTotales, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statTotalToCumul, $cmd->getId());
                    }
                    if (strpos($indexProduction, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statProdToCumul, $cmd->getId());
                    }
                }
            }

            for($i=1; $i < 730; $i++){
                $statHc     = 0;
                $statHp     = 0;
                $statProd   = 0;
                $startDay->sub(new DateInterval('P1D'));
                $endDay->sub(new DateInterval('P1D'));

                if (($i % 40) == 0){
                    event::add('jeedom::alert', array(
                            'level' => 'warning',
                            'page' => 'teleinfo',
                            'message' => __('Les statistiques sont en cours de regénérations, cela peut prendre un peu de temps veuillez patienter ... ('. intval($i/7.3) .' %)', __FILE__),
                    ));
                }


                foreach ($statTotalToCumul as $key => $value) {
                    $cmd    = cmd::byId($value);
                    $statTotal += intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['min']);
                }
                foreach ($statHcToCumul as $key => $value) {
                    $cmd    = cmd::byId($value);
                    $statHc += intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['min']);
                }
                foreach ($statHpToCumul as $key => $value) {
                    $cmd    = cmd::byId($value);
                    $statHp += intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['min']);
                }

                foreach ($statProdToCumul as $key => $value) {
                    $cmd        = cmd::byId($value);
                    $statProd 	+= intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['max']) - intval($cmd->getStatistique($startDay->format('Y-m-d H:i:s'), $endDay->format('Y-m-d H:i:s'))['min']);
                }

                foreach ($eqLogic->getCmd('info') as $cmd) {
                    if ($cmd->getConfiguration('type') == "stat" || $cmd->getConfiguration('type') == "panel") {
                        $history = new history();
                        $history->setCmd_id($cmd->getId());
                        $history->setDatetime($startDay->format('Y-m-d 00:00:00'));
                        $history->setTableName('historyArch');
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "STAT_YESTERDAY_HP":
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique HP   ==> ' . $startDay->format('Y-m-d') . " / Valeur : " . intval($statHp)) ;
                                $history->setValue(intval($statHp));
                                $history->save();
                                break;
                            case "STAT_YESTERDAY_HC":
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique HC   ==> ' . $startDay->format('Y-m-d') . " / Valeur : " . intval($statHc)) ;
                                $history->setValue(intval($statHc));
                                $history->save();
                                break;
                            case "STAT_YESTERDAY_PROD":
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique PROD ==> ' . $startDay->format('Y-m-d') . " / Valeur : " . intval($statProd)) ;
                                $history->setValue(intval($statProd));
                                $history->save();
                                break;
                            case "STAT_YESTERDAY":
                                log::add('teleinfo', 'debug', 'Mise à jour de la statistique HIER ==> ' . $startDay->format('Y-m-d') . " / Valeur : " . intval($statTotal)) ;
                                $history->setValue(intval($statTotal));
                                $history->save();
                                break;
                        }
                    }
                }

            }
        }
        event::add('jeedom::alert', array(
				'level' => 'success',
				'page' => 'teleinfo',
				'message' => __('Les statistiques ont étés regénérés.', __FILE__),
		));
    }

    public static function moyLastHour()
    {
        $ppapHp  = 0;
        $ppapHc  = 0;
        $cmdPpap = null;
        $indexConsoHP = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        log::add('teleinfo', 'debug', 'moylasthour ');

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == 'stat') {
                    if ($cmd->getConfiguration('info_conso') == 'STAT_MOY_LAST_HOUR') {
                        log::add('teleinfo', 'debug', '----- Calcul de la consommation moyenne sur la dernière heure -----');
                        $cmdPpap = $cmd;
                    }
                }
            }
            if ($cmdPpap !== null) {
                foreach ($eqLogic->getCmd('info') as $cmd) {
                    if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                        if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                            $ppapHp += $cmd->execCmd();
                            log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
                        }
                        if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                            $ppapHc += $cmd->execCmd();
                            log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
                        }
                    }
                }

                $cacheHc = cache::byKey('teleinfo::stat_moy_last_hour::hc', false);
                $cacheHp = cache::byKey('teleinfo::stat_moy_last_hour::hp', false);
                $cacheHc = $cacheHc->getValue();
                $cacheHp = $cacheHp->getValue();

                log::add('teleinfo', 'debug', 'Cache HP : ' . $cacheHp);
                log::add('teleinfo', 'debug', 'Cache HC : ' . $cacheHc);

                log::add('teleinfo', 'debug', 'Conso Wh : ' . (($ppapHp - $cacheHp) + ($ppapHc - $cacheHc)));
                $cmdPpap->event(intval((($ppapHp - $cacheHp) + ($ppapHc - $cacheHc))));

                cache::set('teleinfo::stat_moy_last_hour::hc', $ppapHc, 7200);
                cache::set('teleinfo::stat_moy_last_hour::hp', $ppapHp, 7200);
            }
			else {
                log::add('teleinfo', 'debug', 'Pas de calcul');
            }
        }
    }

    public static function calculatePAPP()
    {
        log::add('teleinfo', 'debug', 'calculatepapp ');
        $indexConsoHP = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
			$ppapHp  = 0;
			$ppapHc  = 0;
			$cmdPpap = null;
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == 'stat') {
                    if ($cmd->getConfiguration('info_conso') == 'PPAP_MANUELLE') {
                        log::add('teleinfo', 'debug', '----- Calcul de la puissance apparente moyenne -----');
                        $cmdPpap = $cmd;
                    }
                }
            }
            if ($cmdPpap !== null) {
                log::add('teleinfo', 'debug', 'Cmd trouvée');
                foreach ($eqLogic->getCmd('info') as $cmd) {
                    if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                        if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
							log::add('teleinfo', 'debug', 'HP : ' . $cmd->getId());
                            $ppapHp += $cmd->execCmd();
                        }
                        if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
							log::add('teleinfo', 'debug', 'HC : ' . $cmd->getId());
                            $ppapHc += $cmd->execCmd();
                        }
                    }
                }
                $cacheHc        = cache::byKey('teleinfo::ppap_manuelle::' . $eqLogic->getId() . '::hc', false);
                $datetimeMesure = date_create($cacheHc->getDatetime());
                $cacheHp        = cache::byKey('teleinfo::ppap_manuelle::' . $eqLogic->getId() . '::hp', false);
                $cacheHc        = $cacheHc->getValue();
                $cacheHp        = $cacheHp->getValue();
                $datetimeMesure = $datetimeMesure->getTimestamp();
                $datetime2      = time();
                $interval       = $datetime2 - $datetimeMesure;
                $consoResultat  = ((($ppapHp - $cacheHp) + ($ppapHc - $cacheHc)) / $interval) * 3600;
                log::add('teleinfo', 'debug', 'Intervale depuis la dernière valeur : ' . $interval);
                log::add('teleinfo', 'debug', 'Conso calculée : ' . $consoResultat . ' Wh');
                $cmdPpap->event(intval($consoResultat));
                cache::set('teleinfo::ppap_manuelle::' . $eqLogic->getId() . '::hc', $ppapHc, 150);
                cache::set('teleinfo::ppap_manuelle::' . $eqLogic->getId() . '::hp', $ppapHp, 150);
            } else {
                log::add('teleinfo', 'debug', 'Pas de calcul');
            }
        }
    }

    public function preSave()
    {
        log::add('teleinfo', 'debug', '-------- PRESAVE --------');
        $this->setCategory('energy', 1);
        $cmd = $this->getCmd('info', 'HEALTH');
        if (is_object($cmd)) {
            $cmd->remove();
        }

        $array = array("STAT_JAN_HP", "STAT_JAN_HC", "STAT_FEV_HP", "STAT_FEV_HC", "STAT_MAR_HP", "STAT_MAR_HC", "STAT_AVR_HP", "STAT_AVR_HC", "STAT_MAI_HP", "STAT_MAI_HC", "STAT_JUIN_HP", "STAT_JUIN_HC", "STAT_JUI_HP", "STAT_JUI_HC", "STAT_AOU_HP", "STAT_AOU_HC", "STAT_SEP_HP", "STAT_SEP_HC");
        foreach ($array as $value){
            log::add('teleinfo', 'debug', 'Recherche de => ' . $value);
            $cmd = $this->getCmd('info', $value);
            if (is_object($cmd)) {
                log::add('teleinfo', 'debug', 'Suppression de => ' . $value);
                cache::set('teleinfo::needRegenerateMonthlyStat', '1');
                $cmd->remove();
                //$cmd->save();
            }
        }

        $array = array("STAT_OCT_HP", "STAT_OCT_HC", "STAT_NOV_HP", "STAT_NOV_HC", "STAT_DEC_HP", "STAT_DEC_HC", "STAT_MONTH_LAST_YEAR", "STAT_YEAR_LAST_YEAR","STAT_MONTH","STAT_MONTH_PROD", "STAT_YEAR", "STAT_YEAR_PROD", "STAT_LASTMONTH");
        foreach ($array as $value){
            log::add('teleinfo', 'debug', 'Recherche de => ' . $value);
            $cmd = $this->getCmd('info', $value);
            if (is_object($cmd)) {
                log::add('teleinfo', 'debug', 'Suppression de => ' . $value);
                cache::set('teleinfo::needRegenerateMonthlyStat', '1');
                $cmd->remove();
                //$cmd->save();
            }
        }
    }

    public function postSave()
    {
        log::add('teleinfo', 'info', '-------- Sauvegarde de l\'objet --------');
        foreach ($this->getCmd(null, null, true) as $cmd) {
            switch ($cmd->getConfiguration('info_conso')) {
                case "BASE":
                case "HCHP":
                case "HCHC":
                case "EJPHN":
                case "BBRHPJB":
                case "BBRHPJW":
                case "BBRHPJR":
                case "BBRHCJB":
                case "BBRHCJW":
                case "BBRHCJR":
                case "EJPHPM":
                case "EAIT":
                case "EAST":
                case "EASF01":
                case "EASF02":
                case "EASF03":
                case "EASF04":
                case "EASF05":
                case "EASF06":
                case "EASF07":
                case "EASF08":
                case "EASF09":
                case "EASF10":
                case "EASD01":
                case "EASD02":
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> index');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
					$cmd->setConfiguration('historizeMode', 'none');
                    $cmd->save();
                    $cmd->refresh();
                    break;
                case "PAPP":
                case "SINSTS":
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> papp ou sinsts');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                        //$cmd->setDisplay('icon', '<i class=\"fa fa-tachometer\"><\/i>');
                    }
					$cmd->setConfiguration('historizeMode', 'avg');
                    $cmd->save();
                    $cmd->refresh();
                    break;
                case "PTEC":
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> ptec');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
                    $cmd->save();
                    $cmd->refresh();
                    break;
                default :
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> default');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
                    break;
            }
        }
        log::add('teleinfo', 'info', '==> Gestion des id des commandes');
        foreach ($this->getCmd('info') as $cmd) {
            log::add('teleinfo', 'debug', 'Commande : ' . $cmd->getConfiguration('info_conso'));
            $cmd->setLogicalId($cmd->getConfiguration('info_conso'));
            $cmd->save();
        }
        log::add('teleinfo', 'debug', '-------- Fin de la sauvegarde --------');

        if ($this->getConfiguration('AutoGenerateFields') == '1') {
            $this->CreateFromAbo($this->getConfiguration('abonnement'));
        }

        $this->createOtherCmd();

        $this->createPanelStats();

        if (cache::byKey('teleinfo::needRegenerateMonthlyStat', '0')->getValue() == '1'){
            cache::set('teleinfo::needRegenerateMonthlyStat', '0');
            $this->regenerateMonthlyStat();
        }

        

    }

    public function preRemove()
    {
        log::add('teleinfo', 'debug', 'Suppression d\'un objet');
    }

    public function createOtherCmd()
    {
        log::add('teleinfo', 'debug', '-------- Santé --------');
        $array = array("HEALTH");
        foreach ($array as $value){
            $cmd = $this->getCmd('info', $value);
            if (!is_object($cmd)) {
                log::add('teleinfo', 'debug', 'Santé => ' . $value);
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                //$cmd->setEqLogic_id($value);
                $cmd->setEqLogic_id($this->id);
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'health');
                $cmd->setSubType('string');
                $cmd->setIsHistorized(0);
                //$cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
            }
        }
    }

    public function createPanelStats()
    {
        log::add('teleinfo', 'debug', '-------- Commandes des stats ---------');
        $array = array("STAT_TODAY","STAT_TODAY_HC", "STAT_TODAY_HP", "STAT_TODAY_PROD",
                        "STAT_YESTERDAY","STAT_YESTERDAY_HC","STAT_YESTERDAY_HP","STAT_YESTERDAY_PROD","STAT_YESTERDAY_PROD_COUT",
                        "STAT_TODAY_INDEX00","STAT_TODAY_INDEX00_COUT","STAT_YESTERDAY_INDEX00","STAT_YESTERDAY_INDEX00_COUT",
                        "STAT_TODAY_INDEX01","STAT_TODAY_INDEX01_COUT","STAT_YESTERDAY_INDEX01","STAT_YESTERDAY_INDEX01_COUT",
                        "STAT_TODAY_INDEX02","STAT_TODAY_INDEX02_COUT","STAT_YESTERDAY_INDEX02","STAT_YESTERDAY_INDEX02_COUT",
                        "STAT_TODAY_INDEX03","STAT_TODAY_INDEX03_COUT","STAT_YESTERDAY_INDEX03","STAT_YESTERDAY_INDEX03_COUT",
                        "STAT_TODAY_INDEX04","STAT_TODAY_INDEX04_COUT","STAT_YESTERDAY_INDEX04","STAT_YESTERDAY_INDEX04_COUT",
                        "STAT_TODAY_INDEX05","STAT_TODAY_INDEX05_COUT","STAT_YESTERDAY_INDEX05","STAT_YESTERDAY_INDEX05_COUT",
                        "STAT_TODAY_INDEX06","STAT_TODAY_INDEX06_COUT","STAT_YESTERDAY_INDEX06","STAT_YESTERDAY_INDEX06_COUT",
                        "STAT_TODAY_INDEX07","STAT_TODAY_INDEX07_COUT","STAT_YESTERDAY_INDEX07","STAT_YESTERDAY_INDEX07_COUT",
                        "STAT_TODAY_INDEX08","STAT_TODAY_INDEX08_COUT","STAT_YESTERDAY_INDEX08","STAT_YESTERDAY_INDEX08_COUT",
                        "STAT_TODAY_INDEX09","STAT_TODAY_INDEX09_COUT","STAT_YESTERDAY_INDEX09","STAT_YESTERDAY_INDEX09_COUT",
                        "STAT_TODAY_INDEX10","STAT_TODAY_INDEX10_COUT","STAT_YESTERDAY_INDEX10","STAT_YESTERDAY_INDEX10_COUT");
        foreach ($array as $value){
            $cmd = $this->getCmd('info', $value);
            if (!is_object($cmd)) {
                log::add('teleinfo', 'debug', 'Nouvelle => ' . $value);
                if (strpos($value,'COUT')<>0) {
                    $unite = ('€');
                }else{
                    $unite = ('Wh');
                }
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                $cmd->setEqLogic_id($this->id);
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setUnite($unite);
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'stat');
				$cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setIsHistorized(1);
                //$cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
                $cmd->refresh();
            } else {
                log::add('teleinfo', 'debug', 'Ancienne => ' . $value);
                $cmd->setIsHistorized(1);
                $cmd->setConfiguration('type', 'stat');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->save();
                $cmd->refresh();
            }

        }
    }

    public function CreateFromAbo($_abo)
    {
        $this->setConfiguration('AutoGenerateFields', '0');
        $this->save();
    }

    /*     * ******** MANAGEMENT ZONE ******* */

    public static function dependancy_info()
    {
        $return                  = array();
        $return['log']           = 'teleinfo_update';
        $return['progress_file'] = '/tmp/jeedom/teleinfo/dependance';
        $return['state']         = (self::installationOk()) ? 'ok' : 'nok';
        return $return;
    }

    public static function installationOk()
    {
        try {
            $dependances_version = config::byKey('dependancy_version', 'teleinfo', 0);
            if (intval($dependances_version) >= 1.0) {
                return true;
            } else {
                config::save('dependancy_version', 1.0, 'teleinfo');
                return false;
            }
        } catch (\Exception $e) {
            return true;
        }
    }

    public static function dependancy_install()
    {
        log::remove(__CLASS__ . '_update');
        return array('script' => __DIR__ . '/../../ressources/install_#stype#.sh ' . jeedom::getTmpFolder('teleinfo') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

}

class teleinfoCmd extends cmd
{

    public function execute($_options = null)
    {

    }

}
