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
<div id='div_OptionsAlert'></div>
<div class="input-group pull-right" style="display:inline-flex">
  <span class="pull-right">
    <a class="btn btn-success pull-right" id="btOptionsSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}</a>
  </span>
</div>


<div class="col-lg-12">
    <form class="form-horizontal">
            <div class="form-group">
                <label class="col-lg-2 control-label">{{Temp. extérieure}} :</label>
                <div class="col-lg-5">
                    <div class="input-group">
              			<?php
            			$outsideTemp = config::byKey('outside_temp', 'teleinfo');
            			if($outsideTemp != ''){
            				echo '<input id="outsideTemp" class="eqLogicAttr form-control input-sm" cmd="'.$outsideTemp.'" value="' . cmd::cmdToHumanReadable('#' . $outsideTemp  . '#') . '" id="outsideTemp"/>';
            			}
            			else {
            				echo '<input id="outsideTemp" class="eqLogicAttr form-control input-sm" id="outsideTemp"/>';
            			}
              			?>
            			<span class="input-group-btn">
            				<a class="btn btn-default btn-sm cursor" id="bt_selectoutsideTemp" title="{{Choisir une commande}}"><i class="fas fa-list-alt"></i></a>
            			</span>
            		</div>
                </div>

            </div>
    </form>
</div>
<div class="col-lg-12">
    <form class="form-horizontal">
            <div class="form-group">
                <label class="col-lg-2 control-label">{{Index consommation HP}} :</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <?php
            			$indexConsoHP = config::byKey('indexConsoHP', 'teleinfo', 'BASE,HCHP,EASF02,BBRHPJB,BBRHPJW,BBRHPJR,EJPHPM');
                        echo '<input id="indexConsoHP" type="text" value="'.$indexConsoHP.'" data-role="tagsinput" />';
              			?>
            		</div>
                </div>
                <div class="col-lg-5">
                </div>
            </div>
    </form>
</div>
<div class="col-lg-12">
    <form class="form-horizontal">
            <div class="form-group">
                <label class="col-lg-2 control-label">{{Index consommation HC}} :</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <?php
            			$indexConsoHC = config::byKey('indexConsoHC', 'teleinfo', 'HCHC,EASF01,BBRHCJB,BBRHCJW,BBRHCJR,EJPHN');
                        echo '<input id="indexConsoHC" type="text" value="'.$indexConsoHC.'" data-role="tagsinput"/>';
              			?>
            		</div>
                </div>
                <div class="col-lg-5">
                </div>
            </div>
    </form>
</div>

<div class="col-lg-12">
    <form class="form-horizontal">
            <div class="form-group">
                <label class="col-lg-2 control-label">{{Index Production}} :</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <?php
            			$indexProduction = config::byKey('indexProduction', 'teleinfo', 'EAIT');
                        echo '<input id="indexProduction" type="text" value="'.$indexProduction.'" data-role="tagsinput"/>';
              			?>
            		</div>
                </div>
                <div class="col-lg-5">
                </div>
            </div>
    </form>
</div>



<?php

include_file('3rdparty', 'bootstrap-tagsinput/bootstrap-tagsinput', 'js', 'teleinfo');
include_file('3rdparty', 'bootstrap-tagsinput/bootstrap-tagsinput', 'css', 'teleinfo');
include_file('desktop', 'options', 'js', 'teleinfo');
include_file('desktop', 'teleinfo', 'css', 'teleinfo');
?>
