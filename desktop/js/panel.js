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

//var globalIndexChart;
var versionEnCours = "22/03/2023 07:50";
var globalEqLogic = $( "#eqlogic_select option:selected" ).val();
var isCoutVisible = false;
var puissanceSeries = [];
var commandesPuissance = [];
var commandesPuissanceCout = [];
var commandesStat = [];
var commandesStatCout = [];
var commandesStatIndex = [];
var commandesStatCoutIndex = [];
var dailyHistoryChart = [];
var tableCouts = [];
var graphTempDaily = false;
var graphTempMonthly = false;
var graphTempAnnualy = false;
faireTotHpHc = false;


$(".in_datepicker").datepicker();

$('#bt_teleinfoPanelSante').on('click', function() {
    $('#md_modal').dialog({title: "{{Santé}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=panel_sante').dialog('open');
});

$('#bt_teleinfoCout').on('click', function() {
    if (newIndex){
        if (prodEtConso == 1){
            $('.PRODCOUT').show();
            $('.PRODCONSO').hide();
        }
        $('.index').hide();
        $('.couts').show();
        $('.conso').hide();
        $('.coutsgraph').show();
        $('.indexgraph').hide();
    }else{
        if (isCoutVisible === false){
            isCoutVisible = true;
            $('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=month]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=year]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=day]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]').show();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=all]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=month]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=year]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=day]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yesterday]').show();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=all]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=month]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYearPartial]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=year]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=day]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yesterday]').show();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=all]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]').show();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=all]').show();
            if (prodEtConso == 1){
                $('.PRODCOUT').show();
            }
        }else{
            isCoutVisible = false;
            $('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=month]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=year]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=day]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]').hide();
            $('.teleinfoAttr[data-l1key=cout][data-l2key=all]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=month]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=year]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=day]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=yesterday]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=all]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=month]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYearPartial]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=year]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=day]').hide();
            $('.teleinfoAttr[data-l1key=coutHC][data-l2key=yesterday]').hide();
            $('.teleinfoAttr[data-l1key=coutHP][data-l2key=all]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]').hide();
            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=all]').hide();
            $('.PRODCOUT').hide();
        }
    }
});

$('#bt_teleinfoConso').on('click', function() {
    if (newIndex){
        if (prodEtConso == 1){
            $('.PRODCOUT').hide();
            $('.PRODCONSO').show();
        }
        $('.couts').hide();
        $('.conso').show();
        $('.index').show();
        $('.coutsgraph').hide();
        $('.indexgraph').show();
    }
});

$('#bt_teleinfoTout').on('click', function() {
    if (newIndex){
        if (prodEtConso == 1){
            $('.PRODCOUT').show();
            $('.PRODCONSO').show();
        }
        $('.couts').show();
        $('.conso').show();
        $('.index').show();
        $('.coutsgraph').show();
        $('.indexgraph').show();
    }
});

$( "#eqlogic_select" ).change(function() {
    globalEqLogic = $( "#eqlogic_select option:selected" ).val();
    initHistoryTrigger();
    //drawStackColumnChart('div_graphGlobalIndex', null);
    loadData();
});

