<?php
// Read MySQL database and build diagram based on highcharts graphics
// Program basis from on Enrico S.
// http://fluuux.de/2013/02/mit-highcharts-werte-aus-einer-datenbank-visualisieren
// 2014-01-07 V0.2 by Thomas Hoeser
//
// Requirements:
// /var/www/js/scripts/highcharts.js
// /var/www/js/scripts/jquery.min.js
// /var/www/js/modules/exporting.js
// /var/www/js/includes/common.inc.php
// /var/www/js/includes/functions.inc.php

//  database + fields are defined in common.inc.php
/************************************************************************
  Übersicht aller Sensorwerte der letzten 24 Stunden in einem Chart     *
  Darunter eine Anzeige der zuletzt gespeicherten Werte aller Sensoren  *
  Die Chart-Linien werden als Spline dargestellt.                       *
************************************************************************/
// debug
$myFile = "avr-highchart.log";
$fh = fopen($myFile, 'w');
fwrite($fh, "----------------------------------------------- starting avr-highcharts.php\n");


include_once("includes/common.inc.php");
include_once("includes/functions.inc.php");

$scope='day';
$scopeval=1;
$scope_title="Temperaturwerte der letzten Stunden";

if(!isset($_GET['scope'])) $_GET['scope'] = 'day'; else $_GET['scope'] = $_GET['scope'];
if(!isset($_GET['scopeval'])) $_GET['scopeval'] = 1; else $_GET['scopeval'] = $_GET['scopeval'];
if(!isset($_GET['chartStyle'])) $_GET['chartStyle'] = 1; else $_GET['chartStyle'] = $_GET['chartStyle'];
// test scope :
// $scope='day';
// $scopeval=3;
$scope= $_GET['scope'];
$scopeval= $_GET['scopeval'];

$scope_days=1;
$scope_hours=24;

if($scope=='year') {
	$scope_title = "Temperaturwerte der letzten ".$scopeval." Jahre";
	if(is_numeric($scopeval))
  		{$scope_days=$scopeval*365;}
	else
    	{$scope_days=365;}
}
elseif($scope=='month') {
	$scope_title = "Temperaturwerte der letzten ".$scopeval." Monate";
	if(is_numeric($scopeval))
  		{$scope_days=$scopeval*30;}
	else
    	{$scope_days=30;}
}
elseif($scope=='week') {
	$scope_title = "Temperaturwerte der letzten ".$scopeval." Wochen";
	if(is_numeric($scopeval))
  		{$scopeval=$scopeval*7;$scope_days=$scopeval;}
	else
    	{$scope_days=7;}
}
elseif($scope=='day') {
	$scope_title = "Temperaturwerte der letzten ".$scopeval." Tage";
	if(is_numeric($scopeval))
  		{$scope_days=$scopeval;}
}
elseif($scope=='hour') {
	$scope_title = "Temperaturwerte der letzten ".$scopeval." Stunden";
	if(is_numeric($scopeval))
  		{$scope_hours=$scopeval;}
}
fwrite($fh, "scope       : $scope\n");
fwrite($fh, "scopeval    : $scopeval\n");


$colors = array('#89A54E','#80699B','#3D96AE','#DB843D','#92A8CD','#A47D7C','#B5CA92');

// echo "before getChartValues ... ";
list($chartValues[], $stundenValues[],$DattimStart,$dataCount) = getChartValues($SensorCount, $scope, $scopeval);
/*
echo "after getChartValues<br>";

      for($i=0;$i<=NUMSENSORS;$i++)
      {        if(!empty($chartValues[0][$i]))
        {
         	echo $chartValues[0][$i]."<br>";
        }
	  }
*/

$interval = $scope_days * $scope_hours * 3600 * 1000 / $dataCount;
fwrite($fh, "scope_days  : $scope_days\n");
fwrite($fh, "scope_hours : $scope_hours\n");
fwrite($fh, "dataCount   : $dataCount\n");
fwrite($fh, "title       : $scope_title\n");
fwrite($fh, "interval    : $interval\n");
// echo "start html ....<br>";

fwrite($fh, "----------------------------------------------- start html ...\n");
// Datenbankverbindung schliessen
$db->close();
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Sensoren &Uuml;bersicht</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="300">
    <meta name="Keywords" content="Raspberry,Temperatursensor,DS18s20">
    <meta name="Description" content="Visualisierung der Temperaturdaten">
    <meta name="Robots" content="index,follow">
    <link rel="stylesheet" type="text/css" href="es_styles/default.css">
<!--    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script> -->
    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/highcharts.js"></script>

<script type="text/javascript">
$(function () 
{
  var chart;
  
  $(document).ready(function() 
  {        
	chart = new Highcharts.Chart(
    {
      chart:
      {
        renderTo: 'container'
      },
      global: {
        useUTC: false
      },
      title:
      {
        text: <?php echo '\''.$scope_title.'\'';?>
		<!-- text: 'bla'  -->
      },
      subtitle:
      {
        text: 'Alle Messstellen'
      },
	xAxis: [{
        type: 'datetime',
        labels: {
            step: 5 ,
			rotation: 90
        },
         dateTimeLabelFormats:
          {
                second: '%H:%M:%S',
                minute: '%H:%M',
                hour: '%H:%M',
				day: '%d/%m/%Y',
<!--                day: '%e. %b', -->
                week: '%e. %b',
                month: '%b \'%y',
                year: '%Y'
            },
            allowDecimals: true,
         tickInterval: <?php echo $interval;?>
       }] ,
      yAxis:
      {
        title:
        {
          text: ''
        },
        labels:
        {
          formatter: function()
          {
            return this.value +'°C'
          }
        }
      },
	  tooltip: {
	    formatter: function() {
    		return ''+
		    Highcharts.dateFormat('%d-%H:%M',this.x) +': '+ this.y;
		    }
	  },
      legend:
      {
        enabled: true
      },
      credits:
      {
        enabled: false
      },
      series:
      [
<?php
      for($i=0;$i<=$SensorCount;$i++)
      {
        if(!empty($chartValues[0][$i]))
        {
?>        {
            pointInterval:  <?php echo $interval;?>,
            pointStart: <?php echo $DattimStart;?>,
            type      : '<?php echo $SensorStyle[$i][0];?>',
            dashStyle : '<?php echo $SensorStyle[$i][1];?>',
            name      : '<?php echo $SensorStyle[$i][2];?>',
            color     : '<?php echo $SensorStyle[$i][4];?>',
            data: [<?php echo $chartValues[0][$i];?>],
<!--			data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6], -->
            marker:
            {
              symbol: 'square',
              enabled: false,
              states:
              {
                hover:
                {
                  symbol: 'square',
                  enabled: true,
                  radius: 8
                } // hover
              } // states
            } // list
          },

<?php
        } // php - if
      } // php - for
?>
      ] //series
    });
  });
});
</script>


  </head>
<body>

<div id="wrapper">
  <script src="es_scripts/highcharts.js"></script>
  <div id="container"></div>
</div>

 <div  class="anfrage"><a href="avr-highchart.php?scope=hour">STUNDE</a></div>
 <div  class="anfrage"><a href="avr-highchart.php?scope=day">TAG</a></div>
 <div  class="anfrage"><a href="avr-highchart.php?scope=week">WOCHE</a></div>
 <div  class="anfrage"><a href="avr-highchart.php?scope=month">MONAT</a></div>
 <div  class="anfrage"><a href="avr-highchart.php?scope=year">JAHR</a></div>

</body>
</html>