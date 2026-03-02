<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE c.componenttype='VTODO' AND i.uri not in ('recurring','lists','projecttime') and i.principaluri='principals/" . $_SESSION['username'] . "'";
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="To Do" class="w3-twothird w3-container" style="overflow:hidden">';
    //echo '<h2>To Do ('.$resultcount.')</h2>';
    echo '<h2>To Do</h2>';
    echo ('<table id="vtodotable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>Calendar</th></tr>');
    // echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
    error_reporting(E_ERROR | E_PARSE);
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
            echo ('<tr class="' . $status . ' listitem_tr '. $category .'"><td><a href="index.php?p=reminder&id='.$rrow['id'].'">' .$rrow['id']. '</a></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$rrow['calendarname'].'</td></tr>'); 
             //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 
        } 

    //start, end, summary
    }
    echo "</table>\n</div>";
} else {
    //must log in
    echo "You must be logged in to use this page.";
}