$('#bt_validChangeDate').on('click',function(){
    graphTempDaily = false;
    graphTempMonthly = false;
    graphTempAnnualy = false;
    puissanceSeries = [];
    //console.log($('#div_graphGlobalIndex').attr("cmd_id"));
    $.each( commandesPuissance, function( key, value ) {
        getObjectHistory('div_graphGlobalPower', 'Simple', {'id': value.id, 'name': value.name}, value.color, 'refresh');
    });
    $.each( commandesStatIndex, function( key, value ) {
        getDailyHistory('div_graphGlobalJournalier',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
        getAnnualHistory('div_graphGlobalAnnual',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
        getMonthlyHistory('div_graphGlobalIndex',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
    });
    $.each( commandesStatCoutIndex, function( key, value ) {
        getDailyHistory('div_graphGlobalJournalierCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
        getAnnualHistory('div_graphGlobalAnnualCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
        getMonthlyHistory('div_graphGlobalIndexCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
    });
    $.each( commandesStat, function( key, value ) {
        getDailyHistory('div_graphGlobalJournalier',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
        getAnnualHistory('div_graphGlobalAnnual',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
        getMonthlyHistory('div_graphGlobalIndex',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie);
    });
    $.each( commandesStatCout, function( key, value ) {
        getDailyHistory('div_graphGlobalJournalierCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
        getAnnualHistory('div_graphGlobalAnnualCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
        getMonthlyHistory('div_graphGlobalIndexCout',{'id': value.id, 'name': value.name}, value.color, value.stackGraph, value.diviseur, value.serie, value.cout);
    });
    $.each( commandesPuissanceCout, function( key, value ) {
        getObjectHistory('div_graphGlobalPowerCout', 'cout', {'id': value.id, 'name': value.name}, value.color, 'refresh');
    });
    $.each( commandesStat, function( key, value ) {
        getDailyHistory(value.graph, {'id': value.id, 'name': value.name} , value.color, value.stackGraph, value.diviseur);
    });
});
initHistoryTrigger();

//displayTeleinfo(object_id);
//drawStackColumnChart('div_graphGlobalIndex', null);

loadData();

function sleep(milliseconds) {
    const date = Date.now();
    let currentDate = null;
    do {
    currentDate = Date.now();
    } while (currentDate - date < milliseconds);
}

function loadData(){
    $.ajax({
        type: 'POST',
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
            action: 'getInformation',
            eqLogic_id: globalEqLogic,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            console.log("[date du fichier panel.js] => " + versionEnCours)
            console.log("[loadData] Objet téléinfo récupéré : " + globalEqLogic);
            if (data.state != 'ok') {
                $.fn.showAlert({message: data.result, level: 'danger'});
                return;
            }
            var compteurProd = false;
            prodEtConso = false;
            var HCHP = false;
            //var color = '#7cb5ec';
            var indexCout = 0;
            newIndex = false;
            let index = [];
            for (i=1;i<11;i++){
                index[i] = false;
            }
            var partiAvant = false;
            var coutIndex;
            var w;
            var j;
            var i;
            var nomIndex;
            var commande;
            var consommation;
            var colori;
            if (data.result[globalEqLogic].configuration.ActivationProduction == 1){
                prodEtConso = true;
            }
            console.log("[loadData] Nom de l'objet => " + data.result[globalEqLogic].name);

            console.log("[si c'est prod = 1 ] => " + data.result[globalEqLogic].configuration.ActivationProduction);
            
            console.log("[si c'est HC HP = 1 ] => " + data.result[globalEqLogic].configuration.HCHP);

            console.log("[loadData Nouveaux index? ] => " + data.result[globalEqLogic].configuration.newIndex);

            if (prodEtConso ==1){
                $('.PRODCOUT').show();
                $('.PRODUCTION').show();
                $('.PRODCONSO').show();
            }else{
                $('.PRODCOUT').hide();
                $('.PRODUCTION').hide();
                $('.PRODCONSO').hide();
            }
            
            var y;
            let index_nom =[];
            let index_cout =[];
            let color = ['#d62828','#001219','#005f73','#0a9396','#94d2bd',
            '#e9d8a6','#ee9b00','#ca6702','#bb3e03','#ae2012',
            '#9b2226','#ed9448','#7cb5ec','#d62828','#00FF00'];

            if (data.result[globalEqLogic].configuration.color0 != null) {
                color[0] = data.result[globalEqLogic].configuration.color0;
                color[1] = data.result[globalEqLogic].configuration.color1;
                color[2] = data.result[globalEqLogic].configuration.color2;
                color[3] = data.result[globalEqLogic].configuration.color3;
                color[4] = data.result[globalEqLogic].configuration.color4;
                color[5] = data.result[globalEqLogic].configuration.color5;
                color[6] = data.result[globalEqLogic].configuration.color6;
                color[7] = data.result[globalEqLogic].configuration.color7;
                color[8] = data.result[globalEqLogic].configuration.color8;
                color[9] = data.result[globalEqLogic].configuration.color9;
                color[10] = data.result[globalEqLogic].configuration.color10;
                color[11] = data.result[globalEqLogic].configuration.color11;
                color[12] = data.result[globalEqLogic].configuration.color12;
                color[13] = data.result[globalEqLogic].configuration.color13;
                color[14] = data.result[globalEqLogic].configuration.color14;
            }
            if (data.result[globalEqLogic].configuration.newIndex == 1) {
                $('.couts').show();
                $('.index').show();
                $('.TOTAL').hide();
                $('.HCHP').hide();
                $('.coutsgraph').show();
                newIndex = true;
                index[0] = true;
                index_nom = ['Global',
                            data.result[globalEqLogic].configuration.index01_nom,
                            data.result[globalEqLogic].configuration.index02_nom,
                            data.result[globalEqLogic].configuration.index03_nom,
                            data.result[globalEqLogic].configuration.index04_nom,
                            data.result[globalEqLogic].configuration.index05_nom,
                            data.result[globalEqLogic].configuration.index06_nom,
                            data.result[globalEqLogic].configuration.index07_nom,
                            data.result[globalEqLogic].configuration.index08_nom,
                            data.result[globalEqLogic].configuration.index09_nom,
                            data.result[globalEqLogic].configuration.index10_nom];
                index_cout = [Number(data.result[globalEqLogic].configuration.Coutindex00),
                            Number(data.result[globalEqLogic].configuration.Coutindex01),
                            Number(data.result[globalEqLogic].configuration.Coutindex02),
                            Number(data.result[globalEqLogic].configuration.Coutindex03),
                            Number(data.result[globalEqLogic].configuration.Coutindex04),
                            Number(data.result[globalEqLogic].configuration.Coutindex05),
                            Number(data.result[globalEqLogic].configuration.Coutindex06),
                            Number(data.result[globalEqLogic].configuration.Coutindex07),
                            Number(data.result[globalEqLogic].configuration.Coutindex08),
                            Number(data.result[globalEqLogic].configuration.Coutindex09),
                            Number(data.result[globalEqLogic].configuration.Coutindex10)];
                $('.teleinfoAttr[data-l1key=titre][data-l2key=Index00]').text(index_nom[0]);


                for(i=1;i<11;i++){
                    if (i<10){
                        var numeroIndex = 'Index0' + i;
                    }else{
                        var numeroIndex = 'Index' + i;
                    }
                    if(index_nom[i] !== ''){
                        index[i] = true;
                        y = document.getElementsByClassName(numeroIndex);
                        for (w = 0; w < y.length; w++) {
                            y[w].style.display = 'table-cell';
                        }
                        $('.teleinfoAttr[data-l1key=titre][data-l2key=' + numeroIndex + ']').text(index_nom[i]);
                    }else{
                        y = document.getElementsByClassName(numeroIndex);
                        for (w = 0; w < y.length; w++) {
                            y[w].style.display = 'none';
                        }
    
                    }
                }
            }else{
                newIndex = false;
                $('.couts').hide();
                $('.PRODCOUT').hide();
                $('.index').hide();
                $('.TOTAL').show();
                $('.coutsgraph').hide();
                if (data.result[globalEqLogic].configuration.HCHP==1){
                    HCHP = true;
                    $('.HCHP').show();
                }else{
                    HCHP = false;
                    $('.HCHP').hide();
                }
                console.log("[loadData] anciens index HCHP : " + HCHP);
                index[0] = false;
    /*				for (k=0; k<=10; k++){
                    switch(k)
                        {
                            case 0:
                                y = document.getElementsByClassName('Index00')
                            break;
                            case 1:
                                y = document.getElementsByClassName('Index01')
                            break;
                            case 2:
                                y = document.getElementsByClassName('Index02')
                            break;
                            case 3:
                                y = document.getElementsByClassName('Index03')
                            break;
                            case 4:
                                y = document.getElementsByClassName('Index04')
                            break;
                            case 5:
                                y = document.getElementsByClassName('Index05')
                            break;
                            case 6:
                                y = document.getElementsByClassName('Index06')
                            break;
                            case 7:
                                y = document.getElementsByClassName('Index07')
                            break;
                            case 8:
                                y = document.getElementsByClassName('Index08')
                            break;
                            case 9:
                                y = document.getElementsByClassName('Index09')
                            break;
                            case 10:
                                y = document.getElementsByClassName('Index10')
                            break;
                        }
                    for (w = 0; w < y.length; w++) {
                        y[w].style.display = 'none';
                    }
                }
            */			}

    //            if(data.result[globalEqLogic].configuration.ActivationProduction == 0){
    //				 y = document.getElementsByClassName('PROD');
    //				 for (j = 0; j < y.length; j++) {
    //					y[j].style.display = 'none';
    //				}
    //			}
                    
            
            var CoutindexProd = Number(data.result[globalEqLogic].configuration.CoutindexProd);                        
                
            if(data.result[globalEqLogic].configuration.abonnement){
                $('.teleinfoAttr[data-l1key=abonnement][data-l2key=type]').text(' ' + data.result[globalEqLogic].configuration.abonnement);
                if (data.result[globalEqLogic].configuration.abonnement.includes("PROD")){
                    compteurProd = true;
                    $('#spanTitreResume').html('<i style="font-size: initial;" class="icon fas fa-leaf"></i> Ma Production');
                }
                else{
                    if(data.result[globalEqLogic].configuration.ActivationProduction == 0){
                        $('#spanTitreResume').html('<i style="font-size: initial;" class="fas fa-bolt"></i> Ma Consommation');
                    }
                    else{
                        $('#spanTitreResume').html('<i style="font-size: initial;" class="fas fa-bolt"></i> Ma Consommation et Ma production');
                        prodEtConso = true;
                    }
                }
            }else{
                if(data.result[globalEqLogic].configuration.ActivationProduction == 0){
                    $('#spanTitreResume').html('<i style="font-size: initial;" class="fas fa-bolt"></i> Ma Consommation');
                }
                else{
                    $('#spanTitreResume').html('<i style="font-size: initial;" class="fas fa-bolt"></i> Ma Consommation et Ma production');
                    prodEtConso = true;
                }
            }

            try {
                var diviseur = 1;
                todayHp = 0;
                todayHC = 0;
                monthHp = 0;
                monthHC = 0;
                yearHp = 0;
                yearHC = 0;
                pause = 0;

                for(cmd in data.result[globalEqLogic].cmd)
                {
                    var datacmd= data.result[globalEqLogic].cmd[cmd];
                    console.log("[Courbes à tracer de " + globalEqLogic + " ] => " + datacmd.logicalId)
                    if (datacmd.configuration.calculValueOffset!==undefined){
                        tdiviseur = (datacmd.configuration.calculValueOffset).split("/")
                        diviseur = tdiviseur[1]
                        if (diviseur==undefined){
                            diviseur = 1
                        }
                        console.log("[diviseur] => " + diviseur)
                    }
                    try{
                        test = datacmd.logicalId
                        switch(true)
                        {
                            case (test==="STAT_YESTERDAY_HC"&&!newIndex):
                                if(!compteurProd&&HCHP){
                                    sleep(pause);
                                    stackGraph = 1;
                                    serie = 3;
                                    getMonthlyHistory('div_graphGlobalIndex',datacmd, color[11], stackGraph, diviseur, serie);
                                    getAnnualHistory('div_graphGlobalAnnual',datacmd, color[11], stackGraph, diviseur, serie);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=all]'), 'all', "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYear]'), 'monthLastYear', "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial', "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=month]'), 'month' , "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYear]'), 'yearLastYear' , "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=year]'), 'year' , "coutHC", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=yesterday]'), 'yesterday' , "coutHC", datacmd);

                                    console.log("[loadData][STAT_YESTERDAY_HC] " + datacmd.id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=all]'), 'all' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=month]'), 'month' , datacmd);
                                    monthHC = $('.teleinfoAttr[data-l1key=consoHC][data-l2key=month]').text.value;
                                    console.log("[loadData][STAT_YESTERDAY_HC] " + monthHC)
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=year]'), 'year' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yesterday]'), 'yesterday' , datacmd);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":datacmd.id,"name":datacmd.name,"color":color[11],"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',datacmd, color[11], stackGraph, diviseur, serie);

                                    }
                            break;
                            case (test==="STAT_YESTERDAY_HP"&&!newIndex):
                                if(!compteurProd&&HCHP){
                                    sleep(pause);
                                    stackGraph = 1;
                                    serie = 2;
                                    getAnnualHistory('div_graphGlobalAnnual',datacmd, color[12], stackGraph, diviseur, serie);
                                    getMonthlyHistory('div_graphGlobalIndex',datacmd, color[12], stackGraph, diviseur, serie);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=all]'), 'all' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYear]'), 'monthLastYear' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=month]'), 'month' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYear]'), 'yearLastYear' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=year]'), 'year' , "coutHP", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=yesterday]'), 'yesterday' , "coutHP", datacmd);

                                    console.log("[loadData][STAT_YESTERDAY_HP 1] " + datacmd.id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=all]'), 'all' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=month]'), 'month' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=year]'), 'year' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yesterday]'), 'yesterday' , datacmd);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":datacmd.id,"name":datacmd.name,"color":color[12],"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',datacmd, color[12], stackGraph, diviseur, serie);

                                    }
                            break;
                            case (test==="STAT_YESTERDAY"&&!newIndex):
                                sleep(pause);
                                console.log("[loadData][STAT_YESTERDAY] " + datacmd.id);
                                if(!compteurProd){
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=all]'), 'all' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]'), 'monthLastYear' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=month]'), 'month' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]'), 'yearLastYear' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=year]'), 'year' , "coutBase", datacmd);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]'), 'yesterday' , "coutBase", datacmd);
                                    
                                    console.log("[loadData][STAT_YESTERDAY] " + datacmd.id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=all]'), 'all' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=month]'), 'month' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=year]'), 'year' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yesterday]'), 'yesterday' , datacmd);
                                }
                                if(!compteurProd&&(!HCHP)){
                                    stackGraph = 0;
                                    serie = 1;
                                    getAnnualHistory('div_graphGlobalAnnual',datacmd, color[13], stackGraph, diviseur, serie);
                                    getMonthlyHistory('div_graphGlobalIndex',datacmd, color[13], stackGraph, diviseur, serie);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":datacmd.id,"name":datacmd.name,"color":color[13],"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',datacmd, color[13], stackGraph, diviseur, serie);
                                }
                            break;
                            case (test==="STAT_YESTERDAY_PROD"):
                                sleep(pause);
                                console.log("[loadData][STAT_YESTERDAY_PROD] " + datacmd.id + ' prod? ' + compteurProd + ' ' + prodEtConso);
                                if(compteurProd||prodEtConso){
                                    stackGraph = 0
                                    if (datacmd.name == 'STAT_YESTERDAY_PROD'){
                                        datacmd.name = 'Prod ';
                                    }
                                    serie = 14;
                                    console.log("[loadData][STAT_YESTERDAY_PROD] " + datacmd.id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=all]'), 'all' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=month]'), 'month' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=year]'), 'year' , datacmd);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yesterday]'), 'yesterday' , datacmd);
                                    if(!newIndex){
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=all]'), 'all' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]'), 'monthLastYear' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]'), 'month' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]'), 'yearLastYear' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]'), 'year' , "coutProd", datacmd);
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]'), 'yesterday' , "coutProd", datacmd);
                                    }
                                    commandesStat.push({"graph":"graph", "id":datacmd.id,"name":datacmd.name,"color":color[14],"stackGraph":stackGraph,"diviseur":diviseur,"serie":serie});
                                    getAnnualHistory('div_graphGlobalAnnual',datacmd, color[14], stackGraph, diviseur, serie);
                                    getMonthlyHistory('div_graphGlobalIndex',datacmd, color[14], stackGraph, diviseur, serie);
                                    getDailyHistory('div_graphGlobalJournalier',datacmd, color[14], stackGraph, diviseur, serie);
                                }
                            break;
                            case (test==="STAT_YESTERDAY_PROD_COUT"&&newIndex):
                                sleep(pause);
                                console.log("[loadData][STAT_YESTERDAY_PROD_COUT] " + datacmd.id + ' prod? ' + compteurProd + ' ' + prodEtConso);
                                if(compteurProd||prodEtConso){
                                    stackGraph = 0
                                    commande = datacmd;
                                    commande.name = 'Prod ';
                                    serie = 14;
                                    console.log("[loadData][STAT_YESTERDAY_PROD_COUT] " + commande.id);
                                    //if(!prodEtConso){
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=all]'), 'all' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]'), 'monthLastYear' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]'), 'yearLastYear' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]'), 'month' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]'), 'year' , commande, 1);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]'), 'yesterday' , commande, 1);
                                    
                                    commandesStatCout.push({"graph":"graph", "id":commande.id,"name":commande.name,"color":color[14],"stackGraph":stackGraph,"diviseur":1000,"serie":serie,"cout":'cout'});
                                    getAnnualHistory('div_graphGlobalAnnualCout',commande, color[14], stackGraph, 1000, serie, 'cout');
                                    getMonthlyHistory('div_graphGlobalIndexCout',commande, color[14], stackGraph, 1000, serie, 'cout');
                                    getDailyHistory('div_graphGlobalJournalierCout',commande, color[14], stackGraph, 1000, serie, 'cout');
                                
                                }
                            break;
                            case (test.includes("STAT_YESTERDAY_INDEX")&&newIndex):
                                sleep(pause);
                                var indexEnCours = parseInt(test.substr(20,2));
                                if (indexEnCours==0){
                                    stackGraph = 0;
                                }else{
                                    stackGraph = 1;
                                }
                                if (index[indexEnCours]){
                                    nomIndex = index_nom[indexEnCours];
                                    coutIndex = index_cout[indexEnCours];
                                    if (indexEnCours<10){
                                        consommation = 'consoIndex0' + indexEnCours;
                                        cout = 'coutIndex0' + indexEnCours;
                                    }else{
                                        consommation = 'consoIndex' + indexEnCours;
                                        cout = 'coutIndex' + indexEnCours;
                                    }
                                    serie = indexEnCours + 1;
                                    coutoui = false;
                                    if (test.includes("COUT")){
                                        coutoui = true;
                                    }

                                    commande = datacmd;
                                    commande.name = nomIndex;

                                    console.log("commande.name " + commande.unite + ' index en cours ' + indexEnCours + ' ' + index[indexEnCours]);
                                    if (coutoui){
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=all]'), 'all' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=month]'), 'month' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=year]'), 'year' ,datacmd, 1);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=yesterday]'), 'yesterday' , datacmd, 1);
                                        commandesStatCoutIndex.push({"graph":"graph", "id":commande.id,"name":commande.name,"color":color[indexEnCours],"stackGraph":stackGraph,"diviseur":1000,"serie":serie,"cout":'cout'});
                                        getDailyHistory('div_graphGlobalJournalierCout',commande, color[indexEnCours], stackGraph, 1000, serie, 'cout');
                                        getAnnualHistory('div_graphGlobalAnnualCout',commande, color[indexEnCours], stackGraph, 1000, serie, 'cout');
                                        getMonthlyHistory('div_graphGlobalIndexCout',commande, color[indexEnCours], stackGraph, 1000, serie, 'cout');
                                    }else{
                                        commande.unite = 'kWh';
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=all]'), 'all' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=monthLastYear]'), 'monthLastYear' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=yearLastYear]'), 'yearLastYear' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=month]'), 'month' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=year]'), 'year' , datacmd);
                                        getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=yesterday]'), 'yesterday' , datacmd);
                                        commandesStatIndex.push({"graph":"graph", "id":commande.id,"name":commande.name,"color":color[indexEnCours],"stackGraph":stackGraph,"diviseur":diviseur,"serie":serie});
                                        getDailyHistory('div_graphGlobalJournalier',commande, color[indexEnCours], stackGraph, diviseur, serie);
                                        getAnnualHistory('div_graphGlobalAnnual',commande, color[indexEnCours], stackGraph, diviseur, serie);
                                        getMonthlyHistory('div_graphGlobalIndex',commande, color[indexEnCours], stackGraph, diviseur, serie);
                                    }
                                }
                            break;
                            case (test==="SINSTI"):
                            case (test==="SINST1"):
                                sleep(pause);
                                if(compteurProd||prodEtConso){
                                    commandesPuissance.push({"id":datacmd.id,"name":datacmd.name,"color":color[14]});
                                    console.log("[loadData][SINST*] " + datacmd.id);
                                    getObjectHistory('div_graphGlobalPower', 'Simple', datacmd, color[14]);
                                }
                            break;
                            case (test==="SINSTS"):
                                    colori = color[0];
                            case (test==="SINSTS1"):
                                    if (test==="SINSTS1"){
                                        colori = color[1];
                                    }
                            case (test==="SINSTS2"):
                                    if (test==="SINSTS2"){
                                        colori = color[2];
                                    }
                            case (test==="SINSTS3"):
                                    if (test==="SINSTS3"){
                                        colori = color[3];
                                    }
                            case (test==="PAPP"):
                                    if (test==="PAPP"){
                                        colori = color[0];
                                    }
                                    if(!compteurProd){
                                        sleep(pause);
                                        commandesPuissance.push({"id":datacmd.id,"name":datacmd.name,"color":colori});
                                        console.log("[loadData][PAPP ou SINSTS] " + datacmd.id);
                                        getObjectHistory('div_graphGlobalPower', 'Simple', datacmd, colori);
                                    }
                            break;
                            case (test==="STAT_TODAY"&&!newIndex):
                                    if(!compteurProd){
                                        sleep(pause);
                                        console.log("[loadData][STAT_TODAY] " + datacmd.value);
                                        $('.teleinfoAttr[data-l1key=conso][data-l2key=day]').text(((datacmd.value)/1000).toFixed(2));
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=day]'), 'day' , "coutBase", datacmd);
                                    }
                            break;
                            case (test==="STAT_TODAY_HC"&&!newIndex):
                                    if(!compteurProd&&HCHP){
                                        sleep(pause);
                                        console.log("[loadData][STAT_TODAY_HC] " + datacmd.value);
                                        $('.teleinfoAttr[data-l1key=consoHC][data-l2key=day]').text(((datacmd.value)/1000).toFixed(2));
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=day]'), 'day' , "coutHC", datacmd);
                                    }
                            break;
                            case (test==="STAT_TODAY_HP"&&!newIndex):
                                    if(!compteurProd&&HCHP){
                                        sleep(pause);
                                        console.log("[loadData][STAT_TODAY_HP] " + datacmd.value);
                                        $('.teleinfoAttr[data-l1key=consoHP][data-l2key=day]').text(((datacmd.value)/1000).toFixed(2));
                                        getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=day]'), 'day' , "coutHP", datacmd);
                                    }
                            break;
                            case (test.includes("STAT_TODAY_INDEX")&&newIndex):
                                    var indexEnCours = parseInt(test.substr(16,2));
                                    if (index[indexEnCours]){
                                        indexCout = index_cout[indexEnCours];
                                        if (indexEnCours <10){
                                            cout = 'coutIndex0' + indexEnCours;
                                            consommation = 'consoIndex0' + indexEnCours;
                                        }else{
                                            cout = 'coutIndex' + indexEnCours;
                                            consommation = 'consoIndex' + indexEnCours;
                                        }
                                        if(!compteurProd){
                                            sleep(pause);
                                            if (test.includes("COUT")){
                                                console.log("[loadData 1][STAT_TODAY_INDEX_COUT " + indexEnCours + "] " + cout + ' ' + indexCout);
                                                getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + cout + '][data-l2key=day]'), 'day' , datacmd, 1);
                                                //$('.teleinfoAttr[data-l1key='+ cout + '][data-l2key=day]').text((datacmd.value).toFixed(2));
                                                //if (indexEnCours == 0){
                                                nomIndex = index_nom[indexEnCours];
                                                commande = datacmd;
                                                commande.name = nomIndex;
                                                console.log("[loadData 1][STAT_TODAY_INDEX_COUT " + indexEnCours + "] couleur: " + color[indexEnCours]);
                                                commandesPuissanceCout.push({"id":commande.id,"name":commande.name,"color":color[indexEnCours]});
                                                console.log("[loadData][" + nomIndex + "] " + commande.id);
                                                getObjectHistory('div_graphGlobalPowerCout', 'cout', commande, color[indexEnCours]);
                                                //}
                                            }else{
                                                console.log("[loadData 2][STAT_TODAY_INDEX " + indexEnCours + "] " +datacmd.id + ' ' + (datacmd.value));
                                                getCommandHistoryValue($('.teleinfoAttr[data-l1key=' + consommation + '][data-l2key=day]'), 'day' , datacmd);
                                                //$('.teleinfoAttr[data-l1key='+ consommation + '][data-l2key=day]').text(((datacmd.value)/1000).toFixed(2));
                                            }
                                        }
                                    }
                            break;
                            case (test==="STAT_TODAY_PROD"):
                                    if(compteurProd||prodEtConso){
                                        sleep(pause);
                                        console.log("[loadData][STAT_TODAY_PROD] " + datacmd.value);
                                        $('.teleinfoAttr[data-l1key=prod][data-l2key=day]').text(((datacmd.value)/1000).toFixed(2));
                                        if(newIndex){
                                            commande = datacmd;
                                            commande.name = 'Revenu Prod'; 
                                            revenusprod = ((datacmd.value)*CoutindexProd/1000).toFixed(2);
                                            commande.value = revenusprod;
                                            $('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]').text(revenusprod);                                                      
                                            //getObjectHistory('div_graphGlobalPowerCout', 'cout', commande, color[14]);
                                        }else{
                                            getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]'), 'day' , CoutindexProd, datacmd);
                                        }
                                }
                            break;
                        }
                    }
                    catch(err) {
                        console.log("Exception dans le remplissage evolution de la consommation : " + err);
                    }
                }
                serie0 += 2;
                serie1 += 2;
            }
            catch(err) {
                console.log("Exception dans le traitement des commandes : " + err);
            }

            for(globalEqLogic in data.result)
            {
                console.log("[loadData] => " + data.result[globalEqLogic].name);

            }
        },
    });
}

