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
    }

    public static function changeLogLive($level) {
        $value = array('apikey' => jeedom::getApiKey('teleinfo'), 'cmd' => $level);
        $value = json_encode($value);
        self::socket_connection($value,True);
    }

    public static function socket_connection($value)
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);

            socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'teleinfo','55062'));
            socket_write($socket, $value, strlen($value));
            socket_close($socket);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

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

    public static function createCmdFromDef($oADCO, $oKey, $oValue)
    {
        if (!isset($oKey)) {
            log::add('teleinfo', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($oKey, true));
            return false;
        }
        if (!isset($oADCO)) {
            log::add('teleinfo', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($oADCO, true));
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
            $cmd->setEqLogic_id($teleinfo->id);
            $cmd->setConfiguration('info_conso', $oKey);
            switch ($oKey) {
                //case "PAPP":
                case "OPTARIF":
                case "HHPHC":
                case "PPOT":
                case "PEJP":
                case "DEMAIN":
                case "PTEC":
                    $cmd->setSubType('string')
                            ->setDisplay('generic_type', 'GENERIC_INFO');
                    break;
                default:
                    $cmd->setSubType('numeric')
                            ->setDisplay('generic_type', 'GENERIC_INFO');
                    break;
            }
            $cmd->setIsHistorized(1)
                    ->setIsVisible(1);
            $cmd->save();
            $cmd->event($oValue);
            return $cmd;
        }
    }

    /**
     *
     * @param type $debug
     * @param type $type
     * @return boolean
     */
    public static function runDeamon($debug = false, $type = 'conso')
    {
        log::add('teleinfo', 'info', 'Démarrage compteur de consommation');
        $teleinfoPath         = realpath(dirname(__FILE__) . '/../../ressources');
        $modemSerieAddr       = config::byKey('port', 'teleinfo');
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
        $linky                = config::byKey('linky', 'teleinfo');
        $modemVitesse         = config::byKey('modem_vitesse', 'teleinfo');
        $cycleSommeil         = config::byKey('cycle_sommeil', 'teleinfo', '0.5');
        if ($modemSerieAddr == "serie") {
            $port = config::byKey('modem_serie_addr', 'teleinfo');
        } else {
            $port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
            if ($twoCptCartelectronic == 1) {
                $port = '/dev/ttyUSB1';
            } else {
                if (!file_exists($port)) {
                    log::add('teleinfo', 'error', 'Le port n\'existe pas');
                    return false;
                }
                $cle_api = config::byKey('api');
                if ($cle_api == '') {
                    log::add('teleinfo', 'error', 'Erreur de clé api, veuillez la vérifier.');
                    return false;
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

		exec('sudo chmod 777 ' . $port . ' > /dev/null 2>&1'); // TODO : Vérifier dans futur release si tjs nécessaire

        log::add('teleinfo', 'info', '--------- Informations sur le master --------');
        log::add('teleinfo', 'info', 'Port modem : ' . $port);
		log::add('teleinfo', 'info', 'Socket : ' . config::byKey('socketport', 'teleinfo', '55062'));
        log::add('teleinfo', 'info', 'Type : ' . $type);
        log::add('teleinfo', 'info', 'Mode : ' . $mode);
        log::add('teleinfo', 'info', '---------------------------------------------');

        if ($twoCptCartelectronic == 1) {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteur');
            $cmd          = 'sudo nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo_2_cpt.py';
			$cmd         .= ' --port ' . $port;
            $cmd         .= ' --vitesse ' . $modemVitesse;
            $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
            $cmd         .= ' --mode ' . $mode;
            $cmd         .= ' --socketport ' . config::byKey('socketport', 'teleinfo', '55062');
            $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
            $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
            $cmd         .= ' --loglevel info'; // . log::convertLogLevel(log::getLogLevel('teleinfo'));
            $cmd         .= ' --cyclesommeil ' . $cycleSommeil;
        } else {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
            $cmd          = 'nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo.py';
            $cmd         .= ' --port ' . $port;
            $cmd         .= ' --vitesse ' . $modemVitesse;
            $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
            $cmd         .= ' --type ' . $type;
            $cmd         .= ' --mode ' . $mode;
            $cmd         .= ' --socketport ' . config::byKey('socketport', 'teleinfo', '55062');
            $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
            $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
            $cmd         .= ' --loglevel info'; // . log::convertLogLevel(log::getLogLevel('teleinfo'));
            $cmd         .= ' --cyclesommeil ' . $cycleSommeil;
        }

        log::add('teleinfo', 'info', 'Exécution du service : ' . $cmd);
        $result = exec($cmd . ' >> ' . log::getPathToLog('teleinfo_deamon') . ' 2>&1 &');
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
    }

    /**
     *
     * @param type $_debug
     * @param type $type
     * @return boolean
     */
    public static function runProductionDeamon($debug = false, $type = 'prod')
    {
        log::add('teleinfo', 'info', '[Production] Mode local');
        $teleinfoPath         = realpath(dirname(__FILE__) . '/../../ressources');
        $modemSerieAddr       = config::byKey('port_production', 'teleinfo');
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic_production', 'teleinfo');
        $linky                = config::byKey('linky_prod', 'teleinfo');
        $modemVitesse         = config::byKey('modem_vitesse_production', 'teleinfo');
		$cycleSommeil         = config::byKey('cycle_sommeil', 'teleinfo', '0.5');
        if ($modemSerieAddr == "serie") {
            $port = config::byKey('modem_serie_production_addr', 'teleinfo');
        } else {
            $port = jeedom::getUsbMapping(config::byKey('port_production', 'teleinfo'));
            if ($twoCptCartelectronic == 1) {
                $port = '/dev/ttyUSB1';
            } else {
                if (!file_exists($port)) {
                    log::add('teleinfo', 'error', '[Production] Le port n\'existe pas');
                    return false;
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

        log::add('teleinfo', 'info', '--------- Informations sur le master --------');
        log::add('teleinfo', 'info', 'Port modem : ' . $port);
		log::add('teleinfo', 'info', 'Socket : ' . config::byKey('socketport', 'teleinfo', '55062') + 1);
        log::add('teleinfo', 'info', 'Type : ' . $type);
        log::add('teleinfo', 'info', 'Mode : ' . $mode);
        log::add('teleinfo', 'info', '---------------------------------------------');

        if ($twoCptCartelectronic == 1) {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteur');
            $cmd          = 'sudo nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo_2_cpt.py';
            $cmd         .= ' --port ' . $port;
            $cmd         .= ' --vitesse ' . $modemVitesse;
            $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
            $cmd         .= ' --mode ' . $mode;
            $cmd         .= ' --socketport ' . (config::byKey('socketport', 'teleinfo', '55062') + 1);
            $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
            $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
            $cmd         .= ' --loglevel info'; // . log::convertLogLevel(log::getLogLevel('teleinfo'));
            $cmd         .= ' --cyclesommeil ' . $cycleSommeil;
        } else {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
            $cmd          = 'nice -n 19 /usr/bin/python ' . $teleinfoPath . '/teleinfo.py';
            $cmd         .= ' --port ' . $port;
            $cmd         .= ' --vitesse ' . $modemVitesse;
            $cmd         .= ' --apikey ' . jeedom::getApiKey('teleinfo');
            $cmd         .= ' --type ' . $type;
            $cmd         .= ' --mode ' . $mode;
            $cmd         .= ' --socketport ' . (config::byKey('socketport', 'teleinfo', '55062') + 1);
            $cmd         .= ' --cycle ' . config::byKey('cycle', 'teleinfo','0.3');
            $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/teleinfo/core/php/jeeTeleinfo.php';
            $cmd         .= ' --loglevel info'; //. log::convertLogLevel(log::getLogLevel('teleinfo'));
            $cmd         .= ' --cyclesommeil ' . $cycleSommeil;
		}

        log::add('teleinfo', 'info', '[Production] Exécution du service : ' . $cmd);
        $result = exec($cmd . ' >> ' . log::getPathToLog('teleinfo_deamon') . ' 2>&1 &');
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
            log::add('teleinfo', 'error', $result);
            return false;
        }
        sleep(2);
        if (!self::deamonRunning()) {
            sleep(10);
            if (!self::deamonRunning()) {
                log::add('teleinfo', 'error', '[Production] Impossible de lancer le démon téléinfo, vérifiez l\'ip', 'unableStartDeamon');
                return false;
            }
        }
        message::removeAll('teleinfo', 'unableStartDeamon');
        log::add('teleinfo', 'info', '[Production] Service OK');
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
            log::add('teleinfo', 'info', 'Vérification de l\'état du service : NOK ');
            return false;
        } else {
            $result = exec("ps aux | grep teleinfo.py | grep -v grep | awk '{print $2}'");
            if ($result != "") {
                return true;
            }
            log::add('teleinfo', 'info', 'Vérification de l\'état du service : NOK ');
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
            $pidFile = '/tmp/jeedom/teleinfo/teleinfo2cpt.pid';
        } else {
            $pidFile = '/tmp/jeedom/teleinfo/teleinfo_conso.pid';
        }
        if (file_exists($pidFile)) {
            if (posix_getsid(trim(file_get_contents($pidFile)))) {
                $return['state'] = 'ok';
            } else {
                shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
            }
        }
        $productionActivated = config::byKey('activation_production', 'teleinfo');
        if ($productionActivated == 1) {
            $pidFile = '/tmp/jeedom/teleinfo/teleinfo_prod.pid';
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
        $productionActivated = config::byKey('activation_production', 'teleinfo');
        if (config::byKey('port', 'teleinfo') != "") {    // Si un port est sélectionné
            if (!self::deamonRunning()) {
                self::runDeamon($debug);
            }
            if ($productionActivated == 1) {
                self::runProductionDeamon($debug);
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
                $result = exec("ps aux | grep teleinfo_2_cpt.py | grep -v grep | awk '{print $2}'");
                system::kill($result);
            } else {
                $productionActivated = config::byKey('activation_production', 'teleinfo');
                if ($productionActivated == 1) {
                    $pidFile = '/tmp/jeedom/teleinfo/teleinfo_prod.pid';
                    if (file_exists($pidFile)) {
                        $pid  = intval(trim(file_get_contents($pidFile)));
                        $kill = posix_kill($pid, 15);
                        usleep(500);
                        if (!$kill) {
                            system::kill($pid);
                        }
                    }
                }
                $pidFile = '/tmp/jeedom/teleinfo/teleinfo_conso.pid';
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
        $statTodayHp     = 0;
        $statTodayHc     = 0;
        $statYesterdayHp = 0;
        $statYesterdayHc = 0;
        $typeTendance     = 0;
        $statHpToCumul  = array();
        $statHcToCumul  = array();

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "BASE":
                        case "HCHP":
                        case "BBRHPJB":
                        case "BBRHPJW":
                        case "BBRHPJR":
                        case "EJPHPM":
                        case "EASF02":
                            array_push($statHpToCumul, $cmd->getId());
                            break;
                    }
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "HCHC":
                        case "BBRHCJB":
                        case "BBRHCJW":
                        case "BBRHCJR":
                        case "EJPHN":
                        case "EASF01":
                            array_push($statHcToCumul, $cmd->getId());
                            break;
                    }
                }
                if ($cmd->getConfiguration('info_conso') == "TENDANCE_DAY") {
                    $typeTendance = $cmd->getConfiguration('type_calcul_tendance');
                }
            }
        }

        $startDateToday = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $endDateToday   = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        log::add('teleinfo', 'info', '----- Calcul des statistiques temps réel -----');
        log::add('teleinfo', 'info', 'Date de début : ' . $startDateToday);
        log::add('teleinfo', 'info', 'Date de fin   : ' . $endDateToday);
        log::add('teleinfo', 'info', '----------------------------------------------');

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

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

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
                    }
                }
            }
        }
    }

    public static function calculateOtherStats()
    {
        $statYesterdayHc = 0;
        $statYesterdayHp = 0;
        $statLastMonth    = 0;

        $statMonthLastYearHc = 0;
        $statMonthLastYearHp = 0;

        $statYearLastYearHc = 0;
        $statYearLastYearHp = 0;

        $statMonth   = 0;
        $statYear    = 0;
        $statJanHp  = 0;
        $statJanHc  = 0;
        $statFevHp  = 0;
        $statFevHc  = 0;
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
        $statDecHc  = 0;

        $statHpToCumul = array();
        $statHcToCumul = array();
        log::add('teleinfo', 'info', '----- Calcul des statistiques de la journée -----');
        foreach (eqLogic::byType('teleinfo') as $eqLogic) {
            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "data" || $cmd->getConfiguration('type') == "") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "BASE":
                        case "HCHP":
                        case "BBRHPJB":
                        case "BBRHPJW":
                        case "BBRHPJR":
                        case "EJPHPM":
                        case "EASF02":
                            array_push($statHpToCumul, $cmd->getId());
                            break;
                    }
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "HCHC":
                        case "BBRHCJB":
                        case "BBRHCJW":
                        case "BBRHCJR":
                        case "EJPHN":
                        case "EASF01":
                            array_push($statHcToCumul, $cmd->getId());
                            break;
                    }
                }
            }
        }

        $startdateyesterday = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $enddateyesterday   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));

        $startdateyear = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, date("Y")));
        $enddateyear   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));

        $startdatemonth = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $enddatemonth   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));

        $startdatelastmonth = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $enddatelastmonth   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m") - 1, date("t", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"))), date("Y")));

        $startdatemonthlastyear = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y") - 1));
        $enddatemonthlastyear   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d"), date("Y") - 1));

        $startdateyearlastyear = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, date("Y") - 1));
        $enddateyearlastyear   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y") - 1));

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

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

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
                        case "STAT_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique anuelle ==> ' . intval($statYear));
                            $cmd->event(intval($statYear));
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
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "BASE":
                            case "HCHP":
                            case "BBRHPJB":
                            case "BBRHPJW":
                            case "BBRHPJR":
                            case "EJPHPM":
                            case "EASF02":
                                $ppapHp += $cmd->execCmd();
                                log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
                                break;
                        }
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "HCHC":
                            case "BBRHCJB":
                            case "BBRHCJW":
                            case "BBRHCJR":
                            case "EJPHN":
                            case "EASF01":
                                $ppapHc += $cmd->execCmd();
                                log::add('teleinfo', 'debug', 'Cmd : ' . $cmd->getId() . ' / Value : ' . $cmd->execCmd());
                                break;
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
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "BASE":
                            case "HCHP":
                            case "BBRHPJB":
                            case "BBRHPJW":
                            case "BBRHPJR":
                            case "EJPHPM":
                            case "EASF02":
                                $ppapHp += $cmd->execCmd();
                                break;
                        }
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "HCHC":
                            case "BBRHCJB":
                            case "BBRHCJW":
                            case "BBRHCJR":
                            case "EJPHN":
                            case "EASF01":
                                $ppapHc += $cmd->execCmd();
                                break;
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
                    log::add('teleinfo', 'debug', $cmd->getConfiguration('info_conso') . '=> papp');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                        $cmd->setDisplay('icon', '<i class=\"fa fa-tachometer\"><\/i>');
                    }
					$cmd->setConfiguration('historizeMode', 'none');
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
        $array = array("STAT_JAN_HP", "STAT_JAN_HC", "STAT_FEV_HP", "STAT_FEV_HC", "STAT_MAR_HP", "STAT_MAR_HC", "STAT_AVR_HP", "STAT_AVR_HC", "STAT_MAI_HP", "STAT_MAI_HC", "STAT_JUIN_HP", "STAT_JUIN_HC", "STAT_JUI_HP", "STAT_JUI_HC", "STAT_AOU_HP", "STAT_AOU_HC", "STAT_SEP_HP", "STAT_SEP_HC", "STAT_OCT_HP", "STAT_OCT_HC", "STAT_NOV_HP", "STAT_NOV_HC", "STAT_DEC_HP", "STAT_DEC_HC", "STAT_MONTH_LAST_YEAR", "STAT_YEAR_LAST_YEAR", "STAT_TODAY", "STAT_MONTH", "STAT_YEAR");
        for ($ii = 0; $ii < 26; $ii++) {
            $cmd = $this->getCmd('info', $array[$ii]);
            if ($cmd === false) {
                $cmd = new teleinfoCmd();
                $cmd->setName($array[$ii]);
                $cmd->setEqLogic_id($this->id);
                $cmd->setLogicalId($array[$ii]);
                $cmd->setType('info');
                $cmd->setConfiguration('info_conso', $array[$ii]);
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
        $return['progress_file'] = '/tmp/teleinfo_in_progress';
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
