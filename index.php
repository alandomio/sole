<?php
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA5);
$user -> login_no_redirect(false);

if($user -> autentica){
	io::headto('building-chart.php', array());
}

$initialize = '';

$link_last_report = '';
if($user -> idg == 5){
	$link_last_report = io::a('ajax/report.php', array('id' => $user -> aUser['CODE']), DOWNLOAD_REPORT, array('class' => 'g-button g-button-yellow'));
}

if(array_key_exists('first_login', $_GET)){
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = trim($_SERVER['HTTP_USER_AGENT']);
	}
	if(!empty($agent) && $user -> idg < 5){
		
		$browsers = ' <a href="http://www.google.it/search?q=Google+Chrome+download">Chrome</a> <a href="http://www.google.it/search?q=Mozilla+Firefox+download">Firefox</a> <a href="http://www.google.it/search?q=safari+download">Safari</a>';
	
		if(strpos($agent, 'Firefox') === false && strpos( $agent, 'Chrome') === false){
			$MYFILE -> add_err(ERR_BROWSER.$browsers);
		}
	}
}
$html['link-codice-appartamento'] = io::a('enable-user.php', array(), LABEL_INSERT_FLAT_CODE, array('class' => 'g-button g-button-yellow', 'style'=>'width:206px;'));


$MYFILE->add_js('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', 1, 'head', 'file');
$MYFILE->add_js(JS_MAIN.'index.js', 10, 'footer', 'file');

include_once HEAD_AR;
?>
<div id="map-content">
	<div id="icons_homepage">
     <ul>
        <li id="edificio"><a href='building-chart.php'><span><?=__('Edifici')?></span></a></li>
        <? /*
        <li id="output"><a href='output.php'><span><?=__('Output')?></span></a></li>
     	*/
     	?>
     </ul>