function getObjectHistory(div, type, object, color, action = 'none') {
    dailyHistoryChart[div] = null;
    if (type === 'cout'){
        symbole = '€';
        plotBackgroundColor = 'rgba(255, 99, 71, 0.2)';
    }else{
        symbole = 'VA';
        plotBackgroundColor = 'rgba(255, 255, 255, 0)';
    }
        console.log("[getObjectHistory] couleur: du graph " + color);
    //    if(action === 'refresh'){
        startDate = $('#in_startDate').value()
    //    }else {
    //        startDate = $('#in_endDate').value()
    //    }
    console.log("[getObjectHistory] Récupération de l'historique pour la commande " + object.name + " date de début: " + startDate + " date de fin: " + $('#in_endDate').value());
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : '1 day',
                    dateStart: startDate,
                    dateEnd: $('#in_endDate').value(),
                    showNavigator : false,
                    charts: {
                        plotBackgroundColor: plotBackgroundColor,
                    },
                    option: {
                        name : object.name,
                        graphColor : color,
                        derive : 0,
                        graphType : 'line',
                        graphZindex :3,
                        graphScale : 1,
                        displayAlert: false,
                        },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ' + symbole + '<br/>',
                        shared: true
                    },
                    //newGraph: true,
                });
    /*    console.log("[getObjectHistory] Name => " + object.logicalId),
    jeedom.config.load({
        plugin: "teleinfo",
        configuration : "outside_temp",
        error: function (error) {
        },
        success: function (myId) {
            if ((myId != '') && (object.name == 'SINSTS')){
                console.log("[getDailyHistory] Id température exterieure : " + myId)
                teleinfoDrawChart({
                                cmd_id: myId,
                                el: div,
                                dateRange : 'all',
                                dateStart: startDate,
                                dateEnd: $('#in_endDate').value(),
                                showNavigator : false,
                                tooltipSeries: {
                                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                                    shared: true
                                },
                                option: {
                                    name : 'Température ext.',
                                    graphType : 'line',
                                    graphColor: '#87b125',
                                    derive : 0,
                                    graphZindex : 2,
                                },
                            });
            }
            else{
                console.log("[getDailyHistory] Pas de température extérieur")
            }
        }
    }); */
}

