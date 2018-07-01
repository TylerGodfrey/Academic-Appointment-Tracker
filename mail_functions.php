<?php

require_once ('general_connection.php');

function getPersonInfo ($personID) {
	$personConnection = new Connection();
	$personConnection->createConnection();
	$personConnection->sql="SELECT PersonFirstName, PersonLastName, PersonPhoneNumber, CarrierExtension, PersonEmail, PrefContactMethod
	FROM `database name`.`person info` AS Sub 
		LEFT OUTER JOIN `database name`.`cellphone carriers` AS Carrier 
			ON Carrier.CarrierID = Sub.PersonCarrier
	WHERE PersonID = $personID";
	$personResult = mysqli_query($personConnection->conn, $personConnection->sql);
	if ($personResult != false) {
		if (mysqli_num_rows($personResult) == 1) {
			$row = mysqli_fetch_assoc($personResult);
			$personName = $row['PersonFirstName'] . ' ' . $row['PersonLastName'];
			$personPreferredContact = $row['PrefContactMethod'];
			if ($personPreferredContact == 'Phone') {
				$personPhone = $row['PersonPhoneNumber'];
				$personCarrier = $row['CarrierExtension'];
				$personTargetEmail = $personPhone . '@' . $personCarrier;
			}
			elseif ($personPreferredContact == 'E-mail') {
				$personTargetEmail = $row['PersonEmail'];
			}
			return array($personName, $personTargetEmail);
		}
	}
}

function getStudyName ($studyID) {
	$studyConnection = new Connection();
	$studyConnection->createConnection();
	$studyConnection->sql="SELECT StudyName FROM `database name`.`study` WHERE StudyID = $studyID";
	$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
	if ($studyResult != false) {
		if (mysqli_num_rows($studyResult) == 1) {
			$studyName = mysqli_fetch_assoc($studyResult)['StudyName'];
			return $studyName;
		}
	}
}

function appointmentCreatedMail ($subjectID, $proctorID, $studyID, $startTime, $endTime, $roomID, $date) {

	$subjectInfo = getPersonInfo($subjectID);
	$subjectName = $subjectInfo[0];
	$subjectTargetEmail = $subjectInfo[1];
	$proctorInfo = getPersonInfo($proctorID);
	$proctorName = $proctorInfo[0];
	$proctorTargetEmail = $proctorInfo[1];
	$studyName = getStudyName($studyID);
	$messageDate = date_format(date_create($date), 'l, F j'); // Example: Monday, January 22nd
	$messageStartTime = date_format(date_create($startTime), 'g:i A');
	$messageEndTime = date_format(date_create($endTime), 'g:i A');


	// the subject and the proctor need different messages
	$subjectMessage = wordwrap("You have created an appointment for the $studyName study.  You will be meeting with $proctorName on $messageDate at $messageStartTime-$messageEndTime in Lab $roomID.", 70);
	$proctorMessage = wordwrap("You have been assigned to an appointment for the $studyName study.  You will be meeting with $subjectName on $messageDate at $messageStartTime-$messageEndTime in Lab $roomID.", 70);

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <tyler.godfrey@my.simpson.edu>' . "\r\n";
	$title = "Psychology Experiment Scheduled";

	// send messages
	mail($subjectTargetEmail,$title,$subjectMessage,$headers);
	mail($proctorTargetEmail,$title,$proctorMessage,$headers);
}

