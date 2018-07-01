<html>
<head>
	<title>Existing Appointments</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/existing_appointments.css">
</head>
<body>

<?php

include ('links.php');

$username = $_SESSION['username'];

$appointmentConnection = new Connection();

$appointmentConnection->createConnection();
$appointmentConnection->sql = "SELECT Exp.PersonFirstName, Exp.PersonLastName, Exp.PersonID AS 'ProcID', Sub.PersonID AS 'SubID', StudyName, Date, RoomID, StartTime, EndTime, ClassName, A.StudyID FROM `database name`.`appointments` AS A
		INNER JOIN `database name`.`person info` AS Exp
		 ON A.ExperimenterID = Exp.PersonID 
		INNER JOIN `database name`.`study` AS S
		 ON S.StudyID = A.StudyID 
		LEFT OUTER JOIN `database name`.`classes` AS C
		 ON C.ClassID = A.ClassRequested
		INNER JOIN `database name`.`person info` AS Sub
		 ON Sub.PersonID = A.SubjectID
		WHERE Sub.PersonUsername = '$username'
		";
$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);

if (mysqli_num_rows($appointmentResult) > 0) {
	echo "Appointments You Will Be Taking Part In:<table> <th> Proctor Name </th> <th> Study Name </th> <th> Date </th> <th> Lab Number </th> <th> Start Time </th> <th> End Time </th> <th> Class For Credit </th>";
	while ($row = mysqli_fetch_assoc($appointmentResult)) {
		$proctorName = $row['PersonFirstName'] . " " . $row['PersonLastName'];
		$studyName = $row['StudyName'];
		$date = date_format(date_create($row['Date']), 'M d, Y');
		$labNumber = $row['RoomID'];
		$startTime = date_format(date_create($row['StartTime']), 'h:i A');
		$endTime = date_format(date_create($row['EndTime']), 'h:i A');
		$classForCredit = $row['ClassName'];
		$studyID = $row['StudyID'];
		$subjectID = $row['SubID'];
		$proctorID = $row['ProcID'];
		echo "<tr>
			<td> $proctorName </td> <td> $studyName </td> <td> $date </td> <td> $labNumber </td> <td> $startTime </td> <td> $endTime </td> <td> $classForCredit </td> <td class='buttonHolder'> 
			<form action='appointment_date_selection.php' method='post'>
			<input type='hidden' name='studyID' value='$studyID'>
			<input type='hidden' name='year' value=" . date_format(date_create(), 'Y') . ">
            <input type='hidden' name='month' value=" . date_format(date_create(), 'm') . "> 
            <input type='hidden' name='submitType' value='Edit'>
            <input type='submit' value='Edit' class='button'> </form> </td>
            <td class='buttonHolder'> <form action='appointment_delete.php' method='post'>
            <input type='hidden' name='studyID' value='$studyID'>
            <input type='hidden' name='relation' value='Subject'>
			<input type='hidden' name='subjectID' value='$subjectID'>
			<input type='hidden' name='proctorID' value='$proctorID'>
			<input type='hidden' name='date' value='$date'>
            <input type='submit' value='Delete' class='button'> </form>
            </td> </tr>";
	}
	echo "</table> <br>";
}

$appointmentConnection->sql = "SELECT Sub.PersonID AS 'SubID', Exp.PersonID AS 'ProcID', Sub.PersonFirstName, Sub.PersonLastName, StudyName, Date, RoomID, StartTime, EndTime, ClassName, A.StudyID FROM `database name`.`appointments` AS A
		INNER JOIN `database name`.`person info` AS Exp 
		 ON A.ExperimenterID = Exp.PersonID 
		INNER JOIN `database name`.`study` AS S
		 ON S.StudyID = A.StudyID 
		LEFT OUTER JOIN `database name`.`classes` AS C
		 ON C.ClassID = A.ClassRequested
		INNER JOIN `database name`.`person info` AS Sub
		 ON Sub.PersonID = A.SubjectID
		WHERE Exp.PersonUsername = '$username'";
$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);

