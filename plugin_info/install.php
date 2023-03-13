﻿<?php

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
    $core_version = '1.1.1';
    if (!file_exists(dirname(__FILE__) . '/info.json')) {
        log::add('teleinfo','warning','Pas de fichier info.json');
        goto step2;
    }
    $data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
    if (!is_array($data)) {
        log::add('teleinfo','warning','Impossible de décoder le fichier info.json');
        goto step2;
    }
    try {
        $core_version = $data['pluginVersion'];
    } catch (\Exception $e) {

    }
    step2:
    if (teleinfo::deamonRunning()) {
        teleinfo::deamon_stop();
    }
    $cron = cron::byClassAndFunction('teleinfo', 'calculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('calculateOtherStats');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('10 00 * * *');
        $cron->save();
    }

    $crontoday = cron::byClassAndFunction('teleinfo', 'calculateTodayStats');
    if (!is_object($crontoday)) {
        $crontoday = new cron();
        $crontoday->setClass('teleinfo');
        $crontoday->setFunction('calculateTodayStats');
        $crontoday->setEnable(1);
        $crontoday->setDeamon(0);
        $crontoday->setSchedule('*/5 * * * *');
        $crontoday->save();
    }
    message::removeAll('teleinfo');
    message::add('teleinfo', 'Installation du plugin Téléinfo terminée, vous êtes en version ' . $core_version . '.');
    //cache::set('teleinfo::current_core','2.610', 0);
}

function teleinfo_update() {
    log::add('teleinfo','debug','teleinfo_update');
    $core_version = '1.1.1';
    if (!file_exists(dirname(__FILE__) . '/info.json')) {
        log::add('teleinfo','warning','Pas de fichier info.json');
        goto step2;
    }
    $data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
    if (!is_array($data)) {
        log::add('teleinfo','warning','Impossible de décoder le fichier info.json');
        goto step2;
    }
    try {
        $core_version = $data['pluginVersion'];
    } catch (\Exception $e) {
        log::add('teleinfo','warning','Pas de version de plugin');
    }
    step2:
    if (teleinfo::deamonRunning()) {
        teleinfo::deamon_stop();
    }
    message::add('teleinfo', 'Mise à jour du plugin Téléinfo en cours...');
    log::add('teleinfo','info','*****************************************************');
    log::add('teleinfo','info','*********** Mise à jour du plugin teleinfo **********');
    log::add('teleinfo','info','*****************************************************');
    log::add('teleinfo','info','**        Core version    : '. $core_version. '                **');
    log::add('teleinfo','info','*****************************************************');

    $cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (is_object($cron)) {
        $cron->remove();
    }
    $crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (is_object($crontoday)) {
        $crontoday->remove();
    }


// mise à jour stat si elles n'existent pas
    log::add('teleinfo', 'info', "-------- Commandes des stats si elles n'existent pas ---------");

    $array = array("STAT_TODAY_INDEX00","STAT_TODAY_INDEX00_COUT","STAT_YESTERDAY_INDEX00","STAT_YESTERDAY_INDEX00_COUT",
                    "STAT_TODAY_INDEX01","STAT_TODAY_INDEX01_COUT","STAT_YESTERDAY_INDEX01","STAT_YESTERDAY_INDEX01_COUT",
                    "STAT_TODAY_INDEX02","STAT_TODAY_INDEX02_COUT","STAT_YESTERDAY_INDEX02","STAT_YESTERDAY_INDEX02_COUT",
                    "STAT_TODAY_INDEX03","STAT_TODAY_INDEX03_COUT","STAT_YESTERDAY_INDEX03","STAT_YESTERDAY_INDEX03_COUT",
                    "STAT_TODAY_INDEX04","STAT_TODAY_INDEX04_COUT","STAT_YESTERDAY_INDEX04","STAT_YESTERDAY_INDEX04_COUT",
                    "STAT_TODAY_INDEX05","STAT_TODAY_INDEX05_COUT","STAT_YESTERDAY_INDEX05","STAT_YESTERDAY_INDEX05_COUT",
                    "STAT_TODAY_INDEX06","STAT_TODAY_INDEX06_COUT","STAT_YESTERDAY_INDEX06","STAT_YESTERDAY_INDEX06_COUT",
                    "STAT_TODAY_INDEX07","STAT_TODAY_INDEX07_COUT","STAT_YESTERDAY_INDEX07","STAT_YESTERDAY_INDEX07_COUT",
                    "STAT_TODAY_INDEX08","STAT_TODAY_INDEX08_COUT","STAT_YESTERDAY_INDEX08","STAT_YESTERDAY_INDEX08_COUT",
                    "STAT_TODAY_INDEX09","STAT_TODAY_INDEX09_COUT","STAT_YESTERDAY_INDEX09","STAT_YESTERDAY_INDEX09_COUT",
                    "STAT_TODAY_INDEX10","STAT_TODAY_INDEX10_COUT","STAT_YESTERDAY_INDEX10","STAT_YESTERDAY_INDEX10_COUT");
    
    foreach (eqLogic::byType('teleinfo') as $eqLogic) {
        foreach ($array as $value){
            $cmd = $eqLogic->getCmd('info', $value);
            if (!is_object($cmd)) {
                log::add('teleinfo', 'info', "Nouvelle STAT => compteur '". $eqLogic->getName() . "' " . $value);
                if (strpos($value,'COUT')<>0) {
                    $unite = ('€');
                }else{
                    $unite = ('Wh');
                }
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setUnite($unite);
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'stat');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setIsHistorized(1);
                //$cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
                $cmd->refresh();
            }

        }
    }


    //fin mise à jour stat






    $cron = cron::byClassAndFunction('teleinfo', 'calculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('calculateOtherStats');
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

    $crontoday = cron::byClassAndFunction('teleinfo', 'calculateTodayStats');
    if (!is_object($crontoday)) {
        $crontoday = new cron();
        $crontoday->setClass('teleinfo');
        $crontoday->setFunction('calculateTodayStats');
        $crontoday->setEnable(1);
        $crontoday->setDeamon(0);
        $crontoday->setSchedule('*/5 * * * *');
        $crontoday->save();
    }
    $crontoday->stop();
    message::removeAll('teleinfo');
    message::add('teleinfo', 'Mise à jour du plugin Téléinfo terminée, vous êtes en version ' . $core_version . '.');
    teleinfo::cron();
}

function teleinfo_remove() {
    if (teleinfo::deamonRunning()) {
        teleinfo::deamon_stop();
    }
    $cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (is_object($cron)) {
        $cron->remove();
    }
    $crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (is_object($crontoday)) {
        $crontoday->remove();
    }
    message::removeAll('teleinfo');
    message::add('teleinfo', 'Désinstallation du plugin Téléinfo terminée, vous pouvez de nouveau relever les index à la main ;)');
}
