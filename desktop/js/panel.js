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
 var versionEnCours = "06/11/2020 12:00";
 var globalEqLogic = $( "#eqlogic_select option:selected" ).val();
 var isCoutVisible = false;
 var puissanceSeries = [];
 var commandesPuissance = [];
 var commandesStat = [];
 var dailyHistoryChart = [];
 var tableCouts = [];
$(".in_datepicker").datepicker();

$('#bt_teleinfoPanelSante').on('click', function() {
    $('#md_modal').dialog({title: "{{Santé}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=panel_sante').dialog('open');
});

$('#bt_teleinfoCout').on('click', function() {
    if (isCoutVisible === false){
		isCoutVisible = true;
		$('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]').show();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=month]').show();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]').show();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=year]').show();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=day]').show();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]').show();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]').show();
	}
	else{
		isCoutVisible = false;
		$('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]').hide();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=month]').hide();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]').hide();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=year]').hide();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=day]').hide();
		$('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]').hide();
		$('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]').hide();
	}
});

$( "#eqlogic_select" ).change(function() {
    globalEqLogic = $( "#eqlogic_select option:selected" ).val();
    initHistoryTrigger();
    //drawStackColumnChart('div_graphGlobalIndex', null);
    loadData();
});

$('#bt_validChangeDate').on('click',function(){
    puissanceSeries = [];
    //console.log($('#div_graphGlobalIndex').attr("cmd_id"));
    $.each( commandesPuissance, function( key, value ) {
        getObjectHistory('div_graphGlobalPower', 'Simple', {'id': value.id, 'name': value.name}, value.color, 'refresh');
    });
    $.each( commandesStat, function( key, value ) {
        getDailyHistory(value.graph, {'id': value.id, 'name': value.name} , value.color, value.stackGraph, value.diviseur);
    });
});
initHistoryTrigger();

//displayTeleinfo(object_id);
//drawStackColumnChart('div_graphGlobalIndex', null);

