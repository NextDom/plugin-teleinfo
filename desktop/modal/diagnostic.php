<?php
if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
?>



<div id='divDiagnosticAlert' style="display: none;"></div>

<table class="table table-condensed tablesorter" id="table_health">
	<thead>
		<tr>
			<th>{{Etapes}}</th>
			<th>{{Exécution}}</th>
			<th>{{Résultat}}</th>
            <th>{{Conseil}}</th>
		</tr>
	</thead>
	<tbody>

	</tbody>
</table>

<?php include_file('desktop', 'diagnostic', 'js', 'teleinfo');?>
