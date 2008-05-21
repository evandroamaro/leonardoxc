<?
/************************************************************************/
/* Leonardo: Gliding XC Server					                        */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://sourceforge.net/projects/leonardoserver                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


class flightScore {
	var $flight;
	var $scoresNum;
	var $scores;

	var $valuesArray;
	var $gotValues;
	var $flightTypes=array('FREE_FLIGHT'=>1,'FREE_TRIANGLE'=>2,'FAI_TRIANGLE'=>3 );
	var $flightTypesID=array(1=>'FREE_FLIGHT',2=>'FREE_TRIANGLE',3=>'FAI_TRIANGLE');
	
	function flightScore($flightID="") {
		if ($flightID) {
			$this->flightID=$flightID;
		}

	    //$this->valuesArray=array("flightID","path","name","description");

		$this->gotValues=0;
		$this->scoresNum=0;
		$this->scores=array();
	}



	function getScore($file,$useInternal) {
		global $OLCScoringServerUseInternal,$OLCScoringServerPath, $scoringServerActive , $OLCScoringServerPassword;
		global $baseInstallationPath,$CONF_allow_olc_files,$CONF;

		if ($useInternal) {
			$path=dirname( __FILE__ ).'/server';		
			$igcFilename=tempnam($path."/tmpFiles","IGC.");  //urlencode($basename)
			@unlink($igcFilename);
			
			$lines=file($file);
	
			$cont="";
			foreach($lines as $line) {
				$cont.=$line;
			}		
	
			if (!$handle = fopen($igcFilename, 'w')) exit; 
			if (!fwrite($handle, $cont))    exit; 
			fclose ($handle); 
	
			@chmod ($path."/olc", 0755);  
			if ($CONF['os']=='windows') $olcEXE='olc.exe';
			else $olcEXE='olc';
			
			$cmd=$path."/$olcEXE $igcFilename";
			DEBUG('OLC_SCORE',1,"cmd=$cmd");
			exec($cmd,$res);
			
			DEBUG('OLC_SCORE',1,"result has ".count($res)." lines<BR>");
			$contents=array();
			foreach($res as $line) {
					DEBUG('OLC_SCORE',1,$line.'<BR>');
					if (substr($line,0,4)=="OUT ") { 
						// echo substr($line,4)."\n";
						$contents[]=substr($line,4);
					}
			}
	
			@unlink($igcFilename);	
		}  else {
			$IGCwebPath=urlencode("http://".$_SERVER['SERVER_NAME'].$baseInstallationPath."/").$file; // score saned file
	
			$fl= $OLCScoringServerPath."?pass=".$OLCScoringServerPassword."&file=".$IGCwebPath;
			DEBUG("OLC_SCORE",1,"Will use URL: $fl<BR>");
			//$contents = file($fl); 
			$contents =	split("\n",fetchURL($fl,40));
			// if (!$contents) return;
		}				

		return $contents;
	
	}

