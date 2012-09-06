// Replace https://server with your server address 

<?php
require_once("../config.php");
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Play</title>
</head>
<body>
    <h2 title="Test" onclick="alert('test');">Play</h2>
    <style type="text/css">
        .box {
            width:200px; margin-bottom: 5px;
        }
        .free { 
            height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px;
        }
        .busy { 
            height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right;
        }        
        .title {
            font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555;
        }
        .labavail h4 {
            font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: bold; margin: 0 0 5px 0; padding: 0; letter-spacing: 1px; text-transform: uppercase;
        }        
    </style>
    <p class="labavail">
    </p>
    <script type="text/javascript" src="assets/js/jquery-min.js"></script>
    <script type="text/javascript">
        function loadLabAvailCharts() {
            $.getJSON('https://server/labusage/get-json.php?callback=?',
                function(data) {
                    $('p.labavail').empty();
                    $('p.labavail').append('<h4 style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: bold; margin: 0 0 5px 0; padding: 0; letter-spacing: 1px; text-transform: uppercase;">Lab Availability</h4>');
                    
                    // UN5007
                    var free = Math.round((data.UN5007.available/data.UN5007.total) * 100 );
                    var busy = 100 - free;
                    if(busy == 0) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div>');
                    } else if(busy == 100) {
                        $('p.labavail').append('<div style="width:200px;><div style="width:' + busy + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information"">' + busy + '%</div>');
                    } else {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div><div style="width:' + (busy - 1) + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information"">' + busy + '%</div>');
                    }
                    $('p.labavail').append('<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555; margin-bottom: 5px;">UN5007 (Desktops)</div>');
                    free = null; busy = null;
                    
                    // UN5008
                    var free = Math.round((data.UN5008.available/data.UN5008.total) * 100 );
                    var busy = 100 - free;
                    if(busy == 0) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div></div>');
                    } else if(busy == 100) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + busy + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    } else {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div><div style="width:' + (busy - 1) + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    }
                    $('p.labavail').append('<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555; margin-bottom: 5px;">UN5008 (Laptops)</div>');
                    free = null; busy = null;
                    
                    // UNCART
                    var free = Math.round((data.UNCART.available/data.UNCART.total) * 100 );
                    var busy = 100 - free;
                    if(busy == 0) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div></div>');
                    } else if(busy == 100) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + busy + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    } else {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div><div style="width:' + (busy - 1) + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    }
                    $('p.labavail').append('<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555; margin-bottom: 5px;">UNCART (Loaner Laptops)</div>');
                    free = null; busy = null;
                    
                    // CIRC
                    var free = Math.round((data.CIRC.available/data.CIRC.total) * 100 );
                    var busy = 100 - free;
                    if(busy == 0) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div></div>');
                    } else if(busy == 100) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + busy + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    } else {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div><div style="width:' + (busy - 1) + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    }
                    $('p.labavail').append('<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555; margin-bottom: 5px;">CIRC (Loaner Laptops)</div>');
                    free = null; busy = null;
                    
                    // SC004D
                    var free = Math.round((data.SC004D.available/data.SC004D.total) * 100 );
                    var busy = 100 - free;
                    if(busy == 0) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div></div>');
                    } else if(busy == 100) {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + busy + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    } else {
                        $('p.labavail').append('<div style="width:200px;"><div style="width:' + free + '%; height:17px; background-color:#009933; display: inline-block; border-right:1px #FFF solid; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + free + '%</div><div style="width:' + (busy - 1) + '%; height:17px; background-color:#cc0000;  display: inline-block; color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 3px; text-align: right; cursor:pointer;" onclick="window.open(\'https://server/labusage\');" title="Click for more information">' + busy + '%</div></div>');
                    }
                    $('p.labavail').append('<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #555; margin-bottom: 5px;">Surf-n-Print (Desktops)</div>');
                    free = null; busy = null;
                    $('p.labavail').append('<a href="#" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin-top: 1px; font-weight: bold; color: #990000; margin-bottom: 5px;">Click on chart for more info.</a>');
                }
            );            
        }
        $(document).ready(function() {
            loadLabAvailCharts();
            setInterval(function() { 
                    loadLabAvailCharts(); 
                }, 
                30000);
        });
    </script>
    
</body>
</html>