
<?php 
	require_once("lib/functions.php");
	$result = $dds->setSQL("SELECT * FROM recurrence WHERE uid='" . $rrow['uid']."'");
	if ($remdata = $dds->getNextRow("labeled")) {

	//$startdatestr = date("Y-m-d",strtotime($dtstart->format('Y-m-d H:i:s')));
	//$starttimestr = date("H:i",strtotime($dtstart->format('Y-m-d H:i:s')));
	$startdatestr = date("Y-m-d",strtotime($remdata['start_date']));
	$starttimestr = date("H:i",strtotime($remdata['start_date']));

	//$implementation_note=false;
	$output = "<h4>Server-side Rules</h4><table>";
	$output .= "<tr><td>Start: </td><td>$startdatestr at $starttimestr</td><tr>";

	if (isset($remdata['recur_units'])) {
		if (!is_null($remdata['recur_units'])) {
			if ($remdata['recur_float']==1) $recur_float = "completion"; else $recur_float="start";
			$temp = decode_scale_and_units($remdata['recur_scale'],$remdata['recur_units']);
			$temp = "Every $temp after previous $recur_float";
			
			if (isset($dtend)) {
				if (!is_null($dtend)) {

				//$enddatestr = date("Y-m-d",strtotime($dtend->format('Y-m-d H:i:s')));
				//$endtimestr = date("H:i",strtotime($dtend->format('Y-m-d H:i:s')));
				$enddatestr = date("Y-m-d",strtotime($remdata['end_date']));
				$endtimestr = date("H:i",strtotime($remdata['end_date']));
				//$output .= " and will not recur after " . $enddatestr  . " at " . $endtimestr;
				if (!is_null($enddatestr)) $temp .= " until $enddatestr at $endtimestr";
				}
			}			
					
			
			$output .= "<tr><td>Recurrence: </td><td>$temp</td><tr>";
		}
	}
	
	if (isset($remdata['grace_units'])) {

		$duedatestr = date("Y-m-d",strtotime($remdata['due_date']));
		$duetimestr = date("H:i",strtotime($remdata['due_date']));		
		if (!is_null($remdata['grace_units'])) {
			$temp = decode_scale_and_units($remdata['grace_scale'],$remdata['grace_units'],true);
			$output .= "<tr><td>Due: </td><td>$duedatestr at $duetimestr ($temp after start)</td><tr>";
		}
	}
		
	if (isset($remdata['passive_units'])) {
		if (!is_null($remdata['passive_units'])) {
			$alarmdatestr = date("Y-m-d",strtotime($remdata['active_date']));
			$alarmtimestr = date("H:i",strtotime($remdata['active_date']));	
			$temp = decode_scale_and_units($remdata['passive_scale'],$remdata['passive_units'],true);
			$alarm = decode_scale_and_units($remdata['alarm_interval_scale'],$remdata['alarm_interval_units']);
			$output .= "<tr><td>Alarms*: </td><td>Every $alarm beginning $alarmdatestr at $alarmtimestr ($temp after start)</td><tr>";
			$implementation_note = true;
		}
	}
	

	//$snooze_units = $remdata['snooze_units'];
	//$snooze_scale = $remdata['snooze_scale'];
	
	$days_of_week= "_" . $remdata['days_of_week'];
	if(is_null($remdata['days_of_week'])) $days_of_week="_MTWtFSs";
	if (!is_null($remdata['season_start'])) {
		$blackout_days=true;
		$season_start= (int) $remdata['season_start'] + 1;
	} else {
		$season_start= "1";
	}
	if (!is_null($remdata['season_end'])) {
		$blackout_days=true;
		$season_end= (int) $remdata['season_end'] +1;
	} else {
		$season_end= "365";
	}
	if (!is_null($remdata['day_start'])) {
		$blackout_hours=true;
		$temp = (string) $remdata['day_start'];
		$tempmin = substr($temp,1,-2);
		$temphour = str_replace($tempmin,'',$temp);
		$tempminute = (int) $tempmin;
		if ($tempminute > 59) $tempminute=59;
		if ($tempminute < 10) $tempminute= "0" . $tempminute;
		if ($temphour < 10) $temphour= "0" . $temphour;
		$tod_start= $temphour . ":" . $tempmin ;
	} else {
		$tod_start= "00:00";
	}
	if (!is_null($remdata['day_end'])) {
		$blackout_hours=true;
		$temp = (string) $remdata['day_end'];
		$tempmin = substr($temp,1,-2);
		$temphour = str_replace($tempmin,'',$temp);
		$tempminute = (int) $tempmin;
		if ($tempminute > 59) $tempminute=59;
		if ($tempminute < 10) $tempminute= "0" . $tempminute;
		if ($temphour < 10) $temphour= "0" . $temphour;
		$tod_end= $temphour . ":" . $tempmin ;
	} else {
		$tod_end= "23:59";
	}
	$output .="</table>";
	if (isset($implementation_note)) $output .= "<P>(* = not implemented)</P>" ;
	echo '<p name="reminder_description">' . $output . "</p>";
	//echo '<p><a href="edit_recurrence.php?id=' . $reminderid . '">Edit</a></p>';
	echo '<button id="editrecurrencebutton" onclick="editrecurrence()">Edit</button>';

	}
