#!/bin/sh

# Version 1.0
# MSU OIT 11/2010
# Marnin Goldberg and Turker Yasa

# Logout Script to notify the map database that this Mac is logged out.
# Calls cURL and POSTs the machine name and status of the machine (0 = User logged out) 

computername=`/usr/sbin/scutil --get LocalHostName`
server=http://server/labusage/labavailwriter.php 

curl --data "machine=$computername&mstatus=0" $server

exit 0
s