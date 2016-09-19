<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
?>



<div id='div_CoutAlert' style="display: none;"></div>


<div>
</br>
	<div class="panel panel-primary">
	  <div class="panel-heading">Abonnement :</div>
	  <div class="panel-body">
		<div class="input-group col-md-4">
		  <span class="input-group-addon">Mensuel</span>
		  <input id="abo_mensuel" type="text" class="form-control" value="">
		  <span class="input-group-addon">€</span>
		</div>
	  </div>
	</div>

	<div class="panel panel-primary">
	  <div class="panel-heading">Prix des kWh :</div>
	  </br>
	  <div class="panel-body">
		<div class="input-group col-md-4">
		  <span class="input-group-addon">Heures Pleines</span>
		  <input type="text" class="form-control">
		  <span class="input-group-addon">€</span>
		</div>
	  </div>
	  <div class="panel-body">
		<div class="input-group col-md-4">
		  <span class="input-group-addon">Heures Creuses</span>
		  <input type="text" class="form-control">
		  <span class="input-group-addon">€</span>
		</div>
	  </div>
	</div>

<div>
<a class="btn btn-danger disabled"><i class='fa fa-times'></i> {{Annuler}}</a>&nbsp
<a class="btn btn-success" id="bt_teleinfoSaveCout"><i class='fa fa-floppy-o'></i> {{Enregistrer}}</a>
</div>

 
</div>





<?php include_file('desktop', 'cout', 'js', 'teleinfo');?>