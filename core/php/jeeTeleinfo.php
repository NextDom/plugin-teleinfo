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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
set_time_limit(15);

if (!jeedom::apiAccess(init('apikey'), 'teleinfo')) {
    echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (teleinfo)', __FILE__);
    http_response_code(403);
    die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}

$args = array(
	'device' => FILTER_SANITIZE_STRING,
    'BASE'   => FILTER_SANITIZE_STRING,
    'PAPP'   => FILTER_SANITIZE_STRING,
    'HCHP'   => FILTER_SANITIZE_STRING,
    'HCHC'   => FILTER_SANITIZE_STRING,
    'PTEC'   => FILTER_SANITIZE_STRING,
    'IINST'   => FILTER_SANITIZE_STRING,
    'IINST1'   => FILTER_SANITIZE_STRING,
    'IINST2'   => FILTER_SANITIZE_STRING,
    'IINST3'   => FILTER_SANITIZE_STRING,
    'IMAX'   => FILTER_SANITIZE_STRING,
    'IMAX1'   => FILTER_SANITIZE_STRING,
    'IMAX2'   => FILTER_SANITIZE_STRING,
    'IMAX3'   => FILTER_SANITIZE_STRING,
    'PMAX'   => FILTER_SANITIZE_STRING,
    'ADPS'   => FILTER_SANITIZE_STRING,
    'ADIR1'   => FILTER_SANITIZE_STRING,
    'ADIR2'   => FILTER_SANITIZE_STRING,
    'ADIR3'   => FILTER_SANITIZE_STRING,
    'OPTARIF'   => FILTER_SANITIZE_STRING,
    'ISOUSC'   => FILTER_SANITIZE_STRING,
    'EJPHN'   => FILTER_SANITIZE_STRING,
    'EJPHPM'   => FILTER_SANITIZE_STRING,
    'HHPHC'   => FILTER_SANITIZE_STRING,
    'PPOT'   => FILTER_SANITIZE_STRING,
    'BBRHCJB'   => FILTER_SANITIZE_STRING,
    'BBRHPJB'   => FILTER_SANITIZE_STRING,
    'BBRHCJW'   => FILTER_SANITIZE_STRING,
    'BBRHPJW'   => FILTER_SANITIZE_STRING,
    'BBRHCJR'   => FILTER_SANITIZE_STRING,
    'BBRHPJR'   => FILTER_SANITIZE_STRING,
    'PEJP'   => FILTER_SANITIZE_STRING,
    'DEMAIN'   => FILTER_SANITIZE_STRING,
    'MOTDETAT'   => FILTER_SANITIZE_STRING,
    'ADCO'   => FILTER_SANITIZE_STRING,
    'ADSC'   => FILTER_SANITIZE_STRING,	
    'VTIC'   => FILTER_SANITIZE_STRING,
    'DATE'   => FILTER_SANITIZE_STRING,
    'NGTF'   => FILTER_SANITIZE_STRING,
    'LTARF'   => FILTER_SANITIZE_STRING,
    'EAST'   => FILTER_SANITIZE_STRING,
    'EASF01'   => FILTER_SANITIZE_STRING,
    'EASF02'   => FILTER_SANITIZE_STRING,
    'EASF03'   => FILTER_SANITIZE_STRING,
    'EASF04'   => FILTER_SANITIZE_STRING,
    'EASF05'   => FILTER_SANITIZE_STRING,
    'EASF06'   => FILTER_SANITIZE_STRING,
    'EASF07'   => FILTER_SANITIZE_STRING,
    'EASF08'   => FILTER_SANITIZE_STRING,
    'EASF09'   => FILTER_SANITIZE_STRING,
    'EASF10'   => FILTER_SANITIZE_STRING,
    'EASD01'   => FILTER_SANITIZE_STRING,
    'EASD02'   => FILTER_SANITIZE_STRING,
    'EASD03'   => FILTER_SANITIZE_STRING,
    'EASD04'   => FILTER_SANITIZE_STRING,
    'EAIT'   => FILTER_SANITIZE_STRING,
    'PREF'   => FILTER_SANITIZE_STRING,
    'PCOUP'   => FILTER_SANITIZE_STRING,
    'SINSTS'   => FILTER_SANITIZE_STRING,
    'SMAXSN'   => FILTER_SANITIZE_STRING,
    'SMAXSN-1'   => FILTER_SANITIZE_STRING,
    'CCASN'   => FILTER_SANITIZE_STRING,
    'CCASN-1'   => FILTER_SANITIZE_STRING,
    'UMOY1'   => FILTER_SANITIZE_STRING,
    'STGE'   => FILTER_SANITIZE_STRING,
    'MSG1'   => FILTER_SANITIZE_STRING,
    'MSG2'   => FILTER_SANITIZE_STRING,
    'PRM'   => FILTER_SANITIZE_STRING,
    'RELAIS'   => FILTER_SANITIZE_STRING,
    'NTARF'   => FILTER_SANITIZE_STRING,
    'NJOURF'   => FILTER_SANITIZE_STRING,
    'NJOURF+1'   => FILTER_SANITIZE_STRING,
    'PJOURF+1'   => FILTER_SANITIZE_STRING,
    'PPOINTE'   => FILTER_SANITIZE_STRING,
    'SINST1'   => FILTER_SANITIZE_STRING,
    'SINSTI'   => FILTER_SANITIZE_STRING,
    'IRMS1'   => FILTER_SANITIZE_STRING,
    'URMS1'   => FILTER_SANITIZE_STRING,
    'STGE01'   => FILTER_SANITIZE_STRING,
    'STGE02'   => FILTER_SANITIZE_STRING,
    'STGE03'   => FILTER_SANITIZE_STRING,
    'STGE04'   => FILTER_SANITIZE_STRING,
    'STGE05'   => FILTER_SANITIZE_STRING,
    'STGE06'   => FILTER_SANITIZE_STRING,
    'STGE07'   => FILTER_SANITIZE_STRING,
    'STGE08'   => FILTER_SANITIZE_STRING,
    'STGE09'   => FILTER_SANITIZE_STRING,
    'STGE10'   => FILTER_SANITIZE_STRING,
    'STGE11'   => FILTER_SANITIZE_STRING,
    'STGE12'   => FILTER_SANITIZE_STRING,
    'STGE13'   => FILTER_SANITIZE_STRING,
    'STGE14'   => FILTER_SANITIZE_STRING,
    'STGE15'   => FILTER_SANITIZE_STRING,
    'STGE16'   => FILTER_SANITIZE_STRING,
    'STGE17'   => FILTER_SANITIZE_STRING,
    'STGE18'   => FILTER_SANITIZE_STRING,
    'STGE19'   => FILTER_SANITIZE_STRING,
    'STGE20'   => FILTER_SANITIZE_STRING,
    'RELAIS01'   => FILTER_SANITIZE_STRING,
    'RELAIS02'   => FILTER_SANITIZE_STRING,
    'RELAIS03'   => FILTER_SANITIZE_STRING,
    'RELAIS04'   => FILTER_SANITIZE_STRING,
    'RELAIS05'   => FILTER_SANITIZE_STRING,
    'RELAIS06'   => FILTER_SANITIZE_STRING,
    'RELAIS07'   => FILTER_SANITIZE_STRING,
    'RELAIS08'   => FILTER_SANITIZE_STRING
);

