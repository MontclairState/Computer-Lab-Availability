<ul class="nav nav-list">
    <li class="nav-header"><i class="icon-file"></i> Usage Reports</li>
    <?php
    require_once("../../config.php");
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();

    $labObj = new Lab();
    $labList = $labObj->find($db, array("order" => array("title" => "asc")));
    foreach($labList as $l) {
        if(!empty($l->title)) {
            print("<li><a href=\"report.php?lab=" . $l->name . "\">" . $l->title . "</a></li>");
        } else {
            print("<li><a href=\"report.php?lab=" . $l->name . "\">" . $l->name . "</a></li>");
        }
        $l = null;
    }
    $labObj = null;
    $db->close();
    $db = null;
    ?>    
    <li><a href="report.php?lab=all&type=day">All Labs</a></li>
    <li class="nav-header"><i class="icon-wrench"></i> Administration</li>
    <li><a href="labs.php">Labs</a></li>
    <li><a href="computers.php">Computers</a></li>
</ul>