</div>
    <div id="text_homepage">
    	<?
    	if(LANG_DEF=='IT'){
    	?>
    		<h1>Hive - energy efficiency database</h1>

    		<p>
    		Nei paesi dell'Unione Europea gli edifici consumano circa il 40% del totale dell’energia finale complessivamente utilizzata. 
			</p>
			<p>
			Ma quanto consuma nello specifico l'edificio in cui viviamo? La maggior parte dell'energia viene consumata per il riscaldamento, per il riscaldamento dell'acqua, per l'utilizzo di elettrodomestici o per il raffrescamento?
			</p>
			<p>
			L'attestato di certificazione energetica è uno strumento prezioso per la valutazione del consumo di energia, e consente ad un potenziale acquirente di conoscere il consumo energetico ipotetico di un dato edificio.
			</p>
			<p>
			Tuttavia non dice molto circa il consumo reale, in condizioni di utilizzo reali.
			</p>
			<p>
			Ecco perché abbiamo ideato Hive, un potente strumento di semplice utilizzo che  aiuta a mappare, monitorare e comprendere il consumo di energia negli edifici, anche in casi complessi. 
			</p>
			<p>
			Hive si propone di essere un primo e concreto passo finalizzato alla progettazione di una strategia volta a ridurre il consumo di energia nei nostri edifici.
			</p>
			<p>
			Hive è particolarmente adatto per coloro che si occupano di amministrazione di edifici, per coloro che operano nella riqualificazione energetica degli edifici esistenti, e per coloro che sono attivi nel settore delle costruzioni a basso consumo energetico.
			</p>
	    	<ul>
				<li><a href="building-chart.php">> Guarda gli edifici presenti nel database di Hive</a></li>
				<li><a href="contacts.php">> Contattaci per sapere come unirti alla comunità di Hive</a></li>
			</ul>

			<? } else { ?>
    		<h1>Hive - energy efficiency database</h1>
    		<p>
    			In Europe, 40% of Energy is consumed in the buildings. But… how much does consume, really the building where I live? Where most of energy is consumed, for space heating, or water heating, or for domestic appliances, or for cooling?
    		</p>
    		<p>
    		The Energy Performance Certificate is a valuable tool to assess the energy consumption of a building in standard use condition, so that a possible buyer is informed when choosing between 2 houses, which one will require more energy.
    		</p>
    		<p>
    		But it doesn't tell much about the real consumption, in real use conditions.
			</p>
    		<p>
			That's why we created Hive, an easy to use but powerful tool which will help us to map, monitor and understand energy consumption in the buildings, even in the most complex ones. That's the first, concrete step we need to take in order design an action strategy to reduce energy consumption in our building!
    		</p>
    		<p>
    		Hive is specially suited for those who are in charge of administrate houses and buildings, those who operate in the energy retrofitting of existing buildings, and those who are active in the low energy construction sector.
    		</p>
    		
	    	<ul>
				<li><a href="building-chart.php">> Check our example buildings around Europe</a></li>
				<li><a href="contacts.php">> Contact us to know how you can join us on the Hive community</a></li>
			</ul>
    	
    	<? } ?>
    	
    	<!-- 
    
		<h1>Hive - energy efficiency database</h1>
		<p>
			Hive nasce dall'idea di creare uno strumento per monitorare i consumi energetici degli edifici e 
			conservarne un archivio storico.
		</p>
		<p>
			Nell'Unione Europea i consumi domestici rappresentano circa il 40% del totale dell'energia finale 
			complessivamente utilizzata. Uno strumento che consente di monitorare come e quanta energia viene 
			utilizzata rappresenta una grande opportunità per capire come diminuire l'incidenza di questo dato.
		</p>	
		<p>	La riduzione dei consumi ha un valore in termini di <b>tutela delle risorse e dell'ambiente</b>
			e in termini di <b>benefici economici</b> (riduzione dei costi derivanti dall'impiego di energia per usi 
			domestici).
		</p>
		<p>
			Semplificando, Hive è un database che raccoglie i dati di consumo/produzione energetica degli
			edifici (gas naturale, acqua, energia elettrica, energia solare).
		</p>
		<p>
			I dati vengono immessi nel sistema, consentendo agli utenti con determinati privilegi di avere 
			un <b>quadro statistico e storico dell'andamento dei consumi</b> e agli utenti finali 
			(inquilini degli alloggi monitorati) di capire come stanno consumando, rispetto alle letture 
			precedenti e rispetto alla media dell'edificio di cui fanno parte, considerata la superficie del 
			proprio alloggio.
		</p>
		<p>
			I dati vengono presentati dal sistema in forma grafica e tabellare, per consentire un'analisi 
			chiara e adeguata alle differenti esigenze delle diverse tipologie di utenti.
			Sono presentati quindi sia i dati numerici che possono servire per un'analisi precisa e tecnica, 
			sia delle rappresentazioni in forma di icone create per dare una rappresentazione chiara e 
			immediata dell'efficienza dei consumi energetici.
		</p>
			
		<ul>
			<li><a href="building-chart.php">guarda gli edifici presenti nel database</a></li>
			<li><a href="contacts.php">contattaci</a></li>
		</ul>
		 -->

		</div>
</div>

<div id="page-container">
	<div class="content liquid-content" id="mappaedificio">
		<div class="content" id="ricercaedificio">
			<h3>Login</h3>
			<form method="post" action="./">
			<div class="campo_form">
				<label for="username">Email:</label>	
				<input type="text" id="username" name="username" class="input_form text " style="width:218px;">
			</div>
			<div class="campo_form">
				<label for="password">Password:</label>	
				<input type="password" id="password" name="password" class="input_form text " style="width:218px;">
</div>
			<div class="campo_form" style="margin-top:8px;">
				<input type="submit" style="width:224px;" value="Login" class="g-button g-button-yellow"/>
</div>
			</form>

			<div class="clear"></div>
			<br>
<?=$html['link-codice-appartamento']?>
</div>

		<div id="content-mappa" class="content resizable">
			<div id="mappa" style="min-width: 750px; min-height: 408px; margin: 0 auto"></div>
</div>
</div>
</div>
<div class="clear"></div>
<?php
include_once FOOTER_AR;
?>