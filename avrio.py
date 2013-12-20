#!/usr/bin/python
# -*- coding: utf-8 -*-
#
# Read 1-Wire sensors and write into database
# 2013-06-15 V0.5 by Thomas Hoeser
#

import MySQLdb # muss installiert werden und dann reboot
               # sudo apt-get install python-mysqldb
import sys     # Import sys module
import datetime
import time
import argparse # analyze command line arguments

from avrio_html import *
from avrio_database import *

config_file='/home/pi/avrio/avrio-config.txt'
#config_file='./avrio-config.txt'
html_single_file='/var/www/avrweb/avr-single.html'

db_fields = []        # list of database fields
db_dict = {}          # dictionary
sensor_list = []      # list of sensors
sensor_locations = [] # list of locations
sensor_dict = {} # dictionary -> sensor : location

# define range for faulty sensor data
dead_max = 80
dead_hi = -0.02
dead_lo = -0.08
error_temp = -999

db_Host = "localhost"          # host - local database
db_User = "root"          # username
db_Password = "tbd"      # password
db_Database = "avrio"      # datenbank
db_Table = "avrdat"         # Table
db_RHost = "tbd"         # host - Remote Database / optional
db_RUser = "tbd"         # username
db_RPassword = "tbd"     # password
db_RDatabase = "tbd"     # datenbank
db_RTable = "tbd"        # Table
db_fields_all = ""
db = ""

db_all = [db_Table,db_Database,db_Host,db_User,db_Password]
db_all_local = [db_Table,db_Database,db_Host,db_User,db_Password]
db_all_remote = [db_RTable,db_RDatabase,db_RHost,db_RUser,db_RPassword]
sensordata = []
data_temp = []

verbose_level = 1  # default: show status messages
database_level = 1 # default: write to database
config_level = 0 # default: do not read config file
setup_level = 0 # default: do not read config file
read10_level = 0 # default: do not read
remote_level = 0 # default: use local database  
read_sens = 1


#-------------------------------------------------------------------------------------------
parser = argparse.ArgumentParser(description="Read 1-wire sensors and write to database")

# argument with argument from type int

parser.add_argument("-r", "--read", help="get the [READ] last entries from the database", type=int)

group1 = parser.add_mutually_exclusive_group()
group1.add_argument("-v", "--verbose", default=False, 
                    dest='verbose', help="increase output verbosity", type=int)
                    
group1.add_argument("-q", "--quiet", action='store_const', dest='quiet',
                    const='value-to-store',help="no output")
                    
group1.add_argument("-d", "--debug", action='store_const', dest='debug',
                    const='value-to-store', help="show debug messages")
                    
parser.add_argument("-n", "--nodb", action='store_const', dest='nodb',
                    const='value-to-store', help="execute read but do not write into database")      
parser.add_argument("-w", "--html_single", action='store_const', dest='html_single',
             const='value-to-store',help="create web page(html)with last entry") 
                    
parser.add_argument("-k", "--kill", action='store_const', dest='kill', const='value-to-store',help="kill all entries in database",)

parser.add_argument("-g", "--get", action='store_const', dest='get', const='value-to-store',help="get sensors and append to config.txt",)
                     
parser.add_argument("-s", "--setup", action='store_const', dest='setup',
                    const='value-to-store',help="create config file",)
parser.add_argument("-b", "--backup", action='store_const', dest='remote',
                    const='value-to-store',help="use remote database",)
parser.add_argument("-c", "--createdb", action='store_const', dest='create',
                    const='value-to-store',help="create database",)
                    
parser.add_argument("-x", "--xxx", help="clean [NULL] last entries from the database", type=int)

                    
parser.add_argument('--version', action='version', version='%(prog)s 0.2')
                    
args = parser.parse_args()

if args.verbose:  verbose_level = args.verbose
if args.quiet:    verbose_level = 0   
if args.debug:    verbose_level = 2
if args.nodb:     database_level = 1
if args.setup:    setup_level = 1
	