function getDailyHistory(div,  object, color, stackGraph, diviseur, serie, type) {
    var from = moment($('#in_startDate').value(), "YYYY-MM-DD").format('YYYY-MM-DD');
    if (moment().format('DD') === $('#in_endDate').value().substr(-2,2)) {
        var to = moment($('#in_endDate').value(), "YYYY-MM-DD").subtract(1, 'days').format('YYYY-MM-DD');
    }else {
        var to = moment($('#in_endDate').value(), "YYYY-MM-DD").format('YYYY-MM-DD');
    }
    //    var color = '#7cb5ec';
    //    if (object.logicalId.includes("PROD")){
    //        color = '#ed9448';
    //    }
    if (type === 'cout'){
    symbole = '€';
    decimals = 2;
    plotBackgroundColor = 'rgba(255, 99, 71, 0.2)';
    }else{
    symbole = 'kWh';
    decimals = 1;
    plotBackgroundColor = 'rgba(255, 255, 255, 0)';
    }
    console.log("[getDailyHistory] Commande = " + object.name);
    console.log("[getDailyHistory] Récupération de div " + div);
    console.log("[getDailyHistory] Série " + serie);

    //	if object.logicalId != undefined {
    //		nomCourbe = object.logicalId
    //	} else {
    //		nonCourbe = "PROD"
    //	}

    dailyHistoryChart[div] = null;
    console.log("[getDailyHistory] Récupération de l'historique pour la période du " + from + " au " + to);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: from,
                    dateEnd: to,
                    showNavigator : false,
                    index : serie,
                    charts: {
                        plotBackgroundColor: plotBackgroundColor,
                    },
                    option: {
                        graphColor: color,
                        name : object.name,
                        derive : 0,
                        graphStack : stackGraph,
                        graphStep: 1,
                        graphScale : 1,
                        graphType : 'column',
                        graphZindex :1,
                        groupingType:"high::day",
                        stacking : 'normal',
                        displayAlert: false,
                        valueDecimals: 1,
                        allowZero: 0,
                    },
                    tooltip : {
                        shared : true,
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.'+decimals+'f}</b> ' + symbole + '<br/>',
                        shared: true,
                        valueDecimals: 1,
                    },
                    plotOptions : {
                        column: {
                            stacking: 'normal',
                            valueDecimals: 1,
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black',
                                    fontSize: '10px',

                                },
                                format: '{point.y:.'+decimals+'f}',
                            }
                        }
                    },
                    divide:1000/diviseur,
                });

    if (type !== 'cout'){
        jeedom.config.load({
            plugin: "teleinfo",
            configuration : "outside_temp",
            error: function (error) {
            },
            success: function (myId) {
                if ((myId != '') && !graphTempDaily){
                    graphTempDaily = true;
                    console.log("[getDailyHistory] Id température exterieure : " + myId)
                    teleinfoDrawChart({
                                    cmd_id: myId,
                                    el: div,
                                    dateRange : 'all',
                                    dateStart: from,
                                    dateEnd: to,
                                    showNavigator : false,
                                    index : 20,
                                    tooltipSeries: {
                                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.1f}</b><br/>',
                                        shared: true
                                    },
                                    charts: {
                                        plotBackgroundColor: plotBackgroundColor,
                                    },
                                                option: {
                                        name : 'Température ext.',
                                        graphType : 'line',
                                        graphColor: '#87b125',
                                        derive : 0,
                                        graphZindex : 2,
                                        groupingType:"average::day",
                                    },
                                    tooltipSeries : {
                                        valueDecimals: 1
                                    },
                                });
                }
                else{
                    console.log("[getDailyHistory] + de 1 courbes ou Pas de température extérieure ou courbe déjà tracée")
                }
            }
        });
    }
}

