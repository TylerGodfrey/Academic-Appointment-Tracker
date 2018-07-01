<?php
session_start();
$username = $_SESSION['username'];

include ('general_connection.php');

if (isset($_POST['numberOfRows'])) {
	$numberOfRows = $_POST['numberOfRows'];
}
else {
	$numberOfRows = 0;
}

function getGradebookInfo ($repetitions) {
	$arrayOfInfo = array();
	for ($i = 1; $i <= $repetitions; $i++) {
		if (isset($_POST['AddToGradebook' . $i])) {
			$SubjectStudy = preg_split('/,/', $_POST['AddToGradebook' . $i]); // array holding the information for an individual appointment for which the added to gradebook value needs to be changed to true
			array_push($arrayOfInfo, array($SubjectStudy[0], $SubjectStudy[1]));
		}
	}
	return $arrayOfInfo;
}

$arrayOfInfo = getGradebookInfo($numberOfRows);

if (!empty($arrayOfInfo)) {
	$submissionSuccess = true;
	$appointmentConnection = new Connection();
	$appointmentConnection->createConnection();
	foreach ($arrayOfInfo as $appointmentIdentifier) {
		$appointmentConnection->sql = "UPDATE `database name`.`appointments` SET AddedToGradebook = 1 WHERE SubjectID = $appointmentIdentifier[0] AND StudyID = $appointmentIdentifier[1]";
		if ($appointmentConnection->submit() == false) {
			$submissionSuccess = false;
		}
	}
	$appointmentConnection->closeConnection();

	if ($submissionSuccess == false) {
		echo "<script> alert('It seems there was an error with your submission.  Please try again.'); window.setTimeout( function () { window.location = 'professor_report.php' }, 500); </script>";
	}
	else {
		echo "<script> alert('You have successfully submitted the grade entry note. Returning you to your report.'); window.setTimeout( function () { window.location = 'professor_report.php' }, 500); </script>";
	}
}
else {
	echo "<script> alert('It seems you did not check off any appointments. Returning you to your report.'); window.setTimeout( function () {
			window.location = 'professor_report.php' }, 500); </script>";
}

?>