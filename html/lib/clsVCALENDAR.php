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
            $this->calendarID=$rrow['calendarid'];
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

    public function save() {
        if (isset($this->objectID)) {
            //update calendarobject calendardata, lastmodified (cdata and db row), size, first-and-last occurrence (as applicable) and etag;
            //also increment calendars synctoken by one 
            if (isset($this->modified) && $this->modified) {
                if ($this->componenttype=='VTODO') {
                    $this->vobject->VTODO->{'LAST-MODIFIED'} = new \DateTime();
                } elseif ($this->componenttype=='VEVENT') {
                    $this->vobject->VEVENT->{'LAST-MODIFIED'} = new \DateTime();
                } elseif ($this->componenttype=='VJOURNAL') {
                    $this->vobject->VJOURNAL->{'LAST-MODIFIED'} = new \DateTime();
                }
                $newdata= $this->vobject->serialize();
                //$newdata= str_replace("'","''",$newdata);
                $sql="UPDATE calendarobjects SET size=?, lastmodified=?, calendardata=?, etag=? WHERE id=?";
                $sql2="UPDATE calendars SET synctoken=synctoken+1 WHERE id=?";
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
            //also update calendarinstance sync token? 
        }
    }
} //end class

