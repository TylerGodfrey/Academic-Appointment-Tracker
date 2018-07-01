<?php

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

include ('general_connection.php');

$proctorVerification = false;

if (isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	$userConnection = new Connection();
	$userConnection->createConnection();
	$userConnection->sql="SELECT Proctor.PersonID FROM `person info` AS Proctor INNER JOIN `database name`.`studyexperimenterpairs` AS SEP ON SEP.ExperimenterID = Proctor.PersonID
		WHERE Proctor.PersonUsername = '$username'
		UNION
		SELECT Professor.PersonID FROM `person info` AS Professor INNER JOIN `database name`.`study` AS Study ON Professor.PersonID = Study.CreatedBy WHERE Professor.PersonUsername = '$username'";
	$userResult = mysqli_query($userConnection->conn, $userConnection->sql);
	if ($userResult != false) {
		if (mysqli_num_rows($userResult) > 0) {
			$proctorVerification = true;
		}
	}
}

echo "<div id='textbox' style='margin:5px'> <div style='float: left'>
	<a href='index.php'>Home</a>";
if (isset($_SESSION['username'])) {
	echo " - <a href='study_selection.php'>Study Selection</a> - <a href='existing_appointments.php'>Existing Appointments</a>";
}
if ($proctorVerification == true) {
	echo " - <a href='experiment_verification.php'>Verify Attendance</a>";
}
if (isset($_SESSION['userType']) && $_SESSION['userType'] == 'Professor') {
echo " - 
	<a href='study_creation.php'>Study Creation</a>
	 - <a href='professor_report.php'>Report</a>
	 - <a href='lab_reservation.php'>Lab Reservations</a>";
}

echo " </div> <div style='float: right'>";

if(!isset($_SESSION['username'])) {
	echo "<form action='user_verify.php' method='post' autocomplete='off'>
	<label>Username: </label>
	<input type='username' required autcomplete='off' name='username'>

	<label>Password: </label>
	<input type='password' required autocomplete='off' name='password'>

	<button class='button button-block' name='login'>Log In</button>
</form>";
}
elseif(isset($_SESSION['username'])) {
	echo "You are logged in as " . $_SESSION['username'] . "  ";
	echo "<button onclick=\"location.href='profile.php'\">View Profile</button> ";
	echo "<button onclick=\"location.href='log_out.php'\">Log Out</button>";
}
echo "</div> </div> <div style='clear: both;'></div>";
?>