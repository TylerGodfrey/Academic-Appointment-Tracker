<?php
session_start();
$username = $_SESSION['username'];
?>

<html>
<head>
<title>Lab Reservations</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/lab_reservation.css">
</head>
<body>

<?php

include ('links.php');

if (isset($_POST['submitType'])) {
	$submitType = $_POST['submitType'];
}


echo "<p align='center'>You may enter the dates and times in which you will need access to labs.<br><strong>Do not enter a time that overlaps with an existing reservation for the chosen lab.</strong> </p>";	

include ('calendar_class.php');

class reservationCalendar extends Calendar {

	private $monthNames = Array("January", "February", "March", "April", "May", "June", "July", 
"August", "September", "October", "November", "December");

	function createTopOfCalendar ($studyID, $submitType) {

		$studyID = null;
		if (!isset($_POST["month"])) $_POST["month"] = date("n");
		if (!isset($_POST["year"])) $_POST["year"] = date("Y");

		$this->cMonth = $_POST["month"];
		$this->cYear = $_POST["year"];
		 
		$prev_year = $this->cYear;
		$next_year = $this->cYear;
		$prev_month = $this->cMonth-1;
		$next_month = $this->cMonth+1;
		 
		if ($prev_month == 0 ) {
		    $prev_month = 12;
		    $prev_year = $this->cYear - 1;
		}
		if ($next_month == 13 ) {
		    $next_month = 1;
		    $next_year = $this->cYear + 1;
		}

		echo "

		<table width='1300' id='reservationCalendarTable'>
		<tr align='center'>
		<td bgcolor='#999999' style='color:#FFFFFF'>
		<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>

		<td width='50%' align='left'> <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>
			<input type='hidden' name='month' value='" . $prev_month . "'>
			<input type='hidden' name='year' value='" . $prev_year . "'>
			<input type='hidden' name='submitType' value='" . $submitType . "'>
			<input type='submit' value='Prev'>
			</form> </td>	
		

		<td width='50%' align='right'> <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>
			<input type='hidden' name='month' value='" . $next_month . "'>
			<input type='hidden' name='year' value='" . $next_year . "'>
			<input type='hidden' name='submitType' value='" . $submitType . "'>
			<input type='submit' value='Next'>
			</form> </td>	

		</tr>
		</table>
		</td>
		</tr>
		<tr>
		<td align='center'>
		<table width='100%' border='1' cellpadding='2' cellspacing='2'>
		<tr align='center'>
		<td colspan='7' bgcolor='#999999' style='color:#FFFFFF'><strong>" . $this->monthNames[$this->cMonth-1].' '.$this->cYear . "</strong></td>
		</tr>
		<tr>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Sunday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Monday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Tuesday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Wednesday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Thursday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Friday</strong></td>
		<td align='center' bgcolor='#999999' style='color:#FFFFFF'><strong>Saturday</strong></td>
		</tr>
		";
	}


