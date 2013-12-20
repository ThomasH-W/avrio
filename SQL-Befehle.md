# # mysql – SQL Befehle für Temperatur-Datenbank inkl. Durchschnittstemperatur # #

----------

Alle Datenbanken zeigen                        > SHOW DATABASES;
Mit Datenbank verbinden                        > USE avrio;
Tabellen zeigen                                > SHOW TABLES;
Tabellen-Definition anzeigen                   > DESC avrdat;

Alles anzeigen (Ausgabe: alle Datensätze)      > SELECT * FROM avrdat;
Datensätze zählen (Ausgabe: Anzahl Datensätze) > SELECT COUNT(aussen) FROM avrdat; 
Ein Feld anzeigen (Ausgabe: alle Datensätze)   > SELECT Aussen FROM avrdat; 
Sortieren                                      > SELECT * FROM avrdat ORDER BY dattim;
Gruppieren (Ausgabe: 1 Datensatz pro Jahre)    > SELECT * FROM avrdat GROUP BY YEAR(dattim);
Durchschnitt über alle Datensätze              > SELECT AVG(Aussen) FROM avrdat; 
Daten der letzten 3 Tage                       > SELECT * FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 3 DAY);

----------

Die Durchschnittstemperatur pro Tag anzeigen:

mysql> SELECT dattim, AVG(aussen),avg(innen) FROM avrdat GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim);

Die Durchschnittstemperatur pro Stunde anzeigen:

mysql> SELECT dattim, AVG(aussen) FROM avrdat GROUP BY YEAR(dattim),MONTH(dattim),DAY(dattim),HOUR(dattim);

Die Durchschnittstemperatur pro Stunde der letzten 3 Tage anzeigen:

mysql> SELECT dattim, AVG(aussen) FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 3 DAY) GROUP BY DAY(dattim),HOUR(dattim);
ACHTUNG: Wenn man DAY(dattim) entfernt, erhält man 24 Einträge, d.h. 1 pro Stunde.

http://weblogs.sqlteam.com/jeffs/archive/2007/09/10/group-by-month-sql.aspx

----------

 mysql verwenden  piw@raspberrypi:~$ mysql -u root -p Enter password:
 mysql> show databases; 
+--------------------+ 
| Database           | 
+--------------------+ 
| information_schema | 
| DatenloggerDB      | 
| avrio              | 
| dlog               | 
| mysql              | 
| performance_schema | 
| phpmyadmin         | 
| temp               | 
| test               | 
+--------------------+ 
9 rows in set (0.00 sec) 

mysql> use avrio; 

Reading table information for completion of table and column names You can turn off this feature to get a quicker startup with -A Database changed 
mysql> show tables; 
+-----------------+ 
| Tables_in_avrio | 
+-----------------+ 
| avrdat          | 
| avrdattest      | 
+-----------------+ 
2 rows in set (0.00 sec) 

mysql> desc avrdat;
 +--------------+-----------+------+-----+-------------------+-----------------------------+ 
| Field        | Type      | Null | Key | Default           | Extra                       | 
+--------------+-----------+------+-----+-------------------+-----------------------------+ 
| id           | int(11)   | NO   | PRI | NULL              | auto_increment              | 
| dattim       | timestamp | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP | 
| Aussen       | float     | YES  |     | 0                 |                             | 
| Wintergarten | float     | YES  |     | 0                 |                             | 
| Zimmer       | float     | YES  |     | 0                 |                             | 
| Terrasse     | float     | YES  |     | 0                 |                             | 
| Pool         | float     | YES  |     | 0                 |                             | 
| WW_Speicher  | float     | YES  |     | 0                 |                             | 
| Vorlauf      | float     | YES  |     | 0                 |                             | 
| Ruecklauf    | float     | YES  |     | 0                 |                             | 
+--------------+-----------+------+-----+-------------------+-----------------------------+ 
10 rows in set (0.04 sec) Alle Einträge anzeigen:

mysql> select * from avrdat;

Die Durchschnittstemperatur pro Monat anzeigen:
mysql> select dattim, avg(aussen) from avrdat group by year(dattim),month(dattim);
+---------------------+--------------------+
| dattim              | avg(aussen)        |
+---------------------+--------------------+
| 2013-04-06 18:47:30 |  12.79634127842446 |
| 2013-05-01 00:00:12 | 14.089132557345758 |
| 2013-06-01 00:00:11 |  18.49742645632616 |
| 2013-07-08 19:32:07 | 23.233857636293788 |
| 2013-08-01 00:00:11 | 26.937875309723164 |
| 2013-09-01 00:00:28 |  65.56896182821275 |
| 2013-10-01 00:00:09 | 12.924068399765122 |
| 2013-11-01 00:00:13 |   5.54401955781481 |
| 2013-12-01 00:00:04 | 2.5698396265876196 |
+---------------------+--------------------+
9 rows in set (2.19 sec)

 

----------------------------------------------------------------------------------------------------
Ausgabe (gemessen):

Alles / 65902 rows / 3.97s > SELECT * from avrdat; 24 Stunden / 275 rows / 1.20s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR) ORDER BY dattim; 

 7 Tage    /  1543 rows / 1.27s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 7 DAY) ORDER BY dattim;

 2 Monate  / 16641 rows / 2.20s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 MONTH) ORDER BY dattim;

 2 Jahre   / 65902 rows / 5.57s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR) ORDER BY dattim;

 2 Jahre   / 65902 rows / 5.41s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR);

 2 Jahre   /  5595 rows / 4.51s > SELECT * , UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR) group by year(dattim),month(dattim),day(dattim),hour(dattim);

 2 Jahre   / 65902 rows / 3.10 > SELECT AUSSEN , DATTIM,  UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR) ;

 2 Jahre   /  5595 rows / 4.11s > SELECT AUSSEN , DATTIM, UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR) group by year(dattim),month(dattim),day(dattim),hour(dattim);

 2 Jahre   /  5595 rows / 4.36s > SELECT avg(AUSSEN) , DATTIM, UNIX_TIMESTAMP(dattim) AS date FROM avrdat WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), Interval 2 YEAR) group by year(dattim),month(dattim),day(dattim),hour(dattim);

 

----------------------------------------------------------------------------------------------------

In PHP kann man wie folgt auf die Datenbank zugreifen:
$dbhost  = "127.0.0.1";
$dbuser  = "root";
$dbpass  = "nxt2008";
$dbname  = "avrio";
$dbtable = "avrdat";
$dbfield = "Innen, Aussen, Zimmer, Balkon, Wasser, WW_Speicher, Vorlauf, Ruecklauf";

$sql1 = "SELECT "; $sql2 = $dbfield; 
$sql3 = " , UNIX_TIMESTAMP(dattim) AS date FROM "; 
$sql4 = $dbtable; 
$sql5 = " WHERE dattim >= Date_Sub(CURRENT_TIMESTAMP(), "; 
$sql6 = "Interval 24 HOUR"; 
$sql7 = " ) ORDER BY dattim";
$sql = $sql1.$sql2.$sql3.$sql4.$sql5.$sql6.$sql7;

mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname)or die ('Error connecting to database');
$sql = mysql_query($sql) or die ('Error selecting data');
$rownum = mysql_num_rows($sql);