loadData();

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
            var serie0 = 0, serie1 = 1;
            var compteurProd = false;
            var prodEtConso = false;
            var HCHP = false;
            var color = '#7cb5ec';
            var namestat = '';
            console.log("[loadData] Nom de l'objet => " + data.result[globalEqLogic].name);

            console.log("[si c'est prod = 1 ] => " + data.result[globalEqLogic].configuration.ActivationProduction);
            
            console.log("[si c'est HC HP = 1 ] => " + data.result[globalEqLogic].configuration.HCHP);
			
			var x = document.getElementsByClassName('HCHP');			
			var y = document.getElementsByClassName('PROD');			
            if(data.result[globalEqLogic].configuration.HCHP == 0){
                HCHP = false;
				var i;
				for (i = 0; i < x.length; i++) {
					x[i].style.display = 'none';
				}
			}else{
                HCHP = true;
			}
            if(data.result[globalEqLogic].configuration.ActivationProduction == 0){
				var j;
				for (j = 0; j < y.length; j++) {
					y[j].style.display = 'none';
				}
			}
            
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
            }

            try {
                var diviseur = 1;
				todayHp = 0;
				todayHC = 0;
				monthHp = 0;
				monthHC = 0;
				yearHp = 0;
				yearHC = 0;
                for(cmd in data.result[globalEqLogic].cmd)
                {
                    console.log("[Courbes à tracer de " + globalEqLogic + " ] => " + data.result[globalEqLogic].cmd[cmd].logicalId)
                    if (data.result[globalEqLogic].cmd[cmd].configuration.calculValueOffset!==undefined){
                       tdiviseur = (data.result[globalEqLogic].cmd[cmd].configuration.calculValueOffset).split("/")
                       diviseur = tdiviseur[1]
                       if (diviseur==undefined){
                            diviseur = 1
                       }
                       console.log("[diviseur] => " + diviseur)
                    }
                    try{
                      switch(data.result[globalEqLogic].cmd[cmd].logicalId)
                        {
                            case "STAT_YESTERDAY_HC":
                                if(!compteurProd&&HCHP){
                                    stackGraph = 1
									color = '#ed9448';
                                    getMonthlyHistory('div_graphGlobalIndex',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=monthLastYear]'), 'monthLastYear' , "coutHC", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=month]'), 'month' , "coutHC", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=yearLastYear]'), 'yearLastYear' , "coutHC", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=year]'), 'year' , "coutHC", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=yesterday]'), 'yesterday' , "coutHC", data.result[globalEqLogic].cmd[cmd]);

                                    console.log("[loadData][STAT_YESTERDAY_HC] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=monthLastYear]'), 'monthLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yearLastYear]'), 'yearLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=month]'), 'month' , data.result[globalEqLogic].cmd[cmd]);
                                    monthHC = $('.teleinfoAttr[data-l1key=consoHC][data-l2key=month]').text.value;
									console.log("[loadData][STAT_YESTERDAY_HC] " + monthHC)
									getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=year]'), 'year' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHC][data-l2key=yesterday]'), 'yesterday' , data.result[globalEqLogic].cmd[cmd]);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color,"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);

                                }
                            break;
                            case "STAT_YESTERDAY_HP":
                                if(!compteurProd&&HCHP){
                                    stackGraph = 1
                                    color = '#7cb5ec';
                                    getAnnualHistory('div_graphGlobalAnnual',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    getMonthlyHistory('div_graphGlobalIndex',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
								    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=monthLastYear]'), 'monthLastYear' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
								    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=month]'), 'month' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=yearLastYear]'), 'yearLastYear' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=year]'), 'year' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=yesterday]'), 'yesterday' , "coutHP", data.result[globalEqLogic].cmd[cmd]);

                                    console.log("[loadData][STAT_YESTERDAY_HP 1] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=monthLastYear]'), 'monthLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yearLastYear]'), 'yearLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=month]'), 'month' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=year]'), 'year' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=consoHP][data-l2key=yesterday]'), 'yesterday' , data.result[globalEqLogic].cmd[cmd]);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color,"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);

                                }
                            break;
                            case "STAT_YESTERDAY":
                                console.log("[loadData][STAT_YESTERDAY] " + data.result[globalEqLogic].cmd[cmd].id);
                                if(!compteurProd){
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=monthLastYear]'), 'monthLastYear' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=month]'), 'month' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=yearLastYear]'), 'yearLastYear' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=year]'), 'year' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=yesterday]'), 'yesterday' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
//                                  
                                    console.log("[loadData][STAT_YESTERDAY] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=monthLastYear]'), 'monthLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yearLastYear]'), 'yearLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=month]'), 'month' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=year]'), 'year' , data.result[globalEqLogic].cmd[cmd]);
                                    getCommandHistoryValue($('.teleinfoAttr[data-l1key=conso][data-l2key=yesterday]'), 'yesterday' , data.result[globalEqLogic].cmd[cmd]);
								}
								if(!compteurProd&&(!HCHP)){
                                    stackGraph = 0
                                    color = '#7cb5ec';
                                    getAnnualHistory('div_graphGlobalAnnual',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    getMonthlyHistory('div_graphGlobalIndex',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color,"stackGraph":stackGraph,"diviseur":diviseur});
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                }
                                break;
                            case "STAT_YESTERDAY_PROD":
                                if(compteurProd||prodEtConso){
                                    stackGraph = 0
                                    color = '#f00707';
                                    console.log("[loadData][STAT_YESTERDAY_PROD] " + data.result[globalEqLogic].cmd[cmd].id);
                                    //if(!prodEtConso){
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=monthLastYear]'), 'monthLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=monthLastYearPartial]'), 'monthLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yearLastYear]'), 'yearLastYear' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yearLastYearPartial]'), 'yearLastYearPartial' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=month]'), 'month' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=year]'), 'year' , data.result[globalEqLogic].cmd[cmd]);
                                      getCommandHistoryValue($('.teleinfoAttr[data-l1key=prod][data-l2key=yesterday]'), 'yesterday' , data.result[globalEqLogic].cmd[cmd]);
									  getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=monthLastYear]'), 'monthLastYear' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
									  getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=month]'), 'month' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
									  getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yearLastYear]'), 'yearLastYear' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
									  getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=year]'), 'year' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
									  getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=yesterday]'), 'yesterday' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
                                      commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color,"stackGraph":stackGraph,"diviseur":diviseur});
                                    //}
                                    getAnnualHistory('div_graphGlobalAnnual',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    getMonthlyHistory('div_graphGlobalIndex',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd], color, stackGraph, diviseur);
                                }
                                break;
                            case "SINSTI":
                            case "SINST1":
                                if(compteurProd||prodEtConso){
                                    color = '#f00707';
                                    commandesPuissance.push({"id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color});
                                    console.log("[loadData][SINST1 ou SINSTI] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getObjectHistory('div_graphGlobalPower', 'Simple', data.result[globalEqLogic].cmd[cmd], color);
                                }
                                break;
                            case "SINSTS":
                            case "PAPP":
                                if(!compteurProd){
                                    color = '#7cb5ec';
                                    commandesPuissance.push({"id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name,"color":color});
                                    console.log("[loadData][PAPP ou SINSTS] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getObjectHistory('div_graphGlobalPower', 'Simple', data.result[globalEqLogic].cmd[cmd], color);
                                }
                                break;
                            case "STAT_TODAY":
                                if(!compteurProd){
                                    console.log("[loadData][STAT_TODAY] " + data.result[globalEqLogic].cmd[cmd].value);
                                    $('.teleinfoAttr[data-l1key=conso][data-l2key=day]').text(((data.result[globalEqLogic].cmd[cmd].value)/1000).toFixed(2));
									getCommandHistoryCout($('.teleinfoAttr[data-l1key=cout][data-l2key=day]'), 'day' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
                                }
                                break;
							case "STAT_TODAY_HC":
								if(!compteurProd&&HCHP){
                                    console.log("[loadData][STAT_TODAY_HC] " + data.result[globalEqLogic].cmd[cmd].value);
									$('.teleinfoAttr[data-l1key=consoHC][data-l2key=day]').text(((data.result[globalEqLogic].cmd[cmd].value)/1000).toFixed(2));
									getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHC][data-l2key=day]'), 'day' , "coutHC", data.result[globalEqLogic].cmd[cmd]);
								}
							break;
							case "STAT_TODAY_HP":
								if(!compteurProd&&HCHP){
                                    console.log("[loadData][STAT_TODAY_HP] " + data.result[globalEqLogic].cmd[cmd].value);
									$('.teleinfoAttr[data-l1key=consoHP][data-l2key=day]').text(((data.result[globalEqLogic].cmd[cmd].value)/1000).toFixed(2));
									getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutHP][data-l2key=day]'), 'day' , "coutHP", data.result[globalEqLogic].cmd[cmd]);
								}
							break;
                            case "STAT_TODAY_PROD":
                                if(compteurProd||prodEtConso){
                                    console.log("[loadData][STAT_TODAY_PROD] " + data.result[globalEqLogic].cmd[cmd].value);
                                    $('.teleinfoAttr[data-l1key=prod][data-l2key=day]').text(((data.result[globalEqLogic].cmd[cmd].value)/1000).toFixed(2));
 									getCommandHistoryCout($('.teleinfoAttr[data-l1key=coutProd][data-l2key=day]'), 'day' , "coutProd", data.result[globalEqLogic].cmd[cmd]);
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
        }
    });
}

function getObjectHistory(div, type, object, color, action = 'none') {
    dailyHistoryChart[div] = null;
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
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> Wh<br/>',
                        shared: true
                    },
                    option: {
                        name : object.name,
                        graphcolor : color,
                        derive : 0,
                        graphType : 'line',
                        graphZindex :3,
                        graphScale : 1,
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

function getDailyHistory(div,  object, color, stackGraph, diviseur, logical = 0) {
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
    console.log("[getDailyHistory] Commande = " + object.name);
    console.log("[getDailyHistory] Récupération de div " + div);
 
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
                    },
                    tooltip : {
                        valueDecimals: 2,
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> kWh<br/>',
                        shared: true
                    },
                    divide:1000/diviseur,
                });

	jeedom.config.load({
        plugin: "teleinfo",
        configuration : "outside_temp",
        error: function (error) {
        },
        success: function (myId) {
            if ((myId != '') && (!object.name.includes("PROD")) && (!object.name.includes("HC"))){
                console.log("[getDailyHistory] Id température exterieure : " + myId)
                teleinfoDrawChart({
                                cmd_id: myId,
                                el: div,
                                dateRange : 'all',
                                dateStart: from,
                                dateEnd: to,
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
                                    groupingType:"average::day"
                                },
                            });
            }
            else{
                console.log("[getDailyHistory] + de 1 courbes ou Pas de température extérieure")
            }
        }
    });
}

