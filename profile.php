<html>
<head>
	<title>Profile</title>
</head>
<body>

<?php

function currentValueSelector($optionString, $valueInDatabase) {
	$stringToFind = "<option value='$valueInDatabase'>";
	$locationForEditing = strpos($optionString, $stringToFind) + strlen($stringToFind) - 1;
	$newOptionString = substr_replace($optionString, ' selected', $locationForEditing, 0);
	return $newOptionString;
}

include ('links.php');

$username = $_SESSION['username'];

$profileConnection = new Connection();

$profileConnection->createConnection();
$profileConnection->sql = "SELECT * FROM `database name`.`person info` WHERE PersonUsername = '$username'";
$profileResult = mysqli_query($profileConnection->conn, $profileConnection->sql);

$carrierArray = array();

$carrierConnection = new Connection();

$carrierConnection->createConnection();
$carrierConnection->sql = "SELECT * FROM `database name`.`cellphone carriers`";

$carrierResult = mysqli_query($carrierConnection->conn, $carrierConnection->sql);

if (mysqli_num_rows($carrierResult) > 0) {
	while ($row = mysqli_fetch_assoc($carrierResult)) {
		array_push($carrierArray, array($row['CarrierID'], $row['CarrierName']));
	}
}

echo "<p align='center'>";

if (mysqli_num_rows($profileResult) == 1) {
	$row = mysqli_fetch_assoc($profileResult);

	echo "<form action='submit_profile_info.php' method='post' align='center'>
	Username: " . $_SESSION['username'] . "<br>
	First Name: <input type='text' name='firstName' value='" . $row['PersonFirstName'] . "'> <br>
	Last Name: <input type='text' name='lastName' value='" . $row['PersonLastName'] . "'> <br>
	Preferred Contact Method: <select name='preferredContactMethod'>";

	if ($row['PrefContactMethod'] == 'Phone') {
		echo "<option value='Phone' selected>Phone</option> <option value='E-mail'>E-mail</option>";
	}
	elseif ($row['PrefContactMethod'] == 'E-mail') {
		echo "<option value='Phone'>Phone</option> <option value='E-mail' selected>E-mail</option>";
	}
	echo "</select> <br>
	Phone Number: <input type='text' name='phone' value='" . $row['PersonPhoneNumber'] . "'> <br>
	Cellphone Carrier: <select name='carrierID'> <option value=''></option>";
	foreach ($carrierArray as $carrier) {
		$carrierString = $carrierString . "<option value='" . $carrier[0] . "'>" . $carrier[1] . "</option>";
	}
	$carrierString = currentValueSelector($carrierString, $row['PersonCarrier']);
	echo $carrierString;
	echo "</select> <br>
	Email Address: <input type='text' name='email' value='" . $row['PersonEmail'] . "'> <br>
	Account Type: " . $row['PersonUserType'] . " <br><br>
	<input type='hidden' name='submissionType' value='edit'>
	<input type='submit'></input>
	</form>";
}
elseif (mysqli_num_rows($profileResult) > 1) {
	echo "There seems to be an issue with the data; please contact an administrator for assistance.";
}
else {
	echo "It seems you haven't setup your profile yet.  Please fill in the forms below.
	<form action='submit_profile_info.php' method='post' align='center'>
	Username: " . $_SESSION['username'] . "<br>
	First Name: <input type='text' name='firstName'> <br>
	Last Name: <input type='text' name='lastName'> <br>
	Preferred Contact Method: <select name='preferredContactMethod'> <option value='Phone'>Phone</option> <option value='E-mail'>E-mail</option> </select> <br>
	Phone Number: <input type='text' name='phone'> <br>
	Cellphone Carrier: <select name='carrierID'> <option value=''></option>";
	foreach ($carrierArray as $carrier) {
		echo "<option value='" . $carrier[0] . "'>" . $carrier[1] . "</option>";
	}
	echo "</select> <br>
	Email Address: <input type='text' name='email'> <br> <br>
	<input type='hidden' name='submissionType' value='create'>
	<input type='submit'></input>
	</form>";
}
echo "</p>";

?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>