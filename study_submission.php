<?php
session_start();

include ('general_connection.php');

function redirection ($type) {
	if ($type == 'Professor Verification Failure') {
		$message = 'You are not logged in as a professor.  Redirecting.';
		$destination = 'study_selection.php';
	}
	elseif ($type == 'Create Failure') {
		$message = 'Study creation failed.';
		$destination = 'study_creation.php';
	}
	elseif ($type == 'Create Success') {
		$message = 'You have successfully created a new study. Returning you to study selection.';
		$destination = 'study_selection.php';
	}
	elseif ($type == 'Edit Failure') {
		$message = 'Study editing failed.';
		$destination = 'study_creation.php';
	}
	elseif ($type == 'Edit Success') {
		$message = 'You have successfully edited the study record. Returning you to study selection.';
		$destination = 'study_selection.php';
	}
	elseif ($type == 'Study Professor Pairing Failure') {
		$message = 'Warning: You have not been paired with the study as a potential proctor.';
		$destination = 'study_selection.php';
	}
	elseif ($type == 'Date Error') {
		$message = 'There seems to have been an error with the entered start and end dates. Please try again.';
		$destination = 'study_creation.php';
	}
	echo "<script type='text/javascript'> alert('" . $message . "'); window.setTimeout( function() { window.location='$destination'; }, 500); </script>";
	die();
}
if ($_SESSION['userType'] <> 'Professor') { // they should not be here at all if they are not a professor
	redirection('Professor Verification Failure');
}
else {

	$studySubmitConnection = new Connection(); // creating connection now allows for sanitizing inputs
	$studySubmitConnection->createConnection();
	$checkForFailure = false;

	if (isset($_POST['submitType'])) {
		$submitType = $_POST['submitType'];
	}

	if (isset($_POST['studyID'])) {
		$studyID = $_POST['studyID'];

		$classStudyArray = array();

		$classStudyConnection = new Connection();
		$classStudyConnection->createConnection();
		$classStudyConnection->sql="SELECT * FROM `class study pairings` WHERE StudyID = $studyID";
		$classStudyResult = mysqli_query($classStudyConnection->conn, $classStudyConnection->sql);
		if (mysqli_num_rows($classStudyResult) > 0) {
			while ($row = mysqli_fetch_assoc($classStudyResult)) {
				array_push($classStudyArray, $row['ClassID']);
			}
		}
		$classStudyConnection->closeconnection();

		$labStudyArray = array();

		$labStudyConnection = new Connection();
		$labStudyConnection->createConnection();
		$labStudyConnection->sql="SELECT * FROM `study lab pairings` WHERE StudyID = $studyID";
		$labStudyResult = mysqli_query($labStudyConnection->conn, $labStudyConnection->sql);
		if ($labStudyResult != false && mysqli_num_rows($labStudyResult) > 0) {
			while ($row = mysqli_fetch_assoc($labStudyResult)) {
				array_push($labStudyArray, $row['RoomID']);
			}
		}
	}
	else {
		$classStudyArray = array();
		$labStudyArray = array();
	}

	if (isset($_POST['studyname'])) {
		$studyName = $_POST['studyname'];
		$studyName = mysqli_real_escape_string($studySubmitConnection->conn, $studyName);
	}

	if (isset($_POST['password'])) {
		$password = $_POST['password'];
		$password = mysqli_real_escape_string($studySubmitConnection->conn, $password);
	}

	if (isset($_POST['description'])) {
		$description = $_POST['description'];
		$description = mysqli_real_escape_string($studySubmitConnection->conn, $description);
	}

	$unchangedClassesArray = array();
	$classesToAddArray = array();
	$classesToRemoveArray = array();
	if (isset($_POST['classes'])) {
		foreach($_POST['classes'] as $class) {
			$checkForMatch = false;
			foreach($classStudyArray as $classFromDB) {
				if ($classFromDB == $class) {
					array_push($unchangedClassesArray, $class);
					$checkForMatch = true;
				}
			}
			if ($checkForMatch == false) {
				array_push($classesToAddArray, $class);
			}

		}
		foreach($classStudyArray as $classFromDB) {
			$checkForMatch = false;
			foreach($_POST['classes'] as $class) {
				if ($class == $classFromDB) {
					$checkForMatch = true;
				}
			}
			if ($checkForMatch == false) {
				array_push($classesToRemoveArray, $classFromDB);	
			}
		}
	}
	else {
		foreach($classStudyArray as $classFromDB) {
			array_push($classesToRemoveArray, $classFromDB);
		}
	}

	if (isset($_POST['startdate'])) {
		$startDate = $_POST['startdate'];
		$startDate = mysqli_real_escape_string($studySubmitConnection->conn, $startDate);
		$startDate = date_create($startDate);
	}

	if (isset($_POST['enddate'])) {
		$endDate = $_POST['enddate'];
		$endDate = mysqli_real_escape_string($studySubmitConnection->conn, $endDate);
		$endDate = date_create($endDate);
	}

	if ($startDate > $endDate) {
		redirection('Date Error');
	}

	if (isset($_POST['points'])) {
		$points = $_POST['points'];
		$points = intval($points);
	}

	if (isset($_POST['time'])) {
		$time = $_POST['time'];
		$time = intval($time);
	}

	if (isset($_POST['concurrentTest'])) {
		$concurrentTest = 1;
	}
	else {
		$concurrentTest = 0;
	}
	
	$labToAddArray = array();
	$labToRemoveArray = array();

	if (isset($_POST['lab2'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 2) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 2);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 2) {
				array_push($labToRemoveArray, 2);		
			}
		}
	}

	if (isset($_POST['lab3'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 3) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 3);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 3) {
				array_push($labToRemoveArray, 3);		
			}
		}
	}
	
	if (isset($_POST['lab4'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 4) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 4);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 4) {
				array_push($labToRemoveArray, 4);		
			}
		}
	}

	if (isset($_POST['lab5'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 5) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 5);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 5) {
				array_push($labToRemoveArray, 5);		
			}
		}
	}
	

	if (isset($_POST['lab6'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 6) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 6);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 6) {
				array_push($labToRemoveArray, 6);		
			}
		}
	}
	

	if (isset($_POST['lab7'])) {
		$labCheck = false;
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 7) {
				$labCheck = true;
			}
		}
		if ($labCheck == false) {
			array_push($labToAddArray, 7);	
		}
	}
	else {
		foreach ($labStudyArray as $labStudy) {
			if ($labStudy == 7) {
				array_push($labToRemoveArray, 7);		
			}
		}
	}

	if ($submitType == 'create') {
		$idConnection = new Connection();
		$idConnection->createConnection();
		$idConnection->sql = "SELECT PersonID FROM `database name`.`person info` WHERE PersonUsername = '" . $_SESSION['username'] . "'";
		$idResult = mysqli_query($idConnection->conn, $idConnection->sql);
		if ($idResult != false) {
			if (mysqli_num_rows($idResult) == 1) {
				$professorID = mysqli_fetch_assoc($idResult)['PersonID'];
			}
			else {
				redirection('Create Failure');
			}
		}
		else {
			redirection('Create Failure');
		}
		$studySubmitConnection->sql = "INSERT INTO `database name`.`study` (StudyName, Password, Description, StartDate, EndDate, ExpectedPointValue, ExpectedTimeInMinutes, CreatedBy, ConcurrentTesting) VALUES ('$studyName', '$password', '$description', '" . date_format($startDate, 'Y-m-d') . "', '" . date_format($endDate, 'Y-m-d') . "', $points, $time, $professorID, $concurrentTest);";
		if ($studySubmitConnection->submit() == false) {
			redirection('Create Failure');
		}
		$studyID = $studySubmitConnection->getInsertedId();
		
		// add bridge data between the study and the classes associated with it.
		if (count($classesToAddArray) > 0) {
			$studySubmitConnection->sql = "INSERT INTO `database name`.`class study pairings` (ClassID, StudyID) VALUES ";
			foreach ($classesToAddArray as $classToAdd) {
				$studySubmitConnection->sql .= "(" . $classToAdd . ", " . $studyID . "),";
			}
			$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ";";
			if ($studySubmitConnection->submit() == false) {
				redirection('Create Failure');
			}		
		}

		if (count($labToAddArray) > 0) {
			$studySubmitConnection->sql = "INSERT INTO `database name`.`study lab pairings` (StudyID, RoomID) VALUES ";
			foreach ($labToAddArray as $labToAdd) {
				$studySubmitConnection->sql .= "(" . $studyID . ", " . $labToAdd . "),";
			}
			$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ";";
			if ($studySubmitConnection->submit() == false) {
				redirection('Create Failure');
			}
		}

		$studySubmitConnection->sql = "INSERT INTO `database name`.`studyexperimenterpairs` (ExperimenterID, StudyID) VALUES ($professorID, $studyID)";
		if ($studySubmitConnection->submit() == false) {
			redirection('Study Professor Pairing Failure');
		}
		
		$studySubmitConnection->closeConnection();

		if ($checkForFailure == false) {
			redirection('Create Success');	
		}
	}

	elseif ($submitType == 'edit') {
		$studySubmitConnection->sql = "SELECT PersonUsername FROM `database name`.`study` AS Study INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = Study.CreatedBy WHERE StudyID = " . $studyID;
		$studySubmitResult = mysqli_query($studySubmitConnection->conn, $studySubmitConnection->sql);
		if (mysqli_num_rows($studySubmitResult) == 1) {
			$row = mysqli_fetch_assoc($studySubmitResult);
			$studyProfessor = $row['PersonUsername'];
		}

		if ($studyProfessor == $_SESSION['username']) {
			$studySubmitConnection->sql = "UPDATE `database name`.`study` SET StudyName = '$studyName', Password = '$password', Description = '$description', StartDate = '" . date_format($startDate, 'Y-m-d') . "', EndDate = '" . date_format($endDate, 'Y-m-d') . "', ExpectedPointValue = $points, ExpectedTimeInMinutes = $time, ConcurrentTesting = $concurrentTest WHERE StudyID = $studyID";
			if ($studySubmitConnection->submit() == false) {
				redirection('Edit Failure');
			}

			// adding classes to database
			if (count($classesToAddArray) > 0) {
				$studySubmitConnection->sql = "INSERT INTO `database name`.`class study pairings` (StudyID, ClassID) VALUES ";

				foreach ($classesToAddArray as $classToAdd) {
					$studySubmitConnection->sql .= "(" . $studyID . ", " . $classToAdd . "),";
				}
				$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ";";
				if ($studySubmitConnection->submit() == false) {
					redirection('Edit Failure');
				}
			}
			
			// removing unneeded classes
			if (count($classesToRemoveArray) > 0) {
				$studySubmitConnection->sql = "DELETE FROM `database name`.`class study pairings` WHERE StudyID = $studyID AND ClassID IN (";
				foreach ($classesToRemoveArray as $classToRemove) {
					$studySubmitConnection->sql .= $classToRemove . ",";
				}
				$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ");";
				if ($studySubmitConnection->submit() == false) {
					redirection('Edit Failure');
				}
			}
			
			// adding labs to database
			if (count($labToAddArray) > 0) {
				$studySubmitConnection->sql = "INSERT INTO `database name`.`study lab pairings` (StudyID, RoomID) VALUES ";
				foreach ($labToAddArray as $labToAdd) {
					$studySubmitConnection->sql .= "(" . $studyID . "," . $labToAdd . "),";
				}
				$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ";";
				if($studySubmitConnection->submit() == false) {
					redirection('Edit Failure');
				}
			}
			
			// removing unneeded labs
			if (count($labToRemoveArray) > 0) {

				$studySubmitConnection->sql = "DELETE FROM `database name`.`study lab pairings` WHERE StudyID = $studyID AND RoomID IN (";
				foreach ($labToRemoveArray as $labToRemove) {
					$studySubmitConnection->sql .= $labToRemove . ",";
				}
				$studySubmitConnection->sql = substr($studySubmitConnection->sql, 0, -1) . ");";
				if($studySubmitConnection->submit() == false) {
					redirection('Edit Failure');
				}

			}

			$studySubmitConnection->closeConnection();

			if ($checkForFailure == false) {
				redirection('Edit Success');	
			}
		}
		
	}
}


?>