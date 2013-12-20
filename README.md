Mein erstes Projekt mit dem Raspberry und der Programmiersprache Python:

Die Temperaturen von mehreren Sensoren soll in eine Datenbank geschrieben werden.
Da mein raspberry headless läuft (also ohne Bildschirm, Tastatur) sollen die Daten über einen WEB-Server präsentiert werden.

Die Anregung für dies Projekt habe ich hier gefunden:
http://www.forum-raspberrypi.de/Thread-k...-mit-linux

Für Leute, die sich noch nicht so gut mit Python, MySQL oder Datenbanken auskennen, kann dies vielleicht hilfreich sein.

Voraussetzung:

    Webserver
    PhP5
    MySQL Datenbank
    phpMyAdmin
    Für die Grafik: JPGRAPH oder google graphic
    Ich emppfehle MobaXterm für den Zugriff von Windows auf den raspberry- wesentlich komfortabler als Putty
    1-Wire Sensoren



Die Software wurde in Python geschrieben.
In dem Paket sind folgende Dateien enthalten:

    avrio.py            Das Hauptprogram
    avrio_database.py   Ein Modul mit den Funktionen für die Datenbank
    avrio_html.py       Ein Modul, um eine HTML Datei zu erzeugen
    avr-graph.php       Ein PHP-Script, welches, den Temperaturverlauf als Grafik zeigt
    avrio-writedb.sh    Des kleines shell script, welches von cron aufgerufen wird


Wenn avrio.py das erste Mal gestartet wird, werden die Parameter abgefragt und eine Konfigurationsdatei erzeugt (avrio-config.txt).
Z.b. User/Pw für MySQL und die Datenbank-Parameter.
Anschließend erzeugt das Programm die Datenbank und die Datenbank-Tabelle.
Die bei der Installation angeschlossenen 1-Wire Sensoren werden automatisch erkannt und in die Konfigurations-Datei geschrieben.

![](http://thomas.hoeser-medien.de/pictures/avrio-1.png)


Mit einem Editor (z.B. nano) erfolgt in der Datei die Zuordnung zu den Datenbankfeldern.

![](http://thomas.hoeser-medien.de/pictures/avrio-2.png)


Als nächstes wird das Hauptprogram gestartet, welches ohne die Angabe von Parametern die Sensoren ausliest und die Daten in die Datenbank schreibt:
> python avrio.py

![](http://thomas.hoeser-medien.de/pictures/avrio-3.png)


Die Daten können auf mehrere Arten angezeigt werden:
Über die Kommandozeile die letzten 3 Einträge aufrufen
> python avrio.py -r 3

![](http://thomas.hoeser-medien.de/pictures/avrio-4.png)


Über den Webserver kann man eine Seite aufrufen, die das Programm erzeugt:
> python avrio.py -w
Dann im Browser: http://192.168.178.60/avrweb/avr-single.html

![](http://thomas.hoeser-medien.de/pictures/avrio-5.png)


Über den Webserver kann man eine Seite aufrufen, die eine Grafik erzeugt:
http://192.168.178.60/avrweb/avr-graph.php

![](http://thomas.hoeser-medien.de/pictures/avrio-6.png)



Falls jemand Fehler findet oder Anregung hat, bitte melden.

