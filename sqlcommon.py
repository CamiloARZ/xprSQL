#!/usr/bin/env python
#
# sqlcommon.py
# Description:  Common constants and functions used for -all- scripts of xprSql
#               protocol: sqlsend and sqlreceive
# Author:       Carlos Mella (carlos.mella@r9.cl)
#               R9 Ingenieria Ltda. (contactor9@r9.cl)
#               Albert Parra (aparra@r9.cl)
# Version:      1.1.0
# Date:         November 2012

import os
import sys

# For connection with Sql server, supported DBMS are:
#   - MySQL (mysql)
#   - Microsoft SQL (mssql)
#   - PostgreSQL (psql)
# The short name (the respective client application for connection) is given
#  in the cfg file for to use. The client must be installed on local. (TO DO: 
#  Alternative use for respectives modules of Python).
# Respective data of a DBMS used for protocol is set here

# getStrCnx returns a string for connection with a SQL server for get/send data
# The last parameter must be the option for give to continuation the SQL command

def getStrCnx(db):
    try:
        engine = db['engine']
        
        if engine == 'psql':        # PostgreSQL
            strCnx = "psql --tuples-only --host \"%s\" --port \"%s\" --username \"%s\" --dbname \"%s\" -c " % (
                        db['host'],
                        db['port'],
                        db['user'],
                        db['dbname']
                    ) 
        elif engine == 'mssql':     # MS SQLServer
            # strCnx = "/usr/bin/php /usr/airviro/bin/queryMSSQL.php --server \"%s\" -p \"%s\" --user \"%s\" --password \"%s\" -d \"%s\" -q " % (
            strCnx = "/usr/bin/php71 /usr/airviro/bin/queryMSSQL.php --server \"%s\" -p \"%s\" --user \"%s\" --password \"%s\" -d \"%s\" -q " % (
                        db['host'], 
                        db['port'], 
                        db['user'], 
                        db['password'], 
                        db['dbname']
                    )
        elif engine == 'mysql':     # MySQL
            strCnx = "mysql -A --skip-column-names --silent --connect_timeout=\"%s\" --host=\"%s\" --port=\"%s\" --user=\"%s\" --password=\"%s\" --database=\"%s\" -e " % (
                        db['connecttimeout'],
                        db['host'], 
                        db['port'], 
                        db['user'], 
                        db['password'], 
                        db['dbname']
                    )
        else:
            strCnx = ""
    except ValueError:
        quit("Don't possible to get parameters for some DB", CFGBAD)
    else:
        return strCnx

columnSeparator = {
    'psql'  : '|',
    'mssql' : ';',
    'mysql' : '\t'
}


# Methods for get data from protocol's configuration file
# For a correct use of getxprDictionary method, 'cmd' should include the
#  option '-l' for  getxpr for to get multiples values. For getxrpValue not.

def getxprDictionary(cmd):
    exe = os.popen(cmd).readlines()
    data = {}
    for line in exe:
        try:
            key, value = line.split(":", 1)
        except ValueError:
            quit("Don't possible to parse the line: %s" % line, CFGBAD)
        else:
            data[key] = value.strip()
            
    return data

def getxprValue(cmd):
    exe = os.popen(cmd).read()
    value = exe.strip()
    
    return value


# Possibles cases for exit code of the script, received by xprSql:
# Some cases are named and defined as the Cold does. Others are intern to this
#  protocol, and theirs interpretation for Cold is defined by xprSql script.
# Anyway, all cases can to change theirs interpretation in xprSql.
#
# GOOD CASES - Don't required a error message
#   GOOD:   Data Process OK
#   AGAIN:  Data Process partly OK
#   NODATA: No data for process, but without a fatal Error
#
# BAD CASES - Require a message for debug
#   CFGBAD: Data Process Failed by error in cfg file.
#           Retry is useless until to edit the cfg file.
#   STNBAD: Data Process Failed by temporal problem con SQL server.
#           Cold retries it for X tries.
#   XPRBAD: Problem of access to files, programs, or an hypothetic case of bad
#           structure on airviro file. Intern to this protocol. Retry is useless
#           until to fix the situation.
GOOD = 0
AGAIN = 1
NODATA = 2
CFGBAD = 3
STNBAD = 4
XPRBAD = 5

haveNoFatalError = False               # Say if exit code must be GOOD or AGAIN

# Messages of problems, for log file
def toLogCommon(msg, showFile = True, line = None):
    if showFile is True:
        sys.stderr.write("\tFilename: %s\n" % os.path.basename(__file__))
    if line is not None:
        sys.stderr.write("\tLine number: %s\n" % line)
    sys.stderr.write("%s\n" % msg)

def error(msg):
    global haveNoFatalError
    haveNoFatalError = True
    toLogCommon("ERROR: %s" % msg)

# Exit from script with correct exit Code and message for xprSql (and Cold)
def quit(msg=None, exitCode=GOOD):
    if exitCode == GOOD and haveNoFatalError:
        exitCode = AGAIN
    
    if msg is not None:
        toLogCommon("EXIT FOR ERROR: %s" % msg)
        sys.stdout.write("%s" % msg)    # Message for xprSql
    
    sys.exit(exitCode)
    


# Method for to write some data to a file

def toFile(fileName, mode, data):
    try:
        avFile = open(fileName, mode)
    except OSError, (errno, strerror):
        quit("Don't possible to create the file: %s" % fileName, XPRBAD)
    else:
        avFile.write(data)
        avFile.close()


# Define the character that specifies which time resolution the data has

avTimeRes = {
    '60' : ',',
    '300' : 'f',
    '600' : 't',
    '900' : 'q',
    '1200' : 'i',
    '1800' : 's',
    '3600' : '+',
    '86400' : '*'
}
avResToTimeRes = {
    ',' : '60',
    'f' : '300',
    't' : '600',
    'q' : '900',
    'i' : '1200',
    's' : '1800',
    '+' : '3600',
    '*' : '86400'
}

# Define jokers which replace data in SQL commands

cfgDataCtrl = {
    'time'   : '@TIME@',
    'etime'  : '@ETIME@', 
    'value'  : '@VALUE@',
    'status' : '@STATUS@'
}

cfgSentDataCtrl = [ '@TIME@', '@VALUE@', '@STATUS@' ]


# ****************************************************************************


if __name__ == "__main__":
    print "sqlcommon.py\n\
Shared constants and functions used for scripts of xprSql protocol"
