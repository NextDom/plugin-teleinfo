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
		<div class="col-md-12">
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
		<div style="height: 200px;">
			<form class="form-horizontal">
				<fieldset class="col-md-12">
					<legend>{{Ma consommation}} </legend>
					<div class="form-group col-md-4">
						<div class="text-center">
							<img src="plugins/teleinfo/ressources/panel/Battery_1_5_low.png" height="100px" width="100px" />
							<span class='label label-success' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="day"></span>
								kWh
							</span>
						</div>
						<div class="text-center">
						<label class="text-center;control-label" style="center;font-size: 1.3em;">{{Journée}}</label>
						</div>
					</div>
					<div class="form-group col-md-4">
						<div class="text-center">
							<img src="plugins/teleinfo/ressources/panel/Battery_3_5_low.png" height="100px" width="100px" />
							<span class='label label-success' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="month"></span>
								kWh
							</span>
						</div>
						<div class="text-center">
						<label class="text-center;control-label" style="center;font-size: 1.3em;">{{Mois}}</label>
						</div>
					</div>
					<div class="form-group col-md-4">
						<div class="text-center">
							<img src="plugins/teleinfo/ressources/panel/Battery_full_low.png" height="100px" width="100px" />
							<span class='label label-success' style="font-size: 1.3em;">
								<span class="teleinfoAttr" data-l1key="conso" data-l2key="year"></span>
								kWh
							</span>
						</div>
						<div class="text-center">
						<label class="text-center;control-label" style="center;font-size: 1.3em;">{{Année}}</label>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<div class="col-lg-6">
			<legend>Puissance</legend>
			<div id='div_graphGlobalPower'></div>
		</div>
		<div class="col-lg-6">
			<legend>Evolution de la consommation</legend>
			<div id='div_graphGlobalIndex'></div>
		</div>		
	</div>
</div>

<?php include_file('desktop', 'panel', 'js', 'teleinfo'); ?>