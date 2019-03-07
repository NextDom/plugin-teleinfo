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

<div class="row row-overflow" id="div_teleinfo">
    <div class="row">
        <div class="col-lg-12">
        <input id="in_startDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['start']?>"/>
        <input id="in_endDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['end']?>"/>
        <a class="btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
        <select id="eqlogic_select">
						<?php
						foreach ($eqLogics as $eqLogic) {
							echo '<option value="' . $eqLogic->getId() . '">"' . $eqLogic->getHumanName(true) . '"</option>';
						}
						?>
					</select>

        <a class="btn btn-default pull-right tooltips" style="margin-right:15px"  id="bt_teleinfoPanelSante" title="{{Vérifier l'intégrité des données}}"><i class="fa fa-check-circle-o"></i>{{ Vérifier }}</a>
        </div>
        <div class="col-lg-8 col-lg-offset-2" style="height: 300px;">
			<form class="form-horizontal">
				<fieldset>
					<legend>{{Ma consommation}} 
					</legend>
					<div class="form-group col-md-4" style="top:-10px;left:25px;">
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
					<div class="form-group col-md-4" style="top:-10px">
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
							<label class="control-label" style="font-size: 1em;">{{Mois N-1}} <sup><i class="fa fa-question-circle tooltips" title="{{Du 1er Janvier au même jour au même jour qu'aujourd'hui}}" style="font-size : 1em;color:grey;"></i></sup></label>
							<div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="month"></span>
								kWh
							</span>
							</div>
						</div>
					</div>
					<div class="form-group col-md-4" style="top:-10px">
						<div class="">
                            <div class="">
    						<label class="control-label" style="font-size: 1em;">{{Année}}</label>
    						</div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthlastyear"></span>
								kWh
							</span>
						</div>
						<div class="">
                            <div class="">
    						<label class="control-label" style="font-size: 1em;">{{Année N-1}} <sup><i class="fa fa-question-circle tooltips" title="{{Du 1er Janvier au même jour au même jour qu'aujourd'hui}}" style="font-size : 1em;color:grey;"></i></sup></label>
    						</div>
							<span class='' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearlastyear"></span>
								kWh
							</span>
						</div>
					</div>
					<?php
					switch (date("m")) {
						case "12":
						case "01":
						case "02":
							echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_hiver.png" height="105" width="600" />';
						break;
						case "03":
						case "04":
						case "05":
							echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_printemps.png" height="105" width="600" />';
						break;
						case "06":
						case "07":
						case "08":
							echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_ete.png" height="105" width="600" />';
						break;
						case "09":
						case "10":
						case "11":
							echo '<img class="pull-right" src="plugins/teleinfo/ressources/panel/portraitconso_saisons_energie_automne.png" height="105" width="600" />';
						break;
					}
					
					?>
				</fieldset>
			</form>
        </div>
		<div class="col-lg-2">
		</div>
		<div class="col-lg-6">
			<legend>Evolution de la consommation journalière</legend>
			<div id='div_graphGlobalJournalier'></div>
		</div>
		<div class="col-lg-6">
			<legend>Evolution de la consommation mensuelle</legend>
			<div id='div_graphGlobalIndex'></div>
		</div>		
		<div class="col-lg-6">
			<legend>Puissance</legend>
			<div id='div_graphGlobalPower'></div>
		</div>
		<div class="col-lg-6">
			<legend>Evolution de la production journalière</legend>
			<div id='div_graphGlobalProdJournalier'></div>
		</div>
    </div>
</div>

<?php include_file('desktop', 'panel', 'js', 'teleinfo'); ?>
