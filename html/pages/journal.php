<?php 

$and='';
if (isset($_GET['calendarid']) && is_numeric($_GET['calendarid'])) {
    $calendarid=$_GET['calendarid'];
    $and=" AND i.id=" . $calendarid;
}
$columns=" c.id, c.uri, c.calendardata as objdata, i.principaluri as owner, i.displayname as subfolder_name, i.id as subfolder_id";
$from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
$where=" WHERE c.componenttype='VJOURNAL' AND i.uri NOT IN ('lists','projecttime') AND i.principaluri='principals/" . $_SESSION['username'] . "'";
$sql="SELECT " . $columns . $from . $where . $and;

$pagevar="journal";
$pagevar2="entry";
$pageheader="Journal";
$componenttype='VJOURNAL';

$thead[0]='ID';
$thead[1]='Summary';
$thead[2]='Start';
$thead[3]='Calendar';

$tdata[0]=array('Q','id');
$tdata[1]=array('V','SUMMARY');
$tdata[2]=array('V','DTSTART');
$tdata[3]=array('Q','subfolder_name');

$tdata_xform[0]=array('link','index.php?p=entry');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

include("pages/folder.php");