function getMonthlyHistory(div,  object, color, stackGraph, diviseur) {
//    var color = '#7cb5ec';
    var from = moment().subtract(18, 'months').startOf('month').format('YYYY-MM-DD 00:00:00');
    var to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
//    if (object.logicalId.includes("PROD")){
//        color = '#ed9448';
//    }
//    if (object.logicalId.includes("HP")){
//        color = '#ed9448';
//    }
    console.log("[getMonthlyHistory] Récupération de div " + div);
    console.log("[getMonthlyHistory] Récupération de l'historique pour la période du " + from + " au " + to);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: from,
                    dateEnd: to,
                    showNavigator : false,
                    option: {
                        name : object.name,
						graphColor: color,
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphStack : stackGraph,
                        graphType : 'column',
                        graphZindex : 1,
                        groupingType:"sum::month"
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> kWh<br/>',
                        shared : true
                        //pointFormat: '{series.name}</span>: <b>{point.y} kWh</b><br/>',
                    },
                    tooltip : {
                        stacking : 'normal',
                        shared : true,
                        valueDecimals: 2
                    },
/*                    plotOptions : {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black',
                                    fontSize: '10px',

                                },
                                format: "{point.y:.2f}",
                            }
                        }
                    },
*/                    divide:1000/diviseur,
    });
