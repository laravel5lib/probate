<?php

namespace App\Core;

use \App\Core\Services\ParseService as Parse;
class E_parseDeceasedName implements _Contract{

	
    #########################################################
    ################    PARSING FUNCTIONS    ################
    #########################################################
	public function parseLevel1(){
		#return Parse::parseKeyWord($this->text,'Name:', null, array(2,7));
		$lines  =  Parse::removeShortLines($this->text, 10);
		foreach($lines as $i => $line){
			#$line = preg_replace("/\s+/","",$line);
			if(stripos($line,"Name:") !== false){
				$e = explode(":",$line);
				return Parse::removeExcess($e[1]);
			}
		}
	}


	public function parseLevel2(){
		/*$p = new parseDeceasedName($this->result);
		$spaces = $p->countSpaces();
		if(*/
	}



    #########################################################
    ################    TESTING FUNCTIONS    ################
    #########################################################
	public function testLevel1($result){
		/*$p = new parseDeceasedName($this->result);
		$spaces = $p->countSpaces();
		if($space == 1){
			return 1;
		else{
			return -1;
		}*/
		return true;
	}
	

}
?>