	function parseScore($contents) {
		global $CONF;
/*
OUT TYPE FREE_FLIGHT
OUT FLIGHT_KM 59.695
OUT FLIGHT_POINTS 89.543
DEBUG Best free Flight: 59.695 km = 89.543 Points
OUT p0061 10:22:24 N45:18.426 E 5:53.275
OUT p0432 10:53:33 N45:16.075 E 5:50.499 5.664 km
OUT p1232 13:35:27 N45:28.025 E 5:55.341 23.026 km
OUT p1932 14:39:21 N45:18.086 E 6:2.222 20.482 km
OUT p2206 15:02:11 N45:18.088 E 5:54.149 10.523 km
OUT TYPE FREE_TRIANGLE
OUT FLIGHT_KM 58.118
OUT FLIGHT_POINTS 101.707
DEBUG Best free Triangle: 58.118 km = 101.707 Points
OUT p0090 10:25:04 N45:18.309 E 5:53.346
OUT p0432 10:53:33 N45:16.075 E 5:50.499 1.124 km=d
OUT p1232 13:35:27 N45:28.025 E 5:55.341 23.026 km=a
OUT p1932 14:39:21 N45:18.086 E 6:2.222 20.482 km=b
OUT p2206 15:02:11 N45:18.088 E 5:54.149 15.734 km=c
OUT TYPE FAI_TRIANGLE
OUT FLIGHT_KM 58.583
OUT FLIGHT_POINTS 117.166
bestes FAI Dreieck: 58.583 km = 117.166 Punkte
OUT p0090 10:25:04 N45:18.309 E 5:53.346
OUT p0432 10:53:33 N45:16.075 E 5:50.499 1.124 km=d
OUT p1233 13:35:32 N45:28.021 E 5:55.304 23.006 km=a
OUT p1836 14:31:21 N45:19.664 E 6:3.340 18.688 km=b
OUT p2206 15:02:11 N45:18.088 E 5:54.149 18.013 km=c
*/	
		$this->scoresNum=0;
		$this->scores=array();

		for ($i=1;$i<=2;$i++) {
			$this->scores[$i]['bestScore']=0;
			$this->scores[$i]['bestScoreType']=0;
		}

		$turnpointNum=1;
		foreach(  $contents  as $line) {	
			if (!$line) continue;		
			DEBUG("OLC_SCORE",1,"LINE: $line<BR>\n");
			$var_name  = strtok($line,' '); 
			$var_value = strtok(' '); 

			// the $this->scores array holds scores foreach method
			// method=1 OLD OLC
			// method=2 WXC
			if ($var_name=='TYPE') {
				$scoreType=$var_value ;
				for ($i=1;$i<=2;$i++) {
					$this->scores[$i][$scoreType]=array();					
				}
				$turnpointNum=1;
			}

			$bestScore=0;
			$bestType=0;

			if ($var_name{0}=='p' ) {
				// sample line 
				// p0181 12:45:43 N53:20.898 W 1:48.558 
				// p0181 12:45:43 N53:20.898 W12:48.558 
				
				// preg_match("/.+ .+ ([NS][ \d][^ ]+) ([WE][ \d][^ ]+)/i",$line,$line_parts);
				
				
				preg_match("/.+ ([ \d]+):([ \d]+):([ \d]+) +([NS])([ \d]+):([ \d]+)\.([ \d]+) ([EW])([ \d]+):([ \d]+)\.([ \d]+)/",$line,$matches);

				// $lat=preg_replace("/[NS](\d+):(\d+)\.(\d+)/","\\1\\2\\3",$pointString[0]);
				//	$lon=preg_replace("/[EW](\d+):(\d+)\.(\d+)/","\\1\\2\\3",$pointString[1]);

				$alt=0;
				$pointStringFaked=sprintf("B%02d%02d%02d%02d%02d%03d%1s%03d%02d%03d%1sA00000%05d",
							$matches[1],$matches[2],$matches[3],
							$matches[5]+0,$matches[6]+0,$matches[7]+0,$matches[4],
							$matches[9]+0,$matches[10]+0,$matches[11]+0,$matches[8] , $alt);
			
			/*
				$var_name="turnpoint".$turnpointNum;
				$lat= str_replace(" ","0",trim($line_parts[1]));
				$lon= str_replace(" ","0",trim($line_parts[2]));
				$var_value =$lat." ".$lon;
			*/
			
				$thisTP=new gpsPoint( $pointStringFaked , 0 );	
				$tpStr=$thisTP->to_IGC_Record();

				$this->scores[1][$scoreType]['tp'][$turnpointNum]=$tpStr;
				$this->scores[2][$scoreType]['tp'][$turnpointNum]=$tpStr;
				$turnpointNum++;

			} else if ($var_name=='FLIGHT_KM') {
				$distanceTmp=trim($var_value);
				for ($i=1;$i<=2;$i++) {
					$this->scores[$i][$scoreType]['distance']=$distanceTmp;	
					$this->scores[$i][$scoreType]['score']=$CONF['scoring']['sets'][$i]['types'][$scoreType] * $distanceTmp;
					if ( $this->scores[$i][$scoreType]['score'] > $this->scores[$i]['bestScore'] ) {
						$this->scores[$i]['bestScore']=$this->scores[$i][$scoreType]['score'] ;
						$this->scores[$i]['bestScoreType']=$scoreType;

					}
				}
			}

			if ($var_name=='MAX_LINEAR_DISTANCE') $this->MAX_LINEAR_DISTANCE=trim($var_value);
			
			DEBUG("OLC_SCORE",1,"#".$var_name."=".$var_value."#<br>\n");
		}

		echo "<pre>";
		print_r($this->scores);
		echo "</pre>";

	}


	function getFromDB() {
		global $db,$scoresTable,$flightsTable,$CONF ;
		$res= $db->sql_query("SELECT * FROM $scoresTable WHERE flightID=".$this->flightID ." ORDER BY ID ASC");
  		if($res <= 0){   
			 echo "Error getting scores from DB for flight".$this->flightID."<BR>";
		     return 0;
	    }
		
		//reset everything
		$this->scores=array();
					
	    while ($row = $db->sql_fetchrow($res) ) {
			$type=$this->flightTypesID[$row['type']];
			$this->scores[$row['method']][$type]=array();
			$this->scores[$row['method']][$type]['isBest']	=$row['isBest'];
			$this->scores[$row['method']][$type]['distance']=$row['distance'];
			$this->scores[$row['method']][$type]['score']	=$row['score'];
			
			if ($row['isBest']==1)  {
				$this->scores[$row['method']]['bestScoreType']=$type;
				$this->scores[$row['method']]['bestScore']=$row['score'];
				$this->scores[$row['method']]['bestDistance']=$row['distance'];
				
				if ($row['method']==$CONF['scoring']['default_set']) {
					$this->bestScoreType=$type;
					$this->bestScore=$row['score'];	
					$this->bestDistance=$row['distance'];
				}
			}					
					
			$this->scores[$row['method']][$type]['tp']=array();
			for($i=1;$i<=7;$i++) {
				$this->scores[$row['method']][$type]['tp'][$i]=$row['turnpoint'.$i];
			}
			
		}
		
		//	echo "<pre>";		
		//	print_r($this->scores);
		//	echo "</pre>";
			
		$this->gotValues=1;

		echo "<pre>";		
		//	print_r($this->scores);
		echo $this->toJSON();
		echo "</pre>";
		

		return 1;
    }

