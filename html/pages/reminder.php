<?php 
require_once("lib/clsReminder.php");
//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $reminderid=$_GET['id'];
    } else {
        $reminderid=0;
    }

    $columns=" c.id, c.uid, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname, i.uri as calendaruri ";
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
            //28-MAR-2026: only show one VTODO from each file

            //echo ('<table id="veventtable" class="table" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>End</th></tr>');
            $startdatetime='';
            $enddatetime='';
            $summary= (string)$vtodo->SUMMARY;
            if ($vtodo->DTSTART) {
                $dtstart = $vtodo->DTSTART->getDateTime();
                //$startdatetime= $dtstart->format(\DateTime::W3C);
                $startdatetime=displayFormatDateTime($dtstart);
            }
            if ( $vtodo->DTEND) {
                $dtend = $vtodo->DTEND->getDateTime();
                //$enddatetime= $dtend->format(\DateTime::W3C);
                $enddatetime=displayFormatDateTime($dtend);
            }
            if (isset($vtodo->COMPLETED)) $completed=true; else $completed=false;
            //echo ('<tr><td>'.$rrow['id'].'</td><td>'.$summary.'</td><td>'.$startdatetime.'</td><td>'.$enddatetime.'</td></tr>'); 

            //echo "</table>\n";
            $data=str_replace("\n","<br>\n",$rrow['calendardata']);
            //echo '<p><span class="vcarddata">'.$data.'</span></p></div>';

            /*
            echo '<div id="rhs" style="max-width:95%"><form method="POST" action="index.php?p=reminder&id=' . $reminderid . '" id="conversionform">
            <input type="hidden" name="type" value="VTODO">
            <input type="hidden" value="togglestatus" name="action">
            <input type="hidden" name="id" value="' . $reminderid . '">';

            if ($completed) {
                echo '<input type="submit" value="Mark Incomplete">';
            } else {
                echo '<input type="submit" value="Mark Complete">';
            }
            echo '</form>'; 
            if ($rrow['calendaruri']=='recurring') {
                echo '<div id="recurrence_view">';
                include "pages/recurrence.inc.php";
                echo '</div><div id="recurrence_edit" style="display:none">';
                include "pages/recurrenceedit.inc.php";
                echo '</div>';
            }
            echo '</div>';
            */
        } 
        //28-MAR-2026: only show one VTODO from each file
        echo ('<table id="veventtable" class="table" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>End</th></tr>');
        echo ('<tr><td>'.$rrow['id'].'</td><td>'.$summary.'</td><td>'.$startdatetime.'</td><td>'.$enddatetime.'</td></tr>'); 
        echo "</table>\n";
        echo '<p><span class="vcarddata">'.$data.'</span></p></div>';

        echo '<div id="rhs" style="max-width:95%"><form method="POST" action="index.php?p=reminder&id=' . $reminderid . '" id="conversionform">
        <input type="hidden" name="type" value="VTODO">
        <input type="hidden" value="togglestatus" name="action">
        <input type="hidden" name="id" value="' . $reminderid . '">';
        if ($completed) {
            echo '<input type="submit" value="Mark Incomplete">';
        } else {
            echo '<input type="submit" value="Mark Complete">';
        }
        echo '</form>'; 
        if ($rrow['calendaruri']=='recurring') {
            echo '<div id="recurrence_view">';
            include "pages/recurrence.inc.php";
            echo '</div><div id="recurrence_edit" style="display:none">';
            include "pages/recurrenceedit.inc.php";
            echo '</div>';
        }
        echo '</div>';

    }
echo "\n" . '<script src="js/recurrence.js"></script>' ."\n";
} else {
    //must log in
    echo "You must be logged in to use this page.";
}