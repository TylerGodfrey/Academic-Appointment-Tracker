<?php
session_start();
$username = $_SESSION['username'];

include ('general_connection.php');

if (isset($_POST['studyID'])) {
	$studyID = $_POST['studyID'];
}

$verification = false;

$professorConnection = new Connection();
$professorConnection->createConnection();
$professorConnection->sql="SELECT PersonUsername FROM `database name`.`person info` AS PI
	INNER JOIN `database name`.`study` AS Study ON PI.PersonID = Study.CreatedBy
WHERE PersonUserType = 'Professor'
 	AND StudyID = $studyID";
$professorResult = mysqli_query($professorConnection->conn, $professorConnection->sql);
if ($professorResult != false) {
	if (mysqli_num_rows($professorResult) == 1) {
		if (mysqli_fetch_assoc($professorResult)['PersonUsername'] == $username) {
			$verification = true;
			echo "<script> alert('verification success'); </script>";
		}
	}
}
else {
	echo "<script> alert('This failed.'); </script>";
}

if ($verification == true) {
	$deleteStudyConnection = new Connection();
	$deleteStudyConnection->createConnection();
	$deleteStudyConnection->sql="DELETE FROM `database name`.`study` WHERE StudyID = $studyID";
	if ($deleteStudyConnection->submit() == true) {
		echo "<script> alert('You have successfully deleted the study.'); window.setTimeout( function() { window.location='study_selection.php' }, 500); </script>";
	}
	else {
		echo "<script> alert('Delete failed. Please try again.'); window.setTimeout( function () { window.location='study_selection.php' }, 500); </script>";
	}
}
else {
	echo "<script> alert('Verification failed. You do not have permission to delete this study.'); window.setTimeout( function () { window.location='study_selection.php' }, 500); </script>";
}