<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

$html['select_building'] = sole::select_fhb('');
$input = new io();
$input -> type = 'select';
$input -> addblank = true;
$input -> aval = rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
$input -> css = 'duecento';
$input -> id = 'uploadtype';
$input -> txtblank = S_CHOOSE.' '.strtolower(UPLOADTYPE);
$uploadtype = $input -> set('uploadtype');


$MYFILE->add_js_group('measures2', array(
		JS_MAIN.'measures2.js',
),
	10, 'onoff', 'footer');

include_once HEAD_AR;
?>
<div id="container_convalida">
<div id="col_left" class="duecentocinquanta" style="min-height:10px;">
<div id="box-col-left" class="fixed_position">
<p><?=CH_BUILDING?>:</p>
<?=$html['select_building']?>
<p><?=ANNO?>:</p>
<?=sole::select_year('year')?>

<p>Upload Excel:</p>
<form id="frm-excel" method="post" enctype="multipart/form-data" action="ajax/excel.php?action=carica">
<div style="position:relative;">
<input type="button" class="g-button g-button-yellow" value="<?=CHOOSE?> excel" style="width:200px;" />
<input type="file" name="excel" id="excel" style="cursor:pointer!important; -moz-opacity:0; filter:alpha(opacity: 0); opacity: 0; z-index:2; position:absolute; top:0; left:0;" />
</form>

	<div id="del-measures" class="hidden">
		<p><?=UPLOADTYPE?>:</p>
		<?=$uploadtype?>
	</div>
</div>
</div>
</div>
<div id="col_right">
<div class="fixed_position">
<div id="table-menu" >
	<div id="buttons">
	<a href="#" rel="choose_date" title="Choose date"><img src="images/icon-calendar.png" alt="Choose date" /></a>
	<a href="#" rel="download_model" title="Download excel model"><img src="images/icon-excel.png" alt="Export excel model" /></a>
	<a href="#" rel="print_page" title="Print page"><img src="images/icon-print.png" alt="Print" /></a>
	<a href="#" rel="delete_rows" title="Delete selected rows"><img src="images/icon-trash.gif" alt="Delete" /></a>
	</div>
</div>
<div id="head-table">
<div><?=ANNO?></div>
<div style="margin-left:6px;"><?=MONTH?></div>
<div style="margin-left:28px;"><?=DATE?></div>
<div style="margin-left:115px;">F1</div>
<div style="margin-left:122px;">F2</div>
<div style="margin-left:120px;">F3</div>
<div style="margin-left:104px;"><?=ACTION?></div>
</div>
</div>
<div id="table-content">
<h2 style="margin-top:20px;"><?= SCEGLI_ANNO_EDIFICIO?></h2>
</div>
</div>
<div class="clear"></div>
</div>
<div id="dialog-download" class="hide inputfull">
<form id="frm-add-model" method="post" action="ajax/templates.php?action=add_new_model">
<div id="selected_building"></div>
<div id="selected_year"></div>
<input type="hidden" name="id_building" value="" />
<input type="hidden" name="year" value="" />
<label><?=CHOOSE.' '.ID_UPLOADTYPE?></label>
<? print sole::select_uploadtype('month_download') ?>
</form>
</div>
<div id="dialog-date" class="hide inputfull">
<label><?=CHOOSE_DATE?></label>
<input type="text" name="set_date" id="date_date" class="datepicker" value="" />
<label><?=CHOOSE.' '.ID_UPLOADTYPE?></label>
<? print sole::select_uploadtype('date_month') ?>
</div>
<?php
include_once FOOTER_AR;
?>