<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $eventid=$_GET['id'];
    } else {
        $eventid=0;
    }

    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname, i.uri as caluri ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $eventid;
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);



    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="Events" class="w3-twothird w3-container" style="overflow:hidden">';

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VEVENT as $vevent) {
            echo ('<table id="veventtable" class="table" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>End</th></tr>');
            $starttime='';
            $endtime='';
            $summary= (string)$vevent->SUMMARY;
            $dtstart = $vevent->DTSTART->getDateTime();
            //$starttime= $dtstart->format(\DateTime::W3C);
            $starttime=displayFormatDateTime($dtstart);
            if ( $vevent->DTEND) {
                $dtend = $vevent->DTEND->getDateTime();
                //$endtime= $dtend->format(\DateTime::W3C);
                $endtime=displayFormatDateTime($dtend);
            }
            $url='cal.php/calendars/' . $SESSION['username']  .'/' . $rrow['caluri']. '/' . $rrow['uri'];
            echo ('<tr><td><a href="' .$url .'">&darr;</a> <a href="' .$url .'?sabreAction=info">&#128712;</a> '.$rrow['id'].'</td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 

            $tempobj=VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
            unset($tempobj->VTIMEZONE);
            $tempdata=$tempobj->serialize();
            $tempdata=str_replace("\n","<br>\n",$tempdata);


            echo "</table>\n";
            //$data=str_replace("\n","<br>\n",$rrow['calendardata']);
            echo '<p><span class="vcarddata">'.$tempdata.'</span></p></div>';

            echo '<form method="get" action="index.php" id="conversionform">
            <input type="hidden" name="convert" value="1">
            <input type="hidden" name="p" value="entry">
            <input type="hidden" name="id" value="' . $eventid . '">
            <input type="submit" value="Convert to VJOURNAL">
            </form>'; 
        } 

    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}