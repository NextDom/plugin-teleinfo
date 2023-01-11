<?php
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
                      <div class="form-group">
                          <label class="col-sm-2 control-label">{{Index}}</label>
                          <label class="col-sm-2 control-label">{{Libellé tarif}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Champ téléinfo}}</label>
                          <label class="col-sm-1 control-label">{{ }}</label>
                          <label class="col-sm-2 control-label">{{Prix kWh}}</label>
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

                      </div>
                      
                      <?php
							//création du tableau des paramètres des index
                            $index=array('index01','index02','index03','index04','index05','index06','index07','index08','index09','index10');
							foreach($index as $numindex){
								$tableau.='<div class="form-group">';
									$tableau.='<label class="col-sm-2 control-label">{{Index '.substr($numindex,-2).'}} :</label>';
									$tableau.='<div class="col-sm-2">';
									    $tableau.='<input type="text" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="'.$numindex.'_nom" placeholder="{{...}}"/>';
									$tableau.='</div>';
									$tableau.='<label class="col-sm-1 control-label">{{ }}</label>';
									$tableau.='<div class="col-sm-2">';
										$tableau.='<input type="text" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="'.$numindex.'" placeholder="{{...}}"/>';
									$tableau.='</div>';
								    $tableau.='<label class="col-sm-1 control-label">{{ }}</label>';
									$tableau.='<div class="col-sm-2">';
                                        $tableau.='<input type="number" class="eqLogicAttr configKey" data-l1key="configuration" data-l2key="Cout'.$numindex.'" placeholder="{{0}}"/>';
                                    $tableau.='</div>';
                                $tableau.='</div>';
                                    }
						?>
						<?php echo $tableau ?>

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
							  <div> 
								  <label>Pour la période du (date au format AAA-MM-JJ) : </label>
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

<?php include_file('desktop', 'teleinfo', 'js', 'teleinfo'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
<?php include_file('desktop', 'teleinfo', 'css', 'teleinfo'); ?>