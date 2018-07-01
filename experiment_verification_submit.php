<?php

session_start();
$username = $_SESSION['username'];

include ('general_connection.php');

$timeArray = localtime();
$time = date_format(date_create($timeArray[2] . ":" . $timeArray[1] . ":" . $timeArray[0]), 'H:i');
$date = date('Y-m-d');

$appointments = new Connection();
$appointments->createConnection();
$appointments->sql = "SELECT Study.StudyID AS 'Study ID', Study.StudyName AS 'Study Name', CONCAT(Proctor.PersonFirstName, ' ', Proctor.PersonLastName) AS 'Proctor Name', Subject.PersonID AS 'Subject ID', CONCAT(Subject.PersonFirstName, ' ', Subject.PersonLastName) AS 'Subject Name', App.Date AS 'Date', App.StartTime AS 'Expected Start Time', App.EndTime AS 'Expected End Time' FROM `database name`.`appointments` AS App LEFT OUTER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID LEFT OUTER JOIN `database name`.`person info` AS Subject ON Subject.PersonID = App.SubjectID LEFT OUTER JOIN `database name`.`person info` AS Proctor ON Proctor.PersonID = App.ExperimenterID WHERE ((App.Date = '$date' AND App.EndTime <= '$time') OR (App.Date < '$date')) AND App.ShowOrNoShow IS NULL AND Proctor.PersonUsername = '$username'";
$appointments->createConnection();
$appointmentsResult = mysqli_query($appointments->conn, $appointments->sql);

if ($appointmentsResult != false) {
  $appointmentCount = mysqli_num_rows($appointmentsResult);
}
else {
      echo "<script> alert('There was an error in your submission. Please try again.'); window.setTimeout(function() { window.location='experiment_verification.php' }, 500); </script>";
      die();
}

$previouslyVerifiedAppointments = new Connection();
$previouslyVerifiedAppointments->createConnection();
$previouslyVerifiedAppointments->sql = "SELECT Study.StudyID AS 'Study ID', Study.StudyName AS 'Study Name', CONCAT(Proctor.PersonFirstName, ' ', Proctor.PersonLastName) AS 'Proctor Name', Subject.PersonID AS 'Subject ID', CONCAT(Subject.PersonFirstName, ' ', Subject.PersonLastName) AS 'Subject Name', App.Date AS 'Date', App.StartTime AS 'Expected Start Time', App.EndTime AS 'Expected End Time' FROM `database name`.`appointments` AS App LEFT OUTER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID LEFT OUTER JOIN `database name`.`person info` AS Subject ON Subject.PersonID = App.SubjectID LEFT OUTER JOIN `database name`.`person info` AS Proctor ON Proctor.PersonID = App.ExperimenterID WHERE ((App.Date = '$date' AND App.EndTime <= '$time') OR (App.Date < '$date')) AND App.ShowOrNoShow IS NOT NULL AND Proctor.PersonUsername = '$username'";
$previouslyVerifiedAppointmentsResult = mysqli_query($previouslyVerifiedAppointments->conn, $previouslyVerifiedAppointments->sql);

if ($previouslyVerifiedAppointmentsResult != false) {
  $previouslyVerifiedCount = mysqli_num_rows($previouslyVerifiedAppointmentsResult);
  $newAppointmentCount = $appointmentCount + $previouslyVerifiedCount;
}
else {
      echo "<script> alert('There was an error in your submission. Please try again.'); window.setTimeout(function() { window.location='experiment_verification.php' }, 500); </script>";
      die();
}


$verificationConnection = new Connection();
$verificationConnection->createConnection();