function getMonthlyHistory(div,  object, color, stackGraph, diviseur, serie, type) {
    //    var from = moment().subtract(18, 'months').startOf('month').format('YYYY-MM-DD 00:00:00');
    //    var to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');

    var from = moment($('#in_endDate').value(), "YYYY-MM-DD").subtract(18, 'months').startOf('month').format('YYYY-MM-DD 00:00:00');
    var to = moment($('#in_endDate').value(), "YYYY-MM-DD").endOf('month').format('YYYY-MM-DD 23:59:59');

    if (type === 'cout'){
    symbole = '€';
    decimals = 2;
    plotBackgroundColor = 'rgba(255, 99, 71, 0.2)';
    }else{
    symbole = 'kWh';
    decimals = 1;
    plotBackgroundColor = 'rgba(255, 255, 255, 0)';
    }
    dailyHistoryChart[div] = null;
    console.log("[getMonthlyHistory] Récupération de div " + div);
    console.log("[getMonthlyHistory] Récupération de l'historique pour la période du " + from + " au " + to);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: from,
                    dateEnd: to,
                    showNavigator : false,
                    index : serie,
                    charts: {
                        plotBackgroundColor: plotBackgroundColor,
                    },
                    option: {
                        name : object.name,
                        graphColor: color,
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphStack : stackGraph,
                        graphType : 'column',
                        graphZindex : 1,
                        groupingType:"sum::month",
                        displayAlert: false,
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.'+decimals+'f}</b> ' + symbole + '<br/>',
                        shared : true,
                        //pointFormat: '{series.name}</span>: <b>{point.y} kWh</b><br/>',
                    },
                    tooltip : {
                        stacking : 'normal',
                        shared : true,
                    },
                    plotOptions : {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: false,
                                    fontSize: '10px',

                                },
                                format: '{point.y:.'+decimals+'f}',
                            }
                        }
                    },
                    divide:1000/diviseur,
    });
    //
    if (type !== 'cout'){
        jeedom.config.load({
            plugin: "teleinfo",
            configuration : "outside_temp",
            error: function (error) {
            },
            success: function (myId) {
                if ((myId != '') && !graphTempMonthly){
                    graphTempMonthly = true;
                    console.log("[getMonthlyHistory] Id température exterieure : " + myId)
                    teleinfoDrawChart({
                                    cmd_id: myId,
                                    el: div,
                                    dateRange : 'all',
                                    dateStart: from,
                                    dateEnd: to,
                                    showNavigator : false,
                                    index : 20,
                                    tooltipSeries: {
                                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.1f} °C</b><br/>',
                                        shared: true
                                    },
                                    charts: {
                                        plotBackgroundColor: plotBackgroundColor,
                                    },
                                    option: {
                                        name : 'Température ext.',
                                        graphType : 'line',
                                        graphColor: '#87b125',
                                        derive : 0,
                                        graphZindex : 2,
                                        groupingType:"average::month",
                                    },
                                });
                }
                else{
                    console.log("[getMonthlyHistory] + de 1 courbes ou Pas de température extérieure ou courbe déjà tracée")
                }
            }
        });
    }
    //
}


