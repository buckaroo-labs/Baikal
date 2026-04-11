<?php
require_once 'lib/functions.php';
require_once 'Hydrogen/clsDateTimeExt.php';
require_once 'Hydrogen/db/clsSQLBuilder.php';
//In theory, the SQLBuilder class will sanitize 
//	all the POST variables used as column data

function calculate_weekdays() {
	global $sqlb;
	$daysOfWeek="";
	if (isset($_POST['MondayYN'])) $daysOfWeek .= "M";
	if (isset($_POST['TuesdayYN'])) $daysOfWeek .= "T";
	if (isset($_POST['WednesdayYN'])) $daysOfWeek .= "W";
	if (isset($_POST['ThursdayYN'])) $daysOfWeek .= "t";
	if (isset($_POST['FridayYN'])) $daysOfWeek .= "F";
	if (isset($_POST['SaturdayYN'])) $daysOfWeek .= "S";
	if (isset($_POST['SundayYN'])) $daysOfWeek .= "s";	
	if ($daysOfWeek!="") $sqlb->addColumn("days_of_week",$daysOfWeek);
	return 1;
}
function calculate_blackout_hours () {
	global $sqlb;
	if (isset($_POST['SilentHoursYN'])) {
		if (isset(_POST['TimeOfDayStart'])) {  
			$startTime=$_POST['TimeOfDayStart'];
			$startTime=str_replace($startTime,":","");
			$startTime=substr($startTime,1,4);
			$sqlb->addColumn("day_start",$startTime);
		}
		if (isset(_POST['TimeOfDayEnd'])) {  
			$endTime=$_POST['TimeOfDayEnd'];
			$endTime=str_replace($endTime,":","");
			$endTime=substr($endTime,1,4);
			$sqlb->addColumn("day_end",$endTime);
		}
	}
	return 1;
}

function calculate_seasonality() {
	global $sqlb;
	//UI year starts at day 1; DB year starts at day zero
	if (isset($_POST['SilentDaysYN'])) {
		if (isset(_POST['season_start'])) {  
			$startday= (int) $_POST['season_start'] -1;
			$sqlb->addColumn("day_start",$startday);
		}
		if (isset(_POST['season_end'])) {  
			$endday= (int) $_POST['season_end'] -1;
			$sqlb->addColumn("day_end",$endday);
		}
	}
	return 1;
}
function calculate_enddate() {
	global $sqlb;
	global $endDate;
	global $endDateStr;
	if (($_POST['EndDate'])!="")  {
		$endDateStr=$_POST['EndDate'];
			if (($_POST['EndTime'])!="") {
				$endDateStr .= " " . $_POST['EndTime'];
			}
		$sqlb->addColumn("end_date",$endDateStr);
		$endDate=strtotime($endDateStr);
	}
	return 1;
}		
		
function calculate_duedate() {
	global $sqlb;		
	global $startDate;
	global $dueDate;
	global $dueDateStr;
	$columns = array('grace_scale','grace_units');
	if (isset($_POST['GraceTime'])) {
		if($_POST['GraceTime']=="DueYN") {
			$sqlb->addVarColumns($columns);
			$interval ="+" . $_POST['grace_units'] . " " . decode_scale($_POST['grace_scale']);
			$dueDate = strtotime($interval,$startDate);
			$dueDateStr = date("Y-m-d H:i:s",$dueDate);
			$sqlb->addColumn("due_date",$dueDateStr);
		}
	} else {
		$sqlb->addNullColumn("grace_units");
	}
	return 1;
}
		
function calculate_alarms() {
	global $sqlb;
	global $startDate;
	
	$columns = array('passive_scale',					
					'passive_units',
					'alarm_interval_scale',
					'alarm_interval_units'
					);
	if (isset($_POST['Alarms'])){
		if($_POST['Alarms']=="AlarmYN") {
			$sqlb->addVarColumns($columns);
			$interval ="+" . $_POST['passive_units'] . " " . decode_scale($_POST['passive_scale']);
			$alarmDate = strtotime($interval,$startDate);	
			$almDateStr = date("Y-m-d H:i:s",$alarmDate);			
			$sqlb->addColumn("active_date",$almDateStr);
		} else {
			$sqlb->addNullColumn("passive_units");
		}
	} else {
		$sqlb->addNullColumn("passive_units");
	}
	return 1;
}	

function calculate_rrule() {
	$rrule='';
	if($_POST['Recurrence']=="RecurYN" && isset($_POST['recur_scale']) && isset($_POST['recur_units'])) {
		if (is_numeric($_POST['recur_scale']) && is_numeric($_POST['recur_units']) ) {
			switch ($_POST['recur_scale']) {
				case 1:
					$rrule='FREQ=DAILY;INTERVAL=' .$_POST['recur_units'];
					break;
				case 2:
					$rrule='FREQ=WEEKLY;INTERVAL=' .$_POST['recur_units'];
					break;
				case 3:
					$rrule='FREQ=MONTHLY;INTERVAL=' .$_POST['recur_units'];
					break;	
				case 4:
					$rrule='FREQ=YEARLY;INTERVAL=' .$_POST['recur_units'];
					break;
				default:
					$rrule='FREQ=HOURLY;INTERVAL=' .$_POST['recur_units'];
			}
		}
	}
	return $rrule;
}

