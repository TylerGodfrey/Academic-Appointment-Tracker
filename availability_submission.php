<?php
	include('general_connection.php');

	if (isset($_POST['year'])) {
		$year = $_POST['year'];
	}

	if (isset($_POST['month'])) {
		$month = $_POST['month'];
	}

	if (isset($_POST['studyID'])) {
		$studyID = $_POST['studyID'];
	}

	if (isset($_POST['proctorName'])) {
		$proctorName = $_POST['proctorName'];
	}

	if (isset($_POST['submitType'])) {
		$submitType = $_POST['submitType'];
	}

	$availabilityEntrySuccess = true;
	
	function getTimes ($month, $year) {
		$arrayTimes = array();
		for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
			$date = date_create("$year-$month-$i");
			$monthDay = date_format($date, 'M d');
			$timesCheck = 0;
			if (isset($_POST['startTime' . $i]) && $_POST['startTime' . $i] <> '') {
				$timesCheck++;
			}
			if (isset($_POST['endTime' . $i]) && $_POST['endTime' . $i] <> '') {
				$timesCheck++;
			}
			if ($timesCheck == 2) {
				array_push($arrayTimes, array($i, $_POST['startTime' . $i], $_POST['endTime' . $i]));
			}
			elseif ($timesCheck == 1) {
				echo "<script> alert('You must include both times in your selection.  We only got one time for $monthDay.'); </script>";
				return false;
			}
		}
		return $arrayTimes;
	}

	function getDeletes ($month, $year) {
		$arrayDeletes = array();
		for ($j = 1; $j <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $j++) {
			if (isset($_POST['delete' . $j])) {
				array_push($arrayDeletes, $j);
			}
		}
		return $arrayDeletes;
	}


	$timesArray = getTimes($month, $year);
	$deletesArray = getDeletes($month, $year);

	$idConnection = new Connection();
	$idConnection->createConnection();
	$idConnection->sql="SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$proctorName'";
	$idResult = mysqli_query($idConnection->conn, $idConnection->sql);
	if ($idResult != false) {
		if (mysqli_num_rows($idResult) == 1) {
			$row = mysqli_fetch_assoc($idResult);
			$proctorID = $row['PersonID'];
		}
	}

	$availabilityConnection = new Connection();
	$availabilityConnection->createConnection();
	$appointmentConnection = new Connection();
	$appointmentConnection->createConnection();

	foreach ($deletesArray as $deletes) {
		$date = "$year-$month-" . $deletes;

		$availabilityConnection->sql="SELECT StartTime, EndTime FROM `database name`.`experimenter availability` WHERE StudyID = $studyID AND ExperimenterID = $proctorID AND Date = '$date'";
		$availabilityResult = mysqli_query($availabilityConnection->conn, $availabilityConnection->sql);
		if ($availabilityResult != false) {
			if (mysqli_num_rows($availabilityResult) == 1) {
				$row = mysqli_fetch_assoc($availabilityResult);
				$deletedStartTime = $row['StartTime'];
				$deletedEndTime = $row['EndTime'];

				$appointmentConnection->sql="SELECT StartTime, EndTime FROM `database name`.`appointments` WHERE StudyID = $studyID AND ExperimenterID = $proctorID AND Date = '$date' AND (StartTime >= '$deletedStartTime' AND EndTime <= '$deletedEndTime')";
				$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);
				if ($appointmentResult != false) {
					if (mysqli_num_rows($appointmentResult) > 0) {
						echo "<script> alert('You already have an appointment within that time period. Please contact the subject and delete the appointment before attempting to delete this availability again.'); window.setTimeout( function() { window.location='study_selection.php'; }, 500); </script>";
						die();												
					}
				}
			}
		}

		$availabilityConnection->sql = "DELETE FROM `experimenter availability` WHERE ExperimenterID = $proctorID AND Date = '$date'";
		$availabilityConnection->submit();
	}

	if ($timesArray == false) {
		$availabilityEntrySuccess = false;
	}
	else {
		foreach ($timesArray as $times) {
			$date = "$year-$month-" . $times[0];
			if ($submitType == 'Create') {
				$availabilityConnection->sql = "INSERT INTO `experimenter availability` (StudyID, ExperimenterID, Date, StartTime, EndTime) VALUES ($studyID, $proctorID, '$date', '" . $times[1] . "', '" . $times[2] . "')";
			}
			if ($submitType == 'Edit') {

				$appointmentConnection->sql="SELECT StartTime, EndTime FROM `database name`.`appointments` WHERE StudyID = $studyID AND ExperimenterID = $proctorID AND Date = '$date' AND (StartTime < '" . $times[1] . "' OR EndTime > '" . $times[2] . "')";
				$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);
				if ($appointmentResult != false) {
					if (mysqli_num_rows($appointmentResult) > 0) {
						echo "<script> alert('This change in availability will conflict with an existing appointment for this study. Please contact the subject and delete the appointment before attempting to delete this availability again.'); window.setTimeout( function() { window.location='study_selection.php'; }, 500); </script>";
						die();
					}
				}

				$availabilityConnection->sql = "UPDATE `experimenter availability` SET StartTime = '" . $times[1] . "' , EndTime = '" . $times[2] . "' WHERE Date = '$date' AND StudyID = $studyID AND ExperimenterID = $proctorID";
			}
			if ($availabilityConnection->submit() === false) {
				$availabilityEntrySuccess = false;
			}
		}
	}
	$availabilityConnection->closeConnection();
	$appointmentConnection->closeConnection();
	if ($availabilityEntrySuccess == false) {
		echo "<script> alert('There seems to have been an error with your entry. Please try again.');
				window.setTimeout( function () { window.location='study_selection.php' }, 500); </script>";
	}
	else {
		echo "<script> alert('You have successfully entered your availability.');
			window.setTimeout( function () { window.location='study_selection.php' }, 500); </script>";
	}
?>