<?php
session_start();
$username = $_SESSION['username'];
?>
<html>
<head>
<title>Professor Report</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/professor_report.css">
</head>
<body>

<?php

include ('links.php');

$infoConnection = new Connection(); // creating connection now so that inputs can be sanitized when they are checked for being set, rather than at the SQL string creation
$infoConnection->createConnection();

function createFilterAdditions ($array) {

	if (count($array) > 0) {
		$addToSQLString = " AND (";
		for ($i = 0; $i < count($array); $i++) {
			$addToSQLString .= $array[$i];
			if ($i < count($array) - 1) {
				$addToSQLString .= " OR ";
			}
		}
		$addToSQLString .= ")";
		return $addToSQLString;
	}
	else {
		return false;
	}
}

$gradebookSQLArray = array();
if (isset($_POST['gradebookAdded'])) {
	$gradebookAdded = true;
	array_push($gradebookSQLArray, "AddedToGradeBook = 1");
}
else {
	$gradebookAdded = false;
}

if (isset($_POST['gradebookNotAdded'])) {
	$gradebookNotAdded = true;
	array_push($gradebookSQLArray, "AddedToGradeBook = 0");
}
else {
	$gradebookNotAdded = false;
}

if (isset($_POST['onDate'])) {
	$onDate = true;
}
else {
	$onDate = false;
}

if (isset($_POST['afterDate'])) {
	$afterDate = true;
}
else {
	$afterDate = false;
}

if (isset($_POST['beforeDate'])) {
	$beforeDate = true;
}
else {
	$beforeDate = false;
}

$dateSQLArray = array();
if (isset($_POST['dateValue']) && !empty($_POST['dateValue'])) {
	$dateValue = $_POST['dateValue'];

	if ($onDate == true) {
		array_push($dateSQLArray, "Date = '" . date_format(date_create($dateValue), 'Y-m-d') . "'");
	}

	if ($afterDate == true) {
		array_push($dateSQLArray, "Date > '" . date_format(date_create($dateValue), 'Y-m-d') . "'");
	}

	if ($beforeDate == true) {
		array_push($dateSQLArray, "Date < '" . date_format(date_create($dateValue), 'Y-m-d') . "'");
	}
}
else {
	$dateValue = null;
}

$attendanceSQLArray = array();
if (isset($_POST['attendanceYes'])) {
	$attendanceYes = true;
	array_push($attendanceSQLArray, "ShowOrNoShow = 1");
}
else {
	$attendanceYes = false;
}

if (isset($_POST['attendanceNo'])) {
	$attendanceNo = true;
	array_push($attendanceSQLArray, "ShowOrNoShow = 0");
}
else {
	$attendanceNo = false;
}

if (isset($_POST['attendanceLaterDate'])) {
	$attendanceLaterDate = true;
	array_push($attendanceSQLArray, "ShowOrNoShow = NULL");
}
else {
	$attendanceLaterDate = false;
}

if (isset($_POST['pointsLess'])) {
	$pointsLess = true;
}
else {
	$pointsLess = false;
}

if (isset($_POST['pointsGreater'])) {
	$pointsGreater = true;
}
else {
	$pointsGreater = false;
}

if (isset($_POST['pointsEqual'])) {
	$pointsEqual = true;
}
else {
	$pointsEqual = false;
}


$pointSQLArray = array();
if (isset($_POST['pointValue']) && !empty($_POST['pointValue'])) {
	$pointValue = $_POST['pointValue'];
	$pointValue = mysqli_real_escape_string($infoConnection->conn, $pointValue);
	$pointValue = (int)$pointValue;
	if (is_int($pointValue)) {
		if ($pointsLess == true) {
			array_push($pointSQLArray, "ExpectedPointValue < $pointValue");
		}

		if ($pointsGreater == true) {
			array_push($pointSQLArray, "ExpectedPointValue > $pointValue");
		}

		if ($pointsEqual == true) {
			array_push($pointSQLArray, "ExpectedPointValue = $pointValue");
		}
	}
	else {
		$pointValue = null;
		echo "<script> alert('Non-integer input for integer field. Please try again.'); </script>";
	}
}
else {
	$pointValue = null;
}

// check method of comparison
if (isset($_POST['timeLess'])) {
	$timeLess = true;
}
else {
	$timeLess = false;
}

if (isset($_POST['timeGreater'])) {
	$timeGreater = true;
}
else {
	$timeGreater = false;
}

if (isset($_POST['timeEqual'])) {
	$timeEqual = true;
}
else {
	$timeEqual = false;
}

if (isset($_POST['timeValue']) && !empty($_POST['timeValue'])) {
	$timeValue = $_POST['timeValue'];
}
else {
	$timeValue = null;
}

