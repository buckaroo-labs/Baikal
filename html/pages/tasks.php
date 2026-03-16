<?php 
//mostly this will use the defaults in folder.php
$pagevar="tasks";
$pagevar2="reminder";
$componenttype='VTODO';

$tdata_xform[0]=array('link','index.php?p=reminder');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');
$resort=true;
include("pages/folder.php");

