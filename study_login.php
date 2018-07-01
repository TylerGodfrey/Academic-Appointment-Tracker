<?php
session_start();
$username = $_SESSION['username'];

include ('links.php');

if (isset($_POST['studyID'])) {
	$studyID = $_POST['studyID'];
}

if (isset($_POST['password'])) {
	$password = $_POST['password'];
}

$passwordCheck = false;

$studyConnection = new Connection();
$studyConnection->createConnection();
$studyConnection->sql = "SELECT Password FROM `database name`.`study` WHERE StudyID = $studyID";
$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
if ($studyResult != false) {
	if (mysqli_num_rows($studyResult) == 1) {
		if ($password == mysqli_fetch_assoc($studyResult)['Password']) {
			$passwordCheck = true;
		}
	}
}
$studyConnection->closeConnection();

if ($passwordCheck == true) {
	$pairingConnection = new Connection();
	$pairingConnection->createConnection();
	$username = mysqli_real_escape_string($pairingConnection->conn, $username);
	$pairingConnection->sql = "SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username'";
	$pairingResult = mysqli_query($pairingConnection->conn, $pairingConnection->sql);
	if ($pairingResult != false) {
		if (mysqli_num_rows($pairingResult) == 1) {
			$personID = mysqli_fetch_assoc($pairingResult)['PersonID'];
			$pairingConnection->sql = "INSERT INTO `database name`.`studyexperimenterpairs` (StudyID, ExperimenterID) VALUES ($studyID, $personID)";
			if ($pairingConnection->submit() == true) {
				echo "<script> alert('You have successfully logged into the study. Returning you to study selection.');
						window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
			}
			else {
				echo "<script> alert('There seems to be an error with the login attempt.  Please try again.  If this error persists, please contact the help desk.'); window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
			}
		}
		elseif (mysqli_num_rows($pairingResult) == 0) {
			echo "<script> alert('It seems you don't have a profile setup. Please setup your profile and try again.);
						window.setTimeout( function() { window.location='profile.php' }, 500); </script>";
		}
		elseif (mysqli_num_rows($pairingResult) > 1) {
			echo "<script> alert('It appears that there is an issue with your data. Please contact the help desk for assistance. Click OK to return to the study selection page.'); window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
		}
	}
}

?>