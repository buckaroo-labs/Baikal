<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE c.componenttype='VJOURNAL' AND i.principaluri='principals/" . $_SESSION['username'] . "'";
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $sql="SELECT " . $columns . $from . $where;
    $dds->setMaxRecs(9999);
    $result=$dds->setSQL($sql);
    echo '<div id="Journal" class="w3-twothird w3-container" style="overflow:hidden">';
    //echo '<h2>Events ('.$resultcount.')</h2>';
    echo '<h2>Journal</h2>';
    echo ('<table id="vjournaltable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>Calendar</th></tr>');
    // echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VJOURNAL as $vevent) {
            unset ($dtstart);
            unset ($dtend);
            $starttime='';
            $endtime='';
            $summary= (string)$vevent->SUMMARY;
            if ($vevent->DTSTART) {
                $dtstart = $vevent->DTSTART->getDateTime();
                $starttime= $dtstart->format(\DateTime::W3C);
            } 
            if ($vevent->DTEND) {
                $dtend = $vevent->DTEND->getDateTime();
                $endtime= $dtend->format(\DateTime::W3C);
            }
            echo ('<tr><td><a href="index.php?p=entry&id='.$rrow['id'].'">' . $rrow['id'].'</a></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$rrow['calendarname'].'</td></tr>'); 
             //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 
        } 

    //start, end, summary
    }
    echo "</table>\n</div>";
} else {
    //must log in
    echo "You must be logged in to use this page.";
}