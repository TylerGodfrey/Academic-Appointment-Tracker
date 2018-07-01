<?php
session_start();
$username = $_SESSION['username'];
?>
<html>
<head>
	<title>Proctor Availabilities</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
</head>
<body>
<div>
<?php

include ('links.php');
include ('calendar_class.php');

class AvCalendar extends Calendar {
	function createBottomOfCalendar ($studyID, $submitType) {
		$studyID = $studyID;
		$timestamp = mktime(0,0,0,$this->cMonth,1,$this->cYear);
		$maxday = date("t",$timestamp);
		$thismonth = getdate ($timestamp);
		$startday  = $thismonth['wday'];
		$internalTime = "H:i";
		$printTime = "h:i A";
		$submitType = $submitType;
		$username = $_SESSION['username']; // need to declare it again since it will not get the username from outside of the function without having it put in as a parameter
		$dateToday = getdate();
		$dateToday = $dateToday['year'] . '-' . $dateToday['mon'] . '-' . $dateToday['mday'];

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

		$utcTimes = Array("",
	    	'7:00',		'7:15',		'7:30',		'7:45',
			'8:00',		'8:15',		'8:30',		'8:45',
			'9:00',		'9:15',		'9:30',		'9:45',
			'10:00',	'10:15',	'10:30',	'10:45',
			'11:00',	'11:15',	'11:30',	'11:45',
			'12:00',	'12:15',	'12:30',	'12:45',
			'13:00',	'13:15',	'13:30',	'13:45',
			'14:00',	'14:15',	'14:30',	'14:45',
			'15:00',	'15:15',	'15:30',	'15:45',
			'16:00',	'16:15',	'16:30',	'16:45',
			'17:00',	'17:15',	'17:30',	'17:45',
			'18:00',	'18:15',	'18:30',	'18:45',
			'19:00',	'19:15',	'19:30',	'19:45',
			'20:00',	'20:15',	'20:30',	'20:45',
			'21:00',	'21:15',	'21:30',	'21:45',
		    '22:00'
		    );

	    $periodTimes = Array("");

	    foreach($utcTimes as $time) {
	        if ($time != "") {
	            array_push($periodTimes, date_format(date_create($time), 'g:i A'));
	        }
	    }

		echo "<form action='availability_submission.php' method='post'>
			<input type='hidden' name='month' value='" . $this->cMonth . "'>
			<input type='hidden' name='year' value='" . $this->cYear . "'>
			<input type='hidden' name='proctorName' value='" . $username . "'>
			<input type='hidden' name='studyID' value='" . $studyID . "'>
			<input type='hidden' name='submitType' value='" . $submitType . "'>";

		for ($i=0; $i<(ceil(($maxday+$startday)/7)*7); $i++) {
	    	
	    	$day = $i - $startday + 1;
	    	
	    	$dateObject = date_create(strval($this->cYear) . '-' . strval($this->cMonth) . '-' . strval($day));
			
			

	    	if(($i % 7) == 0 ) echo "<tr>";
	    	if($i < $startday || ($day) > cal_days_in_month(CAL_GREGORIAN, $this->cMonth, $this->cYear)) echo "<td></td>";
	    	elseif (date_diff($startDate, $dateObject)->format('%R days') == '- days' || date_diff($dateObject, $endDate)->format('%R days') == '- days') echo "<td align='left' valign='top' width='100px' height='100px'>$day</td>"; // done separately because we need to avoid $i being less than $startday
			else {

				$date = date_format($dateObject, 'Y-m-d');

				$startTime = "";
				$endTime = "";

				$availabilitiesConnection = new Connection(); // creates Connection object for getting the user's existing availability times
				$availabilitiesConnection->createConnection();
				$availabilitiesConnection->sql = "SELECT * FROM `experimenter availability` AS EA INNER JOIN `person info`AS PI ON PI.PersonID = EA.ExperimenterID WHERE PI.PersonUsername = '$username' AND EA.StudyID = $studyID AND Date = '$date'"; // gets user's availabilities for that study and date
				$availabilitiesResult = mysqli_query($availabilitiesConnection->conn, $availabilitiesConnection->sql);
				if ($availabilitiesResult != false) {
					if (mysqli_num_rows($availabilitiesResult) > 0) {
						while ($row = mysqli_fetch_assoc($availabilitiesResult)) {
							$startTime = date_format(date_create($row['StartTime']), 'h:i A');
							$endTime = date_format(date_create($row['EndTime']), 'h:i A');
						}
					}
				}
				$availabilitiesConnection->closeConnection();

				$dateInsert = "<td align='left' valign='top' width='100px' height='100px'>" . ($day) . "<br>";
				if ($startTime <> "" && $endTime <> "") {
					$dateInsert .= "Existing Availability: <br>
								$startTime - $endTime <br>
								<input type='checkbox' name='delete" . $day . "'>Delete Availability <br>";
				}
				else {
					$dateInsert .= "<br><br>";
				}
				if (date_create($date) >= date_create($dateToday)) {
					$dateInsert .= "Start:<select name='startTime" . $day . "'><option></option>";
					for ($f = 0; $f < count($utcTimes); $f++) {
						$dateInsert .= "<option value='" . $utcTimes[$f] . "'";
						if ($utcTimes[$f] == '') {
							$dateInsert .= " style='display:none'";
						}
						$dateInsert .= "><time>" . $periodTimes[$f] . "</time></option>";
					}
					$dateInsert .= "</select>";

					$dateInsert .= "<br>End: <select name='endTime" . $day . "'> <option></option>";

					for ($f = 0; $f < count($utcTimes); $f++) {
						if(empty($utcTimes[$f])) {
							$dateInsert .= "<option value='default' style='display:none'></option>";
						}
						else {
							$dateInsert .= "<option value='" . $utcTimes[$f] . "'><time>" . $periodTimes[$f] . "</time></option>";
						}
					}

					$dateInsert .= "</select>";
				}

				echo $dateInsert;

				echo "</td>";
			}

			if (($i % 7) == 6 ) { 
				echo "</tr>";
			}
		}
		echo "<tr><td colspan='7'><input type='submit' value='Submit'></td></tr></form>";	
	}
}

