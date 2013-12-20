#!/usr/bin/python
# -*- coding: utf-8 -*-
#
# 2013-04-19 V0.5 by Thomas Hoeser
#
#Embedded file name: /home/pi/avrio/avrio_database.py
import MySQLdb
import sys
import datetime
import time

#-------------------------------------------------------------------------------------------
def create_database(db_all, db_fields_all, verbose_level):

  db_Database = db_all[1]
  db_table = db_all[0]
  db_Host = db_all[2]
  db_User = db_all[3]
  db_Password = db_all[4]

  # db_table = db_table + 'test'
  db_connect(db_all,1,verbose_level)

  # create database avrio ;
  # grant usage on *.* to root@localhost identified by ‘nxt2008’;    
  # grant all privileges on avrio.* to root@localhost ;
  print 'CREATE DATABASE', db_Database
  var = raw_input('Enter yes: ')
  if var == 'yes':  
    stmt = 'CREATE DATABASE IF NOT EXISTS ' + db_Database
    cur = db.cursor()
    if verbose_level>1:
          print stmt
    cur.execute(stmt)
    db.commit()

  db_connect(db_all,0,verbose_level)
  
  print 'DROP TABLE', db_table
  var = raw_input('Enter yes: ')
  if var == 'yes':  
    stmt = 'DROP TABLE IF EXISTS ' + db_table
    cur = db.cursor()
    if verbose_level>0:
          print stmt
    try:
      cur.execute(stmt)
      db.commit()
    except:
      print 'Error class:', sys.exc_info()[0]
      print 'Error code :', sys.exc_info()[1]
    
    
  print 'CREATE TABLE', db_table
  var = raw_input('Enter yes: ')
  if var == 'yes':  
    stmt = 'CREATE TABLE ' + db_table + ' ('
    stmt = stmt + 'id INT NOT NULL AUTO_INCREMENT PRIMARY KEY'
    stmt = stmt + ', dattim TIMESTAMP'
    dbf = db_fields_all.split()
    for x in range(0,len(dbf)):
      x = dbf[x]
      x = x.split(',')
      stmt = stmt + ', ' + x[0] + " FLOAT DEFAULT '0'"
    stmt = stmt + ' )'
    cur = db.cursor()
    if verbose_level>0:
          print stmt
    try:
      cur.execute(stmt)
      db.commit()
    except:
      print 'Error class:', sys.exc_info()[0]
      print 'Error code :', sys.exc_info()[1]
    
# CREATE TABLE xxx (
#         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
#         data VARCHAR(100),
#         income FLOAT , 
#         cur_timestamp TIMESTAMP(8)
#       );

    return ()
#-------------------------------------------------------------------------------------------
def kill_dbentries(db_all, verbose_level):
    db_table = db_all[0]
    db_Database = db_all[1]
    print 'DELETE ALL ENTRIES FROM DATABASE', db_Database
    var = raw_input('Enter yes: ')
    if var == 'yes':
        db_connect(db_all,0,verbose_level)
        stmt = 'DELETE FROM ' + db_table + ' WHERE 1'
        cur = db.cursor()
        if verbose_level>1:
            print stmt
        cur.execute(stmt)
        db.commit()
    else:
        print 'request cancelled'
    sys.exit(0)
    return ()


#-------------------------------------------------------------------------------------------
def read_records(db_lines,db_all, db_fields_all, verbose_level):
    db_Table = db_all[0]
    db_Database = db_all[1]
    db_Host = db_all[2]
    db_User = db_all[3]
    db_Password = db_all[4]
    db_connect(db_all,0,verbose_level)
    dbval = 0

    if verbose_level>0:print "show last "+ str(db_lines) +" record(s) from database"
    stmt = 'SELECT UNIX_TIMESTAMP(dattim), ' + db_fields_all + ' FROM ' + db_Table + ' ORDER BY dattim DESC LIMIT ' + str(db_lines)
    cur = db.cursor()
    if verbose_level>1: print stmt
    
    try:
      dbval = cur.execute(stmt)
    except:
      print 'PANIC - cannot read from database table'
      print 'Error class:', sys.exc_info()[0]
      print 'Error code :', sys.exc_info()[1]
      print 'host       :', db_Host
      print 'user       :', db_User
      print 'passwd     :', db_Password
      print 'database   :', db_Database
      print 'table      :', db_Table
      sys.exit(2)
    
    if dbval>0:        
      if verbose_level>0: 
        print 'Datum                          Aussn Innen Zimme Balko Wasse WW_Sp Vorla Rueckl'
        print '-------------------------------------------------------------------------'
        for row in cur:
          datetimestring = datetime.datetime.fromtimestamp(row[0])
          if verbose_level>0: print datetimestring, '%d %.2f %.2f %.2f %.2f %.2f %.2f %.2f %.2f' % row  
      return row
    else:
      print "no records retrieved from database"   
      return 0

