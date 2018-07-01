<?php
session_start();
$username = $_SESSION['username'];

include ('general_connection.php');
include ('mail_functions.php');

if (isset($_POST['studyID'])) {
	$studyID = $_POST['studyID'];
}

if (isset($_POST['subjectID'])) {
	$subjectID = $_POST['subjectID'];
}

if (isset($_POST['proctorID'])) {
	$proctorID = $_POST['proctorID'];
}

if (isset($_POST['date'])) {
	$date = $_POST['date'];
}

if (isset($_POST['relation'])) {
	$relation = $_POST['relation'];

	$deleteAppointmentConnection = new Connection();
	$deleteAppointmentConnection->createConnection();

	if ($relation == 'Subject') {

		$deleteAppointmentConnection->sql = "DELETE FROM `database name`.`appointments` WHERE StudyID = $studyID AND SubjectID = (SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '$username')";
		if ($deleteAppointmentConnection->submit() == true) {
			appointmentDeletedMail($subjectID, $proctorID, $studyID, $date);
			echo "<script>alert('You have successfully deleted your appointment!');
					window.setTimeout(function() { window.location='existing_appointments.php'; }, 500);</script>";
		}
		else {
			echo "<script>alert('There was an error deleting your appointment.  Please try again.  If this issue persists, please contact the helpdesk.');
					window.setTimeout(function() { window.location='existing_appointments.php'; }, 500);</script>";	
		}
		$deleteAppointmentConnection->closeConnection();
		
	}
	elseif ($relation == 'Proctor') {
		$deleteAppointmentConnection->sql = "SELECT PersonUsername FROM `database name`.`appointments` AS App INNER JOIN `person info` AS Proc ON Proc.PersonID = App.ExperimenterID WHERE Proc.PersonUsername = '$username' AND App.StudyID = $studyID AND App.SubjectID = $subjectID";
		$deleteAppointmentResult = mysqli_query($deleteAppointmentConnection->conn, $deleteAppointmentConnection->sql);
		if ($deleteAppointmentResult != false) {
			if (mysqli_num_rows($deleteAppointmentResult) > 0) {
				$deleteAppointmentConnection->sql = "DELETE FROM `database name`.`appointments` WHERE StudyID = $studyID AND SubjectID = $subjectID";
				if ($deleteAppointmentConnection->submit() == true) {
					appointmentDeletedMail($proctorID, $subjectID, $studyID, $date); // proctor and subject IDs reversed here so that the messages received make sense to the recipient
					echo "<script>alert('You have successfully deleted the appointment.'); window.setTimeout(function() { window.location='existing_appointments.php'; }, 500);</script>";
				}
				else {
					echo "<script>alert('There was an error deleting the appointment. Please try again. If this issue persists, please contact the helpdesk.'); window.setTimeout(function() { window.location='existing_appointments.php'; }, 500);</script>";
				}		
			}
		}
		$deleteAppointmentConnection->closeConnection();
	}
}
else {
	echo "<script> alert('It seems there was an error. Please try again.'); window.setTimeout( function() { window.location='existing_appointments.php' }, 500); </script>";
}

?>