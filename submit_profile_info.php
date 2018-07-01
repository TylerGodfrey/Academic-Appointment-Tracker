<?php
session_start();

include ('general_connection.php');

function phoneFix ($phoneInput) {
    $numberString = preg_replace("/[^0-9]/", "", $phoneInput); // removing non-number characters makes validation easier and protects from SQL injection
    $digitCount = strlen($numberString);
    if ($digitCount >= 10) { // phone numbers are at least 10 digits long; sending phone numbers internationally often involves more than 10 digits
    	return $numberString;
    }
    else {
    	return false;
    }
}

$profileConnection = new Connection();

$profileConnection->createConnection(); // creating connection now to fix data for the database when we set the variables to be inserted into the SQL statement

$valueArray = array();

if (isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	$username = mysqli_real_escape_string($profileConnection->conn, $username);
	array_push($valueArray, array('PersonUsername', $username));
}

if (isset($_POST['firstName'])) {
	$firstName = $_POST['firstName'];
	$firstName = mysqli_real_escape_string($profileConnection->conn, $firstName);
	array_push($valueArray, array('PersonFirstName', $firstName));
}

if (isset($_POST['lastName'])) {
	$lastName = $_POST['lastName'];
	$lastName = mysqli_real_escape_string($profileConnection->conn, $lastName);
	array_push($valueArray, array('PersonLastName', $lastName));
}

if (isset($_POST['preferredContactMethod'])) {
	$preferredContactMethod = $_POST['preferredContactMethod'];
	array_push($valueArray, array('PrefContactMethod', $preferredContactMethod));
}

if (isset($_POST['phone']) && $_POST['phone'] != '') {
	$phone = $_POST['phone'];
	$phone = phoneFix($phone); // phoneFix removes all non-number characters and ensures that the phone number entered is of sufficient length 
	if (empty($phone)) {
		echo "<script>alert('Invalid phone number. Please try again.'); window.setTimeout( function() { window.location='profile.php' }, 500);</script>";
  		die();
	}
}
else {
	$phone = null;
}
array_push($valueArray, array('PersonPhoneNumber', $phone));

if (isset($_POST['carrierID']) && $_POST['carrierID'] != '') {
	$carrierID = $_POST['carrierID']; // carrier ID is built into the carrier selection.  Only those values can be put in, so the input does not need to be sanitized.
}
else {
	$carrierID = null;
}
array_push($valueArray, array('PersonCarrier', $carrierID));

if (isset($_POST['email']) && $_POST['email'] != '') {
	$email = $_POST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // give error message and redirect back to profile page if invalid email was put in
		$email = false;
		echo "<script>alert('Invalid email.');</script>";
	}
	else {
		$email = mysqli_real_escape_string($profileConnection->conn, $email);
	}
}
else {
	$email = null;
}

array_push($valueArray, array('PersonEmail', $email));

if (isset($_POST['submissionType'])) {
	$submissionType = $_POST['submissionType'];
}

if (($preferredContactMethod == 'Phone' && empty($phone)) || ($preferredContactMethod == 'E-mail' && empty($email))) {
		echo "<script> alert('The chosen contact method has an invalid input. Please try again.'); window.setTimeout( function () { window.location='profile.php' }, 500);";
		die();
}


if ($submissionType == 'create') {
	$profileConnection->sql = "INSERT INTO `database name`.`person info` (PersonUsername, PersonFirstName, PersonLastName, PrefContactMethod, PersonPhoneNumber, PersonCarrier, PersonEmail, PersonUserType) VALUES (";
	foreach($valueArray as $array) {
		if (is_int($array[1])) {
			$profileConnection->sql .= $array[1] . ',';
		}
		elseif (empty($array[1]) || $array[1] == false) {
			$profileConnection->sql .= 'NULL,';
		}
		elseif (is_string($array[1])) {
			$profileConnection->sql .= "'" . $array[1] . "'" . ',';
		}
	}
	$profileConnection->sql .= "'General');";
	if ($profileConnection->submit() == true) {
		echo "<script type='text/javascript'>alert('You have successfully created your profile!');</script>";	
	}
	
}
elseif ($submissionType == 'edit') {
	$profileConnection->sql = "UPDATE `database name`.`person info` SET ";
	foreach ($valueArray as $array) {
		if (is_int($array[1])) {
			$profileConnection->sql .= $array[0] . '=' . $array[1] . ',';
		}
		elseif ($array[1] == false) {
			echo "<script>alert('One of the values you put in is not valid.')";
		}
		elseif (empty($array[1])) {
			$profileConnection->sql .= $array[0] . '= NULL,';
		}
		elseif (is_string($array[1])) {
			$profileConnection->sql .= $array[0] . '=' . "'" . $array[1] . "'" . ',';
		}
	}
	$profileConnection->sql = substr($profileConnection->sql, 0, -1) . " WHERE PersonUsername = '$username'"; // removes ending comma from the string due to the loop; adds username filter so that only the applicable record is changed.
	if ($profileConnection->submit() == true) {
		echo "<script type='text/javascript'>alert('You have successfully edited your profile!');</script>";			
	}

}
$profileConnection->closeConnection();

echo "<script> window.setTimeout(function() { window.location='profile.php'; }, 500); </script>"; // avoids issues with redirecting immediately

?>
