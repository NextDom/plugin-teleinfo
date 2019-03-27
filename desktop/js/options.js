
$("body").delegate("#bt_selectoutsideTemp", 'click', function() {
     jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function(result) {
         $('#outsideTemp').value(result.human);
         $('#outsideTemp').attr('cmd',result.cmd.id);
     });
});


$('#btOptionsSave').off().on('click', function () {
    jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"outside_temp" : $('#outsideTemp').attr('cmd')},
		error: function (error) {
		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
		},
		success: function (data) {
            $('#div_OptionsAlert').showAlert({message: 'Sauvegarde effectuée', level: 'success'});
		}
    });

console.log($("#indexConsoHP").val());
    console.log(jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"indexConsoHP" : $("#indexConsoHP").val()},
		error: function (error) {
		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
		},
		success: function (data) {

		}
    }));

    console.log(jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"indexConsoHC" : $("#indexConsoHC").val()},
		error: function (error) {
		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
		},
		success: function (data) {

		}
    }));

    console.log(jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"indexProduction" : $("#indexProduction").val()},
        error: function (error) {
        $('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {

        }
    }));

});
