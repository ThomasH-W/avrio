## Installation ##

# change to home directory
> cd
> mkdir avrio

# copy the following files into ~/avrio files
- avrio.py
- avrio_database.py
- avrio_html.py
- avrio-config.txt

# copy the following files into /var/www/avrweb
- avr-google.php
- avrio-graph.php
- avr-panel.php
Note: these php scripts do not use the config file
I.e. you need to edit these file to reflect you user,password etc.

--------------------------------------------------------------

# make main program executable
# i.e. you do not need to start program with > python avrio.py
> chmod +x avrio.py

# configure avrio system
Option 1 (start from scrath)
rename default file
> mv avrio-config.txt avrio-config.txt_default
> ./avrio.py --setup
edit config file and assign sensors
> nano avrio_config.txt

Option 2 (start with default config file)
- edit config file manually
> nano avrio_config.txt


Below is the default config file
The sensors at the end have to be changed with your sensor id's.
You can either use avrio
> ./avrio.py --get
or you use the linux command
> cat /sys/devices/w1_bus_master1/w1_master_slaves

-------------------------------------------------------------
# config file for avrio.py
# created on 02.03.2013 - 06:50:43 Uhr
# ----------------------------------------
# you can add two MySQL servers
# by default the first col is used
Host localhost  192.168.178.20
User     root      root      
Password rootpw    rootpw      
Database avrio     avrio     
Table    avrdat    avrdat    
# ----------------------------------------
dbfield Aussen
dbfield Innen
dbfield Zimmer
dbfield Balkon
dbfield Wasser
dbfield WW_Speicher
dbfield Vorlauf
dbfield Ruecklauf
# Syntax: 
# Sensor [Sensor ID] [Sensor Field in Database]
# ----------------------------------------
Sensor 28-000004884121 Ruecklauf
Sensor 28-000004885b83 Innen
Sensor 28-00000487ae73 Vorlauf
Sensor 28-00000487bb70 Aussen
Sensor 28-000004882de7 Zimmer
Sensor 28-000004881dd5 Balkon

--------------------------------------------------------------
Create a cron job, which will execute the python script.

Below you will see two entries.
The first one is writing the sensor data into the local database (default)
and the second entry will write into the backup databse.
In my case the backup database is in the web at my provider.
This ensure that the raspberry can still stand on its own regardless of the network status.
In addition I can access my data from anywhere without messing up with DNS.

> crontab -e

# start script every 15min to write into mysql database
*/15 * * * * /home/pi/avrio/avrio.py > crontab.log
*/15 * * * * /home/pi/avrio/avrio.py -b > crontab_remote.log

--------------------------------------------------------------
add 1-wire modules to /etc/modules so they will present after each reboot

sudo nano /etc/modules
# /etc/modules: kernel modules to load at boot time.
#
# This file contains the names of kernel modules that should be loaded
# at boot time, one per line. Lines beginning with "#" are ignored.
# Parameters can be specified after the module name.
snd-bcm2835
i2c-dev
w1-gpio
w1-therm

