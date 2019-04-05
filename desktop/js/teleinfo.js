
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

 var liste_donnees = [{etiquette:"ADCO",name:"Adresse du compteur",unite:""},
{etiquette:"OPTARIF",name:"Option tarifaire choisie",unite:""},
{etiquette:"DATE",name:"Date",unite:""},
{etiquette:"VTIC",name:"Version de la TIC",unite:""},
{etiquette:"ISOUSC",name:"Intensité souscrite",unite:"A"},
{etiquette:"BASE",name:"Index Base",unite:"Wh"},
{etiquette:"HCHC",name:"Index Heures Creuses",unite:"Wh"},
{etiquette:"HCHP",name:"Index Heures Pleines",unite:"Wh"},
{etiquette:"EJPHN",name:"Index EJP Heures Normales",unite:"Wh"},
{etiquette:"EJPHPM",name:"Index EJP Heures de Pointe Mobile",unite:"Wh"},
{etiquette:"BBRHCJB",name:"Index Tempo Heures Creuses Jours Bleus",unite:"Wh"},
{etiquette:"BBRHPJB",name:"Index Tempo Heures Pleines Jours Bleus",unite:"Wh"},
{etiquette:"BBRHCJW",name:"Index Tempo Heures Creuses Jours Blancs",unite:"Wh"},
{etiquette:"BBRHPJW",name:"Index Tempo Heures Pleines Jours Blancs",unite:"Wh"},
{etiquette:"BBRHCJR",name:"Index Tempo Heures Creuses Jours Rouges",unite:"Wh"},
{etiquette:"BBRHPJR",name:"Index Tempo Heures Pleines Jours Rouges",unite:"Wh"},
{etiquette:"PEJP",name:"Préavis Début EJP (30 min)",unite:"min"},
{etiquette:"PTEC",name:"Période Tarifaire en cours",unite:""},
{etiquette:"DEMAIN",name:"Couleur du lendemain",unite:""},
{etiquette:"IINST",name:"Intensité Instantanée",unite:"A"},
{etiquette:"IINST1",name:"Intensité Instantanée phases 1",unite:"A"},
{etiquette:"IINST2",name:"Intensité Instantanée phases 2",unite:"A"},
{etiquette:"IINST3",name:"Intensité Instantanée phases 3",unite:"A"},
{etiquette:"ADPS",name:"Avertissement de Dépassement De Puissance Souscrite",unite:"A"},
{etiquette:"ADIR1",name:"Avertissement de Dépassement d'intensité de réglage phase 1",unite:"A"},
{etiquette:"ADIR2",name:"Avertissement de Dépassement d'intensité de réglage phase 2",unite:"A"},
{etiquette:"ADIR3",name:"Avertissement de Dépassement d'intensité de réglage phase 3",unite:"A"},
{etiquette:"IMAX",name:"Intensité maximale appelée",unite:"A"},
{etiquette:"IMAX1",name:"Intensité maximale phase 1",unite:"A"},
{etiquette:"IMAX2",name:"Intensité maximale phase 2",unite:"A"},
{etiquette:"IMAX3",name:"Intensité maximale phase 3",unite:"A"},
{etiquette:"PAPP",name:"Puissance apparente",unite:"VA"},
{etiquette:"HHPHC",name:"Horaire Heures Pleines Heures Creuses",unite:""},
{etiquette:"MOTDETAT",name:"Mot d'état du compteur",unite:""},
{etiquette:"PMAX",name:"Puissance maximale triphasée atteinte",unite:"W"},
{etiquette:"PPOT",name:"Présence des potentiels",unite:""},
{etiquette:"ADSC",name:"Adresse Secondaire du Compteur",unite:""},
{etiquette:"NGTF",name:"Nom du calendrier tarifaire fournisseur",unite:""},
{etiquette:"LTARF",name:"Libellé tarif fournisseur en cours",unite:""},
{etiquette:"EAST",name:"Energie active soutirée totale",unite:"Wh"},
{etiquette:"EASF01",name:"Energie active soutirée Fournisseur, index 01",unite:"Wh"},
{etiquette:"EASF02",name:"Energie active soutirée Fournisseur, index 02",unite:"Wh"},
{etiquette:"EASF03",name:"Energie active soutirée Fournisseur, index 03",unite:"Wh"},
{etiquette:"EASF04",name:"Energie active soutirée Fournisseur, index 04",unite:"Wh"},
{etiquette:"EASF05",name:"Energie active soutirée Fournisseur, index 05",unite:"Wh"},
{etiquette:"EASF06",name:"Energie active soutirée Fournisseur, index 06",unite:"Wh"},
{etiquette:"EASF07",name:"Energie active soutirée Fournisseur, index 07",unite:"Wh"},
{etiquette:"EASF08",name:"Energie active soutirée Fournisseur, index 08",unite:"Wh"},
{etiquette:"EASF09",name:"Energie active soutirée Fournisseur, index 09",unite:"Wh"},
{etiquette:"EASF10",name:"Energie active soutirée Fournisseur, index 10",unite:"Wh"},
{etiquette:"EASD01",name:"Energie active soutirée Distributeur, index 01",unite:"Wh"},
{etiquette:"EASD02",name:"Energie active soutirée Distributeur, index 02",unite:"Wh"},
{etiquette:"EASD03",name:"Energie active soutirée Distributeur, index 03",unite:"Wh"},
{etiquette:"EASD04",name:"Energie active soutirée Distributeur, index 04",unite:"Wh"},
{etiquette:"EAIT",name:"Energie active injectée totale",unite:"Wh"},
{etiquette:"ERQ1",name:"Energie réactive Q1 totale",unite:"VArh"},
{etiquette:"ERQ2",name:"Energie réactive Q2 totale",unite:"VArh"},
{etiquette:"ERQ3",name:"Energie réactive Q3 totale",unite:"VArh"},
{etiquette:"ERQ4",name:"Energie réactive Q4 totale",unite:"VArh"},
{etiquette:"IRMS1",name:"Courant efficace, phase 1",unite:"A"},
{etiquette:"IRMS2",name:"Courant efficace, phase 2",unite:"A"},
{etiquette:"IRMS3",name:"Courant efficace, phase 3",unite:"A"},
{etiquette:"URMS1",name:"Tension efficace, phase 1",unite:"V"},
{etiquette:"URMS2",name:"Tension efficace, phase 2",unite:"V"},
{etiquette:"URMS3",name:"Tension efficace, phase 3",unite:"V"},
{etiquette:"PREF",name:"Puissance app. de référence",unite:"kVA"},
{etiquette:"PCOUP",name:"Puissance app. de coupure",unite:"kVA"},
{etiquette:"SINSTS",name:"Puissance app. Instantanée soutirée",unite:"VA"},
{etiquette:"SINSTS1",name:"Puissance app. Instantanée soutirée phase 1",unite:"VA"},
{etiquette:"SINSTS2",name:"Puissance app. Instantanée soutirée phase 2",unite:"VA"},
{etiquette:"SINSTS3",name:"Puissance app. Instantanée soutirée phase 3",unite:"VA"},
{etiquette:"SMAXSN",name:"Puissance app. max. soutirée n",unite:"VA"},
{etiquette:"SMAXSN1",name:"Puissance app. max. soutirée n phase 1",unite:"VA"},
{etiquette:"SMAXSN2",name:"Puissance app. max. soutirée n phase 2",unite:"VA"},
{etiquette:"SMAXSN3",name:"Puissance app. max. soutirée n phase 3",unite:"VA"},
{etiquette:"SMAXSN-1",name:"Puissance app max. soutirée n-1",unite:"VA"},
{etiquette:"SMAXSN1-1",name:"Puissance app max. soutirée n-1 phase 1",unite:"VA"},
{etiquette:"SMAXSN2-1",name:"Puissance app max. soutirée n-1 phase 2",unite:"VA"},
{etiquette:"SMAXSN3-1",name:"Puissance app max. soutirée n-1 phase 3",unite:"VA"},
{etiquette:"SINSTI",name:"Puissance app. Instantanée injectée",unite:"VA"},
{etiquette:"SMAXIN",name:"Puissance app. max. injectée n",unite:"VA"},
{etiquette:"SMAXIN-1",name:"Puissance app max. injectée n-1",unite:"VA"},
{etiquette:"CCASN",name:"Point n de la courbe de charge active soutirée",unite:"W"},
{etiquette:"CCASN-1",name:"Point n-1 de la courbe de charge active soutirée",unite:"W"},
{etiquette:"CCAIN",name:"Point n de la courbe de charge active injectée",unite:"W"},
{etiquette:"CCAIN-1",name:"Point n-1 de la courbe de charge active injectée",unite:"W"},
{etiquette:"UMOY1",name:"Tension moy. ph. 1",unite:"V"},
{etiquette:"UMOY2",name:"Tension moy. ph. 2",unite:"V"},
{etiquette:"UMOY3",name:"Tension moy. ph. 3",unite:"V"},
{etiquette:"STGE",name:"Registre de Statuts",unite:""},
{etiquette:"DPM1",name:"Début Pointe Mobile 1",unite:""},
{etiquette:"FPM1",name:"Fin Pointe Mobile 1",unite:""},
{etiquette:"DPM2",name:"Début Pointe Mobile 2",unite:""},
{etiquette:"FPM2",name:"Fin Pointe Mobile 2",unite:""},
{etiquette:"DPM3",name:"Début Pointe Mobile 3",unite:""},
{etiquette:"FPM3",name:"Fin Pointe Mobile 3",unite:""},
{etiquette:"MSG1",name:"Message court",unite:""},
{etiquette:"MSG2",name:"Message Ultra court",unite:""},
{etiquette:"PRM",name:"PRM",unite:""},
{etiquette:"RELAIS",name:"Relais",unite:""},
{etiquette:"NTARF",name:"Numéro de l’index tarifaire en cours",unite:""},
{etiquette:"NJOURF",name:"Numéro du jour en cours calendrier fournisseur",unite:""},
{etiquette:"NJOURF+1",name:"Numéro du prochain jour calendrier fournisseur",unite:""},
{etiquette:"PJOURF+1",name:"Profil du prochain jour calendrier fournisseur",unite:""},
{etiquette:"PPOINTE",name:"Profil du prochain jour de pointe",unite:""}];


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

