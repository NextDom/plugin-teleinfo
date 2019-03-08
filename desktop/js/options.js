var commandeId;

$("body").delegate("#bt_selectoutsideTemp", 'click', function() {
     jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function(result) {
         $('#outsideTemp').value(result.human);
		 commande = result.cmd.id;
     });
});


$('#btOptionsSave').off().on('click', function () {
 
  
  	console.log(jeedom.config.save({
      plugin: "teleinfo",
      configuration: {"outside_temp" : commande},
		error: function (error) {
		$('#div_OptionsAlert').showAlert({message: error.message, level: 'danger'});
		},
		success: function (data) {
          console.log(data);
		}
    }));
});