//$message = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_STRING);
//$adco = filter_input(INPUT_GET, 'ADCO', FILTER_SANITIZE_STRING);
//$adsc = filter_input(INPUT_GET, 'ADSC', FILTER_SANITIZE_STRING);
//$myDatas = filter_input_array(INPUT_GET, $args);

$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}


$var_to_log = '';

if (isset($result['device'])) {
    foreach ($result['device'] as $key => $data) {
            log::add('teleinfo','debug','This is a message from teleinfo program ' . $key);
    		$eqlogic = teleinfo::byLogicalId($data['device'], 'teleinfo');
    		if (is_object($eqlogic)) {
                $healthCmd = $eqlogic->getCmd('info','health');
                $healthEnable = false;
                if (is_object($healthCmd)) {
                    $healthEnable = true;
                }
                $flattenResults = array_flatten($data);
                foreach ($flattenResults as $key => $value) {
                    $cmd = $eqlogic->getCmd('info',$key);
                    if ($cmd === false) {
                        if($key != 'device' && $key != 'ADCO'){
                            teleinfo::createCmdFromDef($eqlogic->getLogicalId(), $key, $value);
                            if($healthEnable) {
                                $healthCmd->setConfiguration($key, array("name" => $key, "value" => $value, "update_time" => date("Y-m-d H:i:s")));
                                $healthCmd->save();
                            }
                        }
                    }
                    else{
                        $cmd->event($value);
                        if($healthEnable) {
                            $healthCmd->setConfiguration($key, array("name" => $key, "value" => $value, "update_time" => date("Y-m-d H:i:s")));
                            $healthCmd->save();
                        }
                    }
                }
            }
            else {
                $teleinfo = ($data['device'] != '') ? teleinfo::createFromDef($data['device']) : teleinfo::createFromDef($data['device']);
                if (!is_object($teleinfo)) {
                    log::add('teleinfo', 'info', 'Aucun équipement trouvé pour le compteur n°' . $data['device']);
                    die();
                }
            }
            log::add('teleinfo','debug',$var_to_log);
        }
    }

function array_flatten($array) {
    global $var_to_log;
    $return = array();
    foreach ($array as $key => $value) {
        $var_to_log = $var_to_log . $key . '=' . $value . '|';
        if (is_array($value))
            $return = array_merge($return, array_flatten($value));
        else
            $return[$key] = $value;
    }
    return $return;
}
