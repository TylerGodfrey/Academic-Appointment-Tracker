<?php

session_start();

include ('general_connection.php');
	
	$error = false;
	
    if (isset($_POST['username']) && !empty($_POST['username'])) {
		$username = $_POST['username'];
	}
	else {
		echo "<script>alert('No username entered.'); window.setTimeout( function() { window.location='index.php' }, 500);";
		die();
	}
    if (isset($_POST['password']) && !empty($_POST['password'])) {
		$password = $_POST['password'];
	}
	else {
		echo "<script>alert('No password entered.'); window.setTimeout( function() { window.location='index.php' }, 500);";
		die();
	}
    $hostname = "ldap://ldap.server.name/";

    $con =  ldap_connect($hostname);
    if (!is_resource($con))
        die("Unable to connect to $hostname");
    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
	
    if (!ldap_bind($con, $username . "@student.email.domain", $password))
    {
        if (!ldap_bind($con, $username . "@staff.email.domain", $password)) {
			$error=True;
		}
    }
    ldap_close($con);

    if($error == false) {
		$userConnection = new Connection();
		$userConnection->createConnection();
		$userConnection->sql="SELECT PersonUserType FROM `database name`.`person info` WHERE PersonUsername = '$username'";
		$userResult = mysqli_query($userConnection->conn, $userConnection->sql);
		if ($userResult != false) {
			if (mysqli_num_rows($userResult) == 1) {
				$userType = mysqli_fetch_assoc($userResult)['PersonUserType'];
			}
		}
        $_SESSION['username'] = $username;
        $_SESSION['userType'] = $userType;
		echo "<script> alert('Login successful.'); window.setTimeout( function() { window.location='index.php'; }, 500); </script>";
    } 
	else {
        echo "<script> alert('Login failed.'); window.setTimeout( function () { window.location='index.php' }, 500); </script>";
		die();
    }


?>