<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    $and='';
    if (isset($_GET['calendarid']) && is_numeric($_GET['calendarid'])) {
        $calendarid=$_GET['calendarid'];
        $and=" AND i.id=" . $calendarid;
    }
    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE c.componenttype='VEVENT' AND i.principaluri='principals/" . $_SESSION['username'] . "'";
    $sql="SELECT count(*) " . $from . $where . $and;
    $resultcount=$dds->getInt($sql);
    $sql="SELECT " . $columns . $from . $where . $and;
    $dds->setMaxRecs(9999);
    $result=$dds->setSQL($sql);
    echo '<div id="Events" class="w3-twothird w3-container" style="overflow:hidden">';
    //echo '<h2>Events ('.$resultcount.')</h2>';
    echo '<h2>Events</h2>';
    echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>Calendar</th></tr>');
    // echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VEVENT as $vevent) {

            $summary= (string)$vevent->SUMMARY;
            $dtstart = $vevent->DTSTART->getDateTime();
            $starttime= $dtstart->format(\DateTime::W3C);
            $dtend = $vevent->DTEND->getDateTime();
            $endtime= $dtend->format(\DateTime::W3C);
            if ($vevent->CATEGORIES) {
                $category=(string)$vevent->CATEGORIES;
                $categories[$category]=0;
            } else {
                $category='';
            }
            if ($vevent->STATUS) $status="status_" .$vevent->STATUS; else $status="vevent";
            echo ('<tr class="' . $status . ' listitem_tr '. $category .'"><td><a href="index.php?p=event&id='.$rrow['id'].'">'.$rrow['id'].'</a></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$rrow['calendarname'].'</td></tr>'); 
             //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$rrow['calendarname'].'</td></tr>'); 
        } 

    //start, end, summary
    }
    echo "</table>\n</div>";

    echo '<div id="calendarlist"><h4>Calendars</h4><ul>';
    $sql="SELECT id, displayname FROM calendarinstances WHERE principaluri='principals/" . $_SESSION['username'] . "' order by FIELD(displayname," .'"Default Calendar"' .") DESC, displayname";
    $result=$dds->setSQL($sql);
    while ($rrow=$dds->getNextRow('assoc')) {
        $liClassAndID=' class="calendarname"';
        if(isset($calendarid) && $calendarid==$rrow['id'])  {
            $liClassAndID .= ' id="activeCalendarName" ';
        }
        echo '<li '. $liClassAndID .'"><a href="index.php?p=events&calendarid=' . $rrow['id'] . '">' . $rrow['displayname'] . '</a></li>';
    }
    echo '</ul></div>';
} else {
    //must log in
    echo "You must be logged in to use this page.";
}