$timeSQLArray = array();
$timeRelatedError = false;
if (isset($_POST['timeType'])) {
	$timeType = $_POST['timeType'];

	if (!empty($timeValue)) {
		$timeValue = mysqli_real_escape_string($infoConnection->conn, $timeValue);
		$timeValue = (int)$timeValue;
		if (is_int($timeValue)) {
			if ($timeLess == true) {
				// users can sort by two different time values; these create the SQL code appropriate to the option they chose.
				if ($timeType == 'Actual') {
					array_push($timeSQLArray, "MINUTE(TIMEDIFF(ActualEndTime, ActualStartTime)) < $timeValue");	
				}
				elseif ($timeType == 'Expected') {
					array_push($timeSQLArray, "ExpectedTimeInMinutes < $timeValue");
				}
			}

			if ($timeGreater == true) {
				if ($timeType == 'Actual') {
					array_push($timeSQLArray, "MINUTE(TIMEDIFF(ActualEndTime, ActualStartTime)) > $timeValue");
				}
				elseif ($timeType == 'Expected') {
					array_push($timeSQLArray, "ExpectedTimeInMinutes > $timeValue");
				}
			}

			if ($timeEqual == true) {
				if ($timeType == 'Actual') {
					array_push($timeSQLArray, "MINUTE(TIMEDIFF(ActualEndTime, ActualStartTime)) = $timeValue");
				}
				elseif ($timeType == 'Expected') {
					array_push($timeSQLArray, "ExpectedTimeInMinutes = $timeValue");
				}
			}	
		}
		else {
			echo "<script> alert('Non-integer input for integer field. Please try again.'); </script>";
		}
	}
}
else { // the query cannot be filtered on time if there is no time type selected
	$timeType = false;
}

if ((!empty($timeType) || // if the time type is selected
	 !empty($timeValue) || // if the time value is selected
	   (!empty($timeLess) || !empty($timeGreater) || !empty($timeEqual))) // or any method of comparison is selected
	   	&& (empty($timeType) || empty($timeValue) || (empty($timeLess) && empty($timeGreater) && empty($timeEqual)))) { // while there is no time type, value, or method of comparison, there cannot be a comparison
	$timeType = null;
	$timeValue = null;
	$timeLess = false;
	$timeGreater = false;
	$timeEqual = false;
	echo "<script> alert('It appears you missed an important part of the time filter.  We proceeded as normal with the rest of your filtering.  Feel free to try again.'); </script>";	
}

if (isset($_POST['desiredClass'])) {
	$desiredClass = $_POST['desiredClass'];
	$desiredClass = mysqli_real_escape_string($infoConnection->conn, $desiredClass);
}
else {
	$desiredClass = null;
}

if (isset($_POST['desiredSubject'])) {
	$desiredSubject = $_POST['desiredSubject'];
	$desiredSubject = mysqli_real_escape_string($infoConnection->conn, $desiredSubject);
}
else {
	$desiredSubject = null;
}

if (isset($_POST['desiredStudy'])) {
	$desiredStudy = $_POST['desiredStudy'];
	$desiredStudy = mysqli_real_escape_string($infoConnection->conn, $desiredStudy);
}
else {
	$desiredStudy = null;
}

if (isset($_POST['desiredProctor'])) {
	$desiredProctor = $_POST['desiredProctor'];
	$desiredProctor = mysqli_real_escape_string($infoConnection->conn, $desiredProctor);
}
else {
	$desiredProctor = null;
}

echo "<div id='pageBody'><table id='centerTable'> <form action='professor_report_gradebook_add.php' method='post'><th> Subject Name </th> <th> Study Name </th> <th> Class Requested </th> <th> Date </th> <th> Proctor Name </th> <th> Attendance </th> <th> Expected Points </th> <th> Expected Time </th> <th> Actual Time </th> <th> Added to Gradebook </th>"; // id set to centerTable so that CSS can be used differently for this table and the table that formats the filters as desired

