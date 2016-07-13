<?php
/**************************************************************
* Hostmon - grid-backend.php
* Author - Isaac Assegai
* Used by grid.php to pull down all graph data from 
* The database. Returns info back to ajax call.
**************************************************************/
//error_reporting(-1);
include_once("functions.php");
include_once("db.php");
$postResult = "";

/** User is trying to add a new device to the system. */

if(isset($_POST['removeDevice'])){
	$postResult = removeDevice($_POST['removeDevice']); //removeDevice is the ID of the device

}else if(isset($_POST['addNewDevice'])){ //This function is not completely done yet.
	//$postResult = $postResult.$_POST['addNewDevice'];
	/*first we check the db to see if a device of this ip has already been added
	if it has, then we pull the info for that device from the db,
		, then we make sure the device is in/added to the active devices list in db.
		, then we create a render of that device and send it to the frontend.
	if it has not been added, then create a new device record with the ip, name, and initial note.
		, then we make sure the new device is in active devices list in db.
		, then we create a render of that device and send it to the frontend.
	*/
	$ip = $_POST['deviceIP'];
	$name = $_POST['deviceName'];
	$note = $_POST['deviceNote'];
	$userName = $_POST['userName'];
	$userID = getUserID($userName);
	$timestamp = round(microtime(true) * 1000);
	error_log("USR: ".$_SESSION['usr']);
	// Demo Code
	//$ip = "chicosystems.com";
	//$name = "Chico Systems";
	//$note = "This is chico systems.";
		
	if(deviceExists($ip)){ // Is the device already in the system.		
		$postResult = " DeviceExists |";
		$id = getDeviceID($ip);
		makeDeviceActive($id);
		submitNote($id, $userID, $timestamp, $note);
		$postResult = $postResult.renderDevice($id, $ip, $name);
	}else{
		$postResult = " AddedDevice | display";
		addNewDevice($ip, $name, $note);
		$id = getDeviceID($ip);
		makeDeviceActive($id);
		
		submitNote($id, $userID, $timestamp, $note);
		$postResult = $postResult.renderDevice($id, $ip, $name);
	}
}else if(isset($_POST['getGridGraphData'])){
	$ip = $_POST['ip'];
    $timeRange = $_POST['timeRange'];
	$data = getTenAveragePointsInTimeRange($timeRange);
	$postResult = $data;
}else if(isset($_POST['getBackendRunning'])){
	$postResult = array();
	$postResult['success'] = true;
	(backendRunning() ? $postResult['backendStatus'] = 'backendRunning' : $postResult['backendStatus'] = 'backendStopped'); //fancy if
	$postResult = json_encode($postResult);
}
	
/**gets the last $timeRange of pings from the database
   gives us 10 even spaced averages. */
function getTenAveragePointsInTimeRange($timeRange){
	$averagePoints = "";
	if($timeRange == "fiveMinute"){
		//$averagePoints = "100 100 200 200 400 300 400 400 500 500 1000 90 800 70 600 50 400 30 200 10";
		$averagePoints = getFiveMinuteAverage($_POST['ip']);
	}else if($timeRange == "hour"){
		//$averagePoints = "1000 90 800 70 600 50 400 30 200 10 100 100 200 200 400 300 400 400 500 500";
		$averagePoints = getHourAverage($_POST['ip']);
	}
	return $averagePoints;
}

/** Gets the last $limit of records from the minute table. */
function getFiveMinuteAverage($ip){
	
	$limit = 31; // Config Value, how many data points show on each graph, in grid.php
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT * FROM minute WHERE ip = '".$ip."' ORDER BY time DESC LIMIT ".$limit;
	$result = mysqli_query($con,$sql);
	$answer = '';
	
	while($row = mysqli_fetch_array($result)) {
		$answer = $answer.$row['latency']." ";
	}
	
	return $answer;
}
	
/** Gets the last $limit records from the hour table. */
function getHourAverage($ip){
	$limit = 21;
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT * FROM hour WHERE ip = '".$ip."' ORDER BY time DESC LIMIT ".$limit;
	$result = mysqli_query($con,$sql);
	$answer = '';
	while($row = mysqli_fetch_array($result)) {
		$answer = $answer.$row['latency']." ";
	}
	return $answer;
}

/** Build the info to display a device. This isn't complete yet, using demo info. */
function renderDevice($id, $ip, $name){
	$returnVal ='	
	<li href="device.php?ip='.$ip.'" id="first" rel="#overlay" data-row="5" data-col="8" data-sizex="1" data-sizey="1" onclick="loadDevice(\'0\');">
       	<img src="images/up-arrow.png" class="grow"><img src="images/down-arrow.png" class="shrink">
		<div class="device_record">
               <h1>'.$name.'</h1><h2>'.$ip.'</h2><h3>Xms</h3><canvas class="can1"></canvas><div id="statusmark"></div>
		</div>
	</li>';	
	return $returnVal;
}

/** Lets us know if a device exists in the db, not implemented yet. */	
function deviceExists($ip){
	return false;	
}
// The info returned back to the ajax script.
echo $postResult;
?>
