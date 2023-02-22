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

<div>
	<div class="row no-gutters" id="div_teleinfo">
		<fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 0px 5px">
			<div style="padding-top:10px;font-size: 1.5em;"> 
				<span id="spanTitreResume">Ma consommation
				</span>
				<select id="eqlogic_select" style="font-size: 15px;border-radius: 3px;border:1px solid #ccc;">
								<?php
								foreach ($eqLogics as $eqLogic) {
									echo '<option value="' . $eqLogic->getId() . '">"' . $eqLogic->getHumanName(true) . '"</option>';
								}
								?>
				</select>

				<a class="btn btn-default btn-sm pull-right tooltips" style="margin-right:10px;" id="bt_teleinfoTout" title="Nouveaux index: Afficher tout: kWh ET €">{{Tout}}</a>
				<a class="btn btn-default btn-sm pull-right tooltips" style="margin-right:10px;" id="bt_teleinfoConso" title="Nouveaux index: Afficher les kWh">{{kWh}}</a>
				<a class="btn btn-default btn-sm pull-right tooltips" style="margin-right:10px;" id="bt_teleinfoCout" title="Afficher les coûts"><i class="fas fa-euro-sign"></i></a>
				<a class="btn btn-default btn-sm pull-right tooltips" id="bt_teleinfoPanelSante" title="Vérifier l'intégrité des données"><i class="fas fa-check-circle"></i></a>
				<a style="margin-right:5px;" class="pull-right btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
				<input id="in_endDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['end']?>"/>
				<input id="in_startDate" class="pull-right form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['start']?>"/>
								  
			</div>
			<label class="control-label" style="font-size: 1em;">
				{{Abonnement : }} 
			</label>
			<span class="teleinfoAttr" data-l1key="abonnement" data-l2key="type"></span>
			<table class="table teleinfotable text-center">
				<thead>
					<tr class="teleinfotr" >
						<th class="teleinfoth" rowspan="2" colspan="2" style="vertical-align: middle">Compteur</th>
						<th class="teleinfoth" colspan="2">Journée</th>
						<th class="teleinfoth" rowspan="2" style="vertical-align: middle">Mois<br>en cours</th>
						<th class="teleinfoth" colspan="2">Mois de A-1</th>
						<th class="teleinfoth" rowspan="2" style="vertical-align: middle">Année<br>en cours</th>
						<th class="teleinfoth" colspan="2">Année A-1</th>
						<th class="teleinfoth" rowspan="2" style="vertical-align: middle">Total<br>général</th>
					</tr>
					<tr class="teleinfotr" >
						<th class="teleinfoth">J</th>
						<th class="teleinfoth">J-1</th>
						<th class="teleinfoth">Partiel</th>
						<th class="teleinfoth">Total</th>
						<th class="teleinfoth">Partiel</th>
						<th class="teleinfoth">Total</th>
					</tr>
					<tr class="teleinfotr" >
						<th rowspan="14" style="vertical-align: middle" class="teleinfoth index">Conso</th>
						<th class="teleinfoth TOTAL">Total</th>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="day" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="day"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yesterday" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yesterday"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="month"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="month"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="monthLastYear"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="year"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="year"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearLastYearPartial"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="yearLastYear"></span>
						</td>
						<td class="teleinfotd TOTAL">
							<span class="teleinfoAttr" data-l1key="conso" data-l2key="all" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="cout" data-l2key="all"></span>
						</td>
					</tr>
					<tr class="teleinfotr" >
						<th class="teleinfoth HCHP">HP</th>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="day" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="day"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yesterday" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yesterday"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="month"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="month"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="monthLastYear"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="year"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="year"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yearLastYearPartial"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="yearLastYear"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHP" data-l2key="all" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHP" data-l2key="all"></span>
						</td>
					</tr>
					<tr class="teleinfotr" >
						<th class="teleinfoth HCHP">HC</th>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="day" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="day"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yesterday" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yesterday"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="month"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="month"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="monthLastYearPartial" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="monthLastYearPartial"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="monthLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="monthLastYear"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="year"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="year"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yearLastYearPartial"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yearLastYearPartial"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="yearLastYear" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="yearLastYear"></span>
						</td>
						<td class="teleinfotd HCHP">
							<span class="teleinfoAttr" data-l1key="consoHC" data-l2key="all" style="font-size: 1em;"></span>
							{{ kWh}}
							<span style="display:none;font-size: 0.9em;" class="teleinfoAttr" data-l1key="coutHC" data-l2key="all"></span>
						</td>
					</tr>
