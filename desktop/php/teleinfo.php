<?php
//header("Location:");
//header('Refresh:');
$url = $_SERVER['HTTP_HOST']; 
$url .= $_SERVER['REQUEST_URI']; 
header('Refresh','URL='.$url);


if (!isConnect('admin')) {
    throw new Exception('Error 401 Unauthorized');
}

$date = array(
	'start' => date('Y-m-d', strtotime('2020-01-01')),
	'end' => date('Y-m-d'),
);

$plugin = plugin::byId('teleinfo');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

try {
	$result = teleinfo::deamon_info();
	if (isset($result['state'])) {
		$controlerState = $result['state'];
	}
} catch (Exception $e) {
	$controlerState = null;
}
switch ($controlerState) {
	case 'ok':
		// event::add('jeedom::alert', array(
		// 	'level' => 'warning',
		// 	'page' => 'teleinfo',
		// 	'message' => __('Le réseau Z-Wave est en cours de démarrage sur le serveur', __FILE__),
		// ));
		break;
	case 'nok':
        if (config::byKey('deamonAutoMode', 'teleinfo') != 1) {
            break;
        }
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'teleinfo',
			'message' => __('Le deamon téléinfo ne semble pas démaré, vérifiez la configuration du port.', __FILE__),
		));
		break;
}
//$deamonRunning = false;
//$deamonRunning = teleinfo::deamonRunning();


