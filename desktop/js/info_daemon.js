var tmp_debug;
var app_console = {
	updater: false,
	init: function(){
		clearInterval(app_console.updater);
		$('#pre_consolelog').html();
	},
	show: function(){
        app_console.updater = setInterval(app_console.refresh,1000);
    },
	refresh: function(){
		$.ajax({
		async:true,
		global : false,
        url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
        data: {
			action:'getInfoDaemon'
			},
        dataType: 'json',
        error: function (request, status, error) {
			console.log("pointeur_debug");
			console.log(request);
            handleAjaxError(request, status, error,$('#div_InfoDaemonAlert'));
        },
        success: function (data) {
			console.log(data);
			tmp_debug = data.result;
			var log = '';
 			if($.isArray(data.result.result)){
 				for (var i in data.result.result.reverse()) {
 						log += $.trim(data.result.result[i])+"\n";
 				}
 			}
 			$('#pre_consolelog').html(log);
			var h = parseInt($('#log')[0].scrollHeight);
			$('#log').scrollTop(h);
        }
		});
	
	}
}