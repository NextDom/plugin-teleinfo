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
<div id='div_OptionsAlert' style=""></div>
<div class="input-group pull-right" style="display:inline-flex">
  <span class="input-group-btn">
    <a class="btn btn-success btn-sm roundedRight" id="btOptionsSave"><i class="fa fa-check-circle"></i> {{Enregistrer}}</a>
  </span>
</div>
<div class="form-group" class="col-md-12">
	<label class="col-xs-3 control-label">{{Temp. extérieure}}</label>
	<div class="col-xs-4">
		<div class="input-group">
  			<?php
			$outsideTemp = config::byKey('outside_temp', 'teleinfo');
			if($outsideTemp != ''){
				echo '<input class="eqLogicAttr form-control input-sm" value="' . cmd::cmdToHumanReadable('#' . $outsideTemp  . '#') . '" id="outsideTemp"/>';
			}
			else {
				echo '<input class="eqLogicAttr form-control input-sm" id="outsideTemp"/>';
			}
  			?>
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm cursor" id="bt_selectoutsideTemp" title="{{Choisir une commande}}"><i class="fa fa-list-alt"></i></a>
			</span>
		</div>
	</div>
	<div class="col-xs-5"></div>
</div>


<?php include_file('desktop', 'options', 'js', 'teleinfo');?>