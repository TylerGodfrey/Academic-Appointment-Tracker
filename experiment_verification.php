<?php
session_start();
$username = $_SESSION['username'];
?>

<html>
<head>
<title>Experiment Verification</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/experiment_verification.css">

</head>
<body>
<?php

include ('links.php');

$date = date_create();
$timezone = new DateTimeZone('America/Chicago');
$time = $date->setTimezone($timezone);
$time = date_format($time, 'H:i:s');
$date = date_format($date, 'Y-m-d');
$printTimeFormat = "h:i A";
$internalTimeFormat = "H:i";
$columnNames = array('Study Name', 'Proctor Name', 'Subject Name', 'Date', 'Start Time', 'End Time', 'Show / No Show', 'Actual Start Time', 'Actual End Time');
$lineNumber = 1;

echo "<table border='2' width='100%'><form action='experiment_verification_submit.php' method='post'>";

$appointments = new Connection();
$appointments->sql = "SELECT Study.StudyID AS 'Study ID', Study.StudyName AS 'Study Name', CONCAT(Proctor.PersonFirstName, ' ', Proctor.PersonLastName) AS 'Proctor Name', Subject.PersonID AS 'Subject ID', CONCAT(Subject.PersonFirstName, ' ', Subject.PersonLastName) AS 'Subject Name', App.Date AS 'Date', App.StartTime AS 'Expected Start Time', App.EndTime AS 'Expected End Time' FROM `database name`.`appointments` AS App LEFT OUTER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID LEFT OUTER JOIN `database name`.`person info` AS Subject ON Subject.PersonID = App.SubjectID LEFT OUTER JOIN `database name`.`person info` AS Proctor ON Proctor.PersonID = App.ExperimenterID WHERE ((App.Date = '$date' AND App.EndTime <= '$time') OR (App.Date < '$date')) AND App.ShowOrNoShow IS NULL AND Proctor.PersonUsername = '$username'";
$appointments->createConnection();
$appointmentsResult = mysqli_query($appointments->conn, $appointments->sql);

if ($appointmentsResult != false) {
	if (mysqli_num_rows($appointmentsResult) > 0) {
		echo "<tr><th colspan='9' text-align='center'>Unverified Appointments</th></tr><tr>";

		foreach ($columnNames as $column) {
			echo "<th>" . $column . "</th>";
		}

		echo "</tr>";
		while ($row = mysqli_fetch_assoc($appointmentsResult)) {
			$subjectID = $row['Subject ID'];
			$studyID = $row['Study ID'];
			$results = array($row['Study Name'], $row['Proctor Name'], $row['Subject Name'], date_format(date_create($row['Date']), 'M d, Y'), date_format(date_create($row['Expected Start Time']), $printTimeFormat) , date_format(date_create($row['Expected End Time']), $printTimeFormat));
			$inputs = array("<div class='attended'><input name='show" . $lineNumber . "' type='radio' value='Attended'>Attended</div> <div class='unattended'><input name='show" . $lineNumber . "' type='radio' value='Unattended'>Unattended</div>", "<input name='startTime" . $lineNumber . "' type='text' class='input'></input>", "<input name='endTime" . $lineNumber . "' type='text' class='input'></input>");
			echo "<tr>";
			foreach ($results as $result) {
				echo "<td>" . $result . "</td>";
			}
			foreach ($inputs as $input) {
				echo "<td class='inputCell'>" . $input . "</td>";
			}
			echo "<input type='hidden' name='studyID" . $lineNumber . "' value='$studyID'>
			<input type='hidden' name='subjectID" . $lineNumber . "' value='$subjectID'>
			</tr>";
			$lineNumber++;
		}
	}
}