if (mysqli_num_rows($appointmentResult) > 0) {
	echo "Appointments You Will Be Proctoring:<table> <th> Subject Name </th> <th> Study Name </th> <th> Date </th> <th> Lab Number </th> <th> Start Time </th> <th> End Time </th> <th> Class For Credit </th>";
	while ($row = mysqli_fetch_assoc($appointmentResult)) {
		$subjectName = $row['PersonFirstName'] . " " . $row['PersonLastName'];
		$studyName = $row['StudyName'];
		$date = date_format(date_create($row['Date']), 'M d, Y');
		$labNumber = $row['RoomID'];
		$startTime = date_format(date_create($row['StartTime']), 'h:i A');
		$endTime = date_format(date_create($row['EndTime']), 'h:i A');
		$classForCredit = $row['ClassName'];
		$studyID = $row['StudyID'];
		$subjectID = $row['SubID'];
		$proctorID = $row['ProcID'];
		echo "<tr>
			<td> $subjectName </td> <td> $studyName </td> <td> $date </td> <td> $labNumber </td> <td> $startTime </td> <td> $endTime </td> <td> $classForCredit </td> 
            <td class='buttonHolder'> <form action='appointment_delete.php' method='post'>
            <input type='hidden' name='studyID' value='$studyID'>
            <input type='hidden' name='subjectID' value='$subjectID'>
			<input type='hidden' name='proctorID' value='$proctorID'>
            <input type='hidden' name='relation' value='Proctor'>
			<input type='hidden' name='date' value='$date'>
            <input type='submit' value='Delete' class='button'> </form>
            </td> </tr>";
	}
	echo "</table> <br>";
}

$appointmentConnection->sql = "SELECT Sub.PersonID, Sub.PersonFirstName, Sub.PersonLastName, StudyName, Date, RoomID, StartTime, EndTime, ClassName, A.StudyID, Exp.PersonID AS 'ProctorID' FROM `database name`.`appointments` AS A
		INNER JOIN `database name`.`person info` AS Exp 
		 ON A.ExperimenterID = Exp.PersonID 
		INNER JOIN `database name`.`study` AS S
		 ON S.StudyID = A.StudyID 
		INNER JOIN `database name`.`person info` AS Prof
		 ON Prof.PersonID = S.CreatedBy
		LEFT OUTER JOIN `database name`.`classes` AS C
		 ON C.ClassID = A.ClassRequested
		INNER JOIN `database name`.`person info` AS Sub
		 ON Sub.PersonID = A.SubjectID
		WHERE Prof.PersonUsername = '$username'";
$appointmentResult = mysqli_query($appointmentConnection->conn, $appointmentConnection->sql);

if (mysqli_num_rows($appointmentResult) > 0) {
	$proctorConnection = new Connection();
	$proctorConnection->createConnection();
	echo "Appointments For Your Studies:<table> <tr> <th> Proctor Name </th> <th> Subject Name </th> <th> Study Name </th> <th> Date </th> <th> Lab Number </th> <th> Start Time </th> <th> End Time </th> <th> Class For Credit </th> </tr>";
	while ($row = mysqli_fetch_assoc($appointmentResult)) {
		$subjectName = $row['PersonFirstName'] . " " . $row['PersonLastName'];
		$studyName = $row['StudyName'];
		$date = date_format(date_create($row['Date']), 'M d, Y');
		$labNumber = $row['RoomID'];
		$startTime = date_format(date_create($row['StartTime']), 'h:i A');
		$endTime = date_format(date_create($row['EndTime']), 'h:i A');
		$classForCredit = $row['ClassName'];
		$studyID = $row['StudyID'];
		$subjectID = $row['PersonID'];
		$proctorID = $row['ProctorID'];

		$proctorDropdown = null;
		$proctorConnection->sql = "SELECT PersonID AS 'ID', PersonFirstName, PersonLastName FROM `database name`.`person info` WHERE PersonID IN (SELECT ExperimenterID FROM `database name`.`studyexperimenterpairs` WHERE StudyID = $studyID);";
		$proctorResult = mysqli_query($proctorConnection->conn, $proctorConnection->sql);
		if ($proctorResult != false) {
			if (mysqli_num_rows($proctorResult) > 0) {
				$proctorDropdown = "<select name='proctorID'>";
				while ($row = mysqli_fetch_assoc($proctorResult)) {
					$selectID = $row['ID'];
					$selectName = $row['PersonFirstName'] . ' ' . $row['PersonLastName'];
					$proctorDropdown .= "<option value='$selectID'";
					if ($selectID == $proctorID) {
						$proctorDropdown .= " selected='selected'";
					}
					$proctorDropdown .= ">$selectName</option>";
				}
				$proctorDropdown .= "</select>";
			}
		}
		
		echo "<tr>
			<form action='appointment_swap_proctors.php' method='post'>
			<td> $proctorDropdown </td> <td> $subjectName </td> <td> $studyName </td> <td> $date </td> <td> $labNumber </td> <td> $startTime </td> <td> $endTime </td> <td> $classForCredit </td> 
			<td class='buttonHolder'>
			<input type='hidden' name='studyID' value='$studyID'>
			<input type='hidden' name='subjectID' value='$subjectID'> 
			<input type='submit' value='Submit' class='button'> </form>
            </tr>
            ";
	}
	echo "</table>";
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>