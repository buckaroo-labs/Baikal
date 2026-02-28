<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Baïkal server</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!-- Le styles -->
		<link href="res/core/TwitterBootstrap/css/bootstrap.css" rel="stylesheet" />
		<link href="res/core/BaikalAdmin/GlyphiconsPro/glyphpro.css" rel="stylesheet" />
		<link href="res/core/BaikalAdmin/GlyphiconsPro/glyphpro-2x.css" rel="stylesheet" />
		<link href="res/core/BaikalAdmin/Templates/Page/style.css" rel="stylesheet" />
		<link href="res/core/TwitterBootstrap/css/bootstrap-responsive.css" rel="stylesheet" />
		<link href="styles.css" rel="stylesheet" />
		<script src="sorttable.js"></script>
	</head>
	<body>


    	<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href=""><img style="vertical-align: text-top; line-height: 20px;" src="res/core/BaikalAdmin/Templates/Page/baikal-text-20.png" alt="baikal"> Server</a>
			<div class="nav-collapse">
				<ul class="nav">
                    <!--
					<li> <a href="admin/"><i class="icon-eject icon-white" style="transform: rotate(90deg)"></i> Login</a></li>
					<li> <a target="_blank" rel="noopener noreferrer" href="https://sabre.io/baikal/"><i class="icon-heart icon-white"></i> Get your own</a></li>
                    -->
				</ul>
			</div>
		</div>
	</div>
</div>


		<div class="container">
			<header class="jumbotron subhead" id="overview" style="min-width:400px;float:right;padding-bottom:7em;">
	<h1><img style="height:150px; float:left;" src="res/core/Baikal/Images/logo-baikal.png" alt="baikal logo"> Baïkal
	<p class="lead">Lightweight CalDAV+CardDAV server.</p></h1>
</header>


<?php
//https://sabre.io/vobject/vcard/
use Sabre\VObject;
use Symfony\Component\Yaml\Yaml;

require_once("../vendor/autoload.php");

define ("PROJECT_PATH_CONFIG","/var/www/config/");
$config = Yaml::parseFile(PROJECT_PATH_CONFIG . "baikal.yaml");
$conn=new mysqli(
    $config['database']['mysql_host'],
    $config['database']['mysql_username'],
    $config['database']['mysql_password'],
    $config['database']['mysql_dbname']
    );
