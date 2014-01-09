<?php
// 2014-01-09 V0.3 by Thomas Hoeser
define('DB_SERVER',"127.0.0.1");        // local server
define('DB_USER',"root");
define('DB_PASSWORD',"nxt2008");
define('DB_NAME',"avrio");
define('DB_TABLE',"avrdat");

define("listViewTempPeriod", 24); //Anzeige einer Liste aller Temperaturwerte der letzten X Stunden

// list sensors as defined in table avrdat
$SensorFields = array(Aussen, Wintergarten, Zimmer, Terrasse,Pool, WW_Speicher, Vorlauf, Ruecklauf);
$SensorNames  = $SensorFields;
$SensorCount  = count($SensorNames);
$SensorStyle  = array(
	array('line', 'solid', $SensorNames[0], 'D1', '#3366CC'),
	array('line', 'solid', $SensorNames[1], 'D2', '#DC3912'),
	array('line', 'solid', $SensorNames[2], 'D3', '#FF9900'),
	array('line', 'solid', $SensorNames[3], 'D1', '#109618'),
	array('line', 'solid', $SensorNames[4], 'D2', '#990099'),
	array('line', 'solid', $SensorNames[5], 'D3', '#0099C6'),
	array('line', 'solid', $SensorNames[6], 'D3', '#DD4477'),
	array('line', 'solid', $SensorNames[7], 'D4', '#66AA00')
	);

// old - mysql
// $conn = mysql_connect(DB_SERVER, DB_NAME, DB_PASSWORD) or die ('Error connecting to mysql');

if (!function_exists('mysqli_init') && !extension_loaded('mysqli'))
{ 	echo 'We don\'t have mysqli!!!';
 	fwrite($fh, "ERROR: function mysqli does NOT exist.\n");
}
else
{ 	fwrite($fh, "function mysqli does exist.\n");
}


// http://www.php.net/manual/en/mysqli.installation.php
// The MySQL Native Driver was included in PHP version 5.3.0.
// run phpinfo (INFO_ALL) and look for php.ini > Loaded Configuration File
// open php.ini and look for > extension_dir
// Windows: The mysqli extension is not enabled by default, so the php_mysqli.dll DLL must be enabled inside of php.ini. In order to do this you need to find the php.ini file (typically located in c:\php), and make sure you remove the comment (semi-colon) from the start of the line extension=php_mysqli.dll, in the section marked [PHP_MYSQLI].

// enable remote access by using mysql on server:
// mysql> GRANT ALL ON *.* to root@'192.168.178.30' IDENTIFIED BY 'nxt2008';
// mysql> FLUSH PRIVILEGES;

// Neues Datenbank-Objekt erzeugen
fwrite($fh, "connect to Database: ".DB_SERVER." - ".DB_USER." - ".DB_NAME." ... ");
$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD,DB_NAME);
// Pruefen ob die Datenbankverbindung hergestellt werden konnte
if (mysqli_connect_errno() == 0)
{
   fwrite($fh, "Database connected\n");
}
else
{
    // Es konnte keine Datenbankverbindung aufgebaut werden
    echo 'Die Datenbank konnte nicht erreicht werden. Folgender Fehler trat auf: <span class="hinweis">' .mysqli_connect_errno(). ' : ' .mysqli_connect_error(). '</span>';
	fwrite($fh, "ERROR: Connect to database FAILED\n");
}

?>