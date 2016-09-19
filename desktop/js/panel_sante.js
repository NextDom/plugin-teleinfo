var populate = [{id:"STAT_TODAY",name:"Conso totale Aujourd'hui"},{id:"STAT_MONTH",name:"Conso Mois en cours"},{id:"STAT_YEAR",name:"Conso Année en cours"},{id:"PAPP",name:"Puissance apparente instantanée"}];
	
$.ajax({
        type: 'POST',
		async:true,
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
			action:'getInformation'
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
			for(eqLogic in data.result)
			{
				for(cmd in data.result[eqLogic].cmd)
				{
					//console.log(data.result[eqLogic].cmd[cmd].logicalId);
					switch(data.result[eqLogic].cmd[cmd].logicalId)
					{
						case "PAPP":
							var result = $.grep(populate, function(e){ return e.id == data.result[eqLogic].cmd[cmd].logicalId; });
							if (result.length == 1) {
								result[0].found = 1;
							}
						break;
						case "STAT_TODAY":
							var result = $.grep(populate, function(e){ return e.id == data.result[eqLogic].cmd[cmd].logicalId; });
							if (result.length == 1) {
								result[0].found = 1;
							}
							break;
						case "STAT_MONTH":
							var result = $.grep(populate, function(e){ return e.id == data.result[eqLogic].cmd[cmd].logicalId; });
							if (result.length == 1) {
								result[0].found = 1;
							}
							break;
						case "STAT_YEAR":
							var result = $.grep(populate, function(e){ return e.id == data.result[eqLogic].cmd[cmd].logicalId; });
							if (result.length == 1) {
								result[0].found = 1;
							}
							break;
					}
				}
			}
			
			//nodes = data.result['object'].configuration;
			populate_table();
        }
    });
	
function populate_table(){
	var tbody = '';
	for(var i in populate){
		tbody += '<tr>';
		
		tbody += '<td>';
		tbody += '<span style="font-weight : bold;">'+populate[i].name+'</span>';
		tbody += '</td>';
		
		if(populate[i].found == 1){
			tbody += '<td class="alert alert-success">OK';
			tbody += '</td>';		
		}else{
			tbody += '<td class="alert alert-danger">NOK>';
			tbody += '</td>';		
		}
		
		tbody += '<td></td></tr>';
	}
	
	$('#table_health tbody').empty().append(tbody);
}
