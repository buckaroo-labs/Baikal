<?php
require_once "lib/clsVCALENDAR.php";

class VTODO extends VCALENDAR {
//Class hierarchy: DAVObject->VCALENDAR->VTODO
    public function __construct($id=0,$summary="New vtodo",$parenturi="") {
        parent::__construct($id,$summary,$parenturi);
        $this->componenttype='VTODO';
        if ($id==0) {
            $this->vobject->add('VTODO', [
                'SUMMARY' => $summary,
                'STATUS' => 'OPEN',
                'DTSTART' => new \DateTime()
            ]);
            //set the ParentID
            $this->fetch(0,$parenturi);
        } else {
            //look up an existing one
            $this->fetch($id,$parenturi);
        }  
    } //end construct

    public function markIncomplete() {
        if (isset($this->vobject->VTODO->COMPLETED)) {
            unset($this->vobject->VTODO->COMPLETED);
            $this->vobject->VTODO->STATUS = 'OPEN';
            $this->modified=true;
        }
    }

    public function markComplete() {
        if (!isset($this->vobject->VTODO->COMPLETED)) {
            $this->vobject->VTODO->COMPLETED = new \DateTime();
            $this->vobject->VTODO->STATUS = 'COMPLETED';
            $this->modified=true;
        }
    }

    public function getCategories() {
        $returnValue='';
        if (isset($this->vobject->VTODO->CATEGORIES)) {
            $returnValue=(string)$this->vobject->VTODO->CATEGORIES;
        }
        return $returnValue;
    }

    public function toggle() {
        if (!isset($this->vobject->VTODO->COMPLETED)) {
            if ($this->parenturi!="recurring") {
                $this->markComplete();
            } else {
                $data=$this->getVObject();
                $r = new \Reminder($data);
                $r->markComplete();
                $r->save();
            }
        } else {  
            $this->markIncomplete();
        }
    }    

} //end class

require_once "lib/clsReminder.php";