#-------------------------------------------------------------------------------------------
def read_config(init_level):

  global db_Host
  global db_Database
  global db_User
  global db_Password 
  global db_Table 
  global db_all
  
  global db_RHost
  global db_RDatabase
  global db_RUser
  global db_RPassword 
  global db_RTable 
  global db_all_remote

  global db_fields_all

  error_level = 0

  if verbose_level>1: print "reading config file ", config_file
  jetzt = time.strftime("%d.%m.%Y - %H:%M:%S Uhr") 
  if verbose_level>1: print "-------------------------------------" , jetzt  
  
  try:

    file = open(config_file,"r")
    settings=[]     # Erstellt ein Variablenarray, das die Einstellungen speichert
    for line in file: #
        if line[0]!="#": # i.e. ignore comment lines
          settings.append(line)
    file.close  
    
    for x in range (0, len(settings)):
      line = settings[x]
      if verbose_level>3: print x, ": ", line
      item1 = line.split()
      if len(item1)>1: # need 3 items for sensor: tag, value1, value2
        if verbose_level>2: print item1[0], item1[1], len(item1)
        if item1[0]=="Host":     
           db_Host = item1[1]
           if len(item1)>2:db_RHost = item1[2] # remote database
        if item1[0]=="Database": 
           db_Database = item1[1]
           if len(item1)>2:db_RDatabase = item1[2]
        if item1[0]=="Table":    
           db_Table = item1[1]
           if len(item1)>2:db_RTable = item1[2]
        if item1[0]=="User":     
           db_User = item1[1]
           if len(item1)>2:db_RUser = item1[2]
        if item1[0]=="Password": 
           db_Password = item1[1]
           if len(item1)>2:db_RPassword = item1[2]
        if item1[0]=="dbfield":  db_fields.append(item1[1])
        if len(item1)>2:
          if item1[0]=="Sensor":   
            if verbose_level>2: print "Sensor", item1[1], "Field:", item1[2]
            sensor_list.append(item1[1])
            sensor_locations.append(item1[2])
            sensor_dict[item1[1]] = item1[2]


    for x in range (0, len(db_fields)):
      if verbose_level>2: print  db_fields[x]
      if x==0:
        db_fields_all = str(db_fields[x])
      else:  
        db_fields_all = db_fields_all +  ", " + str(db_fields[x])
      db_dict[db_fields[x]] = x
    if verbose_level>2: print "all db fields: " , db_fields_all
    
    db_all = [db_Table,db_Database,db_Host,db_User,db_Password]
    db_all_remote = [db_RTable,db_RDatabase,db_RHost,db_RUser,db_RPassword]
    if verbose_level>1: 
       print "Database (local) : " , db_all
       print "Database (remote): " , db_all_remote
     
    if verbose_level>2: print  "all sensor list: " , sensor_list       
    if init_level == 0: # during initital setup do not display panic messages
      for x in range (0, len(sensor_list)):    
        x_str = sensor_locations[x]
        if str(db_dict.get(x_str)) == "None": 
          print "PANIC: Sensor", sensor_list[x], "is using unknown location", x_str
          if x == 0:
            print "please assign one out of dbfield"
            print db_fields
        
    if verbose_level>1: print sensor_dict
    if verbose_level>1: print db_fields
    if verbose_level>1: print db_dict
  
    if error_level >0:
      print 
      print
      print "please edit config file " + config_file+ " and assign one out of dbfield"
      print "e.g. Sensor " + sensor_list[x] + " " + db_fields[0]
      print "available fields: " + str(db_fields)
      print
      sys.exit(0)
         
  except IOError:
    print "----------------------" , jetzt
    print "Cannot find file: " + config_file
  return()

