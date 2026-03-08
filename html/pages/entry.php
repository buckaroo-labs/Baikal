<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $journalid=$_GET['id'];
    } else {
        $journalid=0;
    }

$columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $journalid;
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    
    if (isset($_GET['convert']) && $resultcount==1) {
        //Convert to VJOURNAL from VEVENT
        //At this point we have confirmed that the ID exists and belongs to the current user
        //Next confirm the object type and make the change
        $sql="select calendardata, calendarid from calendarobjects where id=" . $journalid;
        $result=$dds->setSQL($sql);
        $rrow=$dds->getNextRow('assoc');
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
        //foreach($vcalendar->VEVENT as $vevent) {
            $newdata=str_replace("\nBEGIN:VEVENT","\nBEGIN:VJOURNAL",$rrow['calendardata']);
            $newdata=str_replace("\nEND:VEVENT","\nEND:VJOURNAL",$newdata);
            $newdata=str_replace("'","''",$newdata);
            $sql="update calendarobjects set size=size+4, componenttype='VJOURNAL', calendardata='" . $newdata . "', etag='". md5($newdata) ."' where id=" . $journalid;
            $result=$dds->setSQL($sql);
            $sql="UPDATE calendars SET synctoken=synctoken+1 WHERE id=" . $rrow['calendarid'];
            $result=$dds->setSQL($sql);
        //}

    }

    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="Entries" class="w3-twothird w3-container" style="overflow:hidden">';

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {

        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VJOURNAL as $vjournal) {
            echo ('<table id="vjournaltable" class="table" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>End</th></tr>');
            $starttime='';
            $endtime='';
            $summary= (string)$vjournal->SUMMARY;
            $dtstart = $vjournal->DTSTART->getDateTime();
            //$starttime= $dtstart->format(\DateTime::W3C);
            $starttime=displayFormatDateTime($dtstart);
            if ( $vjournal->DTEND) {
                $dtend = $vjournal->DTEND->getDateTime();
                //$endtime= $dtend->format(\DateTime::W3C);
                $endtime=displayFormatDateTime($dtend);
            }

            echo ('<tr><td>'.$rrow['id'].'</td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 

            echo "</table>\n";
            $data=str_replace("\n","<br>\n",$rrow['calendardata']);
            echo '<p><span class="vcarddata">'.$data.'</span></p></div>';
        } 

    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}