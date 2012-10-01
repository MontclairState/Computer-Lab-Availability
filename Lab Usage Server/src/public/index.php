<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>MSU Lab Usage</title>
        <meta name="description" content="Lab Usagae Web Application">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- CSS Styles -->
        <?php include("css_styles.php"); ?>
                
        <style type="text/css">
            div.lab-info {
                margin-bottom: 20px;
            }
            div.lab-info h3 {
                line-height: 1.1em;
                margin-bottom: 10px;
            }
            div.lab-info h2 {
                font-size: 1.6em;
            }
            div.lab-info p {
                line-break: 1.1em;
                margin-bottom: 5px;
            }
            div.graph-annot {
                text-align: right;
                margin-bottom: 15px;
            }
            div.graph-annot p {
                line-break: 1.1em;
            }
            .avail_detail_head {
                color: #009933;
                font-weight: bold;
            }
            .bold_text { font-weight: bold; }

            .avail_color { color: #009933; }
            .occup_color { color: #cc0000; }
            .maint_color { color: #696969; }
            .left_align { text-align: left; }
            .right_align { text-align: right; }
            
            .chart_container { width: 120px; height: 120px; float: left; margin:0 5px 0 0; padding:0; }
            
        </style>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">        
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <a href="#" class="pull-right margin_top_10" title="Montclair State University"><img src="assets/img/msu-logo.png" alt="MSU Logo"/></a>
                <h1>MSU Lab Usage<br /><small>Updates every 30 seconds</small></h1>                
            </div>
            <!-- End Header -->

            <div class="row hide error_while_loading">
                <div class="span11 alert alert-error">
                    <h3 class="margin_bottom_10">Error !!</h3>
                    <p>An error occured while loading lab status.</p>
                    <p>Please contact the system administrator.</p>
                </div>
            </div>
            <!-- Legends for charts -->
            <div class="row margin_bottom_10 hide legend">
                <div class="span6 offset6 right_align" style="font-size: 1.1em;">
                    <p><div style="width: 12px; height: 12px; display: inline-block; background-color: #009933; margin: 0 2px 0 0; padding: 0;"></div> Available <div style="width: 12px; height: 12px; display: inline-block; background-color: #cc0000; margin: 0 2px 0 20px; padding: 0;"></div> Occupied <div style="width: 12px; height: 12px; display: inline-block; background-color: #696969; margin: 0 2px 0 20px; padding: 0;"></div> Maintenance</p>
                </div>
            </div>
            <!-- End legends -->

            <!-- Pie charts -->
            <div class="row" id="lab-charts">
            </div>            
            <!-- End Pie charts -->
            
            <!-- Footer -->
            <footer class="footer margin_top_25">                
                <p class="pull-right">v 2.0</p>
                <p>&copy; <a href="http://www.montclair.edu/oit" target="_blank">Information Technology</a>, Montclair State University </p>
            </footer>
            <!-- End Footer -->
        </div>

        <!-- Javascripts at end so that page loads faster -->
        <script type="text/javascript" src="<?php print(_WEB_PATH); ?>/assets/js/jquery-min.js"></script>
        <script type="text/javascript" src="<?php print(_WEB_PATH); ?>/assets/js/bootstrap-modal.js"></script>
        <script type="text/javascript" src="<?php print(_WEB_PATH); ?>/assets/js/highcharts/highcharts.js"></script>
        <script type="text/javascript" src="<?php print(_WEB_PATH); ?>/assets/js/app.js"></script>
    </body>
</html>