<?php

session_start();
$username = $_SESSION['username'];

include ('links.php');

$userError = false;
$userConnection = new Connection();
$userConnection->createConnection();
$userConnection->sql="SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username' AND PersonUserType = 'Professor'";
$userResult = mysqli_query($userConnection->conn, $userConnection->sql);
if ($userResult != false) {
	if (mysqli_num_rows($userResult) != 1) {
		$userError = true;
	}
	else {
		$userID = mysqli_fetch_assoc($userResult)['PersonID'];
	}
}
else {
	$userError = true;
}

if ($userError == true) {
	echo "<script>alert('Validation failed. Re-routing.'); window.setTimeout( function() { window.location='index.php' }, 500);</script>";
	die();
}

function checkForConflicts($lab, $date, $startTime, $endTime, $seriesID) { // returns whether or not there is a conflict with the desired reservation

	$checkReservationsConnection = new Connection();
	$checkReservationsConnection->createConnection();
	if (!empty($seriesID)) {
		$checkReservationsConnection->sql = "SELECT ReservationID, PersonFirstName, PersonLastName, Date, StartTime, EndTime FROM `database name`.`reservation` AS Res INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = Res.ProfessorID WHERE LabID = $lab AND Date = '$date' AND (StartTime BETWEEN '$startTime' AND '$endTime' OR EndTime BETWEEN '$startTime' AND '$endTime') AND ReservationID NOT IN (SELECT ReservationID FROM `database name`.`series reservation pairings` WHERE SeriesID = $seriesID)";
	}
	elseif (empty($seriesID)) {
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


$seriesArray = array();
$deletesArray = array();
$seriesConnection = new Connection();
$seriesConnection->createConnection();
$seriesConnection->sql="SELECT DISTINCT SeriesID FROM `database name`.`reservation` AS Res INNER JOIN `database name`.`series reservation pairings` AS SRP ON SRP.ReservationID = Res.ReservationID WHERE Res.ProfessorID = (SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username')";
$seriesResult = mysqli_query($seriesConnection->conn, $seriesConnection->sql);
if ($seriesResult != false) {
	if (mysqli_num_rows($seriesResult) > 0) {
		while ($row = mysqli_fetch_assoc($seriesResult)) {
			array_push($seriesArray, $row['SeriesID']);	
		}
	}
}
else {
	echo "<script>alert('There was an error in data processing. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
}

foreach ($seriesArray as $series) {

	if (isset($_POST['change' . $series])) {
		if ($_POST['change' . $series] == 'delete') { // deletes the series entirely
			array_push($deletesArray, $series);
		}

		else {

			$daysOfWeek = array();
			$weekString = "";
			

			if (isset($_POST['Sunday' . $series])) {
				array_push($daysOfWeek, 0);
				$weekString .= 'Sunday';
			}

			if (isset($_POST['Monday' . $series])) {
				array_push($daysOfWeek, 1);
			}
			
			if (isset($_POST['Tuesday' . $series])) {
				array_push($daysOfWeek, 2);
			}

			if (isset($_POST['Wednesday' . $series])) {
				array_push($daysOfWeek, 3);
			}
			
			if (isset($_POST['Thursday' . $series])) {
				array_push($daysOfWeek, 4);
			}
			
			if (isset($_POST['Friday' . $series])) {
				array_push($daysOfWeek, 5);
			}
			
			if (isset($_POST['Saturday' . $series])) {
				array_push($daysOfWeek, 6);
			}

			if (isset($_POST['startTime' . $series])) {
				$startTime = date_create($_POST['startTime' . $series]);
			}
			else { 
				$startTime = null;
			}
			
			if (isset($_POST['endTime' . $series])) {
				$endTime = date_create($_POST['endTime' . $series]);
			}
			else {
				$endTime = null;
			}
			
			if (isset($_POST['startDate' . $series])) {
				$startDate = date_create($_POST['startDate' . $series]);
			}
			else {
				$startDate = null;
			}
			
			if (isset($_POST['endDate' . $series])) {
				$endDate = date_create($_POST['endDate' . $series]);
			}
			else {
				$endDate = null;
			}

			if (date_diff($startDate, $endDate)->format('%R days') == '+ days') {
				$dateArray = array();
				$date = $startDate;
				while ($date <= $endDate) {
					foreach ($daysOfWeek as $day) {
						if (date('N', strtotime(date_format($date, 'Y-m-d'))) == $day) {
							array_push($dateArray, date_format($date,'Y-m-d'));
							break;
						}
					}
					$date->modify('+1 day');	
				}
			}
			else {
				echo "<script>alert('It seems that there is an error in your date entry. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
				die();
			}

			if (count($dateArray) == 0) {
				echo "<script>alert('No dates found given the selected parameters. Please try again, ensuring that there are some dates that fall on the selected days of the week within the selected date range.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
				die();
			}

			if ($_POST['change' . $series] == 'remove') { // deletes particular reservations in the series
				$removeReservationsConnection = new Connection();
				$removeReservationsConnection->createConnection();
				$removeReservationsConnection->sql="DELETE FROM `database name`.`reservation` WHERE ReservationID IN (SELECT SRP.ReservationID FROM `database name`.`series reservation pairings` AS SRP WHERE SeriesID = $series) AND ProfessorID = $userID AND Date IN (";
				foreach ($dateArray as $date) {
					$removeReservationsConnection->sql .= "'$date',";
				}
				$removeReservationsConnection->sql = substr($removeReservationsConnection->sql, 0, -1) . ")";
				if ($removeReservationsConnection->submit() == true) {
					echo "<script>alert('You have successfully removed the reservations for this series between the dates of " . date_format(date_create($dateArray[0]), 'M d, Y') . " and " . date_format(date_create($dateArray[count($dateArray) - 1]), 'M d, Y') . "'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500);</script>";
				}
				else {
					echo "<script>alert('There has been an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500);</script>";
					die();
				}
			}

			else {
				if (isset($_POST['lab' . $series])) {
					$lab = $_POST['lab'. $series];
				}
				else {
					$lab = null;
				}

				if (date_diff($startTime, $endTime)->format('%R minutes') != '+ minutes' && date_format($endTime, 'H:i') != '24:00') {
					echo "<script> alert('It seems that there was an error with the times you entered.  Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
					die();
				}

				$startTime = date_format($startTime, 'H:i:s');
				$endTime = date_format($endTime, 'H:i:s');

				foreach ($dateArray as $date) {
					$check = checkForConflicts($lab, $date, $startTime, $endTime, $series);
					if ($check != false) {
						echo "<script> alert('Warning: $check[0] already has lab $check[1] reserved for $check[2] between $check[3] and $check[4].'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
						die();
					}
				}

				if ($_POST['change' . $series] == 'edit') { // edits to existing times in the series will take precedence over new additions to a series

					if (isset($_POST['description' . $series]) && !empty($_POST['description' . $series])) {
						$description = $_POST['description' . $series];
					}
					else {
						$description = null;
					}
				
					$editReservationConnection = new Connection();
					$editReservationConnection->createConnection();

					if (!empty($description)) {
						$editReservationConnection->sql="UPDATE `database name`.`reservation series` SET SeriesDescription = '$description' WHERE SeriesID = $series";
						if ($editReservationConnection->submit() == false) {
							echo "<script> alert('There was an error when we attempted to update the description of the series. Please try again.'); window.setTimeout( function () { window.location='lab_reservation.php' }, 500); </script>";
							die();
						}
					}

					$editReservationConnection->sql="UPDATE `database name`.`reservation` SET StartTime = '$startTime', EndTime = '$endTime'";
					if (!empty($lab)) {
						$editReservationConnection->sql .= ", LabID = $lab";
					}
					$editReservationConnection->sql .= " WHERE ReservationID IN (SELECT ReservationID FROM `database name`.`series reservation pairings` WHERE SeriesID = $series) AND Date IN (";					
					foreach ($dateArray as $date) {
						$editReservationConnection->sql .= "'$date',";
					}
					$editReservationConnection->sql = substr($editReservationConnection->sql, 0, -1) . ")";
					if ($editReservationConnection->submit() == false) {
						echo "<script>alert('There seems to have been an error in your submission. Please try again.'); window.setTimeout(function() { window.location='lab_reservation.php' }, 500);</script>";
						$editReservationConnection->closeConnection();
						die();
					}
					else {
						echo "<script>alert('You have successfully edited the reservations for this series for the dates between " . date_format(date_create($dateArray[0]), 'M d, Y') . " and " . date_format(date_create($dateArray[count($dateArray) - 1]), 'M d, Y') . ".'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500);</script>";
						$editReservationConnection->closeConnection();
					}
				}

				elseif ($_POST['change' . $series] == 'add') { // adds reservations to series
					$addReservationConnection = new Connection();
					$addReservationConnection->createConnection();
					$addError = false;
					foreach ($dateArray as $date) {
						$addReservationConnection->sql="INSERT INTO `database name`.`reservation` (LabID, ProfessorID, Date, StartTime, EndTime) VALUES ($lab, $userID, '$date', '$startTime', '$endTime');";
						if ($addReservationConnection->submit() == true) {
							$reservationID = $addReservationConnection->getInsertedID();
							$addReservationConnection->sql="INSERT INTO `database name`.`series reservation pairings` (SeriesID, ReservationID) VALUES ($series, $reservationID);";
							if ($addReservationConnection->submit() == false) {
								$addError = true;
								echo "<script> alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
								die();
							}
						}
						else {
							$addError = true;
							echo "<script> alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
							die();
						}
					}
					if ($addError == true) {
						echo "<script> alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
						die();
					}
					else {
						echo "<script> alert('You have successfully added reservations to this series.'); </script>";
					}
				}
			}
		}
	}
}

if (count($deletesArray) > 0) {
	$deleteConnection = new Connection();
	$deleteConnection->createConnection();
	$deleteConnection->sql="DELETE FROM `database name`.`reservation` WHERE ReservationID IN (SELECT SRP.ReservationID FROM `database name`.`series reservation pairings` AS SRP WHERE SRP.SeriesID IN (";
	foreach ($deletesArray as $delete) {
		$deleteConnection->sql .= $delete . ",";
	}
	$deleteConnection->sql = substr($deleteConnection->sql, 0, -1) . ") AND ProfessorID = $userID)";
	if ($deleteConnection->submit() == false) {
		echo "<script>alert('There was an error in deleting a series that you have chosen to delete. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
		die();
	}
	else {
		echo "<script>alert('You have successfully deleted the indicated series.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
	}
}

if (isset($_POST['startTime']) && !empty($_POST['startTime'])) {
	$startTime = date_create($_POST['startTime']);
}
else {
	$startTime = null;
}

if (isset($_POST['endTime']) && !empty($_POST['endTime'])) {
	$endTime = date_create($_POST['endTime']);
}
else {
	$endTime = null;
}

if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
	$startDate = date_create($_POST['startDate']);
}
else {
	$startDate = null;
}

if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
	$endDate = date_create($_POST['endDate']);
}
else {
	$endDate = null;
}

