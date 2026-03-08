<?php 

$pagevar="contacts";
$pagevar2="contact";
$pageheader="Contacts";

$thead=array("ID", "Display Name", "Categories", "Address Book");

$tdata[0]=array('Q','id');
$tdata[1]=array('V','FN');
$tdata[2]=array('V','CATEGORIES');
$tdata[3]=array('Q','subfolder_name');

$tdata_xform[0]=array('link','index.php?p=contact');

$sql="SELECT c.id, c.uri, c.carddata as objdata, a.principaluri as owner, a.displayname as subfolder_name, a.id as subfolder_id FROM cards c
    INNER JOIN addressbooks a on c.addressbookid=a.id
    WHERE a.principaluri='principals/" . $_SESSION['username'] . "'";

include("pages/folder.php");