	function createBottomOfCalendar ($studyID, $submitType) {

	$studyID = null;
	$submitType = $submitType;
	$timestamp = mktime(0,0,0,$this->cMonth,1,$this->cYear);
	$maxday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	$startday = $thismonth['wday'];

	$utcTimes = Array(
	'0:00',		'0:15',		'0:30',		'0:45',
	'1:00',		'1:15',		'1:30',		'1:45',
	'2:00',		'2:15',		'2:30',		'2:45',
	'3:00',		'3:15',		'3:30',		'3:45',
	'4:00',		'4:15',		'4:30',		'4:45',
	'5:00',		'5:15',		'5:30',		'5:45',
	'6:00',		'6:15',		'6:30',		'6:45',
	'7:00',		'7:15',		'7:30',		'7:45',
	'8:00',		'8:15',		'8:30',		'8:45',
	'9:00',		'9:15',		'9:30',		'9:45',
	'10:00',	'10:15',	'10:30',	'10:45',
	'11:00',	'11:15',	'11:30',	'11:45',
	'12:00',	'12:15',	'12:30',	'12:45',
	'13:00',	'13:15',	'13:30',	'13:45',
	'14:00',	'14:15',	'14:30',	'14:45',
	'15:00',	'15:15',	'15:30',	'15:45',
	'16:00',	'16:15',	'16:30',	'16:45',
	'17:00',	'17:15',	'17:30',	'17:45',
	'18:00',	'18:15',	'18:30',	'18:45',
	'19:00',	'19:15',	'19:30',	'19:45',
	'20:00',	'20:15',	'20:30',	'20:45',
	'21:00',	'21:15',	'21:30',	'21:45',
	'22:00',	'22:15',	'22:30',	'22:45',
	'23:00',	'23:15',	'23:30',	'23:45'
    );

    $labs = array(2, 3, 4, 5, 6, 7);

	if (isset($_SESSION['username'])) {
		$username = $_SESSION['username'];
	}
    $professorValidity = false;
    if (!empty($username)) {
		$userConnection = new Connection();
		$userConnection->createConnection();
		$userConnection->sql="SELECT PersonID, PersonUserType FROM `database name`.`person info` WHERE PersonUsername = '$username'";
		$userResult = mysqli_query($userConnection->conn, $userConnection->sql);
		if ($userResult != false) {
			if (mysqli_num_rows($userResult) == 1) {
				$row = mysqli_fetch_assoc($userResult);
				if ($row['PersonUserType'] == 'Professor') {
					$professorValidity = true;	
					$userID = $row['PersonID'];		
				}
			}
		}
		$userConnection->closeConnection();
	}
	if ($professorValidity == false) {
		echo "<script>alert('Validation failed. Re-routing.'); window.setTimeout( function () { window.location='index.php' }, 500); </script>";
	}

	$reservationConnection = new Connection();
	$reservationConnection->createConnection();
	$reservationConnection->sql="SELECT Date, ReservationID, LabID, ProfessorID, StartTime, EndTime FROM `database name`.`reservation` WHERE MONTH(Date) = '" . strval($this->cMonth) . "' AND YEAR(Date) = '" . strval($this->cYear) . "'";
	$reservationResult = mysqli_query($reservationConnection->conn, $reservationConnection->sql);
	$reservationArray = array();
	if ($reservationResult != false) {
		if (mysqli_num_rows($reservationResult) > 0) {
			while ($row = mysqli_fetch_assoc($reservationResult)) {
				array_push($reservationArray, array($row['Date'], $row['ReservationID'], $row['LabID'], $row['ProfessorID'], date_format(date_create($row['StartTime']), 'g:i A'), date_format(date_create($row['EndTime']), 'g:i A')));
			}
		}
	}

	echo "<form action='lab_reservation_calendar_submit.php' method='post'>
		<input type='hidden' name='month' value='" . $this->cMonth . "'>
		<input type='hidden' name='year' value='" . $this->cYear . "'>";

	for ($i=0; $i<(ceil(($maxday+$startday)/7)*7); $i++) {
	    if(($i % 7) == 0 ) echo "<tr>";
	    if($i < $startday || ($i - $startday + 1) > cal_days_in_month(CAL_GREGORIAN, $this->cMonth, $this->cYear)) echo "<td></td>";
	    else {
	    	$date = date_create(strval($this->cYear) . '-' . strval($this->cMonth) . '-' . strval($i - $startday + 1));
	    	echo "<td align='left' valign='top' width='110px' height='110px'>". ($i - $startday + 1) . "<br>";
	    	foreach ($reservationArray as $reservation) {
	    		if (date_create($reservation[0]) == $date) {
	    			echo "Lab " . $reservation[2] . " is reserved for " . $reservation[4] . "-" . $reservation[5] . ".<br>";
	   	    		if ($reservation[3] == $userID ) {
		    			echo "<input type='checkbox' name='editReservation" . $reservation[1] . "' value='" . $reservation[2] . "," . $reservation[3] . "'>Edit";
		    			echo "<input type='checkbox' name='deleteReservation" . $reservation[1] . "' value='" . $reservation[2] . "," . $reservation[3] . "'>Delete<br>";
		    		}
	    		}
	    	}
	    	$labSelect = "Lab: <br><select name='labNumber" . strval($i - $startday + 1) . "'> <option value=''>";
	    	foreach ($labs as $lab) {
	    		$labSelect .= "<option value='$lab'>Lab $lab</option>";
	    	}
	    	$labSelect .= "</select><br>";
	    	$timeSelect = "Start Time: <br><select name='startTime" . strval($i - $startday + 1) . "'> <option value=''></option>";
	    	foreach ($utcTimes as $time) {
	    		$timeSelect .= "<option value='$time'>" . date_format(date_create($time), 'g:i A') . "</option>";
	    	}
	    	$timeSelect .= "</select> <br>End Time: <br><select name='endTime" . strval($i - $startday + 1) . "'> <option value=''></option>";
	    	foreach ($utcTimes as $time) {
	    		$timeSelect .= "<option value='$time'>" . date_format(date_create($time), 'g:i A') . "</option>";
	    	}
	    	$timeSelect .= "</select>";
	    	echo $labSelect;
	    	echo $timeSelect;
	    	echo "</td>";
	    }
	    if(($i % 7) == 6 ) echo "</tr>";
	}

	echo "
	<tr><td colspan='7'><input type='submit'></input></td></tr>
	</table>
	</td>
	</tr>
	</table>
	</form>
	";
	}
}
echo "<div align='center'>";
$reservationCalendar = new reservationCalendar();
$reservationCalendar->createTopOfCalendar(null, null);
$reservationCalendar->createBottomOfCalendar(null, null);	
echo "</div>";


