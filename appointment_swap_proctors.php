<?php

include ('general_connection.php');

if (isset($_POST['studyID'])) {
	$studyID = $_POST['studyID'];
}
else {
	$studyID = null;
}

if (isset($_POST['subjectID'])) {
	$subjectID = $_POST['subjectID'];
}
else {
	$subjectID = null;
}

if (isset($_POST['proctorID'])) {
	$proctorID = $_POST['proctorID'];
}
else {
	$proctorID = null;
}

if (!empty($studyID) && !empty($subjectID) && !empty($proctorID)) {
	$swapProctorsConnection = new Connection();
	$swapProctorsConnection->createConnection();
	$swapProctorsConnection->sql="UPDATE `database name`.`appointments` SET ExperimenterID = $proctorID WHERE StudyID = $studyID AND SubjectID = $subjectID";
	if ($swapProctorsConnection->submit() == false) {
		echo "<script> alert('An error occurred when we tried to swap the proctors for you. Please try again.'); </script>";
	}
	else {
		echo "<script> alert('You have successfully swapped proctors for this study.'); </script>";
	}
	$swapProctorsConnection->closeConnection();
}
else {
	echo "<script> alert('Incomplete data provided. Please try again.'); </script>";
}

echo "<script> window.setTimeout( function() { window.location='existing_appointments.php' }, 500); </script>";
die();
?>