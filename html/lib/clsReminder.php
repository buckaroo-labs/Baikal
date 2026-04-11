<?php
require_once "lib/clsVTODO.php";
require_once "lib/functions.php";
require_once ("Hydrogen/db/clsDataSource.php");
require_once ("Hydrogen/clsDateTimeExt.php");
class Reminder extends VTODO {
    //This class may only partially be able to call parent methods due to differences in construction.
    //Recurrence management functions apply here

    //Class hierarchy: DAVObject->VCALENDAR->VTODO->Reminder

	protected $reminder;
    //protected $vobject;

	public function __construct(&$vobject,$owner="") {
        parent::__construct();
        $this->vobject=$vobject;
        $this->componenttype="VTODO";
        //assuming that ownership has already been checked.
        //this class will create a row in the recurrence table if one does not already exist for the $vobject UID

        // Given that we don't know if this object has been stored in the database yet, we must match on UID rather than trying to match on the table's primary key.
        if (is_array($vobject->VTODO)) $UID=$vobject->VTODO[0]->UID;
        else $UID=$vobject->VTODO->UID;
        $this->objectID=0;
		global $dds;
        $sql="SELECT max(id) FROM calendarobjects WHERE uid='" . $UID . "'";
        $result = $dds->setSQL($sql);
        $rrow=$dds->getNextRow();
        if (!is_null($rrow[0])) {
            $this->objectID=$rrow[0];
            $this->fetch($rrow[0]);
        }
        //This statement will silently fail if a matching record already exists
        $sql0="INSERT IGNORE INTO recurrence (uid,sequence) VALUES ('" . $UID . "','" . rand(1,99999999). "')";
		$result = $dds->setSQL($sql0);
		$sqlowner=$owner;
		if (isset($_SESSION['username'])) $sqlowner=$_SESSION['username'];

		$this->refresh(); 
	}
    private function refresh() {
        global $dds;
        
      	$sql= "SELECT * FROM recurrence WHERE uid='". $this->vobject->VTODO->UID . "'";
		$result = $dds->setSQL($sql);
		$result_row = $dds->getNextRow("assoc");
		$this->reminder = $result_row; 
        $this->vobject->VTODO->{'X-2DOAPP-METADATA'}=$this->get2DoAppMeta();  
        $this->vobject->VTODO->{'X-2DOAPP-METADATA'}['SHARE-SCOPE']='GLOBAL';
    }
    public function delete() {
        global $dds;
        $sql="DELETE FROM recurrence WHERE uid='" . $this->vobject->TODO->UID . "'";
        $result = $dds->setSQL($sql);
        parent::delete();
        //$sql="DELETE FROM calendarobjects WHERE uid='" . $this->vobject->TODO->UID . "'";
        //$result = $dds->setSQL($sql);
    }
  
	public function get2DoAppMeta ($verbose=false) {

        //This section is especially for the 2Do Android app which for some reason
        //ignores the standard "DTSTART" iCal line
        $temp=new DateTime($this->reminder['start_date']);
        $SDATEL = $temp->getTimestamp();
        $temp->setTimezone(new DateTimeZone("UTC"));
        $tempstr= $temp->format('Y-m-d H:i:s');
        $temp=new DateTime($tempstr);
        $SDATE=$temp->getTimestamp();
        $X2DoMeta="<2Do Meta>%7B%22uid%22%3A%22" . $this->reminder['uid'] . "%22%2C%22parentuid%22%3A%22%22%2C%22outlookuid%22%3A";
        $X2DoMeta .= "%22%22%2C%22tags%22%3A%22%22%2C%22locations%22%3A%22%22%2C%22actionValue%2";
        $X2DoMeta .= "2%3A%22%22%2C%22actionType%22%3A-1%2C%22RUID%22%3A%22%22%2C%22RecurrenceFr";
        $X2DoMeta .= "om%22%3A1%2C%22RecurrenceType%22%3A0%2C%22RecurrenceValue%22%3A0%2C%22Recu";
        $X2DoMeta .= "rrenceEndType%22%3A0%2C%22RecurrenceEndRepetitions%22%3A0%2C%22RecurrenceE";
        $X2DoMeta .= "ndRepetitionsOrig%22%3A0%2C%22RecurrenceEndDate%22%3A0%2C%22RecurrenceEndD";
        $X2DoMeta .= "ateLocal%22%3A0%2C%22StartDayDelay%22%3A0%2C%22TaskType%22%3A0%2C%22isExpa";
        $X2DoMeta .= "ndedToShowChildProjects%22%3A0%2C%22IsStarred%22%3A0%2C%22DisplayOrder%22%";
        $X2DoMeta .= "3A0%2C%22TaskDuration%22%3A0%2C%22duetime%22%3A999999%2C%22StartDate%22%3A";
        $X2DoMeta .= $SDATE . "%2C%22StartDateLocal%22%3A" . $SDATEL . "%7D</2Do Meta>";
        $X2DoMeta .= "";
		return $this->icsEncode($X2DoMeta);
        //return $this->icsEncode("X-2DOAPP-METADATA;SHARE-SCOPE=GLOBAL:" . $X2DoMeta);
	
	}
	