//
    jeedom.config.load({
        plugin: "teleinfo",
        configuration : "outside_temp",
        error: function (error) {
        },
        success: function (myId) {
            if ((myId != '') && (!object.logicalId.includes("PROD")) && (!object.logicalId.includes("HC"))){
                console.log("[getMonthlyHistory] Id température exterieure : " + myId)
                teleinfoDrawChart({
                                cmd_id: myId,
                                el: div,
                                dateRange : 'all',
                                dateStart: from,
                                dateEnd: to,
                                showNavigator : false,
                                tooltipSeries: {
                                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} °C</b><br/>',
                                    shared: true
                                },
                                option: {
									name : 'Température ext.',
						            graphType : 'line',
                                    graphColor: '#87b125',
                                    derive : 0,
                                    graphZindex : 2,
                                    groupingType:"average::month"
                                },
                            });
            }
            else{
                console.log("[getDailyHistory] + de 1 courbes ou Pas de température extérieure")
            }
        }
    });

//
}


function getAnnualHistory(div,  object, color, stackGraph, diviseur) {
//    var color = '#7cb5ec';
    var from = moment().subtract(18, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
    var to = moment().endOf('year').format('YYYY-MM-DD 23:59:59');
//    if (object.logicalId.includes("PROD")){
//        color = '#ed9448';
//    }
//    if (object.logicalId.includes("HP")){
//        color = '#ed9448';
//    }
    console.log("[getAnnualHistory] Récupération de div " + div);
    console.log("[getAnnualHistory] Récupération de l'historique pour la période du " + from + " au " + to);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: from,
                    dateEnd: to,
                    showNavigator : false,
                    option: {
                        name : object.name,
						graphColor: color,
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphStack : stackGraph,
                        graphType : 'column',
                        graphZindex : 1,
                        groupingType:"sum::year"
                    },
                    tooltipSeries: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> kWh<br/>',
                        shared : true
                        //pointFormat: '{series.name}</span>: <b>{point.y} kWh</b><br/>',
                    },
                    tooltip : {
                        stacking : 'normal',
                        shared : true,
                        valueDecimals: 0
                    },
/*                    plotOptions : {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black',
                                    fontSize: '10px',

                                },
                                format: "{point.y:.2f}",
                            }
                        }
                    },
*/                    divide:1000/diviseur,
    });
