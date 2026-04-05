<?php
use Symfony\Component\Yaml\Yaml;
require_once("lib/clsVTODO.php");
function getDBConn() {
    $config = Yaml::parseFile(PROJECT_PATH_CONFIG . "baikal.yaml");
    return new mysqli(
            $config['database']['mysql_host'],
            $config['database']['mysql_username'],
            $config['database']['mysql_password'],
            $config['database']['mysql_dbname']
            );
}
function resetTaskList($listname,$principal) {
    //loop through all VTODOS in user's calendar having calendar uri of "lists." If VTODO category matches and has COMPLETED property, remove it and set STATUS to OPEN (use VTODO class object's markComplete method)
    $dbconn=getDBConn();
    $sql="SELECT o.id, o.calendardata FROM calendarobjects o INNER JOIN calendarinstances i 
    ON o.calendarid=i.id 
    WHERE o.componenttype='VTODO' AND i.uri='lists' AND i.principaluri='principals/" . $principal . "'";
    $result=$dbconn->query($sql);
    while ($rrow=$result->fetch_assoc()) {
        $object = new VTODO($rrow['id']);
        if ($object->getCategories()==$listname) {
            $object->markIncomplete();
            $object->save();
        }

    }
}

function displayFormatDateTime ($datetime,$timezone='UTC') {
    date_default_timezone_set($timezone);
    return $datetime->format('Y-m-d H:i:s (P)');
}

function decode_scale ($scale_code) {
	switch ($scale_code) {
		case 0:
			$retval = "hours";
			break;
		case 2:
			$retval = "weeks";
			break;
		case 3:
			$retval = "months";
			break;
		case 4:
			$retval = "years";
			break;
		default:
			$retval = "days";
	}
	return $retval;
}

function decode_scale_and_units ($scale_code, $units, $include_1=false) {
	switch ($scale_code) {
		case 0:
			$scale = "hour";
			break;
		case 2:
			$scale = "week";
			break;
		case 3:
			$scale = "month";
			break;
		case 4:
			$scale = "year";
			break;
		default:
			$scale = "day";
	}
	if (is_null($units)) $units=1;
	if ($units ==1) $retval= $scale; else $retval=  "$units $scale" . "s";
	if ($units ==1 and $include_1) $retval="1 " . $retval;
	return $retval;
}

$array_key_for_multisort="id";
function keysort($array1,$array2) {
	global $array_key_for_multisort;
	global $sortorder;
	$key=$array_key_for_multisort;
	if isset($sortorder && $sortorder==SORT_DESC) return  strcmp($array2[$key],$array1[$key]);
	else return strcmp($array1[$key],$array2[$key]);
}