#-------------------------------------------------------------------------------------------
def write_database(read_items, sensordata, sensor_dict, db_all, verbose_level):
    data_temp = []
    comb1 = []
    comb2 = []
    Val_String = ''
    
    db_Table = db_all[0]
    db_Database = db_all[1]
    db_Host = db_all[2]
    db_User = db_all[3]
    db_Password = db_all[4]
    db_connect(db_all,0,verbose_level)
    
    if verbose_level>1: print "read_items: " , read_items
    for x in range(0, read_items):
        dataset = sensordata[x]
        sen_loc = str(sensor_dict.get(dataset[0]))
        if verbose_level>1:
            print dataset, '-> Sensor ID:', dataset[0], 'temp.: ', dataset[1], 'location:', sen_loc
        if sen_loc == 'None':
            print 'PANIC: unknown sensor ', dataset[0]
        elif x == 0:
            Sen_String = sen_loc
            Val_String = '%s'
            data_temp.append(dataset[1])
            comb1.append(dataset[1])
        else:
            Sen_String = Sen_String + ', ' + str(sensor_dict.get(dataset[0]))
            Val_String = Val_String + ', %s'
            data_temp[0] = (data_temp[0], dataset[1])
            comb1.append(dataset[1])

    comb2.append(comb1)
    stmt = 'INSERT INTO ' + db_Table + ' (' + Sen_String + ') VALUES (' + Val_String + ')'
    if verbose_level>1: print stmt
    if verbose_level>1: print comb2
    cur = db.cursor()
    cur.executemany(stmt, comb2)
    db.commit()
    return (0)

