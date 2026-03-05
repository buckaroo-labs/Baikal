<?php
//see https://sabre.io/dav/writing-plugins/
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\VObject;

class RecurrencePlugin extends ServerPlugin {

    protected $server;
    private $vobject;

    function getName() {

        return 'recurrence';

    }

    function initialize(Server $server){

        $this->server = $server;
        $server->on('beforeWriteContent',[$this,'vtodoUpdateHandler' ]);

    }

    function vtodoUpdateHandler($path, \Sabre\DAV\IFile $node, &$data, &$modified) {
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        file_put_contents('/tmp/davlog', $path . " is to be updated.\n",FILE_APPEND);
        $this->vobject = VObject\Reader::read($data, VObject\Reader::OPTION_FORGIVING);
        //inspect the $data and the $node that it targets; if there was an RRULE but no longer will be, and if this is in the user's 'recurring' calendar URI, restore the RRULE to the $data and set $modified to true.
        if (isset($this->vobject->VTODO) && 
            !isset($this->vobject->VTODO->RRULE) &&
            strpos($path,"/recurring/")!=='false'
            ) {
            //calendars/heiner/lists/d704fa812a9e82ac8ba644f11e51da0e.ics has no RRULE.
            file_put_contents('/tmp/davlog', $path . " will have no RRULE.\n",FILE_APPEND);

            $olddata=$node->get();
            $oldobject=VObject\Reader::read($olddata, VObject\Reader::OPTION_FORGIVING);
            if (isset($oldobject->VTODO->RRULE)) {
                $this->vobject->VTODO->RRULE=$oldobject->VTODO->RRULE;
                $data=$this->vobject->serialize();
                $modified=true;
                file_put_contents('/tmp/davlog', $path . " RRULE restored.\n",FILE_APPEND);
            } else {
                $modified=false;
            }
        } else {
            $modified=false;
        }
        return true;   
    }

}