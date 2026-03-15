<?php 

//mostly this will use the defaults in folder.php
$pagevar="time";
$pagevar2="timerecord";
$componenttype='VJOURNAL';

$tdata_xform[0]=array('link','index.php?p=timerecord');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

$where=" WHERE i.uri ='projecttime'";
$thead=array("ID", "Summary", "Start", "End", "Project");

$tdata[0]=array('Q','id');
$tdata[1]=array('V','SUMMARY');
$tdata[2]=array('V','DTSTART');
$tdata[3]=array('V','DTEND');
$tdata[4]=array('V','CATEGORIES');


include("pages/folder.php");


