<?php
//see https://sabre.io/dav/writing-plugins/

//Have I covered cases of both new and updated items?

use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\VObject;
require_once("Hydrogen/clsDateTimeExt.php");

class RecurrencePlugin extends ServerPlugin {

    protected $server;
    private $vobject;
    private $Reminder;

    function getName() {
        return 'recurrence';
    }

    function initialize(Server $server){
        $this->server = $server;
        $server->on('beforeWriteContent',[$this,'vtodoUpdateHandler' ]);
    }

    function vtodoUpdateHandler($path, \Sabre\DAV\IFile $node, &$data, &$modified) {
        $mod=false;
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        /* We are going to manage two related records in this function: first, the standard CalDAV record in the calendarobject table; second, the related record in the recurrence table.
        We are only going to be concerned with VTODO objects in the 'recurring' URI. We are going to keep the two records in sync, and we will be quietly ignoring certain changes made by CalDAV operations in favor of the rules in the recurrence table.

        */
        if (strpos($path,"/recurring/")!=='false') {
            
            //file_put_contents('/tmp/davlog', $path . " is to be updated.\n",FILE_APPEND);
            $this->vobject = VObject\Reader::read($data, VObject\Reader::OPTION_FORGIVING);

            if (isset($this->vobject->VTODO)) {
                //inspect the incoming $data and the $node that it targets; if there was an RRULE but no longer will be, and if this is in the user's 'recurring' calendar URI, restore the RRULE to the $data and set $modified to true.
                if (!isset($this->vobject->VTODO->RRULE)) {
                    //"calendars/username/lists/d704fa812a9e11e51da0e.ics will have no RRULE."
                    //file_put_contents('/tmp/davlog', $path . " will have no RRULE.\n",FILE_APPEND);

                    $olddata=$node->get();
                    $oldobject=VObject\Reader::read($olddata, VObject\Reader::OPTION_FORGIVING);
                    if (isset($oldobject->VTODO->RRULE)) {
                        $this->vobject->VTODO->RRULE=$oldobject->VTODO->RRULE;
                        $data=$this->vobject->serialize();
                        $mod=true;
                        //file_put_contents('/tmp/davlog', $path . " RRULE restored.\n",FILE_APPEND);
                    }  
                } else {
                    //Here is where we should check if we need to create a record in the recurrence table for the object. this will be handled when we instantiate the Reminder class with the object.
                    $Reminder= new \Reminder($this->vobject);

                    // Prep this statement but execute it only if this is a new record. The server and not the client should manage changes to the start date. 
                    $sdMySQL= \DateTimeExt::MySQLDate($this->vobj->VTODO->DTSTART->getDateTime());
                    $sql1="UPDATE recurrence SET start_date='".$sdMySQL ."' WHERE uid='" . $uid . "'";
                    // Execute $sql0 and $sql1 as appropriate
                    
                    // ...

                    //Now handle the RRULE property (for new records)

                    // ...

                    // Do we allow external clients to make any changes at all to an existing RRULE?

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