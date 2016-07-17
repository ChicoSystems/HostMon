<?php
/**************************************************************
* Hostmon - db.php
* Author - Isaac Assegai
* This library interfaces with the db, several times
* throughout the app, we skip this altogether.
**************************************************************/

/** Create and return the DB connection. */	
function openDB(){
	$dbOptions = getDBOptions();
	//$con = mysqli_connect($dbOptions["IP"], $dbOptions["USER"], $dbOptions["PASS"], $dbOptions["DB"]);
	$con = new mysqli($dbOptions["IP"], $dbOptions["USER"], $dbOptions["PASS"]);
	if (!$con) {
		return false;
		//echo getCWD();
		//die(' Could not connect to db: ' . mysqli_error($con));
	}
	mysqli_select_db($con,$dbOptions["DB"]);
	return $con;
}

/** Send A Query to The DB, looking for a single result. */
function queryDB($con, $sql){
	$dbOptions = getDBOptions();
	mysqli_select_db($con,$dbOptions["DB"]);
	$result = mysqli_query($con,$sql);
	$row = mysqli_fetch_array($result);
	return $row;
}

/** Get the options to connect to the DB from the config file. */
function getDBOptions(){
	$dbOptions = array();
	if (strpos(getCWD(), 'php') !== FALSE || strpos(getCWD(), 'install') !== FALSE){
		$lines = file('../cfg/db.cfg');
	}else{
		$lines = file('cfg/db.cfg');
	}
	//parse through the config line finding the settings and adding them to the return array.	
	foreach ($lines as $line_num => $line) {
		if (strpos($line, 'DB:') !== FALSE){
			$end = strpos($line, ';');
			$start = strpos($line, ':')+1;
			$length = $end - $start;
			$dbName = substr($line, $start, $length);
			$dbOptions["DB"] = $dbName;
		}else if (strpos($line, 'USER:') !== FALSE){
			$end = strpos($line, ';');
			$start = strpos($line, ':')+1;
			$length = $end - $start;
			$user = substr($line, $start, $length);
			$dbOptions["USER"] = $user;
		}else if (strpos($line, 'PASS:') !== FALSE){
			$end = strpos($line, ';');
			$start = strpos($line, ':')+1;
			$length = $end - $start;
			$pass = substr($line, $start, $length);
			$dbOptions["PASS"] = $pass;
		}else if (strpos($line, 'IP:') !== FALSE){
			$end = strpos($line, ';');
			$start = strpos($line, ':')+1;
			$length = $end - $start;
			$ip = substr($line, $start, $length);
			$dbOptions["IP"] = $ip;
		}else{
			//any other lines including blanks will be skipped
		}
	}
	return $dbOptions;
}
?>
