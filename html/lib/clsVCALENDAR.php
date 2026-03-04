<?php
require_once "lib/clsDAVObject.php";
use Sabre\VObject;
class VCALENDAR extends DAVObject {
    protected $componenttype;

    protected function fetch($id,$parenturi='') {
        $columns=" c.id, c.uri, c.calendardata, i.displayname as calendarname, i.uri as parenturi, i.id as calendarid ";
        $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
        $where=" WHERE c.componenttype=".$this->componenttype." and i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $id;
        if (strlen($parenturi)>0) $where .= " AND i.uri='" . $parenturi . "'";
        $sql="SELECT " . $columns . $from . $where;
        $result=$this->ds->setSQL($sql);
        if ($rrow=$this->ds->getNextRow('assoc')) {
            $this->objectID=$id; 
            $this->rowdata=$rrow;
            $this->vobject = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
            $this->parenturi=$rrow['parenturi'];
            $this->parentID=$rrow['calendarid'];
            $this->modified=false;
        } else {
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
                $this->vobject->add($key,$value);
                }
        }
    }

    private function setModificationTimeToNow() {
        if ($this->componenttype=='VTODO') {
            $this->vobject->VTODO->{'LAST-MODIFIED'} = new \DateTime();
        } elseif ($this->componenttype=='VEVENT') {
            $this->vobject->VEVENT->{'LAST-MODIFIED'} = new \DateTime();
        } elseif ($this->componenttype=='VJOURNAL') {
            $this->vobject->VJOURNAL->{'LAST-MODIFIED'} = new \DateTime();
        }
    }

    public function save() {
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
                    $stmt->bind_param("i",$this->calendarID);
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
            $uid=md5(uniqid());
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

