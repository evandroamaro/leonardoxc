<? 
/************************************************************************/
/* Leonardo: Gliding XC Server					                        */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://leonardo.thenet.gr                                            */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

showFlight($flightID);  

function showFlight($flightID) {
  global $Theme;
  global $module_name, $flightsAbsPath,$flightsWebPath, $takeoffRadious,$landingRadious;
  global $moduleRelPath;
  global $userID,$admin_users;
  global $enableOLCsubmission ,$scoringServerActive;
  global $gliderCatList;
  global $PREFS;
  global $CONF_show_DBG_XML;

  $flightID+=0;
  $flight=new flight();
  $flight->getFlightFromDB($flightID);
  if ( $flight->private && $flight->userID!=$userID && ! is_leo_admin($userID) ) {
		echo "<TD align=center>"._FLIGHT_IS_PRIVATE."</td>";
		return;
  }

  $flight->incViews();
  $location=formatLocation(getWaypointName($flight->takeoffID),$flight->takeoffVinicity,$takeoffRadious);
  $firstPoint=new gpsPoint($flight->FIRST_POINT,$flight->timezone);						
  $opString="";
  if ( $flight->userID==$userID || is_leo_admin($userID) )
		$opString="<a href='?name=$module_name&op=delete_flight&flightID=".$flightID."'><img src='".$moduleRelPath."/img/x_icon.gif' border=0 align=bottom></a>
				   <a href='?name=$module_name&op=edit_flight&flightID=".$flightID."'><img src='".$moduleRelPath."/img/change_icon.png' border=0 align=bottom></a>"; 


 
  ?>
  <script type="text/javascript" src="<?=$moduleRelPath ?>/tipster.js"></script>
<? echo makePilotPopup(); ?>
<? echo maketakeoffPopup(1,$userID); ?>
  <script language="javascript">
  
  function submitForm(extendedInfo) {
	  var flightID= document.geOptionsForm.flightID.value;
	  var lineWidth= document.geOptionsForm.lineWidth.value;
	  var lineColor= document.geOptionsForm.lineColor.value;
	  var ex= document.geOptionsForm.ex.value;
	
  
//  lineWidth=1;
//  lineColor="ff0000";
//  ex=2;
  
	window.location = "<?=$moduleRelPath?>/download.php?type=kml_trk&flightID="+flightID+"&w="+lineWidth+"&c="+lineColor+"&ex="+ex+"&an="+extendedInfo;
	return false;
}

function setSelectColor(theDiv) {
	
	oColour="#"+theDiv.options[theDiv.selectedIndex].value;
	//oColour="#00ff00";
	if( theDiv.style ) { theDiv = theDiv.style; } if( typeof( theDiv.bgColor ) != 'undefined' ) {
		theDiv.bgColor = oColour; } else if( theDiv.backgroundColor ) { theDiv.backgroundColor = oColour; }
	else { theDiv.background = oColour; }

}

var unknownTakeoffTip = new TipObj('unknownTakeoffTip');
with (unknownTakeoffTip)
{
  template = '<table bgcolor="#000000" cellpadding="0" cellspacing="0" width="%3%" border="0">' +
  '<tr><td class="infoBoxHeader" style="width:%3%px">%4%</td></tr>'+
  '<tr><td class="infoBox" style="width:%3%px">%5%</td></tr></table>';

// tipStick = 1;
 showDelay = 0;
 hideDelay = 2;
 doFades = false;
 tips.floating = new Array(220, 5, "attentionLinkPos", 350, 'This flight has an uknown Takeoff','If you do know from which takeoff/launch this flight began please click to fill it in !');

 tipStick = 0;
}

</script>
<div id="unknownTakeoffTipLayer" class="shadowBox" style="position: absolute; z-index: 10000; visibility: hidden; left: 0px; top: 0px; width: 10px">&nbsp;</div>


<? if ( is_leo_admin($userID) ) { ?>
<script language="javascript">
function add_takeoff(lat,lon,id) {	 
	takeoffTip.hide();
	document.getElementById('takeoffBoxTitle').innerHTML = "Register Takeoff";	
	document.getElementById('addTakeoffFrame').src='modules/<?=$module_name?>/GUI_EXT_waypoint_add.php?lat='+lat+'&lon='+lon+'&takeoffID='+id;
	toggleVisible('takeoffAddID','takeoffAddPos',14,-150,410,320);
}
	 
function edit_takeoff(id) {	 
	 takeoffTip.hide();
	 document.getElementById('takeoffBoxTitle').innerHTML = "Change Takeoff";		 
 	 document.getElementById('addTakeoffFrame').src='modules/<?=$module_name?>/GUI_EXT_waypoint_edit.php?waypointIDedit='+id;
	 toggleVisible('takeoffAddID','takeoffAddPos',14,-150,410,320);
 }
</script>
<? }  ?>
<script language="javascript">
	 function user_add_takeoff(lat,lon,id) {	 
		MWJ_changeContents('takeoffBoxTitle',"Register Takeoff");
		document.getElementById('addTakeoffFrame').src='modules/<?=$module_name?>/GUI_EXT_user_waypoint_add.php?lat='+lat+'&lon='+lon+'&takeoffID='+id;		
		toggleVisible('takeoffAddID','takeoffAddPos',30,-150,410,320);
	 }
</script>

<div id="takeoffAddID" class="dropDownBox">
<table width="100%" >
<tr><td class="infoBoxHeader" style="width:400px">
<div align="left" style="display:block; float:left; clear:left;" id="takeoffBoxTitle">Register Takeoff</div>
<div align="right" style="display:inline; float:right; clear:right;">
<a href='#' onclick="toggleVisible('takeoffAddID','takeoffAddPos',14,-20,0,0);return false;">
<img src='<? echo $moduleRelPath."/templates/".$PREFS->themeName ?>/img/exit.png' border=0></a></div>
</td></tr></table>
<div id='addTakeoffDiv'>
<iframe id="addTakeoffFrame" width=410 height=320 frameborder=0 style='border-width:0px'></iframe></div>
</div>

<style type="text/css">
<!--
.dropDownBox {
	display:block;
	position:absolute;

	top:0px;
	left: 0px;
	width:0px;
	height:0px;
	
	visibility:hidden;

	border-right-width: 2px; border-bottom-width: 2px; border-top-width: 1px; border-left-width: 1px;
	border-right-style: solid; border-bottom-style: solid; border-top-style: solid; border-left-style: solid;
	border-right-color: #999999; border-bottom-color: #999999; border-top-color: #E2E2E2; border-left-color: #E2E2E2;
	background-color:#EEEEEE;
	padding: 1px 1px 1px 1px;
	margin-bottom:0px;

}
-->
</style>

<form name="geOptionsForm" method="POST">
<div id="geOptionsID" class="dropDownBox">
<input type="hidden" name="flightID" value="<?=$flightID?>">

<table bgcolor="#55555" cellpadding="0" cellspacing="0" width="100%" border="0">
<tr>
<td  colspan=2 class="tableBox" style="background-color:#CC6666">
<div align="left" style="display:inline; float:left; clear:left;">&nbsp;<b>Google Earth</b></div>
	<div align="right" style="display:inline; float:right; clear:right;">
		<a href='#' onclick="toggleVisible('geOptionsID','geOptionsPos',14,-20,0,0);return false;">
		<img src='<? echo $moduleRelPath."/templates/".$PREFS->themeName ?>/img/exit.png' border=0></a></div>
</td>
</tr>
<tr>
	<td class="tableBox" align="right">
	Line Color
	</td>
	<td class="tableBox">
	 <select name="lineColor" style="background-color:#ff0000" onChange="setSelectColor(this)">
	<option value='FF0000' style='background-color: #FF0000'>&nbsp;&nbsp;&nbsp;</option>
	<option value='00FF00' style='background-color: #00FF00'>&nbsp;&nbsp;&nbsp;</option>
	<option value='0000FF' style='background-color: #0000FF'>&nbsp;&nbsp;&nbsp;</option>	
	<option value='FFD700' style='background-color: #FFD700'>&nbsp;&nbsp;&nbsp;</option>
	<option value='FF1493' style='background-color: #FF1493'>&nbsp;&nbsp;&nbsp;</option>
	<option value='FFFFFF' style='background-color: #FFFFFF'>&nbsp;&nbsp;&nbsp;</option>
	<option value='FF4500' style='background-color: #FF4500'>&nbsp;&nbsp;&nbsp;</option>
	<option value='8B0000' style='background-color: #8B0000'>&nbsp;&nbsp;&nbsp;</option>
	

	</select> 
	</td>
	
</tr>
<tr>
	<td class="tableBox" align="right">
		Line width
	</td>
	<td class="tableBox" align="left">
		<select  name="lineWidth">	
		<option value='1' >1</option>
		<option value='2' selected >2</option>
		<option value='3' >3</option>
		<option value='4' >4</option>
		<option value='5' >5</option>
		</select> 
	</td>
</tr>
<tr>
	<td class="tableBox"align="right">
		Exaggeration
	</td>
	<td class="tableBox" align="left">
	<select name="ex">	
	<option value='1' >1</option>
	<option value='2' >2</option>
	<option value='3' >3</option>
	</select> 
	</td>
</tr>
<tr>
	<td colspan=2 class="tableBox" align="center">
	<?
	echo "<a href='javascript:submitForm(0)'>Display on Google Earth</a><br>"; 
	?>
	</td>
</tr>
<tr>
	<td colspan=2 class="tableBox" align="center">
<?
	echo "<a href='javascript:submitForm(1)'>Use Man's Module</a><br>"; 
	//	echo "<a href='".$moduleRelPath."/download.php?type=kml_trk&flightID=".$flight->flightID."'>Display on Google Earth</a>"; 
	?>

	</td>
</tr>
</TABLE>


</div>
</form>

<?

  open_inner_table("<table class=main_text width=100% cellpadding=0 cellspacing=0><tr><td id='pilot_pos'>"._PILOT.": ".
	  "<a href=\"javascript:pilotTip.newTip('inline', 0, 13, 'pilot_pos', 200, '".$flight->userID."','".str_replace("'","\'",$flight->userName)."' )\"  onmouseout=\"pilotTip.hide()\">".$flight->userName."</a>".
	"&nbsp;&nbsp; "._DATE_SORT.": ".formatDate($flight->DATE)."</td><td align=right width=50><div align=right>".$opString."</div></td></tr></table>",740,$flight->cat);
?>

<?
  if (!$flight->active &&  (mktime() - datetime2UnixTimestamp($flight->dateAdded) > 5 ) )  {  //  5 secs
		$flight->activateFlight();
  } else if (!$flight->active) {
		open_tr();
		echo "<TD align=center>"._FLIGHT_WILL_BE_ACTIVATED_SOON."<a href=''>"._TRY_AGAIN."</a></td>";
  		close_tr(); 
		close_inner_table();  
		return;
  }

  if ($_REQUEST['updateMap']) $flight->getMapFromServer();		
  if ($_REQUEST['updateMap3d']) $flight->getMapFromServer(1);		
  if ($_REQUEST['updateCharts']) $flight->updateCharts(1);		
  if ($_REQUEST['updateData'])  {
	$flight->getFlightFromIGC( $flight->getIGCFilename() );
	$flight->updateTakeoffLanding();
	$flight->putFlightToDB(1); // 1== UPDATE
  }

  if ($_REQUEST['updateScore'] || $flight->FLIGHT_POINTS==0) { 
		$flight->getOLCscore();
		$flight->putFlightToDB(1); // 1== UPDATE
  }

  $flight->updateAll(0);
  
	$flightHours=$flight->DURATION/3600;
	if ($flightHours) {
		$openDistanceSpeed=formatSpeed($flight->LINEAR_DISTANCE/($flightHours*1000));
		$maxDistanceSpeed=formatSpeed($flight->MAX_LINEAR_DISTANCE/($flightHours*1000));
		$olcDistanceSpeed=formatSpeed($flight->FLIGHT_KM/($flightHours*1000));
	} else {
		$openDistanceSpeed=0;
		$maxDistanceSpeed=0;
		$olcDistanceSpeed=0;	
	}
	
  open_tr();
	 //  echo "<TD width=2>&nbsp</td>";
	   echo "<TD width=140 bgcolor=".$Theme->color2."><div align=".$Theme->table_cells_align.">"._TAKEOFF_LOCATION."</div></TD>";
   	  // echo "<TD width=200><div align=".$Theme->table_cells_align.">".$location."&nbsp;	   
		//<a href='?name=$module_name&op=show_waypoint&waypointIDview=".$flight->takeoffID."'><img src='".$moduleRelPath."/img/icon_magnify_small.gif' border=0></a>";
		echo "<TD width=200><div id='takeoffAddPos' align=".$Theme->table_cells_align."><a href=\"javascript:takeoffTip.newTip('inline',0,13, 'takeoffAddPos', 250, '".$flight->takeoffID."','".str_replace("'","\'",$location)."',".$firstPoint->lat.",".$firstPoint->lon.")\"  onmouseout=\"takeoffTip.hide()\">$location</a>";
		
		if ($flight->takeoffVinicity>$takeoffRadious*2 ) {
			echo "<div id='attentionLinkPos' class='attentionLink'><a href=\"javascript:user_add_takeoff(".$firstPoint->lat.",".$firstPoint->lon.",".$flight->takeoffID.")\" 
			 onmouseover='unknownTakeoffTip.show(\"floating\")'  onmouseout='unknownTakeoffTip.hide()'><img src='$moduleRelPath/img/icon_att3.gif' border=0 align=absmiddle>".Unknown_takeoff."<img src='$moduleRelPath/img/icon_att3.gif' border=0 align=absmiddle></a></div>";
		}
		echo "</div></TD>";
	 //  echo "<a href='".$moduleRelPath."/download.php?type=kml_wpt&wptID=".$flight->takeoffID."'><img src='".$moduleRelPath."/img/gearth_icon.png' border=0></a>";
  	 //  echo "</div></TD>";
	   echo "<TD width=6>&nbsp</td>";
	   echo "<TD width=180 bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._TAKEOFF_TIME."</div></TD>";
   	   echo "<TD width=120><div align=".$Theme->table_cells_align.">".sec2Time($flight->START_TIME)."</div></TD>";
   	//   echo "<TD width=2>&nbsp</td>";
  close_tr(); 
  open_tr();
	//   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color2."><div align=".$Theme->table_cells_align.">"._LANDING_LOCATION."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatLocation(getWaypointName($flight->landingID),$flight->landingVinicity,$landingRadious)."</div></TD>";
	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._LANDING_TIME."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".sec2Time($flight->END_TIME)."</div></TD>";
   	//   echo "<TD >&nbsp</td>";
  close_tr();
  open_tr();
  //	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._OPEN_DISTANCE."</div></TD>";
   	   echo "<TD ><div align=".$Theme->table_cells_align.">".formatDistanceOpen($flight->LINEAR_DISTANCE)." ($openDistanceSpeed)</div></TD>";
	   echo "<TD>&nbsp</td>";
	   echo "<TD  bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._DURATION."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".sec2Time($flight->DURATION)."</div></TD>";
//	   echo "<TD>&nbsp</td>";
  close_tr();
  if ( $scoringServerActive ) {
	  open_tr();
		//   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._MAX_DISTANCE."</div></TD>";
		   echo "<TD ><div align=".$Theme->table_cells_align.">".formatDistanceOpen($flight->MAX_LINEAR_DISTANCE)." ($maxDistanceSpeed)</div></TD>";
		   echo "<TD>&nbsp</td>";
		   echo "<TD  bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._OLC_SCORE_TYPE."</div></TD>";
		   echo "<TD><div align=".$Theme->table_cells_align.">".formatOLCScoreType($flight->BEST_FLIGHT_TYPE)."</div></TD>";
	//	   echo "<TD>&nbsp</td>";
	  close_tr();
	  open_tr();
		//   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._OLC_DISTANCE."</div></TD>";
		   echo "<TD ><div align=".$Theme->table_cells_align.">".formatDistanceOpen($flight->FLIGHT_KM)." ($olcDistanceSpeed)</div></TD>";
		   echo "<TD>&nbsp</td>";
		   echo "<TD  bgcolor=".$Theme->color5."><div align=".$Theme->table_cells_align.">"._OLC_SCORING."</div></TD>";
		   echo "<TD><div align=".$Theme->table_cells_align.">".formatOLCScore($flight->FLIGHT_POINTS)."</div></TD>";
		//   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  open_tr();
  	//   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=#B6F4A8><div align=".$Theme->table_cells_align.">"._MAX_SPEED."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatSpeed($flight->MAX_SPEED)."</div></TD>";
	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=#B6F4A8><div align=".$Theme->table_cells_align.">"._MAX_VARIO."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatVario($flight->MAX_VARIO)."</div></TD>";
 //  	   echo "<TD>&nbsp</td>";
  close_tr();
  open_tr();
  //	   echo "<TD>&nbsp</td>";
  	   echo "<TD bgcolor=#B6F4A8><div align=".$Theme->table_cells_align.">"._MEAN_SPEED."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatSpeed($flight->MEAN_SPEED)."</div></TD>";
	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=#B6F4A8><div align=".$Theme->table_cells_align.">"._MIN_VARIO."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatVario($flight->MIN_VARIO)."</div></TD>";
//	   echo "<TD>&nbsp</td>";	  
  close_tr();
  if ($flight->is3D()) {
    open_tr();
	//   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color3."><div align=".$Theme->table_cells_align.">"._MAX_ALTITUDE."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatAltitude($flight->MAX_ALT)."</div></TD>";
	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color3."><div align=".$Theme->table_cells_align.">"._TAKEOFF_ALTITUDE."</div></TD>";
   	   echo "<TD><div align=".$Theme->table_cells_align.">".formatAltitude($flight->TAKEOFF_ALT)."</div></TD>";
  	//   echo "<TD>&nbsp</td>";
	  close_tr();
	  open_tr();
		//   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color3."><div align=".$Theme->table_cells_align.">"._MIN_ALTITUDE."</div></TD>";
		   echo "<TD><div align=".$Theme->table_cells_align.">".formatAltitude($flight->MIN_ALT)."</div></TD>";
		   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color3."><div align=".$Theme->table_cells_align.">"._ALTITUDE_GAIN."</div></TD>";
		   echo "<TD><div align=".$Theme->table_cells_align.">".formatAltitude($flight->MAX_ALT-$flight->TAKEOFF_ALT)."</div></TD>";
		//   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  open_tr();
   	//  echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color2."><div align=".$Theme->table_cells_align.">"._FLIGHT_FILE."</div></TD>";
   	   echo "<TD colspan=4><div style='float:left'><a href='".$flight->getIGCRelPath()."'>".$flight->filename."</a></div>";
		echo "<div id='geOptionsPos' style='float:right'>";
		echo "<a href='javascript:nop()' onclick=\"toggleVisible('geOptionsID','geOptionsPos',14,-80,170,150);return false;\">Google Earth&nbsp;<img src='".$moduleRelPath."/img/icon_arrow_down.gif' border=0></a></div>";

		echo "</TD>";
	//   echo "<TD>&nbsp</td>";
  close_tr();
  if ( $flight->olcFilename  || ( $flight->insideOLCsubmitWindow() && $flight->FLIGHT_POINTS ) ) $showOLCsubmit=1;
  else  $showOLCsubmit=0;
  if ( $enableOLCsubmission && $showOLCsubmit ) {
	  open_tr();
		//   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color2."><div align=".$Theme->table_cells_align.">OLC</div></TD>";
		   echo "<TD  colspan=4><div align=left>";
			if ($flight->olcFilename) {
			  $olc_url="http://www2.onlinecontest.org/holc/".$flight->getOLCYear();
			  $olcName=strtolower (substr($flight->olcFilename,0,-4) );
			  echo "[ <a href='$olc_url/map/".$olcName.".jpg' target='_blank'>"._OLC_MAP."</a> ] ";
			  echo "[ <a href='$olc_url/ENL/".$olcName.".png' target='_blank'>"._OLC_BARO."</a> ] ";
			  echo "[".substr($flight->olcDateSubmited,0,10)."] ";
			  if ( in_array($userID,$admin_users)  || $flight->userID==$userID  ) echo "(Ref: ".$flight->olcRefNum.") ";
			  echo "<img src='".$moduleRelPath."/img/olc_icon_submited.gif' border=0 align=bottom>";
			  // echo _SUBMITED_SUCCESSFULLY_ON." ".$flight->olcDateSubmited;
			  if ($flight->insideOLCsubmitWindow()  && ( in_array($userID,$admin_users)  || $flight->userID==$userID  )  ) {
				echo "<a href='?name=".$module_name."&op=olc_remove&flightID=".$flight->flightID."'>";	
				echo "<img src='".$moduleRelPath."/img/x_icon.gif' border=0 align=bottom></a>";
			  }
			}
			else if ($flight->insideOLCsubmitWindow() && $flight->FLIGHT_POINTS ) {
				echo _READY_FOR_SUBMISSION;
				if ( in_array($userID,$admin_users)  || $flight->userID==$userID  ) 
				echo " <a href='?name=".$module_name."&op=olc_submit&flightID=".$flight->flightID."'>"._SUBMIT_TO_OLC."</a>";
			}
			else  echo _CANNOT_BE_SUBMITTED;
		   echo "</div></TD>";
	//	   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  if ($flight->comments) {
	  open_tr();
	//	   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._COMMENTS."</div></TD>";
		   echo "<TD colspan=4><div align=left>".$flight->comments."</div></TD>";
	//	   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  if ($flight->linkURL) {
	  open_tr();
		//   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._RELEVANT_PAGE."</div></TD>";
		   echo "<TD colspan=4><div align=left><a href='".formatURL($flight->linkURL)."' target=_blank>".$flight->linkURL."</a></div></TD>";
		//   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  if ($flight->glider) {
	  open_tr();
	//	   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._GLIDER."</div></TD>";
		   echo "<TD colspan=4><div align=left>".$flight->glider." [ <img src='".$moduleRelPath."/img/icon_cat_".$flight->cat.".png' align='absmiddle'> ".$gliderCatList[$flight->cat]."] </div></TD>";
	//	   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  if ($flight->photo1Filename) {
  open_tr();
   //	   echo "<TD>&nbsp</td>";
	   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._PHOTOS."</div></TD>";	  	   
   	   echo "<TD colspan=4><div align=left>";
    	  if ($flight->photo1Filename) 	echo "<a href='".$flight->getPhotoRelPath(1)."' target=_blank><img src='".$flight->getPhotoRelPath(1).".icon.jpg' border=0></a>";
    	  if ($flight->photo2Filename) 	echo "<a href='".$flight->getPhotoRelPath(2)."' target=_blank><img src='".$flight->getPhotoRelPath(2).".icon.jpg' border=0></a>";
    	  if ($flight->photo3Filename) 	echo "<a href='".$flight->getPhotoRelPath(3)."' target=_blank><img src='".$flight->getPhotoRelPath(3).".icon.jpg' border=0></a>";
	   echo "</div></TD>";
   //	   echo "<TD>&nbsp</td>";
  close_tr();
  }

//-------------------------------------------------------------------
// get from paraglidingearth.com
//-------------------------------------------------------------------


	$getXMLurl="http://www.paraglidingearth.com/takeoff_around.php?lng=".-$firstPoint->lon."&lat=".$firstPoint->lat."&distance=50&limit=5";
	$xmlSites1=getHTTPpage($getXMLurl);

if ($xmlSites1) {
	require_once $moduleRelPath.'/miniXML/minixml.inc.php';
	$xmlDoc = new MiniXMLDoc();
	$xmlDoc->fromString($xmlSites1);
	$xmlArray1 = $xmlDoc->toArray();
	$takeoffsNum=0;
	$takoffsList=array();
    // print_r($xmlArray1);
	if (is_array($xmlArray1['search'])) {
		if (is_array($xmlArray1['search']['takeoff'][0])) 
			$arrayToUse=$xmlArray1['search']['takeoff'];
		else
			$arrayToUse=$xmlArray1['search'];
	} else 
		$arrayToUse=0;

	if ($arrayToUse) {
		foreach ($arrayToUse as $flightareaNum=>$flightarea) {
			 if ( $flightareaNum!=="_num" && $flightarea['name']) {
					$distance=$flightarea['distance']; 
					if ($distance>50000) continue;
					if ($flightarea['area']!='not specified') $areaStr=" - ".$flightarea['area'];
					else $areaStr="";

					$takoffsList[$takeoffsNum]= "<a href='".$flightarea['pe_link']."' target=_blank>".$flightarea['name']."$areaStr (".$flightarea['countryCode'].") [~".formatDistance($distance,1)."]</a>";
					$takeoffsNum++;
					if ($takeoffsNum==5) break;
			}
		}
  }
  if ($takeoffsNum) {
  open_tr();
   	  // echo "<TD>&nbsp</td>";
	   echo "<TD valign=top bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">";;
	   echo "<a href='http://www.paraglidingearth.com/en-html/sites_around.php?lng=".-$firstPoint->lon."&lat=".$firstPoint->lat."&dist=20' target=_blank>";
	   echo "<img src='".$moduleRelPath."/img/paraglidingearth_logo.gif' border=0><br>"._FLYING_AREA_INFO;
	   echo "</a>";
	   echo "</div></TD>";	  	   
   	   echo "<TD colspan=4><div align=left>";

	   	echo "<table width=100% class=main_text><tr><td valign=top align=left>";
		echo "<ul>";
			foreach ($takoffsList as $takeoffLink) echo "<li>$takeoffLink";
		echo "</ul>";
  	    //	  echo "<a href='http://www.paragliding365.com/paragliding_sites_kml.html?longitude=".-$firstPoint->lon."&latitude=".$firstPoint->lat."&radius=50' target=_blank>Google Earth Flying area 50km radius from paragliding365.com</a><br>";
		echo "</td></tr></table>";

	   echo "</div></TD>";
   //	   echo "<TD>&nbsp</td>";
  close_tr();
  }

} // if we have content

  
//-------------------------------------------------------------------
// get from paragliding365.com
//-------------------------------------------------------------------


	$getXMLurl="http://www.paragliding365.com/paragliding_sites_xml.html?longitude=".-$firstPoint->lon."&latitude=".$firstPoint->lat."&radius=50&type=mini";
	$xmlSites=getHTTPpage($getXMLurl);

if ($xmlSites) {
	require_once $moduleRelPath.'/miniXML/minixml.inc.php';
	$xmlDoc = new MiniXMLDoc();
	$xmlDoc->fromString($xmlSites);
	$xmlArray = $xmlDoc->toArray();
	$takeoffsNum=0;
	$takoffsList=array();

	if ($xmlArray['root']['flightareas']['flightarea']) {
		if ( is_array($xmlArray['root']['flightareas']['flightarea'][0] ) )
			$arrayToUse=$xmlArray['root']['flightareas']['flightarea'];
		else
			$arrayToUse=$xmlArray['root']['flightareas'];
	} else $arrayToUse=0;

	if ($arrayToUse)
		foreach ($arrayToUse as $flightareaNum=>$flightarea) {
			 if ( $flightareaNum!=="_num") {
					$distance=$flightarea['distance']+0; 
					if ($distance>50000) continue;
					$takoffsList[$takeoffsNum]= "<a href='".$flightarea['link']."' target=_blank>".$flightarea['name']." - ".$flightarea['location']." (".$flightarea['iso'].") [~".formatDistance($distance,1)."]</a>";
					$takeoffsNum++;
					if ($takeoffsNum==5) break;
			}
		}

  if ($takeoffsNum) {
  open_tr();
   	  // echo "<TD>&nbsp</td>";
	   echo "<TD valign=top bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">";;
	   echo "<a href='http://www.paragliding365.com/paragliding_sites.html?longitude=".-$firstPoint->lon."&latitude=".$firstPoint->lat."&radius=50' target=_blank>";
	   echo "<img src='".$moduleRelPath."/img/paraglider365logo.gif' border=0><br>"._FLYING_AREA_INFO;
	   echo "</a>";
	   echo "</div></TD>";	  	   
   	   echo "<TD colspan=4><div align=left>";

	   	echo "<table width=100% class=main_text><tr><td valign=top align=left>";
		echo "<ul>";
			foreach ($takoffsList as $takeoffLink) echo "<li>$takeoffLink";
		echo "</ul>";
  	    //	  echo "<a href='http://www.paragliding365.com/paragliding_sites_kml.html?longitude=".-$firstPoint->lon."&latitude=".$firstPoint->lat."&radius=50' target=_blank>Google Earth Flying area 50km radius from paragliding365.com</a><br>";
		echo "</td></tr></table>";

	   echo "</div></TD>";
   //	   echo "<TD>&nbsp</td>";
  close_tr();
  }

} // if we have content



  if (in_array($userID,$admin_users) ) {
	  open_tr();
	//	   echo "<TD>&nbsp</td>";
		   echo "<TD bgcolor=".$Theme->color0."><div align=".$Theme->table_cells_align.">"._MORE_INFO."</div></TD>";	  	   
		   echo "<TD colspan=4 ><div align=left>";

		   echo "<b>TIMES VIEWED:</b> ".$flight->timesViewed."  ";
		   echo "<b>DATE ADDED:</b> ".$flight->dateAdded."<br>";
		  	// DEBUG MANOLIS
			// processIGC($flight->getIGCFilename());
			// display the trunpoints
			//echo "<hr> ";
			//for($k=1;$k<=5;$k++) { $vn="turnpoint$k"; echo " ".$flight->$vn." <BR>"; }
			if ($CONF_show_DBG_XML) {
				echo "<div id=xmlOutputShow style='display:inline'><a href='javascript:toggleVisibility(\"xmlOutput\")';>See XML from paragliding365.com</a></div>";
				echo "<div id=xmlOutput style='display:none'><hr>";
				echo "XML from paragliding365.com<br>";
				echo "<pre>$xmlSites1</pre><hr><pre>$xmlSites</pre></div>";
			}

		   echo "</div></TD>";
	//	   echo "<TD>&nbsp</td>";
	  close_tr();
  }
  open_tr();
  echo "<td colspan=5><center>";
	  if (in_array($userID,$admin_users) ) {
	  	echo "<a href='?name=".$module_name."&op=show_flight&flightID=".$flight->flightID."&updateData=1'>"._UPDATE_DATA."</a> | ";
	  	echo "<a href='?name=".$module_name."&op=show_flight&flightID=".$flight->flightID."&updateMap=1'>"._UPDATE_MAP."</a> | ";
	  	echo "<a href='?name=".$module_name."&op=show_flight&flightID=".$flight->flightID."&updateCharts=1'>"._UPDATE_GRAPHS."</a> | ";
		echo "<a href='?name=".$module_name."&op=show_flight&flightID=".$flight->flightID."&updateScore=1'>"._UPDATE_SCORE."</a> ";
	

		//echo "<a href='?name=".$module_name."&op=add_waypoint&lat=".$firstPoint->lat."&lon=".$firstPoint->lon."&takeoffID=".$flight->takeoffID."'>"._ADD_WAYPOINT."</a> <br> ";		

		@include dirname(__FILE__)."/site/admin_takeoff_info.php";
	  }
	  
	  if ( is_file($flight->getMapFilename() ) )
	  	echo "<br><img src='".$flight->getMapRelPath()."' border=0>";	
  	
      if ($flight->is3D() &&  is_file($flight->getChartfilename("alt",$PREFS->metricSystem))) 
      	echo "<br><br><img src='".$flight->getChartRelPath("alt",$PREFS->metricSystem)."'>";
	  if ( is_file($flight->getChartfilename("takeoff_distance",$PREFS->metricSystem)) )
	  	echo "<br><br><img src='".$flight->getChartRelPath("takeoff_distance",$PREFS->metricSystem)."'>";
	  if ( is_file($flight->getChartfilename("speed",$PREFS->metricSystem)) )
	  	echo "<br><br><img src='".$flight->getChartRelPath("speed",$PREFS->metricSystem)."'>";
	  if ($flight->is3D() &&  is_file($flight->getChartfilename("vario",$PREFS->metricSystem))) 
	  	echo "<br><br><img src='".$flight->getChartRelPath("vario",$PREFS->metricSystem)."'>";
  echo "</center></td>";
  close_tr();
	
  close_inner_table();   
}

?>