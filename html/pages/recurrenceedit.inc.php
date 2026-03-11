<?php 

	$timeofday = date("Hi");
	$dayofyear = date("z");
	$dayofweek = date("l");
	//Day name comparisons will be made on first character. 
	//Distinguish e.g. T(hursday) from T(uesday) by changing case
	if ($dayofweek=="Thursday") $dayofweek = strtolower($dayofweek); 
	if ($dayofweek=="Sunday") $dayofweek = strtolower($dayofweek);
	
	 
	//STEP ONE
	//Collect start, end  
	//"done" or "more"


	if ($new) {
		echo ('<h4>Set rules</h4>'); 
		$startdatestr = date("Y-m-d");
		$starttimestr = date("H:i");
		$recur_float = 1;
		$recur_units = 1;
		$recur_scale = 1;
		$grace_units = 1;
		$grace_scale = 1;
		$passive_units = 1;
		$passive_scale = 1;
		$alarm_interval_units = 1;
		$alarm_interval_scale = 1;
		$days_of_week="_MTWtFSs";
		$season_start="1";
		$season_end="365";
		$tod_start="00:00";
		$tod_end="23:59";
		$calendar_id=0;
	} else {
		echo ('<h4>Edit rules</h4>');
		//$result = $dds->setSQL("SELECT * FROM recurrence WHERE uid='" . $rrow['uid']  . "'");
		//$rrow = $dds->getNextRow("labeled");
		$startdatestr = date("Y-m-d",strtotime($rrow['start_date']));
		$starttimestr = date("H:i",strtotime($rrow['start_date']));
		if (isset($rrow['end_date'])) {
			if (!is_null($rrow['end_date'])) {
			$enddatestr = date("Y-m-d",strtotime($rrow['end_date']));
			$endtimestr = date("H:i",strtotime($rrow['end_date']));
			}
		}
		$recur_float = $rrow['recur_float'];
		$recur_units = $rrow['recur_units'];
		$recur_scale = $rrow['recur_scale'];
		$grace_units = $rrow['grace_units'];
		$grace_scale = $rrow['grace_scale'];
		$passive_units = $rrow['passive_units'];
		$passive_scale = $rrow['passive_scale'];
		$alarm_interval_units = $rrow['alarm_interval_units'];
		$alarm_interval_scale = $rrow['alarm_interval_scale'];
		$snooze_units = $rrow['snooze_units'];
		$snooze_scale = $rrow['snooze_scale'];
		$days_of_week= "_" . $rrow['days_of_week'];
		if(is_null($rrow['days_of_week'])) $days_of_week="_MTWtFSs";
		if (!is_null($rrow['season_start'])) {
			$blackout_days=true;
			$season_start= (int) $rrow['season_start'] + 1;
		} else {
			$season_start= "1";
		}
		if (!is_null($rrow['season_end'])) {
			$blackout_days=true;
			$season_end= (int) $rrow['season_end'] +1;
		} else {
			$season_end= "365";
		}
		if (!is_null($rrow['day_start'])) {
			$blackout_hours=true;
			$temp = (string) $rrow['day_start'];
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
		if (!is_null($rrow['day_end'])) {
			$blackout_hours=true;
			$temp = (string) $rrow['day_end'];
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
	}
?>
	
	<form class="w3-container" action="index.php?p=reminder&id=<?php echo($reminderid); ?>" method="post">
		<input name="ID" type="hidden" value="<?php echo($rrow['uid']); ?>">
		<input name="DIRTY" type="hidden" value="Y">
		<input name="action" type="hidden" value="update">
		<input name="type" type="hidden" value="recurrence">

			<!-- The UI max length should be several chars smaller than the database
			to allow for HTML encoding e.g. "'" to "&rsquo;" -->
		 
		<div class="w3-container w3-cell-row" style="display: block">

			<div id="StepOne"  class="w3-container w3-mobile">
				<div id="StartDateAndTime" class="w3-container w3-card-4 w3-amber">
					<p>
						<label>Start</label>
						<input name="StartDate" class="w3-input w3-border" type="date" value="<?php echo $startdatestr; ?>" required>
						<input name="StartTime" class="w3-input w3-border" type="time" value="<?php echo $starttimestr; ?>" required>
					</p>
				</div>
			</div>
		    <p></p>
			<div id="StepTwo"  class="w3-container w3-mobile w3-cell ">
				<div id="Recurrence" class="w3-container w3-card-4 w3-pale-green">
					<h4>Recurrence</h4>
					<p> 
						<input name="Recurrence" value="RecurYN" type="checkbox" <?php if(!$new AND $recur_units!="") echo ' checked';  ?> > This item will recur every<br>
						<input name="recur_units" class=" w3-border" type="number"  <?php if($recur_units!="") echo ('value="'. $recur_units . '"'); else echo('value="1"');  ?> required>
						<select name="recur_scale" class=" w3-border" value="<?php echo $recur_scale; ?>" required>
							<option value="0" <?php if ($recur_scale==0)echo ' selected'; ?> >hours</option>
							<option value="1" <?php if ($recur_scale==1 OR $recur_scale=="")echo ' selected'; ?> >days</option>
							<option value="2" <?php if ($recur_scale==2)echo ' selected'; ?> >weeks</option>
							<option value="3" <?php if ($recur_scale==3)echo ' selected'; ?> >months</option>
							<option value="4" <?php if ($recur_scale==4)echo ' selected'; ?> >years</option>
						</select> <br>after: <br>
						<input type="radio" name="recur_float" value="0" <?php if ($recur_float==0) echo ' checked'; ?> > its previous start time<br>
						<input type="radio" name="recur_float" value="1" <?php if ($recur_float==1) echo ' checked'; ?> > its previous completion
					</p>
					<div id="EndDateAndTime" >
						<p>
							<label>Until: </label>
							<input name="EndDate" class="w3-input w3-border" type="date"  <?php if(!$new and isset($enddatestr) ) echo ' value="' . $enddatestr . '"'; ?>>
							<input name="EndTime" class="w3-input w3-border" type="time"  <?php if(!$new and isset($endtimestr)) echo ' value="' . $endtimestr . '"'; ?>>
						</p>
						<br>
					</div>
				</div>
				<br>
				<div id="GraceTime" class="w3-container w3-card-4 w3-pale-yellow">
					<h4>Grace time</h4>
					<p>
						<input name="GraceTime" value="DueYN" type="checkbox" <?php if(!$new AND $grace_units!="") echo ' checked';  ?>> This item will be due<br>
						<input name="grace_units" class="w3-border" type="number"  <?php if($grace_units!="") echo ('value="'. $grace_units . '"'); else echo('value="1"');  ?> required>
						<select name="grace_scale" class=" w3-border" value="<?php echo $grace_scale; ?>" required>
							<option value="0" <?php if ($grace_scale==0)echo ' selected'; ?> >hours</option>
							<option value="1" <?php if ($grace_scale==1 OR $grace_scale=="")echo ' selected'; ?> >days</option>
							<option value="2" <?php if ($grace_scale==2)echo ' selected'; ?> >weeks</option>
							<option value="3" <?php if ($grace_scale==3)echo ' selected'; ?> >months</option>
							<option value="4" <?php if ($grace_scale==4)echo ' selected'; ?> >years</option>
						</select> <br> after it starts
					</p>
				</div>
				<br>
				<div id="Alarms" class="w3-container w3-card-4 w3-pale-red">
					<h4>Alarms</h4>
					<p>
						<input name="Alarms" value="AlarmYN" type="checkbox" <?php if(!$new AND $passive_units!="") echo ' checked';  ?> > Raise an alarm every<br>
						<input name="alarm_interval_units" class=" w3-border" type="number" <?php if($alarm_interval_units!="") echo ('value="'. $alarm_interval_units . '"'); else echo('value="1"');  ?>  required>
						<select name="alarm_interval_scale" class=" w3-border" value="<?php echo $alarm_interval_scale; ?>" required>
							<option value="0" <?php if ($alarm_interval_scale==0)echo ' selected'; ?> >hours</option>
							<option value="1" <?php if ($alarm_interval_scale==1 OR $alarm_interval_scale=="")echo ' selected'; ?> >days</option>
							<option value="2" <?php if ($alarm_interval_scale==2)echo ' selected'; ?> >weeks</option>
							<option value="3" <?php if ($alarm_interval_scale==3)echo ' selected'; ?> >months</option>
							<option value="4" <?php if ($alarm_interval_scale==4)echo ' selected'; ?> >years</option>
						</select>
						<br>if this item is not completed <br>
						<input name="passive_units" class=" w3-border" type="number" <?php if($passive_units!="") echo ('value="'. $passive_units . '"'); else echo('value="1"');  ?>  required>
						<select name="passive_scale" class=" w3-border" value="<?php echo $passive_scale; ?>" required>
							<option value="0" <?php if ($passive_scale==0)echo ' selected'; ?> >hours</option>
							<option value="1" <?php if ($passive_scale==1 OR $passive_scale=="")echo ' selected'; ?> >days</option>
							<option value="2" <?php if ($passive_scale==2)echo ' selected'; ?> >weeks</option>
							<option value="3" <?php if ($passive_scale==3)echo ' selected'; ?> >months</option>
							<option value="4" <?php if ($passive_scale==4)echo ' selected'; ?> >years</option>
						</select>  <br>after it starts
					</p>
				</div>
		</div>
		  <br>
			<div id="StepThree" class="w3-container w3-mobile w3-cell w3-hide-large w3-hide-small w3-hide-medium">
				<div id="DaysOfWeek" class="w3-container w3-card-4 w3-light-blue">
					<h4>Days of week</h4>
					<p>
						Show me this item on these days of the week:<br>
						<input name="MondayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"M")) echo ' checked'; ?> >Mon 
						<input name="TuesdayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"T")) echo ' checked'; ?> >Tue
						<input name="WednesdayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"W")) echo ' checked'; ?> >Wed 
						<input name="ThursdayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"t")) echo ' checked'; ?> >Thu 
						<input name="FridayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"F")) echo ' checked'; ?> >Fri 
						<input name="SaturdayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"S")) echo ' checked'; ?> >Sat 
						<input name="SundayYN" value="1" type="checkbox" <?php if (strpos($days_of_week,"s")) echo ' checked'; ?> >Sun 
					</p>
				</div>
				<br>
				<div id="TimeOfDay" class="w3-container w3-card-4 w3-blue-gray">
					<h4>Time of Day</h4>
					<p>
						<input name="SilentHoursYN" value="Y" type="checkbox" <?php if(!$new AND (isset($blackout_hours) )) echo ' checked';  ?>> This item will be visible only from <br>
						<input name="TimeOfDayStart" class=" w3-border" type="time" value="<?php echo $tod_start; ?>" required>
						to 
						<input name="TimeOfDayEnd" class=" w3-border" type="time" value="<?php echo $tod_end; ?>" required>
					</p>
				</div>
				<br>
				<div id="TimeofYear" class="w3-container w3-card-4 w3-dark-gray">
					<h4>Time of Year</h4>
					<p>
						<input name="SilentDaysYN" value="Y" type="checkbox" <?php if(!$new AND (isset($blackout_days))) echo ' checked';  ?>> This item will be visible only from days <br>
						<input name="season_start" class=" w3-border" type="number" value="<?php echo $season_start; ?>" required>
						to 
						<input name="season_end" class=" w3-border" type="number" value="<?php echo $season_end; ?>" required> 
					</p> 
				</div>
				<br>

			</div>
		</div>
		<input name="btnSubmit" type="Submit" value="Done">

	</form>
