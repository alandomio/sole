
/* Script per l'inserimento di object flash nella pagina senza avviso di protezione */ 


function flash_testa(nome,lan)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="756" height="181">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="movie" value="' + nome + '.swf?lingua='+ lan +'" /><param name="quality" value="high" />\n');
	document.write('<param name="bgcolor" value="#ffffff" />\n');
	document.write('<param name="wmode" value="transparent" />');
	document.write('<embed src="' + nome + '.swf?lingua='+ lan +'" quality="high" bgcolor="#ffffff" width="756" height="181" align="middle" scale="noscale" wmode="transparent" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}
		
		
		function menu(sez,lan)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="251" height="275">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="wmode" value="transparent" />');
	document.write('<param name="movie" value="menu.swf" /><param name="quality" value="high" />\n');
	document.write('<embed src="menu.swf" quality="high"  width="251" height="275" align="middle" scale="noscale" wmode="transparent" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}
	
		function head()
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="454" height="212">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="wmode" value="#c2c2c2" />');
	document.write('<param name="movie" value="head.swf" /><param name="quality" value="high" />\n');
	document.write('<embed src="head.swf" quality="high"  width="454" height="212" align="middle" scale="noscale" wmode="#c2c2c2" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}	
	
	
		function titoli_small(tit)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="254" height="35">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="wmode" value="transparent" />');
	document.write('<param name="movie" value="titoli_small.swf?tit=' + tit  +'" /><param name="quality" value="high" />\n');
	document.write('<embed src="titoli_small.swf?tit=' + tit  +'" quality="high"  width="254" height="35" align="middle" scale="noscale" wmode="transparent" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}
			
	function categoria(nomefoto,larg,alt,del,col)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'+larg+'" height="'+alt+'">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="align" value="middle" />\n')
	document.write('<param name="wmode" value="transparent" />');
	document.write('<param name="movie" value="categoria.swf?nomefoto='+ nomefoto +'&larg='+ larg +'&alt='+ alt +'&del='+ del +'&col='+ col +'" /><param name="quality" value="high" />\n');
	document.write('<embed src="categoria.swf?nomefoto='+ nomefoto +'&del='+ del +'&larg='+ larg +'&alt='+ alt +'&col='+ col +'" quality="high"  width="'+larg+'" height="'+alt+'" align="middle" scale="noscale" wmode="transparent" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}	
		
		function titoletti(tit,subtit)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="270" height="45">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<param name="wmode" value="transparent" />');
	document.write('<param name="movie" value="titoletti.swf?tit=' + tit  +'&subtit='+ subtit +'" /><param name="quality" value="high" />\n');
	document.write('<embed src="titoletti.swf?tit=' + tit  +'&subtit='+ subtit +'" quality="high"  width="270" height="45" align="middle" scale="noscale" wmode="transparent" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}
		
	function flash_intro(nome,lan)
{
    document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="750" height="525">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="movie" value="' + nome + '.swf?lingua='+ lan +'" /><param name="quality" value="high" />\n');
	document.write('<param name="bgcolor" value="#ffffff" />\n');
	document.write('<param name="scale" value="noscale" />\n')
	document.write('<embed src="' + nome + '.swf?lingua='+ lan +'" quality="high" bgcolor="#ffffff" width="750" height="525" align="middle" scale="noscale" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
    document.write('</object>\n');
}
	
		function flash_gallery(nome,lan)
{
    document.write('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100%" height="100%" align="top">\n');
    document.write('<param name="allowScriptAccess" value="sameDomain" />\n');
	document.write('<param name="movie" value="' + nome + '.swf?lingua='+ lan +'" /><param name="quality" value="high" />\n');
	document.write('<param name="wmode" value="transparent" />\n');		
	document.write('<param name="scale" value="noscale" />\n');	
	document.write('<param name="bgcolor" value="#ffffff" />\n');
	document.write('<embed src="grey.swf?nocache=random(65000)" quality="high" bgcolor="#000000" width="100%" height="100%" align="middle" scale="noscale" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent" />\n');
    document.write('</object>\n');
}

							
					 

