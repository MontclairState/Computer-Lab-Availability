<?php
require_once("../../config.php");

$lab = filter_input(
            INPUT_GET, 
            'lab', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[A-Za-z0-9_\-]+$/"))
        );
$type = filter_input(
            INPUT_GET, 
            'type', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[A-Za-z_]+$/"))
        );
$date = filter_input(
            INPUT_GET, 
            'date', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/"))
        );
$month = filter_input(
            INPUT_GET, 
            'month', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^(\d{2})$/"))
        );
$year = filter_input(
            INPUT_GET, 
            'month', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^(\d{4})$/"))
        );

// If paramaters are not set, redirect to index.php
if(empty($lab)) {
    header("Location: index.php");
    exit();
} else if($lab !== "all") {
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();

    $labObj = new Lab();
    $labFound = $labObj->find($db, array("conditions" => array("name" => $lab)));
    if($labFound == null || count($labFound) == 0) {
        header("Location: index.php");
        $labFound = null;
        $labObj = null;
        $db->close();
        $db = null;
        exit();
    } else {
        $lab = $labFound[0]->name;
    }
    $labFound = null;
    $labObj = null;
    $db->close();
    $db = null;
}

if(empty($type)) {
    $type = "day";
} else if(!in_array($type, array("day", "week", "month", "custom", "comp_usage"), true)) {
    header("Location: index.php");
    exit();
}

if(empty($date)) {
    $date = date("Y-m-d");    
}

if(empty($month)) {
    $month = date("n", strtotime($date));
}

if(empty($year)) {
    $year = date("Y", strtotime($date));
}

