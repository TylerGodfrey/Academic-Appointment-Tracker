<?php
	session_start();
?>
<html>
<head>
<title>Appointment Creation Submission</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
</head>
<body>

<?php

include ('links.php');
include ('mail_functions.php');

function checkIfConflicting ($studyID, $subjectID, $proctorID, $roomID, $date, $startTime, $endTime) {

	$date = date_format(date_create($date), 'Y-m-d');
	$studyConnection = new Connection();
	$studyConnection->createConnection();
	$studyConnection->sql="SELECT ConcurrentTesting FROM `database name`.`study` WHERE StudyID = $studyID";
	$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
	if ($studyResult != false) {
		if (mysqli_num_rows($studyResult) == 1) {
			$result = mysqli_fetch_assoc($studyResult);
			if ($result['ConcurrentTesting'] == 1) {
				$concurrentTesting = true;
			}
			elseif ($result['ConcurrentTesting'] == 0) {
				$concurrentTesting = false;
			}
		}
		else {
			$studyConnection->closeConnection();
			return "Error";
		}
	}
	else {
		$studyConnection->closeConnection();
		return "Error";
	}
	$studyConnection->closeConnection();

	$conflictsConnection = new Connection();
	$conflictsConnection->createConnection();
	$conflictsConnection->sql="SELECT * FROM `database name`.`experimenter availability` WHERE StudyID = $studyID AND ExperimenterID = $proctorID AND Date = '$date' AND ('$startTime' BETWEEN StartTime AND EndTime) AND ('$endTime' BETWEEN StartTime AND EndTime)";
	$conflictsResult = mysqli_query($conflictsConnection->conn, $conflictsConnection->sql);
	if ($conflictsResult != false) {
		if (mysqli_num_rows($conflictsResult) == 0) {
			$conflictsConnection->closeConnection();
			return "Error";
		}
	}
	else {
		$conflictsConnection->closeConnection();	
		return "Error";
	}

	if ($concurrentTesting == 0) {
		$conflictsConnection->sql="SELECT * FROM `database name`.`appointments` WHERE ExperimenterID = $proctorID AND (((StartTime >= '$startTime' AND StartTime < '$endTime') OR (EndTime > '$startTime' AND EndTime <= '$endTime')) OR (('$startTime' >= StartTime AND '$startTime' < EndTime) OR ('$endTime' > StartTime AND '$endTime' <= EndTime))) AND ($subjectID <> SubjectID OR StudyID <> $studyID)";
	}
	elseif ($concurrentTesting == 1) {
		$conflictsConnection->sql="SELECT * FROM `database name`.`appointments` WHERE ExperimenterID = $proctorID AND (StartTime = '$startTime') AND ($subjectID <> SubjectID OR StudyID <> $studyID)";
	}

	$conflictsResult = mysqli_query($conflictsConnection->conn, $conflictsConnection->sql);
	if ($conflictsResult != false) {
		if (mysqli_num_rows($conflictsResult) > 0) {
			$conflictsConnection->closeConnection();
			return "Conflict";
		}
	}	
	else {
		$conflictsConnection->closeConnection();
		return "Error";
	}

	$conflictsConnection->sql="SELECT * FROM `database name`.`appointments` WHERE RoomID = $roomID AND (((StartTime >= '$startTime' AND StartTime < '$endTime') OR (EndTime > '$startTime' AND EndTime <= '$endTime')) OR (('$startTime' >= StartTime AND '$startTime' < EndTime) OR ('$endTime' > StartTime AND '$endTime' <= EndTime))) AND Date = '$date' AND ($subjectID <> SubjectID OR StudyID <> $studyID)";
	
	$conflictsResult = mysqli_query($conflictsConnection->conn, $conflictsConnection->sql);
	if ($conflictsResult != false) {
		if (mysqli_num_rows($conflictsResult) > 0) {
			$conflictsConnection->closeConnection();
			return "Conflict";
		}
	}	
	else {
		$conflictsConnection->closeConnection();
		return "Error";
	}

	$conflictsConnection->sql="SELECT * FROM `database name`.`reservation` WHERE LabID = $roomID AND ((StartTime >= '$startTime' AND StartTime < '$endTime') OR (EndTime > '$startTime' AND EndTime <= '$endTime')) OR (('$startTime' >= StartTime AND '$startTime' < EndTime) OR ('$endTime' > StartTime AND '$endTime' <= EndTime)) AND Date = '$date'";
	
	$conflictsResult = mysqli_query($conflictsConnection->conn, $conflictsConnection->sql);
	if ($conflictsResult != false) {
		if (mysqli_num_rows($conflictsResult) > 0) {
			$conflictsConnection->closeConnection();
			return "Conflict";
		}
	}	
	else {
		$conflictsConnection->closeConnection();
		return "Error";
	}	
	
	$conflictsConnection->closeConnection();
	return false;
}

