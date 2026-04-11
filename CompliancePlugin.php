<?php
//see https://sabre.io/dav/writing-plugins/

use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
//use Sabre\HTTP\RequestInterface;
//use Sabre\HTTP\ResponseInterface;
use Sabre\VObject;
require_once("settingsHydrogen.php");
require_once("Hydrogen/clsDateTimeExt.php");
require_once("Hydrogen/lib/Debug.php");
//require_once("Hydrogen/db/clsDataSource.php");

/*
The purpose of this plugin is to identify and correct specific client inputs that are either non standard or incomplete.
1. BusyCal client will save "Journal" entries not as VJOURNAL but as VEVENT with attribute "X-BUSYMAC-EVENT-TYPE:JOURNAL" even though it is capable of recognizing VJOURNAL events.

*/
class CompliancePlugin extends ServerPlugin {

    protected $server;
    private $vobject;


    function getName() {
        return 'compliance';
    }

    function initialize(Server $server){
        $this->server = $server;
        //$server->on('beforeWriteContent',[$this,'UpdateHandler' ]);
        $server->on('beforeCreateFile',[$this,'CreateHandler' ]);
    }

    //This function handles new files.  
    function CreateHandler($path, &$data, \Sabre\DAV\ICollection $parent, &$modified) {
        $mod=false;
        debug("Compliance plugin incoming file path: " . $path);
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        if (strpos($path,"calendars/")!==false && 
            strpos($data,"BEGIN:VEVENT")!==false &&
            strpos($data,"X-BUSYMAC-EVENT-TYPE:JOURNAL")!==false
            ) {
                debug ("Compliance plugin detects non-compliant BusyCal entry");
                $data=str_replace("BEGIN:VEVENT","BEGIN:VJOURNAL",$data);
                $data=str_replace("END:VEVENT","END:VJOURNAL",$data);
                $mod=true;
        }



        $modified=$mod;
        return true;
    }


    //This function handles file updates. Not yet implemented 
    function UpdateHandler($path, \Sabre\DAV\IFile $node, &$data, &$modified) {
        $mod=false;
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        if (strpos($path,"calendars/")!==false && 
            strpos($data,"BEGIN:VEVENT")!==false &&
            strpos($data,"X-BUSYMAC-EVENT-TYPE:JOURNAL")!==false
            ) {
                debug ("Compliance plugin detects non-compliant BusyCal update");
                $data=str_replace("BEGIN:VEVENT","BEGIN:VJOURNAL",$data);
                $data=str_replace("END:VEVENT","END:VJOURNAL",$data);
                $mod=true;
        }


        $modified=$mod;
        return true;
    }

}