	private function icsEncode ($content) {
		$output=str_replace("&rsquo;","'",$content);
		$output=str_replace("&quot;",'"',$output);
		return $output . "\n";
	}

    public function markComplete() {
        global $dds;
        $this->modified=true;
        unset ($this->vobject->VTODO->{'PERCENT-COMPLETE'});
        $sql =  "UPDATE recurrence SET complete_date='" . date("Y-m-d H:i:s") . "', ";
        $reminder=$this->reminder;
        //check for recurrence
        if (!is_null($reminder['recur_units'])) {
            debug("recurring task completed");
            $recurscale = decode_scale($reminder['recur_scale']);
            $gracescale = decode_scale($reminder['grace_scale']);
            $passivescale = decode_scale($reminder['passive_scale']);
            
            if ($reminder['recur_float']==0) {
                $initdate = strtotime($reminder['start_date']);
                debug("recurrence set as fixed, with base date: " .$reminder['start_date']);
            } else {
                $initdate=time();
                debug("recurrence set as floating");
            }	
            debug("recurrence will follow date:" . date("Y-m-d H:i:s",$initdate));
            //Calculate the next correct start time	
            debug("next start: " . "+" . $reminder['recur_units'] . " " . $recurscale);
            $starttime = strtotime("+" . $reminder['recur_units'] . " " . $recurscale, $initdate );
            //Format startdate for MySQL
            $startdate = date("Y-m-d H:i:s",$starttime);
            $this->vobject->VTODO->DTSTART=\DateTimeExt::CalDAVZFormatFromMySQLDateTime($startdate);
            debug("next recurrence date:" . $startdate);
            if (!is_null($reminder['grace_units'])) {
                debug("next recurrence due: " . "+" . $reminder['grace_units'] . " " . $gracescale);
                $duetime = strtotime("+" . $reminder['grace_units'] . " " . $gracescale,$starttime);
                $duedate = date("Y-m-d H:i:s",$duetime);
                $this->vobject->VTODO->DUE=\DateTimeExt::CalDAVZFormatFromMySQLDateTime($duedate);
                debug("next recurrence due date:" . $duedate);
                $sql = $sql . " due_date='" . $duedate . "', ";	
             } else {
                unset($this->vobject->VTODO->DUE);
             }
            $this->vobject->VTODO->STATUS="OPEN";
            unset ($this->vobject->VTODO->COMPLETED);
            
            debug("next recurrence active: " . "+" . $reminder['passive_units'] . " " . $passivescale);
            $activetime = strtotime("+" . $reminder['passive_units'] . " " . $passivescale,$starttime) ;
            $activedate = date("Y-m-d H:i:s",$activetime);
            debug("next recurrence active date:" . $activedate);
            
            $sql = $sql . " start_date='" . $startdate . "', ";				
            $sql = $sql . " active_date='" . $activedate . "', ";	
            debug($sql);
            
        } else {
            //non-recurring reminder will be expired
            $sql = $sql . " end_date='" . date("Y-m-d H:i:s",strtotime("-1 second")) . "', ";
        }
        $timestamp = (string) time();
        $sequence=$reminder['id'] . '000' .  $timestamp ;
        $sql = $sql . " sequence=" . $sequence . ", ";
        $sql = $sql . " last_modified='" . DateTimeExt::zdate() . "' ";
        $sql = $sql . " WHERE id=" . $reminder['id']  ;

        //sql has not yet been executed, and not all vobject changes have been calculated
        $this->vobject->VTODO->{'LAST-MODIFIED'}=\DateTimeExt::zdate();
        $this->vobject->VTODO->{'X-CADENCE-SEQUENCE'}=$sequence;
        //(Testing required to see if vobject changes get passed back by reference)
        //Uupdate recurrence record
        $result = $dds->setSQL($sql);
        $this->refresh(); 
    }
	

} //end class

function testit() {
    $r=new Reminder();
    echo $r->serialize();
}


