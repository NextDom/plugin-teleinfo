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

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

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

    public static function createCmdFromDef($_oADCO, $_oKey, $_oValue)
    {
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
            return false;
        }
        if ($teleinfo->getConfiguration('AutoCreateFromCompteur') == '1') {
            log::add('teleinfo', 'info', 'Création de la commande ' . $_oKey . ' sur l\'ADCO ' . $_oADCO);
            $cmd = (new teleinfoCmd())
                ->setName($_oKey)
                ->setLogicalId($_oKey)
                ->setType('info');
            $cmd->setEqLogic_id($teleinfo->id);
            $cmd->setConfiguration('info_conso', $_oKey);
            switch ($_oKey) {
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
            $cmd->event($_oValue);
            return $cmd;
        }
    }

    public static function runDeamon($_debug = false, $type = 'conso')
    {
        log::add('teleinfo', 'info', 'Démarrage compteur de consommation');
        $teleinfoPath         = realpath(dirname(__FILE__) . '/../../ressources');
        $modemSerieAddr       = config::byKey('port', 'teleinfo');
        $_debug               = config::byKey('debug', 'teleinfo');
        $_force               = config::byKey('force', 'teleinfo');
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
        $linky                = config::byKey('linky', 'teleinfo');
        $modemVitesse         = config::byKey('modem_vitesse', 'teleinfo');
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
        }
        else {
            $mode = 'historique';
            if ($modemVitesse == "") {
                $modemVitesse = '1200';
            }
        }

        $parsed_url = parse_url(config::byKey('internalProtocol', 'core', 'http://') . config::byKey('internalAddr', 'core', '127.0.0.1') . ":" . config::byKey('internalPort', 'core', '80') . config::byKey('internalComplement', 'core'));
        //exec('sudo chmod 777 ' . $port . ' > /dev/null 2>&1'); // TODO : Vérifier dans futur release si tjs nécessaire

        log::add('teleinfo', 'info', '--------- Informations sur le master --------');
        log::add('teleinfo', 'info', 'Adresse             :' . config::byKey('internalProtocol', 'core', 'http://') . config::byKey('internalAddr', 'core', '127.0.0.1') . ":" . config::byKey('internalPort', 'core', '80') . config::byKey('internalComplement', 'core'));
        log::add('teleinfo', 'info', 'Host / Port         :' . $parsed_url['host'] . ':' . $parsed_url['port']);
        log::add('teleinfo', 'info', 'Path complémentaire :' . $parsed_url['path']);
        $ip_interne = $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'] . $parsed_url['path'];
        log::add('teleinfo', 'info', 'Mise en forme pour le service : ' . $ip_interne);
        log::add('teleinfo', 'info', 'Debug : ' . $_debug);
        log::add('teleinfo', 'info', 'Force : ' . $_force);
        log::add('teleinfo', 'info', 'Port modem : ' . $port);
        log::add('teleinfo', 'info', 'Type : ' . $type);
        log::add('teleinfo', 'info', 'Mode : ' . $mode);
        $_debug = ($_debug) ? "1" : "0";
        $_force = ($_force) ? "1" : "0";
        log::add('teleinfo', 'info', '---------------------------------------------');

        if ($twoCptCartelectronic == 1) {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteur');
            $teleinfoPath = $teleinfoPath . '/teleinfo_2_cpt.py';
            $cmd           = 'sudo nice -n 19 /usr/bin/python ' . $teleinfoPath . ' -d ' . $_debug . ' -p ' . $port . ' -v ' . $modemVitesse . ' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
        } else {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
            $teleinfoPath = $teleinfoPath . '/teleinfo.py';
            $cmd           = 'nice -n 19 /usr/bin/python ' . $teleinfoPath . ' -d ' . $_debug . ' -p ' . $port . ' -v ' . $modemVitesse . ' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -t ' . $type . ' -m ' . $mode . ' -r ' . realpath(dirname(__FILE__));
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
    }

    public static function runProductionDeamon($_debug = false, $type = 'prod')
    {
        log::add('teleinfo', 'info', '[Production] Mode local');
        $teleinfoPath         = realpath(dirname(__FILE__) . '/../../ressources');
        $modemSerieAddr       = config::byKey('port_production', 'teleinfo');
        $_debug               = config::byKey('debug_production', 'teleinfo');
        $_force               = config::byKey('force_production', 'teleinfo');
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic_production', 'teleinfo');
        $linky                = config::byKey('linky_prod', 'teleinfo');
        $modemVitesse         = config::byKey('modem_vitesse_production', 'teleinfo');

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
                $cle_api = config::byKey('api');
                if ($cle_api == '') {
                    log::add('teleinfo', 'error', '[Production] Erreur de clé api, veuillez la vérifier.');
                    return false;
                }
            }
        }

        if ($linky == 1) {
            $mode = 'standard';
            if ($modemVitesse == "") {
                $modemVitesse = '9600';
            }
        }
        else {
            $mode = 'historique';
            if ($modemVitesse == "") {
                $modemVitesse = '1200';
            }
        }


        $parsed_url = parse_url(config::byKey('internalProtocol', 'core', 'http://') . config::byKey('internalAddr', 'core', '127.0.0.1') . ":" . config::byKey('internalPort', 'core', '80') . config::byKey('internalComplement', 'core'));

        log::add('teleinfo', 'info', '--------- Informations sur le master --------');
        log::add('teleinfo', 'info', 'Adresse             :' . config::byKey('internalProtocol', 'core', 'http://') . config::byKey('internalAddr', 'core', '127.0.0.1') . ":" . config::byKey('internalPort', 'core', '80') . config::byKey('internalComplement', 'core'));
        log::add('teleinfo', 'info', 'Host / Port         :' . $parsed_url['host'] . ':' . $parsed_url['port']);
        log::add('teleinfo', 'info', 'Path complémentaire :' . $parsed_url['path']);
        $ip_interne = $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'] . $parsed_url['path'];
        log::add('teleinfo', 'info', 'Mise en forme pour le service : ' . $ip_interne);
        log::add('teleinfo', 'info', 'Debug : ' . $_debug);
        log::add('teleinfo', 'info', 'Force : ' . $_force);
        log::add('teleinfo', 'info', 'Port modem : ' . $port);
        log::add('teleinfo', 'info', 'Type : ' . $type);
        log::add('teleinfo', 'info', 'Mode : ' . $mode);
        $_debug = ($_debug) ? "1" : "0";
        $_force = ($_force) ? "1" : "0";
        log::add('teleinfo', 'info', '---------------------------------------------');

        if ($twoCptCartelectronic == 1) {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 2 compteur');
            $teleinfoPath = $teleinfoPath . '/teleinfo_2_cpt.py';
            $cmd           = 'sudo nice -n 19 /usr/bin/python ' . $teleinfoPath . ' -d ' . $_debug . ' -p ' . $port . ' -v ' . $modemVitesse . ' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -r ' . realpath(dirname(__FILE__));
        } else {
            log::add('teleinfo', 'info', 'Fonctionnement en mode 1 compteur');
            $teleinfoPath = $teleinfoPath . '/teleinfo.py';
            $cmd           = 'nice -n 19 /usr/bin/python ' . $teleinfoPath . ' -d ' . $_debug . ' -p ' . $port . ' -v ' . $modemVitesse . ' -e ' . $ip_interne . ' -c ' . config::byKey('api') . ' -f ' . $_force . ' -t ' . $type . ' -m ' . $mode . ' -r ' . realpath(dirname(__FILE__));
        }

        log::add('teleinfo', 'info', '[Production] Exécution du service : ' . $cmd);
        $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('teleinfo') . ' 2>&1 &');
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

    public static function deamon_info()
    {
        $return          = array();
        $return['log']   = 'teleinfo';
        $return['state'] = 'nok';
        $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
        if ($twoCptCartelectronic == 1) {
            $pidFile     = '/tmp/teleinfo2cpt.pid';
        } else {
            $pidFile     = '/tmp/teleinfo_conso.pid';
        }
        if (file_exists($pidFile)) {
            if (posix_getsid(trim(file_get_contents($pidFile)))) {
                $return['state'] = 'ok';
            } else {
                shell_exec('sudo rm -rf ' . $pidFile . ' 2>&1 > /dev/null;rm -rf ' . $pidFile . ' 2>&1 > /dev/null;');
            }
        }
        $return['launchable'] = 'ok';
        return $return;
    }

    /**
     * appelé par jeedom pour démarrer le deamon
     */
    public static function deamon_start($_debug = false)
    {
        $productionActivated = config::byKey('activation_production', 'teleinfo');
        if (config::byKey('port', 'teleinfo') != "") {    // Si un port est sélectionné
            if (!self::deamonRunning()) {
                self::runDeamon($_debug);
            }
            if ($productionActivated == 1) {
                self::runProductionDeamon($_debug);
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
                    $pidFile = '/tmp/teleinfo_prod.pid';
                    if (file_exists($pidFile)) {
                        $pid  = intval(trim(file_get_contents($pidFile)));
                        $kill = posix_kill($pid, 15);
                        usleep(500);
                        if (!$kill) {
                            system::kill($pid);
                        }
                    }
                }
                $pidFile = '/tmp/teleinfo_conso.pid';
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
        $STAT_TODAY_HP     = 0;
        $STAT_TODAY_HC     = 0;
        $STAT_TENDANCE     = 0;
        $STAT_YESTERDAY_HP = 0;
        $STAT_YESTERDAY_HC = 0;
        $TYPE_TENDANCE     = 0;
        $stat_hp_to_cumul  = array();
        $stat_hc_to_cumul  = array();

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
                            array_push($stat_hp_to_cumul, $cmd->getId());
                            break;
                    }
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "HCHC":
                        case "BBRHCJB":
                        case "BBRHCJW":
                        case "BBRHCJR":
                        case "EJPHN":
                            array_push($stat_hc_to_cumul, $cmd->getId());
                            break;
                    }
                }
                if ($cmd->getConfiguration('info_conso') == "TENDANCE_DAY") {
                    $TYPE_TENDANCE = $cmd->getConfiguration('type_calcul_tendance');
                }
            }
        }

        $startdatetoday     = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $enddatetoday       = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        log::add('teleinfo', 'info', '----- Calcul des statistiques temps réel -----');
        log::add('teleinfo', 'info', 'Date de début : ' . $startdatetoday);
        log::add('teleinfo', 'info', 'Date de fin   : ' . $enddatetoday);
        log::add('teleinfo', 'info', '----------------------------------------------');

        $startdateyesterday = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        if ($TYPE_TENDANCE === 1) {
            $enddateyesterday = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
        } else {
            $enddateyesterday = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 1, date("Y")));
        }

        foreach ($stat_hc_to_cumul as $key => $value) {
            $cmd = cmd::byId($value);
            $statHcMaxToday = $cmd->getStatistique($startdatetoday, $enddatetoday)['max'];
            $statHcMinToday = $cmd->getStatistique($startdatetoday, $enddatetoday)['min'];
            log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
            log::add('teleinfo', 'debug', ' ==> Valeur HC MAX : ' . $statHcMaxToday);
            log::add('teleinfo', 'debug', ' ==> Valeur HC MIN : ' . $statHcMinToday);

            $STAT_TODAY_HC     += intval($statHcMaxToday) - intval($statHcMinToday);
            $STAT_YESTERDAY_HC += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
            log::add('teleinfo', 'debug', 'Total HC --> ' . $STAT_TODAY_HC);
        }
        foreach ($stat_hp_to_cumul as $key => $value) {
            $cmd = cmd::byId($value);
            $statHcMaxToday = $cmd->getStatistique($startdatetoday, $enddatetoday)['max'];
            $statHcMinToday = $cmd->getStatistique($startdatetoday, $enddatetoday)['min'];
            log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
            log::add('teleinfo', 'debug', ' ==> Valeur HP MAX : ' . $statHcMaxToday);
            log::add('teleinfo', 'debug', ' ==> Valeur HP MIN : ' . $statHcMinToday);

            $STAT_TODAY_HP     += intval($statHcMaxToday) - intval($statHcMinToday);
            $STAT_YESTERDAY_HP += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
            log::add('teleinfo', 'debug', 'Total HP --> ' . $STAT_TODAY_HP);
        }

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "stat") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "STAT_TODAY":
                        log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière ==> ' . intval($STAT_TODAY_HP + $STAT_TODAY_HC));
                        $cmd->event(intval($STAT_TODAY_HP + $STAT_TODAY_HC));
                        break;
                        case "TENDANCE_DAY":
                        log::add('teleinfo', 'debug', 'Mise à jour de la tendance journalière ==> ' . '(Hier : ' . intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) . ' Aujourd\'hui : ' . intval($STAT_TODAY_HC + $STAT_TODAY_HP) . ' Différence : ' . (intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) - intval($STAT_TODAY_HC + $STAT_TODAY_HP)) . ')');
                        $cmd->event(intval($STAT_YESTERDAY_HC + $STAT_YESTERDAY_HP) - intval($STAT_TODAY_HC + $STAT_TODAY_HP));
                        break;
                        case "STAT_TODAY_HP":
                        log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HP) ==> ' . intval($STAT_TODAY_HP));
                        $cmd->event(intval($STAT_TODAY_HP));
                        break;
                        case "STAT_TODAY_HC":
                        log::add('teleinfo', 'info', 'Mise à jour de la statistique journalière (HC) ==> ' . intval($STAT_TODAY_HC));
                        $cmd->event(intval($STAT_TODAY_HC));
                        break;
                    }
                }
            }
        }
    }

    public static function calculateOtherStats()
    {
        $STAT_YESTERDAY_HC = 0;
        $STAT_YESTERDAY_HP = 0;
        $STAT_LASTMONTH    = 0;

        $stat_month_last_year_hc = 0;
        $stat_month_last_year_hp = 0;

        $stat_year_last_year_hc = 0;
        $stat_year_last_year_hp = 0;

        $STAT_MONTH   = 0;
        $STAT_YEAR    = 0;
        $STAT_JAN_HP  = 0;
        $STAT_JAN_HC  = 0;
        $STAT_FEV_HP  = 0;
        $STAT_FEV_HC  = 0;
        $STAT_MAR_HP  = 0;
        $STAT_MAR_HC  = 0;
        $STAT_AVR_HP  = 0;
        $STAT_AVR_HC  = 0;
        $STAT_MAI_HP  = 0;
        $STAT_MAI_HC  = 0;
        $STAT_JUIN_HP = 0;
        $STAT_JUIN_HC = 0;
        $STAT_JUI_HP  = 0;
        $STAT_JUI_HC  = 0;
        $STAT_AOU_HP  = 0;
        $STAT_AOU_HC  = 0;
        $STAT_SEP_HP  = 0;
        $STAT_SEP_HC  = 0;
        $STAT_OCT_HP  = 0;
        $STAT_OCT_HC  = 0;
        $STAT_NOV_HP  = 0;
        $STAT_NOV_HC  = 0;
        $STAT_DEC_HP  = 0;
        $STAT_DEC_HC  = 0;

        $stat_hp_to_cumul = array();
        $stat_hc_to_cumul = array();
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
                            array_push($stat_hp_to_cumul, $cmd->getId());
                            break;
                    }
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "HCHC":
                        case "BBRHCJB":
                        case "BBRHCJW":
                        case "BBRHCJR":
                        case "EJPHN":
                            array_push($stat_hc_to_cumul, $cmd->getId());
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
        $enddatemonth   = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d"), date("Y")));

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

        foreach ($stat_hc_to_cumul as $key => $value) {
            log::add('teleinfo', 'debug', 'Commande HC N°' . $value);
            //$cache = cache::byKey('teleinfo::stats::' . $value, false, true);
            $cmd               = cmd::byId($value);
            //$STAT_TODAY_HC += intval($cmd->getStatistique($startdatetoday,$enddatetoday)[max]) - intval($cmd->getStatistique($startdatetoday,$enddatetoday)[min]);
            $STAT_YESTERDAY_HC += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
            $STAT_MONTH        += intval($cmd->getStatistique($startdatemonth, $enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth, $enddatemonth)['min']);
            $STAT_YEAR         += intval($cmd->getStatistique($startdateyear, $enddateyear)['max']) - intval($cmd->getStatistique($startdateyear, $enddateyear)['min']);
            $STAT_LASTMONTH    += intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['min']);

            $stat_month_last_year_hp += intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['max']) - intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['min']);
            $stat_year_last_year_hp  += intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['max']) - intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['min']);

            $STAT_JAN_HC  += intval($cmd->getStatistique($startdate_jan, $enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan, $enddate_jan)['min']);
            $STAT_FEV_HC  += intval($cmd->getStatistique($startdate_fev, $enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev, $enddate_fev)['min']);
            $STAT_MAR_HC  += intval($cmd->getStatistique($startdate_mar, $enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar, $enddate_mar)['min']);
            $STAT_AVR_HC  += intval($cmd->getStatistique($startdate_avr, $enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr, $enddate_avr)['min']);
            $STAT_MAI_HC  += intval($cmd->getStatistique($startdate_mai, $enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai, $enddate_mai)['min']);
            $STAT_JUIN_HC += intval($cmd->getStatistique($startdate_juin, $enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin, $enddate_juin)['min']);
            $STAT_JUI_HC  += intval($cmd->getStatistique($startdate_jui, $enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui, $enddate_jui)['min']);
            $STAT_AOU_HC  += intval($cmd->getStatistique($startdate_aou, $enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou, $enddate_aou)['min']);
            $STAT_SEP_HC  += intval($cmd->getStatistique($startdate_sep, $enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep, $enddate_sep)['min']);
            $STAT_OCT_HC  += intval($cmd->getStatistique($startdate_oct, $enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct, $enddate_oct)['min']);
            $STAT_NOV_HC  += intval($cmd->getStatistique($startdate_nov, $enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov, $enddate_nov)['min']);
            $STAT_DEC_HC  += intval($cmd->getStatistique($startdate_dec, $enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec, $enddate_dec)['min']);
        }
        foreach ($stat_hp_to_cumul as $key => $value) {
            log::add('teleinfo', 'debug', 'Commande HP N°' . $value);
            $cmd               = cmd::byId($value);
            $STAT_YESTERDAY_HP += intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['max']) - intval($cmd->getStatistique($startdateyesterday, $enddateyesterday)['min']);
            $STAT_MONTH        += intval($cmd->getStatistique($startdatemonth, $enddatemonth)['max']) - intval($cmd->getStatistique($startdatemonth, $enddatemonth)['min']);
            $STAT_YEAR         += intval($cmd->getStatistique($startdateyear, $enddateyear)['max']) - intval($cmd->getStatistique($startdateyear, $enddateyear)['min']);
            $STAT_LASTMONTH    += intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['max']) - intval($cmd->getStatistique($startdatelastmonth, $enddatelastmonth)['min']);

            $stat_month_last_year_hc += intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['max']) - intval($cmd->getStatistique($startdatemonthlastyear, $enddatemonthlastyear)['min']);
            $stat_year_last_year_hp  += intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['max']) - intval($cmd->getStatistique($startdateyearlastyear, $enddateyearlastyear)['min']);

            $STAT_JAN_HP  += intval($cmd->getStatistique($startdate_jan, $enddate_jan)['max']) - intval($cmd->getStatistique($startdate_jan, $enddate_jan)['min']);
            $STAT_FEV_HP  += intval($cmd->getStatistique($startdate_fev, $enddate_fev)['max']) - intval($cmd->getStatistique($startdate_fev, $enddate_fev)['min']);
            $STAT_MAR_HP  += intval($cmd->getStatistique($startdate_mar, $enddate_mar)['max']) - intval($cmd->getStatistique($startdate_mar, $enddate_mar)['min']);
            $STAT_AVR_HP  += intval($cmd->getStatistique($startdate_avr, $enddate_avr)['max']) - intval($cmd->getStatistique($startdate_avr, $enddate_avr)['min']);
            $STAT_MAI_HP  += intval($cmd->getStatistique($startdate_mai, $enddate_mai)['max']) - intval($cmd->getStatistique($startdate_mai, $enddate_mai)['min']);
            $STAT_JUIN_HP += intval($cmd->getStatistique($startdate_juin, $enddate_juin)['max']) - intval($cmd->getStatistique($startdate_juin, $enddate_juin)['min']);
            $STAT_JUI_HP  += intval($cmd->getStatistique($startdate_jui, $enddate_jui)['max']) - intval($cmd->getStatistique($startdate_jui, $enddate_jui)['min']);
            $STAT_AOU_HP  += intval($cmd->getStatistique($startdate_aou, $enddate_aou)['max']) - intval($cmd->getStatistique($startdate_aou, $enddate_aou)['min']);
            $STAT_SEP_HP  += intval($cmd->getStatistique($startdate_sep, $enddate_sep)['max']) - intval($cmd->getStatistique($startdate_sep, $enddate_sep)['min']);
            $STAT_OCT_HP  += intval($cmd->getStatistique($startdate_oct, $enddate_oct)['max']) - intval($cmd->getStatistique($startdate_oct, $enddate_oct)['min']);
            $STAT_NOV_HP  += intval($cmd->getStatistique($startdate_nov, $enddate_nov)['max']) - intval($cmd->getStatistique($startdate_nov, $enddate_nov)['min']);
            $STAT_DEC_HP  += intval($cmd->getStatistique($startdate_dec, $enddate_dec)['max']) - intval($cmd->getStatistique($startdate_dec, $enddate_dec)['min']);
            //log::add('teleinfo', 'info', 'Conso HP --> ' . $STAT_TODAY_HP);
        }

        foreach (eqLogic::byType('teleinfo') as $eqLogic) {

            foreach ($eqLogic->getCmd('info') as $cmd) {
                if ($cmd->getConfiguration('type') == "stat" || $cmd->getConfiguration('type') == "panel") {
                    switch ($cmd->getConfiguration('info_conso')) {
                        case "STAT_YESTERDAY":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier ==> ' . intval($STAT_YESTERDAY_HC) + intval($STAT_YESTERDAY_HP));
                            $cmd->event(intval($STAT_YESTERDAY_HC) + intval($STAT_YESTERDAY_HP));
                            break;
                        case "STAT_YESTERDAY_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HP) ==> ' . intval($STAT_YESTERDAY_HP));
                            $cmd->event(intval($STAT_YESTERDAY_HP));
                            break;
                        case "STAT_YESTERDAY_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique hier (HC) ==> ' . intval($STAT_YESTERDAY_HC));
                            $cmd->event(intval($STAT_YESTERDAY_HC));
                            break;
                        case "STAT_MONTH_LAST_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois an -1 ==> ' . intval($stat_month_last_year_hc) + intval($stat_month_last_year_hp));
                            $cmd->event(intval($stat_month_last_year_hc) + intval($stat_month_last_year_hp));
                            break;
                        case "STAT_YEAR_LAST_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique an-1 ==> ' . intval($stat_year_last_year_hc) + intval($stat_year_last_year_hp));
                            $cmd->event(intval($stat_year_last_year_hc) + intval($stat_year_last_year_hp));
                            break;
                        case "STAT_LASTMONTH":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois dernier ==> ' . intval($STAT_LASTMONTH));
                            $cmd->event(intval($STAT_LASTMONTH));
                            break;
                        case "STAT_MONTH":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mois en cours ==> ' . intval($STAT_MONTH));
                            $cmd->event(intval($STAT_MONTH));
                            break;
                        case "STAT_YEAR":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique anuelle ==> ' . intval($STAT_YEAR));
                            $cmd->event(intval($STAT_YEAR));
                            break;
                        case "STAT_JAN_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HP) ==> ' . intval($STAT_JAN_HP));
                            $cmd->event(intval($STAT_JAN_HP));
                            break;
                        case "STAT_JAN_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique janvier (HC) ==> ' . intval($STAT_JAN_HC));
                            $cmd->event(intval($STAT_JAN_HC));
                            break;
                        case "STAT_FEV_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HP) ==> ' . intval($STAT_FEV_HP));
                            $cmd->event(intval($STAT_FEV_HP));
                            break;
                        case "STAT_FEV_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique février (HC) ==> ' . intval($STAT_FEV_HC));
                            $cmd->event(intval($STAT_FEV_HC));
                            break;
                        case "STAT_MAR_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HP) ==> ' . intval($STAT_MAR_HP));
                            $cmd->event(intval($STAT_MAR_HP));
                            break;
                        case "STAT_MAR_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mars (HC) ==> ' . intval($STAT_MAR_HC));
                            $cmd->event(intval($STAT_MAR_HC));
                            break;
                        case "STAT_AVR_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HP) ==> ' . intval($STAT_AVR_HP));
                            $cmd->event(intval($STAT_AVR_HP));
                            break;
                        case "STAT_AVR_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique avril (HC) ==> ' . intval($STAT_AVR_HC));
                            $cmd->event(intval($STAT_AVR_HC));
                            break;
                        case "STAT_MAI_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HP) ==> ' . intval($STAT_MAI_HP));
                            $cmd->event(intval($STAT_MAI_HP));
                            break;
                        case "STAT_MAI_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique mai (HC) ==> ' . intval($STAT_MAI_HC));
                            $cmd->event(intval($STAT_MAI_HC));
                            break;
                        case "STAT_JUIN_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HP) ==> ' . intval($STAT_JUIN_HP));
                            $cmd->event(intval($STAT_JUIN_HP));
                            break;
                        case "STAT_JUIN_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juin (HC) ==> ' . intval($STAT_JUIN_HC));
                            $cmd->event(intval($STAT_JUIN_HC));
                            break;
                        case "STAT_JUI_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HP) ==> ' . intval($STAT_JUI_HP));
                            $cmd->event(intval($STAT_JUI_HP));
                            break;
                        case "STAT_JUI_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique juillet (HC) ==> ' . intval($STAT_JUI_HC));
                            $cmd->event(intval($STAT_JUI_HC));
                            break;
                        case "STAT_AOU_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HP) ==> ' . intval($STAT_AOU_HP));
                            $cmd->event(intval($STAT_AOU_HP));
                            break;
                        case "STAT_AOU_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique août (HC) ==> ' . intval($STAT_AOU_HC));
                            $cmd->event(intval($STAT_AOU_HC));
                            break;
                        case "STAT_SEP_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HP) ==> ' . intval($STAT_SEP_HP));
                            $cmd->event(intval($STAT_SEP_HP));
                            break;
                        case "STAT_SEP_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique septembre (HC) ==> ' . intval($STAT_SEP_HC));
                            $cmd->event(intval($STAT_SEP_HC));
                            break;
                        case "STAT_OCT_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HP) ==> ' . intval($STAT_OCT_HP));
                            $cmd->event(intval($STAT_OCT_HP));
                            break;
                        case "STAT_OCT_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique octobre (HC) ==> ' . intval($STAT_OCT_HC));
                            $cmd->event(intval($STAT_OCT_HC));
                            break;
                        case "STAT_NOV_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HP) ==> ' . intval($STAT_NOV_HP));
                            $cmd->event(intval($STAT_NOV_HP));
                            break;
                        case "STAT_NOV_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique novembre (HC) ==> ' . intval($STAT_NOV_HC));
                            $cmd->event(intval($STAT_NOV_HC));
                            break;
                        case "STAT_DEC_HP":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HP) ==> ' . intval($STAT_DEC_HP));
                            $cmd->event(intval($STAT_DEC_HP));
                            break;
                        case "STAT_DEC_HC":
                            log::add('teleinfo', 'debug', 'Mise à jour de la statistique décembre (HC) ==> ' . intval($STAT_DEC_HC));
                            $cmd->event(intval($STAT_DEC_HC));
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
            } else {
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
                                $ppapHp += $cmd->execCmd();
                                break;
                        }
                        switch ($cmd->getConfiguration('info_conso')) {
                            case "HCHC":
                            case "BBRHCJB":
                            case "BBRHCJW":
                            case "BBRHCJR":
                            case "EJPHN":
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
                    log::add('teleinfo', 'debug', '=> index');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
                    $cmd->save();
                    $cmd->refresh();
                    break;
                case "PAPP":
                    log::add('teleinfo', 'debug', '=> papp');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                        $cmd->setDisplay('icon', '<i class=\"fa fa-tachometer\"><\/i>');
                    }
                    $cmd->save();
                    $cmd->refresh();
                    break;
                case "PTEC":
                    log::add('teleinfo', 'debug', '=> ptec');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
                    $cmd->save();
                    $cmd->refresh();
                    break;
                default :
                    log::add('teleinfo', 'debug', '=> default');
                    if ($cmd->getDisplay('generic_type') == '') {
                        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                    }
                    break;
            }
        }
        after_template:
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
        $array = array("STAT_JAN_HP", "STAT_JAN_HC", "STAT_FEV_HP", "STAT_FEV_HC", "STAT_MAR_HP", "STAT_MAR_HC", "STAT_AVR_HP", "STAT_AVR_HC", "STAT_MAI_HP", "STAT_MAI_HC", "STAT_JUIN_HP", "STAT_JUIN_HC", "STAT_JUI_HP", "STAT_JUI_HC", "STAT_AOU_HP", "STAT_AOU_HC", "STAT_SEP_HP", "STAT_SEP_HC", "STAT_OCT_HP", "STAT_OCT_HC", "STAT_NOV_HP", "STAT_NOV_HC", "STAT_DEC_HP", "STAT_DEC_HC", "STAT_MONTH_LAST_YEAR", "STAT_YEAR_LAST_YEAR");
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
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setUnite('Wh');
                $cmd->setIsHistorized(0);
                $cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
            } else {
                $cmd->setDisplay('generic_type', 'DONT');
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
        return array('script' => dirname(__FILE__) . '/../../ressources/install_#stype#.sh ' . jeedom::getTmpFolder('teleinfo') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

}

class teleinfoCmd extends cmd
{
    public function execute($_options = null)
    {
    }
}
