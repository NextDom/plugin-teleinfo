var populate = [{id:"STAT_TODAY",name:"Conso totale Aujourd'hui"},{id:"STAT_YESTERDAY",name:"Hier"},{id:"STAT_YESTERDAY_HC",name:"Hier HC"},{id:"STAT_YESTERDAY_HP",name:"Hier HP"},{id:"STAT_YESTERDAY_PROD",name:"Hier Prod"},{id:"PAPP",name:"Puissance apparente instantanée compteur normal",commentaire:"Nécessaire seulement si la puissance apparente instantanée linky est nok"},{id:"SINSTS",name:"Puissance apparente instantanée linky",commentaire:"Nécessaire seulement si la puissance apparente instantanée normal est nok"}];

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
                $.fn.showAlert({message: data.result, level: 'danger'});
                return;
            }
			for(eqLogic in data.result)
			{
				for(cmd in data.result[eqLogic].cmd)
				{
					var result = $.grep(populate, function(e){ return e.id == data.result[eqLogic].cmd[cmd].logicalId; });
					if (result.length == 1) {
						result[0].found = 1;
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
			tbody += '<td> <span class="label label-success">OK</span>';
			tbody += '</td>';
            tbody += '<td></td></tr>';
		}else{
			tbody += '<td><span class="label label-danger">Nok</span>';
			tbody += '</td>';
            tbody += '<td>'+ populate[i].commentaire +'</td></tr>';
		}


	}

	$('#table_health tbody').empty().append(tbody);
}
