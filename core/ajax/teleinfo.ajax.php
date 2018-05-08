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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new \Exception('401 Unauthorized');
    }

    switch (init('action')){
        case 'stopDeamon':
            teleinfo::deamon_stop();
            ajax::success();
        break;
        case 'restartDeamon':
            $port                 = config::byKey('port', 'teleinfo', 'none');
            $_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if ($port == 'none') {
                ajax::success();
            }
            teleinfo::deamon_stop();
            if (teleinfo::deamonRunning()) {
                throw new \Exception(__('Impossible d\'arrêter le démon', __FILE__));
            }
            log::clear('teleinfocmd');
            teleinfo::cron();
            ajax::success();
        break;
        case 'getTeleinfo':
            if (init('object_id') == '') {
                $_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
            }
            $object = object::byId(init('object_id'));
            if (!is_object($object)) {
                $object = object::rootObject();
            }
            if (!is_object($object)) {
                throw new \Exception('{{Aucun objet racine trouv�}}');
            }
            $return = array('object' => utils::o2a($object));

            $date = array(
                'start' => init('dateStart'),
                'end'   => init('dateEnd'),
            );

            if ($date['start'] == '') {
                $date['start'] = date('Y-m-d', strtotime('-6 days' . date('Y-m-d')));
            }
            if ($date['end'] == '') {
                $date['end'] = date('Y-m-d', strtotime('+1 days' . date('Y-m-d')));
            }
            $return['date'] = $date;
            foreach ($object->getEqLogic() as $eqLogic) {
                if ($eqLogic->getIsVisible() == '1' && $eqLogic->getEqType_name() == 'teleinfo') {
                    $return['eqLogics'][] = array('eqLogic' => utils::o2a($eqLogic), 'html' => $eqLogic->toHtml(init('version')));
                }
            }
            ajax::success($return);
        break;
        case 'getInformation':
            if (init('eqLogic_id') !== '') {

                $eqLogic                          = eqLogic::byId(init('eqLogic_id'));
                $return[$eqLogic->getId()]        = utils::o2a($eqLogic);
                $return[$eqLogic->getId()]['cmd'] = array();
                foreach ($eqLogic->getCmd() as $cmd) {
                    $cmd_info                           = utils::o2a($cmd);
                    $cmd_info['value']                  = $cmd->execCmd(null, 2);
                    $return[$eqLogic->getId()]['cmd'][] = $cmd_info;
                }
                ajax::success($return);
            } else {
                $eqLogics = eqLogic::byType('teleinfo');
                foreach ($eqLogics as $eqLogic) {
                    $return[$eqLogic->getId()]        = utils::o2a($eqLogic);
                    $return[$eqLogic->getId()]['cmd'] = array();
                    foreach ($eqLogic->getCmd() as $cmd) {
                        $cmd_info                           = utils::o2a($cmd);
                        $cmd_info['value']                  = $cmd->execCmd(null, 2);
                        $return[$eqLogic->getId()]['cmd'][] = $cmd_info;
                    }
                }
                ajax::success($return);
            }
        break;
        case 'getHealth':
            if (init('eqLogicID') !== null) {
                $teleinfo       = teleinfo::byLogicalId(init('eqLogicID'), 'teleinfo');
                $health_cmd     = $teleinfo->getCmd('info', 'health');
                $return         = array('object' => utils::o2a($health_cmd));
                $return["ADCO"] = init('eqLogicID');
                ajax::success($return);
            } else {
                $teleinfo = teleinfo::byType('teleinfo');
                foreach ($teleinfo as $eqLogic) {
                    $health_cmd     = $eqLogic->getCmd('info', 'health');
                    $return         = array('object' => utils::o2a($health_cmd));
                    $return["ADCO"] = $eqLogic->getLogicalId();
                    ajax::success($return);
                }
            }

            ajax::error("", "");
        break;
        case 'getInfoDaemon':
            $return   = array();
            $_nbLines = 1000;
            $replace  = array(
                '&gt;'   => '>',
                '&apos;' => '',
            );
            $page     = array();

            $path = realpath(dirname(__FILE__) . '/../../ressources/teleinfo.log');
            if (!file_exists($path)) {
                $return['result'] = array('Deamon non lancé');
                ajax::success($return);
            } else {
                $log = new SplFileObject($path);
                if ($log) {
                    $log->seek(0); //Seek to the begening of lines
                    $linesRead = 0;
                    while ($log->valid() && $linesRead != $_nbLines) {
                        $line = trim($log->current()); //get current line
                        if ($line != '') {
                            array_unshift($page, $line);
                        }
                        $log->next(); //go to next line
                        $linesRead++;
                    }
                }
                $return['result'] = $page;
                ajax::success($return);
            }
        break;
        case 'getInfoExternalDaemon':
            $jeeNetwork = jeeNetwork::byPlugin('enocean');
            $return['result'] = $jeeNetwork;
            ajax::success($return);
        break;
        case 'getHistory':
            $return = array();
            $return = history::byCmdIdDatetime(init('id'), date('Y-m-d H:i:s'));
            ajax::success($return);
        break;
        case 'getCout':
            $return = array();
            $return = history::byCmdIdDatetime(init('id'), date('Y-m-d H:i:s'));
            ajax::success($return);
        break;
        case 'diagnostic_step0':
            $return = array();

            $return['portName'] = config::byKey('port', 'teleinfo');
            if ($return['portName'] == "serie") {
                $return['portName'] = config::byKey('modem_serie_addr', 'teleinfo');
            }
            if ($return['portName'] === ""){
                $return['result'] = '0';
            }
            else {
                $return['result'] = '1';
            }
            $return['message'] = "Modem configuré : " . $return['portName'];
            try {
                $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                exec('rm ' . $diagnosticFile);
                file_put_contents($diagnosticFile, serialize(date('Y-m-d H:i:s')), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize('||STEP_0||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {

            }
            ajax::success($return);
        break;
        case 'diagnostic_step1':
            $return = array();
            $return['portName'] = config::byKey('port', 'teleinfo');
            if ($return['portName'] == "serie") {
                $return['portName'] = config::byKey('modem_serie_addr', 'teleinfo');
            }
            $return['portAddress'] = jeedom::getUsbMapping($return['portName']);
            $return['portAvailable'] = file_exists($return['portAddress']);
            if (!$return['portAvailable']){
                $return['result'] = '0';
                $return['message'] = 'Accès KO';
            }
            else {
                $return['result'] = '1';
                $return['message'] = 'Accès OK';
            }
            try {
                $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_1||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step2':
            $return = array();
            $return['isCapable'] = jeedom::isCapable('sudo');
            if (!jeedom::isCapable('sudo')) {
                $return['result'] = '0';
                $return['message'] = 'Vérifiez la configuration de votre Jeedom';
            }
            else {
                $return['result'] = '1';
                $return['message'] = 'OK';
            }
            try {
                $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_2||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step3':
            $return = array();
            $modemSerieAddr       = config::byKey('port', 'teleinfo');
            $twoCptCartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
            if (config::byKey('modem_vitesse', 'teleinfo') == "") {
                $modemVitesse = '1200';
            } else {
                $modemVitesse = config::byKey('modem_vitesse', 'teleinfo');
            }
            if ($modemSerieAddr == "serie") {
                $port = config::byKey('modem_serie_addr', 'teleinfo');
            } else {
                $port = jeedom::getUsbMapping(config::byKey('port', 'teleinfo'));
            }
            if ($twoCptCartelectronic == 1) {
                $return['result'] = '2';
                $return['message'] = 'Indisponible avec le modem 2 compteurs';
            }
            else{
                exec('stty -F ' . $port . ' ' . $modemVitesse . ' sane evenp parenb cs7 -crtscts');
                passthru('timeout 5 sed -n 5,8p ' . $port, $return['data']);
                $return['result'] = '1';
            }
            if ($return['data'] > 5){
                $return['result'] = '1';
                $return['message'] = 'OK';
            }
            else {
                $return['result'] = '0';
                $return['message'] = 'NOK';
            }
            try {
                $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_3||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step4':
            $return = array();
            $return['message'] = '';
            $return['launch_url'] = parse_url(config::byKey('internalProtocol', 'core', 'http://') . config::byKey('internalAddr', 'core', '127.0.0.1') . ":" . config::byKey('internalPort', 'core', '80') . config::byKey('internalComplement', 'core'));
            $return['result'] = '1';
            try {
                $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_4||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step5':
            $return = array();
            $monfichier = dirname(__FILE__) . '/../../../../tmp/teleinfo_export.txt';
            $diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
            exec('rm ' . $monfichier);
            file_put_contents($monfichier, serialize(date('Y-m-d H:i:s')), FILE_APPEND | LOCK_EX);
            foreach (eqLogic::byType('teleinfo') as $eqLogic) {
                //file_put_contents($monfichier, $eqLogic->getConfiguration(), FILE_APPEND | LOCK_EX);
                file_put_contents($monfichier, serialize('||EQLOGIC_NEW||'), FILE_APPEND | LOCK_EX);
                file_put_contents($monfichier, $eqLogic->getName() . ";", FILE_APPEND | LOCK_EX);
                file_put_contents($monfichier, serialize($eqLogic->getConfiguration()), FILE_APPEND | LOCK_EX);
                //file_put_contents($monfichier, serialize('\r\n'), FILE_APPEND | LOCK_EX);
                foreach ($eqLogic->getCmd() as $cmd) {
                    file_put_contents($monfichier, serialize('||CMD_NEW||'), FILE_APPEND | LOCK_EX);
                    file_put_contents($monfichier, serialize($cmd), FILE_APPEND | LOCK_EX);
                    file_put_contents($monfichier, serialize('||CMD_END||'), FILE_APPEND | LOCK_EX);
                }
                file_put_contents($monfichier, serialize('||EQLOGIC_END||'), FILE_APPEND | LOCK_EX);
            }
            $return["files"] = log::getPathToLog('teleinfo'). " " . log::getPathToLog('teleinfo_deamon'). " " . log::getPathToLog('teleinfo_update') . " " . dirname(__FILE__) . '/../../plugin_info/info.json'. " " . dirname(__FILE__) . '/../../../../tmp/teleinfo_export.txt' . " " . dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
            $return["path"] = dirname(__FILE__) . '/../../../../tmp/teleinfolog.tar';
            exec('rm ' . dirname(__FILE__) . '/../../../../tmp/teleinfolog.tar');
            $return["compress"] = exec('tar -cvf ' . dirname(__FILE__) . '/../../../../tmp/teleinfolog.tar ' . $return["files"]);
            $return['message'] = '<a class="btn btn-success" href="core/php/downloadFile.php?pathfile=tmp/teleinfolog.tar" target="_blank">Télécharger</a>';
            $return['result'] = '2';
            ajax::success($return);
        break;
    }
    throw new \Exception('Aucune methode correspondante');
} catch (\Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