#-------------------------------------------------------------------------------------------
def add_config():

  jetzt = time.strftime("%d.%m.%Y - %H:%M:%S Uhr") 
  file_config = open(config_file,"a")
  file_config.write("# ----------------------------------------\n")
  file_config.write("# sensors added on " + jetzt + "\n")
  file_config.write("# ----------------------------------------\n")

  sensor_count = 0
  if verbose_level>0: print "Open 1-wire slaves list for reading"
  file = open('/sys/devices/w1_bus_master1/w1_master_slaves')

  w1_slaves = file.readlines()            # Read 1-wire slaves list
  file.close()                            # Close 1-wire slaves list
  
  # Repeat following steps with each 1-wire slave
  for line in w1_slaves:
    w1_slave = line.split("\n")[0]        # Extract 1-wire slave
    if verbose_level>0: print w1_slave
    file_config.write("#Sensor " + w1_slave + "\n")

  file.close()
  
  sys.exit(0)
  return()
#-------------------------------------------------------------------------------------------
#-------------------------------------------------------------------------------------------
def write_config():

  global db_Host
  global db_Database
  global db_User
  global db_Password 
  global db_Table 
  global db_fields_all
  global db_all
  
  print "Write new config file", config_file
  var = raw_input("Enter yes: ")
  if var =="yes":
    
    var = raw_input("MySQL host address (default ["+ db_Host + "]):")
    if len(var)!=0: db_Host = var
    var = raw_input("MySQL user name    (default ["+ db_User + "]):")
    if len(var)!=0: db_User = var
    var = raw_input("MySQL password     (default ["+ db_Password + "]):")
    if len(var)!=0: db_Password = var
    var = raw_input("MySQL database     (default ["+ db_Database + "]):")
    if len(var)!=0: db_Database = var
    var = raw_input("MySQL table        (default ["+ db_Table + "]):")
    if len(var)!=0: db_Table = var

    print "check: " + db_Host + " " + db_User + " "  + db_Password + " "  + db_Database + " " + db_Table
    var = raw_input("Enter ok: ")
    if var !="ok":
      print "no file created" 
      return(1)
    
    print "creating config file now..."
    jetzt = time.strftime("%d.%m.%Y - %H:%M:%S Uhr") 
    file_config = open(config_file,"w")
    file_config.write("# config file for avrio.py\n")
    file_config.write("# created on " + jetzt + "\n")
    file_config.write("# ----------------------------------------\n")
    file_config.write("Host " + db_Host + "\n")
    file_config.write("User " + db_User + "\n")
    file_config.write("Password " + db_Password + "\n")
    file_config.write("Database " + db_Database + "\n")
    file_config.write("Table " + db_Table + "\n")
    file_config.write("# ----------------------------------------\n")
    file_config.write("dbfield Aussen\n")
    file_config.write("dbfield Innen\n")
    file_config.write("dbfield Zimmer\n")
    file_config.write("dbfield Balkon\n")
    file_config.write("dbfield Wasser\n")
    file_config.write("dbfield WW_Speicher\n")
    file_config.write("dbfield Vorlauf\n")
    file_config.write("dbfield Ruecklauf\n")
    file_config.write("# Syntax: \n# Sensor [Sensor ID] [Sensor Field in Database]\n")
    file_config.write("# ----------------------------------------\n")
    print
    print "look for connected sensors in /sys/devices/w1_bus_master1/w1_master_slaves"
    file = open('/sys/devices/w1_bus_master1/w1_master_slaves')
    w1_slaves = file.readlines()
    file.close()
    for line in w1_slaves:
      w1_slave = line.split("\n")[0]
      if verbose_level: print w1_slave  
      file_config.write("Sensor " + w1_slave + " tbd\n")
    file_config.close
  else:
    print "request cancelled"
    return(1)
        
  return(0)

