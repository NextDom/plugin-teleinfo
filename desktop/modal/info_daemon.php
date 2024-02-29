﻿<?php

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
if (!class_exists(init('plugin'))) {
	die();
}
if (init('slave_id') == 0) {
	$plugin = plugin::byId(init('plugin'));
	//$deamon_info = $plugin->deamon_info();
} else {
	$jeeNetwork = jeeNetwork::byId(init('slave_id'));
	//$deamon_info = $jeeNetwork->sendRawRequest('plugin::deamonInfo', array('plugin_id' => init('plugin_id')));
}
	// uniquement si on est en version 4.4 ou supérieur
	$jeedomVersion  = jeedom::version() ?? '0';
	$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');

sendVarToJS('versionsup', json_encode($displayInfoValue));
sendVarToJs('logfile', init('plugin_id'));
sendVarToJs('slave_id', init('slave_id'));
?>

<div id='div_updatePluginDependancyAlert' style="display: none;"></div>

<a class="btn btn-warning pull-right" data-state="1" id="bt_pluginDeamonLogStopStart"><i class="fas fa-pause"></i> {{Pause}}</a>
<input class="form-control pull-right" id="in_pluginDeamonLogSearch" style="width : 300px;" placeholder="{{Rechercher}}" />
<br/><br/><br/>
<pre id='pre_pluginDeamonLogUpdate' style='overflow: auto; height: 90%;with:90%;'></pre>


<script>
		if (versionsup == "true"){
			if(slave_id == 0){
				jeedom.log.autoUpdateDelta({
					log : logfile,
					display : $('#pre_pluginDeamonLogUpdate'),
					search : $('#in_pluginDeamonLogSearch'),
					control : $('#bt_pluginDeamonLogStopStart'),
				});
			}else{
				jeedom.log.autoUpdateDelta({
					log : logfile,
					slaveId :slave_id,
					display : $('#pre_pluginDeamonLogUpdate'),
					search : $('#in_pluginDeamonLogSearch'),
					control : $('#bt_pluginDeamonLogStopStart'),
				});
			};
		} else {
			if(slave_id == 0){
				jeedom.log.autoupdate({
					log : logfile,
					display : $('#pre_pluginDeamonLogUpdate'),
					search : $('#in_pluginDeamonLogSearch'),
					control : $('#bt_pluginDeamonLogStopStart'),
				});
			}else{
				jeedom.log.autoupdate({
					log : logfile,
					slaveId :slave_id,
					display : $('#pre_pluginDeamonLogUpdate'),
					search : $('#in_pluginDeamonLogSearch'),
					control : $('#bt_pluginDeamonLogStopStart'),
				});
			};
		};
</script>
