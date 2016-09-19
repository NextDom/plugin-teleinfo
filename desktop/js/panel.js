
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
	//console.log($('#div_graphGlobalIndex').attr("cmd_id"));
	getObjectHistory('div_graphGlobalPower', 'Simple', {'id': $('#div_graphGlobalPower').attr("cmd_id"), 'name': $('#div_graphGlobalPower').attr("cmd_name")});
	
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
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
			var serie0 = 0, serie1 = 1;
			for(eqLogic in data.result)
			{
				var chart = $('#div_graphGlobalIndex').highcharts();
				for(cmd in data.result[eqLogic].cmd)
				{
					//console.log(data.result[eqLogic].cmd[cmd].logicalId);
					switch(data.result[eqLogic].cmd[cmd].logicalId)
					{
						case "STAT_YESTERDAY":
							break;
						case "PAPP":
							getObjectHistory('div_graphGlobalPower', 'Simple', data.result[eqLogic].cmd[cmd]);
							break;
						case "STAT_TODAY":
							$('.teleinfoAttr[data-l1key=conso][data-l2key=day]').text((data.result[eqLogic].cmd[cmd].value)/1000);
							break;
						case "STAT_MONTH":
							$('.teleinfoAttr[data-l1key=conso][data-l2key=month]').text((data.result[eqLogic].cmd[cmd].value)/1000);
							break;
						case "STAT_YEAR":
							//console.log(data.result[eqLogic].cmd[cmd]);
							$('.teleinfoAttr[data-l1key=conso][data-l2key=year]').text((data.result[eqLogic].cmd[cmd].value)/1000);
							break;
						case "STAT_JAN_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 0, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_JAN_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 0, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_FEV_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 1, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_FEV_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 1, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_MAR_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 2, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_MAR_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 2, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_AVR_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 3, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_AVR_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 3, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_MAI_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 4, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_MAI_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 4, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_JUIN_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 5, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_JUIN_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 5, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_JUI_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 6, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_JUI_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 6, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_AOU_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 7, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_AOU_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 7, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_SEP_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 8, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_SEP_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 8, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_OCT_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 9, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_OCT_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 9, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_NOV_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 10, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_NOV_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 10, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_DEC_HP":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie0].addPoint({x: 11, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
						case "STAT_DEC_HC":
							if(data.result[eqLogic].cmd[cmd].configuration['type'] == 'panel'){chart.series[serie1].addPoint({x: 11, y: (data.result[eqLogic].cmd[cmd].value)/1000 },true);}
							break;
					}
				}
				serie0 += 2;
				serie1 += 2;
			}
        }
    });

}

function getTeleinfoCmdValue(cmd) {
    $.ajax({
        type: 'POST',
		async:true,
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
			action:'getValue',
			id:object.id
			},
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {

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
	
function getObjectHistory(div, type, object) {
	$('#div_graphGlobalPower').attr( "cmd_id", object.id );
	$('#div_graphGlobalPower').attr( "cmd_name", object.name );
    $.ajax({
        type: 'POST',
		async:true,
        url: "core/ajax/cmd.ajax.php", // url du fichier php
        data: {
			action:'getHistory',
			id:object.id,
			dateRange:'7 days',
			dateStart:$('#in_startDate').value(),
			dateEnd:$('#in_endDate').value(),
			derive:'',
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
					//console.log(data.result.data);
					drawSimpleGraph(div, Series);
				break;
				case 'Stack':
					drawStackGraph(div, Series);
				break;
				case 'StackColumn':
					console.log(data.result.data);
					drawStackColumnChart(div, Series);
				break;
				case 'Pie':
					drawPieChart(div, Series, '{{'+object.name+'}}');
				break;
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
                    this.series.name + ': ' + this.y + '<br/>' +
                    'Total: ' + this.point.stackTotal;
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
            name: 'HP'/*,
            data: [5, 3, 4, 7, 2]*/
        }, {
            name: 'HC'/*,
            data: [2, 2, 3, 2, 1]*/
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
        series: _serie
    });
}

function displayTeleinfo(object_id) {
    /*$.ajax({
        type: 'POST',
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
            action: 'getTeleinfo',
            object_id: object_id,
            version: 'dashboard'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            var icon = '';
            if (isset(data.result.object.display) && isset(data.result.object.display.icon)) {
                icon = data.result.object.display.icon;
            }
            $('.objectName').empty().append(icon + ' ' + data.result.object.name);
            $('#div_displayEquipement').empty();
            $('#div_charts').empty();
            for (var i in data.result.eqLogics) {
                $('#div_displayEquipement').append(data.result.eqLogics[i].html);
                var div_graph = '<legend>' + data.result.eqLogics[i].eqLogic.name + '</legend>'
                div_graph += '<div class="chartContainer" id="div_graph' + data.result.eqLogics[i].eqLogic.id + '"></div>';
                $('#div_charts').append(div_graph);
                graphTeleinfo(data.result.eqLogics[i].eqLogic.id);
            }
            positionEqLogic();
            $('#div_displayEquipement').packery({columnWidth: 1});
        }
    });*/
}

function graphTeleinfo(_cmd_id) {
	if (isset(jeedom.history.chart['div_graphGlobalPower'])) {
		jeedom.history.chart['div_graphGlobalPower'].chart.get(parseInt(_cmd_id)).remove();
	}
		
	jeedom.history.drawChart({
            cmd_id: _cmd_id,
            el: 'div_graphGlobalPower',
            dateRange : 'all',
            dateStart : $('#in_startDate').value(),
            dateEnd :  $('#in_endDate').value(),
            success: function (data) {
                /*if(isset(data.cmd.display)){
                    if (init(data.cmd.display.graphStep) != '') {
                        $('#cb_step').off().value(init(data.cmd.display.graphStep));
                    }
                    if (init(data.cmd.display.graphType) != '') {
                        $('#sel_chartType').off().value(init(data.cmd.display.graphType));
                    }
                    if (init(data.cmd.display.graphDerive) != '') {
                        $('#cb_derive').off().value(init(data.cmd.display.graphDerive));
                    }
                }*/
                //initHistoryTrigger();
            }
        });
}

function initHistoryTrigger() {

}