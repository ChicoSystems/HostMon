<?php 
/**************************************************************
* Hostmon - menu-backend.php
* Author - Isaac Assegai
* This script is used by the menu ajax calls
* to query the database and return all configuration data.
* It is also used to set individual pieces of configuration
* data via the config set buttons on the front end.
**************************************************************/
include_once("db.php");
include_once("functions.php");
session_start(); //We'll end up needing to check for admin status.
if(isset($_POST['getConfigData'])){ // Front end wants ALL configuration data. Any lvl can do this.
	$con = openDB();
	$dbOptions = getDBOptions();
	mysqli_select_db($con,$dbOptions['DB']);
	$sql = "SELECT * FROM configuration";
	$result = mysqli_query($con,$sql);
	$config = array();
	$id = array();
	$row = array();;
	while($row = mysqli_fetch_array($result)) {
		foreach($row as  $key => $value){
			if(!((string)(int)$key == $key)){
				$id[$key] = $value;
			}
		}
		array_push($config, $id);
	}
}else if(isset($_POST['setConfigValue'])){
	if($_SESSION['admin_level'] == '10'){ // Make sure user is admin before changing config values
		$con = openDB();
		$dbOptions = getDBOptions();
		mysqli_select_db($con,$dbOptions['DB']);
		$name = $_POST['name'];
		$value = $_POST['value'];
		$value = trim($value); // Problem with whitespace in value.
		$sanitizedValue = mysqli_real_escape_string($con, $value); // Discourage some hackers.
		$sanitizedValue = trim($sanitizedValue); // Get rid of leading and ending whitespace.
		$sql = "UPDATE `".$dbOptions['DB']."`.`configuration` SET `value` = '".$sanitizedValue."' WHERE `configuration`.`name` = '".$name."';";
		$result2 = mysqli_query($con,$sql);
		// Pack up the name and value in a json object and send it back to the frontend.
		$config = array();
		$config['name'] = $name;
		$config['value'] = $value;
	}
}else if(isset($_POST['changePassword'])){
	$con = openDB();
	$dbOptions = getDBOptions();
	mysqli_select_db($con,$dbOptions['DB']);
	$userName = $_SESSION['usr']; // User is only setting his own password.
	$newPass = $_POST['newPass'];
	$userName = mysqli_real_escape_string($con, $userName); // Discourage some hackers.
	$newPass = mysqli_real_escape_string($con, $newPass); // Discourage some hackers.
	$sql = "UPDATE `".$dbOptions['DB']."`.`users` SET `pass` = '".$newPass."' WHERE `users`.`usr` = '".$userName."';";
	$result2 = mysqli_query($con,$sql);
	$config = array();
		$config['returnVal'] = "Password Changed.";
}else if(isset($_POST['addUser'])){
	if($_SESSION['admin_level'] == '10'){ // Make sure user is admin before changing config values
		$newUsername = $_POST['newUserName'];
		$newPassword = $_POST['newPassword'];
		$newAdminLvl = $_POST['newAdminLvl'];
		
		$con = openDB();
		$dbOptions = getDBOptions();
		mysqli_select_db($con,$dbOptions['DB']);
		$config = array();
		
		$newUsername = mysqli_real_escape_string($con, $newUsername); // Discourage some hackers.
		$newPassword = mysqli_real_escape_string($con, $newPassword); // Discourage some hackers.
		$newAdminLvl = mysqli_real_escape_string($con, $newAdminLvl); // Discourage some hackers.
		
		if(userExists($newUsername, $con)){
			$config['returnVal'] = "User Exists";
		}else{
			$sql = "INSERT INTO `hostmon`.`users` (`usr`, `admin_level`, `pass`) VALUES ('".$newUsername."', '".$newAdminLvl."', '".$newPassword."');";
			$result = mysqli_query($con,$sql);
			$config['returnVal'] = "Added User ".$newUsername;
		}
	}
}else if(isset($_POST['removeUser'])){ // Admin clicked remove user.
	if($_SESSION['admin_level'] == '10'){ // Make sure user is admin before changing config values
		$removeUsername = $_POST['removeUsername'];
		$con = openDB();
		$dbOptions = getDBOptions();
		mysqli_select_db($con,$dbOptions['DB']);
		$config = array();
		$removeUsername = mysqli_real_escape_string($con, $removeUsername); // Discourage some hackers.
		$sql = "DELETE FROM `".$dbOptions['DB']."`.`users` WHERE `users`.`usr` = '".$removeUsername."'";
		$result = mysqli_query($con,$sql);
		$config['returnVal'] = "Removed ".$removeUsername;
	}
}else if(isset($_POST['startStopBackend'])){ // User wants to stop, or start the java backend
	if($_SESSION['admin_level'] == '10'){ // Make sure user is admin before touching backend
		$config = array();
		$startOrStop = $_POST['startStopBackend'];
		if($startOrStop == 'start'){
			//start the backend.
			startBackend();
			$config['returnVal'] = "Started the Backend";
			$config['newButtonVal'] = "STOP";
		}else{
			//stop the backend.
			stopBackend(); // Tells the db that the backend is stopped, it will stop it.
			$config['returnVal'] = "Stopped the Backend";
			$config['newButtonVal'] = "START";
		}
	}
}else if(isset($_POST['getBackendRunning'])){
	$config = array();
	if($_SESSION['admin_level'] == '10'){ // Make sure user is admin before touching backend
		$config['success'] = true;
		(backendRunning() ? $config['backendStatus'] = 'backendRunning' : $config['backendStatus'] = 'backendStopped'); //fancy if
	}else{
		$config['success'] = false;
		$config['err'] = "You are not an admin, why are you seeing this?";
	}
}

$jencodeddata = json_encode($config);
echo $jencodeddata;

/** Returns true if $userName exists in db represented by $con
  * @param unknown $userName The Username we are checking.
  * @param unknown $con The db connection we are checking.*/
function userExists($userName, $con){
	$sql = "SELECT * FROM `users` WHERE `usr` = '".$userName."'";
	$result = mysqli_query($con,$sql);
	$array_result = array();
	while($row = mysqli_fetch_array($result)) {
		array_push($array_result, $row);
	}
	if(empty($array_result)){
		return false;
	}else{
		return true;
	}
}
?>