#-------------------------------------------------------------------------------------------
def read_sensor(sensor_slave):
  # Open 1-wire slave file
  sensor_device = '/sys/bus/w1/devices/' + str(sensor_slave) + '/w1_slave'
  try:
	file = open(sensor_device)
	filecontent = file.read()                              # Read content from 1-wire slave file
	file.close()                                           # Close 1-wire slave file
	stringvalue = filecontent.split("\n")[1].split(" ")[9] # Extract temperature string
	if stringvalue[0].find("YES") > 0:
		temperature = error_temp
	else:
		temperature = float(stringvalue[2:]) / 1000            # Convert temperature value
    
  except IOError:
     print "PANIC read_sensor - Cannot find file >" + sensor_slave + "< in /sys/bus/w1/devices/"
     print "No sensor attached"
     print "check with > cat /sys/devices/w1_bus_master1/w1_master_slaves"
     sys.exit(1) 
    
  return(temperature) # exit function read_sensor
  
#-------------------------------------------------------------------------------------------
def read_sensors(read_level):
 sensor_count = 0
 sensor_slaves = '/sys/devices/w1_bus_master1/w1_master_slaves'
 # Open 1-wire slaves list for reading
 try: 
  file = open(sensor_slaves)

  w1_slaves = file.readlines()            # Read 1-wire slaves list
  file.close()                            # Close 1-wire slaves list

  # Print header for results table
  if verbose_level>0: print('Sensor ID       | Temperature')
  if verbose_level>0: print('-----------------------------')
  
  # Repeat following steps with each 1-wire slave
  for line in w1_slaves:
    w1_slave = line.split("\n")[0]        # Extract 1-wire slave
    time.sleep(0.2)
    temperature = read_sensor( w1_slave)  # call read function
    
    if temperature >= dead_lo and temperature <= dead_hi or temperature > dead_max:  # check for faulty data
      if verbose_level>1:print "Panic", temperature
      time.sleep(0.5)
      temperature = read_sensor( w1_slave)
      if verbose_level>1:print "2nd try", temperature
      
    if temperature >= dead_lo and temperature <= dead_hi or temperature> dead_max:  # check for faulty data
      if verbose_level>0:print "Panic", temperature
      time.sleep(1.5)
      temperature = read_sensor( w1_slave)
      if verbose_level>0:print "3rd try", temperature
        
    if verbose_level>0: print(str(w1_slave) + ' | %5.3f °C' % temperature) # Print temperature
    sensor_count = sensor_count + 1

    if read_level:sensordata.append((w1_slave, temperature)) # store temperature in database
  if verbose_level>2: print "sensors detected: ", sensor_count
  return(sensor_count) # exit function read_sensors

 except IOError:
    print "----------------------" , jetzt
    print "read_sensors - Cannot find file: " , sensor_slaves
 return(0)

    

#-------------------------------------------------------------------------------------------

jetzt = time.strftime("%d.%m.%Y - %H:%M:%S Uhr") 
  
if args.get:
  add_config()
 
if args.setup:
  return_val = write_config()
  if return_val==0:
    print "read config file which has been created"
    read_config(1) 
    print "create database incl. tables"
    create_database(db_all,db_fields_all,verbose_level)
  sys.exit(0)
  
return_val = read_config(0) #db_all,db_fields_all,verbose_level)
if return_val: 
  print "Error: cannot find config file " + config_file 
  print "Please run >avrio.py --setup"
  sys.exit(0)
  database_level = 0

if args.remote: 
  db_all = db_all_remote

if args.create: 
  create_database(db_all,db_fields_all,verbose_level)
  read_sens=0
  
if args.read: 
  read_records(args.read,db_all,db_fields_all,verbose_level)
  read_sens=0
  
if args.kill:   
  kill_dbentries(db_all,verbose_level) 
  read_sens=0
  
if args.xxx:   
  clean_records(args.xxx,db_all,db_fields_all,verbose_level)
  read_sens=0
  
if args.html_single:  
  write_html_single(html_single_file,db_all,db_fields_all,verbose_level)
  read_sens=0

if read_sens:
  if verbose_level>0: print sys.argv[0], " - reading sensors ...  ", jetzt
  read_items = read_sensors(1) # read + collect data
  if database_level: write_database(read_items,sensordata,sensor_dict, db_all, verbose_level)

# Quit python script
sys.exit(0)

