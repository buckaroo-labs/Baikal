<?php
require_once "lib/clsDAVObject.php";
require_once "../vendor/sabre/dav/lib/CalDAV/Backend/PDO.php";
use Sabre\VObject;
class VCALENDAR extends DAVObject {
    protected $componenttype;
    protected $rowdata;

    protected function fetch($id,$parenturi='') {
        $owner='nobody';
        if (isset($_SESSION['username'])) $owner=$_SESSION['username']; elseif (isset($_SERVER['PHP_AUTH_USER'])) $owner=$_SERVER['PHP_AUTH_USER'];
        //we're going to fudge so we can use all this code to determine the parent ID for a new item too
        if ($id!=0) {
            //this function is called by child classes, which will have set $this->componenttype
            $columns=" c.id, c.uri, c.calendardata, i.displayname as calendarname, i.uri as parenturi, i.id as calendarid ";
            $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
            //$where=" WHERE c.componenttype='".$this->componenttype."' and i.principaluri='principals/" . $owner . "' and c.id=" . $id;
            $where=" WHERE c.componenttype='".$this->componenttype."' and i.principaluri='principals/" . $owner . "' and c.id=" . $id;
        } else {        
            $columns="i.displayname as calendarname, i.uri as parenturi, i.id as calendarid ";
            $from=" FROM calendarinstances i ";
            $where=" WHERE i.principaluri='principals/" . $owner . "' ";
        }
        if (strlen($parenturi)>0) $where .= " AND i.uri='" . $parenturi . "'";
        $sql="SELECT " . $columns . $from . $where;
        $result=$this->ds->setSQL($sql);
        if ($rrow=$this->ds->getNextRow('assoc')) {
            if ($id!=0) {
                $this->objectID=$id; 
                $this->rowdata=$rrow;
                $this->vobject = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
                $this->parenturi=$rrow['parenturi'];
                $this->modified=false;
                debug ("Fetched VCALENDAR component from database to rowdata"); 
            }
            $this->parentID=$rrow['calendarid'];           
        } elseif ($id!=0) {
            debug ("Error fetching VCALENDAR component from database");  
            return false;
        } else {
            debug ("Error fetching VCALENDAR parent ID from database");  
            return false;
        } //end row fetch
    }

    public function __construct($id=0,$summary="New vcalendar",$parenturi="") {
        parent::__construct();

        if (strlen($parenturi)>0 && $id==0) {
            $this->parenturi = $parenturi; 
        } elseif ($id==0)  {
            $this->parenturi="default";
        }
        if ($id==0) {
            //make a new one
            $this->vobject = new VObject\Component\VCalendar();

            //child object will have to add component VTODO, VEVENT or VJOURNAL
        } //else: child object will have to fetch, based on component type

        //child object must set componenttype class property

    } //end construct

    public function setProperties($nvp) {
        //takes an associative array of name-value pairs (e.g. $_POST) and applies 
        //any of them which are relevant to the object; let's try any index names in all caps
        foreach ($nvp as $key => $value) {
            if ($key==strtoupper($key)) {
                debug("Setting VCALENDAR property " . $key . " to " . $value);
                //$this->vobject->{$this->componenttype}->add($key,$value);
                $this->vobject->{$this->componenttype}->{$key}=$value;
                $this->modified=true;
            }
        }
    }

    private function setModificationTimeToNow() {
        $this->vobject->{$this->componenttype}->{'LAST-MODIFIED'} = new \DateTime();
    }
    public function delete() {
        //former logic:
        //if (isset($this->objectID)) $this->ds->setSQL("DELETE from calendarobjects where id=" . $this->objectID);

        debug ("VCALENDAR 'delete' method called");
        //Bootstrap the frameworks to use ../vendor/sabre/dav/lib/CalDAV/Backend/PDO.php
        if (!isset($GLOBALS['DB'])) require_once("server.php");
        $pdo=$GLOBALS['DB']->getPDO();
        $backend=new Sabre\CalDAV\Backend\PDO($pdo);
        if (isset($this->parenturi)) {

            //04-APR-2026 this SQL was not specific enough
            //$sql0="SELECT id, calendarid FROM calendarinstances WHERE uri='" . $this->parenturi . "'";
            //The value passed to $backend->deleteCalendarObject($calendarId) is expected to be an array with a calendarId and an instanceId; don't they always match?
            if (isset($this->parentID)) {
            //if ($rrow=$this->ds->getNextRow()) {
                //list($this->parentID,$parentCal)=$rrow;
                $calIDs=array($this->parentID,$this->parentID);
                $backend->deleteCalendarObject($calIDs,$this->rowdata['uri']);
            } else {
                //debug ("VCALENDAR 'delete' method called without valid parent uri");
                debug ("VCALENDAR 'delete' method called without valid parent ID");
            }
        }
    }
    
