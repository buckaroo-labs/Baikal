<?php

require_once "../vendor/autoload.php";
require_once "Hydrogen/db/clsDataSource.php";
use Sabre\VObject;
use Symfony\Component\Yaml\Yaml;
//define ("PROJECT_PATH_CONFIG","/var/www/config/");
class Reminder {
    private $reminderID;
    private $vcalendar;
    private $ds;
    private $dbconn;
    private $rowdata;
    private $calendaruri;
    private $calendarID;
    private $modified;

    public function __construct($id=0,$summary="New vtodo",$calendaruri="") {
        global $dds;
        $this->ds=$dds;
        //this class will depend on having MySQL as the back end
        $config = Yaml::parseFile(PROJECT_PATH_CONFIG . "baikal.yaml");
        $this->dbconn=new mysqli(
            $config['database']['mysql_host'],
            $config['database']['mysql_username'],
            $config['database']['mysql_password'],
            $config['database']['mysql_dbname']
            );


        if (strlen($calendaruri)>0 && $id==0) {
            $this->calendaruri = $calendaruri; 
        } elseif ($id==0)  {
            $this->calendaruri="default";
        }
        if ($id==0) {
            //make a new one
            $this->vcalendar = new VObject\Component\VCalendar();
            $this->vcalendar->add('VTODO', [
                'SUMMARY' => $summary,
                'DTSTART' => new \DateTime()
            ]);
        } else {
            //look up an existing one
            $columns=" c.id, c.uri, c.calendardata, i.displayname as calendarname, i.uri as calendaruri, i.id as calendarid ";
            $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
            $where=" WHERE c.componenttype='VTODO' and i.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $id;
            if (strlen($calendaruri)>0) $where .= " AND i.uri='" . $calendaruri . "'";
            $sql="SELECT " . $columns . $from . $where;
            $result=$this->ds->setSQL($sql);
            if ($rrow=$this->ds->getNextRow('assoc')) {
                $this->reminderID=$id; 
                $this->rowdata=$rrow;
                $this->vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);
                $this->calendaruri=$rrow['calendaruri'];
                $this->calendarID=$rrow['calendarid'];
                $this->modified=false;
            } else {
                return false;
            } //end row fetch
        } //end ID check
    } //end construct

    public function serialize() {
        return $this->vcalendar->serialize();
    }

    public function getReminderID() {
        return $this->reminderID;
    }

    public function getRowData() {
        return $this->rowdata;
    }

    public function getCalendarURI() {
        return $this->calendaruri;
    }

    public function getVObject() {
        return $this->vcalendar;
    }

    public function markIncomplete() {
        if (isset($this->vcalendar->VTODO->COMPLETED)) {
            unset($this->vcalendar->VTODO->COMPLETED);
            $this->vcalendar->VTODO->STATUS = 'OPEN';
            $this->modified=true;
        }
    }

    public function markComplete() {
        if (!isset($this->vcalendar->VTODO->COMPLETED)) {
            $this->vcalendar->VTODO->COMPLETED = new \DateTime();
            $this->vcalendar->VTODO->STATUS = 'COMPLETED';
            $this->modified=true;
        }
    }

    public function setData($cdata) {
        //the "nuclear option" for working with the object: explicitly set the whole thing
        $this->vcalendar = VObject\Reader::read($cdata, VObject\Reader::OPTION_FORGIVING);
        $this->modified=true;
    }

    public function save() {
        if (isset($this->reminderID)) {
            //update calendarobject calendardata, lastmodified (cdata and db row), size, first-and-last occurrence (as applicable) and etag;
            //also increment calendars synctoken by one 
            if (isset($this->modified) && $this->modified) {
                $this->vcalendar->VTODO->{'LAST-MODIFIED'} = new \DateTime();
                $newdata= $this->vcalendar->serialize();
                //$newdata= str_replace("'","''",$newdata);
                $sql="UPDATE calendarobjects SET size=?, lastmodified=?, calendardata=?, etag=? WHERE id=?";
                $sql2="UPDATE calendars SET synctoken=synctoken+1 WHERE id=?";
                if ($stmt=$this->dbconn->prepare($sql)) {
                    $stmt->bind_param("iissi", $datasize, $lastmod, $newdata, $etag, $this->reminderID);
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

function testit() {
    $r=new Reminder();
    echo $r->serialize();
}