// Used in leftnav.php for storing lab listings
$labList = array();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Usage report for <?php echo($lab); ?></title>
        
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
                 
                    <div class="panel">					
                        <div class="panel-header">
                            <i class="icon-signal"></i>
                            <?php
                            if($lab === "all") {
                                print("<h3>All Labs <small>Combine report of all labs</small></h3>");
                            } else {
                                foreach($labList as $l) {
                                    if($l->name == $lab) {
                                        if(empty($l->title)) {
                                            print("<h3>" . $l->name . " <small>" . $l->description . "</small></h3>");
                                        } else {
                                            print("<h3>" . $l->title . " <small>" . $l->description . "</small></h3>");
                                        }
                                    }
                                    $l = null;
                                }
                            }
                            ?>
                            
                        </div>
                        <div class="panel-content">
                            <div class="tabbable">
                                <ul class="nav nav-tabs">
                                    <li <?php if($type === 'day') echo("class=\"active\""); ?>><a href="#day_tab" data-toggle="tab">Daily</a></li>
                                    <li <?php if($type === 'week') echo("class=\"active\""); ?>><a href="#week_tab" data-toggle="tab">Weekly</a></li>
                                    <li <?php if($type === 'month') echo("class=\"active\""); ?>><a href="#month_tab" data-toggle="tab">Monthly</a></li>
                                    <li <?php if($type === 'custom') echo("class=\"active\""); ?>><a href="#custom_tab" data-toggle="tab">Custom</a></li>
                                    <li <?php if($type === 'comp_usage') echo("class=\"active\""); ?>><a href="#comp_usage_tab" data-toggle="tab">Computer Usage</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane <?php if($type === 'day') echo("active"); ?>" id="day_tab">
                                        <form id="day_form" class="form-horizontal" style="border-bottom: 1px solid #ccc" onsubmit="return false;" autocomplete="off">
                                            <div class="control-group">
                                                <label class="control-label" for="daypicker" style="width: 100px;">Change the date: </label>
                                                <div class="controls" style="margin-left: 120px;">
                                                    <div class="input-append">
                                                        <input id="daypicker" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value="<?php echo($date) ;?>"/><span class="add-on" onclick="$('#daypicker').datepicker('show');"><i class="icon-calendar"></i></span>
                                                    </div>
                                                    <button class="btn margin_left_5 btn-primary" onclick="generateDayChart('<?php echo($lab); ?>', $('#daypicker').val())">Submit</button>
                                                    
                                                    <a class="btn pull-right btn-success" id="daily-report-download" href="#"><i class="icon-download icon-white"></i> Download</a>
                                                </div>
                                            </div>
                                        </form>
                                        <div id="day_chart">
                                        </div>
                                    </div>
                                    <div class="tab-pane <?php if($type === 'week') echo("active"); ?>" id="week_tab">
                                        <form id="week_form" class="form-horizontal" style="border-bottom: 1px solid #ccc" onsubmit="return false;" autocomplete="off">
                                            <div class="control-group">
                                                <label class="control-label" for="weekpicker" style="width: 170px;">Change the day of the week: </label>
                                                <div class="controls" style="margin-left: 190px;">
                                                    <div class="input-append">
                                                        <input id="weekpicker" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value="<?php echo($date) ;?>"/><span class="add-on" onclick="$('#weekpicker').datepicker('show');"><i class="icon-calendar"></i></span>
                                                        <input id="startdate" name="startdate" type="hidden" value="" />
                                                        <input id="enddate" name="enddate" type="hidden" value="" />
                                                    </div>                                                
                                                    <button class="btn btn-primary margin_left_5" onclick="generateWeekChart('<?php echo($lab); ?>', $('#startdate').val(), $('#enddate').val())">Submit</button>
                                                    <a class="btn pull-right btn-success" id="weekly-report-download" href="#"><i class="icon-download icon-white"></i> Download</a>
                                                    <p class="help-block" id="weekhelptext"></p>
                                                </div>                                            
                                            </div>
                                        </form>
                                        <div id="week_chart">
                                        </div>
                                    </div>
                                    <div class="tab-pane <?php if($type === 'month') echo("active"); ?>" id="month_tab">
                                        <form id="month_form" class="form-horizontal" style="border-bottom: 1px solid #ccc" onsubmit="return false;" autocomplete="off">
                                            <div class="control-group">
                                                <label class="control-label" for="month" style="width: 90px;">Select month: </label>
                                                <div class="controls" style="margin-left: 110px;">
                                                    <select name="month" id="month" class="input-medium">
                                                        <option value="1"  <?PHP if($month==1) echo "selected";?>>January</option>
                                                        <option value="2"  <?PHP if($month==2) echo "selected";?>>February</option>
                                                        <option value="3"  <?PHP if($month==3) echo "selected";?>>March</option>
                                                        <option value="4"  <?PHP if($month==4) echo "selected";?>>April</option>
                                                        <option value="5"  <?PHP if($month==5) echo "selected";?>>May</option>
                                                        <option value="6"  <?PHP if($month==6) echo "selected";?>>June</option>
                                                        <option value="7"  <?PHP if($month==7) echo "selected";?>>July</option>
                                                        <option value="8"  <?PHP if($month==8) echo "selected";?>>August</option>
                                                        <option value="9"  <?PHP if($month==9) echo "selected";?>>September</option>
                                                        <option value="10" <?PHP if($month==10) echo "selected";?>>October</option>
                                                        <option value="11" <?PHP if($month==11) echo "selected";?>>November</option>
                                                        <option value="12" <?PHP if($month==12) echo "selected";?>>December</option>
                                                    </select>
                                                    <select name="year" id="year" class="input-small">
                                                        <?PHP for($i=$year; $i>=2011; $i--)
                                                            if($year == $i) {
                                                                echo "<option value='$i' selected>$i</option>";
                                                            } else {
                                                                echo "<option value='$i'>$i</option>";
                                                            }
                                                        ?>
                                                    </select>                                                
                                                    <button class="btn btn-primary margin_left_5" onclick="generateMonthChart('<?php echo($lab); ?>', $('#month').val(), $('#year').val() )">Submit</button>
                                                    <a class="btn btn-success pull-right" id="monthly-report-download" href="#"><i class="icon-download icon-white"></i> Download</a>
                                                </div>
                                            </div>
                                        </form>
                                        <div id="month_chart">
                                        </div>
                                    </div>
                                    <div class="tab-pane <?php if($type === 'custom') echo("active"); ?>" id="custom_tab">
                                        <form id="custom_form" class="form-inline padding_bottom_20" style="border-bottom: 1px solid #ccc" onsubmit="return false;" autocomplete="off">
                                            <div id="cust_range_alert" class="alert alert-error hide">
                                                <strong>Error!</strong>
                                            </div>
                                            <label class="control-label margin_right_5" for="custom_from">From: </label>
                                            <div class="input-append">
                                                <input id="custom_from" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value=""/><span class="add-on" onclick="$('#custom_from').datepicker('show');"><i class="icon-calendar"></i></span>
                                            </div>

                                            <label class="control-label margin_right_5 margin_left_20" for="custom_to">To: </label>
                                            <div class="input-append">
                                                <input id="custom_to" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value=""/><span class="add-on" onclick="$('#custom_to').datepicker('show');"><i class="icon-calendar"></i></span>
                                            </div>

                                            <button class="btn btn-primary margin_left_5" id="custom_submit" onclick="generateCustomChart('<?php echo($lab); ?>', $('#custom_from').val(), $('#custom_to').val() )">Submit</button>
                                            <a class="btn btn-success pull-right" id="custom-report-download" href="#" ><i class="icon-download icon-white"></i> Download</a>                                        
                                        </form>
                                        <div id="custom_chart">
                                        </div>                                    
                                    </div>
                                    <div class="tab-pane <?php if($type === 'comp_usage') echo("active"); ?>" id="comp_usage_tab">
                                        <form id="comp_usage_form" class="form-horizontal padding_bottom_20" style="border-bottom: 1px solid #ccc" onsubmit="return false;" autocomplete="off">
                                            <div id="comp_usage_alert" class="alert alert-error hide">
                                                <strong>Error!</strong>
                                            </div>
                                            
                                            <a class="btn btn-success pull-right" id="compusage-report-download" ><i class="icon-download icon-white"></i> Download</a>
                                            <div class="control-group">
                                                <label class="control-label" for="month" style="width: 130px;">Select computer: </label>
                                                <div class="controls" style="margin-left: 150px;">
                                                    <select name="comp_name" id="comp_name" class="input-medium">
                                                    <?php
                                                        
                                                        $db = new Db($env[_APP_ENV]['db']);
                                                        $db->connect();
                                                        
                                                        $comp = new Computer();
                                                        $computers = array();
                                                                                                                
                                                        if($lab === "all") {
                                                            $computers = $comp->find($db, array("order" => array("name" => "asc")));
                                                        } else {
                                                            $computers = $comp->find($db, array("conditions" => array("lab" => $lab), 
                                                                                                "order" => array("name" => "asc")));
                                                        }
                                                        foreach($computers as $c) {
                                                            echo("<option value=\"" . $c->name . "\">" . $c->name . "</option>");
                                                            $c = null;
                                                        }
                                                        $computers = null;
                                                        $comp = null;
                                                        $db->close();
                                                        $db = null;
                                                    ?>                                                    
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" style="width: 130px;">Select date or range: </label>
                                                <div class="controls" style="margin-left: 150px;">
                                                    <div class="margin_bottom_10">
                                                        <input type="radio" checked="checked" value="day" id="cu_option_date" name="cu_option" style="vertical-align: top;" onclick="enableCUOption('date');" />
                                                        <span style="cursor: pointer;" onclick="enableCUOption('date');">Date: </span>
                                                        <div class="input-append">
                                                            <input id="comp_usage_date" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value="<?php echo($date) ;?>"/><span class="add-on" id="comp_usage_date_addon"><i class="icon-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="margin_bottom_10">
                                                        <input type="radio" value="month" id="cu_option_month" name="cu_option" style="vertical-align: top;" onclick="enableCUOption('month'); ">
                                                        <span style="cursor: pointer;" onclick="enableCUOption('month');">Month: </span>
                                                        <select name="comp_usage_month" id="comp_usage_month" class="input-medium">
                                                            <option value="1"  <?PHP if($month==1) echo "selected";?>>January</option>
                                                            <option value="2"  <?PHP if($month==2) echo "selected";?>>February</option>
                                                            <option value="3"  <?PHP if($month==3) echo "selected";?>>March</option>
                                                            <option value="4"  <?PHP if($month==4) echo "selected";?>>April</option>
                                                            <option value="5"  <?PHP if($month==5) echo "selected";?>>May</option>
                                                            <option value="6"  <?PHP if($month==6) echo "selected";?>>June</option>
                                                            <option value="7"  <?PHP if($month==7) echo "selected";?>>July</option>
                                                            <option value="8"  <?PHP if($month==8) echo "selected";?>>August</option>
                                                            <option value="9"  <?PHP if($month==9) echo "selected";?>>September</option>
                                                            <option value="10" <?PHP if($month==10) echo "selected";?>>October</option>
                                                            <option value="11" <?PHP if($month==11) echo "selected";?>>November</option>
                                                            <option value="12" <?PHP if($month==12) echo "selected";?>>December</option>
                                                        </select>
                                                        <select name="comp_usage_year" id="comp_usage_year" class="input-small">
                                                            <?PHP for($i=$year; $i>=2011; $i--)
                                                                if($year == $i) {
                                                                    echo "<option value='$i' selected>$i</option>";
                                                                } else {
                                                                    echo "<option value='$i'>$i</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="margin_bottom_10">
                                                        <input type="radio" value="range" id="cu_option_range" name="cu_option" style="vertical-align: top;" onclick="enableCUOption('range'); ">
                                                        <span class="margin_right_5" style="cursor: pointer;" onclick="enableCUOption('range');">Range: </span>
                                                        <div class="input-append">
                                                            <input id="comp_usage_from" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value=""/><span class="add-on" id="comp_usage_from_addon"><i class="icon-calendar"></i></span>
                                                        </div>
                                                        
                                                        <span class="margin_left_10 margin_right_5">To: </span>
                                                        <div class="input-append">
                                                            <input id="comp_usage_to" class="input-small" type="text" data-date-format="yyyy-mm-dd" data-date-autoclose="true" value=""/><span class="add-on" id="comp_usage_to_addon"><i class="icon-calendar"></i></span>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="controls" style="margin-left: 150px; ">
                                                    <button class="btn btn-primary" id="comp_usage_submit" onclick="generateCUChart($('#comp_name').val(), $('input:radio[name=cu_option]:checked').val(), $('#comp_usage_date').val(), $('#comp_usage_month').val(), $('#comp_usage_year').val(), $('#comp_usage_from').val(), $('#comp_usage_to').val())">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div id="cu_chart">
                                        </div>                                    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="footer">                
                <?php include("footer.php"); ?>
            </footer>
            <!-- End Footer -->            
        </div>
        
        <!-- Javascripts -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/jquery-min.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-tab.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-alert.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-datepicker.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/highcharts/highcharts.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/highcharts/modules/exporting.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/report.js"></script>
        <script type="text/javascript">
            var lab = "<?php echo($lab); ?>";
            var type = "<?php echo($type); ?>";
            var date = "<?php echo($date); ?>";
            
            var weekDatesForDate = getWeekStartAndEndDates(new Date(date));
            $("#weekhelptext").text("Week of " + formateDate(weekDatesForDate[0]) + " to " + formateDate(weekDatesForDate[1]));
            $("#startdate").val(formateDate(weekDatesForDate[0]));
            $("#enddate").val(formateDate(weekDatesForDate[1]));
            weekDatesForDate = null;
        </script>
    </body>
</html>

<?php
$labList = null;
?>