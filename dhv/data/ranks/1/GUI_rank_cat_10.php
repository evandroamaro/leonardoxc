<?
/************************************************************************/
/* Leonardo: Gliding XC Server					                                */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://sourceforge.net/projects/leonardoserver                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

//-----------------------------------------------------------------------
//-----------------------  custom league --------------------------------
//-----------------------------------------------------------------------

	// Martin Jursa  23.05.2007
	// Newcomer PG;
	$cat=1; // pg
	$where_clause='';

	$y=$year ? $year : date('Y');
	$where_clause.=" AND FirstOlcYear=$y ";

	require_once dirname(__FILE__)."/common_pre.php";

	$query = "SELECT $flightsTable.ID, userID, takeoffID ,
  				 gliderBrandID, $flightsTable.glider as glider, cat,
  				 FLIGHT_POINTS  , FLIGHT_KM, BEST_FLIGHT_TYPE
  		FROM $flightsTable,$pilotsTable
        WHERE (userID!=0 AND  private=0) AND $flightsTable.userID=$pilotsTable.pilotID $where_clause ";


require_once dirname(__FILE__)."/common.php";


?>