function getAnnualHistory(div,  object, color, stackGraph, diviseur, serie, type) {
    //    var from = moment().subtract(18, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
    //    var to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
    var from = moment($('#in_endDate').value(), "YYYY-MM-DD").subtract(18, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
    var to = moment($('#in_endDate').value(), "YYYY-MM-DD").endOf('year').format('YYYY-MM-DD 23:59:59');
    if (type === 'cout'){
        symbole = '€';
        decimals = 1;
        plotBackgroundColor = 'rgba(255, 99, 71, 0.2)';
    }else{
        symbole = 'kWh';
        decimals = 0;
        plotBackgroundColor = 'rgba(255, 255, 255, 0)';
    }
    dailyHistoryChart[div] = null;
    console.log("[getAnnualHistory] Récupération de div " + div);
    console.log("[getAnnualHistory] Récupération de l'historique pour la période du " + from + " au " + to);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: from,
                    dateEnd: to,
                    showNavigator : false,
                    index : serie,
                    charts: {
                        plotBackgroundColor: plotBackgroundColor,
                    },
                    option: {
                        name : object.name,
                        graphColor: color,
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphStack : stackGraph,
                        graphType : 'column',
                        graphZindex : 1,
                        groupingType:"sum::year",
                        displayAlert: false,
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.'+decimals+'f}</b> ' + symbole + '<br/>',
                        shared : true
                        //pointFormat: '{series.name}</span>: <b>{point.y} kWh</b><br/>',
                    },
                    tooltip : {
                        stacking : 'normal',
                        shared : true,
                    },
                    plotOptions : {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black',
                                    fontSize: '10px',
                                },
                                format: '{point.y:.'+decimals+'f}',
                            }
                        }
                    },
                    divide:1000/diviseur,
    });
    //
    if (type !== 'cout'){
        jeedom.config.load({
            plugin: "teleinfo",
            configuration : "outside_temp",
            error: function (error) {
            },
            success: function (myId) {
                if ((myId != '') && !graphTempAnnualy){
                    graphTempAnnualy = true;
                    console.log("[getAnnualHistory] Id température exterieure : " + myId)
                    teleinfoDrawChart({
                                    cmd_id: myId,
                                    el: div,
                                    dateRange : 'all',
                                    dateStart: from,
                                    dateEnd: to,
                                    showNavigator : false,
                                    index : 20,
                                    tooltipSeries: {
                                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.1f} °C</b><br/>',
                                        shared: true
                                    },
                                    charts: {
                                        plotBackgroundColor: plotBackgroundColor,
                                    },
                                    option: {
                                        name : 'Température ext.',
                                        graphType : 'line',
                                        graphColor: '#87b125',
                                        derive : 0,
                                        graphZindex : 2,
                                        groupingType:"average::year",
                                    },
                                    tooltipSeries : {
                                        valueDecimals: 1
                                    },
                                });
                }
                else{
                    console.log("[getAnnualHistory] + de 1 courbe courbe ou Pas de température extérieure ou courbe déjà tracée")
                }
            }
        });
    }
    //
}





