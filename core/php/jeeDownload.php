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
	require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
	include_file('core', 'authentification', 'php');
	if (!isConnect() && !jeedom::apiAccess(init('apikey'))) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	unautorizedInDemo();

	/*$pathfile = realpath(calculPath(urldecode(init('pathfile'))));

	if ($pathfile === false) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	if (strpos($pathfile, '.php') !== false) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	$rootPath = realpath(__DIR__ . '/../../');
	log::add('teleinfo', 'debug', $rootPath);
    */
    $pathfile = jeedom::getTmpFolder("teleinfo") . "/teleinfolog.tar";
	$path_parts = pathinfo($pathfile);
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . $path_parts['basename']);
	readfile($pathfile);
	exit;
} catch (Exception $e) {
	echo $e->getMessage();
}
