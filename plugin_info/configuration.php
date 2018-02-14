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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

$port = config::byKey('port', 'teleinfo');
?>

<form class="form-horizontal">
    <fieldset>
		<legend><i class="icon loisir-darth"></i> {{Démon}}</legend>
		
		<div class="form-group div_local">
            <label class="col-lg-4 control-label">Port du modem :</label>
            <div class="col-lg-4">
                <select id="select_port" class="configKey form-control" data-l1key="port">
                    <option value="">Aucun</option>
                    <?php
                    foreach (jeedom::getUsbMapping() as $name => $value) {
                        echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                    }
					echo '<option value="serie">Modem Série</option>';
                    ?>
                </select>
				
				<input id="port_serie" class="configKey form-control" data-l1key="modem_serie_addr" style="margin-top:5px;display:none" placeholder="Renseigner le port série (ex : /dev/ttyS0)"/>
				<script>
				$( "#select_port" ).change(function() {
					$( "#select_port option:selected" ).each(function() {
						if($( this ).val() == "serie"){
						 $("#port_serie").show();
						}
						else{
							$("#port_serie").hide();
							}
						});
					
				});
				
				</script>
            </div>
        </div>
		
		<div class="form-group div_local">
            <label class="col-lg-4 control-label">Vitesse : </label>
            <div class="col-lg-4">
				<!--<input id="port_serie" class="configKey form-control" data-l1key="modem_vitesse" style="margin-top:5px;" placeholder="1200"/>-->
				<select class="configKey form-control" id="port_serie" data-l1key="modem_vitesse">
					<option value="">{{Par défaut}}</option>
					<option value="1200">1200</option>
					<option value="2400">2400</option>
					<option value="4800">4800</option>
					<option value="9600">9600</option>
					<option value="19200">19200</option>
					<option value="38400">38400</option>
					<option value="56000">56000</option>
					<option value="115200">115200</option>
				</select>
            </div>
        </div>
		
		<div class="form-group div_local">
            <label class="col-lg-4 control-label">Mode 2 compteurs : </label>
            <div id="div_mode_2_cpt" class="col-lg-4 tooltips" title="{{En cas d'utilisation de 2 compteurs simultanés (Cartelectronic)}}">
				<label class="checkbox-inline"><input id="mode_2_cpt" type="checkbox" class="configKey" data-l1key="2cpt_cartelectronic" />{{Actif}}</label>
            </div>
        </div>
		
		<div class="form-group">
            <label class="col-lg-4 control-label">Debug : </label>
            <div id="div_debug" class="col-lg-4 tooltips" title="{{ Afficher les traces ERDF }}">
				<label class="checkbox-inline"><input id="debug" type="checkbox" class="configKey" data-l1key="debug" />{{On}}</label>
            </div>
        </div>
		
		<div class="form-group">
            <label class="col-lg-4 control-label">Force : </label>
            <div id="div_debug" class="col-lg-4 tooltips" title="{{ Forcer le lancement }}">
				<label class="checkbox-inline"><input id="debug" type="checkbox" class="configKey" data-l1key="force" />{{On}}</label>
            </div>
        </div>
	</fieldset>
	<fieldset>
	<legend><i class="icon loisir-pacman1"></i> {{Versions}}</legend>
		<div class="form-group">
			<label class="col-lg-1 control-label">Core Teleinfo </label>
			<span style="top:6px;" class="col-lg-4"><?php echo config::byKey('teleinfo_core_version','teleinfo'); ?></span>
		</div>
		<div class="form-group">
			<label class="col-lg-1 control-label">Desktop Teleinfo </label>
			<span style="top:6px;" class="col-lg-4"><?php echo config::byKey('teleinfo_desktop_version','teleinfo'); ?></span>
		</div>
		<div class="form-group">
			<label class="col-lg-1 control-label">Mobile Teleinfo </label>
			<span style="top:6px;" class="col-lg-4"><?php echo config::byKey('teleinfo_mobile_version','teleinfo'); ?></span>
		</div>
    </fieldset>
</form>

<script>
		$('#bt_stopTeleinfoDeamon').on('click', function () {
			$.ajax({// fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
				data: {
					action: "stopDeamon",
				},
				dataType: 'json',
				error: function (request, status, error) {
					handleAjaxError(request, status, error);
				},
				success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
					$('#div_alert').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('#div_alert').showAlert({message: 'Le daemon a été correctement arrêté : il se relancera automatiquement dans 1 minute', level: 'success'});
				$('#ul_plugin .li_plugin[data-plugin_id=teleinfo]').click();
			}
			});
		});
		
		$('#bt_restartTeleinfoDeamon').on('click', function () {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
            data: {
                action: "restartDeamon",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Le démon a été correctement (re)démaré}}', level: 'success'});
            $('#ul_plugin .li_plugin[data-plugin_id=teleinfo]').click();
			}
		});
		});
		
		
</script>