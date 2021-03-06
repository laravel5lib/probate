<?php

namespace App\Core\Services;
use Illuminate\Support\Facades\Cache as Cache;
Class ParseService{

	public static $out;
	private static $zip;
	private static $name;


	# Break Out Lines
	public static function breakLines($text){

		$lines = explode("\n",$text);

		$result = array();
		foreach($lines as $i => $line){
			if(strlen($line) > 0){
				array_push($result,$line);
			}
		}

		return $result;
	}


	public static function implodeLines($lines=array()){

		$text = "";
		foreach($lines as $i => $line){
			$text.= "\n".$line;
		}

		return $text;
	}


	# ###################################################################################
	# Generic keyword search function
	#	text - block of text getting parsed
	#	keyword - keyword getting searched
	#	scope - length of string to be returned. If null, it will return the line
	#	qLine - the lines to be examined.  
	#			array(start,end) -- based on index so the first value is 0
	# ###################################################################################
	public static function parseKeyWord($text,$keyword,$scope=null,$qLine = array()){

		$lines = self::breakLines($text);

		if(count($lines) == 0){return false;}

		if(count($qLine)==0){
			$a = 0;
			$b = count($lines);
		}else{
			$a = $qLine[0];
			$b = $qLine[1];
		}

		for($i=$a;$i<$b;$i++){

			// Set Line
        	$line = trim($lines[$i]);

            // Remove Spaces
            $line = preg_replace('/\s+/','',$line);
            $keyword = preg_replace('/\s+/','',$keyword);

            // Get Position of Keyword
            $pos = stripos($line,$keyword);

            if($pos !== false){
                $pos = $pos + strlen($keyword);
                if(is_null($scope)){
                	return substr($line,$pos);
                }else{
                	return substr($line,$pos,$scope);
                }
            }
        }

        return false;
    }


    public static function indexArray($array){

    	$result = array();
    	foreach($array as $i =>$info){
    		array_push($result,$info);
    	}

    	return $result;
    }



	# ###################################################################################
    # Removes lines that are shorter than a certain length
    #
    # Text - the text from OCR
    # Length - if less than legth, remove line
	# ###################################################################################
    public static function removeShortLines($text, $length){

    	$lines = self::breakLines($text);

    	$temp  = $lines;
    	foreach($lines as $i => $line){
    		if(strlen($line) < $length){
    			unset($temp[$i]);
    		}
    	}

    	return $temp;
    }



	# ###################################################################################
    # Find the line in the text file where the prbate information is...
	# ###################################################################################
	public static function findProbateLine($lines){
		
		foreach($lines as $i => $line){
			
			$line = preg_replace('/\s+/','',$line);

			//Probate addres comes right after the line
			// that says 'TYPE Address:'
			if(stripos($line,'typeAddress:') !== false){
				return $i+1;
			}
		}

		return false;
	}

	public static function getProbateLine($lines){
		
		$index = self::findProbateLine($lines);
		
		if($index === false){	return false;
		}else{					return $lines[$index];}
	}


	# ###################################################################################
	# Checks to see if the string is propser case
	# ###################################################################################
	public static function checkProper($string){
		
	    $a = substr($string,0,1);
	    $b = substr($string,1,1);
	    
	    if(     ctype_alpha($string) !== false
	        &&  $a == strtoupper($a)
	        &&  $b == strtolower($b)){
	        
	        return true;        
	    }
	        
	        
		return false;

	}


	# ###################################################################################
	# Checks for an initial
	# ###################################################################################
	public static function checkInitial($string){
    
	    $a = substr($string,0,1);
	    $b = substr($string,1,1);

	    if($a == strtoupper($a) && $b == "."){
	        return true;
	    }else{
	        return false;
	    }
	}

	# ###################################################################################
	# Removes all word without a given case: UPPER, LOWER,or PROPER
	# ###################################################################################
	public static function removeCase($line, $case='PROPER'){

		// Break appart line
		$e = explode(' ',$line);

		$line = '';
		foreach($e as $i => $string){
			switch($case){

				case 'UPPER':

					if(strtoupper($string) == $string){ 
						$line.= $string.' ';
					}// test, if pass, add to line
					
					break;

				case 'LOWER':

					if(strtolower($string) == $string){
						$line.= $string.' ';
					}// test, if pass, add to line

					break;

				case 'PROPER':

					if(self::checkProper($string)){
						$line.= $string.' ';
					}

					break;
			}
		}

		if($line == ''){	return false;
		}else{				return $line;}
	}


	public static function findCaseIndex($line, $case='PROPER'){
		
		// Break appart line
		$e = explode(' ',$line);

		$line = '';
		foreach($e as $i => $string){
			switch($case){

				case 'UPPER':

					if(strtoupper($string) == $string){ 
						return $i;
					}// test, if pass, add to line

					break;

				case 'LOWER':

					if(strtolower($string) == $string){
						return $i;
					}// test, if pass, add to line

					break;

				case 'PROPER':

					if(self::checkProper($string)){
						return $i;
					}

					break;
			}
		}

		return false;

	}


	# ###################################################################################
	# Find the number
	# line - string to pase
	# worderNumbers - will check of spelled out numbers like 'One', 'Two', 'Three'... etc
	# ###################################################################################
	public static function findNumber($line, $wordedNumbers=false){

		$wordedNumbers = array('one','two','three','four','five','six','seven','eight','nine');

		$e = explode(' ',$line);

		foreach($e as $i => $a){
			if(		floatval($a)>0 // if word is numeric
				||  ( $wordedNumbers === true 
					&& in_array(strtolower($a),$wordedNumbers)) 
				){
				
					return $i;
			}
		}

		return false;
	}


	public static function checkNumber($string){

		$wordedNumbers = array('one','two','three','four','five','six','seven','eight','nine');

	    if(floatval($string) > 0 || in_array(strtolower($string),$wordedNumbers)){
	        return true;
	    }

		return false;
	}


	# ###################################################################################
	# Slices up the in line between spaces a and b.
	# line- string to be parsed
	# a- start
	# b- end
	# ###################################################################################
	public static function sliceLine($line,$a,$b=null){

		if(strlen(str_replace(" ","",$line)) == 0){return false;}
		if(!is_numeric($a)){return false;}

		$e = explode(' ',$line);

        if(is_null($b)){$b = count($e)-1;}
        if(!isset($e[$b])){return false;}

		$line = '';
		#$c = 0
		for($i=$a;$i<=$b;$i++){
			$line.=$e[$i].' ';
			#$c++;
		}

		return trim($line);
	}



	# ###################################################################################
	# Removes stuff from the line, such as periods, apostrophes, commas etc..
	# ###################################################################################
	public static function removeExcess($string){

		//Remove commas
		$string = trim(str_replace(",","",$string));

		//End the string at the last letter
		preg_match_all('/\p{L}/u', $string, $matches, PREG_OFFSET_CAPTURE);

		$lastLetter = end($matches[0]); // Last match
		$pos = $lastLetter[1]+1; // position of last letter

		return trim(substr($string,0,$pos));// return substring
	}


	# ###################################################################################
	# Compares two strings 
	# Returns a true if the similarity is greater than the set threshold
	# ###################################################################################
	public static function compareStrings($a,$b){

		$diff = levenshtein($a,$b);
		if(strlen($a)==0 || strlen($b)==0){return false;}
		$percentage = 1-($diff/strlen($a));

		if($percentage >= .6){	return true;
		}else{					return false;}

	}


	public static function sanitizeLines($array){

		$r = array();
		foreach($array as $key => $info){
			$r[$key] = preg_replace( "/\r|\n/", "", $info );
		}

		return $r;
	}


	# Converts an array to CSV
	public static function array_to_CSV($array, $filename){
		$keys	=	array_keys($array[1]);
		$f		=	fopen($filename.".csv" ,'w');
		fputcsv ($f	 , $keys);
		
		foreach($array as $i => $info){
			$info = preg_replace( "/\r|\n/", "", $info );
			fputcsv ($f	 , $info);
		}
		
		fclose($f);
	}

}