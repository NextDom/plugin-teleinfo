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

console.log($("#indexConsoTotales").val());
    console.log(jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"indexConsoTotales" : $("#indexConsoTotales").val()},
		error: function (error) {
		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
		},
		success: function (data) {

		}
    }));

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

    console.log($("#coutBase").val());
        console.log(jeedom.config.save({
          plugin: "teleinfo",
          configuration: {"coutBase" : $("#coutBase").val()},
    		error: function (error) {
    		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
    		},
    		success: function (data) {

    		}
        }));
    console.log($("#coutHP").val());
        console.log(jeedom.config.save({
          plugin: "teleinfo",
          configuration: {"coutHP" : $("#coutHP").val()},
    		error: function (error) {
    		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
    		},
    		success: function (data) {

    		}
        }));
    console.log($("#coutHC").val());
        console.log(jeedom.config.save({
          plugin: "teleinfo",
          configuration: {"coutHC" : $("#coutHC").val()},
            error: function (error) {
            $('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
            },
            success: function (data) {

            }
        }));
    console.log($("#coutProd").val());
        console.log(jeedom.config.save({
          plugin: "teleinfo",
          configuration: {"coutProd" : $("#coutProd").val()},
            error: function (error) {
            $('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
            },
            success: function (data) {

            }
        }));

});