#-------------------------------------------------------------------------------------------
def clean_records(db_lines,db_all, db_fields_all, verbose_level):
	db_Table    = db_all[0]
	db_Database = db_all[1]
	db_Host     = db_all[2]
	db_User     = db_all[3]
	db_Password = db_all[4]
	db_connect(db_all,0,verbose_level)
	dbval       = 0
	db_rows     = 0
	db_array3    =[]
	db_array2    =[]
	db_array1    =[]
	db_array_update =[0,0,0,0,0,0,0,0,0,0,0,0]
	update_flag = 0
	TempVar1 = 0
	TempVar2 = 0
	TempVar3 = 0
         
	if verbose_level>0:print "clean last "+ str(db_lines) +" record(s) from database"
	stmt = 'SELECT id, UNIX_TIMESTAMP(dattim), ' + db_fields_all + ' FROM ' + db_Table + ' ORDER BY dattim DESC LIMIT ' + str(db_lines)
	cur = db.cursor()
	if verbose_level>2: print stmt
    
	try:
		dbval = cur.execute(stmt)
	except:
		print 'PANIC - cannot read from database table'
		print 'Error class:', sys.exc_info()[0]
		print 'Error code :', sys.exc_info()[1]
		print 'host       :', db_Host
		print 'user       :', db_User
		print 'passwd     :', db_Password
		print 'database   :', db_Database
		print 'table      :', db_Table
		sys.exit(2)
    
	db_rows = cur.rowcount
	if verbose_level > 2: print "rows found: ", db_rows
	if db_rows < 3:        
		print "not enough records retrieved from database" 
		return(0)
	print "read items from database"
	lineno = 0	
	for row in cur:
		if verbose_level > 2: print " check line ", lineno
		if verbose_level > 3:  print row
		datetimestring = datetime.datetime.fromtimestamp(row[0])
		if verbose_level > 2: print datetimestring, '%d %d %.2f %.2f %.2f %.2f %.2f %.2f %.2f %.2f' % row
		if lineno > 2: db_array3 = db_array2
		if lineno > 1: db_array2 = db_array1
		db_array1 = row
		# Need three lines to have comparison data
		if lineno > 2:
			if verbose_level>2: print "three lines read "
			#	print "3: " , db_array3
			#	print "2: " , db_array2
			#	print "1: " , db_array1
			# skip item 0 = id, 1 = datetime  
			# read every value of current row
			db_array_update[0] = db_array3[0]
			db_array_update[1] = db_array3[1]
			for x in range(2,10):
				db_array_update[x] = db_array2[x]
				# when middle value is 0, check value after and before 
				if ( db_array2[x] == 0 ):
					if ( db_array3[x] != 0 ):
						if ( db_array1[x] != 0 ):
							if verbose_level>1:
								print " ZERO entry found; substitute with ", db_array3[x]
							db_array_update[x] = db_array3[x]
							update_flag = 1
					# end of 0 check			
				else:
					TempVar1 = db_array2[x] - db_array1[x]
					TempVar3 = db_array3[x] - db_array2[x]
					# x1   x2   x3
					# 20 - 15 - 21
					# V1 = 15 - 20 = -5
					# V2 = 21 - 15 = 6
					glitch_val = 10
					# print TempVar1
					if ( (TempVar1 < 0) & (abs(TempVar1) > glitch_val) ):
						if verbose_level>1: print "glitch for id ",db_array2[0],"1: ", TempVar1, " = ", db_array1[x], " / ", db_array2[x], " / ", db_array3[x]
						if ( (TempVar3 > 0) & (abs(TempVar3) > glitch_val) ):
							print "glitch 2: ", TempVar3, " = ", db_array1[x], " - ", db_array2[x], " / ", db_array3[x]      
							db_array_update[x] = db_array3[x]
							update_flag = 1
					# end of glitch
				# end of for - all fields read	
			if ( update_flag == 1):
				db_array_update[0] = db_array2[0]
				if verbose_level>1:print "update ", db_array2
				if verbose_level>1:print "with   ", db_array_update
				# print db_fields_all
				db_fields_= db_fields_all.split(',')
				sql_update = "UPDATE " + db_Table + " SET "
				for y in range(0,8):
					sql_update = sql_update + db_fields_[y] + "=" + str(db_array_update[y+2])
					if y <7: sql_update = sql_update + ", "
				sql_update = sql_update + " WHERE id=" + str(db_array_update[0])
				if verbose_level>1: print sql_update
				curu = db.cursor()
				#curu.execute(sql_update)
				db.commit()
				update_flag = 0
		lineno = lineno + 1
	# end of for / rows
	return(db_rows)
#
#
# UPDATE [LOW_PRIORITY] [IGNORE] tbl_name
#    SET col_name1=expr1 [, col_name2=expr2 ...]
#    [WHERE where_condition]
#
#-------------------------------------------------------------------------------------------
def db_connect(db_all,nodb,verbose_level):

    global db
    db_table    = db_all[0]
    db_Database = db_all[1]
    db_Host     = db_all[2]
    db_User     = db_all[3]
    db_Password = db_all[4]

    try:
          if(nodb):
            print "connect to host >" , db_Host, "< w/o database"
            db = MySQLdb.connect(host=db_Host, user=db_User, passwd=db_Password)
          else:
            print "connect to host >" , db_Host, "< and database >", db_Database, "<"
            db = MySQLdb.connect(host=db_Host, user=db_User, passwd=db_Password, db=db_Database)
          
    except:
          print 'PANIC - cannot connect to database'
          print 'Error class:', sys.exc_info()[0]
          print 'Error code :', sys.exc_info()[1]
          print 'host       :', db_Host
          print 'user       :', db_User
          print 'passwd     :', db_Password
          print 'database   :', db_Database
          sys.exit(2)

    return (0)

version = '0.4'
#+++ okay decompyling avrio_database.pyc 
# decompiled 1 files: 1 okay, 0 failed, 0 verify failed
# 2013.03.03 08:57:17 CET