$('#bt_options').on('click', function() {
    $('#md_modal').dialog({title: "{{Options}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=options').dialog('open');
});

$('#bt_info_daemon').on('click', function() {
    $('#md_modal').dialog({title: "{{Informations du modem}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=info_daemon&plugin_id=teleinfo_deamon_conso&slave_id=0').dialog('open');
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

$('#btTeleinfoHealth').on('click', function() {
    $('#md_modal').dialog({title: "{{Santé Téléinformation}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=health').dialog('open');
});

$('#btTeleinfoMaintenance').on('click', function() {
    $('#md_modal').dialog({title: "{{Maintenance Téléinformation}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=maintenance').dialog('open');
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
        selRequestType = '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="info_conso">';
        selRequestType += '<option value="AUCUN">Aucune</option>';
        selRequestType += '<option value="STAT_YESTERDAY">Conso totale hier</option>';
        selRequestType += '<option value="STAT_YESTERDAY_HP">Conso HP hier</option>';
        selRequestType += '<option value="STAT_YESTERDAY_HC">Conso HC hier</option>';
        selRequestType += '<option value="STAT_YESTERDAY_PROD">Production hier</option>';
        selRequestType += '<option value="STAT_TODAY">Conso totale Aujourd\'hui</option>';
        selRequestType += '<option value="STAT_TODAY_HP">Conso HP Aujourd\'hui</option>';
        selRequestType += '<option value="STAT_TODAY_HC">Conso HC Aujourd\'hui</option>';
        selRequestType += '<option value="STAT_TODAY_PROD">Production Aujourd\'hui</option>';
        selRequestType += '<option value="STAT_MONTH">Conso Mois en cours</option>';
        selRequestType += '<option value="STAT_MONTH_PROD">Production Mois en cours</option>';
        selRequestType += '<option value="STAT_MONTH_LAST_YEAR">Conso Mois en cours année précédente</option>';
        selRequestType += '<option value="STAT_YEAR_LAST_YEAR">Conso Année précédente au même jour</option>';
        selRequestType += '<option value="STAT_YEAR">Conso Année en cours</option>';
        selRequestType += '<option value="STAT_YEAR_PROD">Production Année en cours</option>';
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
        selRequestType = '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="info_conso">';
        liste_donnees.forEach(function(element) {
            selRequestType += '<option value="' + element.etiquette + '">' + element.name + '</option>';
        });
        selRequestType += '</select>';
    }

    if(init(_cmd.configuration['type']) == 'panel'){
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" style="display:none">';
    }else if(init(_cmd.configuration['type']) == 'health'){
        var tr = '';
        if(_cmd.configuration['NGTF']){
            $("#typeAbonnement").html(_cmd.configuration['NGTF'].value);
        }
        else if (_cmd.configuration['OPTARIF']){
            $("#typeAbonnement").html(_cmd.configuration['OPTARIF'].value);
        }
    }
    else if (init(_cmd.configuration['type']) == 'stat'){
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    }
    else{
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    }
    if(init(_cmd.configuration['type']) != 'health'){
        tr += '<td>';
        tr += '<span class="cmdAttr expertModeVisible" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}"></td>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" disabled>';
        tr += '<select style="margin-top : 5px;" class="cmdAttr form-control input-sm tooltips" title="{{Numérique pour les indexs et nombres, Autre pour les chaines de caractères (Tranche tarifaire par exemple.}}" data-l1key="subType"><option value="numeric">Numérique</option><option value="binary">Binaire</option><option value="string">Autre</option></select>';
        tr += '</td>';
        tr += '<td>';
        tr +=  selRequestType;
        tr += '</td>';
        tr += '<td>';
        tr += '<span><input class="cmdAttr" style="display:none" data-l1key="configuration" data-l2key="type" value="' + init(_cmd.configuration['type']) +'"/></span>';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';

        if(init(_cmd.configuration['info_conso']) == 'TENDANCE_DAY'){
            tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr tooltips checkbox-inline" title="Spécifie si le calcul de la tendance se fait sur la journée entière ou sur la plage jusqu\'à l\'heure actuelle." data-l1key="configuration" data-l2key="type_calcul_tendance"/> {{Journée entière}}</label></span>';
        }

        tr += '</br><input class="cmdAttr form-control tooltips input-sm" data-l1key="unite" style="margin-left:10px;width: 20%;display: inline-block;" placeholder="Unité" title="{{Unité de la donnée (Wh, A, kWh...) pour plus d\'informations aller voir le wiki}}">';

        tr += '<input style="margin-left:10px;width: 20%;display: inline-block;" class="tooltips cmdAttr form-control expertModeVisible input-sm" data-l1key="cache" data-l2key="lifetime" placeholder="{{Lifetime cache}}">';
        tr += '<input style="margin-left:10px;width: 20%;display: inline-block;" class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Borne minimum de la valeur}}" > ';
        tr += '<input style="margin-left:10px;width: 20%;display: inline-block;" class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Borne maximum de la valeur}}" >';

        if(init(_cmd.configuration['info_conso']) == 'ADPS' || init(_cmd.configuration['info_conso']) == 'ADIR1' || init(_cmd.configuration['info_conso']) == 'ADIR2' || init(_cmd.configuration['info_conso']) == 'ADIR3'){
            //tr += '<input class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0">';
            tr += '</br><input style="margin-left:10px;width: 20%;display: inline-block;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}">';
            tr += '<input style="margin-left:10px;width: 20%;display: inline-block;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}">';
        }

        tr += '</td>';
        tr += '<td>';

        tr += '<div class="input-group pull-right" style="display:inline-flex"><span class="input-group-btn">';

        if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i></a>';
        tr += '<a class="btn btn-default btn-xs cmdAction tooltips" title="Attention, ne sert qu\'a afficher la dernière valeur reçu." data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '<a class="btn btn-danger btn-xs cmdAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i></a> ';
        tr += '</span></div>';
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
