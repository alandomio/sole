<?php
# V.0.1.10
?>
<div id="left_prodotti">
  <form method="post" enctype="multipart/form-data">
    <?=request::hidden($backUri)?>  
    <table>    
      <tr class="bg">      
        <th colspan="2"><input type="submit" name="subDo" id="button" value="salva" class="button_salva" style="float:right" />
          <?=$href_annulla?> <?php if($scheda->action_type != 'read') print $href_nuovo; ?>
        </th>    
      </tr>    
	<? if($crud=='upd'){ if(strlen($etichetta)==0) $etichetta = '&nbsp;';
	  print'
      <tr class="celeste">      
        <td colspan="2">
          '.$etichetta.'
        </td>    
      </tr>';}
foreach($scheda->ext_table as $ext_table=>$ext_id){
	if(!in_array($k,$aStripC)){
	?>
<tr>
  <td valign="top"><?=constant(stringa::tbl2field($ext_table))?></td>
  <td><? $db->$ext_id->get(); ?></td>
</tr><? }}

foreach($sublable as $k=>$v){
	if(!in_array($k,$aStripC) && ($k != $scheda->f_path)){
	?>
<tr>
  <td valign="top"><?=$v?></td>
  <td><? $db->$k->get(); ?></td>
</tr><? }}?>

<?php
include BLOCCHI_AR.'crud_image.php';
      	?>                         
      <th colspan="2" align="right">      
        <input name="subDo" type="submit" value="<?=SAVE?>" class="button_salva" />      
      </th>    
      </tr>  
    </table>
  </form>
</div>