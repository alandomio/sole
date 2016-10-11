<?php
# casa => 2, motori => 3, lavoro => 4, compro e vendo => 5
/*	
$my_vars -> campi_force['text']['ARTICLE'] = 'articles'; # $my_vars -> campi_force['tipo_input']['CAMPO'] = 'nome_tabella';
$my_vars -> campi_force['range']['PREZZO_ARTICLE'] = 'articles';
$my_vars -> campi_force['range']['CASA_MQ_INTERNI'] = 'articles';
$my_vars -> campi_force['multicheck']['CASA_TYPE'] = 'casa_types';
$my_vars -> campi_force['multicheck']['CASA_CONTRACT'] = 'casa_contracts';
$my_vars -> campi_force['text']['USER'] = 'users';
*/
# TUTTI GLI STRIP
$aGenerica = array(
'IS_NAME', 'IS_EMAIL', 'IS_TELEPHONE', 'IS_CELL', 'IS_NEWSLETTER', 'IS_CONFIRMED', 'IS_ABIL', 'IS_PRIVACY', 'PATH_USER', 'PASSWORD', 'ALIAS', 'NAME', 'SURNAME', 'ADDRESS_USR', 'FOLDER_USR', 'RAGIONESOCIALE_USR', 'PIVA_USR', 'CF_USR', 'WEBSITE_USR', 'TELEPHONE_USR', 'CELL_USR', 'ID_GRUPPI', 'ID_CLIENTS_TYPE', 'ID_USER_TYPE', 'CODE', 'TS', 'ID_USERS_TYPE',
'ORDINAMENTO_CATEG', 'IS_ANNUNCI',
'IS_ALLINEATO', 'TS_INSERT', 'TS_UPDATE', 'FOLDER_ARTICLE', 'ID_STEP',
'IS_DEFAULT'
);
$aGenericaList = array(
'IS_URGENTE', 'DESCRIP_ART_IT', 'PREZZO_ARTICLE', 'RECAPITO', 'ANNO',
'ID_DESCRIPTORS_CATEG',
'K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K0_ID_COMUNI', 'K0_ID_LOCALITA',
'K1_ID_CAT1','K0_ID_CAT2','K0_ID_CAT3','K0_ID_CAT4','K0_ID_CAT5','K0_ID_CAT6','K0_ID_CAT7','K0_ID_CAT8','K0_ID_CAT9',
'IS_CASA_GIARDINO', 'IS_CASA_VISTA_APERTA', 'IS_CASA_VISTA_MARE', 'IS_CASA_CUCINA_ABITABILE', 'IS_CASA_CANTINA', 'IS_CASA_DISABILI', 'IS_CASA_PA_COPERTO', 'IS_CASA_PA_SCOPERTO', 'IS_CASA_PA_FACILE', 'IS_CASA_BOX', 'IS_CASA_BOX_DOPPIO', 'IS_CASA_FOTOVOLTAICO', 'IS_CASA_DOMOTICO', 'IS_CASA_CLIMA', 'IS_CASA_ALLARME', 'IS_CASA_TV_SAT', 'IS_CASA_CAMINETTO', 'IS_CASA_SAUNA', 'IS_CASA_PISCINA', 'IS_CASA_TAVERNA', 'IS_CASA_MANSARDA', 'IS_CASA_ABITAZIONI', 'IS_CASA_PA', 'IS_CASA_ACQUA', 'IS_CASA_ELETTRICITA', 'IS_CASA_ANCHE_AFFITTO', 'IS_CASA_APERTURA_ELETTRICA', 'IS_CASA_LOCATO', 'IS_CASA_ALTA_RENDITA', 'IS_CASA_PASSO_CARRABILE', 'IS_CASA_PARCHEGGIO_CLIENTI', 'IS_CASA_VETRINE', 'IS_CASA_ZONA_PASSAGGIO', 'IS_CASA_ATTIVITA_AVVIATA', 'IS_CASA_ZONA_TURISTICA', 'IS_CASA_ZONA_CONGRESSUALE', 'IS_CASA_RISTORANTE', 'IS_CASA_ANCHE_VENDITA', 'IS_CASA_NO_ANIMALI', 'IS_CASA_SETTIMANA_BREVE', 'IS_CASA_USO_CUCINA', 'IS_CASA_BAGNO_COMUNE', 'IS_CASA_BAGNO_PERSONALE', 'IS_CASA_LAVATRICE', 'IS_CASA_WIRELESS', 'IS_CASA_TV_CAVO', 'IS_CASA_TELEFONO', 'IS_CASA_SPORT', 'IS_CASA_ESCURSIONI', 'IS_CASA_WELLNESS', 'IS_CASA_BEAUTY', 'IS_CASA_TERMALE', 'IS_CASA_SU_MARE_PISTE', 'IS_CASA_VETRINE', 'IS_CASA_ZONA_PASSAGGIO', 'IS_CASA_ATTIVITA_AVVIATA', 'IS_CASA_ZONA_TURISTICA', 'IS_CASA_ZONA_CONGRESSUALE', 'IS_CASA_RISTORANTE', 'IS_CASA_ANCHE_VENDITA', 'IS_CASA_NO_ANIMALI', 'IS_CASA_SETTIMANA_BREVE', 'IS_CASA_USO_CUCINA', 'IS_CASA_BAGNO_COMUNE', 'IS_CASA_BAGNO_PERSONALE', 'IS_CASA_LAVATRICE', 'ID_K2_CASA_POSIZIONE', 'ID_K2_CASA_ARREDO', 'ID_K2_CASA_RISCALDAMENTO', 'ID_K2_ASCENSORE', 'ID_K2_CASA_PAGAMENTO',
'CASA_CAMERE', 'CASA_BAGNI', 'CASA_LIVELLI', 'CASA_PIANO', 'CASA_PIANI', 'CASA_MQ_ESTERNI', 'CASA_N_BOX', 'CASA_N_CANTINE', 'CASA_ADATTA_A', 'CASA_MQ_EDIFICABILI', 'CASA_BOX_LARGHEZZA', 'CASA_BOX_LUNGHEZZA', 'CASA_MQ_COPERTI', 'CASA_MQ_SCOPERTI', 'CASA_MQ_UFFICIO', 'CASA_N_SALE', 'CASA_N_COPERTI_INTERNI', 'CASA_N_COPERTI_ESTERNI', 'CASA_STELLE', 'CASA_N_SUITES', 'CASA_N_LETTI', 'CASA_PREZZO_MENSILE', 'CASA_ALTRE_SPESE', 'CASA_N_INQUILINI', 'CASA_DISTANZA_MARE_IMPIANTI'
);