function getCommandHistoryValue(div, type , object, coutoui = 1000, virgule = 1) {
    var from = moment().format('YYYY-MM-DD 00:00:00');
    var to = moment().format('YYYY-MM-DD 23:59:59');
    virgule = 2;
    switch (type){
        case 'day':
            from = moment().format('YYYY-MM-DD 00:00:00');
            to = moment().format('YYYY-MM-DD 23:59:59');
        break;
        case 'monthLastYear':
            from = moment().subtract(1, 'years').startOf('month').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').endOf('month').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYear':
            from = moment().subtract(1, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
        case 'month':
            from = moment().startOf('month').format('YYYY-MM-DD 00:00:00');
            to = moment().endOf('month').format('YYYY-MM-DD 23:59:59');
        break;
        case 'year':
            from = moment().startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yesterday':
            from = moment().subtract(1, 'days').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
        break;
        case 'monthLastYearPartial':
            from = moment().subtract(1, 'years').startOf('month').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYearPartial':
            from = moment().subtract(1, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'all':
            from = moment().subtract(25, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
    }

    //    dailyHistoryChart[div] = null;
    jeedom.history.get({
        cmd_id: object.id,
        dateStart : from,
        dateEnd : to,
        error: function (error) {
        },
        success: function (myCommandHistory) {
            if (coutoui == 1 || type == 'yesterday' || type == 'day'){
                virgule = 2;
            }else{
                if (type == 'month' || type == 'monthLastYearPartial'){
                    virgule = 1;
                }else{
                    virgule = 0;
                }
            }
            if(myCommandHistory.data.length != 0){ 
                if(myCommandHistory.data.length == 1 || type == 'day' || type == 'yesterday'){
                    div.text((myCommandHistory.data[myCommandHistory.data.length -1][1] / coutoui).toFixed(virgule)); //(myCommandHistory.maxValue / coutoui).toFixed(virgule));
                    console.log("[Object 1] " + object.id + " [getCommandHistoryValue 1] " + object.name + " " + type + " | from : " + from + " | to : " + to + " | value : " + (myCommandHistory.maxValue/coutoui).toFixed(virgule));
                }else {
                    resultat = (myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / coutoui).toFixed(virgule);                    
                    div.text(resultat);
                    console.log("[Object 2] " + object.id + " [getCommandHistoryValue 2] " + object.name + " "  + type + " | from : " + from + " | to : " + to + " | value : " + resultat);
                }
            }else{
                div.text(0);
            }
        }
    });
}


function getCommandHistoryCout(div, type, cout, object) {
    var from = moment().format('YYYY-MM-DD 00:00:00');
    var to = moment().format('YYYY-MM-DD 23:59:59');
    switch (type){
        case 'monthLastYear':
            from = moment().subtract(1, 'years').startOf('month').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'years').endOf('month').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYear':
            from = moment().subtract(1, 'years').startOf('year').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'years').endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
        case 'month':
            from = moment().startOf('month').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().endOf('month').format('YYYY-MM-DD 23:59:59');
        break;
        case 'year':
            from = moment().startOf('year').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yesterday':
            from = moment().subtract(2, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'days').format('YYYY-MM-DD 00:00:01');
        break;
        case 'monthLastYearPartial':
            from = moment().subtract(1, 'years').startOf('month').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYearPartial':
            from = moment().subtract(1, 'years').startOf('year').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'all':
            from = moment().subtract(25, 'years').startOf('year').subtract(1, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
        break;
    }

    dailyHistoryChart[div] = null;
    jeedom.config.load({
        plugin: "teleinfo",
        configuration : cout,
        error: function (error) {
        },
        success: function (valeurCout) {
            if (valeurCout != '' && valeurCout != 0){

                jeedom.history.get({
                    cmd_id: object.id,
                    dateStart : from,
                    dateEnd : to,
                    error: function (error) {
                    },
                    success: function (myCommandHistory) {
                        if (valeurCout != 0){
                            if(tableCouts[type] === undefined){
                                tableCouts[type] = 0;
                            }
                            tableCouts[type] = 0;
                            if(type === "day"){
                                if(myCommandHistory.data[myCommandHistory.data.length - 1] !== undefined){
                                    tableCouts[type] = tableCouts[type] + (myCommandHistory.data[myCommandHistory.data.length - 1][1] / 1000) * valeurCout;
                                    console.log("[getCommandHistoryCout 1] " + type + " " + cout + " : " + myCommandHistory.data[myCommandHistory.data.length - 1][1] / 1000 + " * " + valeurCout + " = " + tableCouts[type])
                                }

                            }else {
                            if(myCommandHistory.data.length == 1){
                                tableCouts[type] = tableCouts[type] + (myCommandHistory.maxValue / 1000) * valeurCout;
                                console.log("[getCommandHistoryCout 2] " + type + " " + cout + " : " + (myCommandHistory.maxValue / 1000) + " * " + valeurCout + " = " + tableCouts[type]);
                            }
                            else {
                                resultat = (myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / 1000) * valeurCout
                                myCommandHistory.data.splice(-1,1);
                                tableCouts[type] = tableCouts[type] + resultat ;
                                console.log("[getCommandHistoryCout 3] " + type + " " + cout + " : " + resultat)
                            }
                            }
                            div.text('( ~' + tableCouts[type].toFixed(2) + ' )');
                        }
                    }
                });
            }else{
                if (cout == 'coutBase'){
                    faireTotHpHc = true;
                }
                console.log("[getCommandHistoryCout] Pas de cout " + cout)
            }
        }
    });
}

function initHistoryTrigger() {

}


function teleinfoDrawChart(_params) {
    $.showLoading();
    if ($.type(_params.dateRange) == 'object') {
    _params.dateRange = json_encode(_params.dateRange);
    }
    _params.option = init(_params.option, {derive: ''});
    $.ajax({
    type: "POST",
    url: "core/ajax/cmd.ajax.php",
    data: {
        action: "getHistory",
        id: _params.cmd_id,
        dateRange: _params.dateRange || '',
        dateStart: _params.dateStart || '',
        dateEnd: _params.dateEnd || '',
        derive: _params.option.derive || '',
        allowZero: init(_params.option.allowZero, 0)
    },
    dataType: 'json',
    global: _params.global || true,
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {

        if (init(_params.divide) != '') {
            data.result.maxValue = data.result.maxValue / 1000;
            data.result.data.forEach(function(item, index, array) {
                item[1] = item[1] / _params.divide;
            });
        }

        if (data.state != 'ok') {
        $.fn.showAlert({message: data.result, level: 'danger'});
        return;
        }
        if (data.result.data.length < 1) {
        if(_params.option.displayAlert == false){
            return;
        }
        if(!_params.noError){
            var message = '{{Il n\'existe encore aucun historique pour cette commande :}} ' + data.result.history_name;
            if (init(data.result.dateStart) != '') {
            message += (init(data.result.dateEnd) != '') ?  ' {{du}} ' + data.result.dateStart + ' {{au}} ' + data.result.dateEnd : ' {{à partir de}} ' + data.result.dateStart;
            } else {
            message += (init(data.result.dateEnd) != '') ? ' {{jusqu\'au}} ' + data.result.dateEnd:'';
            }
            $.fn.showAlert({message: message, level: 'danger'});
        }
        return;
        }
        if (isset(dailyHistoryChart[_params.el]) && isset(dailyHistoryChart[_params.el].cmd[_params.cmd_id])) {
        dailyHistoryChart[_params.el].cmd[_params.cmd_id] = null;
        }
        _params.option.graphColor = (isset(dailyHistoryChart[_params.el])) ? init(_params.option.graphColor, Highcharts.getOptions().colors[init(dailyHistoryChart[_params.el].color, 0)]) : init(_params.option.graphColor, Highcharts.getOptions().colors[0]);
        _params.option.graphStep = (_params.option.graphStep == "1") ? true : false;
        if(isset(data.result.cmd)){
        if (init(_params.option.graphStep) == '') {
            _params.option.graphStep = (data.result.cmd.subType == 'binary') ? true : false;
            if (isset(data.result.cmd.display) && init(data.result.cmd.display.graphStep) != '') {
            _params.option.graphStep = (data.result.cmd.display.graphStep == "0") ? false : true;
            }
        }
        if (init(_params.option.graphType) == '') {
            _params.option.graphType = (isset(data.result.cmd.display) && init(data.result.cmd.display.graphType) != '') ? data.result.cmd.display.graphType : 'line';
        }
        if (init(_params.option.groupingType) == '' && isset(data.result.cmd.display) && init(data.result.cmd.display.groupingType) != '') {
            var split = data.result.cmd.display.groupingType.split('::');
            _params.option.groupingType = {function :split[0],time : split[1] };
        }
        }
        var stacking = (_params.option.graphStack == undefined || _params.option.graphStack == null || _params.option.graphStack == 0) ? null : 'value';
        _params.option.graphStack = (_params.option.graphStack == undefined || _params.option.graphStack == null || _params.option.graphStack == 0) ? Math.floor(Math.random() * 10000 + 2) : 1;
        _params.option.graphScale = (_params.option.graphScale == undefined) ? 0 : parseInt(_params.option.graphScale);
        _params.showLegend = (init(_params.showLegend, true) && init(_params.showLegend, true) != "0") ? true : false;
        _params.showTimeSelector = (init(_params.showTimeSelector, true) && init(_params.showTimeSelector, true) != "0") ? true : false;
        _params.showScrollbar = (init(_params.showScrollbar, true) && init(_params.showScrollbar, true) != "0") ? true : false;
        _params.showNavigator = (init(_params.showNavigator, true) && init(_params.showNavigator, true) != "0") ? true : false;

        if (init(_params.tooltip) == '') {
        _params.tooltip = {pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>', valueDecimals: 2, };
        }
        if (init(_params.tooltipSeries) == '') {
        _params.tooltipSeries = {valueDecimals: 2, };
        }

    // afficher les données au dessus des colonnes et dans chaque graphstack
        label = true;
        stacklabel = true;

        if (init(_params.plotOptions) == '') {
        _params.plotOptions = {
            column: {
            dataLabels: {
                enabled: label,
                style: {
                    textShadow: false,
                    textOutline: 'none',
                    fontSize: '10px',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                },
            },
            showInLegend: true
            },

        };
        }

        if (init(_params.legend) == '') {
        var legend = {borderColor: 'black',borderWidth: 2,shadow: true};
        }
        legend.enabled = init(_params.showLegend, true);
        if(isset(_params.newGraph) && _params.newGraph == true){
        delete dailyHistoryChart[_params.el];
        }
        var charts = {
        zoomType: 'x',
        plotBackgroundColor: _params.charts.plotBackgroundColor,
        renderTo: _params.el,
        alignTicks: false,
        spacingBottom: 5,
        spacingTop: 5,
        spacingRight: 5,
        spacingLeft: 5,
        height : _params.height || null,
        style: {fontFamily: 'Roboto'}
        }
        if(charts.height < 10){
        charts.height = null;
        }

        if(isset(_params.transparentBackground) && _params.transparentBackground == "1"){
        charts.backgroundColor = 'rgba(255, 255, 255, 0)';
        }

        if (isset(dailyHistoryChart[_params.el]) && dailyHistoryChart[_params.el].type == 'pie') {
        _params.option.graphType = 'pie';
        }

        if( _params.option.graphType == 'pie'){
        var series = {
            type: _params.option.graphType,
            id: _params.cmd_id,
            cursor: 'pointer',
            data: [{y:data.result.data[data.result.data.length - 1][1], name : (isset(_params.option.name)) ? _params.option.name + ' '+ data.result.unite : data.result.history_name + ' '+ data.result.unite}],
            color: _params.option.graphColor,
        };
        if (!isset(dailyHistoryChart[_params.el]) || (isset(_params.newGraph) && _params.newGraph == true)) {
            dailyHistoryChart[_params.el] = {};
            dailyHistoryChart[_params.el].cmd = new Array();
            dailyHistoryChart[_params.el].color = 0;
            dailyHistoryChart[_params.el].type = _params.option.graphType;
            dailyHistoryChart[_params.el].chart = new Highcharts.Chart({
            chart: charts,
            title: {
                text: ''
            },
            credits: {
                text: '',
                href: '',
            },
            exporting: {
                enabled: _params.enableExport || ($.mobile) ? false : true
            },
            tooltip: _params.tooltip,
            plotOptions: _params.plotOptions,
            series: [series]
            });
        }else {
            dailyHistoryChart[_params.el].chart.series[0].addPoint({y:data.result.data[data.result.data.length - 1][1], name : (isset(_params.option.name)) ? _params.option.name + ' '+ data.result.unite : data.result.history_name + ' '+ data.result.unite, color: _params.option.graphColor});
        }
        }else{
        var dataGrouping = {
            enabled: false
        };
        if(isset(_params.option.groupingType) && jQuery.type(_params.option.groupingType) == 'string' && _params.option.groupingType != ''){
            var split = _params.option.groupingType.split('::');
            _params.option.groupingType = {function :split[0],time : split[1] };
        }
        if(isset(_params.option.groupingType) && isset(_params.option.groupingType.function) && isset(_params.option.groupingType.time)){
            dataGrouping = {
            approximation: _params.option.groupingType.function,
            enabled: true,
            forced: true,
            units: [[_params.option.groupingType.time,[1]]]
            };
        }
        if(data.result.timelineOnly){
            if(!isset(dailyHistoryChart[_params.el]) || !isset(dailyHistoryChart[_params.el].nbTimeline)){
            nbTimeline = 1;
            }else{
            dailyHistoryChart[_params.el].nbTimeline++;
            nbTimeline = dailyHistoryChart[_params.el].nbTimeline;
            }
            var series = {
            type: 'flags',
            name: (isset(_params.option.name)) ? _params.option.name + ' '+ data.result.unite : data.result.history_name+ ' '+ data.result.unite,
            data: [],
            id: _params.cmd_id,
            color: _params.option.graphColor,
            shape: 'squarepin',
            cursor: 'pointer',
            y : -30 - 25*(nbTimeline - 1),
            point: {
            }
            }
            for(var i in data.result.data){
            series.data.push({
                x : data.result.data[i][0],
                title : data.result.data[i][1]
            });
            }
        }else{
            var series = {
            dataGrouping: dataGrouping,
            type: _params.option.graphType,
            id: _params.cmd_id,
            cursor: 'pointer',
            name: (isset(_params.option.name)) ? _params.option.name + ' ' : data.result.history_name+ ' '+ _params.option.unite,
            data: data.result.data,
            color: _params.option.graphColor,
            stack: _params.option.graphStack,
            step: _params.option.graphStep,
            yAxis: _params.option.graphScale,
            stacking : stacking,
            textShadow : false,
            tooltip: _params.tooltipSeries,
            point: {
            }
            };
        }
        if(isset(_params.option.graphZindex)){
            series.zIndex = _params.option.graphZindex;
        }

        if(isset(_params.index)){
            series.index = _params.index;
        }
    
        if (!isset(dailyHistoryChart[_params.el]) || (isset(_params.newGraph) && _params.newGraph == true)) {
            dailyHistoryChart[_params.el] = {};
            dailyHistoryChart[_params.el].cmd = new Array();
            dailyHistoryChart[_params.el].color = 0;
            dailyHistoryChart[_params.el].nbTimeline = 1;

            if(_params.dateRange == '30 min'){
            var dateRange = 0
            }else  if(_params.dateRange == '1 hour'){
            var dateRange = 1
            }else  if(_params.dateRange == '1 day'){
            var dateRange = 2
            }else  if(_params.dateRange == '7 days'){
            var dateRange = 3
            }else  if(_params.dateRange == '1 month'){
            var dateRange = 4
            }else  if(_params.dateRange == '1 year'){
            var dateRange = 5
            }else  if(_params.dateRange == 'all'){
            var dateRange = 6
            }else{
            var dateRange = 3;
            }
            dailyHistoryChart[_params.el].type = _params.option.graphType;
            console.log("[type de graph] => " + _params.el)
            dailyHistoryChart[_params.el].chart = new Highcharts.StockChart({
            chart: charts,
            credits: {
                text: '',
                href: '',
            },
            navigator: {
                enabled:  _params.showNavigator,
                series: {
                includeInCSVExport: false
                }
            },
            exporting: {
                enabled: _params.enableExport || ($.mobile) ? false : true
            },
            rangeSelector: {
                buttons: [{
                type: 'minute',
                count: 30,
                text: '30m'
                }, {
                type: 'hour',
                count: 1,
                text: 'H'
                }, {
                type: 'day',
                count: 1,
                text: 'J'
                }, {
                type: 'week',
                count: 1,
                text: 'S'
                }, {
                type: 'month',
                count: 1,
                text: 'M'
                }, {
                type: 'year',
                count: 1,
                text: 'A'
                }, {
                type: 'all',
                count: 1,
                text: 'Tous'
                }],
                selected: dateRange,
                inputEnabled: false,
                enabled: _params.showTimeSelector
            },
            legend: legend,
            yAxis: [{
                            format: '{value}',
                            showEmpty: false,
                            minPadding: 0.001,
                            maxPadding: 0.001,
                            showLastLabel: true,
                            }, {
                            opposite: false,
                            format: '{value}',
                            showEmpty: false,
                            gridLineWidth: 1,
                            minPadding: 0.001,
                            maxPadding: 0.001,
                            labels: {
                                align: 'left',
                                x: 2
                            },
                            dataLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                textOutline: 'none',
                                textShadow: '0 0 3px black',
                                fontSize: '10px',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'white'
                            },
                        },
                        stackLabels: {
                                enabled: false,
                                style: {
                                    fontWeight: 'bold',
                                    textOutline: 'none',
                                    textShadow: '0 0 3px black',
                                    fontSize: '10px',
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'white'
                                }
                            },
                        }],
            xAxis: {
                type: 'datetime',
                ordinal: false,
                maxPadding : 0.02,
                minPadding : 0.02
            },
            scrollbar: {
                barBackgroundColor: 'gray',
                barBorderRadius: 7,
                barBorderWidth: 0,
                buttonBackgroundColor: 'gray',
                buttonBorderWidth: 0,
                buttonBorderRadius: 7,
                trackBackgroundColor: 'none', trackBorderWidth: 1,
                trackBorderRadius: 8,
                trackBorderColor: '#CCC',
                enabled: _params.showScrollbar
            },
            plotOptions: _params.plotOptions,
            tooltip: _params.tooltip,
            series: [series]
            });
        } else {
            dailyHistoryChart[_params.el].chart.addSeries(series);
        }
        dailyHistoryChart[_params.el].cmd[_params.cmd_id] = {option: _params.option, dateRange: _params.dateRange};
        }

        dailyHistoryChart[_params.el].color++;
        if (dailyHistoryChart[_params.el].color > 9) {
        dailyHistoryChart[_params.el].color = 0;
        }

        $.hideLoading();
        if (typeof (init(_params.success)) == 'function') {
        _params.success(data.result);
        }
    }
    });
}
