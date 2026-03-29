<?php 

$pagevar="contacts";
$pagevar2="contact";
$pageheader="Contacts";

$thead=array("ID", "Display Name", "Categories", "Address Book");

$tdata[0]=array('Q','id');
$tdata[1]=array('V','FN');
$tdata[2]=array('V','CATEGORIES');
$tdata[3]=array('Q','subfolder_name');

$resort=true;

$tdata_xform[0]=array('link','index.php?p=contact');

$sql="SELECT c.id, c.uri, c.carddata as objdata, i.principaluri as owner, i.displayname as subfolder_name, i.id as subfolder_id FROM cards c
    INNER JOIN addressbooks i on c.addressbookid=a.id
    WHERE i.principaluri='principals/" . $_SESSION['username'] . "'";

include("pages/folder.php");