    public function save() {
        //should use ../vendor/sabre/dav/lib/CalDAV/Backend/PDO.php
        //public function updateCalendarObject($calendarId, $objectUri, $calendarData)
        //public function createCalendarObject($calendarId, $objectUri, $calendarData)
        debug ("VCALENDAR 'save' method called");

/* 04-APR-2026 change all this
        if (isset($this->parenturi)) {
            $sql0="SELECT id, calendarid FROM calendarinstances WHERE uri='" . $this->parenturi . "'";
            $this->ds->setSQL($sql0);
            if ($rrow=$this->ds->getNextRow()) list($this->parentID,$parentCal)=$rrow;
        }
*/


        //The value passed to $backend->updateCalendarObject($calendarId) is expected to be an array with a calendarId and an instanceId 
        //if (isset($this->parentID) && isset($parentCal)) $calIDs=array($parentCal,$this->parentID);
        if (isset($this->parentID)) $calIDs=array($this->parentID,$this->parentID);
        if (!isset($GLOBALS['DB'])) require_once("server.php");
        $pdo=$GLOBALS['DB']->getPDO();
        $backend=new Sabre\CalDAV\Backend\PDO($pdo);
        if (isset($this->objectID)) {
            debug ("VCALENDAR object ID is set");
            if (isset($this->modified) && $this->modified) {
                $this->setModificationTimeToNow();
                $newdata= $this->vobject->serialize();
                if (!isset($this->rowdata))  debug ("VCALENDAR 'save' method called without valid rowdata");
                if (isset($calIDs)) $backend->updateCalendarObject($calIDs,$this->rowdata['uri'],$newdata);
                else debug ("VCALENDAR 'save' method called without valid parent ID");
            }
        } else {
            //create calendarobject
            $this->setModificationTimeToNow();
            $newdata= $this->vobject->serialize();
            if (isset($calIDs)) $backend->createCalendarObject($calIDs,$this->vobject->{$this->componenttype}->UID . ".ics",$newdata);
            else debug ("VCALENDAR 'save' method called without valid parent ID");
        }
    }

    public function deprecated_save() {
        //should use ../vendor/sabre/dav/lib/CalDAV/Backend/PDO.php instead
        //public function updateCalendarObject($calendarId, $objectUri, $calendarData)
        //public function createCalendarObject($calendarId, $objectUri, $calendarData)
        debug ("VCALENDAR 'save' method called");
        
        if (isset($this->objectID)) {
            debug ("VCALENDAR object ID is set");
            //update calendarobject calendardata, lastmodified (cdata and db row), size, first-and-last occurrence (as applicable) and etag;
            //also increment calendars synctoken by one 
            if (isset($this->modified) && $this->modified) {
                debug("VCALENDAR object has been modified");
                $this->setModificationTimeToNow();
                $newdata= $this->vobject->serialize();
                $sql="UPDATE calendarobjects SET size=?, lastmodified=?, calendardata=?, etag=? WHERE id=?";

                $sql2="UPDATE calendars SET synctoken=synctoken+1 WHERE id=?";
                debug ("Preparing SQL update");
                if ($stmt=$this->dbconn->prepare($sql)) {
                    $stmt->bind_param("iissi", $datasize, $lastmod, $newdata, $etag, $this->objectID);
                    $lastmod=time();
                    $etag=md5($newdata);
                    $datasize=strlen($newdata); 
                    $stmt->execute();
                    $stmt=$this->dbconn->prepare($sql2);
                    $stmt->bind_param("i",$this->parentID);
                    $stmt->execute();
                } else {
                    //uh-oh!
                }

            }
        } else {
            //insert calendarobject
            //also update calendarinstance sync token
            $sql0="SELECT id FROM calendarinstances WHERE uri='" . $this->parenturi . "'";
            $this->ds->setSQL($sql0);
            if ($rrow=$this->ds->getNextRow('assoc')) $this->parentID=$rrow['id'];
            $sql="INSERT INTO calendarobjects (calendardata,uri,calendarid,lastmodified,etag,size,componenttype,firstoccurence,lastoccurence,uid) 
            VALUES (?,?,?,?,?,?,?,null,null,?)";
            debug ("Preparing SQL update");
            $this->setModificationTimeToNow();
            $newdata= $this->vobject->serialize();
            $uid =$this->vobject->{$this->componenttype}->UID;
            $uri=$uid . ".ics";
            $lastmod=time();
            $etag=md5($newdata);
            $datasize=strlen($newdata); 
            if ($stmt=$this->dbconn->prepare($sql)) {
                $stmt->bind_param("ssiisiss",$newdata,$uri,$this->parentID,$lastmod,$etag,$datasize,$this->componenttype,$uid );
                $stmt->execute();
                $sql2="UPDATE calendars SET synctoken=synctoken+1 WHERE id=?";
                $stmt=$this->dbconn->prepare($sql2);
                $stmt->bind_param("i",$this->parentID);
                $stmt->execute();
            } else {
                //uh-oh!
            }
        }
    }
} //end class

