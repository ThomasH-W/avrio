<?php
// Read MySQL database and build diagram based on google graphics
// 2013-12-28 V0.7a by Thomas Hoeser
//
// syntax: avr-google.php[?scope={day|week|month|year}]&[?group={air|water}]
// avr-google.php?scope=day
// avr-google.php?scope=day&?group=air
//
// you want to create a symbolic link to the config file
// > cd / var/wwww/avrweb
// > sudo ln -s  /home/pi/avrio/avrio-config.txt

// If you have changed the database fields, you need to change the names in the
// function drawData() and the php section below of the function.

// debug
$myFile = "avr-google.log";
$fh = fopen($myFile, 'w');
fwrite($fh, "----------------------------------------------- ");fwrite($fh, "\n"); 

// default Values used when config file cannot be found
$dbhost  = "127.0.0.1";
$dbuser  = "root";
$dbpass  = "rootpassword";
$dbname  = "avrio";
$dbtable = "avrdat";
$dbfield = "Innen, Aussen, Zimmer, Balkon, Wasser, WW_Speicher, Vorlauf, Ruecklauf";
$dbf_avg = "AVG(Innen), AVG(Aussen), AVG(Zimmer), AVG(Balkon), AVG(Wasser), AVG(WW_Speicher), AVG(Vorlauf), AVG(Ruecklauf)";
$sql6    = "Interval 24 HOUR";

$sql1 = "SELECT ";
$sql2 = $dbf_avg;
$sql3 = " , UNIX_TIMESTAMP(dattim) AS date FROM ";
$sql4 = $dbtable;
$sql5 = " WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), ";
$sql7 = " ) ORDER BY dattim";

// TAG   - SELECT Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf , UNIX_TIMESTAMP(dattim) AS date
//         FROM avrdat 
//         WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR )
//         ORDER BY dattim
// WOCHE - SELECT Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 7 DAY ) ORDER BY dattim
// MONAT - SELECT Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 1 MONTH ) ORDER BY dattim
// JAHR

// Select Ort, avg(Gewinn) from Tabelle group by Ort

$group_air   = "Aussen,Wintergarten,Zimmer,Terrasse";
$group_water = "WW_Speicher,Vorlauf,Ruecklauf";
$group1      = 1;
$group2      = 1;

// read php-script options provided when calling this script
fwrite($fh, "read options      - ");
// Handle command line arguments
$time_scope = $_GET ['scope'];
fwrite($fh, "scope - ".$time_scope."\n");
if (''   == $time_scope)
							{ $time_scope = 'day'; }
fwrite($fh, "----------------------------------------------- ");fwrite($fh, "\n"); 


// read config file
$configFile = "avrio-config.txt";
fwrite($fh, $configFile."\n");

$init = 0;
$lines = file($configFile);
foreach ($lines as $line_num ) //=> $line)
{
  // fwrite($fh,"read line: ");
  $zeile    = explode (' ', $line_num);
  $token    = $zeile[0];
  $val1     = rtrim($zeile[1]);  // rtrim will surpress CR at line end
  // fwrite($fh,">".$token."< >".$val1."<\n");
  
  switch ($token) {
   case 'Host'        : $dbhost      = $val1; $dbfield ='';break;
   case 'User'        : $dbuser      = $val1; break;
   case 'Password'    : $dbpass      = $val1; break;
   case 'Database'    : $dbname      = $val1; break;
   case 'Table'       : $dbtable     = $val1; break;
   case 'group_air'   : $group_air   = $val1; break;
   case 'group_water' : $group_water = $val1; break;
   case 'dbfield' :
   					// fwrite($fh, "dbfield - ".$val1."\n");
   					if($init == 0) {
						if ('day'   == $time_scope)
							{ $dbfield = $val1; $init = 1; }
						else
							{ $dbfield = 'AVG('.$val1.') as '.$val1.' '; $init = 1;}
						}
                    else  {
						if ('day'   == $time_scope)
							{ $dbfield = $dbfield.', '.$val1; }
						else
							{ $dbfield = $dbfield.', AVG('.$val1.') as '.$val1.' ';}
						}
                    break;
  }
}

// Problem: sql statement needs AVG function.
fwrite($fh, 'dbfield >'.$dbfield."<\n");
if($init == 1) {
  fwrite($fh, "using config file\n");
  $sql2 = $dbfield;
  }

fwrite($fh, "database options: ");
fwrite($fh, $dbhost.' / '.$dbuser.' / '.$dbpass.' / '.$dbname);
fwrite($fh, "\n\n");

if ('day'   == $time_scope) { $sql6 = "Interval 24 HOUR"; }
if ('week'  == $time_scope) { $sql6 = "Interval 7 DAY";
							  $sql7 = ') GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim),HOUR(dattim);';
							  }
if ('month' == $time_scope) { $sql6 = "Interval 1 MONTH";
							  $sql7 = ') GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim)';
								}
if ('year'  == $time_scope) { $sql6 = "Interval 1 YEAR";
							  $sql7 = ') GROUP BY YEAR(dattim),MONTH(dattim)';
								}

$sql = $sql1.$sql2.$sql3.$sql4.$sql5.$sql6.$sql7;

$sensor_group = $_GET ['group'];
fwrite($fh, "group - ");fwrite($fh,$sensor_group);fwrite($fh,"\n");
if ('air'   == $sensor_group)  { $group = $group_air; $group2=0;}
if ('water' == $sensor_group)  { $group = $group_water; $group1=0;}

fwrite($fh, "mysql_connect     - ");
// Mit mysql_connect() öffnet man eine Verbindung zu einer MySQL-Datenbank. 
// Im Erfolgsfall gibt diese Funktion eine Verbindungskennung, sonst false zurück
mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
fwrite($fh, "SQL server o.k.\n");

