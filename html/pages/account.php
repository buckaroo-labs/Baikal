<?php 
 
if (isset($_SESSION['username'])) {


    $columns=" c.id, c.components, i.displayname as calendarname, i.uri as calendaruri, i.calendarcolor ";
    $from=" FROM calendars c INNER JOIN calendarinstances i on c.id=i.id ";
    $from .= " LEFT JOIN calendarobjects o on i.id=o.calendarid ";
    $where=" WHERE i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $eventid;
    $sql="SELECT count(*) as itemcount," . $columns . $from . $where . " GROUP BY " . $columns;
    $result=$dds->setSQL($sql);

    echo '<h3>Calendars</h3>' . '
    <table>
    <tr><th>URI</th><th>Display name</th><th>Types</th><th>Item Count</th></tr>
    ';

    while ($rrow=$dds->getNextRow("assoc")) {
        echo '
        <tr><td>'.$rrow['uri'].'</td><td>'.$rrow['displayname'].'</td><td>'.$rrow['components'].'</td><td>'.$rrow['itemcount'].'</td>
        ';

    }
    echo '</table>';

} else {
    //must log in
    echo "You must be logged in to use this page.";
}