//
    jeedom.config.load({
        plugin: "teleinfo",
        configuration : "outside_temp",
        error: function (error) {
        },
        success: function (myId) {
            if ((myId != '') && (!object.logicalId.includes("PROD")) && (!object.logicalId.includes("HC"))){
                console.log("[getAnnualHistory] Id température exterieure : " + myId)
                teleinfoDrawChart({
                                cmd_id: myId,
                                el: div,
                                dateRange : 'all',
                                dateStart: from,
                                dateEnd: to,
                                showNavigator : false,
                                tooltipSeries: {
                                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} °C</b><br/>',
                                    shared: true
                                },
                                option: {
									name : 'Température ext.',
						            graphType : 'line',
                                    graphColor: '#87b125',
                                    derive : 0,
                                    graphZindex : 2,
                                    groupingType:"average::year"
                                },
								tooltipSeries : {
									valueDecimals: 2
								},
                            });
            }
            else{
                console.log("[getAnnualHistory] + de 1 courbe courbe ou Pas de température extérieure")
            }
        }
    });

//
}





function getCommandHistoryValue(div, type , object) {
    var from = moment().format('YYYY-MM-DD 00:00:00');
    var to = moment().format('YYYY-MM-DD 23:59:59');
    switch (type){
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
            from = moment().subtract(2, 'days').format('YYYY-MM-DD 23:59:59');
            to = moment().subtract(1, 'days').format('YYYY-MM-DD 00:00:01');
        break;
        case 'monthLastYearPartial':
            from = moment().subtract(1, 'years').startOf('month').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYearPartial':
            from = moment().subtract(1, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
    }


    dailyHistoryChart[div] = null;
    jeedom.history.get({
        cmd_id: object.id,
        dateStart : from,
        dateEnd : to,
        error: function (error) {
        },
        success: function (myCommandHistory) {
            if(myCommandHistory.data.length == 1){
              div.text((myCommandHistory.maxValue / 1000).toFixed(2))
              console.log("[Object 1] " + object.id + " [getCommandHistoryValue] " + type + " | from : " + from + " | to : " + to + " | value : " + (myCommandHistory.maxValue / 1000).toFixed(2));
            }else {
              //myCommandHistory.data.splice(-1,1);
              div.text((myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / 1000).toFixed(2));
              console.log("[Object 2] " + object.id + " [getCommandHistoryValue] " + type + " | from : " + from + " | to : " + to + " | value : " + myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / 1000);
            }
        }
    });
}

function getCommandHistoryCout(div, type, cout, object) {
    var from = moment().format('YYYY-MM-DD 00:00:00');
    var to = moment().format('YYYY-MM-DD 23:59:59');
    switch (type){
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
            from = moment().subtract(1, 'days').startOf('day').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'days').endOf('day').format('YYYY-MM-DD 23:59:59');
        break;
        case 'monthLastYearPartial':
            from = moment().subtract(1, 'years').startOf('month').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
        case 'yearLastYearPartial':
            from = moment().subtract(1, 'years').startOf('year').format('YYYY-MM-DD 00:00:00');
            to = moment().subtract(1, 'years').format('YYYY-MM-DD 23:59:59');
        break;
    }


    jeedom.config.load({
        plugin: "teleinfo",
        configuration : cout,
        error: function (error) {
        },
        success: function (valeurCout) {
            if (valeurCout != ''){

                jeedom.history.get({
                    cmd_id: object.id,
                    dateStart : from,
                    dateEnd : to,
                    error: function (error) {
                    },
                    success: function (myCommandHistory) {
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
                            //myCommandHistory.data.splice(-1,1);
                            tableCouts[type] = tableCouts[type] + (myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / 1000) * valeurCout;
							console.log("[getCommandHistoryCout 3] " + type + " " + cout + " : " + myCommandHistory.data.reduce(function(prev, cur) {  return prev + cur[1];}, 0) / 1000 + " * " + valeurCout + " = " + tableCouts[type])
                          }
						}

                        div.text(' (~' + tableCouts[type].toFixed(2) + ' €)');
                    }
                });
            }
            else{
                console.log("[getCommandHistoryCout] Pas de cout " + cout)
            }
        }
    });
}







