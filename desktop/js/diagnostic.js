var etapes = [{"name": "Configuration du plugin", "result": "", "advise": "Vérifier la configuration du plugin"},
    {"name": "Modem connecté", "result": "", "advise": "Vérifier la connexion du modem à Jeedom"},
    {"name": "Accès au Modem", "result": "", "advise": "Vérifier les droits sur le port du Modem"},
    {"name": "Lecture des données", "result": "", "advise": "Vérifier que les cables soient bien connectés; que la téléinformation est bien activée par EDF"},
    {"name": "Intégritée des données", "result": "", "advise": ""},
    {"name": "Package de logs", "result": "", "advise": ""}
];

function populate_table(){

    var tbody = '';
    for(var i in etapes){
            tbody += '<tr>';

            tbody += '<td>';
            tbody += '<span  class="" style="font-size : 1em;">'+etapes[i].name+'</span>';
            tbody += '</td>';

            tbody += '<td>';
            tbody += '<span  style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid='+i+' ><i class="fa fa-fast-forward"></i></a></span>';
            tbody += '</td>';

            tbody += '<td class="result">';
            /*tbody += '<span class="" style="">'+etapes[i].result+'</span>';*/
            tbody += '<span class="result'+i+'" style=""></span>';
            tbody += '</td>';

            tbody += '<td>';
            tbody += '<span class="advise'+i+'" style="display:none">'+etapes[i].advise+'</span>';
            tbody += '</td>';

            tbody += '</tr>';
    }
    $('#table_health tbody').empty().append(tbody);
}

$( document ).on( "click", ".btn", function() {
  var temp = $(this).attr("btnid")
  $(".result"+temp+"").append('<i class="fa fa-spinner fa-spin"></i>');
  $.ajax({
  async:true,
  global : false,
  url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
  data: {
      action:'diagnostic_step' + temp
      },
  dataType: 'json',
  error: function (request, status, error) {
      console.log(request);
      handleAjaxError(request, status, error,$('#div_DiagnosticAlert'));
  },
  success: function (data) {
      console.log(data);
      if (data.result.result === '0'){
          $('.advise'+temp).show();
          $(".result"+temp+"").empty().append('<i class="fa fa-times" style="color:#FF0000"></i> ');
      }
      else if (data.result.result === '1'){
          $(".result"+temp+"").empty().append('<i class="fa fa-check" style="color:#00FF00"></i> ');
      }else {
          $(".result"+temp+"").empty();
      }
      $(".result"+temp+"").append(data.result.message);
  }
  });
});

function check_state(name,  datetime){
    return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
}

populate_table();
