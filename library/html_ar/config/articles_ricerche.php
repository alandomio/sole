<?php
$my_vars -> inputs();
$html['ricerca_avanzata'] = '';
if($vasz == 1){ # CASA
	$my_vars -> ricerca = '<th colspan="'.$my_vars->colonne.'"></th>';
	
	
	$html['ricerca_avanzata'] .= '<tr><th colspan="'.($my_vars->colonne+1).'">
  <table id="tab_clear">
	<tr><td><h1>Ricerca</h1></td></tr>
		<tr>
	<td colspan="8">
<div style="float:right;"><span class="lbl">Inserito da (email utente)</span> '.$my_vars->inputs['USER'].'</div>
<span class="lbl">'.PROVINCE.'</span> '.$my_vars->inputs['PROVINCE'].' | 
<span class="lbl">'.CASA_ZONE.'</span> '.$my_vars->inputs['CASA_ZONE'].' | 
<span class="lbl">'.CASA_CONTRACT.': </span> '.$my_vars->inputs['CASA_CONTRACT'].'
</td>
	</tr>

	<tr>
	<td colspan="8">
<span class="lbl">'.PREZZO_ARTICLE.'</span> '.$my_vars->inputs['PREZZO_ARTICLE'].' | 
<span class="lbl">'.CASA_MQ_INTERNI.'</span> '.$my_vars->inputs['CASA_MQ_INTERNI'].' | 
<span class="lbl">'.CASA_MQ_GIARDINO.'</span> '.$my_vars->inputs['CASA_MQ_GIARDINO'].' | 
<span class="lbl">'.CASA_STANZE.'</span> '.$my_vars->inputs['CASA_STANZE'].'</td>
	</tr>
	<tr>
  <td><span class="lbl">'.CASA_TYPE.':</span></td><td colspan="5">'.$my_vars->inputs['CASA_TYPE'].'</td>
</tr>

	
	
	';
	$html['ricerca_avanzata'] .= '</table></th></tr>';

}
elseif($vasz == '2'){ # MOTORI
	$my_vars->campi_ricerca();
}
elseif($vasz == '3'){ # NAUTICA
	$my_vars->campi_ricerca();
}
elseif($vasz == '4'){ # LAVORO
	$my_vars->campi_ricerca();
}
elseif($vasz == '5'){ # INCONTRI
	$my_vars->campi_ricerca();
}
elseif($vasz == '6'){ # COMPROVENDO
	$my_vars->campi_ricerca();
}
?>