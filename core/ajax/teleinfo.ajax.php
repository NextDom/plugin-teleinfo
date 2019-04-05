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
            teleinfo::deamon_stop();
            teleinfo::deamon_start();
            ajax::success();
        break;
        case 'changeLogLive':
            ajax::success(teleinfo::changeLogLive(init('level')));
        break;
        case 'getTeleinfo':
            if (init('object_id') == '') {
                $_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
            }
            $object = jeeObject::byId(init('object_id'));
		    if (!is_object($object)) {
                $object = jeeObject::rootObject();
            }
            if (!is_object($object)) {
                throw new \Exception('{{Aucun objet racine trouve}}');
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
            if (init('eqLogicID') !== '') {
                $teleinfo       = teleinfo::byLogicalId(init('eqLogicID'), 'teleinfo');
                $health_cmd     = $teleinfo->getCmd('info', 'health');
                $return         = array('object' => utils::o2a($health_cmd));
                $return["ADCO"] = init('eqLogicID');
                ajax::success($return);
            } else {
                foreach (eqLogic::byType('teleinfo') as $eqLogic) {
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
        case 'getHistory':
            $return = array();
            $return = history::byCmdIdDatetime(init('id'), date('Y-m-d H:i:s'));
            ajax::success($return);
        break;
		case 'findModemType':
			ajax::success(teleinfo::findModemType(init('port'),init('type')));
		break;
        case 'countArchive':
            $return = array();
			if (init('id') !== '') {
                $sql = 'SELECT COUNT(*) as count FROM historyArch WHERE cmd_id=:cmdId';
                $values = array(
			                 'cmdId' => init('id'),
		        );
                $sqlResult = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
                $return['count'] = $sqlResult;

                $sql = 'SELECT MIN(datetime) as oldest FROM historyArch WHERE cmd_id =:cmdId';
                $sqlResult = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
                $return['oldest'] = $sqlResult;
                ajax::success($return);
            }
		break;
		case 'countArchiveNotZero':
            $return = array();
			if (init('id') !== '') {
                $sql = 'SELECT COUNT(*) as count FROM historyArch WHERE cmd_id=:cmdId AND MINUTE(datetime) <> "0"';
                $values = array(
			                 'cmdId' => init('id'),
		        );
                $sqlResult = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
                $return['count'] = $sqlResult;
                ajax::success($return);
            }
		break;
        case 'optimizeArchive':
			$return = array();
			$valuesClean = 0;
			if (init('id') !== '') {

				// Plus ancienne valeur différente de heure fixe
                $sql = "SELECT datetime as oldest FROM historyArch WHERE MINUTE(datetime) <> '0' AND  cmd_id=:cmdId";
                $values = array(
			                 'cmdId' => init('id'),
		        );
				$oldest = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);

				while ($oldest['oldest'] !== null) {
					// Récupération de la valeur max sur l heure
                    if(substr($oldest['oldest'],-8,2) == "00" && init('type') != "AVG"){
                        $sql = "SELECT MIN(CAST(value AS DECIMAL(12,2))) as value, FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(datetime))) as datetime FROM historyArch WHERE addtime(datetime,'-01:00:00')<:oldest AND cmd_id=:cmdId;";
                    }
                    else{
                        $sql = "SELECT ". init('type') . "(CAST(value AS DECIMAL(12,2))) as value, FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(datetime))) as datetime FROM historyArch WHERE addtime(datetime,'-01:00:00')<:oldest AND cmd_id=:cmdId;";
                    }

                    $values = array(
								 'cmdId' => init('id'),
								 'oldest' => $oldest['oldest'],
					);
					$maxValue = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);

					$sql = "REPLACE INTO historyArch SET cmd_id=:cmdId,datetime=:newDatetime,value=:value";
					$values = array(
								'cmdId' => init('id'),
								'newDatetime' => date('Y-m-d H:00:00', strtotime($oldest['oldest']) + 300),
								'value' => $maxValue['value'],
					);
					$return['replaceValue'] = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);

					// Nettoyage de toutes les valeurs autres qu a heure fixe
					$sql = "DELETE FROM historyArch WHERE addtime(datetime,'-01:00:00')< :oldest AND cmd_id=:cmdId AND MINUTE(datetime) <> '0';";
					$values = array(
								 'cmdId' => init('id'),
								 'oldest' => $oldest['oldest'],
					);
					$deleteValues = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
					$sql = "SELECT datetime as oldest FROM historyArch WHERE MINUTE(datetime) <> '0' AND  cmd_id=:cmdId";
					$values = array(
			                 'cmdId' => init('id'),
					);
					$oldest = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
					$valuesClean+=1;
				}
				$return['valuesClean'] = $valuesClean;
                ajax::success($return);
                //ajax::success();
            }
		break;
        case 'regenerateMonthlyStat':
            $return = array();
            cache::set('teleinfo::regenerateMonthlyStat', '1', 86400);
            $indexConsoHP           = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
            $indexConsoHC           = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
            $indexProduction        = config::byKey('indexProduction', 'teleinfo', 'EAIT');

            foreach (eqLogic::byType('teleinfo') as $eqLogic) {

                $startDay = (new DateTime())->setTimestamp(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
                $endDay   = (new DateTime())->setTimestamp(mktime(23, 59, 59, date("m"), date("d"), date("Y")));
                $statHpToCumul       = array();
                $statHcToCumul       = array();
                $statProdToCumul     = array();

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

                for($i=1; $i < 366; $i++){
                    $statHc     = 0;
                    $statHp     = 0;
                    $statProd   = 0;
                    $startDay->sub(new DateInterval('P1D'));
                    $endDay->sub(new DateInterval('P1D'));


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
                            }
                        }
                    }

                }
            }
            ajax::success($return);
        break;
        case 'diagnostic_step1':
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
            $return['test'] = jeedom::getTmpFolder("teleinfo");
            try {
                //$diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
                exec('rm ' . $diagnosticFile);
                file_put_contents($diagnosticFile, serialize(date('Y-m-d H:i:s')), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize('||STEP_1||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {

            }
            ajax::success($return);
        break;
        case 'diagnostic_step2':
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
                //$diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_2||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step3':
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
                //$diagnosticFile = dirname(__FILE__) . '/../../../../tmp/teleinfo_diag.txt';
                $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_3||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step4':
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
                $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_4||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step5':
            $return = array();
            $return['message'] = '';
            $return['result'] = '1';
            try {
                $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
                file_put_contents($diagnosticFile, serialize('||STEP_5||'), FILE_APPEND | LOCK_EX);
                file_put_contents($diagnosticFile, serialize($return), FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {}
            ajax::success($return);
        break;
        case 'diagnostic_step6':
            $return = array();
            $monfichier = jeedom::getTmpFolder("teleinfo") . '/teleinfo_export.txt';
            $diagnosticFile = jeedom::getTmpFolder("teleinfo") . '/teleinfo_diag.txt';
            exec('rm ' . $monfichier);
            file_put_contents($monfichier, serialize(date('Y-m-d H:i:s')), FILE_APPEND | LOCK_EX);
            foreach (eqLogic::byType('teleinfo') as $eqLogic) {
                file_put_contents($monfichier, serialize('||EQLOGIC_NEW||'), FILE_APPEND | LOCK_EX);
                file_put_contents($monfichier, $eqLogic->getName() . ";", FILE_APPEND | LOCK_EX);
                file_put_contents($monfichier, serialize($eqLogic->getConfiguration()), FILE_APPEND | LOCK_EX);
                foreach ($eqLogic->getCmd() as $cmd) {
                    file_put_contents($monfichier, serialize('||CMD_NEW||'), FILE_APPEND | LOCK_EX);
                    file_put_contents($monfichier, serialize($cmd), FILE_APPEND | LOCK_EX);
                    file_put_contents($monfichier, serialize('||CMD_END||'), FILE_APPEND | LOCK_EX);
                }
                file_put_contents($monfichier, serialize('||EQLOGIC_END||'), FILE_APPEND | LOCK_EX);
            }
            $return["files"] = log::getPathToLog('teleinfo'). " " . log::getPathToLog('teleinfo_deamon_conso'). " " . log::getPathToLog('teleinfo_update') . " " . dirname(__FILE__) . '/../../plugin_info/info.json'. " " . $diagnosticFile  . " " . $monfichier;
            $return["path"] = jeedom::getTmpFolder("teleinfo") . '/teleinfolog.tar';
            exec('rm ' . jeedom::getTmpFolder("teleinfo") . '/teleinfolog.tar');
            $return["compress"] = exec('tar -cvf ' . jeedom::getTmpFolder("teleinfo") . '/teleinfolog.tar ' . $return["files"]);
            $return['message'] = '<a class="btn btn-success" href="plugins/teleinfo/core/php/jeeDownload.php" target="_blank">Télécharger le package</a>';
            $return['result'] = '2';
            ajax::success($return);
        break;
    }
    throw new \Exception('Aucune methode correspondante');
} catch (\Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