if (isset($_POST['lab']) && !empty($_POST['lab'])) {
	$lab = $_POST['lab'];
}
else {
	$lab = null;
}

if (!empty($startTime) && !empty($endTime) && !empty($startDate) && !empty($endDate) && !empty($lab)) {

	$newReservationSeriesConnection = new Connection();
	$newReservationSeriesConnection->createConnection();

	$inputWeekDays = array();
	if (isset($_POST['Sunday'])) {
		array_push($inputWeekDays, 0);
	}
	if (isset($_POST['Monday'])) {
		array_push($inputWeekDays, 1);
	}
	if (isset($_POST['Tuesday'])) {
		array_push($inputWeekDays, 2);
	}
	if (isset($_POST['Wednesday'])) {
		array_push($inputWeekDays, 3);
	}
	if (isset($_POST['Thursday'])) {
		array_push($inputWeekDays, 4);
	}
	if (isset($_POST['Friday'])) {
		array_push($inputWeekDays, 5);
	}
	if (isset($_POST['Saturday'])) {
		array_push($inputWeekDays, 6);
	}
	if (isset($_POST['description'])) {
		$description = mysqli_real_escape_string($newReservationSeriesConnection->conn, $_POST['description']);
	}
	else {
		$description = null;
	}

	$newSeriesError = false;
	$newReservationSeriesConnection->sql="INSERT INTO `database name`.`reservation series` (SeriesDescription) VALUES ('$description')";
	if ($newReservationSeriesConnection->submit() == false) {
		echo "<script>alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
		die();
	}
	else {
		$seriesID = $newReservationSeriesConnection->getInsertedId();

		$dateArray = array();
		$date = $startDate;
		while (date_diff($date, $endDate)->format('%R days') == '+ days') {
			foreach ($inputWeekDays as $day) {
				if (date('N', strtotime(date_format($date, 'Y-m-d'))) == $day) {
					array_push($dateArray, date_format($date, 'Y-m-d'));
					break;
				}
			}
			$date->modify('+1 day');
		}

		if (date_diff($startTime, $endTime)->format('%R minutes') != '+ minutes' && date_format($endTime, 'H:i') != '24:00') {
			echo "<script> alert('It seems that there was an error with the times you entered.  Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
			die();
		}

		$startTime = date_format($startTime, 'H:i:s');
		$endTime = date_format($endTime, 'H:i:s');

		foreach ($dateArray as $date) {
			$check = checkForConflicts($lab, $date, $startTime, $endTime, null);
			if ($check != false) {
				echo "<script> alert('Warning: $check[0] already has lab $check[1] reserved for $check[2] between $check[3] and $check[4].'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
				die();
			}
		}

		foreach ($dateArray as $date) {
			$newReservationSeriesConnection->sql="INSERT INTO `database name`.`reservation` (LabID, ProfessorID, Date, StartTime, EndTime) VALUES ($lab, $userID, '$date', '$startTime', '$endTime');";
			if ($newReservationSeriesConnection->submit() == true) {
				$reservationID = $newReservationSeriesConnection->getInsertedId();

				$newReservationSeriesConnection->sql="INSERT INTO `database name`.`series reservation pairings` (SeriesID, ReservationID) VALUES ($seriesID, $reservationID);";
				if ($newReservationSeriesConnection->submit() == false) {
					$newReservationSeriesConnection->closeConnection();
					echo "<script>alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
					die();
				}
			}
			else {
				echo "<script>alert('There was an error in your submission. Please try again.'); window.setTimeout( function() { window.location='lab_reservation.php' }, 500); </script>";
				die();
			}	
		}
	}
	$newReservationSeriesConnection->closeConnection();
	echo "<script> alert('You have successfully submitted a new series.'); </script>";
}

echo "<script> window.setTimeout( function() { window.location='lab_reservation.php' } , 500); </script>";
die();

?>