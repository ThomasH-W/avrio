<?php
include ("src/jpgraph.php");
include ("src/jpgraph_line.php");
require_once("src/jpgraph_date.php");

/* Verbindung zu mySQL aufbauen, Auswählen einer Datenbank */
$link = mysql_connect("localhost", "root", "rootpassword")     // Anmelden bei der Datenbank
or die("Keine Verbindung moeglich: " . mysql_error() . "<br>");

mysql_select_db("avrio") or die("Auswahl der Datenbank fehlgeschlagen<br>");

$now = time();
$Nunc = date('Y-m-d',$now);

// debug
$myFile = "avrio.log";
$fh = fopen($myFile, 'w');

// SQL Abfrage

$query = "SELECT UNIX_TIMESTAMP(dattim),Aussen, Wintergarten, Zimmer, Terrasse, Pool, WW_Speicher, Vorlauf, Ruecklauf FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR) ORDER BY dattim";
// $query = "SELECT UNIX_TIMESTAMP(dattim),Aussen, Innen, Zimmer, Balkon, Wasser, WW_Speicher, Vorlauf, Ruecklauf FROM avrdat";
$result = mysql_query($query);
fwrite($fh, $query); fwrite($fh, "\n");

$i=0;
while ($array=mysql_fetch_array($result)) {
$datax[$i]=  $array[0];
$adata[$i] = $array[1]; // Aussen
$bdata[$i] = $array[2]; // Innen
$cdata[$i] = $array[3]; // Zimmer
$ddata[$i] = $array[4]; // Balkon
$edata[$i] = $array[5]; // Wasser
$fdata[$i] = $array[6]; // WW_Speicher
$gdata[$i] = $array[7]; // Vorlauf
$hdata[$i] = $array[8]; // Rucklauf

$i++;
};
fwrite($fh,$i);
fwrite($fh,$i);

fclose($fh);

mysql_free_result($result);
/* Schliessen der mySQL Verbinung */
mysql_close($link);

function TimeCallback($aVal) {
return Date('d H:i    ',$aVal);
}
// Create the graph. These two calls are always required
$graph = new Graph(1000,350,"auto");
$graph->SetScale("datlin");
$graph->SetShadow();
$graph->SetMargin(40,170,20,100);
$graph->title->Set('Datum: '.date('Y-m-d',$now));

#$graph->title->Set('Datum ');
$graph->xaxis->title->set("Zeit");
$graph->yaxis->title->Set("Temperatur °C");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->xaxis->SetLabelFormatCallback('TimeCallback');
// $graph->xaxis->scale->SetDateFormat("H:i");
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetTimeAlign(MINADJ_15);

// Create the linear plot
// Aussen
$lineplot=new LinePlot($adata,$datax);
$lineplot->SetColor("red");
$lineplot->SetWeight(2);

// Innen
$lineplotb = new LinePlot($bdata,$datax);
$lineplotb->SetColor("orange");
$lineplotb->SetWeight(2);

// Zimmer
$lineplotc = new LinePlot($cdata,$datax);
$lineplotc->SetColor("blueviolet");
$lineplotc->SetWeight(2);

// Balkon
$lineplotd = new LinePlot($ddata,$datax);
$lineplotd->SetColor("cyan");
$lineplotd->SetWeight(2);

// Warmwasser
$lineplote = new LinePlot($edata,$datax);
$lineplote->SetColor("brown");
$lineplote->SetWeight(2);

// WW_Speicher
$lineplotf = new LinePlot($fdata,$datax);
$lineplotf->SetColor("blue");
$lineplotf->SetWeight(2);

// Vorlauf
$lineplotg = new LinePlot($gdata,$datax);
$lineplotg->SetColor("green");
$lineplotg->SetWeight(2);

// Rucklauf
$lineploth = new LinePlot($hdata,$datax);
$lineploth->SetColor("black");
$lineploth->SetWeight(2);

$lineplot->SetLegend("T Kellerschacht Ost");
$lineplotb->SetLegend("T Arbeitszimmer");
$lineplotc->SetLegend("T Zimmer");
$lineplotd->SetLegend("T Balkon");
$lineplote->SetLegend("T Warmwasser");
$lineplotf->SetLegend("T WW_Speicher");
$lineplotg->SetLegend("T Vorlauf");
$lineploth->SetLegend("T Rücklauf");

// Add the plot to the graph
$graph->Add($lineplot);
$graph->Add($lineplotb);
$graph->Add($lineplotc);
$graph->Add($lineplotd);
$graph->Add($lineplote);
$graph->Add($lineplotf);
$graph->Add($lineplotg);
$graph->Add($lineploth);

// Adjust the legend position
$graph->legend->Pos(0.01,0.3,"right","center");

// Display the graph
$graph->Stroke();

?>

