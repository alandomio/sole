<form id="form1" action="<?=$swf->upload_url?>" method="post" enctype="multipart/form-data">
<div id="divSWFUploadUI">
<div class="fieldset  flash" id="fsUploadProgress">
<span class="legend">Carica immagini, max 1MB</span>
</div>
<p id="divStatus"></p>
<p>
<span id="spanButtonPlaceholder"></span>
<input id="btnCancel" type="button" value="Annulla" disabled="disabled" class="button_annulla" style="display:none" />
<br />
</p>
</div>
</form>
<noscript>Abilita JavaScript nelle opzioni del tuo browser per poter inserire le foto.</noscript>
<div id="divLoadingContent" class="content" style="padding: 10px 15px; display: none;">
Il programma per le foto sta caricando. Attendere prego...
</div>

<div id="divLongLoading" class="content" style="padding: 10px 15px; display: none;">
Per inserire le foto assicurati che il Plugin Flash sia abilitato e che sia installato Adobe Flash Player.<br />
<form method="post" enctype="multipart/form-data" action="<?=$swf->upload_url?>">
<?=request::hidden($backUri)?>
<input type="hidden" name="return" value="<?=FILENAME.'.php'?>" />
<input type="file" name="Filedata" />
<input type="submit" value="salva" />
</form>
</div>

<div id="divAlternateContent" class="content" style="padding: 10px 15px; display: none;">
Il programma per inserire le foto non pu&ograve; essere caricato, aggiorna Adobe Flash Player.
Visita il <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">sito web Adobe</a> per ottenere l'ultimo Flash Player. <br />
<form  method="post" enctype="multipart/form-data" action="<?=$swf->upload_url?>">
<?=request::hidden($backUri)?>
<input type="hidden" name="return" value="<?=FILENAME.'.php'?>" />
<input type="file" name="Filedata" />
<input type="submit" value="salva" />
</form>
</div>	