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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function teleinfo_install() {
	if (teleinfo::deamonRunning()) {
        teleinfo::stopDeamon();
    }
	$cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('CalculateOtherStats');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('05 00 * * *');
        $cron->save();
    }
	
	$crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (!is_object($crontoday)) {
        $crontoday = new cron();
        $crontoday->setClass('teleinfo');
        $crontoday->setFunction('CalculateTodayStats');
        $crontoday->setEnable(1);
        $crontoday->setDeamon(0);
        $crontoday->setSchedule('*/5 * * * *');
        $crontoday->save();
    }
	cache::set('teleinfo::current_core','2.210', 0);
}

function teleinfo_update() {
	if (teleinfo::deamonRunning()) {
        teleinfo::stopDeamon();
    }
	$cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('CalculateOtherStats');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('05 00 * * *');
        $cron->save();
    }
	else{
		$cron->setSchedule('05 00 * * *');
        $cron->save();
	}
    $cron->stop();
	
	$crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (!is_object($crontoday)) {
        $crontoday = new cron();
        $crontoday->setClass('teleinfo');
        $crontoday->setFunction('CalculateTodayStats');
        $crontoday->setEnable(1);
        $crontoday->setDeamon(0);
        $crontoday->setSchedule('*/5 * * * *');
        $crontoday->save();
    }
    $crontoday->stop();
	$thisplugin = plugin::byid('teleinfo');
	//$current_core_version = cache::byKey('teleinfo::current_core', false,true);
	//log::add('teleinfo', 'message', 'Merci de valider à nouveau votre objet Téléinfo en re-sauvegardant.');
	//cache::set('teleinfo::current_core','2.230', 0);
	//cache::set('teleinfo::current_core',$thisplugin->getVersion(), 0);
	teleinfo::cron();
}

function teleinfo_remove() {
	if (teleinfo::deamonRunning()) {
        teleinfo::stopDeamon();
    }
	$cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (is_object($cron)) {
        $cron->remove();
    }
	$crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (is_object($crontoday)) {
        $crontoday->remove();
    }
}

?>
