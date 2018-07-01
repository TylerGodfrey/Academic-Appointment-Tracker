<?php

include ('general_connection.php');

if (isset($_POST['seriesID'])) {
	$seriesID = $_POST['seriesID'];
}
else {
	$seriesID = null;
}

$getSeriesConnection = new Connection();
$getSeriesConnection->createConnection();
$getSeriesConnection->sql="SELECT LabID, Date, StartTime, EndTime FROM `database name`.`reservation` AS Res WHERE ReservationID IN (SELECT ReservationID FROM `database name`.`series reservation pairings` AS SRP WHERE SRP.SeriesID = $seriesID)";
$getSeriesResult = mysqli_query($getSeriesConnection->conn, $getSeriesConnection->sql);
if ($getSeriesResult != false) {
	if (mysqli_num_rows($getSeriesResult) > 0) {
		// string lengths are necessary for alert formatting
		$labIDStringLength = 11;
		$dateStringLength = 24;
		$startTimeStringLength = 30;
		// end time string length is irrelevant
		$alertString = "Lab ID     Date                    Start Time                    End Time\n";
		while ($row = mysqli_fetch_assoc($getSeriesResult)) {
			$labID = $row['LabID'] . "              ";
			$date = date_format(date_create($row['Date']), 'M d, Y') . "       ";
			$startTime = date_format(date_create($row['StartTime']), 'g:i A');
			if (strlen($startTime) == 7) {
				$startTime .= "                       ";
			}
			elseif (strlen($startTime) == 8) {
				$startTime .= "                     ";
			}

			$endTime = date_format(date_create($row['EndTime']), 'g:i A');
			$alertString .= " " . $labID . $date . $startTime . $endTime . "\n";
		}
		echo $alertString;
	}
}
$getSeriesConnection->closeConnection();

?>