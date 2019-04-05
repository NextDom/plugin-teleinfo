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
 var globalEqLogic = $( "#eqlogic_select option:selected" ).val();
 var puissanceSeries = [];
 var commandesPuissance = [];
 var commandesStat = [];
 var dailyHistoryChart = [];
$(".in_datepicker").datepicker();


$('#bt_teleinfoPanelSante').on('click', function() {
    $('#md_modal').dialog({title: "{{Santé}}"});
    $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=panel_sante').dialog('open');
});

$( "#eqlogic_select" ).change(function() {
    globalEqLogic = $( "#eqlogic_select option:selected" ).val();
    initHistoryTrigger();
    drawStackColumnChart('div_graphGlobalIndex', null);
    loadData();
});

$('#bt_validChangeDate').on('click',function(){
    puissanceSeries = [];
    //console.log($('#div_graphGlobalIndex').attr("cmd_id"));
    $.each( commandesPuissance, function( key, value ) {
        getObjectHistory('div_graphGlobalPower', 'Simple', {'id': value.id, 'name': value.name}, 'refresh');
    });
    $.each( commandesStat, function( key, value ) {
        getDailyHistory(value.graph, {'id': value.id, 'name': value.name}, 'refresh');
    });
});
initHistoryTrigger();

//displayTeleinfo(object_id);
drawStackColumnChart('div_graphGlobalIndex', null);

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
            console.log("[loadData] Objet téléinfo récupéré : " + globalEqLogic);
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            var serie0 = 0, serie1 = 1;
            var compteurProd = false;
            console.log("[loadData] => " + data.result[globalEqLogic].name);
            if(data.result[globalEqLogic].configuration.abonnement){
                $('.teleinfoAttr[data-l1key=abonnement][data-l2key=type]').text(data.result[globalEqLogic].configuration.abonnement);
                if (data.result[globalEqLogic].configuration.abonnement.includes("PROD")){
                    compteurProd = true;
                    $('#spanTitreResume').html('<i style="font-size: initial;" class="icon fas fa-leaf"></i> Ma Production');
                }
                else{
                    $('#spanTitreResume').html('<i style="font-size: initial;" class="fas fa-bolt"></i> Ma Consommation');
                }
            }

            try {
                var chart = $('#div_graphGlobalIndex').highcharts();
                console.log(data.result[globalEqLogic].cmd);
                for(cmd in data.result[globalEqLogic].cmd)
                {
                    try{
                        switch(data.result[globalEqLogic].cmd[cmd].logicalId)
                        {
                            case "STAT_YESTERDAY":
                                break;
                            case "SINSTI":
                                if(compteurProd){
                                    commandesPuissance.push({"id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name});
                                    console.log("[loadData][PAPP] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getObjectHistory('div_graphGlobalPower', 'Simple', data.result[globalEqLogic].cmd[cmd]);
                                }
                                break;
                            case "SINSTS":
                            case "PAPP":
                                if(!compteurProd){
                                    commandesPuissance.push({"id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name});
                                    console.log("[loadData][PAPP] " + data.result[globalEqLogic].cmd[cmd].id);
                                    getObjectHistory('div_graphGlobalPower', 'Simple', data.result[globalEqLogic].cmd[cmd]);
                                }
                                break;
                            case "STAT_TODAY":
                                if(!compteurProd){
                                    console.log("[loadData][STAT_TODAY] " + data.result[globalEqLogic].cmd[cmd].value);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name});
                                    $('.teleinfoAttr[data-l1key=conso][data-l2key=day]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd])
                                }
                                break;
                            case "STAT_TODAY_PROD":
                                if(compteurProd){
                                    console.log("[loadData][STAT_TODAY_PROD] " + data.result[globalEqLogic].cmd[cmd].value);
                                    commandesStat.push({"graph":"div_graphGlobalJournalier", "id":data.result[globalEqLogic].cmd[cmd].id,"name":data.result[globalEqLogic].cmd[cmd].name});
                                    $('.teleinfoAttr[data-l1key=conso][data-l2key=day]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                    getDailyHistory('div_graphGlobalJournalier',data.result[globalEqLogic].cmd[cmd])
                                    getMonthlyHistory('div_graphGlobalIndex',data.result[globalEqLogic].cmd[cmd])
                                }
                                break;
                            case "STAT_MONTH":
                                console.log("[loadData][STAT_MONTH] " + data.result[globalEqLogic].cmd[cmd].value);
                                $('.teleinfoAttr[data-l1key=conso][data-l2key=month]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                break;
                            case "STAT_MONTH_LAST_YEAR":
                                console.log("[loadData][STAT_MONTH_LAST_YEAR] " + data.result[globalEqLogic].cmd[cmd].value);
                                $('.teleinfoAttr[data-l1key=conso][data-l2key=monthlastyear]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                break;
                            case "STAT_YEAR_LAST_YEAR":
                                console.log("[loadData][STAT_YEAR_LAST_YEAR] " + data.result[globalEqLogic].cmd[cmd].value);
                                $('.teleinfoAttr[data-l1key=conso][data-l2key=yearlastyear]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                break;
                            case "STAT_YEAR":
                                console.log("[loadData][STAT_YEAR] " + data.result[globalEqLogic].cmd[cmd].value);
                                $('.teleinfoAttr[data-l1key=conso][data-l2key=year]').text((data.result[globalEqLogic].cmd[cmd].value)/1000);
                                break;
                            case "STAT_JAN_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 0, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_JAN_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 0, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_FEV_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 1, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_FEV_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 1, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_MAR_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 2, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_MAR_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 2, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_AVR_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 3, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_AVR_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 3, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_MAI_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 4, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_MAI_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 4, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_JUIN_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 5, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_JUIN_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 5, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_JUI_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 6, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_JUI_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 6, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_AOU_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 7, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_AOU_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 7, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_SEP_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 8, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_SEP_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 8, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_OCT_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 9, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_OCT_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 9, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_NOV_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 10, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_NOV_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 10, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_DEC_HP":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 11, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
                                break;
                            case "STAT_DEC_HC":
                                if(data.result[globalEqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 11, y: (data.result[globalEqLogic].cmd[cmd].value)/1000 },true);}
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


function getTeleinfoObjectHistory(div, type, object) {
    $.ajax({
        type: 'POST',
        async:true,
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
            action:'getHistory',
            id:object.id,
            dateRange:'7 days',
            dateStart:'',
            dateEnd:'',
            derive:0,
            allowZero:1
            },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            switch(type)
            {
                case 'Simple':
                    var Series = [{
                        step: true,
                        name: '{{'+object.name+'}}',
                        data: data.result.data,
                        type: 'line',
                        tooltip: {
                            valueDecimals: 2
                        },
                    }];
                    drawSimpleGraph(div, Series);
                break;
                case 'Stack':
                    drawStackGraph(div, Series);
                break;
                case 'StackColumn':
                    console.log(data);
                    drawStackColumnChart(div, Series);
                break;
                case 'Pie':
                    drawPieChart(div, Series, '{{'+object.name+'}}');
                break;
            }
        }
    });
}

function getObjectHistory(div, type, object, action = 'none') {
    dailyHistoryChart[div] = null;
    if(action === 'refresh'){
        startDate = $('#in_startDate').value()
    }else {
        startDate = $('#in_endDate').value()
    }
    console.log("[getObjectHistory] Récupération de l'historique pour la commande " + object.name);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: startDate,
                    dateEnd: $('#in_endDate').value(),
                    showNavigator : false,
                    option: {
                        derive : 0,
                        graphType : 'line',
                        graphZindex :3
                    },
                    newGraph: true,
                });
}

function getDailyHistory(div,  object) {
    dailyHistoryChart[div] = null;
    console.log("[getDailyHistory] Récupération de l'historique pour la commande " + object.name);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: $('#in_startDate').value(),
                    dateEnd: $('#in_endDate').value(),
                    showNavigator : false,
                    option: {
                        graphColor: '#7cb5ec',
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphType : 'column',
                        graphZindex :1,
                        groupingType:"high::day"
                    },
                    divide:1000,
                });

    jeedom.config.load({
        plugin: "teleinfo",
        configuration : "outside_temp",
        error: function (error) {
        },
        success: function (myId) {
            if (myId != ''){
                console.log("[getDailyHistory] Id température exterieure : " + myId)
                teleinfoDrawChart({
                                cmd_id: myId,
                                el: div,
                                dateRange : 'all',
                                dateStart: $('#in_startDate').value(),
                                dateEnd: $('#in_endDate').value(),
                                showNavigator : false,
                                option: {
                                    graphType : 'line',
                                    graphColor: '#87b125',
                                    derive : 0,
                                    graphZindex : 2,
                                    groupingType:"average::day"
                                },
                            });
            }
            else{
                console.log("[getDailyHistory] Pas de température extérieur")
            }
        }
    });
}

function getMonthlyHistory(div,  object) {
    dailyHistoryChart[div] = null;
    console.log("[getMonthlyHistory] Récupération de l'historique pour la commande " + object.name);
    teleinfoDrawChart({
                    cmd_id: object.id,
                    el: div,
                    dateRange : 'all',
                    dateStart: $('#in_startDate').value(),
                    dateEnd: $('#in_endDate').value(),
                    showNavigator : false,
                    option: {
                        graphColor: '#7cb5ec',
                        derive : 0,
                        graphStep: 1,
                        graphScale : 1,
                        graphType : 'column',
                        graphZindex :1,
                        groupingType:"high::month"
                    },
                    divide:1000,
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
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
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
          $('#div_alert').showAlert({message: message, level: 'danger'});
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

      var legend = {borderColor: 'black',borderWidth: 2,shadow: true};
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
            plotOptions: {
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
              }
            },
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
              events: {
                click: function (event) {
                  var deviceInfo = getDeviceType();
                  if ($.mobile || deviceInfo.type == 'tablet' || deviceInfo.type == 'phone') {
                    return
                  }
                  if($('#md_modal2').is(':visible')){
                    return;
                  }
                  if($('#md_modal1').is(':visible')){
                    return;
                  }
                  var id = this.series.userOptions.id;
                  var datetime = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x);
                  var value = this.y;
                  bootbox.prompt("{{Edition de la série :}} <b>" + this.series.name + "</b> {{et du point de}} <b>" + datetime + "</b> ({{valeur :}} <b>" + value + "</b>) ? {{Ne rien mettre pour supprimer la valeur}}", function (result) {
                    if (result !== null) {
                      jeedom.history.changePoint({cmd_id: id, datetime: datetime,oldValue:value, value: result});
                    }
                  });
                }
              }
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
            tooltip: _params.tooltip,
            point: {
              events: {
                click: function (event) {
                  var deviceInfo = getDeviceType();
                  if ($.mobile || deviceInfo.type == 'tablet' || deviceInfo.type == 'phone') {
                    return
                  }
                  if($('#md_modal2').is(':visible')){
                    return;
                  }
                  if($('#md_modal1').is(':visible')){
                    return;
                  }
                  var id = this.series.userOptions.id;
                  var datetime = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x);
                  var value = this.y;
                  bootbox.prompt("{{Edition de la série :}} <b>" + this.series.name + "</b> {{et du point de}} <b>" + datetime + "</b> ({{valeur :}} <b>" + value + "</b>) ? {{Ne rien mettre pour supprimer la valeur}}", function (result) {
                    if (result !== null) {
                      jeedom.history.changePoint({cmd_id: id, datetime: datetime,oldValue:value, value: result});
                    }
                  });
                }
              }
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
            tooltip: _params.tooltip,
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
                                  gridLineWidth: 0,
                                  minPadding: 0.001,
                                  maxPadding: 0.001,
                                  labels: {
                                    align: 'left',
                                    x: 2
                                  }
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
