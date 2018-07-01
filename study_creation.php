<?php
session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/study_creation.css">
</head>
<body>

<?php
include ('links.php');

function currentValueSelector($optionString, $valueInDatabase) {
	$stringToFind = "<option value='$valueInDatabase'>";
	$locationForEditing = strpos($optionString, $stringToFind) + strlen($stringToFind) - 1;
	$newOptionString = substr_replace($optionString, ' selected', $locationForEditing, 0);
	return $newOptionString;
}

if ($_SESSION['userType'] == 'Professor') {
	echo "<div id='studyCreation'>";
	echo "<center>Study Creation</center>
	<form action='study_submission.php' method='post'>
	";
	if (!isset($_POST['studyID'])) {
		echo "Study Name:<br>
		<input type='text' name='studyname'><br>
		Password:<br>
		<input type='password' name='password'><br>
		Study Description:<br>
		<input type='text' name='description'><br><br>";

		$classesQuery = new Connection();
		$classesQuery->createConnection();
		$classesQuery->sql = 'SELECT * FROM `classes`';
		$classesResult = mysqli_query($classesQuery->conn, $classesQuery->sql);
		$classesDropdown = '';
		if (mysqli_num_rows($classesResult) > 0) {
			$classesDropdown = $classesDropdown . "<select multiple name='classes[]'>";
			while ($row = mysqli_fetch_assoc($classesResult)) {
				$classesDropdown = $classesDropdown . "<option value='" . $row['ClassID'] . "'>" . $row['ClassName'] . "</option>";
			}
			$classesDropdown = $classesDropdown . "</select>";
		}
		else {
			$classesDropdown = "None Available.";
		}
		echo "Classes Available: <br>" . $classesDropdown . "<br><br>
		Start Date: <br>
		<input type='date' name='startdate'><br>
		End Date: <br>
		<input type='date' name='enddate'><br>
		Expected Points: <br>
		<input type='text' name='points'> <br>
		Expected Time (in minutes): <br>
		<input type='text' name='time'> <br>
		Concurrent Testing Support (would the proctor be able to run multiple experiments like it at the same time?): <br>
		<input type='checkbox' name='concurrentTest' value='true'> Allow Concurrent Testing <br>
		Rooms: <br>
		<input type='checkbox' name='lab2' value='2'> Lab 2 <br>
		<input type='checkbox' name='lab3' value='3'> Lab 3 <br>
		<input type='checkbox' name='lab4' value='4'> Lab 4 <br>
		<input type='checkbox' name='lab5' value='5'> Lab 5 <br>
		<input type='checkbox' name='lab6' value='6'> Lab 6 <br>
		<input type='checkbox' name='lab7' value='7'> Lab 7 <br>
		<input name='submitType' type='hidden' value='create'>
		<br>
		<input type='submit' value='Submit'>
		</form>";
	}
	elseif (isset($_POST['studyID'])) {

		$studyConnection = new Connection();
		$studyConnection->createConnection();
		$studyConnection->sql = "SELECT S.*, PI.PersonUsername FROM `database name`.`study` AS S INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = S.CreatedBy WHERE StudyID = " . $_POST['studyID'];
		$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
		if (mysqli_num_rows($studyResult) == 1) {
			$row = mysqli_fetch_assoc($studyResult);
			$createdBy = $row['PersonUsername'];
			if ($createdBy == $_SESSION['username']) { // need to add pre-existing data here
				echo "<input name='studyID' type='hidden' value='". $_POST['studyID'] . "'>
				Study Name:<br>
				<input type='text' name='studyname' value='" . $row['StudyName'] . "'><br>
				Password:<br>
				<input type='password' name='password' value='" . $row['Password'] . "'><br>
				Study Description:<br>
				<input type='text' name='description' value='" . $row['Description'] . "'><br><br>";

				$classArray = array();
				$studyClassConnection = new Connection();
				$studyClassConnection->createConnection();
				$studyClassConnection->sql = "SELECT * FROM `database name`.`class study pairings` WHERE StudyID = " . $_POST['studyID'];
				$studyClassResult = mysqli_query($studyClassConnection->conn, $studyClassConnection->sql);
				if (mysqli_num_rows($studyClassResult) > 0) {
					while ($studyClassRow = mysqli_fetch_assoc($studyClassResult)) {
						array_push($classArray, $studyClassRow['ClassID']);
					}
				}
				$studyClassConnection->closeConnection();

				$classesQuery = new Connection();
				$classesQuery->createConnection();
				$classesQuery->sql = 'SELECT * FROM `classes`';
				$classesResult = mysqli_query($classesQuery->conn, $classesQuery->sql);
				$classesDropdown = '';
				if (mysqli_num_rows($classesResult) > 0) {
					$classesDropdown = $classesDropdown . "<select multiple name='classes[]'>";
					while ($classRow = mysqli_fetch_assoc($classesResult)) {
						$classesDropdown = $classesDropdown . "<option value='" . $classRow['ClassID'] . "'>" . $classRow['ClassName'] . "</option>";
					}
					$classesDropdown = $classesDropdown . "</select>";
				}
				else {
					$classesDropdown = "None Available.";
				}
				$classesQuery->closeConnection();

				foreach ($classArray as $class) {
					$classesDropdown = currentValueSelector($classesDropdown, $class);
				}

				echo "Classes Available: <br>" . $classesDropdown . "<br><br>
				Start Date: <br>
				<input type='date' name='startdate' value='" . date_format(date_create($row['StartDate']), 'Y-m-d') . "'><br>
				End Date: <br>
				<input type='date' name='enddate' value='" . date_format(date_create($row['EndDate']), 'Y-m-d') . "'><br>
				Expected Points: <br>
				<input type='text' name='points' value='" . $row['ExpectedPointValue'] . "'> <br>
				Expected Time (in minutes): <br>
				<input type='text' name='time' value='" . $row['ExpectedTimeInMinutes'] . "'> <br>
				Concurrent Testing Support (would the proctor be able to run multiple experiments like it at the same time?): <br>
				<input type='checkbox' name='concurrentTest' value='true'"; 
				if ($row['ConcurrentTesting'] == true) {
					echo " checked";
				}
				echo "> Allow Concurrent Testing <br> Rooms: <br>";

				$studyLabArray = array();
				$studyConnection->sql="SELECT * FROM `database name`.`study lab pairings` WHERE StudyID = " . $_POST['studyID'];
				$studyResult = mysqli_query($studyConnection->conn, $studyConnection->sql);
				if (mysqli_num_rows($studyResult) > 0) {
					while ($row = mysqli_fetch_assoc($studyResult)) {
						array_push($studyLabArray, $row['RoomID']);
					}
				}
				$studyConnection->closeConnection();
				
				function createLabCheckbox($labNumber, $studyLabArray) {
					$checkboxString = "<input type='checkbox' name='lab" . $labNumber . "' value='" . $labNumber. "'";
					foreach ($studyLabArray as $labEntry) {
						if ($labNumber == $labEntry) {
							$checkboxString .= " checked";
						}
					}
					$checkboxString .= "> Lab " . $labNumber . " <br>";
					echo $checkboxString;
				}

				$labArray = array();
				$labConnection = new Connection();
				$labConnection->createConnection();
				$labConnection->sql="SELECT * FROM `database name`.`room info`";
				$labResult = mysqli_query($labConnection->conn, $labConnection->sql);
				if (mysqli_num_rows($labResult) > 0) {
					while ($row = mysqli_fetch_assoc($labResult)) {
						array_push($labArray, $row['RoomID']);
					}
				}

				foreach ($labArray as $uncheckedNumber) {
					createLabCheckbox($uncheckedNumber, $studyLabArray);
				}
				$labConnection->closeConnection();
				echo "<input name='submitType' type='hidden' value='edit'> <br>
				<input type='submit' name='submit'>
				</form>";
			}
		}
	}
	echo "</div>";
}
else {
	echo "<script> alert('Unauthorized access.  Returning you to the home page.'); window.setTimeout(function() { window.location='index.php'; }, 500); </script>";
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type='text/javascript' src='js/study_creation.js'></script>
</body>
</html>