for ($i = 1; $i <= $appointmentCount; $i++) {
  if (isset($_POST['studyID' . $i])) {
    $studyID = $_POST['studyID' . $i];
  }
  else {
    $studyID = null;
  }
  if (isset($_POST['subjectID' . $i])) {
    $subjectID = $_POST['subjectID' . $i];
  }
  else {
    $subjectID = null;
  }
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
  if (isset($_POST['show' . $i])) {
    if ($_POST['show' . $i] == 'Attended') {
      $showOrNoShow = 1;
    }
    elseif($_POST['show' . $i] == 'Unattended') {
      $showOrNoShow = 0;
    }
    else {
      $showOrNoShow = null;
    }
  }
  else {
    $showOrNoShow = null;
  }


  if (empty($subjectID) || empty($studyID)) {
    echo "<script> alert('There was an error in the submitted data. Please try again.'); window.setTimeout( function() { window.location='experiment_verification.php' }, 500); </script>";
    die();
  }

  if (($showOrNoShow == 1 && (empty($startTime) || empty($endTime))) ||
  ($showOrNoShow == 0 && (!empty($startTime) || !empty($endTime))) ||
  (empty($showOrNoShow) && (!empty($startTime) || !empty($endTime)))) {
    echo "<script> alert('The attendance indicator and the entries for actual start and end times are inconsistent with each other. Please try again.'); window.setTimeout( function() { window.location='experiment_verification.php' }, 500); </script>";
    die();
  }

  if (!empty($startTime) && !empty($endTime) && $showOrNoShow == 1) {
    $verificationConnection->sql="UPDATE `database name`.`appointments` SET ShowOrNoShow = $showOrNoShow, ActualStartTime = '$startTime', ActualEndTime = '$endTime' WHERE StudyID = $studyID AND SubjectID = $subjectID";  
  }
  elseif ($showOrNoShow == 0 && !is_null($showOrNoShow)) {
    $verificationConnection->sql="UPDATE `database name`.`appointments` SET ShowOrNoShow = 0, ActualStartTime = NULL, ActualEndTime = NULL WHERE StudyID = $studyID AND SubjectID = $subjectID";
  }
  
  if (!is_null($showOrNoShow)) {
    if ($verificationConnection->submit() == false) {
      $verificationConnection->closeConnection();
      echo "<script> alert('There was an error in the submission of your attendance verification.  Please try again.'); window.setTimeout(function() { window.location='experiment_verification.php' }, 500); </script>";
      die();
    }
  }
}


for ($i = $appointmentCount + 1; $i <= $newAppointmentCount; $i++) {
  if (isset($_POST['studyID' . $i])) {
    $studyID = $_POST['studyID' . $i];
  }
  else {
    $studyID = null;
  }
  if (isset($_POST['subjectID' . $i])) {
    $subjectID = $_POST['subjectID' . $i];
  }
  else {
    $subjectID = null;
  }
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
  if (isset($_POST['show' . $i])) {
    if ($_POST['show' . $i] == 'Attended') {
      $showOrNoShow = 1;
    }
    elseif($_POST['show' . $i] == 'Unattended') {
      $showOrNoShow = 0;
    }
    else {
      $showOrNoShow = null;
    }
  }
  else {
    $showOrNoShow = null;
  }


  if (empty($subjectID) || empty($studyID)) {
    echo "<script> alert('There was an error in the submitted data. Please try again.'); window.setTimeout( function() { window.location='experiment_verification.php' }, 500); </script>";
    die();
  }

  if (($showOrNoShow == 1 && (empty($startTime) || empty($endTime))) ||
  ($showOrNoShow == 0 && (!empty($startTime) || !empty($endTime))) ||
  (empty($showOrNoShow) && (!empty($startTime) || !empty($endTime)))) {
    echo "<script> alert('The attendance indicator and the entries for actual start and end times are inconsistent with each other. Please try again.'); window.setTimeout( function() { window.location='experiment_verification.php' }, 500); </script>";
    die();
  }

  if (!empty($startTime) && !empty($endTime) && $showOrNoShow == 1) {
    $verificationConnection->sql="UPDATE `database name`.`appointments` SET ShowOrNoShow = $showOrNoShow, ActualStartTime = '$startTime', ActualEndTime = '$endTime' WHERE StudyID = $studyID AND SubjectID = $subjectID";  
  }
  elseif ($showOrNoShow == 0 && !is_null($showOrNoShow)) {
    $verificationConnection->sql="UPDATE `database name`.`appointments` SET ShowOrNoShow = 0, ActualStartTime = NULL, ActualEndTime = NULL WHERE StudyID = $studyID AND SubjectID = $subjectID";
  }
  
  if (!is_null($showOrNoShow)) {
    if ($verificationConnection->submit() == false) {
      $verificationConnection->closeConnection();
      echo "<script> alert('There was an error in the submission of your attendance verification.  Please try again.'); window.setTimeout(function() { window.location='experiment_verification.php' }, 500); </script>";
      die();
    }
  }
}

$verificationConnection->closeConnection();
echo "<script> alert('You have successfully verified attendance for the appointments prior to the current time and date. Thank you!'); window.setTimeout(function() { window.location='experiment_verification.php' }, 500); </script>";

?>
