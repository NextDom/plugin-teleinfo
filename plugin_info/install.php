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
        $cron->setSchedule('10 00 * * *');
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
    cache::set('teleinfo::current_core','2.524', 0);
}

function teleinfo_update() {
    $core_version = '2.524';
    $desktop_version = '1.010';
    $mobile_version = '1.001';
    if (teleinfo::deamonRunning()) {
        teleinfo::stopDeamon();
    }
    message::add('Téléinfo', 'Mise à jour en cours...', null, null);
    log::add('teleinfo','info','*****************************************************');
    log::add('teleinfo','info','*********** Mise à jour du plugin teleinfo **********');
    log::add('teleinfo','info','*****************************************************');
    log::add('teleinfo','info','*        Core version    : '. $core_version. '                 *');
    log::add('teleinfo','info','*        Desktop version : '. $desktop_version. '                 *');
    log::add('teleinfo','info','*        Mobile version  : '. $mobile_version. '                 *');
    log::add('teleinfo','info','*****************************************************');

    config::save('teleinfo_core_version',$core_version,'teleinfo');
    config::save('teleinfo_desktop_version',$desktop_version,'teleinfo');
    config::save('teleinfo_mobile_version',$mobile_version,'teleinfo');

    $cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('CalculateOtherStats');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('10 00 * * *');
        $cron->save();
    }
    else{
        $cron->setSchedule('10 00 * * *');
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
    message::removeAll('Téléinfo');
    message::add('Téléinfo', 'Mise à jour terminée, vous êtes en version ' . $core_version . '.', null, null);
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
