<?php
require_once("../../config.php");
// Used in leftnav.php for storing lab listings
$labList = array();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Lab Usage - Lab Administration</title>
        
        <!-- CSS Styles -->
        <?php include('../css_styles.php'); ?>
        <style type="text/css">
            .table td {
                vertical-align: middle;
            }
            div.panel-header h3 {
                font-size: 1.1em;
            }
        </style>
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
                            <i class="icon-cog"></i>
                            <h3>Labs Admin</h3>
                        </div>
                        <div class="panel-content">
                            <div class="tabbable">
                                <button class="btn pull-right btn-small" onclick="loadList();" id="refresh_data_button"><i class="icon-refresh"></i> Refresh</button>
                                <ul class="nav nav-tabs" id="tabs">
                                    <li class="active"><a href="#list_tab" data-toggle="tab" id="list_tab_link">List</a></li>
                                    <li><a href="#addedit_tab" data-toggle="tab" id="addedit_tab_link">Add / Edit</a></li>
                                </ul>
                                
                                <div class="tab-content">
                                    <div class="tab-pane active" id="list_tab">
                                        <table class="table table-striped table-bordered" 
                                                id="lab_list">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th class="span2">Name</th>
                                                    <th class="span2">Title</th>
                                                    <th class="span6">Description</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="lab_list_tbody">
                                                <?php
                                                    $count = 1;
                                                    foreach($labList as $l) {
                                                        $output = "<tr id=\"rl_" . $l->id . "\">\n";
                                                        $output .= "<td>" . $count . "</td>\n";
                                                        $output .= "<td>" . $l->name . "</td>\n";
                                                        $output .= "<td>" . $l->title . "</td>\n";
                                                        $output .= "<td>" . $l->description . "</td>\n";
                                                        $output .= "<td style=\"white-space:nowrap\"><button class=\"btn\" style=\"padding: 3px 4px; margin-right: 5px;\" title=\"Edit lab information\" onclick=\"editLab('" . $l->id . "');\"><i class=\"icon-pencil\"></i></button><button class=\"btn\" style=\"padding: 3px 4px;\" title=\"Delete this lab\" onclick=\"confirmDeleteLab('" . $l->id . "');\"><i class=\"icon-trash\"></i></button></td>\n";
                                                        $output .= "</tr>\n";
                                                        print($output);
                                                        $count++;
                                                        $l = null;
                                                        $output = null;
                                                    }
                                                    $count = null; 
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane" id="addedit_tab">
                                        <form id="lab_form" class="form-horizontal" onsubmit="return saveLab();" autocomplete="off">
                                            <div id="msg_hldr" class="controls hidden"></div>
                                            <fieldset>
                                                <div class="control-group">
                                                    <label class="control-label required" for="name">Name: </label>
                                                    <div class="controls">
                                                        <input id="name" class="span3" type="text" name="name" />
                                                        <p class="help-block">Name of the lab. (Required)</p>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="title">Title: </label>
                                                    <div class="controls">
                                                        <input id="title" class="span3" type="text" name="title" />
                                                        <p class="help-block">Title for lab if different then the name. If it is set, it will be used for displaying lab in left navigation and on the page instead of name.</p>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="desc">Description: </label>
                                                    <div class="controls">                                                        
                                                        <textarea id="desc" class="span3" name="desc" rows="3"></textarea>
                                                        <p class="help-block">Description of the lab.</p>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <button class="btn btn-primary" type="submit" title="Save changes">Submit</button>
                                                    <button class="btn" type="button" onclick="return resetLabForm();">Add new</button>
                                                </div>
                                                <input name="labid" type="hidden" id="labid" />
                                            </fieldset>
                                        </form>
                                    </div>
                                    <div class="modal hide fade" id="confirmDelModel">
                                        <div class="modal-header"><h3></h3></div>
                                        <div class="modal-body alert"></div>
                                        <div class="modal-footer"></div>
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
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/jquery-min.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-tab.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-alert.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/bootstrap-modal.js"></script>
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/labs.js"></script>
    </body>
</html>

<?php
$labList = null;
?>