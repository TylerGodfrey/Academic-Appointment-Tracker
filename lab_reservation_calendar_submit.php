<?php

session_start();
$username = $_SESSION['username'];

include('general_connection.php');

$idConnection = new Connection();
$idConnection->createConnection();
$idConnection->sql="SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username'";
$idResult = mysqli_query($idConnection->conn, $idConnection->sql);
if ($idResult != false) {
	if (mysqli_num_rows($idResult) == 1) {
		$userID = mysqli_fetch_assoc($idResult)['PersonID'];
	}
	else {
		echo "<script>alert('There was an error concerning your account. Please try again. If this issue persists, please contact the helpdesk.'); window.setTimeout(function () { window.location='lab_reservation.php' }, 500); </script>";
	}
}
$idConnection->closeConnection();

if (isset($_POST['year'])) {
	$year = $_POST['year'];
}

if (isset($_POST['month'])) {
	$month = $_POST['month'];
}

function getEdits($month, $year) { 
	$arrayIDs = array();
	$arrayEdits = array();
	$getReservationConnection = new Connection();
	$getReservationConnection->createConnection();
	$getReservationConnection->sql="SELECT Date, DAY(Date) AS 'Day', ReservationID FROM `database name`.`reservation` WHERE YEAR(Date) = $year AND MONTH(Date) = $month ORDER BY Date";
	$getReservationResult = mysqli_query($getReservationConnection->conn, $getReservationConnection->sql);
	if ($getReservationResult != false) {
		if (mysqli_num_rows($getReservationResult) > 0) {
			while ($row = mysqli_fetch_assoc($getReservationResult)) {
				$reservationID = $row['ReservationID'];
				$day = $row['Day'];
				array_push($arrayIDs, array($day, $reservationID));
			}
		}
	}

	foreach ($arrayIDs as $id) {
		if (isset($_POST['editReservation' . $id[1]])) {
			array_push($arrayEdits, array($id[0], $id[1]));
		}
	}
	return $arrayEdits;
}

function getNewReservations($month, $year, $edits) { 
	$arrayNewReservations = array();
	for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
		$editCheck = false;
		foreach ($edits as $edit) {
			if ($edit[0] == $i) {
				$editCheck = true;
			}
		}
		if ($editCheck == false) {
			if (isset($_POST['startTime' . $i]) && !empty($_POST['startTime' . $i]) && isset($_POST['endTime' . $i]) && !empty($_POST['endTime' . $i]) && isset($_POST['labNumber' . $i]) && !empty($_POST['labNumber' . $i])) {
				array_push($arrayNewReservations, $i);	
			} 
		}
	}
	return $arrayNewReservations;
}

function getDeletes($month, $year) {
	$arrayIDs = array();
	$arrayDeletes = array();
	$getReservationConnection = new Connection();
	$getReservationConnection->createConnection();
	$getReservationConnection->sql="SELECT Date, DAY(Date) AS 'Day', ReservationID FROM `database name`.`reservation` WHERE YEAR(Date) = $year AND MONTH(Date) = $month ORDER BY Date";
	$getReservationResult = mysqli_query($getReservationConnection->conn, $getReservationConnection->sql);
	if ($getReservationResult != false) {
		if (mysqli_num_rows($getReservationResult) > 0) {
			while ($row = mysqli_fetch_assoc($getReservationResult)) {
				$reservationID = $row['ReservationID'];
				$day = $row['Day'];
				array_push($arrayIDs, array($day, $reservationID));
			}
		}
	}

	foreach ($arrayIDs as $id) {
		if (isset($_POST['deleteReservation' . $id[1]])) {
			array_push($arrayDeletes, array($id[0], $id[1]));
		}
	}
	return $arrayDeletes;
}

