<html>
<head>
<title>Appointment Creation</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
</head>
<body>

<?php

include ('links.php');
include ('calendar_class.php');

if (isset($_POST['submitType'])) {
	$submitType = $_POST['submitType'];	
}


class AppCalendar extends Calendar {

	function checkIfConflicting($newAppointmentTime, $existingAppointmentTime, $allowConcurrence) {
		if ($allowConcurrence == 0) {
			if ( $newAppointmentTime[1] <= $existingAppointmentTime[0] || $newAppointmentTime[0] >= $existingAppointmentTime[1] ) { // checks to see if the new appointment is either completely before or completely after the existing appointment - if the new appointment ends before the existing appointment or starts after the existing appointment, a false value is returned indicating that it does not conflict with the existing appointment. This will be run for each existing appointment on that day
				return false;
			}
			else {
				return true;
			}
		}
		elseif ($allowConcurrence == 1) {
			if ($newAppointmentTime[0] == $existingAppointmentTime[0]) {
				return true;
			}
			else {
				return false;
			}
		}
	}

	function createBottomOfCalendar ($studyID, $submitType) {

		date_default_timezone_set('America/Chicago');
		$username = $_SESSION['username'];
		$studyID = $studyID;
		$timestamp = mktime(0,0,0,$this->cMonth,1,$this->cYear);
		$maxday = date("t",$timestamp);
		$thismonth = getdate ($timestamp);
		$startday = $thismonth['wday'];
		$internalTime = "H:i";
		$printTime = "g:i A";
		$currentDate = date_format(date_create(getdate()['year'] . "-" . getdate()['mon'] . "-" . getdate()['mday']), 'Y-m-d');
		$currentDay = getdate()['mday'];
		$currentTime = date_create(date('H:i:s'));

		$studyConnection = new Connection();
		$studyConnection->createConnection();
		$studyError = false;
		$studyConnection->sql="SELECT StartDate, EndDate FROM `database name`.`study` WHERE StudyID = $studyID";
		$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
		if ($studyResult != false) {
			if (mysqli_num_rows($studyResult) == 1) {
				$row = mysqli_fetch_assoc($studyResult);
				$startDate = date_create($row['StartDate']);
				$endDate = date_create($row['EndDate']);
			}
			else {
				$studyError = true;
			}
		}
		else {
			$studyError = true;
		}
		$studyConnection->closeConnection();

		if ($studyError == true) {
			echo "<script> alert('An unexpected error has occurred. Please try again.'); window.setTimeout(function() { window.location='study_selection.php' }, 500); </script>";
		}

		$roomNumbers = array();
		$roomNumberFetcher = new Connection();
		$roomNumberFetcher->sql = "SELECT RoomID FROM `database name`.`study lab pairings` WHERE StudyID = $studyID";
		$roomNumberFetcher->createConnection();
		$roomNumbersResult = mysqli_query($roomNumberFetcher->conn, $roomNumberFetcher->sql);
		if (mysqli_num_rows($roomNumbersResult) > 0) {
			while ($row = mysqli_fetch_assoc($roomNumbersResult)) {
				array_push($roomNumbers, $row['RoomID']);
			}
		}
		$roomNumberFetcher->closeConnection();

		$experimentDuration = "";
		$getExperimentDuration = new Connection();
    	$getExperimentDuration->sql = "SELECT ExpectedTimeInMinutes, ConcurrentTesting FROM `study` WHERE StudyID = $studyID"; // creates query to get expected time of experiment for study in question
    	$getExperimentDuration->createConnection();
    	$getExperimentDuration = mysqli_query($getExperimentDuration->conn, $getExperimentDuration->sql); // runs the query on the database
    	if (mysqli_num_rows($getExperimentDuration) == 1) {
    		$row = mysqli_fetch_assoc($getExperimentDuration); // since the query should only return one row, this will only run once
    		$experimentDuration = $row['ExpectedTimeInMinutes']; // sets variable to the time the experiment is expected to take
    		$concurrentTesting = $row['ConcurrentTesting'];
    	}

		for ($i=0; $i<(ceil(($maxday+$startday)/7)*7); $i++) { // repeats until end of line, instead of until last day of month, for preferable formatting


	    	$day = $i - $startday + 1;
	    	
	    	$dateObject = date_create(strval($this->cYear) . '-' . strval($this->cMonth) . '-' . strval($day));
			

	    	if(($i % 7) == 0 ) echo "<tr>";
	    	if($i < $startday || ($i - $startday + 1) > cal_days_in_month(CAL_GREGORIAN, $this->cMonth, $this->cYear)) echo "<td></td>";
	    	elseif (date_diff($startDate, $dateObject)->format('%R days') == '- days' || date_diff($dateObject, $endDate)->format('%R days') == '- days') {
    			$dateInsert = "$day";
				$date = date_format($dateObject, 'Y-m-d');
    			$originalTimeConnection = new Connection();
	    		$originalTimeConnection->createConnection();
	    		$originalTimeConnection->sql = "SELECT StartTime, EndTime FROM `database name`.`appointments` AS A
	    											INNER JOIN `database name`.`person info` AS PI
	    												ON PI.PersonID = A.SubjectID WHERE StudyID = $studyID AND PersonUsername = '" . $_SESSION['username'] . "' AND Date = '$date'";
	    		$originalTimeResult = mysqli_query($originalTimeConnection->conn, $originalTimeConnection->sql);
	    		if ($originalTimeResult != false) {
		    		if (mysqli_num_rows($originalTimeResult) == 1) {
		    			$row = mysqli_fetch_assoc($originalTimeResult);
		    			$originalStartTime = date_format(date_create($row['StartTime']), $printTime);
		    			$originalEndTime = date_format(date_create($row['EndTime']), $printTime);
		    			$dateInsert .= "<br>Existing Time: <br> $originalStartTime - $originalEndTime";
		    		}
	    		}
	    		$dateInsert .= "<br>No times available.";
	    		$originalTimeConnection->closeConnection();
	    	 	echo "<td align='left' valign='top' width='100px' height='100px'>$dateInsert</td>"; // done separately because we need to avoid $i being less than $startday
	    	}
		    else {
		    	
		    	$date = date_format($dateObject, 'Y-m-d');

		    	$availabilityArray = array(); // create array object for start times and end times of different availabilities to be put into
		    	if ($date >= $currentDate) {
			    	$availabilitiesConnection = new Connection();  // creates Connection object for getting the available times
			    	$availabilitiesConnection->sql = "SELECT StartTime, EndTime, ExperimenterID FROM `database name`.`experimenter availability` WHERE StudyID = $studyID AND Date = '$date' AND ExperimenterID <> (SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username')"; // gets experimenter availabilities for that study and date
			    	$availabilitiesConnection->createConnection();
			    	$availabilities = mysqli_query($availabilitiesConnection->conn, $availabilitiesConnection->sql); // runs query
			    	if ($availabilities != false) {
				    	if (mysqli_num_rows($availabilities) > 0) {
				    		while ($row = mysqli_fetch_assoc($availabilities)) {
				    			$startTime = date_create($row['StartTime']);
				    			$endTime = date_create($row['EndTime']);
				    			$individualAvailability = array($startTime, $endTime, $row['ExperimenterID']);
				    			array_push($availabilityArray, $individualAvailability);
				    		}
				    	}
			    	}
			    	$availabilitiesConnection->closeConnection();	
		    	}
		    	
		    	$experimenterTimeSlots = array(); // array of time slots that work for the experimenter
		    	$roomTimeSlots = array(); // array of time slots after room availability is checked
		    	$finalTimeSlots = array(); // used for a simpler removal of duplicates
		    	$getExistingAppointments = new Connection();
		    	$getExistingAppointments->createConnection();
		    	foreach($availabilityArray as $individualAvailability) {
		    		$startOfTimeSlot = $individualAvailability[0];
		    		$endOfTimeSlot = date_create(date_format($startOfTimeSlot, $internalTime)); // set this way so that we can change the end time without changing the start time
		    		$endOfTimeSlot->modify('+' . $experimentDuration . ' minutes');  // end of time slot is 30 minutes after start of time slot
		    		$endOfAvailability = $individualAvailability[1];
		    		$proctorID = $individualAvailability[2];
		    		$existingAppointments = array();
		    		$getExistingAppointments->sql = "SELECT App.StartTime, App.EndTime, Study.ConcurrentTesting FROM `appointments` AS App INNER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID WHERE App.ExperimenterID = $proctorID AND App.Date = '$date' AND (App.StudyID <> $studyID OR App.SubjectID <> (SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username'))";
		    		$existingAppointmentsResult = mysqli_query($getExistingAppointments->conn, $getExistingAppointments->sql);
		    		if (mysqli_num_rows($existingAppointmentsResult) > 0) {
		    			while ($row = mysqli_fetch_assoc($existingAppointmentsResult)) {
		    				if ($concurrentTesting == 1) {
		    					$appointment = array(date_create($row['StartTime']), date_create($row['EndTime']), $row['ConcurrentTesting']);
		    				}
		    				else {
		    					$appointment = array(date_create($row['StartTime']), date_create($row['EndTime']), 0);
		    				}
		    				array_push($existingAppointments, $appointment);
		    			}
		    		}
		    		
		    		while ($endOfTimeSlot <= $endOfAvailability) {
		    			$individualSlot = array($startOfTimeSlot, $endOfTimeSlot);
    					$startTime = date_create(date_format($individualSlot[0], $internalTime));
    					$endTime = date_create(date_format($individualSlot[1], $internalTime));
		    			if (count($existingAppointments) > 0) {
			    			foreach ($existingAppointments as $appointment) {
			    				if (!$this->checkIfConflicting($individualSlot, array($appointment[0], $appointment[1]), $appointment[2])) {
			    					if ($currentDay == $day) {
			    						if (date_diff($currentTime, $startTime)->format('%R%h') >= '+2') {
			    							array_push($experimenterTimeSlots, array($startTime, $endTime, $proctorID)); // add if it's two hours or more after the current time today
			    						}
			    					}
			    					else {
			    						array_push($experimenterTimeSlots, array($startTime, $endTime, $proctorID)); // add if it's not today (previous dates removed earlier on in the process)
			    					}
			    				}	
			    			}
		    			}
		    			else {
	    					if ($currentDay == $day) {
	    						if (date_diff($currentTime, $startTime)->format('%R%h') >= '+2') {
	    							array_push($experimenterTimeSlots, array($startTime, $endTime, $proctorID)); // add if it's two hours or more after the current time today
	    						}
	    					}
	    					else {
	    						array_push($experimenterTimeSlots, array($startTime, $endTime, $proctorID)); // add if it's not today (previous dates removed earlier on in the process)
	    					}
		    			}
		    			$startOfTimeSlot->modify('+15 minutes');
		    			$endOfTimeSlot->modify('+15 minutes');
		    		}	
		    	}
		    	
		    	$filteredExperimenterTimeSlots = array();
				if (count($experimenterTimeSlots) > 0) {
					array_push($filteredExperimenterTimeSlots, $experimenterTimeSlots[0]);
					for ($indexOfLoop = 1; $indexOfLoop < count($experimenterTimeSlots); $indexOfLoop++) {
	    				if ($experimenterTimeSlots[$indexOfLoop][0] != $experimenterTimeSlots[$indexOfLoop - 1][0] || $experimenterTimeSlots[$indexOfLoop][2] != $experimenterTimeSlots[$indexOfLoop - 1][2]) {
	    					array_push($filteredExperimenterTimeSlots, $experimenterTimeSlots[$indexOfLoop]);
	    				}
	    			}
	    		}

	    		foreach($roomNumbers as $room) {
	    			$roomTimes=array();
			    	$roomConnection = new Connection();
			    	$roomConnection->sql = "SELECT App.RoomID AS 'RoomID', App.StartTime AS 'StartTime', App.EndTime AS 'EndTime'
			    					FROM `database name`.`appointments` AS App 
			    					WHERE App.Date = '$date' AND App.RoomID = $room AND 
			    					(App.StudyID <> $studyID OR App.SubjectID <> 
			    					(SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username'))
			    					UNION 
			    					SELECT Res.LabID AS 'RoomID', Res.StartTime AS 'StartTime', Res.EndTime AS 'EndTime'
			    					FROM `database name`.`reservation` AS Res
			    					WHERE Res.Date = '$date' AND Res.LabID = $room
			    					";
					$roomConnection->createConnection();
					$roomResult = mysqli_query($roomConnection->conn, $roomConnection->sql);
					if ($roomResult != false) {
						if (mysqli_num_rows($roomResult) > 0) {
							while ($row = mysqli_fetch_assoc($roomResult)) {
								$startTime = date_create($row['StartTime']);
								$endTime = date_create($row['EndTime']);
								array_push($roomTimes, array($startTime, $endTime, $row['RoomID']));
							}
						}
					}
					$roomConnection->closeConnection();

	    			foreach($filteredExperimenterTimeSlots as $slot) {
		    			$startTime = date_create(date_format($slot[0], $internalTime));
			    		$endTime = date_create(date_format($slot[1], $internalTime));
		    			if (count($roomTimes) > 0) {
		    				$roomCheck = false;
			    			foreach($roomTimes as $roomTime) {
				    			if ($roomTime[2] == $room) {
				    				if ($this->checkIfConflicting($slot, array($roomTime[0], $roomTime[1]), 0)) {
				    					$roomCheck = true;
				    				}
				    			}
			    			}
		    				if ($roomCheck == false) {
				    			array_push($roomTimeSlots, array($startTime, $endTime, $slot[2], $room));
		    				}
			    		}
			    		else {
			    			array_push($roomTimeSlots, array($startTime, $endTime, $slot[2], $room));
			    		}
		    		}
	    		}

		    	foreach($roomTimeSlots as $slot) {
		    		$equal = false;
		    		if (count($finalTimeSlots) > 0) {
		    			foreach($finalTimeSlots as $final) {
			    			if ($slot[0] == $final[0]) {
			    				$equal = true;
			    				break;
			    			}
			    			else {
			    				$equal = false;
			    			}
			    		}
			    	}
		    		if ($equal == false) {
		    			array_push($finalTimeSlots, array($slot[0], $slot[1]));
		    		}
		    	}

		    	$timeSlotsWithPairings = array(); // time slots paired with the experimenters and rooms available at the time

		    	foreach($finalTimeSlots as $final) {
		    		$proctorArray = array();
		    		$roomArray = array();
		    		foreach($roomTimeSlots as $room) {
		    			if ($final[0] == $room[0]) {
		    				array_push($proctorArray, $room[2]);
		    				array_push($roomArray, $room[3]);
		    			}
		    		}
		    		array_push($timeSlotsWithPairings, array($final[0], $final[1], $proctorArray, $roomArray));
		    	}
				
		    	// this section sorts the time slots properly
		    	$sortedTimeSlots = array();
		    	$tempArray = array();

		    	foreach ($timeSlotsWithPairings as $timeSlot) {
		    		array_push($tempArray, $timeSlot[0]);
		    	}

		    	sort($tempArray);

		    	foreach ($tempArray as $temp) {
		    		foreach ($timeSlotsWithPairings as $timeSlot) {
		    			if ($temp == $timeSlot[0]) {
		    				array_push($sortedTimeSlots, $timeSlot);
		    			}
		    		}
		    	}


		    	$dateInsert = "<td align='left' valign='top' width='100px' height='100px'>" . ($day) . "<br>";

		    	if ($submitType == 'Edit') {
		    		$originalTimeConnection = new Connection();
		    		$originalTimeConnection->createConnection();
		    		$originalTimeConnection->sql = "SELECT StartTime, EndTime FROM `database name`.`appointments` AS A
		    											INNER JOIN `database name`.`person info` AS PI
		    												ON PI.PersonID = A.SubjectID WHERE StudyID = $studyID AND PersonUsername = '" . $_SESSION['username'] . "' AND Date = '$date'";
		    		$originalTimeResult = mysqli_query($originalTimeConnection->conn, $originalTimeConnection->sql);
		    		if ($originalTimeResult != false) {
			    		if (mysqli_num_rows($originalTimeResult) == 1) {
			    			$row = mysqli_fetch_assoc($originalTimeResult);
			    			$originalStartTime = date_format(date_create($row['StartTime']), $printTime);
			    			$originalEndTime = date_format(date_create($row['EndTime']), $printTime);
			    			$dateInsert .= "Existing Time: <br> $originalStartTime - $originalEndTime <br>";
			    		}
		    		}
		    		$originalTimeConnection->closeConnection();

			    	if (count($sortedTimeSlots) > 0) {
			    		echo "<form action='appointment_creation_final.php' method='post'>";
			    		$dateInsert = $dateInsert . "<select name='timeSlotInfo'> <option value=''></option>";
			    		foreach ($sortedTimeSlots as $slot) {
			    			$string = date_format($slot[0], $printTime) . "-" . date_format($slot[1], $printTime);
			    			$value = date_format($slot[0], $internalTime) . "-" . date_format($slot[1], $internalTime) . "|";
			    			foreach (array_unique($slot[2]) as $proctor) {
			    				$value = $value . $proctor . ",";
			    			}
			    			$value = substr($value,0,-1);

			    			$value = $value . "|";
			    			foreach (array_unique($slot[3]) as $room) {
			    				$value = $value . $room . ",";
			    			}
			    			$value = substr($value,0,-1);

			    			$dateInsert = $dateInsert . "<option value='" . $value . "'>" . $string . "</option>";
			    		}
			    		$dateInsert = $dateInsert . "</select><br>
			    		<input type='hidden' name='studyID' value='". $studyID . "'>
			    		<input type='hidden' name='date' value='" . $date . "'>
			    		<input type='hidden' name='submitType' value='" . $submitType . "'>
			    		<input type='submit'></input></form>";
			    	}
			    	else {
			    		$dateInsert = $dateInsert . "No times available.";
			    	}
			    	$dateInsert = $dateInsert . "</td>";
			    	echo $dateInsert;	
		    	}

		    	if ($submitType == 'Create') {
			    	if (count($sortedTimeSlots) > 0) {
			    		echo "<form action='appointment_creation_final.php' method='post'>";
			    		$dateInsert = $dateInsert . "<select name='timeSlotInfo'> <option value=''></option>";
			    		foreach ($sortedTimeSlots as $slot) {
			    			$string = date_format($slot[0], $printTime) . "-" . date_format($slot[1], $printTime);
			    			$value = date_format($slot[0], $internalTime) . "-" . date_format($slot[1], $internalTime) . "|";
			    			foreach (array_unique($slot[2]) as $proctor) {
			    				$value = $value . $proctor . ",";
			    			}
			    			$value = substr($value,0,-1);

			    			$value = $value . "|";
			    			foreach (array_unique($slot[3]) as $room) {
			    				$value = $value . $room . ",";
			    			}
			    			$value = substr($value,0,-1);


			    			$dateInsert = $dateInsert . "<option value='" . $value . "'>" . $string . "</option>";
			    		}
			    		$dateInsert = $dateInsert . "</select><br>
			    		<input type='hidden' name='studyID' value='". $studyID . "'>
			    		<input type='hidden' name='date' value='" . $date . "'>
			    		<input type='hidden' name='submitType' value='" . $submitType . "'>
			    		<input type='submit'></input></form>";
			    	}
			    	else {
			    		$dateInsert = $dateInsert . "No times available.";
			    	}
			    	$dateInsert = $dateInsert . "</td>";
			    	echo $dateInsert;		    		
		    	}
		    }
		    if(($i % 7) == 6 ) echo "</tr>";
		}

	echo "
	</table>
	</td>
	</tr>
	</table>
	";
	}
}

if ($submitType == 'Edit' || $submitType == 'Create') {
	$studyID = $_POST['studyID'];
	$year = $_POST['year'];
	$month = $_POST['month'];

	$studyConnection = new Connection();

	$studyConnection->sql = "SELECT * FROM `study` WHERE StudyID = $studyID;";
	$studyConnection->createConnection();

	$result = mysqli_query($studyConnection->conn, $studyConnection->sql);

	if (mysqli_num_rows($result) > 0) {
		$studyInfo = mysqli_fetch_assoc($result);

		echo "<p align='center'>
		Study Name: " . $studyInfo['StudyName'] . "<br>
		Description: " . $studyInfo['Description'] . "<br>
		Expected Points Earned: " . $studyInfo['ExpectedPointValue'] . "<br>
		Expected Duration: " . $studyInfo['ExpectedTimeInMinutes'] . " minutes <br>";
		$appCal = new AppCalendar();
		$appCal->createTopOfCalendar($studyInfo['StudyID'], $submitType);
		$appCal->createBottomOfCalendar($studyInfo['StudyID'], $submitType);
		echo "</p>";
	}

	$studyConnection->closeConnection();
}
else {
	echo "<script type='text/javascript'>alert('There seems to have been an error. Re-routing.');</script>";
	echo "<script> window.setTimeout(function() { window.location='index.php'; }, 3000); </script>";
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>