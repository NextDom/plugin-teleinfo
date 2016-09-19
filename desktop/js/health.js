var nodes;
var ADCO;
var d = new Date();
$.ajax({
        type: 'POST',
		async:true,
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
			action:'getHealth',
			eqLogicID: $('.eqLogicAttr[data-l1key=logicalId]').value()
			},
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
			nodes = data.result['object'].configuration;
			ADCO = data.result['ADCO'];
			populate_table();
        }
    });
	
function populate_table(){
	
	var tbody = '';
    for(var i in nodes){
		console.log(nodes[i]);
		if(nodes[i].name != "api" && nodes[i].name != "ADCO" && nodes[i].name != null ){
			tbody += '<tr>';
			tbody += '<td>';
			tbody += '<span  class="label label-default" style="font-size : 1em;">'+ADCO+'</span>';
			tbody += '</td>';

			tbody += '<td>';
			tbody += '<span  class="label label-primary" style="font-size : 1em;">'+nodes[i].name+'</span>';
			tbody += '</td>';
			
			tbody += '<td>';
			tbody += '<span class="" style="" title="'+nodes[i].value+'">'+nodes[i].value+'</span>';
			tbody += '</td>';
			
			tbody += '<td>';
			tbody += check_state(nodes[i].name,nodes[i].update_time);
			tbody += '</td>';
			
			tbody += '<td>';
			tbody += '<span class="" style="" title="'+nodes[i].update_time+'">'+nodes[i].update_time+'</span>';
			tbody += '</td>';
			
			tbody += '</tr>';
		}
	}
	$('#table_health tbody').empty().append(tbody);
}

function check_state(name,  datetime){
	
	var group1 = ["BASE","HCHP","HCHC","IINST","PAPP"];
	var group2 = ["PTEC","HHPHC","PEJP","DEMAIN"];
	var group3 = ["IMAX","OPTARIF","ISOUSC"];
	
	if(group1.indexOf(name) != -1){
		if((new Date(datetime).getTime() - d.getTime()) < -1800000){
			return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
		}
		else {
			return '<span  class="label label-success" style="font-size : 1em;">OK</span>';
		}
	}
	else if (group2.indexOf(name) != -1){
		if((new Date(datetime).getTime() - d.getTime()) < -5400000){
			return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
		}
		else {
			return '<span  class="label label-success" style="font-size : 1em;">OK</span>';
		}
		
	}
	else if (group3.indexOf(name) != -1){
		if((new Date(datetime).getTime() - d.getTime()) < -86400000){
			return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
		}
		else {
			return '<span  class="label label-success" style="font-size : 1em;">OK</span>';
		}
	}
	else{
		return '<span  class="label label-default" style="font-size : 1em;">--</span>';		
	}
		
	return result;
}