$username = mysqli_real_escape_string($infoConnection->conn, $username); // username must be sanitized before the query, in case of apostrophes
$infoConnection->sql = "SELECT CONCAT(Sub.PersonFirstName, ' ', Sub.PersonLastName) AS 'Subject Name', StudyName AS 'Study Name', ClassName AS 'Class Name', App.Date AS 'Date', CONCAT(Proc.PersonFirstName, ' ', Proc.PersonLastName) AS 'Proctor Name', App.ShowOrNoShow AS 'Attendance', Study.ExpectedPointValue AS 'Expected Points', Study.ExpectedTimeInMinutes AS 'Expected Time', 
(HOUR(TIMEDIFF(App.ActualEndTime, App.ActualStartTime)) * 60) +
MINUTE(TIMEDIFF(App.ActualEndTime, App.ActualStartTime)) AS 'Actual Time', AddedToGradeBook AS 'Added To Gradebook', App.StudyID AS 'StudyID', App.SubjectID AS 'SubjectID'
FROM `database name`.`appointments` AS App 
	INNER JOIN `database name`.`person info` AS Sub ON Sub.PersonID = App.SubjectID 
	INNER JOIN `database name`.`person info` AS Proc ON Proc.PersonID = App.ExperimenterID 
	INNER JOIN `database name`.`study` AS Study ON Study.StudyID = App.StudyID 
	INNER JOIN `database name`.`classes` AS Class ON Class.ClassID = App.ClassRequested 
	INNER JOIN `database name`.`person info` AS Professor ON Professor.PersonID = Class.InstructorID 
WHERE Professor.PersonUsername = '$username'";

$gradebookString = createFilterAdditions($gradebookSQLArray);
if ($gradebookString != false) {
	$infoConnection->sql .= $gradebookString;
}

if (!empty($dateValue)) {
	$dateString = createFilterAdditions($dateSQLArray);
	if ($dateString != false) {
		$infoConnection->sql .= $dateString;
	}
}

$attendanceString = createFilterAdditions($attendanceSQLArray);
if ($attendanceString != false) {
	$infoConnection->sql .= $attendanceString;
}

if (!empty($pointValue)) {
	$pointString = createFilterAdditions($pointSQLArray);
	if ($pointString != false) {
		$infoConnection->sql .= $pointString;
	}
}

if (!empty($timeType) && !empty($timeValue)) {
	$timeString = createFilterAdditions($timeSQLArray);
	if ($timeString != false) {
		$infoConnection->sql .= $timeString;
	}
}

if (!empty($desiredClass)) {
	$desiredClass = mysqli_real_escape_string($infoConnection->conn, $desiredClass);
	$infoConnection->sql .= " AND ClassName LIKE '%$desiredClass%'"; // inputs must be sanitized in case of injection attack
}

if (!empty($desiredSubject)) {
	$desiredSubject = mysqli_real_escape_string($infoConnection->conn, $desiredSubject);
	$infoConnection->sql .= " AND CONCAT(Sub.PersonFirstName, ' ', Sub.PersonLastName) LIKE '%$desiredSubject%'";
}

if (!empty($desiredStudy)) {
	$desiredStudy = mysqli_real_escape_string($infoConnection->conn, $desiredStudy);
	$infoConnection->sql .= " AND StudyName LIKE '%$desiredStudy%'";
}

if (!empty($desiredProctor)) {
	$desiredProctor = mysqli_real_escape_string($infoConnection->conn, $desiredProctor);
	$infoConnection->sql .= " AND CONCAT(Proc.PersonFirstName, ' ', Proc.PersonLastName) LIKE '%$desiredProctor%'";
}

$infoResult = mysqli_query($infoConnection->conn, $infoConnection->sql);
$rowNumber = 0;
if ($infoResult != false) {
	if (mysqli_num_rows($infoResult) > 0) {
		while ($row = mysqli_fetch_assoc($infoResult)) {
			$rowNumber++;
			echo "<tr>
				<td>" . $row['Subject Name'] . "</td>
				<td>" . $row['Study Name'] . "</td>
				<td>" . $row['Class Name'] . "</td>
				<td>" . date_format(date_create($row['Date']), 'M d, Y') . "</td>
				<td>" . $row['Proctor Name'] . "</td>
				<td>";
			if ($row['Attendance'] == 1) {
				echo "Yes";
			}
			elseif ($row['Attendance'] == 0) {
				echo "No";
			}
			elseif (isnull($row['Attendance'])) {
				echo "Not yet verified";
			}
			echo "</td>
				<td>" . $row['Expected Points'] . "</td>
				<td>" . $row['Expected Time'] . "</td>
				<td>";
			if (!is_null($row['Actual Time'])) {
				echo $row['Actual Time'];
			}
			elseif (is_null($row['Actual Time'])) {
				echo "No actual time reported yet.";
			}
			echo "</td>
				<td>";
				if ($row['Added To Gradebook'] == 0) {
					echo "<input type='checkbox' name='AddToGradebook" . $rowNumber . "' value='" . $row['SubjectID'] . "," . $row['StudyID'] . "'>" . " Check to add";
				}
				if ($row['Added To Gradebook'] == 1) {
					echo "Added";
				}
			echo "</td></tr>";
		}
		echo "</table><input type='hidden' name='numberOfRows' value='" . $rowNumber . "'><br><div id='centerButton'><input type='submit' value='Submit Gradebook Checks'></div></form>";
	}
}
if ($rowNumber == 0) {
	echo "</table></form>";
}

