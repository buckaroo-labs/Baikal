<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {

    /*create the calendar if it doesn't exist? never mind, just make it default for all users.
    see D:\Code\Docker\Baikal\Core\Frameworks\Baikal\Model\User.php
    $sql="SELECT id FROM calendarinstances where uri='lists' and principaluri='principals/" . $_SESSION['username'] . "'";
    $result=$dds->setSQL($sql);
    if ($rrow=$dds->getNextRow()) {
        $calendarid=$rrow[0];
    } 
    */
    if(isset($_GET['category'])) {
        $categoryname=htmlspecialchars($_GET['category']);
    }

    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE i.uri='lists' and i.principaluri='principals/" . $_SESSION['username'] . "'";
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="ListItems" class="w3-twothird w3-container" style="overflow:hidden">';
     echo '<h2>List Items</h2>';
    echo ('<table id="vtodotable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>Category</th></tr>');
    // echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
    error_reporting(E_ERROR | E_PARSE);
            $categories=[];
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VTODO as $vtodo) {
            $starttime='';
            $endtime='';
            $summary= (string)$vtodo->SUMMARY;
            $dtstart = $vtodo->DTSTART->getDateTime();
            $starttime= $dtstart->format(\DateTime::W3C);
            if ( $vtodo->DTEND) {
                $dtend = $vtodo->DTEND->getDateTime();
                $endtime= $dtend->format(\DateTime::W3C);
            }
            if ($vtodo->CATEGORIES) {
                $category=(string)$vtodo->CATEGORIES;
                $categories[$category]=0;
            } else {
                $category='';
            }
            if ($vtodo->STATUS) $status="status_" .$vtodo->STATUS; else $status="status_unknown";
            if (isset($categoryname)) {
                if(strpos($category,$categoryname)!==false) $echo=true; else $echo=false;
            } else $echo=true;
            if ($echo) echo ('<tr class="' . $status . ' listitem_tr '. $category .'"><td><a href="index.php?p=reminder&id='.$rrow['id'].'">' .$rrow['id']. '</a></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$category.'</td></tr>'); 
       
        } 



    }
    echo "</table>\n</div>";

    echo '<div id="newitem">
        <h4 class="formheader">New Item</h4>
        <form action="index.php?p=lists" method="POST" id="newitemform">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="type"  value="VTODO">
            <input type="hidden" name="parenturi" value="lists">
            <label for="title">Item Name:</label><br>
            <input type="text" name="title"  value=""><br>
            <label for="CATEGORIES">List:</label><br>
            <input type="text" name="CATEGORIES"  value=""><br>
        </form><br>
        <button type="submit" form="newitemform" value="Submit">Submit</button>
    </div>';


    if (!isset($categoryname)) {
        echo '<div class="w3-container" id="listcategories">
        <h4 class="datagrouplist">Lists</h4>
        <ul>';
        ksort($categories);
        foreach($categories as $key=>$value) {
            $keyid=str_replace(" ","",$key);
            $keyid=str_replace("'","",$keyid);
            $keyid=str_replace("&","-",$keyid);
            if (strlen($key)>0 && !strpos($key,",")) echo '<li id="' . $keyid . '" class="vlistcat"><a href="index.php?p=lists&category=' . $key . '">' . $key . '</a></li>';
        }
        echo '</ul></div>';

    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}