?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor logoSecondary" id="bt_info_daemon">
				<i class="fas fa-heartbeat"></i>
                <br/>
                <span>{{Info Modem}}</span>
			</div>

			<div class="cursor logoSecondary eqLogicAction" data-action="gotoPluginConf">
			    <i class="fas fa-wrench"></i>
                <br/>
                <span>{{Configuration}}</span>
			</div>

			<div class="cursor logoSecondary" id="bt_options">
				<i class="fas fa-list-alt"></i>
                <br/>
                <span>{{Options}}</span>
            </div>
		</div>

        <legend>{{Mes Modules de Téléinformation}}</legend>
            <div class="eqLogicThumbnailContainer">

				<div class="eqLogicDisplayCard cursor eqLogicAction logoPrimaryTeleinfo" data-action="add">
					<i class="fas fa-plus-circle logoPlusEqlogic"></i>
                    </br>
					<span class="name">Ajouter</span>
				</div>
                <?php
			    foreach ($eqLogics as $eqLogic) {
				    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				    echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-logical-id="' . $eqLogic->getLogicalId() . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';
    				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                    echo '</br>';
    				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
    				echo '</div>';
			    }
			    ?>
            </div>
    </div>



    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
                <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
                <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction cursor" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>

		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
      </br>
      <div class="row">
          <div class="col-lg-6">
              <form class="form-horizontal">
                  <fieldset>
                      <div class="form-group">
                          <label class="col-lg-4 control-label">{{Nom de l'équipement}} :</label>
                          <div class="col-lg-4">
                              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label" >{{Objet parent :}}</label>
                          <div class="col-lg-4">
                              <select class="eqLogicAttr form-control" data-l1key="object_id">
                                  <option value="">{{Aucun}}</option>
                                  <?php
								  foreach (jeeObject::all() as $object) {
                                      echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                  }
                                  ?>
                              </select>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label">{{Identifiant Compteur}} :</label>
                          <div class="col-lg-4">
                              <input type="text" class="eqLogicAttr form-control tooltips" title="{{Identifiant du compteur aussi connu sous le nom ADCO.}}" data-l1key="logicalId" placeholder="{{ADCO du compteur}}"/>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group" style="display:none">
                          <label class="col-lg-4 control-label">{{Catégorie}} :</label>
                          <div class="col-lg-8">
                              <!--<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="energy" checked/>-->
                              <?php
                              /*foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                              echo '<label class="checkbox-inline">';
                              echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                              echo '</label>';
                          }*/
                              ?>
                          </div>
                      </div>
                      <div class="form-group etatObjet">
                          <label class="col-lg-4 control-label">{{Etat de l'objet}} :</label>
                          <div class="col-lg-8">
                              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                          </div>
                      </div>
                      <div class="form-group ProdCons">
                          <label class="col-lg-4 control-label pull-left">{{Compteur en mode conso ET prod}} <sup><i class="fas fa-question-circle tooltips" title="{{A cocher si le compteur sert aussi en production (Linky)}}"></i></sup></label>
                          <div class="col-lg-7 tooltips">
                              <input type="checkbox" id="activation_production" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="ActivationProduction" placeholder="{{Activer}}"/>
                          </div>
                      </div>
                      <div class="form-group HCHP">
                          <label class="col-lg-4 control-label pull-left">{{Abo HC / HP (ancienne méthode)}} <sup><i class="fas fa-question-circle tooltips" title="{{Si vous voulez toujours l'ancienne méthode et si votre abonnement est HC / HP}}"></i></sup></label>
                          <div class="col-lg-7 tooltips">
                              <input type="checkbox" id="HC_HP" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="HCHP" placeholder="{{Abonnement HC/HP}}"/>
                          </div>
                      </div>
                      <div class="form-group NewIndex">
                          <label class="col-lg-4 control-label pull-left">{{Utilisation des nouveaux index}} <sup><i class="fas fa-question-circle tooltips" title="{{Si vous voulez utiliser la nouvelle méthode avec les Index ci-dessous}}"></i></sup></label>
                          <div class="col-lg-7 tooltips">
                              <input type="checkbox" id="NewMethode" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="newIndex" placeholder="{{Utilisation des index}}"/>
                          </div>
                      </div>
                      <div class="form-group Colors">
                          <a class="btn btn-warning tooltips col-sm-2 pull-right"  id="btTeleinfoRazCouleurs"><i class="fas fa-medkit"></i>{{ RAZ couleurs}}</a>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">{{Index}}</label>
                          <label class="col-sm-2 control-label">{{Libellé tarif}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Champ téléinfo}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-1 control-label">{{Prix kWh}}</label>
						  <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Couleurs ligne}}</label>
                      </div>

                      <div class="form-group">
                          <label class="col-sm-2 control-label">{{Index Prod}} :</label>
                          <label class="col-sm-2 control-label">{{Injection Totale}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{EAIT}}</label>
						  <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-2">
                              <input type="number" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="CoutindexProd" placeholder="{{0}}"/>
                          </div>
						  <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-1">
                              <input type="color" class="eqLogicAttr configKey" id="favcolor14"  data-l1key="configuration" data-l2key="color14" name="favcolor14">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">{{Index 00}} :</label>
                          <label class="col-sm-2 control-label">{{Conso Totale}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{BASE ou EAST}}</label>
						  <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-2">
                              <input type="number" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="Coutindex00" placeholder="{{Si abo de base sinon 0}}"/>
                          </div>
						  <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-1">
                              <input type="color" class="eqLogicAttr configKey" id="favcolor0"  data-l1key="configuration" data-l2key="color0" name="favcolor0">
                          </div>

                      </div>
                      <?php
                            //création du tableau des paramètres des index
                            $index=array('index01','index02','index03','index04','index05','index06','index07','index08','index09','index10');
                            $indexId=array("EASF01","EASF02","EASF03","EASF04","EASF05","EASF06","EASF07","EASF08","EASF09","EASF10",
                                            "HCHC", "HCHP", "EJPHN", "EJPHPM", "BBRHCJB", "BBRHPJB", "BBRHCJW", "BBRHPJW", "BBRHCJR","BBRHPJR");
                            $color = 0;
							foreach($index as $numindex){
                                $color += 1;
								$tableau.='<div class="form-group">';
									$tableau.='<label class="col-sm-2 control-label">{{Index '.substr($numindex,-2).'}} :</label>';
									$tableau.='<div class="col-sm-2">';
									    $tableau.='<input type="text" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="'.$numindex.'_nom" placeholder="{{...}}"/>';
									$tableau.='</div>';
									$tableau.='<label class="col-sm-1 control-label">{{ }}</label>';
									$tableau.='<div class="col-sm-2">';
										//$tableau.='<input type="text" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="'.$numindex.'" placeholder="{{...}}" />';
                                        $tableau.='<select class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="'.$numindex.'">';
                                        $tableau.='<option selected="selected"></option>';
                                        foreach($indexId as $value){
                                            $tableau.='<option value='.$value.'>';
                                            $tableau.= $value.' </option>';
                                        }
                                        $tableau.='</select>';
									$tableau.='</div>';
								    $tableau.='<label class="col-sm-1 control-label">{{ }}</label>';
									$tableau.='<div class="col-sm-2">';
                                        $tableau.='<input type="number" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="Cout'.$numindex.'" placeholder="{{0}}"/>';
                                    $tableau.='</div>';
                                    $tableau.='<label class="col-sm-1 control-label">{{ }}</label>';
                                    $tableau.='<div class="col-sm-1">';
                                        $tableau.='<input type="color" class="eqLogicAttr configKey" id="favcolor'.$color.'"  data-l1key="configuration" data-l2key="color'.$color.'" name="favcolor'.$color.'">';
                                    $tableau.='</div>';
                                $tableau.='</div>';
                                    }
                        ?>
						<?php echo $tableau ?>

                        <div class="form-group">
                          <label class="col-sm-2 control-label">{{ }}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-4 control-label">{{Pour les autres courbes :}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Stat HC}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-1">
                              <input type="color" class="eqLogicAttr configKey" id="favcolor11"  data-l1key="configuration" data-l2key="color11" name="favcolor11">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-sm-8 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Stat HP}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-1">
                              <input type="color" class="eqLogicAttr configKey" id="favcolor12"  data-l1key="configuration" data-l2key="color12" name="favcolor12">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-sm-8 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Stat Today}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <div class="col-sm-1">
                              <input type="color" class="eqLogicAttr configKey" id="favcolor13"  data-l1key="configuration" data-l2key="color13" name="favcolor13">
                          </div>
                        </div>
                  </fieldset>
              </form>
          </div>
          <div class="col-lg-6">
              <form class="form-horizontal">
                  <fieldset>
                      <!--<legend>{{Paramètres}}</legend>-->
                      <div class="form-group infoAbonnement">
                          <label class="col-lg-4 control-label pull-left">{{Votre abonnement }}</label>
                          <div class="col-lg-7">
                              <span class="eqLogicAttr" data-l1key="configuration" data-l2key="abonnement" id="typeAbonnement">Aucun</span>
                          </div>
                      </div>
                      <div class="form-group creationCommandes">
                          <label class="col-lg-4 control-label pull-left">{{Création des commandes}} <sup><i class="fas fa-question-circle tooltips" title="{{Créer automatiquement les commandes envoyées par le compteur}}"></i></sup></label>
                          <div class="col-lg-7 tooltips">
                              <input type="checkbox" id="AutoCreateFromCompteur" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="AutoCreateFromCompteur"/>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label pull-left"></label>
                          <div class="col-lg-7">
                              <a class="btn btn-info tooltips"  id="btTeleinfoHealth"><i class="fas fa-medkit"></i>{{ Santé}}</a>
                              <a class="btn btn-warning tooltips"  id="btTeleinfoMaintenance"><i class="fas fa-hospital"></i>{{ Maintenance}}</a>
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-lg-12">
                              <div class="alert alert-info globalRemark">{{Attention, il est nécessaire d'activer l'historisation des index pour utiliser les statistiques}}</div>
                          </div>
                      </div>
                      <div class="form-group">
                          </br>
						  </br>
						  </br>
                          </br>
						  </br>
						  </br>
							  <div> 
								  <label>Pour la période du (date au format AAAA-MM-JJ) : </label>
								  <input id="in_startDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['start']?>"/>
								  <label> au : </label>
								  <input id="in_endDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 87px;" value="<?php echo $date['end']?>"/>
							  </div>