fwrite($fh, "mysql_select_db   - ");
// Mit mysql_select_db() wählt man eine Datenbank aus. 
// Im Erfolgsfall gibt diese Funktion true, sonst false zurück.
mysql_select_db($dbname)or die ('Error connecting to database');
fwrite($fh, "database o.k.\n");

fwrite($fh, "mysql_query       - ");
fwrite($fh, $sql); fwrite($fh, "\n");
// Mit mysql_query() sendet man eine SQL-Anfrage (Anfrage) an einen Datenbankserver.
// Die Funktion mysql_query() liefert im Erfolgsfall true, sonst false zurück. 
$sql = mysql_query($sql) or die ('Error selecting data');
fwrite($fh, "mysql_query o.k.\n");

fwrite($fh, "mysql_num_rows - ");
$rownum = mysql_num_rows($sql);
fwrite($fh, $rownum); fwrite($fh, "\n");

fwrite($fh, "end of php - starting html\n\n");

// close - otherwise buffer will be lost if script crashes
fclose($myFile);

$int_y_pos = -1;
$int_y_step_small = 1;
?>
<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    
      // Load the Visualization API and the piechart package.
      google.load("visualization", "1", {packages:["corechart"]});
      
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawData);
      
      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawData() {
      
        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Datum');
        data.addColumn('number', 'Aussen');
        data.addColumn('number', 'Terrasse');
        data.addColumn('number', 'Wintergarten');
		data.addColumn('number', 'Zimmer');
		data.addColumn('number', 'Pool');
        data.addColumn('number', 'Vorlauf');
        data.addColumn('number', 'Ruecklauf');
        data.addColumn('number', 'WW_Speicher');

    
<?php
    echo "        data.addRows($rownum);\n";

    $fh = fopen($myFile, 'a');fwrite($fh, "\n");

    while($row = mysql_fetch_assoc($sql)) {

        $int_y_pos += $int_y_step_small;

        echo "        data.setValue(" . $int_y_pos . ", 0, new Date(" . $row['date']*1000 . "));\n";

        // better: check content of group
        fwrite($fh, ' group1:'.$group1.' - ');
        fwrite($fh, ' group2:'.$group2.' - ');
        if (1 == $group1)  {
          echo "        data.setValue(" . $int_y_pos . ", 1," . $row['Aussen'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 2," . $row['Terrasse'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 3," . $row['Wintergarten'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 4," . $row['Zimmer'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 5," . $row['Pool'] . ");\n";
        }	
        if (1 == $group2)  {
          echo "        data.setValue(" . $int_y_pos . ", 6," . $row['Vorlauf'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 7," . $row['Ruecklauf'] . ");\n";
          echo "        data.setValue(" . $int_y_pos . ", 8," . $row['WW_Speicher'] . ");\n";	
        }	
        
        fwrite($fh, $row['date'] ); fwrite($fh, " - ");
        fwrite($fh, $row['Innen']);fwrite($fh, " - ");
        fwrite($fh, $row['Vorlauf']);fwrite($fh, " - ");
        fwrite($fh, $row['Ruecklauf']);fwrite($fh, " - ");
        fwrite($fh, "\n");

    }
?>
		  // Create and draw the visualization.
      // https://developers.google.com/chart/interactive/docs/gallery/areachart
		  var ac = new google.visualization.AreaChart(document.getElementById('visualization'));
		  ac.draw(data, {
			title : '1Wire Temperatursensoren',
			isStacked: false,
			width: 1400,
			height: 800,
			vAxis: {title: "Temperatur (Grad Celsius)",
              minorGridlines: {count: 3},
              // maxValue: 40,
              minValue: 0
              },
			hAxis: {title: "Zeit",
              minorGridlines: {count: 3},  
              format: "H:mm"
              },
      legend:{position: "bottom"},
      areaOpacity: 0
		  });

      }
    </script>
  </head>
<!-- <h2>Raspberry PI</h2> -->
<p>
<!--
Insert the required hyperlinks below:
New window can be forced by adding target="_blank"
-->
<form action="input_radio.htm">
  <p>Temperaturen:
    <input type="radio" name="group" value="air"> Luft
    <input type="radio" name="group" value="water"> Wasser 
    <input type="radio" name="group" value="all"> Alle<br>
  </p>
</form>

 </p>  
 <!--<body style="font-family: Arial;border: 0 none;">-->
    <!--Div that will hold the chart-->
    <div id="visualization" style="width: 600px; height: 600px;"></div>
    
     <div class="spanne">
 <style>
 .spanne{
         width:1000;
         margin:auto;
         height:20px;
         margin-top:-620px;
         z-index:2;
 }
 .anfrage{
          width:120px;
          float:left;
          text-align:center;
 }
 #visualization{
                margin-top:50px;
                z-index:1;
 }
 </style>
 
 <div  class="anfrage"><a href="../index.php">HOME</a></div>
 <div  class="anfrage"><a href="avr-panel.php">PANEL</a></div>
 <div  class="anfrage"><a href="avr-google.php?group=air">LUFT</a></div>
 <div  class="anfrage"><a href="avr-google.php?group=water">HEIZUNG</a></div>
 <div  class="anfrage"><a href="avr-google.php?scope=day">TAG</a></div>
 <div  class="anfrage"><a href="avr-google.php?scope=week">WOCHE</a></div>
 <div  class="anfrage"><a href="avr-google.php?scope=month">MONAT</a></div>
 <div  class="anfrage"><a href="avr-google.php?scope=year">JAHR</a></div>
 </div>
</body>
</html>