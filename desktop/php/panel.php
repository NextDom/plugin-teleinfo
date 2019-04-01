<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$date = array(
	'start' => date('Y-m-d', strtotime(config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d'))),
	'end' => date('Y-m-d'),
);
sendVarToJS('eqType', 'teleinfo');
sendVarToJs('object_id', init('object_id'));
$eqLogics = eqLogic::byType('teleinfo');

?>

<div class="row" id="div_teleinfo">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2" style="height: 320px;padding-top:10px;">

			<form class="form-horizontal">
				<fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 0px 5px;background-color:#f8f8f8">
					<div style="padding-top:10px;padding-left:24px;color: #333;font-size: 1.5em;"> <span id="spanTitreResume">Ma consommation</span>
                        <select id="eqlogic_select" style="color: #555;font-size: 16px;border-radius: 3px;border:1px solid #ccc;">
                						<?php
                						foreach ($eqLogics as $eqLogic) {
                							echo '<option value="' . $eqLogic->getId() . '">"' . $eqLogic->getHumanName(true) . '"</option>';
                						}
                						?>
                		</select>

                        <a class="btn btn-default btn-sm pull-right tooltips" style="margin-right:10px;" id="bt_teleinfoPanelSante" title="Vérifier l'intégrité des données"><i class="fas fa-check-circle"></i> Vérifier </a>
                        <a style="margin-right:5px;" class="pull-right btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
                        <input id="in_endDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 100px;" value="<?php echo $date['end']?>"/>
                        <input id="in_startDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 100px;" value="<?php echo $date['start']?>"/>


                    </div>

					<div class="form-group col-md-4" style="left:25px;">
                        <div class="">
                            <label class="control-label" style="font-size: 1em;">
                                {{Abonnement : }}</label><span class="teleinfoAttr" data-l1key="abonnement" data-l2key="type"></span>

                        </div>
                        <div class="">
						<label class="control-label" style="font-size: 1em;">{{Journée}}</label>
						</div>
						<div class="">
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="day"></span>
								kWh
							</span>
						</div>
					</div>
					<div class="form-group col-md-4" style="">
                        <div style="min-height: 27px;">
                        </div>
						<div class="">
    						<label class="control-label" style="font-size: 1em;">{{Mois}}</label>
							<div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="month"></span>
								kWh
							</span>
							</div>
						</div>
						<div class="">
							<label class="control-label" style="font-size: 1em;">{{Mois N-1}} <sup><i class="fas fa-question-circle tooltips" title="{{Du 1er Janvier au même jour au même jour qu'aujourd'hui}}" style="font-size : 1em;color:grey;"></i></sup></label>
							<div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthlastyear"></span>
								kWh
							</span>
							</div>
						</div>
					</div>
					<div class="form-group col-md-4" style="">
                        <div style="min-height: 27px;">
                        </div>
						<div class="">
                            <div class="">
    						<label class="control-label" style="font-size: 1em;">{{Année}}</label>
    						</div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="year"></span>
								kWh
							</span>
						</div>
						<div class="">
                            <div class="">
    						<label class="control-label" style="font-size: 1em;">{{Année N-1}} <sup><i class="fas fa-question-circle tooltips" title="{{Du 1er Janvier au même jour au même jour qu'aujourd'hui}}" style="font-size : 1em;color:grey;"></i></sup></label>
    						</div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearlastyear"></span>
								kWh
							</span>
						</div>
					</div>


					<?php
                    $date = date("md");
                    if ($date >= '1222') {
                        echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_hiver.png" height="105" width="600" />';
                    }
                    elseif ($date >= '0923') {
                        echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_automne.png" height="105" width="600" />';
                    }
                    elseif ($date >= '0621') {
                        echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_ete.png" height="105" width="600" />';
                    }
                    elseif ($date >= '0321') {
                        echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_printemps.png" height="105" width="600" />';
                    }
                    else {
                        echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_hiver.png" height="105" width="600" />';
                     }
					?>

				</fieldset>
			</form>
        </div>
		<div class="col-lg-2">
		</div>
        </div>
        <div class="row">
    		<div class="col-lg-8 col-lg-offset-2">
                <form class="form-horizontal">
                     <fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 5px 5px;background-color:#f8f8f8">
                         <div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
                             <i style="font-size: initial;" class="fas fa-chart-line"></i> {{Puissance}}
                         </div>
                         <div id='div_graphGlobalPower'></div>

                         <div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
                             <i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution journalière}}
                         </div>
                         <div id='div_graphGlobalJournalier'></div>

                         <div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
                             <i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution mensuelle}}
                         </div>
                         <div id='div_graphGlobalIndex'></div>
                     </br>
                     </fieldset>
                     <div style="min-height: 10px;"></div>
                 </form>
    		</div>
        </div>
    </div>

<?php include_file('desktop', 'panel', 'js', 'teleinfo'); ?>
