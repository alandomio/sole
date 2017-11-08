<?php
include_once 'init.php';
$user = new autentica($aA4);
$user -> login_standard();

$MYFILE -> add_css('js/flexigrid/css/flexigrid.css');


include_once HEAD_AR;

print '
<input id="mother-height" type="hidden" value="0" />
<div id="flex1">
</div>';

ob_start();
?>
<script type="text/javascript">
$(document).ready(function() {

$(function() {
	height = $(window).height();
	$("#mother-height").val(height);
}); 

$("#flex1").flexigrid({
	url: 'ajax/json.php?action=get_sinottica&id_building=<?=$_GET['id_building']?>',
	dataType: 'json',
	colModel : [
		{display: '<?=CODE_METER?>', name : 'CODE_METER', width : 80, sortable : false, align: 'left'},
		{display: '<?=MATRICULA_ID?>', name : 'MATRICULA_ID', width : 80, sortable : false, align: 'left'},
		{display: '<?=REGISTERNUM?>', name : 'REGISTERNUM', width : 80, sortable : false, align: 'left'},
		{display: '<?=USAGE?>', name : 'K2_ID_USAGE', width : 80, sortable : false, align: 'left'},
		{display: '<?=NAME_METER?>', name : 'NAME_METER', width : 80, sortable : false, align: 'left'},
		{display: '<?=SCALA_MT?>', name : 'SCALA_MT', width : 80, sortable : false, align: 'left'},
		{display: '<?=D_FIRSTVALUE?>', name : 'D_FIRSTVALUE', width : 80, sortable : false, align: 'left'},
		{display: '<?=START_1?>', name : 'START_1', width : 80, sortable : false, align: 'left'},
		{display: '<?=START_2?>', name : 'START_2', width : 80, sortable : false, align: 'left'},
		{display: '<?=START_3?>', name : 'START_3', width : 80, sortable : false, align: 'left'},
		{display: '<?=END_1?>', name : 'END_1', width : 80, sortable : false, align: 'left'},
		{display: '<?=END_2?>', name : 'END_2', width : 80, sortable : false, align: 'left'},
		{display: '<?=END_3?>', name : 'END_3', width : 80, sortable : false, align: 'left'},
		{display: '<?=METERPROPERTY?>', name : 'METERPROPERTY', width : 80, sortable : false, align: 'left'},
		{display: '<?=SUPPLYTYPE?>', name : 'SUPPLYTYPE', width : 80, sortable : false, align: 'left'},
		{display: '<?=RF?>', name : 'RF', width : 80, sortable : false, align: 'left'},
		{display: '<?=OUTPUT?>', name : 'OUTPUT', width : 80, sortable : false, align: 'left'},
		{display: '<?=FORMULA?>', name : 'FORMULA', width : 240, sortable : false, align: 'left'},
		{display: '<?=A?>', name : 'A', width : 240, sortable : false, align: 'left'},
		{display: '<?=B?>', name : 'B', width : 240, sortable : false, align: 'left'},
		{display: '<?=FLATS?>', name : 'FLATS', width : 240, sortable : false, align: 'left'},
	],
	buttons : [

	],
	searchitems : [

	],
	sortname: "CODE_METER",
	sortorder: "asc",
	usepager: false,
	title: '',
	useRp: false,
	rp: 100,
	showTableToggleBtn: true,
	width: $(document).width() - 60,
	height: $("#mother-height").val() - 340
	});   
});

function test(){
}
</script>
<?php
$js_flexi = ob_get_clean();
$MYFILE -> add_js('<script src="js/flexigrid/flexigrid.js" type="text/javascript"></script>', 'file', 'footer');
$MYFILE -> add_js($js_flexi, 'code', 'footer');
include_once FOOTER_AR;
?>