function drawPieChart(_el, _data, _title) {
    new Highcharts.Chart({
        chart: {
            renderTo: _el,
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            height: 225,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        credits: {
            /*text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',*/
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
            }
        },
        series: [{
                type: 'pie',
                name: 'Browser share',
                data: _data
            }]
    });
}
function drawStackGraph(_el, _data) {
    var series = [];
    for (var i in _data) {
        if (isset(_data[i].data.history.power) && _data[i].data.history.power.length > 0) {
            var serie = {
                step: true,
                name: _data[i].name,
                data: _data[i].data.history.power,
            };
            series.push(serie);
        }
    }

    if (!$.mobile) {
        var legend = {
            enabled: true,
            borderColor: 'black',
            borderWidth: 2,
            shadow: true
        };
    } else {
        var legend = {};
    }

    new Highcharts.StockChart({
        chart: {
            zoomType: 'x',
            type: 'area',
            renderTo: _el,
            height: 225,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            },
        },
        credits: {
            /*text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',*/
        },
        navigator: {
            enabled: false
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
            selected: 6,
            inputEnabled: false
        },
        legend: legend,
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
            valueDecimals: 2,
        },
        yAxis: {
            format: '{value}',
            showEmpty: false,
            showLastLabel: true,
            min: 0,
            labels: {
                align: 'right',
                x: -5
            }
        },
        xAxis: {
            type: 'datetime',
            ordinal: false,
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
            trackBorderColor: '#CCC'
        },
        series: series
    });

}


function drawStackColumnChart(_el, _data){

    new Highcharts.Chart({
        chart: {
            renderTo: _el,
            type: 'column',
            height: 350,
            spacingTop: 5,
            spacingLeft: -15,
            spacingRight: 0,
            spacingBottom: 0
        },
        title: {
            text: ''
        },
        credits: {
            text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',
        },
        xAxis: {
            categories: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],

        },
        yAxis: {
            min: 0,
            title: {
                text: 'kWh'
            },
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        navigator: {
            enabled: false
        },
        legend: {
            align: 'right',
            x: -30,
            verticalAlign: 'top',
            y: 25,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.x + '</b><br/>' +
                    this.series.name + ': ' + this.y + ' kWh<br/>' +
                    'Total: ' + this.point.stackTotal + ' kWh';
            }
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    style: {
                        textShadow: '0 0 3px black'
                    }
                }
            }
        },
        series: [{
            name: 'HP',
            color: '#7cb5ec',
        }, {
            name: 'HC',
            color: '#ed9448',
        }]
    });

}

function drawSimpleGraph(_el, _serie) {
    var legend = {
        enabled: true,
        borderColor: 'black',
        borderWidth: 2,
        shadow: true
    };

    new Highcharts.StockChart({
        chart: {
            zoomType: 'x',
            renderTo: _el,
            height: 350,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
        },
        credits: {
            text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',
        },
        navigator: {
            enabled: false
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
            selected: 6,
            inputEnabled: false
        },
        legend: legend,
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
            valueDecimals: 2,
        },
        yAxis: {
            format: '{value}',
            showEmpty: false,
            showLastLabel: true,
            min: 0,
            labels: {
                align: 'right',
                x: -5
            }
        },
        series: _serie
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

      if (init(_params.plotOptions) == '') {
        _params.plotOptions = {
          pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              format: '<b>{point.name}</b>: {point.percentage:.1f} %',
              style: {
                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
              }
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
            name: (isset(_params.option.name)) ? _params.option.name + ' '+ data.result.unite : data.result.history_name+ ' '+ data.result.unite,
            data: data.result.data,
            color: _params.option.graphColor,
            stack: _params.option.graphStack,
            step: _params.option.graphStep,
            yAxis: _params.option.graphScale,
            stacking : stacking,
            tooltip: _params.tooltipSeries,
            point: {
            }
          };
        }
        if(isset(_params.option.graphZindex)){
          series.zIndex = _params.option.graphZindex;
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
                          stackLabels: {
                              enabled: true,
                              style: {
                                  fontWeight: 'bold',
                                  color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
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

      var extremes = dailyHistoryChart[_params.el].chart.xAxis[0].getExtremes();
      var plotband = jeedom.history.generatePlotBand(extremes.min,extremes.max);
      for(var i in plotband){
        dailyHistoryChart[_params.el].chart.xAxis[0].addPlotBand(plotband[i]);
      }
      $.hideLoading();
      if (typeof (init(_params.success)) == 'function') {
        _params.success(data.result);
      }
    }
  });
}