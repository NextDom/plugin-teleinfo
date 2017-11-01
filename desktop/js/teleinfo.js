
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

$('#bt_stopTeleinfoDaemon').on('click', function() {
    stopTeleinfoDeamon();
});

function stopTeleinfoDeamon() {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
        data: {
            action: "stopDeamon",
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function(data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: 'Le démon a été correctement arrêté : il se relancera automatiquement dans 1 minute', level: 'success'});
        }
    });
}

$('#create_data_teleinfo').on('click', function() {
	document.getElementById("checkbox-autocreate").checked = true;
	$('.eqLogicAction[data-action=save]').click();
});

$('#bt_cout').on('click', function() {
	$('#md_modal').dialog({title: "{{Gestion des coûts}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=cout').dialog('open');
});

$('#bt_info_daemon').on('click', function() {
	$('#md_modal').dialog({title: "{{Informations du modem}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=info_daemon&plugin_id=teleinfo_deamon&slave_id=0').dialog('open');
});

$('.bt_info_external_daemon').on('click', function() {
	var slave_id_tmp = $(this).attr('slave_id');
	$('#md_modal').dialog({title: "{{Informations du modem}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=info_daemon&plugin_id=teleinfo_deamon&slave_id=' + slave_id_tmp).dialog('open');
});


$('#bt_config').on('click', function() {
	$('#md_modal').dialog({title: "{{Configuration}}"});
    $('#md_modal').load('index.php?v=d&p=plugin&ajax=1&id=rfxcom').dialog('open');
});

$('#bt_teleinfoHealth').on('click', function() {	
	$('#md_modal').dialog({title: "{{Santé Téléinformation}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=health').dialog('open');
});


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
	if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
	init(_cmd.id);
	var selRequestType = '';
	var type_of_data = init(_cmd.configuration['type']);
	//alert(type_of_data);
	if(init(_cmd.configuration['type']) == 'stat' || init(_cmd.configuration['type']) == 'panel'){
		selRequestType = '<select style="width : 220px;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="info_conso">';
		selRequestType += '<option value="AUCUN">Aucune</option>';
		selRequestType += '<option value="STAT_YESTERDAY">Conso totale hier</option>';
		selRequestType += '<option value="STAT_YESTERDAY_HP">Conso HP hier</option>';
		selRequestType += '<option value="STAT_YESTERDAY_HC">Conso HC hier</option>';
		selRequestType += '<option value="STAT_TODAY">Conso totale Aujourd\'hui</option>';
		selRequestType += '<option value="STAT_TODAY_HP">Conso HP Aujourd\'hui</option>';
		selRequestType += '<option value="STAT_TODAY_HC">Conso HC Aujourd\'hui</option>';
		selRequestType += '<option value="STAT_MONTH">Conso Mois en cours</option>';
		selRequestType += '<option value="STAT_YEAR">Conso Année en cours</option>';
		selRequestType += '<option value="STAT_LASTMONTH">Conso Mois dernier</option>';
		selRequestType += '<option value="STAT_JAN_HP">Conso Janvier HP</option>';
		selRequestType += '<option value="STAT_FEV_HP">Conso Février HP</option>';
		selRequestType += '<option value="STAT_MAR_HP">Conso Mars HP</option>';
		selRequestType += '<option value="STAT_AVR_HP">Conso Avril HP</option>';
		selRequestType += '<option value="STAT_MAI_HP">Conso Mai HP</option>';
		selRequestType += '<option value="STAT_JUIN_HP">Conso Juin HP</option>';
		selRequestType += '<option value="STAT_JUI_HP">Conso Juillet HP</option>';
		selRequestType += '<option value="STAT_AOU_HP">Conso Août HP</option>';
		selRequestType += '<option value="STAT_SEP_HP">Conso Septembre HP</option>';
		selRequestType += '<option value="STAT_OCT_HP">Conso Octobre HP</option>';
		selRequestType += '<option value="STAT_NOV_HP">Conso Novembre HP</option>';
		selRequestType += '<option value="STAT_DEC_HP">Conso Décembre HP</option>';
		selRequestType += '<option value="STAT_JAN_HC">Conso Janvier HC</option>';
		selRequestType += '<option value="STAT_FEV_HC">Conso Février HC</option>';
		selRequestType += '<option value="STAT_MAR_HC">Conso Mars HC</option>';
		selRequestType += '<option value="STAT_AVR_HC">Conso Avril HC</option>';
		selRequestType += '<option value="STAT_MAI_HC">Conso Mai HC</option>';
		selRequestType += '<option value="STAT_JUIN_HC">Conso Juin HC</option>';
		selRequestType += '<option value="STAT_JUI_HC">Conso Juillet HC</option>';
		selRequestType += '<option value="STAT_AOU_HC">Conso Août HC</option>';
		selRequestType += '<option value="STAT_SEP_HC">Conso Septembre HC</option>';
		selRequestType += '<option value="STAT_OCT_HC">Conso Octobre HC</option>';
		selRequestType += '<option value="STAT_NOV_HC">Conso Novembre HC</option>';
		selRequestType += '<option value="STAT_DEC_HC">Conso Décembre HC</option>';
		selRequestType += '<option value="TENDANCE_DAY">Tendance journalière de consommation</option>';
		selRequestType += '<option value="PPAP_MANUELLE">Conso moy dernière minute</option>';
		selRequestType += '<option value="STAT_MOY_LAST_HOUR">Conso moy dernière heure</option>';
		selRequestType += '</select>';
	}
	else{
	selRequestType = '<select style="width : 220px;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="info_conso">';
	selRequestType += '<option value="BASE">Index (BASE)</option>';
	selRequestType += '<option value="HCHP">Index heures pleines (BLEU)</option>';
 	selRequestType += '<option value="HCHC">Index heures creuses (BLEU)</option>';
	selRequestType += '<option value="PTEC">Tranche tarifaire</option>';
	selRequestType += '<option value="PAPP">Puissance apparente instantanée</option>';
	selRequestType += '<option value="IINST">Intensité instantanée</option>';
	selRequestType += '<option value="IINST1">Intensité instantanée phase 1</option>';
	selRequestType += '<option value="IINST2">Intensité instantanée phase 2</option>';
	selRequestType += '<option value="IINST3">Intensité instantanée phase 3</option>';
	selRequestType += '<option value="IMAX">Intensité maximale</option>';
	selRequestType += '<option value="IMAX1">Intensité maximale phase 1</option>';
	selRequestType += '<option value="IMAX2">Intensité maximale phase 2</option>';
	selRequestType += '<option value="IMAX3">Intensité maximale phase 3</option>';
	selRequestType += '<option value="PMAX">Puissance maximale triphasé</option>';
	selRequestType += '<option value="ADPS">Alerte dépassement</option>';
	selRequestType += '<option value="ADIR1">Alerte dépassement phase 1</option>';
	selRequestType += '<option value="ADIR2">Alerte dépassement phase 2</option>';
	selRequestType += '<option value="ADIR3">Alerte dépassement phase 3</option>';
	selRequestType += '<option value="OPTARIF">Type d\'abonnement</option>';
	selRequestType += '<option value="ISOUSC">Intensité souscrite</option>';
	selRequestType += '<option value="EJPHN">Index Heures normales (EJP)</option>';
	selRequestType += '<option value="EJPHPM">Index Heures de pointe mobile (EJP)</option>';
	selRequestType += '<option value="HHPHC">Horaires heures pleines et creuses</option>';
	selRequestType += '<option value="PPOT">Présence des potentiels (triphasé)</option>';
	selRequestType += '<option value="BBRHCJB">Index Heures Creuses Jour Bleu (EJP)</option>';
	selRequestType += '<option value="BBRHPJB">Index Heures Pleines Jour Bleu (EJP)</option>';
	selRequestType += '<option value="BBRHCJW">Index Heures Creuses Jour Blanc (EJP)</option>';
	selRequestType += '<option value="BBRHPJW">Index Heures Pleines Jour Blanc (EJP)</option>';
	selRequestType += '<option value="BBRHCJR">Index Heures Creuses Jour Rouge (EJP)</option>';
	selRequestType += '<option value="BBRHPJR">Index Heures Pleines Jour Rouge (EJP)</option>';
	selRequestType += '<option value="PEJP">Préavis (30 min avant) (EJP)</option>';
	selRequestType += '<option value="DEMAIN">Couleur lendemain (TEMPO)</option>';
	selRequestType += '<option value="MOTDETAT">Mot d\'état du compteur</option>';
 	selRequestType += '</select>';
	}
	
	if(init(_cmd.configuration['type']) == 'panel'){
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" style="display:none">';
	}else if(init(_cmd.configuration['type']) == 'health'){
		var tr = '';
	}
	else{
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	}
    if(init(_cmd.configuration['type']) != 'health'){   
		tr += '<td>';
        tr += '<span class="cmdAttr expertModeVisible" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}"></td>';
		tr += '</td>';
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" disabled>';
		tr += '<select style="width : 120px;margin-top : 5px;" class="cmdAttr form-control input-sm tooltips" title="{{Numérique pour les indexs et nombres, Autre pour les chaines de caractères (Tranche tarifaire par exemple.}}" data-l1key="subType"><option value="numeric">Numérique</option><option value="binary">Binaire</option><option value="string">Autre</option></select>';
		tr += '</td>';
        tr += '<td>';
        tr +=  selRequestType;
		//tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="margin-top : 5px;">';
		//tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}" style="margin-top : 5px;">';
        tr += '</td>';
        tr += '<td>';
        tr += '<span><input class="cmdAttr" style="display:none" data-l1key="configuration" data-l2key="type" value="' + init(_cmd.configuration['type']) +'"/></span>';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        
		if(init(_cmd.configuration['info_conso']) == 'TENDANCE_DAY'){
			tr += '<span><input type="checkbox" class="cmdAttr tooltips" title="Spécifie si le calcul de la tendance se fait sur la journée entière ou sur la plage jusqu\'à l\'heure actuelle." data-l1key="configuration" data-l2key="type_calcul_tendance"/> {{Journée entière}}<br/></span>';
		}		
		
		tr += '<input class="cmdAttr form-control tooltips input-sm" data-l1key="unite" style="width : 100px;" placeholder="Unité" title="{{Unité de la donnée (Wh, A, kWh...) pour plus d\'informations aller voir le wiki}}">';
				
		tr += '<input style="width : 150px;" class="tooltips cmdAttr form-control expertModeVisible input-sm" data-l1key="cache" data-l2key="lifetime" placeholder="{{Lifetime cache}}">';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Borne minimum de la valeur}}" style="width : 40%;display : inline-block;"> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Borne maximum de la valeur}}" style="width : 40%;display : inline-block;">';
		
		if(init(_cmd.configuration['info_conso']) == 'ADPS' || init(_cmd.configuration['info_conso']) == 'ADIR1' || init(_cmd.configuration['info_conso']) == 'ADIR2' || init(_cmd.configuration['info_conso']) == 'ADIR3'){
			//tr += '<input class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0">';
			tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="margin-top : 5px;">';
			tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}" style="margin-top : 5px;">';
		}
		
        tr += '</td>';
        tr += '<td>';
		if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction tooltips" title="Attention, ne sert qu\'a afficher la dernière valeur reçu." data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
		}
        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';
		
		if (isset(_cmd.configuration.info_conso)) {
        //$('#table_cmd tbody tr:last .cmdAttr[data-l1key=configuration][data-l2key=info_conso]').value(init(_cmd.configuration.info_conso));
        //$('#table_cmd tbody tr:last .cmdAttr[data-l1key=configuration][data-l2key=info_conso]').trigger('change');
		}
	
	    $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
		var tr = $('#table_cmd tbody tr:last');
		if(init(_cmd.unite) == ''){
			if(init(_cmd.configuration['info_conso']) == 'ADPS'){
				tr.find('.cmdAttr[data-l1key=unite]').append("A"); 
				tr.setValues(_cmd, '.cmdAttr');
			}
		}
		else{

		}	
	}
}

$('#addStatToTable').on('click', function() {
	var _cmd = {type: 'info'};
	_cmd.configuration = {'type':'stat'};
	addCmdToTable(_cmd);
});
$('#addDataToTable').on('click', function() {
	var _cmd = {type: 'info'};
	_cmd.configuration = {'type':'data'};
	addCmdToTable(_cmd);
});