function checkForConflictingReservationsAndAppointments($lab, $date, $startTime, $endTime, $reservationID) { // returns whether or not there is a conflict with the desired reservation
	$checkReservationsConnection = new Connection();
	$checkReservationsConnection->createConnection();
	if (!empty($reservationID)) {
		$checkReservationsConnection->sql = "SELECT ReservationID, PersonFirstName, PersonLastName, Date, StartTime, EndTime FROM `database name`.`reservation` AS Res INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = Res.ProfessorID WHERE LabID = $lab AND Date = '$date' AND (StartTime BETWEEN '$startTime' AND '$endTime' OR EndTime BETWEEN '$startTime' AND '$endTime') AND ReservationID <> $reservationID";
	}
	else {
		$checkReservationsConnection->sql = "SELECT ReservationID, PersonFirstName, PersonLastName, Date, StartTime, EndTime FROM `database name`.`reservation` AS Res INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = Res.ProfessorID WHERE LabID = $lab AND Date = '$date' AND (StartTime BETWEEN '$startTime' AND '$endTime' OR EndTime BETWEEN '$startTime' AND '$endTime')";
	}
	$checkReservationResult = mysqli_query($checkReservationsConnection->conn, $checkReservationsConnection->sql);
	if ($checkReservationResult != false) {
		if (mysqli_num_rows($checkReservationResult) > 0) {
			while ($row = mysqli_fetch_assoc($checkReservationResult)) {
				$firstName = $row['PersonFirstName'];
				$lastName = $row['PersonLastName'];
				$professorName = $firstName . " " . $lastName;
				$startTime = $row['StartTime'];
				$endTime = $row['EndTime'];
				$date = $row['Date'];
				$checkReservationsConnection->closeConnection();
				return array($professorName, $lab, $date, $startTime, $endTime); // there is a conflict
			}
		}
	}
	$checkReservationsConnection->closeConnection();

	$checkAppointmentConnection = new Connection();
	$checkAppointmentConnection->createConnection();
	$checkAppointmentConnection->sql = "SELECT PersonFirstName, PersonLastName, StartTime, EndTime, Date FROM `database name`.`appointments` AS App INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = App.SubjectID WHERE RoomID = $lab AND Date = '$date' AND (StartTime BETWEEN '$startTime' AND '$endTime' OR EndTime BETWEEN '$startTime' AND '$endTime'";
	$checkAppointmentResult = mysqli_query($checkAppointmentConnection->conn, $checkAppointmentConnection->sql);
	if ($checkAppointmentResult != false) {
		if (mysqli_num_rows($checkAppointmentResult) > 0) {
			while ($row = mysqli_fetch_assoc($checkAppointmentResult)) {
				$firstName = $row['PersonFirstName'];
				$lastName = $row['PersonLastName'];
				$personName = $firstName . " " . $lastName;
				$startTime = $row['StartTime'];
				$endTime = $row['EndTime'];
				$date = $row['Date'];
				$checkAppointmentConnection->closeConnection();
				return array($personName, $lab, $date, $startTime, $endTime); // there is a conflict
			}
		}
	}
	$checkAppointmentConnection->closeConnection();
	return false; // there are no conflicts
}

$editsArray = getEdits($month, $year); // IDs to be edited along with the days
$newReservationsArray = getNewReservations($month, $year, $editsArray); // days for inserts
$deletesArray = getDeletes($month, $year); // IDs to be deleted along with their days



$editError = false;
if (count($editsArray) > 0) {
	$editConnection = new Connection();
	$editConnection->createConnection();
}

$newReservationError = false;
if (count($newReservationsArray) > 0) {
	$newReservationConnection = new Connection();
	$newReservationConnection->createConnection();
	$newReservationConnection->sql="INSERT INTO `database name`.`reservation` (LabID, ProfessorID, Date, StartTime, EndTime) VALUES ";
}

$deleteError = false;
if (count($deletesArray) > 0) {
	$deleteConnection = new Connection();
	$deleteConnection->createConnection();
}