if (isset($_POST['studyID'])) {
    $studyID = $_POST['studyID'];
}

if (isset($_POST['submitType'])) {
	$submitType = $_POST['submitType'];
}

$proctorValidity = false; // by default, the user is not a valid proctor for the study

// checking if the user is a proctor for the study
$proctorVerificationConnection = new Connection();
$proctorVerificationConnection->createConnection();
$proctorVerificationConnection->sql = "SELECT * FROM `database name`.`studyexperimenterpairs` AS SEP INNER JOIN `database name`.`person info` AS PI ON SEP.ExperimenterID = PI.PersonID WHERE SEP.StudyID = $studyID AND PI.PersonUsername = '$username'";
$proctorVerificationResult = mysqli_query($proctorVerificationConnection->conn, $proctorVerificationConnection->sql);

if ($proctorVerificationResult != false) {
    if (mysqli_num_rows($proctorVerificationResult) == 1) {
        $proctorValidity = true; // the user is a valid proctor for the study
    }
}

$proctorVerificationConnection->closeConnection();

$professorValidity = false; // by default, the user is not assumed to have created the study

// checking if the user created the study
$professorVerificationConnection = new Connection();
$professorVerificationConnection->createConnection();
$professorVerificationConnection->sql="SELECT * FROM `database name`.`study` AS Study INNER JOIN `database name`.`person info` AS PI ON Study.CreatedBy = PI.PersonID WHERE Study.StudyID = $studyID AND PI.PersonUsername = '$username'";
$professorVerificationResult = mysqli_query($professorVerificationConnection->conn, $professorVerificationConnection->sql);
if ($professorVerificationResult != false) {
	if (mysqli_num_rows($professorVerificationResult) == 1) {
		$professorValidity = true;
	}
}


if ($proctorValidity == true || $professorValidity == true) {
	echo "<div align='center'>";
	$avCal = new AvCalendar();
	$avCal->createTopOfCalendar($studyID, $submitType);
	$avCal->createBottomOfCalendar($studyID, $submitType);
	echo "</div>";
	
}







?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<!-- <script type="text/javascript" src="js/availability.php"></script> -->
</body>
</html>