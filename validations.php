<?php
include_once 'init.php';
$user = new autentica($aA3);
$user -> login_standard();

/*
 * inizializza alcune variabili passate col get
* */
list($id) = request::get(array('id' => NULL));
// $MYFILE->add_js(JS_MAIN.'convalida.js', 15, 'head', 'file');

$MYFILE->add_js_group('convalida', array(
		JS_MAIN.'convalida.js',
),
		15, 'on', 'head');

$initialize = $body_ini;

$html['select_building'] = sole::select_fhb('1');

$input['uploadtype'] = new io();
$input['uploadtype'] -> type = 'select'; 
//$input['uploadtype']->addblank = true;
$input['uploadtype']->val = 1;
$input['uploadtype'] -> aval = rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
$input['uploadtype'] -> css = 'duecento'; 
$input['uploadtype'] -> id = 'upload_type'; 
$input['uploadtype'] -> txtblank = S_CHOOSE.' '.strtolower(UPLOADTYPE); 
$upload_type = $input['uploadtype'] -> set('upload_type');

$year = sole::select_year('year');

/*
 * costruzione pagina html
* */
include_once HEAD_AR;
?>
<div id="container_convalida">
	<div id="col_left" class="duecentocinquanta" style="min-height: 10px;">
		<div id="wizard">
<form id="myForm" method="post">
<input id="idu" type="hidden" value="<?=$user -> aUser['ID_USER']?>" />
<span><?php print(FIND); ?></span>
<?=$html['select_building']?>
				<p>
					<?=ANNO?>
					:
				</p>
<?=$year?>
				<p>
					<?=UPLOADTYPE?>
					:
				</p>
<?=$upload_type?>
				<input type=checkbox name=davalidare id=davalidare> <?php print __('Mostra solo invii da pubblicare')?>
				<br /> <input type="button" id="show" value="<?=NEXT?>"
					class="g-button g-button-yellow" />

<?php
				if($user->idg <= 3){
				print ' <br /><br /><input type="button" id="ripubblica" value="'.__('Pubblica').'" class="g-button g-button-disabled" />';
				print '<input type="button" id="delete-ms" value="'.__('Annulla pubblicazione').'" class="g-button g-button-disabled" />';
}
?>
</form>
		</div>
	</div>
	<div id=col_right>
<div id="tabs">
<ul>
	<li><a href="#tabs-1"><?=VALIDATE_MEASURES?></a></li>
				<li><a href="#tabs-2"><?=VALIDATE_FORMULAS?> </a></li>
</ul>
			<div id="tabs-1" class="tabpage"></div>
			<div id="tabs-2" class="tabpage"></div>

</div>
</div>

	<div class="clear"></div>
</div>

<div id="dialog-confirm" class="dn" title="<?=LBL_MESSAGE?>">
	<p>
		<span class="ui-icon ui-icon-alert"
			style="float: left; margin: 0 7px 20px 0;"></span>
<?=COMPLETE_FIELDS?>
</p>
</div>

<div id="dialog-change-meter">
	<form id="frm-replace" action="ajax/json.php?action=save_replace_meter"
		method="post">
<input type="hidden" name="ID_METER" id="change_ID_METER" value="" />
<input type="hidden" name="ID_MEASURE" id="change_ID_MEASURE" value="" />
		<div id="meter-data" style="margin: 10px 0; padding: 0;">
			<ul style="list-style: none; margin: 0; padding: 0;"></ul>

			<label><?=D_CHANGE?>:</label><br /> <input type="text"
				name="D_CHANGE" id="D_CHANGE" value="" class="datepicker" />

</div>
<div class="clear"></div>
<table style="border:none; background-color:#eee;">
			<tr>
				<th></th>
				<th></th>
			</tr>
			<tr>
				<td style="padding: 8px;">
					<h3>
						<?=OLD_METER?>
					</h3>

					<div id="old_meter" class="change_meter"></div>

				</td>
				<td style="padding: 8px;">
					<h3>
						<?=NEW_METER?>
					</h3>

					<div id="new_meter" class="change_meter"></div>

				</td>
			</tr>
</table>
</form>

</div>

<script>
$(function(){
	$( "#tabs" ).tabs({
		selected: 0,
		select: function(e, ui){
			var index=ui.index;
			if(index == 2){ // Convalida formule
				id = $('#buildings1').val();
				year = $('#year').val();
				upload = $('select#upload_type').val();
				load_formulas(id, year, upload);
			}
		}
	});
});
</script>
<?php
include_once FOOTER_AR;
?>