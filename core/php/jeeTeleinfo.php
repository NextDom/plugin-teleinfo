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

$message = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_STRING);
$adco = filter_input(INPUT_GET, 'ADCO', FILTER_SANITIZE_STRING);
$array_recu = "";

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

if ($adco == ''){
    log::add('teleinfo', 'info', 'Pas d\'ADCO dans la trame');
    die();
}

$teleinfo = teleinfo::byLogicalId($adco, 'teleinfo');
if (!is_object($teleinfo)) {
    $teleinfo = teleinfo::createFromDef($adco);
    if (!is_object($teleinfo)) {
        log::add('teleinfo', 'info', 'Aucun équipement trouvé pour le compteur n°' . $adco);
        die();
    }
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
    'ADCO'   => FILTER_SANITIZE_STRING
);

$myDatas = filter_input_array(INPUT_GET, $args);

$healthCmd = $teleinfo->getCmd('info','health');
$healthEnable = false;
if (is_object($healthCmd)) {
    $healthEnable = true;
}

foreach ($myDatas as $key => $value){
    if ($value != '') {
        $array_recu = $array_recu . $key . '=' . $value . ' / ';
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
log::add('teleinfo', 'debug', 'Reception de : ' . $array_recu);
