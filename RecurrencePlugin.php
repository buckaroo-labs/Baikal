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

class RecurrencePlugin extends ServerPlugin {

    protected $server;
    private $vobject;
    private $Reminder;
    protected $recurprops;

    function getName() {
        return 'recurrence';
    }

    private function parseRRULE ($rrule) {

        //https://icalendar.org/iCalendar-RFC-5545/3-8-5-3-recurrence-rule.html
        //RRULE:FREQ=WEEKLY;INTERVAL=4;UNTIL=20280317T190000Z
        //COUNT=6
        //WKST ;  BYDAY=1SU,-1SU | TU
        //BYDAY=-1MO
        //BYMONTHDAY=-3 | 1,15
        // /BYYEARDAY=1,100,200
        //BYMONTH=6,7,8
        $rruleprops=explode(";",$rrule);
        unset($recur_scale);
        unset($recur_units);
        for ($i=0;$i<count($rruleprops); $i++) {
            $prop=explode("=",$rruleprops[$i]);
            if ($prop[0]=="FREQ") {
                switch ($prop[1]) {
                    case "HOURLY":
                        $recur_scale=0;
                        break;
                    case "DAILY":
                        $recur_scale=1;
                        break;
                    case "WEEKLY":
                        $recur_scale=2;
                        break;
                    case "MONTHLY":
                        $recur_scale=3;
                        break; 
                    case "YEARLY":
                        $recur_scale=4;
                        break;
                    case "MINUTELY":
                        //this may not actually be supported here
                        //$recur_scale=5;
                        break;  
                    default:
                }
            }
            if ($prop[0]=="INTERVAL") {
                if (is_numeric($prop[1])) $recur_units=$prop[1];
            }
            if ($prop[0]=="UNTIL") {
                //UNTIL=20280317T190000Z
                $dateobj = DateTime::createFromFormat("Ymd\THis\Z",$prop[1]);
                $dateobj->setTimezone(new DateTimeZone("UTC"));
                $this->recurprops['end_date']= $dateobj->format("Y-m-d H:i:s");
            }


        }
        if (isset($recur_scale) ) {
            if(!isset($recur_units)) $recur_units=1;
            $this->recurprops['recur_scale']=$recur_scale;
            $this->recurprops['recur_units']=$recur_units;
        }

    }

    function initialize(Server $server){
        $this->server = $server;
        $server->on('beforeWriteContent',[$this,'vtodoUpdateHandler' ]);
        $server->on('beforeCreateFile',[$this,'vtodoCreateHandler' ]);
    }

    //this function handles new files
    function vtodoCreateHandler($path, &$data, \Sabre\DAV\ICollection $parent, &$modified) {
        global $dds;
        $mod=false;
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        if (strpos($path,"/recurring/")!=='false') {
            debug ("Recurrence Plugin detects new entry in 'recurring' calendar");
            //file_put_contents('/tmp/davlog', $path . " is to be created.\n",FILE_APPEND);
            $this->vobject = VObject\Reader::read($data, VObject\Reader::OPTION_FORGIVING);

            if (isset($this->vobject->VTODO)) {

                //Here is where we create a record in the recurrence table for the object. this will be handled when we instantiate the Reminder class with the object.
                $Reminder= new \Reminder($this->vobject);

                if ($this->vobject->VTODO->DTSTART) {
                    $sdMySQL= $this->vobject->VTODO->DTSTART->getDateTime()->format('Y-m-d H:i:s');
                    $sql1="UPDATE recurrence SET start_date='".$sdMySQL ."' WHERE uid='" . $this->vobject->VTODO->UID . "'";
                    $dds->setSQL($sql1);
                    
                    // ...
                }
                if ($this->vobject->VTODO->RRULE) {
                    $this->parseRRULE($this->vobject->VTODO->RRULE);
                    if (isset($this->recurprops['end_date'])) {
                        $sql="UPDATE recurrence SET end_date='".$this->recurprops['end_date'] ."' WHERE uid='" . $this->vobject->VTODO->UID . "'";
                        $dds->setSQL($sql);
                    }
                    if (isset($this->recurprops['recur_units'])) {
                        $sql="UPDATE recurrence SET recur_units=".$this->recurprops['recur_units'] .", recur_scale =" . $this->recurprops['recur_scale'] . " WHERE uid='" . $this->vobject->VTODO->UID . "'";
                        $dds->setSQL($sql);                        
                    }

                }

            }
        }
        $modified=$mod;
        return true;
    }