for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
	if (isset($_POST['startTime' . $i])) {
		$startTime = $_POST['startTime' . $i];
	}
	else {
		$startTime = null;
	}
	if (isset($_POST['endTime' . $i])) {
		$endTime = $_POST['endTime' . $i];
	}
	else {
		$endTime = null;
	}
	if (isset($_POST['labNumber' . $i])) {
		$labNumber = $_POST['labNumber' . $i];
	}
	else {
		$labNumber = null;
	}

	if (isset($startTime) && !empty($startTime) && isset($endTime) && !empty($endTime)) {
		if (date_diff(date_create($endTime), date_create($startTime))->format('%R hours') == '+ hours') {
			echo "<script>alert('There is an error with the times you put in.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
			die();
		}
	}

	$dateExternal = date_format(date_create($year . "-" . $month . "-" . $i), 'l, F j');
	$dateInternal = date_format(date_create($year . "-" . $month . "-" . $i), 'Y-m-d');
	$smallDeletesArray = array();

	$editForInsert = null;
	foreach ($editsArray as $edit) {
		if (($edit[0] == $i) && (empty($editForInsert))) {
			$check = checkForConflictingReservationsAndAppointments($labNumber, $dateInternal, $startTime, $endTime, $edit[1]);
			if ($check == false) {
				$editForInsert = $edit[1];
			}
			else {
				$name = $check[0];
				$lab = $check[1];
				$date = date_format(date_create($check[2]), 'l, F j');
				$startTime = date_format(date_create($check[3]), 'g:i A');
				$endTime = date_format(date_create($check[4]), 'g:i A');
				echo "<script>alert('It seems that $name is already using lab $lab from $startTime to $endTime on $date.');</script>";
				$editError = true;
			}
		}
		elseif (($edit[0] == $i) && (!empty($editForInsert))) {
			echo "<script>alert('Warning: it seems you have entered multiple edits on the same date. This would result in conflicting reservations. The first of your selected edits will be used, and the others ignored.');</script>";
			break;
		}
	}

	$newReservationForInsert = null;
	foreach ($newReservationsArray as $newReservation) {
		if ($newReservation == $i) {
			$check = checkForConflictingReservationsAndAppointments($labNumber, $dateInternal, $startTime, $endTime, 0);
			if ($check == false) {
				$newReservationForInsert = $newReservation; // there will only be one new reservation per day
				break;
			}
			else {
				$name = $check[0];
				$lab = $check[1];
				$date = date_format(date_create($check[2]), 'l, F j');
				$startTime = date_format(date_create($check[3]), 'g:i A');
				$endTime = date_format(date_create($check[4]), 'g:i A');
				echo "<script>alert('It seems that $name is already using lab $lab from $startTime to $endTime on $date.');</script>";
				$newReservationError = true;
			}
		}
	}

	foreach ($deletesArray as $delete) {
		if ($delete[0] == $i) {
			array_push($smallDeletesArray, $delete[1]);
		}
	}
	
	if (!empty($startTime) && !empty($endTime) && !empty($labNumber)) { // deal with new reservations and edits, which deal with these values
	
		if ((!empty($editForInsert)) && ($editError == false)) {
			$editConnection->sql="UPDATE `database name`.`reservation` SET StartTime = '$startTime', EndTime = '$endTime', LabID = $labNumber WHERE ProfessorID = $userID AND ReservationID = $editForInsert";
			if ($editConnection->submit() == false) {
				$editError = true;
			}		
		}
		
		if (!empty($newReservationForInsert)) {
			$newReservationConnection->sql .= "($labNumber, $userID, '$dateInternal', '$startTime', '$endTime'),";	
		}
						
	}

	if (count($smallDeletesArray) > 0) {
		$deleteConnection->sql="DELETE FROM `database name`.`reservation` WHERE ProfessorID = $userID AND ReservationID IN (";
		foreach ($smallDeletesArray as $delete) {
			$deleteConnection->sql .= $delete . ",";
		}	
		$deleteConnection->sql = substr($deleteConnection->sql, 0, -1) . ");";
		if ($deleteConnection->submit() == false) {
			$deleteError = true;
		}
	}
}

if ((count($newReservationsArray) > 0) && ($newReservationError == false)) {
	$newReservationConnection->sql = substr($newReservationConnection->sql, 0, -1) . ";";
	if ($newReservationConnection->submit() == false) {
		$newReservationError = true;
	}
}

if (isset($editConnection)) {
	$editConnection->closeConnection();
}
if (isset($newReservationConnection)) {
	$newReservationConnection->closeConnection();
}
if (isset($deleteConnection)) {
	$deleteConnection->closeConnection();
}

if ($editError == true) {
	echo "<script>alert('There has been an error with editing one of your reservations.');</script>";
}
if ($newReservationError == true) {
	echo "<script>alert('There has been an error with one of your new reservations.');</script>";
}
if ($deleteError == true) {
	echo "<script>alert('There has been an error in deleting one of your reservations.');</script>";
}

echo "<script>window.setTimeout(function() { window.location='lab_reservation.php' }, 500);</script>";

?>