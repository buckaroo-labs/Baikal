<?php 

//mostly this will use the defaults in folder.php
$pagevar="time";
$pagevar2="timerecord";
$componenttype='VJOURNAL';

$tdata_xform[0]=array('link','index.php?p=timerecord');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

$where=" WHERE i.uri ='projecttime'";

include("pages/folder.php");