$sql="SELECT c.id, c.uri, c.carddata, a.principaluri as owner, a.displayname as book_name FROM cards c
INNER JOIN addressbooks a on c.addressbookid=a.id";
$result=$conn->query($sql);
echo '<div id="contacts">';
echo '<h2>Contacts</h2>';
echo ('<table id="vcardtable" class="table sortable" style="clear:both"><tr>
<th>ID</th><th>Data</th><th>Categories</th><th>Telephone</th><th>Addresses</th><th>email</th><th>Display Name</th><th>Name</th><th>Org</th><th>Book Owner</th><th>Book</th></tr>
');
error_reporting(E_ERROR | E_PARSE);
while ($rrow=$result->fetch_assoc()) {
    $owner=str_replace('principals/','',$rrow['owner']);
    $vcard = VObject\Reader::read($rrow['carddata']);
    $telephone='';
    foreach($vcard->TEL as $tel) {
        $telephone.="Phone";
        if ($tel['TYPE']) {
            $telephone .= " (" . strtolower($tel['TYPE']) . ")";
        }
        $telephone .= ": " .$tel . ": <BR>\n";
    }
    $email='';
    foreach($vcard->EMAIL as $eml) {
        $email.="email";
        if ($eml['TYPE']) {
            $email .= " (" . strtolower($eml['TYPE']) . ")";
        }
        $email .= ": ". $eml . "<BR>\n";
    }
    $addresses="";
    foreach($vcard->ADR as $adr) {
        $addresses.="Address";
        if ($adr['TYPE']) {
            $addresses .= " (" . strtolower($adr['TYPE']) . ")";
        }
        $addresses .= ": " . str_replace(";","|",$adr) . "<BR>\n";
    }

    echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['carddata'].'</span></td><td>'.$vcard->CATEGORIES.'</td><td>'.$telephone.'</td><td>'.$addresses.'</td><td>'.$email.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$vcard->N.'</td><td>'.$vcard->ORG.'</td><td>'.$owner.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
}
echo "</table>\n</div>";

$sql="SELECT c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname FROM calendarobjects c
INNER JOIN calendarinstances i on c.calendarid=i.id";
$result=$conn->query($sql);
echo '<div id="Events">';
echo '<h2>Events</h2>';
//echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Categories</th><th>Telephone</th><th>Addresses</th><th>email</th><th>Display Name</th><th>Name</th><th>Org</th><th>Book Owner</th><th>Book</th></tr>');
echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
error_reporting(E_ERROR | E_PARSE);
while ($rrow=$result->fetch_assoc()) {
    $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
    //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['carddata'].'</span></td><td>'.$vcard->CATEGORIES.'</td><td>'.$telephone.'</td><td>'.$addresses.'</td><td>'.$email.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$vcard->N.'</td><td>'.$vcard->ORG.'</td><td>'.$owner.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
   foreach($vcalendar->VEVENT as $vevent) {

        $summary= (string)$vevent->SUMMARY;
        $dtstart = $vevent->DTSTART->getDateTime();
        $starttime= $dtstart->format(\DateTime::W3C);
        $dtend = $vevent->DTEND->getDateTime();
        $endtime= $dtend->format(\DateTime::W3C);
        echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td class="bold">'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 
    } 

   //start, end, summary
}
echo "</table>\n</div>";

//repeat query loop for TODOs
$result=$conn->query($sql);
echo '<div id="Reminders">';
echo '<h2>Reminders</h2>';
//echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Categories</th><th>Telephone</th><th>Addresses</th><th>email</th><th>Display Name</th><th>Name</th><th>Org</th><th>Book Owner</th><th>Book</th></tr>');
echo ('<table id="vtodotable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
error_reporting(E_ERROR | E_PARSE);
while ($rrow=$result->fetch_assoc()) {
    $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
    //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['carddata'].'</span></td><td>'.$vcard->CATEGORIES.'</td><td>'.$telephone.'</td><td>'.$addresses.'</td><td>'.$email.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$vcard->N.'</td><td>'.$vcard->ORG.'</td><td>'.$owner.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
   foreach($vcalendar->VTODO as $vtodo) {

        $summary= (string)$vtodo->SUMMARY;
        $dtstart = $vtodo->DTSTART->getDateTime();
        $starttime= $dtstart->format(\DateTime::W3C);
        $dtend = $vtodo->DTEND->getDateTime();
        $endtime= $dtend->format(\DateTime::W3C);
        echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 
    } 

   //start, end, summary
}
echo "</table>\n</div>";


//repeat query loop for Journals
$result=$conn->query($sql);
echo '<div id="Journal">';
echo '<h2>Journal</h2>';
//echo ('<table id="veventtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Categories</th><th>Telephone</th><th>Addresses</th><th>email</th><th>Display Name</th><th>Name</th><th>Org</th><th>Book Owner</th><th>Book</th></tr>');
echo ('<table id="vjournaltable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Summary</th><th>Start</th><th>End</th></tr>');
error_reporting(E_ERROR | E_PARSE);
while ($rrow=$result->fetch_assoc()) {
    $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
    //echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['carddata'].'</span></td><td>'.$vcard->CATEGORIES.'</td><td>'.$telephone.'</td><td>'.$addresses.'</td><td>'.$email.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$vcard->N.'</td><td>'.$vcard->ORG.'</td><td>'.$owner.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
   foreach($vcalendar->VJOURNAL as $vjournal) {

        $summary= (string)$vjournal->SUMMARY;
        $dtstart = $vjournal->DTSTART->getDateTime();
        $starttime= $dtstart->format(\DateTime::W3C);
        $dtend = $vjournal->DTEND->getDateTime();
        $endtime= $dtend->format(\DateTime::W3C);
        echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['calendardata'].'</span></td><td>'.$summary.'</td><td>'.$starttime.'</td><td>'.$endtime.'</td></tr>'); 
    } 

   //start, end, summary
}
echo "</table>\n</div>";

?>
	<!-- Le javascript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="res/core/TwitterBootstrap/js/jquery-3.1.0.min.js"></script>
		<script src="res/core/TwitterBootstrap/js/jquery.color-2.2.0.min.js"></script>
		<script src="res/core/TwitterBootstrap/js/bootstrap.min.js"></script>
		<script src="res/core/BaikalAdmin/main.js"></script>
		
	</body>
</html>