function appointmentEditedMail ($subjectID, $originalProctorID, $newProctorID, $studyID, $startTime, $endTime, $roomID, $date) {

	// subject info
	$subjectInfo = getPersonInfo($subjectID);
	$subjectName = $subjectInfo[0];
	$subjectTargetEmail = $subjectInfo[1];
	// other message details
	$studyName = getStudyName($studyID);
	$messageDate = date_format(date_create($date), 'l, F j'); // Example: Monday, January 22nd
	$messageStartTime = date_format(date_create($startTime), 'g:i A');
	$messageEndTime = date_format(date_create($endTime), 'g:i A');


	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <tyler.godfrey@my.simpson.edu>' . "\r\n";
	$title = "Psychology Experiment Re-Scheduled";
	

	if ($originalProctorID != $newProctorID) {
		// original proctor info
		$originalProctorInfo = getPersonInfo($originalProctorID);
		$originalProctorTargetEmail = $originalProctorInfo[1];
		// new proctor info
		$newProctorInfo = getPersonInfo($newProctorID);
		$newProctorName = $newProctorInfo[0];
		$newProctorTargetEmail = $newProctorInfo[1];
	
		$originalProctorMessage = wordwrap("Your appointment with $subjectName on $messageDate has been re-assigned due to schedule changes.", 70);
		$newProctorMessage = wordwrap("You have been assigned to an appointment for the $studyName study.  You will be meeting with $subjectName on $messageDate at $messageStartTime-$messageEndTime in Lab $roomID of Mary Berry.", 70);

		$title = "Psychology Experiment Re-Assignment";
		
		mail($originalProctorTargetEmail,$title,$originalProctorMessage,$headers);
		mail($newProctorTargetEmail,$title,$newProctorMessage,$headers);

		$proctorName = $newProctorName;
	}
	else {
		$proctorInfo = getPersonInfo($originalProctorID);
		$proctorName = $proctorInfo[0];
		$proctorTargetEmail = $proctorInfo[1];

		$proctorMessage = wordwrap("Your appointment with $subjectName on $messageDate has been re-scheduled to $messageDate at $messageStartTime-$messageEndTime in Lab $roomID of Mary Berry.", 70);

		mail($proctorTargetEmail,$title,$proctorMessage,$headers);
	}

	// the subject and the proctor need different messages
	$subjectMessage = wordwrap("You have edited your appointment for the $studyName study.  You will now be meeting with $proctorName on $messageDate at $messageStartTime-$messageEndTime in Lab $roomID of Mary Berry.", 70);

	// send messages
	mail($subjectTargetEmail,$title,$subjectMessage,$headers);	
}

function appointmentDeletedMail ($subjectID, $proctorID, $studyID, $date) {
	// subject info
	$subjectInfo = getPersonInfo($subjectID);
	$subjectName = $subjectInfo[0];
	$subjectTargetEmail = $subjectInfo[1];
	// proctor Info
	$proctorInfo = getPersonInfo($proctorID);
	$proctorName = $proctorInfo[0];
	$proctorTargetEmail = $proctorInfo[1];
	// other message details
	$studyName = getStudyName($studyID);
	$messageDate = date_format(date_create($date), 'l, F j'); // Example: Monday, January 22nd

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <tyler.godfrey@my.simpson.edu>' . "\r\n";
	$title = "Psychology Experiment Deleted";
	
	// the subject and the proctor need different messages
	$subjectMessage = wordwrap("You have deleted your appointment for the $studyName study.", 70);

	$proctorMessage = wordwrap("Your appointment with $subjectName on $messageDate has been deleted.", 70);

	// send messages
	mail($subjectTargetEmail,$title,$subjectMessage,$headers);		
	mail($proctorTargetEmail,$title,$proctorMessage,$headers);

}

function sendReminders ($subjectID, $proctorID, $studyID, $startTime, $endTime, $labNumber) {
	// subject info
	$subjectInfo = getPersonInfo($subjectID);
	$subjectName = $subjectInfo[0];
	$subjectTargetEmail = $subjectInfo[1];
	// proctor Info
	$proctorInfo = getPersonInfo($proctorID);
	$proctorName = $proctorInfo[0];
	$proctorTargetEmail = $proctorInfo[1];
	// other message details
	$studyName = getStudyName($studyID);
	$startTime = date_format(date_create($startTime), 'g:i A');
	$endTime = date_format(date_create($endTime), 'g:i A');
	
	$headers = "MIME-Version: 1.0" . "\r\n" . "Content-type:text/html;charset=UTF-8" . "\r\n" . "From: <tyler.godfrey@my.simpson.edu>" . "\r\n";
	$title = "Psychology Experiment Reminder";
	
	// the subject and the proctor need different messages
	$subjectMessage = wordwrap("You have an appointment with $proctorName at Lab $labNumber in Mary Berry tomorrow, from $startTime to $endTime, for the $studyName study.", 70);
	$proctorMessage = wordwrap("You have an appointment with $subjectName at Lab $labNumber in Mary Berry tomorrow, from $startTime to $endTime, for the $studyName study. Please remember to verify their attendance on the appointment tracker website afterwards.", 70);
	
	// send messages
	mail($subjectTargetEmail,$title,$subjectMessage,$headers);
	mail($proctorTargetEmail,$title,$proctorMessage,$headers);
}

?>