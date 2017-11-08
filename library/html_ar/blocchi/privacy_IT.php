<?php
ob_start();
?>
Gentile signore/a,

desideriamo informarLa che il d.lgs. n. 196 del 30 giugno 2003 ("Codice in materia di protezione dei dati personali")
prevede la tutela delle persone e di altri soggetti rispetto al trattamento dei dati personali.

Secondo la normativa indicata, tale trattamento sarà improntato ai principi di correttezza, liceità e trasparenza e di tutela
della Sua riservatezza e dei Suoi diritti.

Ai sensi dell'art.13 d.lgs.196/2003 la nostra Cooperativa <?=$hc_name?> è tenuta a fornirLe alcune
informazioni relative all'utilizzo dei dati personali da Lei forniti o comunque da noi acquisiti anche in futuro, nel corso
della durata dell'erogazione del Servizio di informazione dei consumi energetici e di acqua negli edifici mediante il database "Sole" www.sole-project.com.


1. FINALITA'E MODALITA'DEL TRATTAMENTO CUI SONO DESTINATI I DATI

I dati relativi ai consumi energetici del Vostro alloggio sono raccolti manualmente da un persona incaricata dalla nostra Coopertaiva, che effettua la lettura dei contatori ed immette i dati nel database "Sole". 
Il trattamento dei dati raccolti avviene mediante strumenti manuali, informatici e telematici, con logiche e
modalità strettamente correlate alla finalità di informarLa dei consumi energetici riconducibili al Vostro alloggio, in modo comparato rispetto ai consumi medi dell'edificio presso il quale Lei risiede.

Nel corso del tempo. I dati saranno trattati nel rispetto delle regole di riservatezza e sicurezza previsti dalla legge, anche
in caso di eventuale comunicazione a terzi; i dati raccolti saranno in ogni caso conservati e trattati per il periodo
necessario alla erogazione del Servizio. 
Il trattamento dei dati NON riguarderà in ogni caso anche categorie di dati c.d.
"sensibili".
I dati trattati non saranno messi a conoscenza delle altre famiglie residenti nell'edificio, se non in forma aggregata (consumi totali o medi dell'intero edificio), né tantomeno di famiglie o altri soggetti terzi non direttamente responsabili dell'erogazione del Servizio.
Per soli scopi di ricerca, i dati potranno essere trasmessi a terzi in forma assolutamente anonima.

2. NATURA OBBLIGATORIA DEL CONFERIMENTO DEI DATI E CONSEGUENZE DI UN EVENTUALE
RIFIUTO DI RISPONDERE.

I dati raccolti hanno natura obbligatoria per poter effettuare le operazioni di cui al punto 1. La mancata
accettazione e la conseguente mancata autorizzazione al trattamento dei dati comporta l'impossibilità per la Cooperativa di informarla mediante il Servizio "Sole".
Si chiarisce che i dati relativi alle forniture centralizzate di energia intestate alla Cooperativa o al Condominio non necessitano della Vostra accettazione al trattamento, in quanto non costituiscono dati personali.

3. AMBITO DI COMUNICAZIONE E DIFFUSIONE

I dati relativi ai consumi di energia ed acqua, dopo l'elaborazione informatizzata da parte del database "Sole", saranno accessibili solo dalle persone responsabili dell'erogazione del Servizio ed alla Vostra famiglia. Altri utenti aderenti al Servizio "Sole" avranno accesso ai vostri dati di consumo esclusivamente in forma aggregata (consumi totali e medi nell'intero edificio).
Comunicazioni a terzi per fini scientifici o di ricerca saranno effettuate esclusivamente in forma aggregata, o comunque anonima (senza nessuna possibilità di risalire al titolare dei dati personali).

4. DIRITTI DELL'INTERESSATO

L'art.7 del d.lgs.196/2003 Le attribuisce, in quanto soggetto interessato, i seguenti diritti:

a) ottenere la conferma dell'esistenza dei Suoi dati personali, anche se non ancora registrati e la loro
comunicazione in forma intelligibile.

b) l'indicazione dell'origine dei dati personali, della finalità e modalità del loro trattamento; della logica applicata
in caso di trattamento effettuato con l'ausilio di strumenti elettronici; degli estremi identificativi del titolare,
del responsabile e dei soggetti o categorie di soggetti ai quali i dati possono essere comunicati o che possono
venirne a conoscenza in qualità di responsabile o incaricato.

c) ottenere l'aggiornamento, la rettifica o l'integrazione dei dati; la loro cancellazione, la trasformazione in forma
anonima o il blocco dei dati trattati in violazione di legge; l'attestazione che tali operazioni sono state portate a
conoscenza degli eventuali soggetti cui i dati sono stati comunicati o diffusi.

d) opporsi al trattamento dei Suoi dati personali in presenza di giustificati motivi o nel caso in cui gli stessi siano
utilizzati per l'invio di materiale pubblicitario, di direct marketing o per il compimento di indagini di mercato.


5. ESTREMI IDENTIFICATIVI DEL TITOLARE

Titolare del Trattamento dei Suoi dati è la Cooperativa <?=$hc_name?>, con sede in <?=$hc_address?>, nella persona
<?=$hc_reference?>, cui Lei potrà rivolgersi per far valere i Suoi diritti così come previsti dall'art. 7 del D.Lgs. 196/2003. Il nominativo di ulteriori ed eventuali responsabili
nominati è reperibile presso l'ufficio soci della Cooperativa.
<?php
$privacy = ob_get_clean();
echo htmlspecialchars($privacy);
?>