function common_post_proc() {
	global $sqlb;
	global $dds;
	global $startDate;
	global $endDate;
	global $dueDate;
	global $endDateStr;
	global $dueDateStr;

	//$sqlb->addColumn("owner",$_SESSION['username']);
	
	//process POST variables into sanitized SQL stmt
	
	//some are easy
	//$columns = array('snooze_scale','snooze_units'	);
	//$sqlb->addVarColumns($columns);
	
	//others require more processing
	$startDateStr=$_POST['StartDate'] . " " . $_POST['StartTime'];
	$sqlb->addColumn("start_date",$startDateStr);
	$startDate= strtotime($startDateStr);	
	
	//Recurrence
	$columns = array('recur_scale','recur_units','recur_float');
	if (isset($_POST['Recurrence'])) {
		if($_POST['Recurrence']=="RecurYN") $sqlb->addVarColumns($columns);
	}
	
	$return=calculate_duedate();
	$return=calculate_alarms();
	$return=calculate_seasonality() ;
	$return=calculate_blackout_hours();
	$return=calculate_enddate();		
	$return=calculate_weekdays();
	$rrule=calculate_rrule();
	
	$SQL=$sqlb->getSQL();
	//final security check before committing changes:
	if(isset($_SESSION['username'])) {
		$checkOwner="SELECT count(*) FROM calendarobjects o inner join calendarinstances i on o.calendarid=i.id where o.uid='" . $_POST['ID'] . "' and i.principaluri='principals/" . $_SESSION['username'] . "'";
		$result=$dds->setSQL($checkOwner);
		$rrow=$dds->getNextRow();
		if ($rrow[0]==1 && isset($_GET['id']) && is_numeric($_GET['id'])) {
			$dds->setSQL($SQL);
			//update DTSTART, DUE and DTEND in the vobject
			$o=new VTODO($_GET['id']);
			$f_in='Y-m-d H:i';
			$f_out='Ymd\THis\Z';
			$nvp=array();

			if (strlen($endDateStr) ==10 && strpos($endDateStr,':')===false) $endDateStr .= " 00:00";

			if (strlen($endDateStr) > 4 && $ed=\DateTime::createFromFormat($f_in,$endDateStr)) $nvp['DTEND']=$ed->format($f_out); elseif (strlen($endDateStr) > 4 ) debug("error saving end date: " . $endDateStr);

			// error saving end date: 2026-04-10

			if ($sd=\DateTime::createFromFormat($f_in, $startDateStr)){
				if (!isset($ed) || $ed>$sd) $nvp['DTSTART']=$sd->format($f_out);
			}  else debug("error saving start date: '" . $startDateStr ."'");

			if (strlen($dueDateStr) > 4 && $dd=\DateTime::createFromFormat($f_in.":s",$dueDateStr)) {
				if (!isset($ed) || $ed>$dd) $nvp['DUE']=$dd->format($f_out);
			} else debug("error saving due date: " . $dueDateStr);

			if (strlen($rrule) > 1 ) $nvp['RRULE']=$rrule;

			/*
			if ($sd=\DateTime::createFromFormat($f_in, $startDateStr)) $o->VTODO->DTSTART=$sd->format($f_out); else debug("error saving start date: '" . $startDateStr ."'");
			if (strlen($endDateStr) > 4 && $ed=\DateTime::createFromFormat($f_in,$endDateStr)) $o->VTODO->DTEND=$ed->format($f_out); else debug("error saving end date: " . $endDateStr);
			if (strlen($dueDateStr) > 4 && $dd=\DateTime::createFromFormat($f_in,$dueDateStr)) $o->VTODO->DUE=$dd->format($f_out . ":s"); else debug("error saving due date: " . $dueDateStr);
*/
			$o->setProperties($nvp);
			$o->save();
			
		}	
	}
}	
$endDate=new \DateTime;		
$endDateStr='';
$dueDateStr='';
if ($_POST['ID']=="new") {
	$timestamp = (string) time();	
	$sqlb = new SQLBuilder("INSERT");
	$sqlb->setTableName('recurrence');

	$sqlb->addColumn("sequence",$timestamp);
	$sqlb->addColumn("created",\DateTimeExt::zdate());
	$sqlb->addColumn("uid",$_POST['ID']);

	common_post_proc();
	$newID=$dds->getInt("select max(id) from recurrence");


} else {
//not "new"	
	if (isset($_POST['DIRTY'])) {
		$sqlb = new SQLBuilder("UPDATE");
		$sqlb->setTableName('recurrence');
		$sqlb->addWhere("uid='" . $_POST['ID']. "'");
		$sqlb->addColumn("last_modified",DateTimeExt::zdate());

		common_post_proc();
		
	} //dirty


} //not new

?>