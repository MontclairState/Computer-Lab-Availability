Computer-Lab-Availability
=========================

<b>INTRODUCTION</b>
<p>
Computer Lab Availability is a web-based system designed to provide real-time computer availability information for computers in a lab environment.
A public facing Computer Lab Availability site is live and can be found at https://oit-app2.montclair.edu/labusage
<br>
The Administrator side can been seen in the <a href="https://github.com/MontclairState/Computer-Lab-Availability/tree/master/Screenshots">screenshots folder</a>

<b>FEATURES AND CAPABILITIES</b>
<p>
Computer Lab Availability features an attractive, mobile compatible website that gives users the ability to quickly review which computer labs have available computers. Users can click on details button next to each lab to view exactly which computers are available.
<p>
The system is compatible with both Windows and Mac computers. 
The lab computers use Login and Logout Hooks with curl to update a database that displays the computer status.
On the Administrator side, the system features and easy to use ajax interface that allows for configuring the labs, edit computers status (setting a computer to maintenance mode) and pull detailed usage reports. 

<b>COMPONENTS AND CREDITS</b>
<p>
Computer Lab Availability was developed using the following projects:
<br>
<a href="http://httpd.apache.org">Apache Server</a>
<br>
<a href="http://php.net">PHP</a>
<br>
<a href="http://mysql.com">MySQL</a>
<br>
<a href="http://twitter.github.com/bootstrap/">Twitter Bootstrap</a>
<br>
<a href="http://curl.haxx.se">Curl</a>

The Computer Lab Availability team: Dhaval Patel, Marnin Goldberg, Viktor Turchyn, Summer Jones and Turker Yasa (depreciated)

<b>SETUP AND REQUIREMENTS</b>
<p>
Directions for server and client setup can be found in their respective folders

<b>LIMITATIONS AND DEPENDENCIES</b>
<p>
Computer Lab Availability currently relies on the command-line "curl" binary to upload the computer status. Curl is available on OS X and Windows (32bit and 64bit) versions. See http://curl.haxx.se for more information.

<b>LICENSE</b>
<p>
Copyright 2012 Montclair State University, Office of Information Technology

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.