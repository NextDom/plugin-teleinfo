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

<div class="container>
	<div class="row no-gutters" id="div_teleinfo">
		<fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 0px 5px;background-color:#f8f8f8">
			<div style="padding-top:10px;color: #333;font-size: 1.5em;"> 
				<span id="spanTitreResume">Ma consommation
				</span>
				<select id="eqlogic_select" style="color: #555;font-size: 15px;border-radius: 3px;border:1px solid #ccc;">
								<?php
								foreach ($eqLogics as $eqLogic) {
									echo '<option value="' . $eqLogic->getId() . '">"' . $eqLogic->getHumanName(true) . '"</option>';
								}
								?>
				</select>

				<a class="btn btn-default btn-sm pull-right tooltips" style="margin-right:10px;" id="bt_teleinfoCout" title="Afficher les coûts. Attention les coûts unitaires sont considérés constants"><i class="fas fa-euro-sign"></i></a>
				<a class="btn btn-default btn-sm pull-right tooltips" id="bt_teleinfoPanelSante" title="Vérifier l'intégrité des données"><i class="fas fa-check-circle"></i></a>
				<a style="margin-right:5px;" class="pull-right btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
				<input id="in_endDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['end']?>"/>
				<input id="in_startDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['start']?>"/>
								  
			</div>
			<label class="control-label" style="font-size: 1em;">
				{{Abonnement : }} 
			</label>
			<span class="teleinfoAttr" data-l1key="abonnement" data-l2key="type"></span>
			<table class="table text-center">
				<thead>
					<tr>
						<th rowspan="2" colspan="2" style="vertical-align: middle">Compteur</th>
						<th colspan="2">Journée</th>
						<th rowspan="2" style="vertical-align: middle">Mois</th>
						<th colspan="2">Mois de A-1</th>
						<th rowspan="2" style="vertical-align: middle">Année</th>
						<th colspan="2">Année A-1</th>
					</tr>
					<tr>
						<th>J</th>
						<th>J-1</th>
						<th>Partiel</th>
						<th>Total</th>
						<th>Partiel</th>
						<th>Total</th>
					</tr>
					<tr>
						<th rowspan="3" style="vertical-align: middle">Conso</th>
						<th>Total</th>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="day" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="day"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yesterday" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yesterday"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="month"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="month"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="monthLastYearPartial"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="monthLastYear"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="year"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="year"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearLastYearPartial"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yearLastYearPartial"></span>
						</td>
						<td>
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yearLastYear"></span>
						</td>
					</tr>
					<tr>
						<th class="HCHP">HP</th>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="day" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="day"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yesterday" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yesterday"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="month"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="month"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="monthLastYear"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="year"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="year"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yearLastYearPartial"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yearLastYear"></span>
						</td>
					</tr>
					<tr>
						<th class="HCHP">HC</th>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="day" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="day"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yesterday" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yesterday"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="month"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="month"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="monthLastYear"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="year"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="year"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yearLastYearPartial"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yearLastYear"></span>
						</td>
					</tr>
					<tr class="PROD">
						<th colspan="2">Prod</th>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="day" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="day"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="yesterday" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="yesterday"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="month"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="month"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="monthLastYear"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="year"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="year"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="yearLastYearPartial"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="PROD">
							<span class="teleinfoAttr" data-l1key="prod" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							kWh
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutProd" data-l2key="yearLastYear"></span>
						</td>
					</tr>
				</thead>
			</table>
		
		</fieldset>


<!--
						<?php
						$date = date("md");
						if ($date >= '1222') {
							echo '<img class="pull-right" src="plugins/teleinfo/desktop/img/portraitconso_saisons_energie_hiver.png" height="100" />';
						}
						elseif ($date >= '0923') {
							echo '<img class="pull-right" src="plugins/teleinfo/desktop/img/portraitconso_saisons_energie_automne.png" height="100"/>';
						}
						elseif ($date >= '0621') {
							echo '<img class="pull-right" src="plugins/teleinfo/desktop/img/portraitconso_saisons_energie_ete.png" height="100" />';
						}
						elseif ($date >= '0321') {
							echo '<img class="pull-right" src="plugins/teleinfo/desktop/img/portraitconso_saisons_energie_printemps.png" height="100" />';
						}
						else {
							echo '<img class="pull-right" src="plugins/teleinfo/desktop/img/portraitconso_saisons_energie_hiver.png" height="100" />';
						 }
						?>
-->



		<div class="row no-gutters">
			<div class="col-sm-12 col-sm-offset-0">
				<form class="form-horizontal">
					<fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 5px 5px;background-color:#f8f8f8">
						<div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-line"></i> {{Puissance}}
						</div>
						<div id='div_graphGlobalPower'>
						</div>
						<div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution journalière}}
						</div>
						<div id='div_graphGlobalJournalier'>
						</div>
						<div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution mensuelle}}
						</div>
						<div id='div_graphGlobalIndex'>
						</div>
						<div style="padding-top:10px;padding-left:24px;padding-bottom:25px;color: #333;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution annuelle}}
						</div>
						<div id='div_graphGlobalAnnual'>
						</div>
						</br>
					</fieldset>
					<div style="min-height: 10px;">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
                      
<?php include_file('3rdparty', 'moment/moment-with-locales.min', 'js', 'teleinfo'); ?>
<?php include_file('desktop', 'panel', 'js', 'teleinfo'); ?>
<?php include_file('desktop', 'panel', 'css', 'teleinfo'); ?>