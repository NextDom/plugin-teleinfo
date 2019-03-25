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
$core_version = '1.1.1';
if (!file_exists(dirname(__FILE__) . '/info.json')) {
    log::add('teleinfo','warning','Pas de fichier info.json');
}
$data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
if (!is_array($data)) {
    log::add('teleinfo','warning','Impossible de décoder le fichier info.json');
}
try {
    $core_version = $data['pluginVersion'];
} catch (\Exception $e) {
    log::add('teleinfo','warning','Impossible de récupérer la version.');
}
?>

<form class="form-horizontal">
    <fieldset>
        <legend><i class="icon fa fa-bolt"></i> {{Consommation électrique}}</legend>
        <div class="form-group">
            <label class="col-lg-4 control-label">Compteur Linky <sup><i class="fa fa-question-circle tooltips" title="{{Veuillez regarder la documentation pour identifier votre compteur}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <div id="div_linky" class="col-lg-4 tooltips" title="{{ Veuillez regarder la documentation pour identifier votre compteur }}">
                <input type="checkbox" id="linky" class="configKey" data-l1key="linky" placeholder="{{}}"/>
                <label for="linky">  </label>
                <label id="label_linky" style="color:red;margin-left:100px;margin-top:-15px;display:none">Attention, assurez vous que votre compteur soit en mode standard. Aucune idée ? Se reporter à la documentation.</label>
                <script>
                $( "#linky" ).change(function() {
                        if($( this ).value() == "1"){
                            $("#label_linky").show();
                        }
                        else{
                            $("#label_linky").hide();
                        }
                });
                </script>
            </div>
        </div>
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
                    <option style="font-weight: bold;" value="1200">1200</option>
                    <option value="2400">2400</option>
                    <option value="4800">4800</option>
                    <option style="font-weight: bold;" value="9600">9600</option>
                    <option value="19200">19200</option>
                    <option value="38400">38400</option>
                    <option value="56000">56000</option>
                    <option value="115200">115200</option>
                </select>
            </div>
        </div>

        <div class="form-group div_local">
            <label class="col-lg-4 control-label">Mode 2 compteurs <sup><i class="fa fa-question-circle tooltips" title="{{Si vous utilisez le modem Cartelectronic en mode 2 compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <div id="div_mode_2_cpt" class="col-lg-4 tooltips" title="{{Seulement en cas d'utilisation de 2 compteurs simultanés (Cartelectronic)}}">
                <input type="checkbox" id="mode_2_cpt" class="configKey" data-l1key="2cpt_cartelectronic" placeholder="{{Actif}}"/>
                <label for="mode_2_cpt">  </label>
                <!--<label class="checkbox-inline"><input id="mode_2_cpt" type="checkbox" class="configKey" data-l1key="2cpt_cartelectronic" />{{Actif}}</label>-->
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">Bloquer la création automatique des compteurs <sup><i class="fa fa-question-circle tooltips" title="{{Interdire la création automatique des nouveaux compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <div id="div_auth_new_object" class="col-lg-4 tooltips"  title="{{Interdire la création automatique des nouveaux compteurs}}">
                <!--<label class="checkbox-inline"><input id="force" type="checkbox" class="configKey" data-l1key="force" />{{Oui}}</label>-->
                <input type="checkbox" id="auth_new_object" class="configKey" data-l1key="createNewADCO" placeholder="{{Actif}}"/>
                <label for="auth_new_object">  </label>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><i class="icon fa fa-envira"></i> {{Production électrique}} <i class="fa fa-plus-circle" data-toggle="collapse" href="#ProductionCollapse" role="button" aria-expanded="false" aria-controls="ProductionCollapse"></i></legend>
        <div class="collapse" id="ProductionCollapse">
            <div class="form-group div_local">
                <label class="col-lg-4 control-label">Activer le modem de production électrique <sup><i class="fa fa-question-circle tooltips" title="{{En cas d'utilisation d'un second modem téléinfo}}" style="font-size : 1em;color:grey;"></i></sup></label>
                <div id="div_activation_production" class="col-lg-4 tooltips" title="{{En cas d'utilisation d'un second modem téléinfo}}">
                    <input type="checkbox" id="activation_production" class="configKey" data-l1key="activation_production" placeholder="{{Activer}}"/>
                    <label for="activation_production"></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-4 control-label">Compteur Linky <sup><i class="fa fa-question-circle tooltips" title="{{Veuillez regarder la documentation pour identifier votre compteur}}" style="font-size : 1em;color:grey;"></i></sup></label>
                <div id="div_linky_prod" class="col-lg-4 tooltips" title="{{ Veuillez regarder la documentation pour identifier votre compteur }}">
                    <input type="checkbox" id="linky_prod" class="configKey" data-l1key="linky_prod" placeholder="{{}}"/>
                    <label for="linky_prod">  </label>
                    <label id="label_linky_prod" style="color:red;margin-left:100px;margin-top:-15px;display:none">Attention, assurez vous que votre compteur soit en mode standard. Aucune idée ? Se reporter à la documentation.</label>
                    <script>
                    $( "#linky_prod" ).change(function() {
                            if($( this ).value() == "1"){
                                $("#label_linky_prod").show();
                            }
                            else{
                                $("#label_linky_prod").hide();
                            }
                    });
                    </script>
                </div>
            </div>
            <div class="form-group div_local">
                <label class="col-lg-4 control-label">Port du modem de production :</label>
                <div class="col-lg-4">
                    <select id="select_port_production" class="configKey form-control" data-l1key="port_production">
                        <option value="">Aucun</option>
                        <?php
                        foreach (jeedom::getUsbMapping() as $name => $value) {
                            echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                        }
                        echo '<option value="serie">Modem Série</option>';
                        ?>
                    </select>
                    <input id="port_serie_production" class="configKey form-control" data-l1key="modem_serie_production_addr" style="margin-top:5px;display:none" placeholder="Renseigner le port série (ex : /dev/ttyS0)"/>
                    <script>
                    $( "#select_port_production" ).change(function() {
                        $( "#select_port_production option:selected" ).each(function() {
                            if($( this ).val() == "serie"){
                                $("#port_serie_production").show();
                            }
                            else{
                                $("#port_serie_production").hide();
                            }
                        });
                    });
                    </script>
                </div>
            </div>
            <div class="form-group div_local">
                <label class="col-lg-4 control-label">Vitesse : </label>
                <div class="col-lg-4">
                    <select class="configKey form-control" id="port_serie_production" data-l1key="modem_production_vitesse">
                        <option value="">{{Par défaut}}</option>
                        <option style="font-weight: bold;" value="1200">1200</option>
                        <option value="2400">2400</option>
                        <option value="4800">4800</option>
                        <option style="font-weight: bold;" value="9600">9600</option>
                        <option value="19200">19200</option>
                        <option value="38400">38400</option>
                        <option value="56000">56000</option>
                        <option value="115200">115200</option>
                    </select>
                </div>
            </div>
            <div class="form-group div_local" style="display:none">
                <label class="col-lg-4 control-label">Mode 2 compteurs <sup><i class="fa fa-question-circle tooltips" title="{{Si vous utilisez le modem Cartelectronic en mode 2 compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
                <div id="div_mode_2_cpt_production" class="col-lg-4 tooltips" title="{{Seulement en cas d'utilisation de 2 compteurs simultanés (Cartelectronic)}}">
                    <input type="checkbox" id="mode_2_cpt_production" class="configKey" data-l1key="2cpt_cartelectronic_production" placeholder="{{Actif}}"/>
                    <label for="mode_2_cpt_production"> Actif </label>
                    <!--<label class="checkbox-inline"><input id="mode_2_cpt" type="checkbox" class="configKey" data-l1key="2cpt_cartelectronic" />{{Actif}}</label>-->
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
    <legend><i class="icon loisir-pacman1"></i> {{Version}}</legend>
        <div class="form-group">
            <label class="col-lg-4 control-label">Core Teleinfo <sup><i class="fa fa-question-circle tooltips" title="{{C'est la version du programme de connexion au modem}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <span style="top:6px;" class="col-lg-4"><?php echo $core_version; ?></span>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">Diagnostic <sup><i class="fa fa-question-circle tooltips" title="{{Rechercher la cause d'un disfonctionnement}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <span style="top:6px;" class="col-lg-4"><a class="btn btn-sm btn-info" id="btn_diagnostic" style="position:relative;top:-5px;"><i class="divers-svg"></i> Démarrer</a></span>
        </div>
        <div class="form-group">
    	    <label class="col-lg-4 control-label">{{Adresse IP socket interne (modification dangereuse)}}</label>
    	    <div class="col-lg-2">
    	        <input class="configKey form-control" data-l1key="sockethost" placeholder="{{127.0.0.1}}" />
    	    </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Port socket interne (modification dangereuse)}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="socketport" placeholder="{{55062}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Cycle (s)}}</label>
            <div class="col-sm-2">
                <input class="configKey form-control" data-l1key="cycle" placeholder="{{0.3}}"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Cycle sommeil acquisition teleinfo (s)}}</label>
            <div class="col-sm-2">
                <input class="configKey form-control" data-l1key="cycle_sommeil" placeholder="{{0.5}}"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-4">
        		<a class="btn btn-warning changeLogLive" data-log="logdebug"><i class="fa fa-cogs"></i> {{Mode debug forcé temporaire}}</a>
        		<a class="btn btn-success changeLogLive" data-log="lognormal"><i class="fa fa-paperclip"></i> {{Remettre niveau de log normal}}</a>
        	</div>
        </div>
    </fieldset>
</form>

<script>
        $('#btn_diagnostic').on('click',function(){
            $('#md_modal').dialog({title: "{{Diagnostique de résolution d'incident}}"});
            $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=diagnostic').dialog('open');
        });

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

        $('.changeLogLive').on('click', function () {
	           $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
                data: {
                    action: "changeLogLive",
    				level : $(this).attr('data-log')
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
                    $('#div_alert').showAlert({message: '{{Réussie}}', level: 'success'});
                }
            });
    });