	function deleteFromDB() {
		global $db,$scoresTable ,$flightsTable;
		
		$res= $db->sql_query("DELETE FROM  $scoresTable  WHERE flightID=".$this->flightID );
  		if($res <= 0){   
			 echo "Error deleting scores for flight ".$this->flightID." <BR>";
	    }
	}

	function toJSON() {
		global $db,$scoresTable ,$flightsTable,$CONF;

		if (!$this->gotValues) $this->getFromDB();		

		$str='';
		$k=0;
		foreach ( $this->scores as $methodID=>$scoreForMethod) {
			$l=0;
			foreach($scoreForMethod as $scoreType=>$scoreDetails ) {
				if (!is_array($scoreDetails) ) continue;
				if ($scoreType==$scoreForMethod['bestScoreType']) $isBest=1; 
				else $isBest=0;
				if ($l!=0) $str.=",\n";
			$str.="\n\n".'{	"XCscoreMethod": "'.$methodID.'", '."\n".
					'	"XCtype": "'.($this->flightTypes[$scoreType]+0).'", '."\n".
					'	"isBest" :"'.$isBest.'", '."\n".
					'	"XCdistance" :"'.$scoreDetails['distance'].'", '."\n".
					'	"XCscore" :"'.$scoreDetails['score'].'", '."\n";			
										
					$tpNum=0;
					$tpStr='';
					for($i=1;$i<=7;$i++) {
						if ($scoreDetails['tp'][$i]) {
							$newPoint=new gpsPoint($scoreDetails['tp'][$i]);	
							if ($tpNum>0) $tpStr.=" ,\n		";
							$tpStr.=' {"id": '.$i.' , "lat": '.$newPoint->lat().', "lon": '.$newPoint->lon().' } ';
							$tpNum++;
						}	
					}
					$str.='	"turnpoints": [ '.$tpStr.' ] ';
					$str.="\n }";

					$l++;
			
			}
			$k++;
		}
		
		$str=' "score": { '.
	 '		"XCtype": "'.$this->bestScoreType.'", '."\n".
	 '		"XCdistance": "'.$this->bestDistance.'", '."\n".
	 '		"XCscore": "'.$this->bestScore.'", '."\n".
	 '		"XCscoreMethod": "'. $CONF['scoring']['default_set'].'", '."\n".
	 '		"scores": [ '.
		 $str." ] \n } \n";

		return $str;	
	}	

	function putToDB($updateScoringTable=1,$updateFlightsTable=1) {
		global $db,$scoresTable ,$flightsTable;

		// if (!$this->gotValues) $this->getFromDB();		
		if ($updateScoringTable) {
			$this->deleteFromDB();
			
			foreach ( $this->scores as $methodID=>$scoreForMethod) {
				foreach($scoreForMethod as $scoreType=>$scoreDetails ) {
					if (!is_array($scoreDetails) ) continue;
					if ($scoreType==$scoreForMethod['bestScoreType']) $isBest=1; 
					else $isBest=0;
	
					$query="INSERT INTO $scoresTable (flightID,method,type,isBest,distance,score,
													turnpoint1,turnpoint2,turnpoint3,turnpoint4,turnpoint5,turnpoint6,turnpoint7) 
										VALUES (".
						$this->flightID.",$methodID, ".($this->flightTypes[$scoreType]+0).", $isBest , ".
											 $scoreDetails['distance'].",".
											 $scoreDetails['score'].",'".
											 $scoreDetails['tp'][1]."','".
											 $scoreDetails['tp'][2]."','".
											 $scoreDetails['tp'][3]."','".
											 $scoreDetails['tp'][4]."','".
											 $scoreDetails['tp'][5]."','".
											 $scoreDetails['tp'][6]."','".
											 $scoreDetails['tp'][7]."' ) ";
				
					// echo $query;
					$res= $db->sql_query($query);
					if($res <= 0){
					  echo "Error putting score $i for flight ".$this->flightID." to DB: $query<BR>";
					  return 0;
					}		
				}
			}
		}		

		if ($updateFlightsTable) {
			global $CONF;
			$defaultMethodID= $CONF['scoring']['default_set'];
			$defaultScore=$this->scores[$defaultMethodID];

			$query="UPDATE $flightsTable SET 
							 MAX_LINEAR_DISTANCE=".$this->MAX_LINEAR_DISTANCE.
							 ", BEST_FLIGHT_TYPE='".$defaultScore['bestScoreType']."'".
							 ", FLIGHT_KM=".($defaultScore[ $defaultScore['bestScoreType'] ]['distance']*1000).
							 ", FLIGHT_POINTS=".$defaultScore['bestScore'].							
						" WHERE ID=".$this->flightID;
			// echo $query."<HR>";
			$res= $db->sql_query($query );
			if($res <= 0){   
				 echo "Error updating scoring details  for flight ".$this->flightID." : $query<BR>";
			}
		}	
		
		$this->gotValues=1;			
		return 1;
    }

}

?>