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

if ((php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) && (config::byKey('api') != init('api') && init('api') != '')) {
    echo 'Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (jeeTeleinfo)';
    die();
}
$args = array(
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
    'SINSTI'   => FILTER_SANITIZE_STRING,
    'IRMS1'   => FILTER_SANITIZE_STRING,
    'URMS1'   => FILTER_SANITIZE_STRING
);

$message = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_STRING);
$adco = filter_input(INPUT_GET, 'ADCO', FILTER_SANITIZE_STRING);
$adsc = filter_input(INPUT_GET, 'ADSC', FILTER_SANITIZE_STRING);
$sentDatas = "";

if($message != ''){
    $text = substr($message, 0, -2);
    $messages = preg_split("#(&|[\*]{2})#", $text);
    foreach ($messages as $key => $value){
        log::add('teleinfo', 'event', 'Log Daemon : ' . $value);
        $text = $text . date("Y-m-d H:i:s") . " " .  $value . "</br>";
    }
    $cache = cache::byKey('teleinfo::console', false);
    cache::set('teleinfo::console', $cache->getValue("") . $text, 1440);
    die();
}

if ($adco == '' && $adsc == ''){
    log::add('teleinfo', 'info', 'Pas d\'ADCO/ADSC dans la trame');
    die();
}

if ($adco != '')
{
    $teleinfo = teleinfo::byLogicalId($adco, 'teleinfo');
}
else
{
    $teleinfo = teleinfo::byLogicalId($adsc, 'teleinfo');
}

if (!is_object($teleinfo)) {
    $teleinfo = ($adco != '') ? teleinfo::createFromDef($adco) : teleinfo::createFromDef($adsc);
    if (!is_object($teleinfo)) {
        log::add('teleinfo', 'info', 'Aucun équipement trouvé pour le compteur n°' . $adco . $adsc);
        die();
    }
}

$myDatas = filter_input_array(INPUT_GET, $args);

$healthCmd = $teleinfo->getCmd('info','health');
$healthEnable = false;
if (is_object($healthCmd)) {
    $healthEnable = true;
}

foreach ($myDatas as $key => $value){
    if ($value != '') {
        $sentDatas = $sentDatas . $key . '=' . $value . ' / ';
        $cmd = $teleinfo->getCmd('info',$key);
        if ($cmd === false) {
            if($key != 'api' && $key != 'ADCO'){
                teleinfo::createCmdFromDef($teleinfo->getLogicalId(), $key, $value);
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
log::add('teleinfo', 'debug', 'Reception de : ' . $sentDatas);