</script>

<style type="text/css">
[type="checkbox"][class="configKey"]:not(:checked),
    [type="checkbox"][class="configKey"]:checked {
        display:none;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label,
    [type="checkbox"][class="configKey"]:checked + label {
        position: relative;
        padding-left: 75px;
        cursor: pointer;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:before,
    [type="checkbox"][class="configKey"]:checked + label:before,
    [type="checkbox"][class="configKey"]:not(:checked) + label:after,
    [type="checkbox"][class="configKey"]:checked + label:after {
        content: '';
        position: absolute;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:before,
    [type="checkbox"][class="configKey"]:checked + label:before {
        left:0; top: -3px;
        width: 65px; height: 30px;
        background: #DDDDDD;
        border-radius: 15px;
        -webkit-transition: background-color .2s;
        -moz-transition: background-color .2s;
        -ms-transition: background-color .2s;
        transition: background-color .2s;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:after,
    [type="checkbox"][class="configKey"]:checked + label:after {
        width: 20px; height: 20px;
        -webkit-transition: all .2s;
        -moz-transition: all .2s;
        -ms-transition: all .2s;
        transition: all .2s;
        border-radius: 50%;
        background: #d9534f;
        top: 2px; left: 5px;
    }

    /* on checked */
    [type="checkbox"][class="configKey"]:checked + label:before {
        background:#DDDDDD;
    }
    [type="checkbox"][class="configKey"]:checked + label:after {
        background: #62c462;
        top: 2px; left: 40px;
    }

    [type="checkbox"][class="configKey"]:checked + label .ui,
    [type="checkbox"][class="configKey"]:not(:checked) + label .ui:before,
    [type="checkbox"][class="configKey"]:checked + label .ui:after {
        position: absolute;
        left: 6px;
        width: 65px;
        border-radius: 15px;
        font-size: 14px;
        font-weight: bold;
        line-height: 22px;
        -webkit-transition: all .2s;
        -moz-transition: all .2s;
        -ms-transition: all .2s;
        transition: all .2s;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label .ui:before {
        content: "no";
        left: 32px
    }
    [type="checkbox"][class="configKey"]:checked + label .ui:after {
        content: "yes";
        color: #62c462;
    }
    [type="checkbox"][class="configKey"]:focus + label:before {
        border: 1px dashed #777;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        -ms-box-sizing: border-box;
        box-sizing: border-box;
        margin-top: -1px;
    }
</style>
