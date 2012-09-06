<?php
require_once("../../config.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Lab Usage - Administration and Reporting Console</title>
        
        <!-- CSS Styles -->
        <?php include('../css_styles.php'); ?>
        
    </head>
    <body>
        <div class="container">
            <!-- Header Start -->
            <div class="page-header">
                <?php include("header.php"); ?>
            </div>
            <!-- Header End -->
            
            <!-- Page -->
            <div class="row">
                <div class="span3">
                    <div style="padding: 8px 0;" class="well">
                        <?php include('leftnav.php'); ?>
                    </div>                    
                </div>
                <div class="span9">
                    <h2>Welcome !!</h2>
                    <p>Welcome to Administration and Reporting Console. Select a link from left side bar to begin.</p>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="footer">                
                <?php include("footer.php"); ?>
            </footer>
            <!-- End Footer -->            
        </div>
        
        <!-- Javascripts -->
        
    </body>
</html>