<!--						  <label class="col-sm-5 control-label pull-left">Copier anciennes données  conso totale vers Index00</label>
                          <div class="col-sm-6">
                              <a class="btn btn-info tooltips"  id="btIndex00"><i class="fas fa-medkit"></i>{{ Index00}}</a>
                          </div>
-->						  </br>
                          <label class="col-sm-5 control-label pull-left">Copier anciennes données vers Index </label>
                          <div class="col-sm-6">
                              <a class="btn btn-info tooltips"  id="btIndex"><i class="fas fa-medkit"></i>{{ Copier}}</a>
                          </div>
                      </div>
                  </fieldset>
              </form>
          </div>
      </div>
  </div>
  <div role="tabpanel" class="tab-pane" id="commandtab">
  <div class="input-group pull-right inputAddCmd" style="display:inline-flex">
			<span class="input-group-btn">
                <a class="btn btn-success btn-sm cmdAction roundedLeft" id="addDataToTable"><i class="fas fa-plus-circle"></i> {{Ajouter une donnée}}</a> &nbsp;
                <a class="btn btn-info btn-sm cmdAction roundedRight" id="addStatToTable"><i class="fas fa-plus-circle"></i> {{Ajouter une statistique}}</a>
            </span>
  </div>

  <table id="table_cmd" class="table table-bordered table-condensed">
      <thead>
          <tr>
              <th>#</th>
              <th style="width: 15%">{{Nom}}</th>
              <th style="width: 15%;">{{Sous-Type}}</th>
              <th style="width: 30%;">{{Donnée}}</th>
              <th style="width: 30%;">{{Paramètres}}</th>
              <th></th>
          </tr>
      </thead>
      <tbody>
      </tbody>
  </table>
  <form class="form-horizontal">
      <fieldset>
          <div class="form-actions">
          </div>
      </fieldset>
  </form>
