<?php

/*
If these color settings are not set here, defaults may be assigned elsewhere in the code. These aren't colors per se, but classes assigned to elements, which by default will be colored according to w3.css specs. You can use the w3 color classes or something else defined in your styles.css file. ... or even override the colors of the w3 classes in your css. 
*/

//navbar and footer color
$settings['color1']="w3-black";
//secondary footer color
$settings['color2']="w3-red";
//navbar hover color
$settings['color3']="w3-hover-white";
//sidebar hover color
$settings['color4']="w3-hover-red";
$settings['color5']="w3-hover-black";


$logo_image="res/core/Baikal/Images/logo-baikal.png";
$settings['login_page']="index.php?p=login";
$settings['registration_page']="index.php?p=register";
$hideSearchForm=true;
$settings['footer_text1']='&copy;2026 buckaroo-labs';
$settings['footer_text2']="https://github.com/buckaroo-labs";

//2-week expiry
$settings['JWTExpireTime'] = 1209600;
$settings['JWTTokenName'] = 'persistentLogin';

$navbar_links=array();  
$sidebar_links=array();  
if (!isset($_GET['menu'])) {
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'🪪 Contacts',"href"=>"index.php?p=contacts","class"=>$settings['color4']);
    $sidebar_links[sizeof($sidebar_links)]=array("name"=>'&#128198; Events',"href"=>"index.php?p=events","class"=>$settings['color4']);
    $sidebar_links[sizeof($sidebar_links)]=array("name"=>'&#10004; To Do',"href"=>"index.php?p=todo","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'&#9200; Reminders',"href"=>"index.php?p=reminders","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'🛒 Lists',"href"=>"index.php?p=lists","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'&#128348; Time',"href"=>"index.php?p=time","class"=>$settings['color4']);
    $sidebar_links[sizeof($sidebar_links)]=array("name"=>'📋 Journal',"href"=>"index.php?p=journal","class"=>$settings['color4']);


} elseif($_GET['menu']=="hadmin") {
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Mail setup',"href"=>"admin.php?p=Mail","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Users',"href"=>"admin.php?p=Users","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Roles',"href"=>"admin.php?p=Roles","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Privileges',"href"=>"admin.php?p=Privs","class"=>$settings['color4']);
} elseif($_GET['menu']=="baikal") {
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Dashboard',"href"=>"admin/","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Users',"href"=>"admin/?/users/","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Config',"href"=>"admin/?/settings/standard/","class"=>$settings['color4']);
} elseif($_GET['menu']=="sabre") {
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'DAV Home',"href"=>"dav.php","class"=>$settings['color4']);
    $sidebar_links[sizeof($sidebar_links)]=array("name"=>'Calendars',"href"=>"dav.php/calendars/","class"=>$settings['color4']);
	$sidebar_links[sizeof($sidebar_links)]=array("name"=>'Contacts',"href"=>"dav.php/addressbooks/","class"=>$settings['color4']);

}


$active_menu_class="w3-hide-small " . $settings['color5'];
$other_menu_class="w3-hide-small " . $settings['color5'];

$navbar_links[sizeof($navbar_links)]=array("name"=>'<img src="'. $logo_image .'" height="20">',"href"=>"index.php","class"=>"w3-theme-l2");
$navbar_links[sizeof($navbar_links)]=array("name"=>"Home","href"=>"index.php","class"=> $settings['color3']);
if (isset($_SESSION['username'])) {
	//$navbar_links[sizeof($navbar_links)]=array("name"=>"Admin","href"=>"admin.php?menu=admin","class"=> $settings['color3']);
}
//Best to hide most navbar links on smaller screens, or else they overlap the sidebar
$navbar_links[sizeof($navbar_links)]=array("name"=>"Admin","href"=>"index.php?menu=baikal","class"=>"w3-hide-small " .$settings['color3']);
$navbar_links[sizeof($navbar_links)]=array("name"=>"Explorer","href"=>"index.php?menu=sabre","class"=>"w3-hide-small " .$settings['color3']);
$navbar_links[sizeof($navbar_links)]=array("name"=>"About","href"=>"index.php?p=About","class"=>"w3-hide-small " .$settings['color3']);

//GET variables to persist between page clicks
$stateVarList=array('menu','id');



#Not sure what to call this project/product, so going to keep it flexible
$settings['appname']='Dauriya';

require_once("../vendor/autoload.php");
define ("PROJECT_PATH_CONFIG","/var/www/config/");
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile(PROJECT_PATH_CONFIG . "baikal.yaml");
//map Baikal's yaml configuration file to the Hydrogen framework's settings
//DB defaults:
$settings['DEFAULT_DB_TYPE'] = "mysql";
//The following settings would be needed for an Oracle or MySQL connection:
$settings['DEFAULT_DB_USER'] = $config['database']['mysql_username'];
$settings['DEFAULT_DB_HOST'] = $config['database']['mysql_host'];
$settings['DEFAULT_DB_PORT'] = "3306";
$settings['DEFAULT_DB_INST'] = $config['database']['mysql_dbname'];
$settings['DEFAULT_DB_MAXRECS'] = 150;
//Because this file may not be ignored by git, don't put a password 
//  in this file, but do use this format:
$settings['DEFAULT_DB_PASS'] = "password"; 
//Normally put any required passwords in this file instead:
@include ("settingsPasswords.php");
//But in this case we are working with Baikal's yaml file
$settings['DEFAULT_DB_PASS'] = $config['database']['mysql_password'];

//settingsPasswords.php can also contain any values for framework testing
// that we don't want checked into the git repo for this project. 
// Will override anything above.