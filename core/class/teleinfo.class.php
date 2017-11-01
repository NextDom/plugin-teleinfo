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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class teleinfo extends eqLogic {
    /*     * *************************Attributs****************************** */
    /*     * ***********************Methode static*************************** */
	public static function getTeleinfoInfo($_url){
		return 1;
	}
	
	public static function cron() {
		if (config::byKey('jeeNetwork::mode') == 'slave') { //Je suis l'esclave
			if (!self::deamonRunning()) {
                self::runExternalDeamon();
            }
		}
		else{	// Je suis le jeedom master			
			self::Calculate_PAPP();
		}
    }
	
	public static function cronHourly() {
		if (config::byKey('jeeNetwork::mode') == 'master') {
			self::Moy_Last_Hour();
		}
	}
	
	
	public static function createFromDef($_def) {
		if (!isset($_def['ADCO'])) {
			log::add('teleinfo', 'info', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
			return false;
		}
		$teleinfo = teleinfo::byLogicalId($_def['ADCO'], 'teleinfo');
		if (!is_object($teleinfo)) {
			$eqLogic = new teleinfo();
			$eqLogic->setName($_def['ADCO']);
		}
		$eqLogic->setLogicalId($_def['ADCO']);
		$eqLogic->setEqType_name('teleinfo');
		$eqLogic->setIsEnable(1);
		$eqLogic->setIsVisible(1);
		$eqLogic->save();
		//$eqLogic->applyModuleConfiguration();
		return $eqLogic;
	}
	
	public static function createCmdFromDef($_oADCO, $_oKey, $_oValue) {
		if (!isset($_oKey)) {
			log::add('teleinfo', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_oKey, true));
			return false;
		}
		if (!isset($_oADCO)) {
			log::add('teleinfo', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_oADCO, true));
			return false;
		}
		$teleinfo = teleinfo::byLogicalId($_oADCO, 'teleinfo');
		if (!is_object($teleinfo)) {
			//$eqLogic = new teleinfo();
			//$eqLogic->setName($_def['ADCO']);
		}
		if($teleinfo->getConfiguration('AutoCreateFromCompteur') == '1'){
			log::add('teleinfo', 'info', 'Création de la commande ' . $_oKey . ' sur l\'ADCO ' . $_oADCO);
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName($_oKey);
			//$cmd->setEqLogic_id($_oADCO);
			$cmd->setEqLogic_id($teleinfo->id);
			log::add('teleinfo', 'debug', 'EqLogicID');
			$cmd->setLogicalId($_oKey);
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', $_oKey);
			switch ($_oKey) {
							case "PAPP":
								$cmd->setDisplay('generic_type','GENERIC_INFO');
								$cmd->setDisplay('icon','<i class=\"fa fa-tachometer\"><\/i>');
								$cmd->setSubType('string');
								break;
							case "OPTARIF":
							case "HHPHC":
							case "PPOT":
							case "PEJP":
							case "DEMAIN":
								$cmd->setSubType('string');
								$cmd->setDisplay('generic_type','GENERIC_INFO');
								break;
							default:
								$cmd->setSubType('numeric');
								$cmd->setDisplay('generic_type','GENERIC_INFO');
							break;	
						}		
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd->setValue($_oValue);
			$cmd->event($_oValue);
			return $cmd;
		}
	}

	public static function runExternalDeamon($_debug = false) {
        log::add('teleinfo', 'info', 'Démarrage du service en mode satellite');
        $teleinfo_path = realpath(dirname(__FILE__) . '/../../ressources');
		$modem_serie_addr = config::byKey('port', 'teleinfo');
		$_debug = config::byKey('debug', 'teleinfo');
		$_force = config::byKey('force', 'teleinfo');
		$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');

		if(config::byKey('modem_vitesse', 'teleinfo') == ""){
			$modem_vitesse = '1200';
		}else{
			$modem_vitesse = config::byKey('modem_vitesse', 'teleinfo');
		}
		
		if($modem_serie_addr == "serie"){
			$port = config::byKey('modem_serie_addr', 'teleinfo');
			goto lancement;
		}
		$port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
		if($_2cpt_cartelectronic == 1){
				$port = '/dev/ttyUSB1';
				goto lancement;
		}
        if (!file_exists($port)) {
				log::add('teleinfo', 'error', 'Le port n\'existe pas');
				goto end;
        }
		lancement:
		exec('sudo chmod 777 ' . $port . ' > /dev/null 2>&1');
		$parsed_url = parse_url(config::byKey('jeeNetwork::master::ip'));
		
		log::add('teleinfo', 'info', '--------- Informations sur le master --------');
		log::add('teleinfo', 'info', 'Adresse             : ' . config::byKey('jeeNetwork::master::ip'));
		log::add('teleinfo', 'info', 'Host / Port         : ' . $parsed_url['host'] . ':' . $parsed_url['port']);
		log::add('teleinfo', 'info', 'Path complémentaire : ' .  $parsed_url['path']);
		$ip_externe =  $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'] . $parsed_url['path'];
		log::add('teleinfo', 'info', 'Mise en forme pour le service : ' . $ip_externe);
		log::add('teleinfo', 'info', 'Port modem : ' . $port);
		log::add('teleinfo', 'info', '---------------------------------------------');
		if($parsed_url['host'] == ""){
			$ip_externe = config::byKey('jeeNetwork::master::ip');
			log::add('teleinfo', 'error', 'Attention, vérifiez que l\'ip est bien renseignée dans la partie Configuration Réseau.');
		}
		
		$cle_api = config::byKey('jeeNetwork::master::apikey');
		if($cle_api == ''){
			log::add('teleinfo', 'error', 'Erreur de clé api, veuillez la vérifier.');
			goto end;
		}	
		if($_debug){
			$_debug = "1";
		}
		else{
			$_debug = "0";
		}
		if($_force != "1"){
			$_force = "0";
		}
		if($_2cpt_cartelectronic == 1){
			log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteurs');
			//exec('sudo chmod 777 /dev/bus/usb/* > /dev/null 2>&1');
			$teleinfo_path = $teleinfo_path . '/teleinfo_2_cpt.py';
			$cmd = 'sudo nice -n 19 /usr/bin/python ' . $teleinfo_path . ' -d '.$_debug.' -p ' . $port . ' -v ' . $modem_vitesse . ' -e ' . $ip_externe . ' -c ' . config::byKey('jeeNetwork::master::apikey') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
		}
		else{
			log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
			$teleinfo_path = $teleinfo_path . '/teleinfo.py';
			$cmd = 'nice -n 19 /usr/bin/python ' . $teleinfo_path . ' -d '.$_debug.' -p ' . $port . ' -v ' . $modem_vitesse . ' -e ' . $ip_externe . ' -c ' . config::byKey('jeeNetwork::master::apikey') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
		}	
		
		log::add('teleinfo', 'info', 'Exécution du service : ' . $cmd);
		$result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('teleinfo') . ' 2>&1 &');
		if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
			log::add('teleinfo', 'error', $result);
			return false;
		}
		sleep(2);
		if (!self::deamonRunning()) {
			sleep(10);
			if (!self::deamonRunning()) {
				log::add('teleinfo', 'error', 'Impossible de lancer le démon téléinfo, vérifiez l\'ip', 'unableStartDeamon');
				return false;
			}
		}
		message::removeAll('teleinfo', 'unableStartDeamon');
		log::add('teleinfo', 'info', 'Service OK');
		log::add('teleinfo', 'info', '---------------------------------------------');
		end:
    }
	
	public static function runDeamon($_debug = false) {
        log::add('teleinfo', 'info', 'Mode local');
        $teleinfo_path = realpath(dirname(__FILE__) . '/../../ressources');
		$modem_serie_addr = config::byKey('port', 'teleinfo');
		$_debug = config::byKey('debug', 'teleinfo');
		$_force = config::byKey('force', 'teleinfo');
		$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
				if(config::byKey('modem_vitesse', 'teleinfo') == ""){
			$modem_vitesse = '1200';
		}else{
			$modem_vitesse = config::byKey('modem_vitesse', 'teleinfo');
		}
		if($modem_serie_addr == "serie"){
			$port = config::byKey('modem_serie_addr', 'teleinfo');
			goto lancement;
		}
		$port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
		if($_2cpt_cartelectronic == 1){
				$port = '/dev/ttyUSB1';
				goto lancement;
		}
        if (!file_exists($port)) {
			log::add('teleinfo', 'error', 'Le port n\'existe pas');
			goto end;
        }
		$cle_api = config::byKey('api');
		if($cle_api == ''){
			log::add('teleinfo', 'error', 'Erreur de clé api, veuillez la vérifier.');
			goto end;
		}	
		lancement:
		$parsed_url = parse_url(config::byKey('internalProtocol','core','http://') . config::byKey('internalAddr','core','127.0.0.1') . ":" . config::byKey('internalPort','core','80') . config::byKey('internalComplement','core'));
		exec('sudo chmod 777 ' . $port . ' > /dev/null 2>&1');
		
		log::add('teleinfo', 'info', '--------- Informations sur le master --------');
		log::add('teleinfo', 'info', 'Adresse             :' . config::byKey('internalProtocol','core','http://') . config::byKey('internalAddr','core','127.0.0.1') . ":" . config::byKey('internalPort','core','80') . config::byKey('internalComplement','core'));
		log::add('teleinfo', 'info', 'Host / Port         :' . $parsed_url['host'] . ':' . $parsed_url['port']);
		log::add('teleinfo', 'info', 'Path complémentaire :' .  $parsed_url['path']);
		$ip_interne =  $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'] . $parsed_url['path'];
		log::add('teleinfo', 'info', 'Mise en forme pour le service : ' . $ip_interne);
		log::add('teleinfo', 'info', 'Debug : ' . $_debug);
		log::add('teleinfo', 'info', 'Force : ' . $_force);
		log::add('teleinfo', 'info', 'Port modem : ' . $port);
		
		if($_debug){
			$_debug = "1";
		}
		else{
			$_debug = "0";
		}
		if($_force != "1"){
			$_force = "0";
		}
		log::add('teleinfo', 'info', '---------------------------------------------');
		
		if($_2cpt_cartelectronic == 1){
			log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteur');
			//exec('sudo chmod 777 /dev/bus/usb/* > /dev/null 2>&1');
			$teleinfo_path = $teleinfo_path . '/teleinfo_2_cpt.py';
			$cmd = 'sudo nice -n 19 /usr/bin/python ' . $teleinfo_path . ' -d '.$_debug.' -p ' . $port . ' -v ' . $modem_vitesse .' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
		}
		else{
			log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
			$teleinfo_path = $teleinfo_path . '/teleinfo.py';
			// $cmd = 'sudo nice -n 19 /usr/bin/python ' . $teleinfo_path . ' -d 0 -p ' . $port . ' -v ' . $modem_vitesse . ' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -r ' . realpath(dirname(__FILE__));
			$cmd = 'nice -n 19 /usr/bin/python ' . $teleinfo_path . ' -d '.$_debug.' -p ' . $port . ' -v ' . $modem_vitesse . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
		}
		
		log::add('teleinfo', 'info', 'Exécution du service : ' . $cmd);
		$result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('teleinfo') . ' 2>&1 &');
		if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
			log::add('teleinfo', 'error', $result);
			return false;
		}
		sleep(2);
		if (!self::deamonRunning()) {
			sleep(10);
			if (!self::deamonRunning()) {
				log::add('teleinfo', 'error', 'Impossible de lancer le démon téléinfo, vérifiez l\'ip', 'unableStartDeamon');
				return false;
			}
		}
		message::removeAll('teleinfo', 'unableStartDeamon');
		log::add('teleinfo', 'info', 'Service OK');
		log::add('teleinfo', 'info', '---------------------------------------------');
		end:
    }

    public static function deamonRunning() {
		$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
		if($_2cpt_cartelectronic == 1){
			$result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
			if($result != ""){
				//log::add('teleinfo', 'info', 'Vérification de l\'état du service : OK ');
				return true;
			}
			log::add('teleinfo', 'info', 'Vérification de l\'état du service : NOK ');
			return false;
		}else{
			$result = exec("ps aux | grep teleinfo.py | grep -v grep | awk '{print $2}'");
			if($result != ""){
				//log::add('teleinfo', 'info', 'Vérification de l\'état du service : OK ');
				return true;
			}
			log::add('teleinfo', 'info', 'Vérification de l\'état du service : NOK ');
			return false;
		}
    }
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'teleinfo';
		$return['state'] = 'nok';
		$pid_file = '/tmp/teleinfo.pid';
		if (file_exists($pid_file)) {
			if (posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec('sudo rm -rf ' . $pid_file . ' 2>&1 > /dev/null;rm -rf ' . $pid_file . ' 2>&1 > /dev/null;');
			}
		}
		/*if(self::deamonRunning()){
			$return['state'] = 'ok';
		}*/
		$return['launchable'] = 'ok';
		return $return;
	}
	
	public static function deamon_start($_debug = false) {
		
		if (config::byKey('jeeNetwork::mode') == 'slave') { //Je suis l'esclave
			if (!self::deamonRunning()) {
                self::runExternalDeamon($_debug);
            }
		}
		else{	// Je suis le jeedom master			
			if(config::byKey('port', 'teleinfo') != ""){	// Si un port est sélectionné
				if (!self::deamonRunning()) {
					self::runDeamon($_debug);
				}
				message::removeAll('teleinfo', 'noTeleinfoPort');
			}
			else{
				log::add('teleinfo', 'info', 'Pas d\'informations sur le port USB (Modem série ?)');
			}
		}
	}
	
	public static function deamon_stop() {
		log::add('teleinfo', 'info', '[deamon_stop] Arret du service');
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] == 'ok') {
			$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
			if($_2cpt_cartelectronic == 1){
				$result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
				system::kill($result);
			}
			else{
				$pid_file = '/tmp/teleinfo.pid';
                if (file_exists($pid_file)) {
                        $pid = intval(trim(file_get_contents($pid_file)));
						$kill = posix_kill($pid, 15);
						usleep(500);
						if ($kill) {
							return true;
						}
						else{
							system::kill($pid);
						}
                }
                system::kill('teleinfo.py');
                $port = config::byKey('port', 'teleinfo');
				if($port != "serie"){
					$port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
					system::fuserk(jeedom::getUsbMapping($port));
					sleep(1);
				}
				//$result = exec("ps aux | grep teleinfo.py | grep -v grep | awk '{print $2}'");
			}
		}
	}

    public static function stopDeamon() {
        if (!self::deamonRunning()) {
            return true;
        }
		
		log::add('teleinfo', 'info', 'Tentative d\'arrêt du service');
		
		$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
		if($_2cpt_cartelectronic == 1){
			$result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
		}
		else{
			$result = exec("ps aux | grep teleinfo.py | grep -v grep | awk '{print $2}'");
		}
		//foreach ($result as $pid) {
		exec('kill ' . $result);
		//}
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
				posix_kill($result, 9);
                sleep(1);
            }
        }
		try {
		exec('sudo kill 9 ' . $result . ' > /dev/null 2&1');
		} catch (Exception $e) {
			log::add('teleinfo', 'error', 'Impossible d\'arrêter le service');
		}
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
                sleep(1);
            }
        }
        return true;
    }
	
	public static function CalculateTodayStats(){
		$STAT_TODAY_HP = 0;
		$STAT_TODAY_HC = 0;
		$STAT_TENDANCE = 0;
		$STAT_YESTERDAY_HP = 0;
		$STAT_YESTERDAY_HC = 0;
		$TYPE_TENDANCE = 0;
		$stat_hp_to_cumul = array();
		$stat_hc_to_cumul = array();

		foreach (eqLogic::byType('teleinfo') as $eqLogic){
			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== "data" || $cmd->getConfiguration('type')== "") {
					switch ($cmd->getConfiguration('info_conso')) {
						case "BASE":
						case "HCHP":
						case "EJPHN":
						case "BBRHPJB":
						case "BBRHPJW":
						case "BBRHPJR":
						array_push($stat_hp_to_cumul, $cmd->getId()); 
						break;	
					}
					switch ($cmd->getConfiguration('info_conso')) {
						case "HCHC":
						case "BBRHCJB":
						case "BBRHCJW":
						case "BBRHCJR":
						array_push($stat_hc_to_cumul, $cmd->getId()); 
						break;	
					}
				}
				if($cmd->getConfiguration('info_conso') == "TENDANCE_DAY"){
					$TYPE_TENDANCE = $cmd->getConfiguration('type_calcul_tendance');
				}
			}
		}
		
		log::add('teleinfo', 'info', '----- Calcul des statistiques temps réel -----');
		log::add('teleinfo', 'info', 'Date de début : ' . date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")  , date("d"), date("Y"))));
		log::add('teleinfo', 'info', 'Date de fin   : ' . date("Y-m-d H:i:s" ,mktime(date("H"), date("i"),date("s"), date("m")  , date("d"), date("Y"))));
		log::add('teleinfo', 'info', '----------------------------------------------');
		//log::add('teleinfo', 'info', '----- Calcul des statistiques horraires -----');
		//log::add('teleinfo', 'info', 'StartDateLastHour : ' . date("Y-m-d H:i:s" ,mktime(date("H")-1, date("i"),date("s"), date("m")  , date("d"), date("Y")));
		//log::add('teleinfo', 'info', 'EndDateLastHour : ' . date("Y-m-d H:i:s" ,mktime(date("H"), date("i"),date("s"), date("m")  , date("d"), date("Y")));
		
		$startdatetoday = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$enddatetoday = date("Y-m-d H:i:s" ,mktime(date("H"), date("i"),date("s"), date("m")  , date("d"), date("Y")));
		$startdateyesterday = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
		
		//$startdatelasthour = date("Y-m-d H:i:s" ,mktime(date("H")-1, date("i"),date("s"), date("m")  , date("d"), date("Y")));
		//$enddatelasthour = date("Y-m-d H:i:s" ,mktime(date("H"), date("i"),date("s"), date("m")  , date("d"), date("Y")));

		if($TYPE_TENDANCE == 1){
			$enddateyesterday = date("Y-m-d H:i:s" ,mktime(23, 59,59, date("m")  , date("d")-1, date("Y")));
		}
		else{
			$enddateyesterday = date("Y-m-d H:i:s" ,mktime(date("H"), date("i"),date("s"), date("m")  , date("d")-1, date("Y")));
		}
		
		foreach ($stat_hc_to_cumul as $key => $value){
			log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
			//$cache = cache::byKey('teleinfo::stats::' . $value, false, true);
			$cmd = cmd::byId($value);
			/*log::add('teleinfo', 'info', 'HC : ');
			foreach($cmd->getStatistique($startdatetoday,$enddatetoday) as $key => $value){
				log::add('teleinfo', 'info', '[' . $key . '] ' . $value );
			}*/
			log::add('teleinfo', 'debug', ' ==> Valeur HC MAX : ' . $cmd->getStatistique($startdatetoday,$enddatetoday)['max']);
			log::add('teleinfo', 'debug', ' ==> Valeur HC MIN : ' . $cmd->getStatistique($startdatetoday,$enddatetoday)['min']);
			
			$STAT_TODAY_HC += intval($cmd->getStatistique($startdatetoday,$enddatetoday)['max']) - intval($cmd->getStatistique($startdatetoday,$enddatetoday)['min']);
			$STAT_YESTERDAY_HC += intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['min']);
			log::add('teleinfo', 'debug', 'Total HC --> ' . $STAT_TODAY_HC);
		}
		foreach ($stat_hp_to_cumul as $key => $value){
			//log::add('teleinfo', 'debug', 'ID HP --> ' . $value);
			log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
			$cmd = cmd::byId($value);
			/*log::add('teleinfo', 'info', 'HP : ');
			foreach($cmd->getStatistique($startdatetoday,$enddatetoday) as $key => $value){
				log::add('teleinfo', 'info', '[' . $key . '] ' . $value );
			}*/
			log::add('teleinfo', 'debug', ' ==> Valeur HP MAX : ' . $cmd->getStatistique($startdatetoday,$enddatetoday)['max']);
			log::add('teleinfo', 'debug', ' ==> Valeur HP MIN : ' . $cmd->getStatistique($startdatetoday,$enddatetoday)['min']);
			
			$STAT_TODAY_HP += intval($cmd->getStatistique($startdatetoday,$enddatetoday)['max']) - intval($cmd->getStatistique($startdatetoday,$enddatetoday)['min']);
			$STAT_YESTERDAY_HP += intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['min']);
			log::add('teleinfo', 'debug', 'Total HP --> ' . $STAT_TODAY_HP);
		}
		
		/*if(($STAT_MAX_YESTERDAY - $STAT_TODAY) > 100){
			$STAT_TENDANCE = -1;
		}
		else if (($STAT_MAX_YESTERDAY - $STAT_TODAY) < 100){
			$STAT_TENDANCE = 1;
		}*/


		foreach (eqLogic::byType('teleinfo') as $eqLogic){

			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== "stat") {
					if($cmd->getConfiguration('info_conso') == "STAT_TODAY"){
						log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière ==> ' . intval($STAT_TODAY_HP + $STAT_TODAY_HC));
						$cmd->setValue(intval($STAT_TODAY_HP + $STAT_TODAY_HC));
						$cmd->event(intval($STAT_TODAY_HP + $STAT_TODAY_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "TENDANCE_DAY"){
						log::add('teleinfo', 'debug', 'Mise à jour de la tendance journalière ==> ' . '(Hier : '. intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) . ' Aujourd\'hui : ' . intval($STAT_TODAY_HC + $STAT_TODAY_HP) . ' Différence : ' . (intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) - intval($STAT_TODAY_HC + $STAT_TODAY_HP)) . ')');
						$cmd->setValue(intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) - intval($STAT_TODAY_HC + $STAT_TODAY_HP));
						$cmd->event(intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) - intval($STAT_TODAY_HC + $STAT_TODAY_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_TODAY_HP"){
						log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HP) ==> ' . intval($STAT_TODAY_HP));
						$cmd->setValue(intval($STAT_TODAY_HP));
						$cmd->event(intval($STAT_TODAY_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_TODAY_HC"){
						log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HC) ==> ' . intval($STAT_TODAY_HC));
						$cmd->setValue(intval($STAT_TODAY_HC));
						$cmd->event(intval($STAT_TODAY_HC));
					}
				}
			}
		}
	
	}
	
	public static function CalculateOtherStats(){
		$STAT_YESTERDAY = 0;
		$STAT_YESTERDAY_HC = 0;
		$STAT_YESTERDAY_HP = 0;
		$STAT_LASTMONTH = 0;
		$STAT_MONTH = 0;
		$STAT_YEAR = 0;
		$STAT_JAN_HP = 0;
		$STAT_JAN_HC = 0;
		$STAT_FEV_HP = 0;
		$STAT_FEV_HC = 0;
		$STAT_MAR_HP = 0;
		$STAT_MAR_HC = 0;
		$STAT_AVR_HP = 0;
		$STAT_AVR_HC = 0;
		$STAT_MAI_HP = 0;
		$STAT_MAI_HC = 0;
		$STAT_JUIN_HP = 0;
		$STAT_JUIN_HC = 0;
		$STAT_JUI_HP = 0;
		$STAT_JUI_HC = 0;
		$STAT_AOU_HP = 0;
		$STAT_AOU_HC = 0;
		$STAT_SEP_HP = 0;
		$STAT_SEP_HC = 0;
		$STAT_OCT_HP = 0;
		$STAT_OCT_HC = 0;
		$STAT_NOV_HP = 0;
		$STAT_NOV_HC = 0;
		$STAT_DEC_HP = 0;
		$STAT_DEC_HC = 0;

		$stat_hp_to_cumul = array();
		$stat_hc_to_cumul = array();
		log::add('teleinfo', 'info', '----- Calcul des statistiques de la journée -----');
		foreach (eqLogic::byType('teleinfo') as $eqLogic){
			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== "data" || $cmd->getConfiguration('type')== "") {
					switch ($cmd->getConfiguration('info_conso')) {
						case "BASE":
						case "HCHP":
						case "EJPHN":
						case "BBRHPJB":
						case "BBRHPJW":
						case "BBRHPJR":
						array_push($stat_hp_to_cumul, $cmd->getId()); 
						break;	
					}
					switch ($cmd->getConfiguration('info_conso')) {
						case "HCHC":
						case "BBRHCJB":
						case "BBRHCJW":
						case "BBRHCJR":
						array_push($stat_hc_to_cumul, $cmd->getId()); 
						break;	
					}
				}
			}
		}

		$startdateyesterday = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
		$enddateyesterday = date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m"), date("d")-1, date("Y")));
		
		$startdateyear = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 1, 1, date("Y")));
		$enddateyear = date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m"), date("d")-1, date("Y")));
		/*$startdateyesterday = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		$enddateyesterday = date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m"), date("d"), date("Y")));*/
		$startdatemonth = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")  , 1, date("Y")));
		$enddatemonth = date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m")  , date("d"), date("Y")));
		$startdatelastmonth = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")-1  , 1, date("Y")));
		$enddatelastmonth = date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m")-1  , date("t", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y"))), date("Y")));
		
		
		$startdate_jan = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 1, 1, date("Y")));	$enddate_jan = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 1  , 31, date("Y")));
		$startdate_fev = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 2, 1, date("Y")));	$enddate_fev = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 2  , 28, date("Y")));
		$startdate_mar = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 3, 1, date("Y")));	$enddate_mar = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 3  , 31, date("Y")));
		$startdate_avr = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 4, 1, date("Y")));	$enddate_avr = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 4  , 30, date("Y")));
		$startdate_mai = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 5, 1, date("Y")));	$enddate_mai = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 5  , 31, date("Y")));
		$startdate_juin = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 6, 1, date("Y")));	$enddate_juin = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 6  , 30, date("Y")));
		$startdate_jui = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 7, 1, date("Y")));	$enddate_jui = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 7  , 31, date("Y")));
		$startdate_aou = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 8, 1, date("Y")));	$enddate_aou = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 8  , 31, date("Y")));
		$startdate_sep = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 9, 1, date("Y")));	$enddate_sep = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 9  , 30, date("Y")));
		$startdate_oct = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 10, 1, date("Y")));	$enddate_oct = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 10  , 31, date("Y")));
		$startdate_nov = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 11, 1, date("Y")));	$enddate_nov = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 11  , 30, date("Y")));
		$startdate_dec = date("Y-m-d H:i:s" ,mktime(0, 0, 0, 12, 1, date("Y")));	$enddate_dec = date("Y-m-d H:i:s" ,mktime(23, 59, 59, 12  , 31, date("Y")));
		
		foreach ($stat_hc_to_cumul as $key => $value){
			log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
			//$cache = cache::byKey('teleinfo::stats::' . $value, false, true);
			$cmd = cmd::byId($value);
			//$STAT_TODAY_HC += intval($cmd->getStatistique($startdatetoday,$enddatetoday)[max]) - intval($cmd->getStatistique($startdatetoday,$enddatetoday)[min]);
			$STAT_YESTERDAY_HC += intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['min']);
			$STAT_MONTH += intval($cmd->getStatistique($startdatemonth,$enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth,$enddatemonth)['min']);
			$STAT_YEAR += intval($cmd->getStatistique($startdateyear,$enddateyear)['max']) - intval($cmd->getStatistique($startdateyear,$enddateyear)['min']);
			$STAT_LASTMONTH += intval($cmd->getStatistique($startdatelastmonth,$enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth,$enddatelastmonth)['min']);
			$STAT_JAN_HC += intval($cmd->getStatistique($startdate_jan,$enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan,$enddate_jan)['min']);
			$STAT_FEV_HC += intval($cmd->getStatistique($startdate_fev,$enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev,$enddate_fev)['min']);
			$STAT_MAR_HC += intval($cmd->getStatistique($startdate_mar,$enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar,$enddate_mar)['min']);
			$STAT_AVR_HC += intval($cmd->getStatistique($startdate_avr,$enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr,$enddate_avr)['min']);
			$STAT_MAI_HC += intval($cmd->getStatistique($startdate_mai,$enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai,$enddate_mai)['min']);
			$STAT_JUIN_HC += intval($cmd->getStatistique($startdate_juin,$enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin,$enddate_juin)['min']);
			$STAT_JUI_HC += intval($cmd->getStatistique($startdate_jui,$enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui,$enddate_jui)['min']);
			$STAT_AOU_HC += intval($cmd->getStatistique($startdate_aou,$enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou,$enddate_aou)['min']);
			$STAT_SEP_HC += intval($cmd->getStatistique($startdate_sep,$enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep,$enddate_sep)['min']);
			$STAT_OCT_HC += intval($cmd->getStatistique($startdate_oct,$enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct,$enddate_oct)['min']);
			$STAT_NOV_HC += intval($cmd->getStatistique($startdate_nov,$enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov,$enddate_nov)['min']);
			$STAT_DEC_HC += intval($cmd->getStatistique($startdate_dec,$enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec,$enddate_dec)['min']);
			
			//log::add('teleinfo', 'info', 'Conso HC --> ' . $STAT_TODAY_HC);
		}
		foreach ($stat_hp_to_cumul as $key => $value){
			log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
			$cmd = cmd::byId($value);
			$STAT_YESTERDAY_HP += intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday,$enddateyesterday)['min']);			
			$STAT_MONTH += intval($cmd->getStatistique($startdatemonth,$enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth,$enddatemonth)['min']);
			$STAT_YEAR += intval($cmd->getStatistique($startdateyear,$enddateyear)['max']) - intval($cmd->getStatistique($startdateyear,$enddateyear)['min']);
			$STAT_LASTMONTH += intval($cmd->getStatistique($startdatelastmonth,$enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth,$enddatelastmonth)['min']);

			$STAT_JAN_HP += intval($cmd->getStatistique($startdate_jan,$enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan,$enddate_jan)['min']);
			$STAT_FEV_HP += intval($cmd->getStatistique($startdate_fev,$enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev,$enddate_fev)['min']);
			$STAT_MAR_HP += intval($cmd->getStatistique($startdate_mar,$enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar,$enddate_mar)['min']);
			$STAT_AVR_HP += intval($cmd->getStatistique($startdate_avr,$enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr,$enddate_avr)['min']);
			$STAT_MAI_HP += intval($cmd->getStatistique($startdate_mai,$enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai,$enddate_mai)['min']);
			$STAT_JUIN_HP += intval($cmd->getStatistique($startdate_juin,$enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin,$enddate_juin)['min']);
			$STAT_JUI_HP += intval($cmd->getStatistique($startdate_jui,$enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui,$enddate_jui)['min']);
			$STAT_AOU_HP += intval($cmd->getStatistique($startdate_aou,$enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou,$enddate_aou)['min']);
			$STAT_SEP_HP += intval($cmd->getStatistique($startdate_sep,$enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep,$enddate_sep)['min']);
			$STAT_OCT_HP += intval($cmd->getStatistique($startdate_oct,$enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct,$enddate_oct)['min']);
			$STAT_NOV_HP += intval($cmd->getStatistique($startdate_nov,$enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov,$enddate_nov)['min']);
			$STAT_DEC_HP += intval($cmd->getStatistique($startdate_dec,$enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec,$enddate_dec)['min']);
			//log::add('teleinfo', 'info', 'Conso HP --> ' . $STAT_TODAY_HP);
		}
		

		foreach (eqLogic::byType('teleinfo') as $eqLogic){

			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== "stat" || $cmd->getConfiguration('type')== "panel") {
					if($cmd->getConfiguration('info_conso') == "STAT_YESTERDAY"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier ==> ' . intval($STAT_YESTERDAY_HC) + intval($STAT_YESTERDAY_HP));
						$cmd->setValue(intval($STAT_YESTERDAY_HC) + intval($STAT_YESTERDAY_HP));
						$cmd->event(intval($STAT_YESTERDAY_HC) + intval($STAT_YESTERDAY_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_YESTERDAY_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HP) ==> ' . intval($STAT_YESTERDAY_HP));
						$cmd->setValue(intval($STAT_YESTERDAY_HP));
						$cmd->event(intval($STAT_YESTERDAY_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_YESTERDAY_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HC) ==> ' . intval($STAT_YESTERDAY_HC));
						$cmd->setValue(intval($STAT_YESTERDAY_HC));
						$cmd->event(intval($STAT_YESTERDAY_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_LASTMONTH"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois dernier ==> ' . intval($STAT_LASTMONTH));
						$cmd->setValue(intval($STAT_LASTMONTH));
						$cmd->event(intval($STAT_LASTMONTH));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_MONTH"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois en cours ==> ' . intval($STAT_MONTH));
						$cmd->setValue(intval($STAT_MONTH));
						$cmd->event(intval($STAT_MONTH));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_YEAR"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique anuelle ==> ' . intval($STAT_YEAR));
						$cmd->setValue(intval($STAT_YEAR));
						$cmd->event(intval($STAT_YEAR));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JAN_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HP) ==> ' . intval($STAT_JAN_HP));
						$cmd->setValue(intval($STAT_JAN_HP));
						$cmd->event(intval($STAT_JAN_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JAN_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HC) ==> ' . intval($STAT_JAN_HC));
						$cmd->setValue(intval($STAT_JAN_HC));
						$cmd->event(intval($STAT_JAN_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_FEV_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HP) ==> ' . intval($STAT_FEV_HP));
						$cmd->setValue(intval($STAT_FEV_HP));
						$cmd->event(intval($STAT_FEV_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_FEV_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HC) ==> ' . intval($STAT_FEV_HC));
						$cmd->setValue(intval($STAT_FEV_HC));
						$cmd->event(intval($STAT_FEV_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_MAR_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HP) ==> ' . intval($STAT_MAR_HP));
						$cmd->setValue(intval($STAT_MAR_HP));
						$cmd->event(intval($STAT_MAR_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_MAR_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HC) ==> ' . intval($STAT_MAR_HC));
						$cmd->setValue(intval($STAT_MAR_HC));
						$cmd->event(intval($STAT_MAR_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_AVR_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HP) ==> ' . intval($STAT_AVR_HP));
						$cmd->setValue(intval($STAT_AVR_HP));
						$cmd->event(intval($STAT_AVR_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_AVR_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HC) ==> ' . intval($STAT_AVR_HC));
						$cmd->setValue(intval($STAT_AVR_HC));
						$cmd->event(intval($STAT_AVR_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_MAI_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HP) ==> ' . intval($STAT_MAI_HP));
						$cmd->setValue(intval($STAT_MAI_HP));
						$cmd->event(intval($STAT_MAI_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_MAI_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HC) ==> ' . intval($STAT_MAI_HC));
						$cmd->setValue(intval($STAT_MAI_HC));
						$cmd->event(intval($STAT_MAI_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JUIN_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HP) ==> ' . intval($STAT_JUIN_HP));
						$cmd->setValue(intval($STAT_JUIN_HP));
						$cmd->event(intval($STAT_JUIN_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JUIN_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HC) ==> ' . intval($STAT_JUIN_HC));
						$cmd->setValue(intval($STAT_JUIN_HC));
						$cmd->event(intval($STAT_JUIN_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JUI_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HP) ==> ' . intval($STAT_JUI_HP));
						$cmd->setValue(intval($STAT_JUI_HP));
						$cmd->event(intval($STAT_JUI_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_JUI_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HC) ==> ' . intval($STAT_JUI_HC));
						$cmd->setValue(intval($STAT_JUI_HC));
						$cmd->event(intval($STAT_JUI_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_AOU_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HP) ==> ' . intval($STAT_AOU_HP));
						$cmd->setValue(intval($STAT_AOU_HP));
						$cmd->event(intval($STAT_AOU_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_AOU_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HC) ==> ' . intval($STAT_AOU_HC));
						$cmd->setValue(intval($STAT_AOU_HC));
						$cmd->event(intval($STAT_AOU_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_SEP_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HP) ==> ' . intval($STAT_SEP_HP));
						$cmd->setValue(intval($STAT_SEP_HP));
						$cmd->event(intval($STAT_SEP_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_SEP_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HC) ==> ' . intval($STAT_SEP_HC));
						$cmd->setValue(intval($STAT_SEP_HC));
						$cmd->event(intval($STAT_SEP_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_OCT_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HP) ==> ' . intval($STAT_OCT_HP));
						$cmd->setValue(intval($STAT_OCT_HP));
						$cmd->event(intval($STAT_OCT_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_OCT_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HC) ==> ' . intval($STAT_OCT_HC));
						$cmd->setValue(intval($STAT_OCT_HC));
						$cmd->event(intval($STAT_OCT_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_NOV_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HP) ==> ' . intval($STAT_NOV_HP));
						$cmd->setValue(intval($STAT_NOV_HP));
						$cmd->event(intval($STAT_NOV_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_NOV_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HC) ==> ' . intval($STAT_NOV_HC));
						$cmd->setValue(intval($STAT_NOV_HC));
						$cmd->event(intval($STAT_NOV_HC));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_DEC_HP"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HP) ==> ' . intval($STAT_DEC_HP));
						$cmd->setValue(intval($STAT_DEC_HP));
						$cmd->event(intval($STAT_DEC_HP));
					}
					else if($cmd->getConfiguration('info_conso') == "STAT_DEC_HC"){
						log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HC) ==> ' . intval($STAT_DEC_HC));
						$cmd->setValue(intval($STAT_DEC_HC));
						$cmd->event(intval($STAT_DEC_HC));
					}
				}
			}
		}
	
	}
	
	public static function Moy_Last_Hour(){
		$ppap_hp = 0;
		$ppap_hc = 0;
		$cmd_ppap = null;
		foreach (eqLogic::byType('teleinfo') as $eqLogic){
			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== 'stat') {
					if($cmd->getConfiguration('info_conso') == 'STAT_MOY_LAST_HOUR'){
						log::add('teleinfo', 'debug', '----- Calcul de la consommation moyenne sur la dernière heure -----');
						$cmd_ppap = $cmd;
					}
				}
			}
			if($cmd_ppap != null){
				//log::add('teleinfo', 'debug', 'Cmd trouvée');
				foreach ($eqLogic->getCmd('info') as $cmd) {
					if ($cmd->getConfiguration('type')== "data" || $cmd->getConfiguration('type')== "") {
						switch ($cmd->getConfiguration('info_conso')) {
							case "BASE":
							case "HCHP":
							case "BBRHPJB":
							case "BBRHPJW":
							case "BBRHPJR":
							$ppap_hp += $cmd->execCmd();
							log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
							break;	
						}
						switch ($cmd->getConfiguration('info_conso')) {
							case "HCHC":
							case "BBRHCJB":
							case "BBRHCJW":
							case "BBRHCJR":
							$ppap_hc += $cmd->execCmd();
							log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
							break;	
						}
					}
				}
			
				$cache_hc = cache::byKey('teleinfo::stat_moy_last_hour::hc', false);
				$cache_hp = cache::byKey('teleinfo::stat_moy_last_hour::hp', false);
				$cache_hc = $cache_hc->getValue();
				$cache_hp = $cache_hp->getValue();
				
				log::add('teleinfo', 'debug', 'Cache HP : ' . $cache_hp);
				log::add('teleinfo', 'debug', 'Cache HC : ' . $cache_hc);

				log::add('teleinfo', 'debug', 'Conso Wh : ' . (($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc)) );
				$cmd_ppap->event(intval((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc))));
				
				cache::set('teleinfo::stat_moy_last_hour::hc',$ppap_hc , 7200);
				cache::set('teleinfo::stat_moy_last_hour::hp',$ppap_hp, 7200);

			}
			else{
				log::add('teleinfo', 'debug', 'Pas de calcul');
			}
		}
	}
	
	public static function Calculate_PAPP(){
		$ppap_hp = 0;
		$ppap_hc = 0;
		$cmd_ppap = null;
		foreach (eqLogic::byType('teleinfo') as $eqLogic){
			foreach ($eqLogic->getCmd('info') as $cmd) {
				if ($cmd->getConfiguration('type')== 'stat') {
					if($cmd->getConfiguration('info_conso') == 'PPAP_MANUELLE'){
						log::add('teleinfo', 'debug', '----- Calcul de la puissance apparante moyenne -----');
						$cmd_ppap = $cmd;
					}
				}
			}
			if($cmd_ppap != null){
				log::add('teleinfo', 'debug', 'Cmd trouvée');
				foreach ($eqLogic->getCmd('info') as $cmd) {
					if ($cmd->getConfiguration('type')== "data" || $cmd->getConfiguration('type')== "") {
						switch ($cmd->getConfiguration('info_conso')) {
							case "BASE":
							case "HCHP":
							case "BBRHPJB":
							case "BBRHPJW":
							case "BBRHPJR":
							$ppap_hp += $cmd->execCmd();
							break;	
						}
						switch ($cmd->getConfiguration('info_conso')) {
							case "HCHC":
							case "BBRHCJB":
							case "BBRHCJW":
							case "BBRHCJR":
							$ppap_hc += $cmd->execCmd();
							break;	
						}
					}
				}
			
				$cache_hc = cache::byKey('teleinfo::ppap_manuelle::hc', false);
				$datetime_mesure = date_create($cache_hc->getDatetime());
				$cache_hp = cache::byKey('teleinfo::ppap_manuelle::hp', false);
				$cache_hc = $cache_hc->getValue();
				$cache_hp = $cache_hp->getValue();
				
				$datetime_mesure = $datetime_mesure->getTimestamp();
				$datetime2 = time();
				$interval = $datetime2 - $datetime_mesure;
				log::add('teleinfo', 'debug', 'Intervale depuis la dernière valeur : ' . $interval );
				// log::add('teleinfo', 'debug', 'Conso calculée : ' . (($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc)) . ' Wh' );
				log::add('teleinfo', 'debug', 'Conso calculée : ' . ((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc)) / $interval) * 3600 . ' Wh' );
				// $cmd_ppap->setValue(intval((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc))* $interval));
				$cmd_ppap->setValue(intval(((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc)) / $interval) * 3600));
				// $cmd_ppap->event(intval((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc))* $interval));
				$cmd_ppap->event(intval(((($ppap_hp - $cache_hp) + ($ppap_hc - $cache_hc)) / $interval) * 3600));
				
				cache::set('teleinfo::ppap_manuelle::hc',$ppap_hc , 150);
				cache::set('teleinfo::ppap_manuelle::hp',$ppap_hp, 150);

			}
			else{
				log::add('teleinfo', 'debug', 'Pas de calcul');
			}
		}
	}
	
	public function preSave() {
		$this->setCategory('energy',  1);
		$cmd = null;
		$cmd = $this->getCmd('info','HEALTH');
		if (is_object($cmd)) {
            $cmd->remove();
			$cmd->save();
        }
	}
	
	public function postSave() {
		log::add('teleinfo', 'debug', '-------- Sauvegarde de l\'objet --------');
		//$template_name = "";

		/*if($this->getConfiguration('template') == 'bleu'){
                    $template_name = "teleinfo_bleu_";		
		}
        else if($this->getConfiguration('template') == 'base'){
            $template_name = "teleinfo_base_";
        }
        else if($this->getConfiguration('template') == ''){
            goto after_template;
        }
		log::add('teleinfo', 'info', '==> Gestion des templates');
              */  
		foreach ($this->getCmd(null, null, true) as $cmd) {
			 //$replace['#'.$cmd->getLogicalId().'#'] = $cmd->toHtml($_version);
			 switch ($cmd->getConfiguration('info_conso')) {
					case "BASE":
					case "HCHP":
					case "EJPHN":
					case "BBRHPJB":
					case "BBRHPJW":
					case "BBRHPJR":
					case "HCHC":
					case "BBRHCJB":
					case "BBRHCJW":
					case "BBRHCJR":
						log::add('teleinfo', 'debug', '=> index');
						if($cmd->getDisplay('generic_type') == ''){
							$cmd->setDisplay('generic_type','GENERIC_INFO');
						}
						//$cmd->setTemplate('dashboard',  $template_name . 'teleinfo_new_index');
						//$cmd->setTemplate('mobile',  $template_name . 'teleinfo_new_index');
						$cmd->save();
						$cmd->refresh();
						break;
					case "PAPP":
						log::add('teleinfo', 'debug', '=> papp');
						if($cmd->getDisplay('generic_type') == ''){
							$cmd->setDisplay('generic_type','GENERIC_INFO');
							$cmd->setDisplay('icon','<i class=\"fa fa-tachometer\"><\/i>');
						}
						//$cmd->setTemplate('dashboard',  $template_name . 'teleinfo_conso_inst');
						//$cmd->setTemplate('mobile',  $template_name . 'teleinfo_conso_inst');
						$cmd->save();
						$cmd->refresh();
					break;	
					case "PTEC":
						log::add('teleinfo', 'debug', '=> ptec');
						if($cmd->getDisplay('generic_type') == ''){
							$cmd->setDisplay('generic_type','GENERIC_INFO');
						}
						//$cmd->setTemplate('dashboard',  $template_name . 'teleinfo_ptec');
						//$cmd->setTemplate('mobile',  $template_name . 'teleinfo_ptec');
						$cmd->save();
						$cmd->refresh();
						break;
					default :
						log::add('teleinfo', 'debug', '=> ptec');
						if($cmd->getDisplay('generic_type') == ''){
							$cmd->setDisplay('generic_type','GENERIC_INFO');
						}
						break;
				}
		}
		after_template:
		log::add('teleinfo', 'info', '==> Gestion des id des commandes');
		foreach ($this->getCmd('info') as $cmd) {
		//foreach ($this->getCmd(null, null, true) as $cmd) {
			log::add('teleinfo', 'debug', 'Commande : ' . $cmd->getConfiguration('info_conso'));
			$cmd->setLogicalId($cmd->getConfiguration('info_conso'));
			$cmd->save();
		}
		log::add('teleinfo', 'debug', '-------- Fin de la sauvegarde --------');

		if($this->getConfiguration('AutoGenerateFields') == '1'){
			$this->CreateFromAbo($this->getConfiguration('abonnement'));
		}
		
		$this->CreateOtherCmd();
	
		$this->CreatePanelStats();
		
		/*foreach ($this->getCmd(null, null, true) as $cmd) {
			$cmd->setLogicalId($cmd->getConfiguration('info_conso'));
			$cmd->save();
		}*/
	}
	
	public function preRemove() {
		log::add('teleinfo', 'debug', 'Suppression d\'un objet');
	}
	
	public function CreateOtherCmd(){
		$array = array("HEALTH");
		for($ii = 0; $ii < 1; $ii++){
			$cmd = $this->getCmd('info',$array[$ii]);
			if ($cmd == null) {
				$cmd = null;
				$cmd = new teleinfoCmd();
				$cmd->setName($array[$ii]);
				$cmd->setEqLogic_id($this->id);
				$cmd->setLogicalId($array[$ii]);
				$cmd->setType('info');
				$cmd->setConfiguration('info_conso', $array[$ii]);
				$cmd->setConfiguration('type', 'health');
				$cmd->setSubType('numeric');
				$cmd->setUnite('Wh');
				$cmd->setIsHistorized(0);
				$cmd->setEventOnly(1);
				$cmd->setIsVisible(0);
				$cmd->save();
			}
		}
	}
	
	public function CreatePanelStats(){
		$array = array("STAT_JAN_HP","STAT_JAN_HC", "STAT_FEV_HP","STAT_FEV_HC", "STAT_MAR_HP","STAT_MAR_HC", "STAT_AVR_HP","STAT_AVR_HC", "STAT_MAI_HP","STAT_MAI_HC", "STAT_JUIN_HP","STAT_JUIN_HC", "STAT_JUI_HP","STAT_JUI_HC", "STAT_AOU_HP","STAT_AOU_HC", "STAT_SEP_HP","STAT_SEP_HC", "STAT_OCT_HP","STAT_OCT_HC", "STAT_NOV_HP","STAT_NOV_HC", "STAT_DEC_HP","STAT_DEC_HC");
		for($ii = 0; $ii < 24; $ii++){
			$cmd = $this->getCmd('info',$array[$ii]);
			if ($cmd == null) {
				$cmd = null;
				$cmd = new teleinfoCmd();
				$cmd->setName($array[$ii]);
				$cmd->setEqLogic_id($this->id);
				$cmd->setLogicalId($array[$ii]);
				$cmd->setType('info');
				$cmd->setConfiguration('info_conso', $array[$ii]);
				$cmd->setConfiguration('type', 'panel');
				$cmd->setDisplay('generic_type','DONT');
				$cmd->setSubType('numeric');
				$cmd->setUnite('Wh');
				$cmd->setIsHistorized(0);
				$cmd->setEventOnly(1);
				$cmd->setIsVisible(0);
				$cmd->save();
			}
			else{
				$cmd->setDisplay('generic_type','DONT');
				$cmd->save();
			}
		}
	}
	
	public function CreateFromAbo($_abo){
		$this->setConfiguration('AutoGenerateFields','0');
		$this->save();
		if($_abo == 'base'){
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Index');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('BASE');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'BASE');
			$cmd->setDisplay('generic_type','GENERIC_INFO');
			$cmd->setSubType('numeric');
			$cmd->setUnite('Wh');
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Intensité Instantanée');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('IINST');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'IINST');
			$cmd->setDisplay('generic_type','DONT');
			$cmd->setSubType('numeric');
			$cmd->setUnite('A');
			$cmd->setIsHistorized(0);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Puissance apparente');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('PAPP');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'PAPP');
			$cmd->setDisplay('generic_type','GENERIC_INFO');
			$cmd->setDisplay('icon','<i class=\"fa fa-tachometer\"><\/i>');
			$cmd->setSubType('numeric');
			$cmd->setUnite('VA (~W)');
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Dépassement');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('ADPS');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'ADPS');
			$cmd->setDisplay('generic_type','DONT');
			$cmd->setSubType('numeric');
			$cmd->setUnite('A');
			$cmd->setIsHistorized(0);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
		}
		else if($_abo == 'bleu'){
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Index HP');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('HCHP');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'HCHP');
			$cmd->setDisplay('generic_type','GENERIC_INFO');
			$cmd->setSubType('numeric');
			$cmd->setUnite('Wh');
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Index HC');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('HCHC');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'HCHC');
			$cmd->setDisplay('generic_type','GENERIC_INFO');
			$cmd->setSubType('numeric');
			$cmd->setUnite('Wh');
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Puissance Apparente');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('PAPP');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'PAPP');
			$cmd->setDisplay('generic_type','GENERIC_INFO');
			$cmd->setDisplay('icon','<i class=\"fa fa-tachometer\"><\/i>');
			$cmd->setSubType('numeric');
			$cmd->setUnite('VA (~W)');
			$cmd->setIsHistorized(1);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Intensité');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('IINST');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'IINST');
			$cmd->setDisplay('generic_type','DONT');
			$cmd->setSubType('numeric');
			$cmd->setUnite('A');
			$cmd->setIsHistorized(0);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Dépassement');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('ADPS');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'ADPS');
			$cmd->setDisplay('generic_type','DONT');
			$cmd->setSubType('numeric');
			$cmd->setUnite('A');
			$cmd->setIsHistorized(0);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
			$cmd = null;
			$cmd = new teleinfoCmd();
			$cmd->setName('Plage Horaire');
			$cmd->setEqLogic_id($this->id);
			$cmd->setLogicalId('HHPHC');
			$cmd->setType('info');
			$cmd->setConfiguration('info_conso', 'HHPHC');
			$cmd->setDisplay('generic_type','DONT');
			$cmd->setSubType('string');
			//$cmd->setUnite('');
			$cmd->setIsHistorized(0);
			$cmd->setEventOnly(1);
			$cmd->setIsVisible(1);
			$cmd->save();
		}
		
	
	
	}
	
	/*public function toHtml($_version = 'dashboard') 
	{
		$_version = jeedom::versionAlias($_version);
		$replace = array(
			'#id#' => $this->getId(),
			'#name#' => ($this->getIsEnable()) ? $this->getName() : '<del>' . $this->getName() . '</del>',
			'#background_color#' => $this->getBackgroundColor($_version),
			'#eqLink#' => $this->getLinkToConfiguration(),
		);
		if ($this->getIsEnable()) {
			foreach ($this->getCmd(null, null, true) as $cmd) {
				 $replace['#'.$cmd->getLogicalId().'#'] = $cmd->toHtml($_version);
			}
		}     
		if ($_version == 'dview' || $_version == 'mview') {
			$object = $this->getObject();
			$replace['#name#'] = (is_object($object)) ? $object->getName() . ' - ' . $replace['#name#'] : $replace['#name#'];
		}
		
		//log::add('presence', 'debug', 'HTML : ' . $replace["#name#"]);
		$parameters = $this->getDisplay('parameters');
		if (is_array($parameters)) {
			foreach ($parameters as $key => $value) {
				$replace['#' . $key . '#'] = $value;
			}
		}
		log::add('presence', 'debug', 'Template sélectionné : ' . $this->getConfiguration('template'););
	
		
		//cache::set('weatherWidget' . $_version . $this->getId(), $html, 0);

		//return template_replace($replace, getTemplate('core', $_version, 'eqLogic','teleinfo'));
		//return template_replace($replace, getTemplate('core', $_version, 'eqLogic','teleinfo'));
    }
    /*     * *********************Methode d'instance************************* */

    /*public function forceUpdate() {
        foreach ($this->getCmd() as $cmd) {
            try {
                $cmd->forceUpdate();
            } catch (Exception $e) {
                
            }
        }
        try {
            //self::callTeleinfo('/teleinfo');
        } catch (Exception $e) {
            
        }
    }*/
	
	
	
	/********** MANAGEMENT ZONE ********/
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'teleinfo_update';
		$return['progress_file'] = '/tmp/teleinfo_in_progress';
		$return['state'] = (self::installationOk()) ? 'ok' : 'nok';

		return $return;
	}
	
	public static function installationOk() {	
		try {
			$dependances_version = config::byKey('dependancy_version', 'teleinfo', 0);
			if(intval($dependances_version) >= 1.0){
				return true;
			}
			else{
				config::save('dependancy_version', 1.0, 'teleinfo');
				return false;
			}
		} catch (Exception $e) {
            return true;
        }
	}
	
	public static function dependancy_install() {
		if (file_exists('/tmp/teleinfo_in_progress')) {
			return;
		}
		log::remove('teleinfo_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('teleinfo_update') . ' 2>&1 &';
		exec($cmd);
	}

}

class teleinfoCmd extends cmd {

    public function execute($_options = null) {
        
    }
	
    /*     * **********************Getteur Setteur*************************** */
}
