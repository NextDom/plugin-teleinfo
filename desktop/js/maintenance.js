
jeedom.eqLogic.getCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    error: function (error) {
        console.log(error);
    },
    success: function (data) {
        var tbody = '';
        data.forEach(function(element) {
            tbody += '<tr class="cmdMaintenance" cmd_id='+element.id+'>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " style="font-size : 1em;">'+element.name+'</span>';
            tbody +=    '</td>';
            tbody +=    '<td>';
            tbody +=    '<span  class="cmdMaintenanceAttr " type="count"></span>';
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
                console.log(data);
                tempcount.find(".cmdMaintenanceAttr[type=count]").text(data.result.count[0].count);
                if (data.result.oldest.length  > 0){
                    tempcount.find(".cmdMaintenanceAttr[type=olddate]").text(data.result.oldest[0].datetime);
                }
                if(data.result.count[0].count > 20){
                    tempcount.find(".cmdMaintenanceAttr[type=optimize]").html('<a class="btn btn-sm btn-info tooltips"  id="btTeleinfoMaintenance"><i class="fas fa-terminal"></i>{{}}</a>');
                }
            }
    });
});