$infoConnection->closeConnection();


echo "<form action='professor_report.php' method='post'>
	<br>
	<b>Leave filters blank if you do not want them to affect the query (e.g. leave 'Point filters' alone, rather than selecting greater than, less than, and equal to) </b> <br>
	Gradebook filters: <br>
		<input type='checkbox' name='gradebookAdded'";
	if ($gradebookAdded == true) {
		echo " checked";
	}
	echo ">Added to gradebook<br>
		<input type='checkbox' name='gradebookNotAdded'";
	if ($gradebookNotAdded == true) {
		echo " checked";
	}
	echo ">Not added to gradebook<br>
		<br>
	Date filters: <br>
	  	<input type='checkbox' name='onDate'";
	if ($onDate == true) {
		echo " checked";
	}
	echo ">On this date<br>
	  	<input type='checkbox' name='afterDate'";
	if ($afterDate == true) {
		echo " checked";
	}
	echo ">After this date<br>
	  	<input type='checkbox' name='beforeDate'";
	if ($beforeDate == true) {
		echo " checked";
	}
	echo ">Before this date<br>
	  	Date: <input type='text' name='dateValue'";
	if (!empty($dateValue)) {
		echo " value='$dateValue'";
	}
	echo "><br>
	  	<br>
  	Attendance filters: <br>
  		<input type='checkbox' name='attendanceYes'";
  	if ($attendanceYes == true) {
  		echo " checked";
  	}
  	echo ">Attended<br>
  		<input type='checkbox' name='attendanceNo'";
  	if ($attendanceNo == true) {
  		echo " checked";
  	}
  	echo ">Not attended<br>
  		<input type='checkbox' name='attendanceLaterDate'";
  	if ($attendanceLaterDate == true) {
  		echo " checked";
  	}
  	echo ">Future date (N/A)<br>
  		<br>
	Point filters: <br>
		<input type='checkbox' name='pointsLess'";
	if ($pointsLess == true) {
		echo " checked";
	}
	echo "> Less than <br>
		<input type='checkbox' name='pointsGreater'";
	if ($pointsGreater == true) {
		echo " checked";
	}
	echo "> Greater than <br>
		<input type='checkbox' name='pointsEqual'";
	if ($pointsEqual == true) {
		echo " checked";
	}
	echo "> Equal to <br>
		Points: <input type='number' name='pointValue'";
	if (!empty($pointValue)) {
		echo "value='$pointValue'";
	}
	echo "><br>
		<br>
	Time filters: <br>
		<input type='radio' name='timeType' value='Expected'";
	if ($timeType == 'Expected') {
		echo " checked='checked'";
	}
	echo "> Expected <input type='radio' name='timeType' value='Actual'";
	if ($timeType == 'Actual') {
		echo " checked='checked'";
	}
	echo "> Actual <br>
		<input type='checkbox' name='timeLess'";
	if ($timeLess == true) {
		echo " checked";
	}
	echo "> Less than <br>
		<input type='checkbox' name='timeGreater'";
	if ($timeGreater == true) {
		echo " checked";
	}
	echo "> Greater than <br>
		<input type='checkbox' name='timeEqual'";
	if ($timeEqual == true) {
		echo " checked";
	}
	echo "> Equal to <br>
		Time: <input type='number' name='timeValue'";
	if (!empty($timeValue)) {
		echo " value='$timeValue'";
	}
	echo "> <br>
  	<br>
  		<table id='filterTable'>
  		<tr> <td> Class: </td> <td> <input type='text' name='desiredClass'";
  	if (!empty($desiredClass)) {
  		echo " value='$desiredClass'";
  	}
  	echo "> </td> </tr>
  		<tr> <td> Subject: </td> <td> <input type='text' name='desiredSubject'";
  	if (!empty($desiredSubject)) {
  		echo " value='$desiredSubject'";
  	}
  	echo "> </td> </tr>
		<tr> <td> Study: </td> <td> <input type='text' name='desiredStudy'";
	if (!empty($desiredStudy)) {
		echo " value='$desiredStudy'";
	}
	echo "> </td> </tr>
		<tr> <td> Proctor: </td> <td> <input type='text' name='desiredProctor'";
	if (!empty($desiredProctor)) {
		echo " value='$desiredProctor'";
	}
	echo "> </td> </tr>
		</table>
		<br>
		<input type='submit' value='Submit filters'></div>";
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>