$aCasa = array('CASA_MQ_INTERNI', 'CASA_MQ_GIARDINO', 'CASA_STANZE', 'PREZZO_CASA');
$aMotori = array('MOTORI_KM', 'MOTORI_KW', 'MOTORI_CILINDRATA', 'IS_MOTORI_AIRBAG', 'IS_MOTORI_ALZACRISTALLI', 'IS_MOTORI_CLIMA','IS_MOTORI_PELLE', 'IS_MOTORI_NAVIGATORE', 'IS_MOTORI_AIRBAG_LATERALE', 'IS_MOTORI_CERCHI', 'IS_MOTORI_CLIMA_AUTOMATICO', 'IS_MOTORI_SENSORI_PARCHEGGIO', 'IS_MOTORI_TETTUCCIO', 'IS_MOTORI_AIRBAG_PASSEGGERO', 'IS_MOTORI_CHIUSURA_CENTRALIZZATA', 'IS_MOTORI_FARI_XENON', 'IS_MOTORI_SERVOSTERZO', 'IS_MOTORI_TRAZIONE_INTEGRALE', 'ID_MOTORI_COLORI', 'PREZZO_MOTORI', 'COLORE_MOTORI');
$aLavoro = array('PREZZO_LAVORO');
$aComprovendo = array('PREZZO_COMPROVENDO','K1_ID_CAT1','K0_ID_CAT2','K0_ID_CAT3','K0_ID_CAT4','K0_ID_CAT5','K0_ID_CAT6','K0_ID_CAT7','K0_ID_CAT8','K0_ID_CAT9');

# CAMPI ORDINAMENTO COMUNI
$aAllOrd = array('ds' => 'D_SCADENZA', 'sz' => 'ID_DESCRIPTORS_CATEG', 'ta' => 'TITLE_ARTICLE', 'oc' => 'OFFROCERCO');

