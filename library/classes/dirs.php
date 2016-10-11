<?php
# V.0.1.8
class dirs{

public function __construct($path){
	$this->folder = stringa::namedir(stringa::rightfromlast($path, '/'));
	$this->path = stringa::leftfromlast($path,'/').'/';
	$this->fullpath = $this->path.$this->folder;
	$this->check_dir();
	$this->mk_upld_list = array('atc','m_web','m_big','thu','web','big','sqr');
	$this->mk_upld();
}

function check_dir(){
	if(!@opendir($this->fullpath)){
		mkdir($this->fullpath, 0777);
	}
	//print 'FULLPATH '.$this -> fullpath.BR;
}

function mk_upld(){
	foreach($this->mk_upld_list as $new_folder){
		$dirname = $this->fullpath.'/'.$new_folder;
		if(!@opendir($dirname)) mkdir($dirname, 0777);
		print 'DIRNAME '.$dirname.BR;
	}
}




}
?>