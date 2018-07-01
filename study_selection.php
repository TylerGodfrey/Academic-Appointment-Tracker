<?php
session_start();
?>
<html>
<head>
<title>Study Selection</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
</head>
<body>

<?php

include ('links.php');

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $userConnection = new Connection();
    $userConnection->sql = "SELECT PersonUserType FROM `database name`.`person info` WHERE PersonUsername = '$username'";
    $userConnection->createConnection();
    $userResult = mysqli_query($userConnection->conn, $userConnection->sql);

    if (mysqli_num_rows($userResult) == 1) {
        $row = mysqli_fetch_assoc($userResult);

        $userType = $row['PersonUserType'];
    }

    $studyConnection = new Connection();
    $studyConnection->createConnection();
    $studyConnection->sql = "SELECT * FROM `study`";
    $result = mysqli_query($studyConnection->conn, $studyConnection->sql);

    $study_number = mysqli_num_rows($result);
    if ($study_number > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            $classesConnection = new Connection();

            $classesConnection->sql = "SELECT * FROM `class study pairings` AS CSP INNER JOIN `classes` AS Classes ON Classes.ClassID = CSP.ClassID WHERE StudyID ="  . $row['StudyID'] . ";";
            $classesConnection->createConnection();
            $classesResult = mysqli_query($classesConnection->conn, $classesConnection->sql);

            $class_concat = '';

            if (mysqli_num_rows($classesResult) > 0) {
                while($class_row = mysqli_fetch_assoc($classesResult)) {
                    $class_concat = $class_concat . "<br>" . $class_row['ClassName'] . ', ';
                }
                $class_concat = substr($class_concat, 0, -2);
            }
            else {
                $class_concat = "<br>None available.";
            }
            $classesConnection->closeConnection();

            // checking to see if the user has an existing appointment for the study
            $existingAppointment = false;
            $existingAppointmentConnection = new Connection();
            $existingAppointmentConnection->createConnection();
            $existingAppointmentConnection->sql="SELECT SubjectID FROM `database name`.`appointments` AS A
                                                    INNER JOIN `database name`.`person info` AS PI ON PI.PersonID = A.SubjectID
                                                WHERE PI.PersonUsername = '$username' AND StudyID = " . $row['StudyID'];
            $existingAppointmentResult = mysqli_query($existingAppointmentConnection->conn, $existingAppointmentConnection->sql);
            if (mysqli_num_rows($existingAppointmentResult) > 0) {
                $existingAppointment = true;
            }
            $existingAppointmentConnection->closeConnection();

            // checking if the user is a proctor for the study
            $proctorVerificationConnection = new Connection();
            $proctorVerificationConnection->createConnection();
            $proctorVerificationConnection->sql = "SELECT * FROM `database name`.`studyexperimenterpairs` AS SEP INNER JOIN `database name`.`person info` AS PI ON SEP.ExperimenterID = PI.PersonID WHERE SEP.StudyID = " . $row['StudyID'] . " AND PI.PersonUsername = '$username'";
            $proctorVerificationResult = mysqli_query($proctorVerificationConnection->conn, $proctorVerificationConnection->sql);

            if (mysqli_num_rows($proctorVerificationResult) == 1) {
                $proctorValidity = true; // the user is a valid proctor for the study
            }
            else {
                $proctorValidity = false; // the user is not a valid proctor for the study
            }
            $proctorVerificationConnection->closeConnection();



            // checking if the user is the creator of the study
            $professorVerificationConnection = new Connection();
            $professorVerificationConnection->createConnection();
            $professorVerificationConnection->sql = "SELECT * FROM `study` AS S INNER JOIN `person info` AS PI ON S.CreatedBy = PI.PersonID WHERE S.StudyID = " . $row['StudyID'] . " AND PI.PersonUsername = '$username'";
            $professorVerificationResult = mysqli_query($professorVerificationConnection->conn, $professorVerificationConnection->sql);

            if (mysqli_num_rows($professorVerificationResult) == 1) {
                $professorValidity = true; // the user is a valid professor for the study
            }
            else {
                $professorValidity = false; // the user is not a valid professor for the study
            }
            $professorVerificationConnection->closeConnection();
            
            echo "
                <table border='2' style='margin:5px'>
                <col width='200'>
                <col width='200'>

                <tr>
                <td colspan='2'>" . 
                $row['StudyName'];
            if (isset($username)) { // any user can create an appointment for any study
                echo "<form action='appointment_date_selection.php' method='POST'>
                    <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                    <input type='hidden' name='year' value=" . date_format(date_create(), 'Y') . ">
                    <input type='hidden' name='month' value=" . date_format(date_create(), 'm') . ">";
                    if ($existingAppointment == true) {
                        echo "<input type='hidden' name='submitType' value='Edit'>
                            <input type='submit' value='Edit Appointment'>";
                    }
                    else {
                        echo "<input type='hidden' name='submitType' value='Create'>
                            <input type='submit' value='Make An Appointment'>"; 
                    }
                    echo "</form>";   

            
                if ($proctorValidity == true || $professorValidity == true) { // both students and professors can proctor an experiment; both need to set availabilities and verify that subjects took part in the experiment
                    echo "<form action='proctor_availabilities.php' method='POST'>
                        <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                        <input type='hidden' name='submitType' value='Create'>
                        <input type='submit' value='Create Availabilities'>
                        </form>

                        <form action='proctor_availabilities.php' method='POST'>
                        <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                        <input type='hidden' name='submitType' value='Edit'>
                        <input type='submit' value='Edit Availabilities'>
                        </form>

                        <form action='experiment_verification.php' method='POST'>
                        <input type='submit' value='Verify Attendance'>
                        </form>";
                }
                else {
                    echo "<form action='proctor_login.php' method='POST'>
                        <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                        <input type='hidden' name='studyName' value=". $row['StudyName'] . ">
                        <input type='submit' value='Login to Study'>
                        </form>";
                }

                if ($professorValidity == true) { // only professors can edit or delete their studies
                    echo "<form action='delete_study.php' method='POST'>
                        <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                        <input type='submit' value='Delete Study'>
                        </form>

                        <form action='study_creation.php' method='POST'>
                        <input type='hidden' name='studyID' value=" . $row['StudyID'] . ">
                        <input type='submit' value='Edit Study'>
                        </form>";
                }
            }
            
            echo "</td> </tr>
            <tr>
                <td colspan='2'>" .
                $row['Description'] .
                "</td>
            </tr>
            <tr>
                <td>Points Available:<br>" .
                $row['ExpectedPointValue'] .
                " points</td>
                <td>Expected Duration:<br>" .
                $row['ExpectedTimeInMinutes'] . 
                " minutes</td>
            </tr>
            <tr>
                <td colspan='2'>
                Eligible Classes: " .
                $class_concat .
                "
                </td>
            </tr>
            </table>";

        }
    } else {
        echo "0 results";
    }

    $studyConnection->closeConnection();


}
else {
    echo "<p align='center'><b>Attention: </b>You must be logged in to sign up for an appointment.</p>";
}


?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>