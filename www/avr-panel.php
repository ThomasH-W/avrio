<html>
<!- 
// Read MySQL database and display values in a panel
// 2013-03-27 V0.4 by Thomas Hoeser 
-->
<head>
<!- update the page every 120 seconds -->
<meta http-equiv="refresh" content="120" /> 
<!- load font -->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>
<style type="text/css">
*{
font-family: 'Open Sans', sans-serif;
font-weight: light; 
}
 .boxes { 
    background-color:#40B3DF; 
    padding:6px; margin:0px; 
    font-weight: 100; 
    }
 .greenbox { 
    color:#FFFFFF; background-color:#799458; padding:6px; margin:0px; 
    }
 .darkbluebox { 
    color:#FFFFFF; background-color:#7EB0B9; padding:6px; margin:0px; 
    }    
 .temp{
       font-size:26pt;
       font-weight: 100; 
 }
 .degc{
       font-size:18pt;
       font-weight: 400; 
 }
 .anfrage{
          font-size:16pt;
          width:150px;
          float:left;
          text-align:center;
 }
</style>
<head>
<body>
<?php

$dbhost = "127.0.0.1";
$dbuser = "root";
$dbpass = "rootpassword";
$dbname = "avrio";

$sensorname[0]="Datum";
$sensorname[1]="Aussen";
$sensorname[2]="Wintergarten";
$sensorname[3]="Zimmer";
$sensorname[4]="Terrasse";
$sensorname[5]="Pool";
$sensorname[6]="WW_Speicher";
$sensorname[7]="Vorlauf";
$sensorname[8]="Rücklauf";

// debug
$myFile = "monitor.log";
$fh = fopen($myFile, 'w');

fwrite($fh, "mysql_connect - ");
// Mit mysql_connect() öffnet man eine Verbindung zu einer MySQL-Datenbank. 
// Im Erfolgsfall gibt diese Funktion eine Verbindungskennung, sonst false zurück
$mysql_ret = mysql_connect($dbhost, $dbuser, $dbpass);
if (!$mysql_ret) {
	echo "<p>Error - cannot connect to sql server</p>";
	echo "<p>a) test mysql -u".$dbuser." -p".$dbpass." -h ".$dbhost."</p>";
	echo "<p>b) comment bind-address in /etc/mysql/my.cnf on server</p>";
	echo "<p>c) GRANT ALL ON avrio.* TO root@'CLIENT_IP_ADDRESS' IDENTIFIED BY '".$dbpass."'</p>";
	die ('Keine Verbindung zur Datenbank : ' . mysql_error());
} // mysql_connect
fwrite($fh, "SQL server ".$dbhost." o.k.\n");

fwrite($fh, "mysql_select_db - ");
// Mit mysql_select_db() wählt man eine Datenbank aus. 
// Im Erfolgsfall gibt diese Funktion true, sonst false zurück.
$mysql_ret = mysql_select_db($dbname);
if (!$mysql_ret) {
	echo "<p>Error - cannot open database ".$dbname."</p>";
	echo "<p>a) test mysql> SHOW DATABASES;</p>";
	die ('Keine Verbindung zur Datenbank : ' . mysql_error());
} // mysql_select_db

// SQL Abfrage
// 1 Datensatz holen
$query = "SELECT UNIX_TIMESTAMP(dattim),Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf FROM avrdat ORDER BY dattim DESC LIMIT 1";

$mysql_ret = mysql_query($query);
if (!$mysql_ret) {
	echo "<p>Error - invalid query on database ".$dbname."</p>";
	echo "<p>a) test mysql> USE ".$dbname.";DESC avrdat;</p>";
	die('Ungültige Anfrage: ' . mysql_error());
} 
fwrite($fh, $mysql_ret); fwrite($fh, "\n");

$array = mysql_fetch_array($mysql_ret);  

// get date
fwrite($fh, $array[0]); fwrite($fh, "\n");
$dbDate = Date(' d.M /  H:i ',$array[0]);
fwrite($fh, $dbDate); fwrite($fh, "\n");

echo '<div  class="anfrage"><a href="../index.php">HOME</a></div>';
echo '<div  class="anfrage"><a href="avr-google.php?group=air">LUFT</a></div>';
echo '<div  class="anfrage"><a href="avr-google.php?group=water">WASSER</a></div>';

echo "<h2><span style='font-weight:100'>Temperatur am ".$dbDate." </span></h2>";

// start table
echo '<table class=\"boxes\" width="100%">';

// start 1st row
echo "<tr>";
for($i=1;$i<=4;$i++){
  $array[$i]=round($array[$i],1);
	$xxx = "  <td class='greenbox'>
               <p>".$sensorname[$i]."</p>
               <h3><span class='temp'>".$array[$i]."</span><span class='degc'> &deg;C</span></h3>
            </td>";
	echo $xxx;
	fwrite($fh, $xxx); fwrite($fh, "\n");
}
echo "</tr>";

// start 2nd row
echo "<tr>";
for($i=5;$i<=8;$i++){
  $array[$i]=round($array[$i],1);
	$xxx = "  <td class='darkbluebox'>
               <p>".$sensorname[$i]."</p>
               <h3><span class='temp'>".$array[$i]."</span><span class='degc'> &deg;C</span></h3>
            </td>";
	echo $xxx;
	fwrite($fh, $xxx); fwrite($fh, "\n");
}
echo "</tr>";
echo "</table>";

fclose($fh);
?>
</body>
</html>