$appointments->sql = "SELECT Study.StudyID AS 'Study ID', Study.StudyName AS 'Study Name', CONCAT(Proctor.PersonFirstName, ' ', Proctor.PersonLastName) AS 'Proctor Name', Subject.PersonID AS 'Subject ID', CONCAT(Subject.PersonFirstName, ' ', Subject.PersonLastName) AS 'Subject Name', App.Date AS 'Date', App.StartTime AS 'Expected Start Time', App.EndTime AS 'Expected End Time', App.ShowOrNoShow AS 'Show Or No Show', App.ActualStartTime AS 'Actual Start Time', App.ActualEndTime AS 'Actual End Time' FROM `database name`.`appointments` AS App LEFT OUTER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID LEFT OUTER JOIN `database name`.`person info` AS Subject ON Subject.PersonID = App.SubjectID LEFT OUTER JOIN `database name`.`person info` AS Proctor ON Proctor.PersonID = App.ExperimenterID WHERE ((App.Date = '$date' AND App.EndTime <= '$time') OR (App.Date < '$date')) AND App.ShowOrNoShow IS NOT NULL AND Proctor.PersonUsername = '$username' ORDER BY App.ShowOrNoShow ASC";
$appointmentsResult = mysqli_query($appointments->conn, $appointments->sql);

if ($appointmentsResult != false) {
	if (mysqli_num_rows($appointmentsResult) > 0) {

		echo "<tr><th colspan='9' text-align='center'>Verified Appointments</th></tr><tr>";

		foreach ($columnNames as $column) {
			echo "<th>" . $column . "</th>";
		}

		echo "</tr>";

		while ($row = mysqli_fetch_assoc($appointmentsResult)) {
			$subjectID = $row['Subject ID'];
			$studyID = $row['Study ID'];
			$results = array($row['Study Name'], $row['Proctor Name'], $row['Subject Name'], date_format(date_create($row['Date']), 'M d, Y'), date_format(date_create($row['Expected Start Time']), $printTimeFormat) , date_format(date_create($row['Expected End Time']), $printTimeFormat));
			
			if ($row['Show Or No Show'] == 1) {
				$attendanceString = "<div class='attended'><input name='show" . $lineNumber . "' type='radio' value='Attended' checked='checked'>Attended</div> <div class='unattended'><input name='show" . $lineNumber . "' type='radio' value='Unattended'>Unattended</div>";
			}
			elseif ($row['Show Or No Show'] == 0) {
				$attendanceString = "<div class='attended'><input name='show" . $lineNumber . "' type='radio' value='Attended'>Attended</div> <div class='unattended'><input name='show" . $lineNumber . "' type='radio' value='Unattended' checked='checked'>Unattended</div>";	
			}

			if (!empty($row['Actual Start Time'])) {
				$actualStartTime = "<input name='startTime" . $lineNumber . "' type='text' class='input' value='" . date_format(date_create($row['Actual Start Time']), 'g:i A') . "'></input>";
			}
			elseif (empty($row['Actual Start Time'])) {
				$actualStartTime = "<input name='startTime" . $lineNumber . "' type='text' class='input'></input>";
			}

			if (!empty($row['Actual End Time'])) {
				$actualEndTime = "<input name='endTime" . $lineNumber . "' type='text' class='input' value='" . date_format(date_create($row['Actual End Time']), 'g:i A') . "'></input>";
			}
			elseif(empty($row['Actual End Time'])) {
				$actualEndTime = "<input name='endTime" . $lineNumber . "' type='text' class='input'></input>";
			}

			$inputs = array($attendanceString, $actualStartTime, $actualEndTime);
			echo "<tr>";
			foreach ($results as $result) {
				echo "<td>" . $result . "</td>";
			}
			foreach ($inputs as $input) {
				echo "<td class='inputCell'>" . $input . "</td>";
			}
			echo "<input type='hidden' name='studyID" . $lineNumber . "' value='$studyID'>
			<input type='hidden' name='subjectID" . $lineNumber . "' value='$subjectID'>
			</tr>";
			$lineNumber++;
		}
	}
}

$appointments->closeConnection();

if ($lineNumber > 1) {
	echo "<tr><td colspan='9'><input type='submit' value='Submit Verifications'></td></tr></form></table>";
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>