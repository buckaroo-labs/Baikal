<?php 
//mostly this will use the defaults in folder.php
$pagevar="events";
$pagevar2="event";
$componenttype='VEVENT';

$tdata_xform[0]=array('link','index.php?p=event');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');
$resort=true;
include("pages/folder.php");
