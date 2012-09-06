::Version 1

@ECHO off
:: Use cURL to send a POST request to the server

::Sets the server URL where the script to update the database resides
SET server_url=http://server/labusage/labavailwriter.php 


::Calls cURL and POSTs the machine name and status of the machine (1 = User logged in) 
::Make sure to target curl.exe properly if it does not reside in root.
TITLE Machine Status (Login)
C:\WINDOWS\system32\GroupPolicy\User\Scripts\Logon\curl --data "machine=%COMPUTERNAME%&mstatus=1" %server_url%
