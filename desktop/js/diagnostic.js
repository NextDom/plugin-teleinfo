var etapes = [{"name": "Configuration du plugin", "command": "", "result": "", "advise": "Vérifier la configuration du plugin"},
    {"name": "Modem connecté", "command": "", "result": "", "advise": "Vérifier la connexion du modem à Jeedom"},
    {"name": "Accès au Modem", "command": "", "result": "", "advise": "Vérifier les droits sur le port du Modem"},
    {"name": "Lecture des données", "command": "", "result": "", "advise": "Vérifier que les cables soient bien connectés; que la téléinformation est bien activée par EDF"},
    {"name": "Intégritée des données", "command": "", "result": "", "advise": ""}
];

function populate_table(){

    var tbody = '';
    for(var i in etapes){
            tbody += '<tr>';

            tbody += '<td>';
            tbody += '<span  class="" style="font-size : 1em;">'+etapes[i].name+'</span>';
            tbody += '</td>';

            tbody += '<td>';
            tbody += '<span  style="font-size : 1em;"><a class="btn btn-sm btn-success" onclick="run_check('+etapes[i].command+');"><i class="fa fa-fast-forward"></i></a></span>';
            tbody += '</td>';

            tbody += '<td class="result">';
            /*tbody += '<span class="" style="">'+etapes[i].result+'</span>';*/
            tbody += '<span class="" style=""></span>';
            tbody += '</td>';

            tbody += '<td>';
            tbody += '<span class="" style="">'+etapes[i].advise+'</span>';
            tbody += '</td>';

            tbody += '</tr>';
    }
    $('#table_health tbody').empty().append(tbody);
}

function run_check(command){
    $(this).closest('.result').find('.span').html('test');
    console.log($(this).closest('.result').find('.span').value);

}

function check_state(name,  datetime){
    return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
}

populate_table();
