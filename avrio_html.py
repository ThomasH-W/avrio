#!/usr/bin/python
# -*- coding: utf-8 -*-
#
# generate html file - is obsolete, kept for legacy purposes
# please use avr-panel.php
# 2013-06-15 V0.4 by Thomas Hoeser
#
#
import sys     # Import sys module
import datetime
from avrio_database import read_records

def read_records2(file_name,db_all, db_fields_all,verbose_level):
  return
#-------------------------------------------------------------------------------------------
def write_html_single(file_name,db_all, db_fields_all,verbose_level):
   
  # Erstellt eine Datei und ˆffnet sie zum beschreiben (writing 'w')
  if verbose_level>0: print "create html: ", file_name
  try:
	f = open(file_name, 'w')

	if verbose_level>1: print "read database"
	row = read_records(1,db_all, db_fields_all, verbose_level)

	f.write("<html>"+"\n")
	f.write("<head>"+"\n")
	f.write("<title>Temperatur</title>"+"\n")
	f.write("</head>"+"\n")
	f.write("<body bgcolor='#ffffdd'>"+"\n")
	f.write("<font face='verdana' color='#000000'>"+"\n")
	f.write("<center>"+"\n")
	f.write("<h1>Temperatur - Messwerte</h1><p>"+"\n")
	f.write("<hr>"+"\n")
	f.write("</center>"+"\n")
	datetimestring = datetime.datetime.fromtimestamp(row[0])
	f.write("<h3>" + str(datetimestring) + "</h3><p>"+"\n")
	if verbose_level>1: print "date written: ", str(datetimestring)
	f.write("<table border=1>")
	f.write("<tr><td>" + "Sensor"+ "</td><td>" + "Temp." + "</td></tr>")
 
	# print db_fields_all
	db_f = db_fields_all.split()
	i = 0
	for x in (db_f):
		i = i + 1 # the 1st element is the date
		db_f1 = x.split(',')
		if verbose_level>1: print db_f1[0],  str(row[i])
		temp = "%.1f" %  row[i]
		f.write("<tr><td>" + str(db_f1[0])+ "</td><td>" + str(temp) + "</td></tr>")

	#f.write("/tr")
	f.write("<hr>"+"\n")
	f.write("<center>"+"\n")
	f.write("<font size='2'>"+"\n")
	f.write("<b><a href='http:avr-single.html'>Refresh</a></b><p>"+"\n")
	f.write("<b><a href='http:avr-graph.php'>Graph</a></b><p>"+"\n")
	f.write("</body>"+"\n")
	f.write("</html>")
 
	# Die Datei schlieﬂen
	f.close()
   
  except:
	print "Error: can't open file " + file_name + "for writing"
	print "Please check if directory is existing"
	
  sys.exit(0)
  return()
  
version = '0.1'

# Ende von meinmodul.py
#<table border="1">
#  <tr>
#    <td> INHALT ZELLE 1  </td>
#    <td> INHALT ZELLE 2  </td>
#  </tr>
#  <tr>
#    <td> INHALT ZELLE 3  </td>
#    <td> INHALT ZELLE 4  </td>
#  </tr>
#</table>
