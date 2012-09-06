<?php
require_once("../../config.php");
// Used in leftnav.php for storing lab listings
$labList = array();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Lab Usage - Lab Computer Administration</title>
        
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
                            <h3>Computers Admin</h3>
                        </div>
                        <div class="panel-content">
                            <div class="tabbable">
                                <button class="btn pull-right btn-small" onclick="return refreshTable();" id="refresh_data_button"><i class="icon-refresh"></i> Refresh</button>
                                <ul class="nav nav-tabs" id="tabs">
                                    <li class="active"><a href="#list_tab" data-toggle="tab" id="list_tab_link">List</a></li>
                                    <li><a href="#addedit_tab" data-toggle="tab" id="addedit_tab_link">Add / Edit</a></li>
                                </ul>
                                
                                <div class="tab-content">
                                    <div class="tab-pane active" id="list_tab">
                                        <div class="control-group form-horizontal pull-right">
                                            <label for="filter_by_lab" class="control-label" style="width: 100px;">Filter by lab:</label>
                                            <div class="controls" style="margin-left: 110px;">
                                                <select id="filter_by_lab" class="span2" onchange="return filterLab(this);">
                                                    <option value=""></option>
                                                <?php
                                                foreach($labList as $l) {
                                                    if(empty($l->title)) {
                                                        print("<option value=\"" . $l->id . "\">" . $l->name . "</option>");
                                                    } else {
                                                        print("<option value=\"" . $l->id . "\">" . $l->title . "</option>");
                                                    }
                                                    $l = null;
                                                }
                                                ?>
                                                </select>
                                            </div>
                                        </div>                                        
                                        <div class="control-group form-horizontal">
                                            <label for="select01" class="control-label" style="width: 100px;">Entries per page:</label>
                                            <div class="limit_per_page" style="margin-left: 110px;">
                                                <select id="limit_per_page" class="span1" onchange="return changeLimit(this);">
                                                    <option value="10">10</option>
                                                    <option value="20">20</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <table class="table table-striped table-bordered" 
                                                id="comp_list">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th class="span4" style="cursor: pointer; color: #0088cc" title="Sort by name" onclick="sortTable(this);">Name<i class="icon-chevron-up pull-right"></i></a></th>
                                                    <th class="span3" style="cursor: pointer; color: #0088cc" title="Sort by status" onclick="sortTable(this);">Current Status<i class="pull-right"></i></th>
                                                    <th class="span3" style="cursor: pointer; color: #0088cc" title="Sort by lab" onclick="sortTable(this);">Lab<i class="pull-right"></i></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="comp_list_tbody"></tbody>
                                        </table>
                                        <span class="pull-left" id="showing-text"></span>
                                        <div class="pagination pagination-right"></div>
                                        <div class="hide" id="sort_by"></div>
                                        <div class="hide" id="sort_type"></div>
                                        <div class="hide" id="current_page"></div>
                                    </div>
                                    <div class="tab-pane" id="addedit_tab">
                                        <form id="comp_form" class="form-horizontal" onsubmit="return saveComp();" autocomplete="off">
                                            <div id="msg_hldr" class="controls hidden"></div>
                                            <fieldset>
                                                <div class="control-group">
                                                    <label class="control-label required" for="name">Name: </label>
                                                    <div class="controls">
                                                        <input id="name" class="span3" type="text" name="name" />
                                                        <p class="help-block">Name of the computer. (Required)</p>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label required" for="mstatus">Status: </label>
                                                    <div class="controls">                                                        
                                                        <select id="mstatus" class="span3">
                                                            <option value=""></option>
                                                            <?php
                                                            foreach($labCompStatusArray as $key => $value) {
                                                                print("<option value=\"" . $key . "\">" . $value . "</option>");
                                                            }
                                                            ?>
                                                        </select>
                                                        <p class="help-block">Status of this computer. (Required)</p>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label required" for="lab">Lab: </label>
                                                    <div class="controls">
                                                        <select id="lab" class="span3">
                                                            <option value=""></option>
                                                            <?php
                                                            foreach($labList as $l) {
                                                                if(empty($l->title)) {
                                                                    print("<option value=\"" . $l->id . "\">" . $l->name . "</option>");
                                                                } else {
                                                                    print("<option value=\"" . $l->id . "\">" . $l->title . "</option>");
                                                                }
                                                                $l = null;
                                                            }
                                                            ?>
                                                        </select>
                                                        <p class="help-block">Lab in which the computer belong. (Required)</p>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <button class="btn btn-primary" type="submit" title="Save changes">Submit</button>
                                                    <button class="btn" type="reset" onclick="return resetCompForm();">Reset</button>
                                                </div>
                                                <input name="compid" type="hidden" id="compid" />
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
        <script src="<?php echo(_WEB_PATH); ?>/assets/js/computers.js"></script>
        <script type="text/javascript">
            loadCompList();            
        </script>
    </body>
</html>

<?php
$labList = null;
?>