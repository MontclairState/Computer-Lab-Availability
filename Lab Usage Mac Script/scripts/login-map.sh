#!/bin/sh

# Version 1.0
# MSU OIT 11/2010
# Marnin Goldberg and Turker Yasa

# Login Script to notify the map database that this Mac is logged in.
# Calls cURL and POSTs the machine name and status of the machine (1 = User logged in) 

computername=`/usr/sbin/scutil --get LocalHostName`
server=http://server/labusage/labavailwriter.php 

curl --data "machine=$computername&mstatus=1" $server

exit 0
