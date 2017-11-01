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
        throw new Exception('401 Unauthorized');
    }

    if (init('action') == 'stopDeamon') {
        teleinfo::stopDeamon();
        ajax::success();
    }
	
	if (init('action') == 'restartDeamon') {
		$port = config::byKey('port', 'teleinfo', 'none');
		$_2cpt_cartelectronic = config::byKey('2cpt_cartelectronic', 'teleinfo');
		if($_2cpt_cartelectronic == 1){
			
		}
		if ($port == 'none') {
			ajax::success();
		}
		teleinfo::stopDeamon();
		if (teleinfo::deamonRunning()) {
			throw new Exception(__('Impossible d\'arrêter le démon', __FILE__));
		}
		log::clear('teleinfocmd');
		teleinfo::cron();
		ajax::success();
	}
	
	if (init('action') == 'restartSlaveDeamon') {
		if (config::byKey('jeeNetwork::mode') == 'master') {
			$jeeNetwork = jeeNetwork::byId(init('id'));
			if (!is_object($jeeNetwork)) {
				throw new Exception(__('Impossible de trouver l\'esclave : ', __FILE__) . init('id'));
			}
			$jeeNetwork->sendRawRequest('restartDeamon', array('plugin' => 'teleinfo'));
		}
		ajax::success();
	}

	if (init('action') == 'stopSlaveDeamon') {
		if (config::byKey('jeeNetwork::mode') == 'master') {
			$jeeNetwork = jeeNetwork::byId(init('id'));
			if (!is_object($jeeNetwork)) {
				throw new Exception(__('Impossible de trouver l\'esclave : ', __FILE__) . init('id'));
			}
			$jeeNetwork->sendRawRequest('stopDeamon', array('plugin' => 'teleinfo'));
		}
		ajax::success();
	}
	
	if (init('action') == 'getTeleinfo') {
        if (init('object_id') == '') {
            $_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
        }
        $object = object::byId(init('object_id'));
        if (!is_object($object)) {
            $object = object::rootObject();
        }
        if (!is_object($object)) {
            throw new Exception('{{Aucun objet racine trouvé}}');
        }
        $return = array('object' => utils::o2a($object));

        $date = array(
            'start' => init('dateStart'),
            'end' => init('dateEnd'),
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
    }
    
	if (init('action') == 'getInformation') {
		if(init('eqLogic_id') != null){
			$eqLogic = eqLogic::byId(init('eqLogic_id'));
				$return[$eqLogic->getId()] = utils::o2a($eqLogic);
				$return[$eqLogic->getId()]['cmd'] = array();
				foreach ($eqLogic->getCmd() as $cmd) {
					$cmd_info = utils::o2a($cmd);
					$cmd_info['value'] = $cmd->execCmd(null, 2);
					$return[$eqLogic->getId()]['cmd'][] = $cmd_info;
				}
			ajax::success($return);			
		}else{
			$eqLogics = eqLogic::byType('teleinfo');
			foreach ($eqLogics as $eqLogic) {
				$return[$eqLogic->getId()] = utils::o2a($eqLogic);
				$return[$eqLogic->getId()]['cmd'] = array();
				foreach ($eqLogic->getCmd() as $cmd) {
					$cmd_info = utils::o2a($cmd);
					$cmd_info['value'] = $cmd->execCmd(null, 2);
					$return[$eqLogic->getId()]['cmd'][] = $cmd_info;
				}
			}
			ajax::success($return);
		}
    }
	if (init('action') == 'getHealth') {
		//$eqLogics = eqLogic::byType('teleinfo');
		if(init('eqLogicID') != null){
			$teleinfo = teleinfo::byLogicalId(init('eqLogicID'), 'teleinfo');
			$health_cmd = $teleinfo->getCmd('info','health');
			//$return[0] = $health_cmd;
			$return = array('object' => utils::o2a($health_cmd));
			$return["ADCO"] = init('eqLogicID');
			ajax::success($return);
		}
		else{
			$teleinfo = teleinfo::byType('teleinfo');
			foreach ($teleinfo as $eqLogic) {
				$health_cmd = $eqLogic->getCmd('info','health');
				//$return[0] = $health_cmd;
				$return = array('object' => utils::o2a($health_cmd));
				$return["ADCO"] = $eqLogic->getLogicalId();
				ajax::success($return);		
			}
		}
		
		ajax::error("", "");
	}
	if (init('action') == 'getInfoDaemon') {
	
		$return = array();
		$_nbLines = 1000;
		$replace = array(
			'&gt;' => '>',
			'&apos;' => '',
		);
		$page = array();
		
		$path = realpath(dirname(__FILE__) . '/../../ressources/teleinfo.log');
		//log::add('teleinfo', 'info', $path);
		if (!file_exists($path)) {
			$return['result'] = array('Deamon non lancé');
			ajax::success($return);
		}else{
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
			//return $page;
			$return['result'] = $page;
			ajax::success($return);
		}
	}
	
	if (init('action') == 'getInfoExternalDaemon') {
		$jeeNetwork = jeeNetwork::byPlugin('enocean');
		
		$return['result'] = $jeeNetwork;
		ajax::success($return);
		//$jeeNetwork = jeeNetwork::byId(init('id'));
		//	//if (!is_object($jeeNetwork)) {
		//		throw new Exception(__('Impossible de trouver l\'esclave : ', __FILE__) . init('id'));
		//	}
		//	$jeeNetwork->sendRawRequest('getInfoDaemon', array('plugin' => 'teleinfo'));
	}
	if (init('action') == 'getHistory') {
		$return = array();
		/*$data = array();
		$datetime = null;*/
		//console.log("essai");
		//console.log('Commande : ' . init('id'));
		$return = history::byCmdIdDatetime( init('id'), date('Y-m-d H:i:s'));
		//$dateEnd = date('Y-m-d H:i:s');
        ajax::success($return);
    }

	if (init('action') == 'getCout') {
		$return = array();
		/*$data = array();
		$datetime = null;*/
		//console.log("essai");
		//console.log('Commande : ' . init('id'));
		$return = history::byCmdIdDatetime( init('id'), date('Y-m-d H:i:s'));
		//$dateEnd = date('Y-m-d H:i:s');
        ajax::success($return);
    }
	
	
    throw new Exception('Aucune methode correspondante');
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