if($vasz == 2){ # CASA
	$scheda->ext_table(array('users', 'offrocercos'));
	$scheda -> descriptors('descriptors', array('K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K0_ID_COMUNI', 'K0_ID_LOCALITA'));
	
	$aStripExt = array_merge($aGenerica, $aMotori, $aLavoro, $aComprovendo, array('ID_NUOVO', 'ANNO')); 
	$aStripList = array_merge($scheda->astrip, $aStripExt, $aGenericaList, array('ID_OFFROCERCO'));
	$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));
	
	$my_vars = new ordinamento(array_merge($aAllOrd, array('ci' => 'CASA_MQ_INTERNI', 'cg' => 'CASA_MQ_GIARDINO', 'cs' => 'CASA_STANZE', 'usr' => 'USER', 'pa' => 'PREZZO_CASA')));
	
	$my_vars -> js_dpt = array('statis');
	$my_vars -> sort_default($scheda -> f_id);
	$my_vars -> tabella = $scheda -> table;

	}
	
elseif($vasz == '3'){ # MOTORI
	$scheda->ext_table(array('users', 'nuovos', 'offrocercos'));
	$scheda -> descriptors('descriptors', array('K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K0_ID_COMUNI', 'K0_ID_LOCALITA'));
	
	$aStripExt = array_merge($aGenerica, $aCasa, $aLavoro, $aComprovendo, array());
	$aStripList = array_merge($scheda->astrip, $aStripExt, $aGenericaList, array('IS_MOTORI_AIRBAG', 'IS_MOTORI_ALZACRISTALLI', 'IS_MOTORI_CLIMA', 'IS_MOTORI_PELLE', 'IS_MOTORI_NAVIGATORE', 'IS_MOTORI_AIRBAG_LATERALE', 'IS_MOTORI_CERCHI', 'IS_MOTORI_CLIMA_AUTOMATICO', 'IS_MOTORI_SENSORI_PARCHEGGIO', 'IS_MOTORI_TETTUCCIO', 'IS_MOTORI_AIRBAG_PASSEGGERO', 'IS_MOTORI_CHIUSURA_CENTRALIZZATA', 'IS_MOTORI_FARI_XENON', 'IS_MOTORI_SERVOSTERZO', 'IS_MOTORI_TRAZIONE_INTEGRALE','MOTORI_KW'));
	$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));
	$my_vars = new ordinamento(array_merge($aAllOrd, array('an' => 'ANNO', 'nv' => 'NUOVO', 'cs' => 'CASA_STANZE', 'usr' => 'USER', 'pa' => 'PREZZO_MOTORI', 'km' => 'MOTORI_KM', 'cl' => 'MOTORI_CILINDRATA', 'co' => 'COLORE_MOTORI')));
	
	$my_vars -> sort_default($scheda -> f_id);
	$my_vars -> tabella = $scheda -> table;
}

elseif($vasz == '4'){ # LAVORO
	$scheda->ext_table(array('users', 'offrocercos'));
	$scheda -> descriptors('descriptors', array('K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K0_ID_COMUNI', 'K0_ID_LOCALITA'));
	$aStripExt = array_merge($aGenerica, $aCasa, $aMotori, $aComprovendo, array('ANNO', 'ID_NUOVO','PREZZO_LAVORO')); 
	$aStripList = array_merge($scheda->astrip, $aStripExt, $aGenericaList);
	$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));

	$my_vars = new ordinamento(array_merge($aAllOrd, array('usr' => 'USER', 'oc' => 'OFFROCERCO')));
	$my_vars -> sort_default($scheda -> f_id);
	$my_vars -> tabella = $scheda -> table;
}

elseif($vasz == '5'){ # COMPROEVENDO
	$scheda->ext_table(array('users'));
	$scheda -> descriptors('descriptors', array(
	'K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K0_ID_COMUNI', 'K0_ID_LOCALITA',
	'K1_ID_CAT1', 'K0_ID_CAT2', 'K0_ID_CAT3', 'K0_ID_CAT4', 'K0_ID_CAT5', 'K0_ID_CAT6', 'K0_ID_CAT7', 'K0_ID_CAT8'
	));
	
	$aStripExt = array_merge($aGenerica, $aCasa, $aMotori, $aLavoro); 
	$aStripList = array_merge($scheda->astrip, $aStripExt, $aGenericaList);
	$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));

	$my_vars = new ordinamento(array_merge($aAllOrd, array('usr' => 'USER', 'oc' => 'OFFROCERCO')));
	
	//$my_vars -> campi_force['text']['ARTICLE'] = 'articles'; # $my_vars -> campi_force['tipo_input']['CAMPO'] = 'nome_tabella';
	//$my_vars -> campi_force['text']['USER'] = 'users';
	$my_vars -> sort_default($scheda -> f_id);
	$my_vars -> tabella = $scheda -> table;
}
else{ # REDIRECT
	header(HEADER_TO.'articles_main.php?err='.rawurlencode('Scegliere una categoria'));
}

?>