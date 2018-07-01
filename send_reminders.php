<?php

include ('general_connection.php');
include ('mail_functions.php');

$appointmentConnection = new Connection();
$appointmentConnection->createConnection();
$appointmentConnection->sql="SELECT Sub.PersonID AS 'Subject ID', Proc.PersonID AS 'Proctor ID', App.StartTime AS 'Start Time', App.EndTime AS 'End Time', App.RoomID AS 'Lab Number', App.StudyID AS 'Study ID' FROM `database name`.`appointments` AS App INNER JOIN `database name`.`person info` AS Sub ON Sub.PersonID = App.SubjectID INNER JOIN `database name`.`person info` AS Proc ON Proc.PersonID = App.ExperimenterID WHERE App.Date = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))";
$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);
if ($appointmentResult != false) {
	if (mysqli_num_rows($appointmentResult) > 0) {
		while ($row = mysqli_fetch_assoc($appointmentResult)) {
			$subjectID = $row['Subject ID'];
			$proctorID = $row['Proctor ID'];
			$startTime = $row['Start Time'];
			$endTime = $row['End Time'];
			$studyID = $row['Study ID'];
			$labNumber = $row['Lab Number'];
			
			sendReminders($subjectID, $proctorID, $studyID, $startTime, $endTime, $labNumber);
		}
	}
}
$appointmentConnection->closeConnection();

?>