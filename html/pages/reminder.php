<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $reminderid=$_GET['id'];
    } else {
        $reminderid=0;
    }
    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $reminderid;
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="Reminders" class="w3-twothird w3-container" style="overflow:hidden">';

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VTODO as $vtodo) {
            echo ('<table id="veventtable" class="table" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>End</th></tr>');
            $starttime='';
            $endtime='';
            $summary= (string)$vtodo->SUMMARY;
            $dtstart = $vtodo->DTSTART->getDateTime();
            $starttime= $dtstart->format(\DateTime::W3C);
            if ( $vtodo->DTEND) {
                $dtend = $vtodo->DTEND->getDateTime();
                $endtime= $dtend->format(\DateTime::W3C);
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