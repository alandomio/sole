<form id="form1" action="<?=$swf->upload_url?>" method="post" enctype="multipart/form-data">
<div id="divSWFUploadUI">
<div class="fieldset  flash" id="fsUploadProgress">
<span class="legend"><?=SWF_CODA.' - '.$swf->files_types.' max '.$swf->file_size_limit.' MB'?></span>
</div>
<p id="divStatus">Salvataggio multiplo file</p>
<p>
<span id="spanButtonPlaceholder"></span>
<input id="btnCancel" type="button" value="<?=CANCEL?> uploads" disabled="disabled" class="button_annulla" />
<br />
</p>
</div>
</form>      
<noscript>Abilita JavaScript nelle opzioni del tuo browser per poter usare SWFupload.</noscript>
<div id="divLoadingContent" class="content" style="background-color: #FFFF66; border-top: solid 4px #FF9966; border-bottom: solid 4px #FF9966; margin: 10px 25px; padding: 10px 15px; display: none;">
SWFUpload sta caricando. Attendere prego...
</div>
<div id="divLongLoading" class="content" style="background-color: #FFFF66; border-top: solid 4px #FF9966; border-bottom: solid 4px #FF9966; margin: 10px 25px; padding: 10px 15px; display: none;">
SWFUpload sta impiegando molto tempo per caricarsi. Assicurarsi che il Plugin Flash sia abilitato e che sia installata una versione funzionante di Adobe Flash Player. <br />
<form method="post" enctype="multipart/form-data" action="<?=$swf->upload_url?>">
<input type="hidden" name="return" value="<?=FILE.'.php'?>" />
<input type="hidden" name="id_rec" value="<?=$_GET['id_rec']?>" />
<input type="file" name="Filedata" />
<input type="submit" value="salva" />
</form>
</div>
<div id="divAlternateContent" class="content" style="background-color: #FFFF66; border-top: solid 4px #FF9966; border-bottom: solid 4px #FF9966; margin: 10px 25px; padding: 10px 15px; display: none;">
SWFUpload non pu&ograve; essere caricato. &Egrave; necessario un aggiornamento di Flash Player.
Visita il <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">sito web Adobe</a> per ottenere l'ultimo Flash Player. <br />
<form  method="post" enctype="multipart/form-data" action="<?=$swf->upload_url?>">
<input type="hidden" name="return" value="<?=FILE.'.php'?>" />
<input type="hidden" name="id_rec" value="<?=$_GET['id_rec']?>" />
<input type="file" name="Filedata" />
<input type="submit" value="salva" />
</form></div>	