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