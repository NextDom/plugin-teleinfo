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
        $return = self::deamon_info();
        if ($return['state'] != 'ok') {
            return "";
        }
    }

    public static function cron()
    {
        self::calculatePAPP();
    }

    public static function cronHourly()
    {
        self::moyLastHour();
        cache::set('teleinfo::regenerateMonthlyStat', '0');
    }

    public static function changeLogLive($level)
    {
        $value = array('apikey' => jeedom::getApiKey('teleinfo'), 'cmd' => $level);
        $value = json_encode($value);
        self::socket_connection($value,True);
    }

    public static function socket_connection($value)
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            socket_connect($socket, config::byKey('sockethost', 'teleinfo', '127.0.0.1'), config::byKey('socketport', 'teleinfo', '55062'));
            socket_write($socket, $value, strlen($value));
            socket_close($socket);
            $productionActivated = (config::byKey('port_modem2', 'teleinfo') == "") ? 0 : 1;
            //$productionActivated = config::byKey('activation_production', 'teleinfo');
            if ($productionActivated == 1) {
                $socket = socket_create(AF_INET, SOCK_STREAM, 0);
                socket_connect($socket, config::byKey('sockethost', 'teleinfo', '127.0.0.1'), config::byKey('socketport', 'teleinfo','55062') + 1);
                socket_write($socket, $value, strlen($value));
                socket_close($socket);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

	/**
	 * Creation objet sur reception de trame
	 * @param string $adco
	 * @return eqLogic
	 */
    public static function createFromDef(string $adco)
    {
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
                    ->setIsVisible(1);
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
            log::add('teleinfo', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($oKey, true) . ' ' . print_r($oADCO, true));
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
                case "OPTARIF":
                case "PTEC":
                case "DEMAIN":
                case "MOTDETAT":
                case "HHPHC":
                case "PPOT":
                case "NGTF":
                case "LTARF":
                case "STGE":
                case "DPM1":
                case "FPM1":
                case "DPM2":
                case "FPM2":
                case "DPM3":
                case "FPM3":
                case "MSG1":
                case "MSG2":
                case "PRM":
                case "RELAIS":
                case "NJOURF":
                case "NJOURF+1":
                case "PJOURF+1":
                case "PPOINTE":
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

		exec('stty -F ' . $port . ' 1200 sane evenp parenb cs7 -crtscts');
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
		return $return;
	}

	/**
     *
     * @param type $debug
     * @param type $type
     * @return boolean
     */
    public static function runDeamon($debug = false, $type = 'conso')
    {
        log::add('teleinfo', 'info', '[' . $type . '] Démarrage compteur ');
        $teleinfoPath         	  = realpath(dirname(__FILE__) . '/../../ressources');

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
					if (!file_exists($port)) {
						log::add('teleinfo', 'error', '[' . $type . '] Le port n\'existe pas');
						return false;
					}
				}
			}
		}
		else{
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
					if (!file_exists($port)) {
						log::add('teleinfo', 'error', '[' . $type . '] Le port n\'existe pas');
						return false;
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
            $cmd          = 'sudo nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo_2_cpt.py';
        }
		else {
            log::add('teleinfo', 'info', '[' . $type . '] Fonctionnement en mode 1 compteur');
            $cmd          = 'nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo.py';
            $cmd         .= ' --type ' . $type;
        }
		$cmd         .= ' --port ' . $port;
        $cmd         .= ' --vitesse ' . $modemVitesse;
        $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
        $cmd         .= ' --mode ' . $mode;
        $cmd         .= ' --socketport ' . $socketPort;
        $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
        $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
        $cmd         .= ' --loglevel debug';
        $cmd         .= ' --cyclesommeil ' . config::byKey('cycle_sommeil', 'teleinfo', '0.5');

        log::add('teleinfo', 'info', '[' . $type . '] Exécution du service : ' . $cmd);
        $result = exec($cmd . ' >> ' . log::getPathToLog('teleinfo_deamon_' . $type) . ' 2>&1 &');
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
            log::add('teleinfo', 'error', $result);
            return false;
        }
        sleep(2);
        if (!self::deamonRunning()) {
            sleep(10);
            if (!self::deamonRunning()) {
                log::add('teleinfo', 'error', '[' . $type . '] Impossible de lancer le démon téléinfo, vérifiez la configuration.', 'unableStartDeamon');
                return false;
            }
        }
        message::removeAll('teleinfo', 'unableStartDeamon');
        log::add('teleinfo', 'info', '[' . $type . '] Service OK');
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

    /**
     *
     * @return array
     */
    public static function deamon_info()
    {
        $return               = array();
        $return['log']        = 'teleinfo';
        $return['state']      = 'nok';
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
        if ($twoCptCartelectronic == 1) {
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo2cpt.pid';
        } else {
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_conso.pid';
        }
        if (file_exists($pidFile)) {
            if (posix_getsid(trim(file_get_contents($pidFile)))) {
                $return['state'] = 'ok';
            } else {
                shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
            }
        }
        $productionActivated = (config::byKey('port_modem2', 'teleinfo') == "") ? 0 : 1;
        //$productionActivated = config::byKey('activation_production', 'teleinfo');
        if ($productionActivated == 1) {
            $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_prod.pid';
            if (file_exists($pidFile)) {
                if (posix_getsid(trim(file_get_contents($pidFile)))) {
                    $return['state'] = 'ok';
                } else {
                    shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
                }
            }
        }
        $return['launchable'] = 'ok';
        return $return;
    }

    /**
     * appelé par jeedom pour démarrer le deamon
     */
    public static function deamon_start($debug = false)
    {
        log::add('teleinfo', 'info', '[deamon_start] Démarrage du service');
        $productionActivated = (config::byKey('port_modem2', 'teleinfo') == "") ? 0 : 1;
        //$productionActivated = config::byKey('activation_production', 'teleinfo');
        if (config::byKey('port', 'teleinfo') != "") {    // Si un port est sélectionné
            if (!self::deamonRunning()) {
                self::runDeamon($debug, 'conso');
            }
            if ($productionActivated == 1) {
                self::runDeamon($debug, 'prod');
            }
            message::removeAll('teleinfo', 'noTeleinfoPort');
        } else {
            log::add('teleinfo', 'info', 'Pas d\'informations sur le port USB (Modem série ?)');
        }
    }

    /**
     * appelé par jeedom pour arrêter le deamon
     */
    public static function deamon_stop()
    {
        log::add('teleinfo', 'info', '[deamon_stop] Arret du service');
        $deamonInfo = self::deamon_info();
        if ($deamonInfo['state'] == 'ok') {
            $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if ($twoCptCartelectronic == 1) {
                $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo2cpt.pid';
                if (file_exists($pidFile)) {
                    $pid  = intval(trim(file_get_contents($pidFile)));
                    $kill = posix_kill($pid, 15);
                    usleep(1000);
                    if ($kill) {
                        return true;
                    } else {
                        system::kill($pid);
                    }
                }
                //$result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
                //system::kill($result);
                system::kill('teleinfo_2_cpt.py');
            } else {
                $productionActivated = (config::byKey('port_modem2', 'teleinfo') == "") ? 0 : 1;
                //$productionActivated = config::byKey('activation_production', 'teleinfo');
                if ($productionActivated == 1) {
                    $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_prod.pid';
                    if (file_exists($pidFile)) {
                        $pid  = intval(trim(file_get_contents($pidFile)));
                        $kill = posix_kill($pid, 15);
                        usleep(500);
                        if (!$kill) {
                            system::kill($pid);
                        }
                    }
                }
                $pidFile = jeedom::getTmpFolder('teleinfo') . '/teleinfo_conso.pid';
                if (file_exists($pidFile)) {
                    $pid  = intval(trim(file_get_contents($pidFile)));
                    $kill = posix_kill($pid, 15);
                    usleep(500);
                    if ($kill) {
                        return true;
                    } else {
                        system::kill($pid);
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
    }

    public static function calculateTodayStats()
    {
        $indexConsoHP      = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC      = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        $indexProduction   = config::byKey('indexProduction', 'teleinfo', 'EAIT');

        log::add('teleinfo', 'info', '----- Calcul des statistiques temps réel -----');
        $startDateToday = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $endDateToday   = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        log::add('teleinfo', 'info', 'Date de début : ' . $startDateToday);
        log::add('teleinfo', 'info', 'Date de fin   : ' . $endDateToday);
        log::add('teleinfo', 'info', 'Liste index HP          : ' . $indexConsoHP);
        log::add('teleinfo', 'info', 'Liste index HC          : ' . $indexConsoHC);
        log::add('teleinfo', 'info', 'Liste index Production  : ' . $indexProduction);


        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

            log::add('teleinfo', 'info', 'Objet : ' . $eqLogic->getName());

            $statTodayHp       = 0;
            $statTodayHc       = 0;
            $statTodayProd     = 0;
            $statYesterdayHp   = 0;
            $statYesterdayHc   = 0;
            $typeTendance      = 0;
            $statHpToCumul     = array();
            $statHcToCumul     = array();
            $statProdToCumul   = array();

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHpToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHcToCumul, $cmd->getId());
                    }
                    if (strpos($indexProduction, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statProdToCumul, $cmd->getId());
                    }
                }
                if ($cmd->getConfiguration('info_conso') == "TENDANCE_DAY") {
                    $typeTendance = $cmd->getConfiguration('type_calcul_tendance');
                }
            }

            $startdateyesterday = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
            if ($typeTendance === 1) {
                $enddateyesterday = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
            } else {
                $enddateyesterday = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 1, date("Y")));
            }

            foreach ($statHcToCumul as $key => $value) {
                $cmd            = cmd::byId($value);
                $statHcMaxToday = $cmd->getStatistique($startDateToday, $endDateToday)['max'];
                $statHcMinToday = $cmd->getStatistique($startDateToday, $endDateToday)['min'];
                log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
                log::add('teleinfo', 'debug', ' ==> Valeur HC MAX : ' . $statHcMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur HC MIN : ' . $statHcMinToday);

                $statTodayHc     += intval($statHcMaxToday) - intval($statHcMinToday);
                $statYesterdayHc += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
                log::add('teleinfo', 'debug', 'Total HC --> ' . $statTodayHc);
            }
            foreach ($statHpToCumul as $key => $value) {
                $cmd            = cmd::byId($value);
                $statHcMaxToday = $cmd->getStatistique($startDateToday, $endDateToday)['max'];
                $statHcMinToday = $cmd->getStatistique($startDateToday, $endDateToday)['min'];
                log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
                log::add('teleinfo', 'debug', ' ==> Valeur HP MAX : ' . $statHcMaxToday);
                log::add('teleinfo', 'debug', ' ==> Valeur HP MIN : ' . $statHcMinToday);

                $statTodayHp     += intval($statHcMaxToday) - intval($statHcMinToday);
                $statYesterdayHp += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
                log::add('teleinfo', 'debug', 'Total HP --> ' . $statTodayHp);
            }

            foreach ($statProdToCumul as $key => $value) {
                $cmd              = cmd::byId($value);
                $statProdMaxToday = $cmd->getStatistique($startDateToday, $endDateToday)['max'];
                $statProdMinToday = $cmd->getStatistique($startDateToday, $endDateToday)['min'];
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
                            log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière ==> ' . intval($statTodayHp + $statTodayHc));
                            $cmd->event(intval($statTodayHp + $statTodayHc));
                            break;
                        case "TENDANCE_DAY":
                            log::add('teleinfo', 'debug', 'Mise à jour de la tendance journalière ==> ' . '(Hier : ' . intval($statYesterdayHc + $statYesterdayHp) . ' Aujourd\'hui : ' . intval($statTodayHc + $statTodayHp) . ' Différence : ' . (intval($statYesterdayHc + $statYesterdayHp) - intval($statTodayHc + $statTodayHp)) . ')');
                            $cmd->event(intval($statYesterdayHc + $statYesterdayHp) - intval($statTodayHc + $statTodayHp));
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
                    }
                }
            }
        }
        log::add('teleinfo', 'info', '----------------------------------------------');
    }

    public static function calculateOtherStats()
    {
        $indexConsoHP           = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC           = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        $indexProduction        = config::byKey('indexProduction', 'teleinfo', 'EAIT');
        $startdateyesterday     = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $enddateyesterday       = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
        $startdateyear          = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, date("Y")));
        $enddateyear            = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
        $startdatemonth         = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $enddatemonth           = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
        $startdatelastmonth     = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $enddatelastmonth       = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m") - 1, date("t", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"))), date("Y")));
        $startdatemonthlastyear = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y") - 1));
        $enddatemonthlastyear   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d"), date("Y") - 1));
        $startdateyearlastyear  = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, date("Y") - 1));
        $enddateyearlastyear    = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y") - 1));

        $startdate_jan  = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, date("Y")));
        $enddate_jan    = date("Y-m-d H:i:s", mktime(23, 59, 59, 1, 31, date("Y")));
        $startdate_fev  = date("Y-m-d H:i:s", mktime(0, 0, 0, 2, 1, date("Y")));
        $enddate_fev    = date("Y-m-d H:i:s", mktime(23, 59, 59, 2, 28, date("Y")));
        $startdate_mar  = date("Y-m-d H:i:s", mktime(0, 0, 0, 3, 1, date("Y")));
        $enddate_mar    = date("Y-m-d H:i:s", mktime(23, 59, 59, 3, 31, date("Y")));
        $startdate_avr  = date("Y-m-d H:i:s", mktime(0, 0, 0, 4, 1, date("Y")));
        $enddate_avr    = date("Y-m-d H:i:s", mktime(23, 59, 59, 4, 30, date("Y")));
        $startdate_mai  = date("Y-m-d H:i:s", mktime(0, 0, 0, 5, 1, date("Y")));
        $enddate_mai    = date("Y-m-d H:i:s", mktime(23, 59, 59, 5, 31, date("Y")));
        $startdate_juin = date("Y-m-d H:i:s", mktime(0, 0, 0, 6, 1, date("Y")));
        $enddate_juin   = date("Y-m-d H:i:s", mktime(23, 59, 59, 6, 30, date("Y")));
        $startdate_jui  = date("Y-m-d H:i:s", mktime(0, 0, 0, 7, 1, date("Y")));
        $enddate_jui    = date("Y-m-d H:i:s", mktime(23, 59, 59, 7, 31, date("Y")));
        $startdate_aou  = date("Y-m-d H:i:s", mktime(0, 0, 0, 8, 1, date("Y")));
        $enddate_aou    = date("Y-m-d H:i:s", mktime(23, 59, 59, 8, 31, date("Y")));
        $startdate_sep  = date("Y-m-d H:i:s", mktime(0, 0, 0, 9, 1, date("Y")));
        $enddate_sep    = date("Y-m-d H:i:s", mktime(23, 59, 59, 9, 30, date("Y")));
        $startdate_oct  = date("Y-m-d H:i:s", mktime(0, 0, 0, 10, 1, date("Y")));
        $enddate_oct    = date("Y-m-d H:i:s", mktime(23, 59, 59, 10, 31, date("Y")));
        $startdate_nov  = date("Y-m-d H:i:s", mktime(0, 0, 0, 11, 1, date("Y")));
        $enddate_nov    = date("Y-m-d H:i:s", mktime(23, 59, 59, 11, 30, date("Y")));
        $startdate_dec  = date("Y-m-d H:i:s", mktime(0, 0, 0, 12, 1, date("Y")));
        $enddate_dec    = date("Y-m-d H:i:s", mktime(23, 59, 59, 12, 31, date("Y")));

        log::add('teleinfo', 'info', '----- Calcul des statistiques de la journée -----');
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            $statYesterdayHc     = 0;
            $statYesterdayHp     = 0;
            $statYesterdayProd   = 0;
            $statLastMonth       = 0;
            $statMonthLastYearHc = 0;
            $statMonthLastYearHp = 0;
            $statYearLastYearHc  = 0;
            $statYearLastYearHp  = 0;
            $statMonth           = 0;
            $statMonthProd       = 0;
            $statYear            = 0;
            $statYearProd        = 0;
            $statJanHp           = 0;
            $statJanHc           = 0;
            $statFevHp           = 0;
            $statFevHc           = 0;
            $statMarHp  = 0;
            $statMarHc  = 0;
            $statAvrHp  = 0;
            $statAvrHc  = 0;
            $statMaiHp  = 0;
            $statMaiHc  = 0;
            $statJuinHp = 0;
            $statJuinHc = 0;
            $statJuiHp  = 0;
            $statJuiHc  = 0;
            $statAouHp  = 0;
            $statAouHc  = 0;
            $statSepHp  = 0;
            $statSepHc  = 0;
            $statOctHp  = 0;
            $statOctHc  = 0;
            $statNovHp  = 0;
            $statNovHc  = 0;
            $statDecHp  = 0;
            $statDecHc           = 0;
            $statHpToCumul       = array();
            $statHcToCumul       = array();
            $statProdToCumul     = array();
            log::add('teleinfo', 'info', 'Objet : ' . $eqLogic->getName());

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHpToCumul, $cmd->getId());
                    }
                    if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statHcToCumul, $cmd->getId());
                    }
                    if (strpos($indexProduction, $cmd->getConfiguration('info_conso')) !== false) {
                        array_push($statProdToCumul, $cmd->getId());
                    }
                }
            }

            foreach ($statHcToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
                $cmd               = cmd::byId($value);
                $statYesterdayHc	 += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
                $statMonth           += intval($cmd->getStatistique($startdatemonth, $enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth, $enddatemonth)['min']);
                $statYear            += intval($cmd->getStatistique($startdateyear, $enddateyear)['max']) - intval($cmd->getStatistique($startdateyear, $enddateyear)['min']);
                $statLastMonth       += intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['min']);
                $statMonthLastYearHp += intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['max']) - intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['min']);
                $statYearLastYearHp  += intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['max']) - intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['min']);
                $statJanHc  		 += intval($cmd->getStatistique($startdate_jan, $enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan, $enddate_jan)['min']);
                $statFevHc  		 += intval($cmd->getStatistique($startdate_fev, $enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev, $enddate_fev)['min']);
                $statMarHc  		 += intval($cmd->getStatistique($startdate_mar, $enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar, $enddate_mar)['min']);
                $statAvrHc  		 += intval($cmd->getStatistique($startdate_avr, $enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr, $enddate_avr)['min']);
                $statMaiHc  		 += intval($cmd->getStatistique($startdate_mai, $enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai, $enddate_mai)['min']);
                $statJuinHc 		 += intval($cmd->getStatistique($startdate_juin, $enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin, $enddate_juin)['min']);
                $statJuiHc  		 += intval($cmd->getStatistique($startdate_jui, $enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui, $enddate_jui)['min']);
                $statAouHc  		 += intval($cmd->getStatistique($startdate_aou, $enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou, $enddate_aou)['min']);
                $statSepHc  		 += intval($cmd->getStatistique($startdate_sep, $enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep, $enddate_sep)['min']);
                $statOctHc  		 += intval($cmd->getStatistique($startdate_oct, $enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct, $enddate_oct)['min']);
                $statNovHc  		 += intval($cmd->getStatistique($startdate_nov, $enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov, $enddate_nov)['min']);
                $statDecHc  		 += intval($cmd->getStatistique($startdate_dec, $enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec, $enddate_dec)['min']);
            }
            foreach ($statHpToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
                $cmd               = cmd::byId($value);
                $statYesterdayHp 	 += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
                $statMonth       	 += intval($cmd->getStatistique($startdatemonth, $enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth, $enddatemonth)['min']);
                $statYear        	 += intval($cmd->getStatistique($startdateyear, $enddateyear)['max']) - intval($cmd->getStatistique($startdateyear, $enddateyear)['min']);
                $statLastMonth   	 += intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['min']);
                $statMonthLastYearHc += intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['max']) - intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['min']);
                $statYearLastYearHp  += intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['max']) - intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['min']);
                $statJanHp 			 += intval($cmd->getStatistique($startdate_jan, $enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan, $enddate_jan)['min']);
                $statFevHp 			 += intval($cmd->getStatistique($startdate_fev, $enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev, $enddate_fev)['min']);
                $statMarHp 			 += intval($cmd->getStatistique($startdate_mar, $enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar, $enddate_mar)['min']);
                $statAvrHp 			 += intval($cmd->getStatistique($startdate_avr, $enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr, $enddate_avr)['min']);
                $statMaiHp 			 += intval($cmd->getStatistique($startdate_mai, $enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai, $enddate_mai)['min']);
                $statJuinHp			 += intval($cmd->getStatistique($startdate_juin, $enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin, $enddate_juin)['min']);
                $statJuiHp 			 += intval($cmd->getStatistique($startdate_jui, $enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui, $enddate_jui)['min']);
                $statAouHp 			 += intval($cmd->getStatistique($startdate_aou, $enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou, $enddate_aou)['min']);
                $statSepHp 			 += intval($cmd->getStatistique($startdate_sep, $enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep, $enddate_sep)['min']);
                $statOctHp 			 += intval($cmd->getStatistique($startdate_oct, $enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct, $enddate_oct)['min']);
                $statNovHp 			 += intval($cmd->getStatistique($startdate_nov, $enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov, $enddate_nov)['min']);
                $statDecHp 			 += intval($cmd->getStatistique($startdate_dec, $enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec, $enddate_dec)['min']);
            }

            foreach ($statProdToCumul as $key => $value) {
                log::add('teleinfo', 'debug', 'Commande Prod N°' . $value);
                $cmd                  = cmd::byId($value);
                $statYesterdayProd 	 += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
                $statMonthProd     	 += intval($cmd->getStatistique($startdatemonth, $enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth, $enddatemonth)['min']);
                $statYearProd     	 += intval($cmd->getStatistique($startdateyear, $enddateyear)['max']) - intval($cmd->getStatistique($startdateyear, $enddateyear)['min']);
            }


            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "stat" || $cmd->getConfiguration('type') == "panel") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "STAT_YESTERDAY":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier ==> ' . intval($statYesterdayHc) + intval($statYesterdayHp));
                            $cmd->event(intval($statYesterdayHc) + intval($statYesterdayHp));
                            break;
                        case "STAT_YESTERDAY_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HP) ==> ' . intval($statYesterdayHp));
                            $cmd->event(intval($statYesterdayHp));
                            break;
                        case "STAT_YESTERDAY_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HC) ==> ' . intval($statYesterdayHc));
                            $cmd->event(intval($statYesterdayHc));
                            break;
                        case "STAT_YESTERDAY_PROD":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (PROD) ==> ' . intval($statYesterdayProd));
                            $cmd->event(intval($statYesterdayProd));
                            break;
                        case "STAT_MONTH_LAST_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois an -1 ==> ' . intval($statMonthLastYearHc) + intval($statMonthLastYearHp));
                            $cmd->event(intval($statMonthLastYearHc) + intval($statMonthLastYearHp));
                            break;
                        case "STAT_YEAR_LAST_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique an-1 ==> ' . intval($statYearLastYearHc) + intval($statYearLastYearHp));
                            $cmd->event(intval($statYearLastYearHc) + intval($statYearLastYearHp));
                            break;
                        case "STAT_LASTMONTH":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois dernier ==> ' . intval($statLastMonth));
                            $cmd->event(intval($statLastMonth));
                            break;
                        case "STAT_MONTH":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois en cours ==> ' . intval($statMonth));
                            $cmd->event(intval($statMonth));
                            break;
                        case "STAT_MONTH_PROD":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mensuelle prod ==> ' . intval($statMonthProd));
                            $cmd->event(intval($statMonthProd));
                            break;
                        case "STAT_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique anuelle ==> ' . intval($statYear));
                            $cmd->event(intval($statYear));
                            break;
                        case "STAT_YEAR_PROD":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique anuelle prod ==> ' . intval($statYearProd));
                            $cmd->event(intval($statYearProd));
                            break;
                        case "STAT_JAN_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HP) ==> ' . intval($statJanHp));
                            $cmd->event(intval($statJanHp));
                            break;
                        case "STAT_JAN_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HC) ==> ' . intval($statJanHc));
                            $cmd->event(intval($statJanHc));
                            break;
                        case "STAT_FEV_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HP) ==> ' . intval($statFevHp));
                            $cmd->event(intval($statFevHp));
                            break;
                        case "STAT_FEV_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HC) ==> ' . intval($statFevHc));
                            $cmd->event(intval($statFevHc));
                            break;
                        case "STAT_MAR_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HP) ==> ' . intval($statMarHp));
                            $cmd->event(intval($statMarHp));
                            break;
                        case "STAT_MAR_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HC) ==> ' . intval($statMarHc));
                            $cmd->event(intval($statMarHc));
                            break;
                        case "STAT_AVR_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HP) ==> ' . intval($statAvrHp));
                            $cmd->event(intval($statAvrHp));
                            break;
                        case "STAT_AVR_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HC) ==> ' . intval($statAvrHc));
                            $cmd->event(intval($statAvrHc));
                            break;
                        case "STAT_MAI_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HP) ==> ' . intval($statMaiHp));
                            $cmd->event(intval($statMaiHp));
                            break;
                        case "STAT_MAI_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HC) ==> ' . intval($statMaiHc));
                            $cmd->event(intval($statMaiHc));
                            break;
                        case "STAT_JUIN_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HP) ==> ' . intval($statJuinHp));
                            $cmd->event(intval($statJuinHp));
                            break;
                        case "STAT_JUIN_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HC) ==> ' . intval($statJuinHc));
                            $cmd->event(intval($statJuinHc));
                            break;
                        case "STAT_JUI_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HP) ==> ' . intval($statJuiHp));
                            $cmd->event(intval($statJuiHp));
                            break;
                        case "STAT_JUI_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HC) ==> ' . intval($statJuiHc));
                            $cmd->event(intval($statJuiHc));
                            break;
                        case "STAT_AOU_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HP) ==> ' . intval($statAouHp));
                            $cmd->event(intval($statAouHp));
                            break;
                        case "STAT_AOU_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HC) ==> ' . intval($statAouHc));
                            $cmd->event(intval($statAouHc));
                            break;
                        case "STAT_SEP_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HP) ==> ' . intval($statSepHp));
                            $cmd->event(intval($statSepHp));
                            break;
                        case "STAT_SEP_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HC) ==> ' . intval($statSepHc));
                            $cmd->event(intval($statSepHc));
                            break;
                        case "STAT_OCT_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HP) ==> ' . intval($statOctHp));
                            $cmd->event(intval($statOctHp));
                            break;
                        case "STAT_OCT_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HC) ==> ' . intval($statOctHc));
                            $cmd->event(intval($statOctHc));
                            break;
                        case "STAT_NOV_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HP) ==> ' . intval($statNovHp));
                            $cmd->event(intval($statNovHp));
                            break;
                        case "STAT_NOV_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HC) ==> ' . intval($statNovHc));
                            $cmd->event(intval($statNovHc));
                            break;
                        case "STAT_DEC_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HP) ==> ' . intval($statDecHp));
                            $cmd->event(intval($statDecHp));
                            break;
                        case "STAT_DEC_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HC) ==> ' . intval($statDecHc));
                            $cmd->event(intval($statDecHc));
                            break;
                    }
                }
            }
        }
    }

    public static function moyLastHour()
    {
        $ppapHp  = 0;
        $ppapHc  = 0;
        $cmdPpap = null;
        $indexConsoHP = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');

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
        $ppapHp  = 0;
        $ppapHc  = 0;
        $cmdPpap = null;
        $indexConsoHP = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
        $indexConsoHC = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == 'stat') {
                    if ($cmd->getConfiguration('info_conso') == 'PPAP_MANUELLE') {
                        log::add('teleinfo', 'debug', '----- Calcul de la puissance apparante moyenne -----');
                        $cmdPpap = $cmd;
                    }
                }
            }
            if ($cmdPpap !== null) {
                log::add('teleinfo', 'debug', 'Cmd trouvée');
                foreach ($eqLogic->getCmd('info') as $cmd) {
                    if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                        if (strpos($indexConsoHP, $cmd->getConfiguration('info_conso')) !== false) {
                            $ppapHp += $cmd->execCmd();
                        }
                        if (strpos($indexConsoHC, $cmd->getConfiguration('info_conso')) !== false) {
                            $ppapHc += $cmd->execCmd();
                        }
                    }
                }

                $cacheHc        = cache::byKey('teleinfo::ppap_manuelle::hc', false);
                $datetimeMesure = date_create($cacheHc->getDatetime());
                $cacheHp        = cache::byKey('teleinfo::ppap_manuelle::hp', false);
                $cacheHc        = $cacheHc->getValue();
                $cacheHp        = $cacheHp->getValue();

                $datetimeMesure = $datetimeMesure->getTimestamp();
                $datetime2      = time();
                $interval       = $datetime2 - $datetimeMesure;
                $consoResultat  = ((($ppapHp - $cacheHp) + ($ppapHc - $cacheHc)) / $interval) * 3600;
                log::add('teleinfo', 'debug', 'Intervale depuis la dernière valeur : ' . $interval);
                log::add('teleinfo', 'debug', 'Conso calculée : ' . $consoResultat . ' Wh');
                $cmdPpap->event(intval($consoResultat));

                cache::set('teleinfo::ppap_manuelle::hc', $ppapHc, 150);
                cache::set('teleinfo::ppap_manuelle::hp', $ppapHp, 150);
            } else {
                log::add('teleinfo', 'debug', 'Pas de calcul');
            }
        }
    }

    public function preSave()
    {
        $this->setCategory('energy', 1);
        $cmd = $this->getCmd('info', 'HEALTH');
        if (is_object($cmd)) {
            $cmd->remove();
            $cmd->save();
        }
    }

    public function postSave()
    {
        log::add('teleinfo', 'debug', '-------- Sauvegarde de l\'objet --------');
        foreach ($this->getCmd(null, null, true) as $cmd) {
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
                case "EJPHPM":
                case "EASF01":
                case "EASF02":
                case "EASD01":
                case "EASD02":
                case "EAIT":
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
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> papp');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                        $cmd->setDisplay('icon', '<i class=\"fa fa-tachometer\"><\/i>');
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
    }

    public function preRemove()
    {
        log::add('teleinfo', 'debug', 'Suppression d\'un objet');
    }

    public function createOtherCmd()
    {
        $array = array("HEALTH");
        for ($ii = 0; $ii < 1; $ii++) {
            $cmd = $this->getCmd('info', $array[$ii]);
            if ($cmd === false) {
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

    public function createPanelStats()
    {
        log::add('teleinfo', 'debug', '-------- Commandes des stats --------');
        $array = array("STAT_JAN_HP", "STAT_JAN_HC", "STAT_FEV_HP", "STAT_FEV_HC", "STAT_MAR_HP", "STAT_MAR_HC", "STAT_AVR_HP", "STAT_AVR_HC", "STAT_MAI_HP", "STAT_MAI_HC", "STAT_JUIN_HP", "STAT_JUIN_HC", "STAT_JUI_HP", "STAT_JUI_HC", "STAT_AOU_HP", "STAT_AOU_HC", "STAT_SEP_HP", "STAT_SEP_HC", "STAT_OCT_HP", "STAT_OCT_HC", "STAT_NOV_HP", "STAT_NOV_HC", "STAT_DEC_HP", "STAT_DEC_HC", "STAT_MONTH_LAST_YEAR", "STAT_YEAR_LAST_YEAR");
        foreach ($array as $value){
            $cmd = $this->getCmd('info', $value);
            log::add('teleinfo', 'debug', '=> ' . $value);
            if ($cmd === false) {
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                $cmd->setEqLogic_id($this->id);
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'panel');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setUnite('Wh');
                $cmd->setIsHistorized(0);
                $cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
            } else {
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->save();
            }
        }
        $array = array("STAT_TODAY", "STAT_MONTH", "STAT_YEAR","STAT_TODAY_PROD","STAT_YESTERDAY_HC","STAT_YESTERDAY_HP","STAT_YESTERDAY_PROD","STAT_MONTH_PROD","STAT_YEAR_PROD");
        foreach ($array as $value){
            $cmd = $this->getCmd('info', $value);
            log::add('teleinfo', 'debug', '=> ' . $value);
            if ($cmd === false) {
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                $cmd->setEqLogic_id($this->id);
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'stat');
				$cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setUnite('Wh');
                $cmd->setIsHistorized(1);
                $cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
                $cmd->refresh();
            } else {
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