</div>
		</div>
	</div>
</div>

<script>

function prePrintEqLogic() {
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index01]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index02]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index03]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index04]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index05]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index06]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index07]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index08]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index09]').value(' ');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index10]').value(' ');
}

//création du tableau des paramètres des couleurs par défaut
$colordefaut = ['#D62828','#001219','#005F73','#0A9396','#94D2BD',
                                '#E9D8A6','#ee9b00','#ca6702','#bb3e03','#ae2012',
                                '#9b2226','#ed9448','#7cb5ec','#d62828','#00FF00'];

$('#btTeleinfoRazCouleurs').on('click', function () {
    $('#favcolor0').value($colordefaut[0]);
    $('#favcolor1').value($colordefaut[1]);
    $('#favcolor2').value($colordefaut[2]);
    $('#favcolor3').value($colordefaut[3]);
    $('#favcolor4').value($colordefaut[4]);
    $('#favcolor5').value($colordefaut[5]);
    $('#favcolor6').value($colordefaut[6]);
    $('#favcolor7').value($colordefaut[7]);
    $('#favcolor8').value($colordefaut[8]);
    $('#favcolor9').value($colordefaut[9]);
    $('#favcolor10').value($colordefaut[10]);
    $('#favcolor11').value($colordefaut[11]);
    $('#favcolor12').value($colordefaut[12]);
    $('#favcolor13').value($colordefaut[13]);
    $('#favcolor14').value($colordefaut[14]);
});

//$('#favcolor14').value('#00FF00');
//    document.getElementById("favcolor14").value = "#00FF00";
//    $('.eqLogicAttr[data-l1key=configuration][data-l2key=color14]').value("#00FF00");
//eqLogicAttr configKey" id="favcolor14"  data-l1key="configuration" data-l2key="color14


</script>


<?php include_file('desktop', 'teleinfo', 'js', 'teleinfo'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
<?php include_file('desktop', 'teleinfo', 'css', 'teleinfo'); ?>