if(!isset($_SESSION['username'])) {
	echo "<script> alert('It appears you are not logged in.  Returning you to the homepage.');
		 window.setTimeout(function() { window.location='index.php'; }, 500); </script>";
	die();
}
else {

	$username = $_SESSION['username'];
	$userConnection = new Connection();
	$userConnection->createConnection();

	$valueFail = false;
	if(isset($_POST['studyID'])) {
		$studyID = $_POST['studyID'];
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['date']) && !empty($_POST['date'])) {
		$date = $_POST['date'];
		$date = mysqli_real_escape_string($userConnection->conn, $date);
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['startTime']) && !empty($_POST['startTime'])) {
		$startTime = $_POST['startTime'];
		$startTime = mysqli_real_escape_string($userConnection->conn, $startTime);
		if ($date == date('Y-m-d')) {
			date_default_timezone_set('America/Chicago');
			$currentTime = date_create(date("H:i:s"));
			if (date_diff($currentTime, date_create($startTime))->format('%R%h') < '+2') {
				$valueFail = true;
				echo "<script> alert('Same-day appointments must be made at least two hours before the start time.');</script>";
			}	
		}
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['endTime']) && !empty($_POST['endTime'])) {
		$endTime = $_POST['endTime'];
		$endTime = mysqli_real_escape_string($userConnection->conn, $endTime);
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['roomID'])) {
		$roomID = $_POST['roomID'];
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['proctorID'])) {
		$proctorID = $_POST['proctorID'];
	}
	else {
		$valueFail = true;
	}

	if(isset($_POST['originalProctorID'])) {
		$originalProctorID = $_POST['originalProctorID'];
	}
	else {
		$originalProctorID = $proctorID;
	}

	if(isset($_POST['classID']) && !empty($_POST['classID'])) {
		$classID = $_POST['classID'];
	}
	else {
		$classID = 'NULL';
	}

	if(isset($_POST['submitType'])) {
		$submitType = $_POST['submitType'];
	}
	else {
		$valueFail = true;
	}

	if ($valueFail == true) {
		echo "<script> alert('There seems to have been an error. Returning you to study selection.  If this error persists, please contact the helpdesk.');
			window.setTimeout(function() { window.location='index.php'; }, 500);		
			</script>";
		die();
	}
	else {

		$userConnection->sql = "SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username'";
		$userResult = mysqli_query($userConnection->conn, $userConnection->sql);
		if ($userResult != false) {
			if (mysqli_num_rows($userResult) == 1) {
				$row = mysqli_fetch_assoc($userResult);
				$subjectID = $row['PersonID'];

				$check = checkIfConflicting($studyID, $subjectID, $proctorID, $roomID, $date, $startTime, $endTime);
				if ($check == "Error") {
					echo "<script> alert('There was an error in processing the submitted data. Please try again.'); window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
					die();
				}
				elseif ($check == "Conflict") {
					echo "<script> alert('There is a conflict between your chosen times and existing personnel/room reservations. Please try again.'); window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
					die();
				}

				$appointmentConnection = new Connection();
				$appointmentConnection->createConnection();

				if ($submitType == 'Create') {
					$appointmentConnection->sql="SELECT SubjectID, StudyID FROM `database name`.`appointments` WHERE SubjectID = $subjectID AND StudyID = $studyID";
					$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);
					if ($appointmentResult != false) {
						if (mysqli_num_rows($appointmentResult) == 0) {
							$appointmentConnection->sql = "INSERT INTO `database name`.`appointments` (RoomID, StudyID, SubjectID, StartTime, EndTime, ExperimenterID, Date, ClassRequested) VALUES ($roomID, $studyID, $subjectID, '$startTime', '$endTime', $proctorID, '$date', $classID)";			
						}
						else {
							echo "<script>alert('It seems you have already made an appointment for this study, so you cannot create another for this study.  You will be re-routed so you can see what appointments you already have.'); 
							window.setTimeout(function() { window.location='existing_appointments.php'; }, 500); </script>";
							die();
						}
					}
				}
				if ($submitType == 'Edit') {
					$appointmentConnection->sql = "UPDATE `database name`.`appointments` SET RoomID = $roomID, StartTime = '$startTime', EndTime = '$endTime', ExperimenterID = $proctorID, Date = '$date', ClassRequested = $classID WHERE StudyID = $studyID AND SubjectID = $subjectID";
				}
				
				$submitFailCheck = false;
				if ($appointmentConnection->submit() == false) {
					$submitFailCheck = true;
				}
				$appointmentConnection->closeConnection();

				if ($submitType == 'Create') {
					if ($submitFailCheck == false) {
						appointmentCreatedMail($subjectID, $proctorID, $studyID, $startTime, $endTime, $roomID, $date);
						echo "<script>alert('Your appointment has been created! You will be notified by your preferred method.');</script>";
					}
					else {
						echo "<script>alert('There was an error in creating your appointment. Please try again.');</script>";
					}
				}
				if ($submitType == 'Edit') {
					if ($submitFailCheck == false) {
						appointmentEditedMail($subjectID, $originalProctorID, $proctorID, $studyID, $startTime, $endTime, $roomID, $date);
						echo "<script>alert('Your appointment has been edited. You will receive a notification of this through your preferred method.');</script>";
					}
					else {
						echo "<script>alert('There was an error in editing your appointment. Please try again.');</script>";
					}
				}
				echo "<script>window.setTimeout( function() { window.location='existing_appointments.php' }, 500);</script>";
				die();

			}
		}
	}
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>