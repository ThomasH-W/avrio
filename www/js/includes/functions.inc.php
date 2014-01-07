<?php
/************************************************
	Letztes Zeichen hinter einem String entfernen	*
************************************************/
function delLastChar($string="")
{
	$t = substr($string, 0, -1);
	return($t);
}

/****************************************************************
	Temperaturwerte, Datum, Zeit und Stundenzahl der Speicherung	*
	dieser Werte für jeden Sensor ermitteln und in Array packen		*
****************************************************************/
function getChartValues($sensorCount, $scope='day', $scope_val=24)
{
	global $db;
    global $SensorNames;
	global $fh;

	fwrite($fh, "----------------------------------------------- getChartValues() ...\n");

	if  ($scope=='day' OR $scope=='hour')
	{
    	foreach($SensorNames AS $name)
		{ $dbFields .= $name.","; }
	}
	else
	{
    	foreach($SensorNames AS $name)
		{ $dbFields .= "AVG(".$name.") as ".$name.","; }
	}
	// echo "$dbFields".$dbFields;
	fwrite($fh, "scope           : ".$scope."\n");
	fwrite($fh, "scope_val       : ".$scope_val."\n");
	fwrite($fh, "sensorCount     : ".$sensorCount."\n");
	fwrite($fh, "Database Fields : ".$dbFields."\n");

	if($scope=='year')
    {
    	$sql = 'SELECT  '.$dbFields.'
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval '.$scope_val.' YEAR )
       		GROUP BY YEAR(dattim),MONTH(dattim)
			';
	}
	elseif($scope=='month')
    {
    	$sql = 'SELECT  '.$dbFields.'
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval '.$scope_val.' MONTH )
       		GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim)
			';
	}
	elseif($scope=='week')
    {
    	$sql = 'SELECT  '.$dbFields.'
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval '.$scope_val.' DAY )
       		GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim),HOUR(dattim)
			';
	}
	elseif($scope=='day')
    {
    	$sql = 'SELECT  '.$dbFields.'
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval '.$scope_val.' DAY )
       		ORDER BY dattim ASC
			';
	}	else  // day
    {
    	$sql = 'SELECT  '.$dbFields.'
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval '.$scope_val.' HOUR )
       		ORDER BY dattim ASC
			';
	}
    $sql_test = 'SELECT Aussen, `Zimmer` FROM avrdat LIMIT 40';
	// $sql = $sql_test;
    //echo $sql;
    fwrite($fh, "SQL: ".$sql."\n");

	$ergebnis = $db->query( $sql );
    // Anzahl gefunde Datensaetze ausgeben
    // echo "<p>Es wurden >" .$ergebnis->num_rows. "< Eintr&auml;ge gefunden.</p>";
	$dataCount = $ergebnis->num_rows;
	fwrite($fh, "Selected rows  : ".$dataCount."\n");

	// Ergebnisse ausgeben
	// $n_data = $ergebnis->num_rows;
    while ($zeile = $ergebnis->fetch_object())
    {
        // echo "Aussen: ".$zeile->Aussen. "   |   Innen:" .$zeile->Zimmer. "<br />";
		$stundenValues[0] .= $zeile->STUNDE.",";  	 // Einzelne Werte durch Komma trennen
		$stundenValues[1] .= $zeile->date.",";  	 // Einzelne Werte durch Komma trennen
		$stundenValues[2] .= $zeile->date."000".",";  	 // Einzelne Werte durch Komma trennen
		$stundenValues[3] .= $zeile->dattim.",";  	 // Einzelne Werte durch Komma trennen
		$i = 0;
		while($i < $sensorCount)
   		{
			$sensor_data[$i] .= $zeile->$SensorNames[$i].",";
   			$i++;
   		}

	}
	// Resourcen freigeben
    $ergebnis->close();

	$i = 0;
	while($i < $sensorCount)
   	{
			$sensor_data[$i] = delLastChar($sensor_data[$i]); 	 // Komma hinter dem letzten Temperaturwert entfernen
			fwrite($fh, "Row[$i]    : ".$sensor_data[$i]."\n");
   			$i++;
   	}
	//$stundenValues[0] = delLastChar($stundenValues[0]);  // Komma hinter letzter Stunde entfernen

	// echo "Aussen: ".$sensor_data[0];
	// echo "chartValues >".$sensor_data[0]."<"."<br>";
	//  echo "stundenValues >".$stundenValues[0]."<"."<br>";
    fwrite($fh, "Zeit[0]  : ".$stundenValues[0]."\n");
    fwrite($fh, "Zeit[1]  : ".$stundenValues[1]."\n");
    fwrite($fh, "Zeit[2]  : ".$stundenValues[2]."\n");
	$DattimStart = substr($stundenValues[2],0,13);
	fwrite($fh, "DattimStart  : ".$DattimStart."\n");
    fwrite($fh, "Zeit[3]  : ".$stundenValues[3]."\n");

	return array($sensor_data, $stundenValues, $DattimStart,$dataCount);
}
/*
day
SQL: SELECT  Aussen,Wintergarten,Zimmer,Terrasse,Pool,WW_Speicher,Vorlauf,Ruecklauf,
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 DAY )
       		ORDER BY dattim ASC
month
SQL: SELECT  AVG(Aussen) as Aussen,AVG(Wintergarten) as Wintergarten,AVG(Zimmer) as Zimmer,AVG(Terrasse) as Terrasse,AVG(Pool) as Pool,AVG(WW_Speicher) as WW_Speicher,AVG(Vorlauf) as Vorlauf,AVG(Ruecklauf) as Ruecklauf,
					UNIX_TIMESTAMP(dattim) AS date,
					DATE_FORMAT(dattim,"%H") AS STUNDE,
					dattim
			FROM avrdat
			WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 MONTH )
       		GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim)
*/
?>


