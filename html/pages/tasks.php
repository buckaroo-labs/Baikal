<?php 
//mostly this will use the defaults in folder.php
$pagevar="tasks";
$pagevar2="reminder";
$pageheader="Tasks";
$componenttype='VTODO';

$tdata_xform[0]=array('link','index.php?p=reminder');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

include("pages/folder.php");