    //this function handles file updates
    function vtodoUpdateHandler($path, \Sabre\DAV\IFile $node, &$data, &$modified) {
        $mod=false;
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        /* We are going to manage two related records in this function: first, the standard CalDAV record in the calendarobject table; second, the related record in the recurrence table.
        We are only going to be concerned with VTODO objects in the 'recurring' URI. We are going to keep the two records in sync, and we will be quietly ignoring certain changes made by CalDAV operations in favor of the rules in the recurrence table.

        */
        if (strpos($path,"/recurring/")!=='false') {
            debug ("Recurrence Plugin detects update in 'recurring' calendar");
            //file_put_contents('/tmp/davlog', $path . " is to be updated.\n",FILE_APPEND);
            $this->vobject = VObject\Reader::read($data, VObject\Reader::OPTION_FORGIVING);

            if (isset($this->vobject->VTODO)) {
                debug ("Recurrence Plugin detects VTODO update in 'recurring' calendar: " .$this->vobject->VTODO->UID );      
                //file_put_contents('/tmp/davlog',"Recurrence Plugin detects VTODO update in 'recurring' calendar: " .$this->vobject->VTODO->UID ."\n",FILE_APPEND);        

                //Here is where we should check if we need to create a record in the recurrence table for the object. this will be handled when we instantiate the Reminder class with the object.
                $this->Reminder= new \Reminder($this->vobject);

                //inspect the incoming $data and the $node that it targets; if there was an RRULE but no longer will be, and if this is in the user's 'recurring' calendar URI, restore the RRULE to the $data and set $modified to true.
                $olddata=$node->get();
                $oldobject=VObject\Reader::read($olddata, VObject\Reader::OPTION_FORGIVING);
                if (!isset($this->vobject->VTODO->RRULE)) {
                    //"calendars/username/lists/d704fa812a9e11e51da0e.ics will have no RRULE."
                    //file_put_contents('/tmp/davlog', $path . " will have no RRULE.\n",FILE_APPEND);
                    debug ("Recurrence Plugin detects VTODO update in 'recurring' calendar without RRULE");
                    
                    if (isset($oldobject->VTODO->RRULE)) {
                        $this->vobject->VTODO->RRULE=$oldobject->VTODO->RRULE;
                        $data=$this->vobject->serialize();
                        $mod=true;
                        debug("Existing RRULE restored.");
                    }  
                } else {
                    
                    debug ("Recurrence Plugin detects VTODO update in 'recurring' calendar with RRULE");
                    
                    // Do we allow external clients to make any changes at all to an existing RRULE? To add one?

                    // ...

                }
                //Now handle cases when the VTODO status is changed to COMPLETE or CANCELLED
                if ($this->vobject->VTODO->STATUS=="COMPLETED") {
                    if ($oldobject->VTODO->STATUS!="COMPLETED") {
                        //...un-complete the item and calculate new start/due dates
                        //set status to OPEN
                        $this->vobject->VTODO->STATUS="OPEN";
                        //unset COMPLETED property
                        unset($this->vobject->VTODO->COMPLETED);
                        //set new DUE, DTSTART, and LAST-MODIFIED dates
                        $this->Reminder->markComplete($this->vobject);
                        $data=$this->vobject->serialize();
                        $mod=true;
                        //last of all, how do we make sure the client see the changes we made to the data it just submitted? testing required
                    }  
                } elseif ($this->vobject->VTODO->STATUS=="CANCELLED") {
                    if ($oldobject->VTODO->STATUS!="CANCELLED") {
                        //...any action required here?
                        //$mod=true; //?
                    }  
                }
            }  

        } 
        $modified=$mod;
        return true;
    }

}