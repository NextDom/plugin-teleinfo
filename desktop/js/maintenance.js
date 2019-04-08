var start = Date.now();
jeedom.eqLogic.getCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    error: function (error) {
        console.log(error);
    },
    success: function (data) {
        var tbody = '';
        data.forEach(function(element) {
            tbody += '<tr class="cmdMaintenance" cmd_id='+element.id+' logicalId='+element.logicalId+'>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " style="font-size : 1em;">'+element.name+'</span>';
            tbody +=    '</td>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="count"></span>';
            tbody +=    '</td>';
			tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="countcleanable"></span>';
            tbody +=    '</td>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="olddate"></span>';
            tbody +=    '</td>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="lissage">'+miseEnForme(element.configuration.historizeMode)+'</span>';
            tbody +=    '</td>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="optimize"></span>';
            tbody +=    '</td>';
            tbody += '</tr>';
        });
        $('#table_maintenance tbody').empty().append(tbody);
    }
});


function miseEnForme(texte){
    switch (texte){
        case 'none':
            return 'Aucun';
        break;
        case 'avg':
            return 'Moyenne';
        break;
        case 'min':
            return 'Minimum';
        break;
        case 'max':
            return 'Maximum';
        break;
        default:
            return '';
        break;
    }
    return texte;
}

function optimize(cmd_id, type){
	$('.btTeleinfoMaintenance[cmd_id='+cmd_id+']').attr('disabled','disabled');
	$('.btTeleinfoMaintenance[cmd_id='+cmd_id+']').removeClass("btn-info").addClass("btn-warning");
	$('.btTeleinfoMaintenance[cmd_id='+cmd_id+']').html('<i class="fas fa-spinner"></i>  En cours...');
	start = Date.now();
	$.ajax({
            type: 'POST',
            url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
            data: {
                action:'optimizeArchive',
                id: cmd_id,
                type: type,
                },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
				console.log(data);

            }
	});
	refresh = setTimeout("refreshCount("+cmd_id+")",2000);
}


$('.eqLogicAction[data-action=regenerateMonthlyStat]').on('click', function() {
    $.ajax({
            type: 'POST',
            url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
            data: {
                action:'regenerateMonthlyStat',
                },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                console.log(data);
                //$('.cmdMaintenance[cmd_id='+cmdId+']').find(".cmdMaintenanceAttr[type=countcleanable]").text(data.result.count[0].count);
            }
	});
});

function refreshCount(cmdId){
	$.ajax({
            type: 'POST',
            url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
            data: {
                action:'countArchiveNotZero',
                id: cmdId,
                },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                $('.cmdMaintenance[cmd_id='+cmdId+']').find(".cmdMaintenanceAttr[type=countcleanable]").text(data.result.count[0].count);

				if (data.result.count[0].count == 0){
					window.clearTimeout(refresh);
					$('.btTeleinfoMaintenance[cmd_id='+cmdId+']').attr('disabled','disabled');
					$('.btTeleinfoMaintenance[cmd_id='+cmdId+']').removeClass("btn-warning").addClass("btn-success");
					$('.btTeleinfoMaintenance[cmd_id='+cmdId+']').html('<i class="fas fa-check"></i>  Ok');
				}else{
					if((Date.now() - start) < 60000){
						refresh = setTimeout("refreshCount("+cmdId+")",2000);
					}
				}
            }
	});
}




$('#table_maintenance .cmdMaintenance').each(function( index ) {
    var tempcount = $(this);
    $.ajax({
            type: 'POST',
            url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
            data: {
                action:'countArchive',
                id: $(this).attr( "cmd_id" ),
                },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                tempcount.find(".cmdMaintenanceAttr[type=count]").text(data.result.count[0].count);
                if (data.result.oldest.length  > 0){
                    tempcount.find(".cmdMaintenanceAttr[type=olddate]").text(data.result.oldest[0].oldest);
                }
            }
    });
	$.ajax({
            type: 'POST',
            url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
            data: {
                action:'countArchiveNotZero',
                id: $(this).attr( "cmd_id" ),
                },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                tempcount.find(".cmdMaintenanceAttr[type=countcleanable]").text(data.result.count[0].count);
				if(data.result.count[0].count > 1000){
                    if(tempcount.attr( "logicalId" ).includes("PAPP") || tempcount.attr( "logicalId" ).includes("SINSTS")){
                        tempcount.find(".cmdMaintenanceAttr[type=optimize]").html('<a class="btn btn-sm btn-info tooltips btTeleinfoMaintenance" cmd_id="' + tempcount.attr( "cmd_id" ) + '" onclick="optimize(' + tempcount.attr( "cmd_id" ) + ',\'AVG\')" ><i class="fas fa-terminal"></i>{{ Optimiser (AVG)}}</a>');
                    }
                    else {
                        tempcount.find(".cmdMaintenanceAttr[type=optimize]").html('<a class="btn btn-sm btn-info tooltips btTeleinfoMaintenance" cmd_id="' + tempcount.attr( "cmd_id" ) + '" onclick="optimize(' + tempcount.attr( "cmd_id" ) + ',\'MAX\')" ><i class="fas fa-terminal"></i>{{ Optimiser}}</a>');
                    }
                }
            }
    });
});