$utcTimes = Array(
	'0:00',		'0:15',		'0:30',		'0:45',
	'1:00',		'1:15',		'1:30',		'1:45',
	'2:00',		'2:15',		'2:30',		'2:45',
	'3:00',		'3:15',		'3:30',		'3:45',
	'4:00',		'4:15',		'4:30',		'4:45',
	'5:00',		'5:15',		'5:30',		'5:45',
	'6:00',		'6:15',		'6:30',		'6:45',
	'7:00',		'7:15',		'7:30',		'7:45',
	'8:00',		'8:15',		'8:30',		'8:45',
	'9:00',		'9:15',		'9:30',		'9:45',
	'10:00',	'10:15',	'10:30',	'10:45',
	'11:00',	'11:15',	'11:30',	'11:45',
	'12:00',	'12:15',	'12:30',	'12:45',
	'13:00',	'13:15',	'13:30',	'13:45',
	'14:00',	'14:15',	'14:30',	'14:45',
	'15:00',	'15:15',	'15:30',	'15:45',
	'16:00',	'16:15',	'16:30',	'16:45',
	'17:00',	'17:15',	'17:30',	'17:45',
	'18:00',	'18:15',	'18:30',	'18:45',
	'19:00',	'19:15',	'19:30',	'19:45',
	'20:00',	'20:15',	'20:30',	'20:45',
	'21:00',	'21:15',	'21:30',	'21:45',
	'22:00',	'22:15',	'22:30',	'22:45',
	'23:00',	'23:15',	'23:30',	'23:45',
	'24:00'
    );

$labs = array(2,3,4,5,6,7);


$reservationSeriesArray = array();
$seriesReservationsConnection = new Connection();
$seriesReservationsConnection->createConnection();
$seriesReservationsConnection->sql="SELECT RS.SeriesID, RS.SeriesDescription FROM `database name`.`reservation series` AS RS WHERE SeriesID IN (SELECT SRP.SeriesID FROM `database name`.`reservation` AS Res INNER JOIN `database name`.`series reservation pairings` AS SRP ON SRP.ReservationID = Res.ReservationID INNER JOIN `database name`.`person info` AS Prof ON Prof.PersonID = Res.ProfessorID WHERE Prof.PersonUsername = '$username')";
$seriesReservationsResult = mysqli_query($seriesReservationsConnection->conn, $seriesReservationsConnection->sql);
if ($seriesReservationsResult != false) {
	if (mysqli_num_rows($seriesReservationsResult) > 0) {
		while ($row = mysqli_fetch_assoc($seriesReservationsResult)) {
			array_push($reservationSeriesArray, array($row['SeriesID'], $row['SeriesDescription']));	
		}
	}
}

