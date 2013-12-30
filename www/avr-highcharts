<?php
// Read MySQL database and build diagram based on highcharts graphics
// 2013-12-30 V0.1 by Thomas Hoeser
//
// Requirements:
// /var/www/js/highcharts.js
// /var/www/js/jquery.min.js
// /var/www/js/modules/exporting.js
//
// debug
$myFile = "avr-highchart.log";
$fh = fopen($myFile, 'w');
fwrite($fh, "----------------------------------------------- ");fwrite($fh, "\n"); 

// default Values used when config file cannot be found
$dbhost  = "127.0.0.1";
$dbuser  = "root";
$dbpass  = "rootpassword";
$dbname  = "avrio";
$dbtable = "avrdat";
$dbfield = "Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf ";
$dbf_avg = "AVG(Innen), AVG(Aussen), AVG(Zimmer), AVG(Balkon), AVG(Wasser), AVG(WW_Speicher), AVG(Vorlauf), AVG(Ruecklauf)";
$sql6    = "Interval 24 HOUR";

$sql1 = 'SELECT ';
$sql2 = $dbfield;
$sql3 = ' , UNIX_TIMESTAMP(dattim) AS date FROM ';
$sql4 = $dbtable;
$sql5 = ' WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), ';
$sql7 = " ) ORDER BY dattim";

$sql = $sql1.$sql2.$sql3.$sql4.$sql5.$sql6.$sql7;
// mysql_query       SELECT Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf  , UNIX_TIMESTAMP(dattim) AS date 
//                   FROM avrdat 
//                   WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR ) 
//                   ORDER BY dattim

fwrite($fh, "database options: ");
fwrite($fh, $dbhost.' / '.$dbuser.' / '.$dbpass.' / '.$dbname);
fwrite($fh, "\n\n");

fwrite($fh, "mysql_connect     - ");
// Mit mysql_connect() öffnet man eine Verbindung zu einer MySQL-Datenbank. 
// Im Erfolgsfall gibt diese Funktion eine Verbindungskennung, sonst false zurück
mysql_connect($dbhost, $dbuser, $dbpass) or die ('MYSQL - Error connecting to mysql');
fwrite($fh, "SQL server o.k.\n");

fwrite($fh, "mysql_select_db   - ");
// Mit mysql_select_db() wählt man eine Datenbank aus. 
// Im Erfolgsfall gibt diese Funktion true, sonst false zurück.
mysql_select_db($dbname)or die ('MYSQL - Error connecting to database');
fwrite($fh, "database o.k.\n");

fwrite($fh, "mysql_query       - ");
fwrite($fh, $sql); fwrite($fh, "\n");
// Mit mysql_query() sendet man eine SQL-Anfrage (Anfrage) an einen Datenbankserver.
// Die Funktion mysql_query() liefert im Erfolgsfall true, sonst false zurück. 
$result = mysql_query($sql) or die ('MYSQL - Error selecting data ');
fwrite($fh, "mysql_query o.k.\n");

fwrite($fh, "mysql_num_rows - ");
$rownum = mysql_num_rows($sql);
fwrite($fh, $rownum); fwrite($fh, "\n");

fwrite($fh, "mysql_fetch_array - >");
while ($row = mysql_fetch_array($result)) 
            {
            $data1[] = $row['Aussen'];
            $data2[] = $row['Wintergarten'];
            $data3[] = $row['Zimmer'];
            $data4[] = $row['Terrasse'];
            $data5[] = $row['Pool'];
            $data6[] = $row['WW_Speicher'];
            $data7[] = $row['Vorlauf'];
            $data8[] = $row['Ruecklauf'];
            // echo $row['date'] . ' / ';
            // echo $row['Aussen'] . '<br>';
            // fwrite($fh, $row['Aussen']); fwrite($fh, "\n");
           }

fwrite($fh, $data3[0]); fwrite($fh, " / ");
fwrite($fh, $data3[1]); fwrite($fh, "\n");
fwrite($fh, '<' ); fwrite($fh, "\n");


fwrite($fh, "end of php - starting html\n\n");

// close - otherwise buffer will be lost if script crashes
fclose($myFile);

                  
// PHP END -------------------------------------------------------------------
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>avr - Highcharts</title>

		<script type="text/javascript" src="../../js/jquery.min.js"></script>
		<script type="text/javascript">
       
$(function () {
        $('#container').highcharts({
            chart: {
                type: 'line',            
                zoomType: 'x',
                spacingRight: 20
            },            
            title: {
                text: 'Monthly Average Temperature',
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' :
                    'Pinch the chart to zoom in'
            },
            xAxis: {
                type: 'datetime',
                maxZoom: 1 * 24 * 60 / 5 , // one days : 1 Tag * 24h/Tag und pro 60 min, alle 5 min eine Messung
                title: {
                    text: null
                }
            },
            yAxis: {
                title: {
                    text: 'Temperature (Â°C)'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: 'Â°C',
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            

            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: false
                    },
                    enableMouseTracking: false
                }
            },
                        
            series: [{
                type: 'area',
                name: 'Aussen',
                pointInterval: 24 * 12,// 1 Tag, alle 5 min eine Messung
                pointStart: Date.UTC(2006, 0, 01),
                data: [<?php echo join($data1, ',') ?>],
            }, {
                name: 'Wintergarten',
                pointInterval: 24 * 12,// 1 Tag, alle 5 min eine Messung
                pointStart: Date.UTC(2006, 0, 01),
                data: [<?php echo join($data2, ',') ?>],
            }, {
                name: 'Zimmer',
                pointInterval: 24 * 12,// 1 Tag, alle 5 min eine Messung
                pointStart: Date.UTC(2006, 0, 01),
                data: [<?php echo join($data3, ',') ?>],
            }]
        });
    });    

		</script>
	</head>
	<body>
<script src="../../js/highcharts.js"></script>
<script src="../../js/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

	</body>

</html>