<!--- mise en forme du tableau des index --->
						<?php
							$index=array('Index00','Index01','Index02','Index03','Index04','Index05','Index06','Index07','Index08','Index09','Index10');
							$periode=array('day','yesterday','month','monthLastYearPartial','monthLastYear','year','yearLastYearPartial','yearLastYear','all');
							foreach($index as $numindex){
								$tableau.='<tr class="teleinfotr index">';
									$tableau.='<th class="teleinfoth '.$numindex.'">';
										$tableau.='<span class="teleinfoAttr" data-l1key="titre" data-l2key="'.$numindex.'" style="font-size: 1em;"></span>';
									$tableau.='</th>';
									foreach($periode as $jours){
										$tableau.='<td class="teleinfotd '.$numindex.'">';
											$tableau.='<span class="teleinfoAttr" data-l1key="conso'.$numindex.'" data-l2key="'.$jours.'" style="font-size: 1em;"></span>';
											$tableau.=' kWh';
										$tableau.='</td>';
									}
								$tableau.='</tr>';
							}
						?>
						<?php echo $tableau ?>
					</tr>

<!--- mise en forme du tableau des couts index --->
					<tr class="teleinfotr" >
					<th rowspan="12" style="vertical-align: middle" class="teleinfoth couts">Coûts</th>
						<?php
							foreach($index as $numindex){
								$tableau2.='<tr class="teleinfotr couts">';
									$tableau2.='<th class="teleinfoth '.$numindex.'">';
										$tableau2.='<span class="teleinfoAttr" data-l1key="titre" data-l2key="'.$numindex.'" style="font-size: 1em;"></span>';
									$tableau2.='</th>';
									foreach($periode as $jours){
										$tableau2.='<td class="teleinfotd '.$numindex.'">';
											$tableau2.='<span class="teleinfoAttr" data-l1key="cout'.$numindex.'" data-l2key="'.$jours.'" style="font-size: 1em;"></span>';
											$tableau2.=' €';
										$tableau2.='</td>';
									}
								$tableau2.='</tr>';
							}
						?>
					</tr>
						<?php echo $tableau2 ?>
<!--- Elaboration du tableau PROD --->
						<?php
							$tableau3.='<tr class="PRODUCTION">';
								$tableau3.='<th rowspan="2" style="vertical-align: middle" class="teleinfoth PRODUCTION">Prod</th>';
								$tableau3.='<th class="teleinfoth PRODCONSO" style="font-size: 1em;">Energie</th>';
								foreach($periode as $jours){
									$tableau3.='<td class="teleinfotd PRODCONSO">';
										$tableau3.='<span class="teleinfoAttr" data-l1key="prod" data-l2key="'.$jours.'" style="font-size: 1em;"></span>';
										$tableau3.=' kWh';
									$tableau3.='</td>';
								}
								$tableau3.='</tr>';
								$tableau3.='<tr class="teleinfotr PRODCOUT">';
									$tableau3.='<th class="teleinfoth PRODCOUT" style="font-size: 1em;">Revenus</span>';
									foreach($periode as $jours){
										$tableau3.='<td class="teleinfotd PRODCOUT">';
											$tableau3.='<span class="teleinfoAttr" data-l1key="coutProd" data-l2key="'.$jours.'" style="font-size: 1em;"></span>';
											$tableau3.=' €';
										$tableau3.='</td>';
									}
								$tableau3.='</tr>';
							
						?>
					</tr>
						<?php echo $tableau3 ?>
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
					<fieldset style="border: 1px solid #e5e5e5; border-radius: 5px 5px 5px 5px">
						<div class="index" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-line"></i> {{Puissance instantanée}}
						</div>
						<div class="index" id='div_graphGlobalPower'>
						</div>
						<div class="couts" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-line"></i> {{Coût instantané}}
						</div>
						<div class="couts" id='div_graphGlobalPowerCout'>
						</div>
						<div class="index" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution consommation journalière}}
						</div>
						<div class="index" id='div_graphGlobalJournalier'>
						</div>
						<div class="couts" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution coûts journaliers}}
						</div>
						<div class="couts" id='div_graphGlobalJournalierCout'>
						</div>
						<div class="index" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution consommation mensuelle}}
						</div>
						<div class="index" id='div_graphGlobalIndex'>
						</div>
						<div class="couts" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution coûts mensuels}}
						</div>
						<div class="couts" id='div_graphGlobalIndexCout'>
						</div>
						<div class="index" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution consommation annuelle}}
						</div>
						<div class="index" id='div_graphGlobalAnnual'>
						</div>
						<div class="couts" style="padding-top:10px;padding-left:24px;padding-bottom:25px;font-size: 1.5em;">
							<i style="font-size: initial;" class="fas fa-chart-bar"></i> {{Evolution coûts annuels}}
						</div>
						<div class="couts" id='div_graphGlobalAnnualCout'>
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