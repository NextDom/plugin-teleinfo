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
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}
set_time_limit(15);

if ((php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) && (config::byKey('api') != init('api') && init('api') != '')) {
	connection::failed();
	echo 'Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (jeeTeleinfo)';
	die();
}

if(isset($_GET['message'])){
	$text = "";
	//log::add('teleinfo', 'event', 'Log Daemon : ' . $_GET['message']);
	$text = substr($_GET['message'], 0, -2);
	$messages = preg_split("#(&|[\*]{2})#", $text);
	$text = "";
	foreach ($messages as $key => $value){
		log::add('teleinfo', 'event', 'Log Daemon : ' . $value);
		$text = $text . date("Y-m-d H:i:s") . " " .  $value . "</br>";
	}
	$cache = cache::byKey('teleinfo::console', false);
	//$cache->setValue($cache->getValue("") . "\n" . $text);
	cache::set('teleinfo::console', $cache->getValue("") . $text, 1440);	
	die();
}

$array_recu = "";
if (!isset($_GET["ADCO"])){
    log::add('teleinfo', 'info', 'Pas d\'ADCO dans la trame');
	die();
}
$teleinfo = teleinfo::byLogicalId($_GET['ADCO'], 'teleinfo');
if (!is_object($teleinfo)) {
	$teleinfo = teleinfo::createFromDef($_GET);
	if (!is_object($teleinfo)) {
		log::add('teleinfo', 'info', 'Aucun équipement trouvé pour le compteur n°' . $_GET['ADCO']);
		die();
	}
}

$health_cmd = $teleinfo->getCmd('info','health');

foreach ($_GET as $key => $value){
	$array_recu = $array_recu . $key . '=' . $value . ' / ';
	if (is_object($health_cmd)) {
		$_value = array("name" => $key, "value" => $value, "update_time" => date("Y-m-d H:i:s"));
		$health_cmd->setConfiguration($key, $_value);
		$health_cmd->save();
	}
	$cmd = $teleinfo->getCmd('info',$key);
	if (is_object($cmd)) {
		$cmd->event($value);
	}
	else{
		if($key != 'api' && $key != 'ADCO'){
			log::add('teleinfo', 'debug', 'Commande inexistante (' . $key . ')');
			teleinfo::createCmdFromDef($teleinfo->getLogicalId(), $key, $value);
		}
	}
}
log::add('teleinfo', 'debug', 'Reception de : ' . $array_recu);
