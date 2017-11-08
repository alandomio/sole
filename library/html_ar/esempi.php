<?php
# SEMPLICE
$news_d = new nw('news');
$news_d->query_list();
$news_vars = new ordinamento(array());
$news_vars->tabella = $news_d->table;
$news_vars->set_filtro(array());
$news_vars->where('');
$news_tot_rec = $news_d->query_list;
$news_tot_rec.= ' '.$news_vars->where;
$a_news = rs::inMatrix($news_tot_rec);

# CON PAGINAZIONE
$news_d = new nw('news');
$news_d->offset=1;
$news_d->query_list();
$news_vars = new ordinamento(array());
$news_vars->tabella = $news_d->table;
$news_vars->set_filtro(array());
$news_vars->where('');
$news_tot_rec = $news_d->query_list;
$news_tot_rec.= ' '.$news_vars->where;

$player_curs=new player_curs($news_tot_rec, $news_d->offset);
$backUri=array_merge($player_curs->aCurs,$news_vars->href);
$player_curs->vars=$backUri;
$player_curs->set();
$a_news = rs::inMatrix($news_tot_rec." ".$player_curs->limit_sql);

ob_start(); # HTML CURSORI
?>
<div id="paginazione"><div id="content_paginazione"><?
for($i=0;$i<7;$i++){?>
	<?=$player_curs->player[$i]; }?>
<div class="clear"></div>
</div></div><?php
$html['news']['player']=ob_get_clean();

# PHOTOGALLERY
$q="SELECT
files.ID_FILE,
files.TYPE,
files.TITLE,
files.DESCRIP,
files.PATH
FROM
".$news_d->file_table."
Left Join files ON ".$news_d->file_table.".ID_FILE = files.ID_FILE
WHERE
".$news_d->file_table.".".$news_d->f_id." = '".$news[$news_d->f_id]."'";
$aImage = rs::inMatrix($q);

	$html['news']['photogallery']='';
	foreach($aImg as $k => $img){
		$html['news']['photogallery'].='<a href="'.IMG_ALB_WEB.$img['PATH'].'" title="'.$img['TITLE'].'" rel="lightbox[galerie]" target="_blank"><img src="'.IMG_ALB_THU.$img['PATH'].'" alt="'.$img['TITLE'].'" /></a>';
	}
	if(!empty($html['news']['photogallery'])){
		$html['news']['photogallery'] = '<div class="box_photogallery">
<h2>Photogallery</h2>
'.$html['news']['photogallery'].'
</div>';
	}


# HTML VARI

$img = !empty($news['PATH_NEW']) ? '<a href="'.IMG_MAIN_BIG.$news['PATH_NEW'].'" title="'.$news['NEW_'.$lang->def].'" target="_blank"><img src="'.IMG_MAIN_WEB.$news['PATH_NEW'].'" alt="'.$news['NEW_'.$lang->def].'" align="right" /></a>' : '';



?>