echo "<br><br><div align='center'>";
echo "<form action='lab_reservation_series_submit.php' method='post'>";
echo "<table width='1300' id='reservationSeriesTable'>
<col width='185'>
<col width='185'>
<col width='185'>
<col width='185'>
<col width='185'>
<col width='185'>
<col width='190'>
<th colspan='7'>New Series</th>
<tr>";
$days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
foreach ($days as $day) {
	echo "<td align='center'> <input type='checkbox' name='$day'>$day </td>";
}
echo "</tr>";
echo "<tr><td>Lab: <select name='lab'> <option value=''></option>";
foreach ($labs as $lab) {
	echo "<option value='$lab'>Lab $lab</option>";
}
echo "</select></td>";
echo "<td colspan='3'>Start Date: <input type='date' name='startDate' min='2017-01-01'>  End Date: <input type='date' name='endDate' min='2017-01-01'></td>";
echo "<td colspan='3'>Start Time: <select name='startTime'> <option value=''></option>";
	
foreach ($utcTimes as $time) {
	$timeInternal = date_format(date_create($time), 'H:i');
	$timeExternal = date_format(date_create($time), 'g:i A');
	echo "<option value='$timeInternal'>$timeExternal</option>";
}
echo "</select>  End Time: <select name='endTime'> <option value=''></option>";

foreach ($utcTimes as $time) {
	$timeInternal = date_format(date_create($time), 'H:i');
	$timeExternal = date_format(date_create($time), 'g:i A');
	echo "<option value='$timeInternal'>$timeExternal</option>";
}
echo "</select></td></tr>";
echo "<td colspan='5'>Description: <input type='text' size='90' name='description'> </td>";
echo "<td colspan='2'><input type='submit' value='Submit Series of Reservations'></td></tr>";
if (count($reservationSeriesArray) > 0) {
	echo "<tr><th colspan='7'>Existing Series</th></tr>";
	foreach ($reservationSeriesArray as $series) {
		foreach ($days as $day) {
			echo "<td align='center'> <input type='checkbox' name='$day" . $series[0] . "'>$day</td>";
		}
		echo "<tr>";
		echo "<td>Lab: <select name='lab" . $series[0] . "'> <option value=''></option>";
		foreach ($labs as $lab) {
			echo "<option value='$lab'>Lab $lab</option>";
		}
		echo "</select></td>";
		echo "<td colspan='3'>Start Date: <input type='date' name='startDate" . $series[0] . "' min='2017-01-01'>  End Date: <input type='date' name='endDate" . $series[0] . "' min='2017-01-01'></td>";
		echo "<td colspan='3'>Start Time: <select name='startTime" . $series[0] . "'> <option value=''></option>";
		
		foreach ($utcTimes as $time) {
			$timeInternal = date_format(date_create($time), 'H:i');
			$timeExternal = date_format(date_create($time), 'g:i A');
			echo "<option value='$timeInternal'>$timeExternal</option>";
		}
		echo "</select>  End Time: <select name='endTime" . $series[0] . "'> <option value=''></option>";

		foreach ($utcTimes as $time) {
			$timeInternal = date_format(date_create($time), 'H:i');
			$timeExternal = date_format(date_create($time), 'g:i A');
			echo "<option value='$timeInternal'>$timeExternal</option>";
		}
		echo "</select></td></tr>";
		echo "<td colspan='3'>Description: <input type='text' size='50' name='description" . $series[0] . "' value='" . $series[1] . "'> </td>";
		echo "<td><button type='button' onclick='getSeriesReservations(" . $series[0] . ")'>View Series Reservations</button></td>";
		echo "<td colspan='3'>";
		echo "<input type='radio' name='change" . $series[0] . "' value='delete'>Delete Series";
		echo "<br><input type='radio' name='change" . $series[0] . "' value='remove'>Remove Reservations";
		echo "<input type='radio' name='change" . $series[0] . "' value='edit'>Edit Reservations";
		echo "<input type='radio' name='change" . $series[0] . "' value='add'>Add Reservations";
		echo "</td></tr>";
	}
}
echo"</table></form>";

?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/lab_reservation_get_series.js"></script>

</body>
</html>