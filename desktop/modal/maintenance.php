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
$date = array(
	'start' => date('Y-m-d', strtotime(config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d'))),
	'end' => date('Y-m-d'),
);
$archiveDatetime = date('Y-m-d H:i:s', strtotime('- 1 months'));

?>


<div id='div_MaintenanceAlert' style="display: none;"></div>
<div class='row'>
    <div class='col-md-12'>
        <center><h4>Statistiques</h4></center>
    </div>
    <div class='col-md-8 col-md-offset-2 center'>
        <?php
            $regenerateMonthlyStat = cache::byKey('teleinfo::regenerateMonthlyStat', '0');
                if($regenerateMonthlyStat->getValue() != '1'){
                    echo '<a class="btn btn-sm btn-success eqLogicAction" data-action="regenerateMonthlyStat"><i class="fas fa-check-circle"></i> {{Regénérer les stats mensuelles}}</a>';
                }
                else{
                    echo '<a class="btn btn-sm btn-success eqLogicAction" data-action="regenerateMonthlyStat" disabled><i class="fas fa-check-circle"></i> {{Regénérer les stats mensuelles}}</a>';
                }
        ?>
	</div>



</div>
<div class='row' style="padding-top:10px;">
    <div class='col-md-8 col-md-offset-2'>
        <div class='col-md-12'>
            <center><h4>Optimisations</h4></center>
        </div>
        <div class='col-md-12'>
            <div class="alert alert-danger globalRemark col-md-12">Attention, ce tableau permet de ne garder que la valeur maximale rencontrée par interval horaire dans l'historique.
        	</br>
        	Si un nettoyage est nécessaire alors un bouton s'affichera sur la commande.
        	</div>
    	</div>

        <div class='col-md-12'>
            <table class="table table-striped table-condensed" id="table_maintenance">
                <thead>
                    <tr>
                        <th class='col-md-2'>{{Donnée}}</th>
                        <th class='col-md-2'>{{Nombre en base}}</th>
                        <th class='col-md-2'>{{A lisser}}</th>
                        <th class='col-md-2'>{{Plus ancienne valeur}}</th>
                        <th class='col-md-2'>{{Lissage}}</th>
                        <th class='col-md-4'></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class='col-md-2'>

    </div>



</div>






<?php include_file('desktop', 'maintenance', 'js', 'teleinfo');?>
