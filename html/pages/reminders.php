<?php 
 
$columns=" c.id, c.uri, c.calendardata as objdata, i.principaluri as owner, i.displayname as subfolder_name, i.id as subfolder_id ";
$from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
$where=" WHERE c.componenttype='VTODO' AND i.uri='recurring' and i.principaluri='principals/" . $_SESSION['username'] . "'";
$sql="SELECT " . $columns . $from . $where;

//mostly this will use the defaults in folder.php
$pagevar="reminders";
$pagevar2="reminder";
$componenttype='VTODO';

$tdata_xform[0]=array('link','index.php?p=reminder');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

include("pages/folder.php");
