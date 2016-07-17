<?php
/**************************************************************
* Hostmon - device-backend.php
* Author - Isaac Assegai
* Used by device.php to get information for all the graphs
* and charts, as well as looking for new notes on the page.
* Returns to an ajax script allowing updating of page without reload.
**************************************************************/

include_once("functions.php");
include_once("db.php");
$postResult = "";
$con = openDB(); //we know we are going to be querying from the db even if nothing was posted.
$dbOptions = getDBOptions();
	
//decide which line graph should be displayed and pull the appropriate data from db
if(isset($_POST['LineChart'])){
	$table = '';
	if($_POST['LineChart']=="FiveMinuteLine"){
		$table = "minute";
	}else if($_POST['LineChart']=="HourLine"){
		$table = "hour";
	}else if($_POST['LineChart']=="DayLine"){
		$table = "day";
	}else if($_POST['LineChart']=="WeekLine"){
		$table = "week";
	}else if($_POST['LineChart']=="YearLine"){
		$table = "year";
	}
	mysqli_select_db($con,$dbOptions["DB"]);
	$sql="SELECT time, latency FROM `".$table."` WHERE ip = '".$_POST['ip']."'";
	$result = mysqli_query($con,$sql);
		
	while($row = mysqli_fetch_array($result)) {
		$postResult = $postResult."".$row['time'].":".$row['latency']." ";
	}
} // End if LineChart

//decide which polor graph to use, pull the data from db and append to results
if(isset($_POST['PolarChart'])){
	$table = '';
	if($_POST['PolarChart']=="FiveMinutePolar"){
		$table = "minute";
	}else if($_POST['PolarChart']=="HourPolar"){
		$table = "hour";
	}else if($_POST['PolarChart']=="DayPolar"){
		$table = "day";
	}else if($_POST['PolarChart']=="WeekPolar"){
		$table = "week";
	}else if($_POST['PolarChart']=="YearPolar"){
		$table = "year";
	}
	$latencyList = Array(); //the structure we are reading latency results to.
	mysqli_select_db($con,$dbOptions["DB"]);
	$sql="SELECT latency FROM `".$table."` WHERE ip = '".$_POST['ip']."'";
	$result2 = mysqli_query($con,$sql);
	//Record the latency results to the latency list.
	while($row2 = mysqli_fetch_array($result2)) {
		array_push($latencyList, $row2['latency']);
	}
	//we need to know the min and max of items in the list.
	$minimum = min($latencyList);
	$maximum = max($latencyList);
	$range = $maximum - $minimum; //the range of items in the list
	$num = count($latencyList); // the number of items in the list.
	$itemLimit = $range / 5; // The number of items in the polar chart.
	//calculate the limit for each item in the polar chart.
	$item1Limit = $minimum+$itemLimit;
	$item2Limit = $minimum+$itemLimit*2;
	$item3Limit = $minimum+$itemLimit*3;
	$item4Limit = $minimum+$itemLimit*4;
	$item5Limit = $minimum+$itemLimit*5;
	$item1Array = Array();
	$item2Array = Array();
	$item3Array = Array();
	$item4Array = Array();
	$item5Array = Array();

	//push each item to it's respective section of the polar chart.
	for($i = 0; $i < $num; $i++){
		if($latencyList[$i] <= $item1Limit){
			array_push($item1Array, $latencyList[$i]);
		}else if($latencyList[$i] <= $item2Limit){
			array_push($item2Array, $latencyList[$i]);
		}else if($latencyList[$i] <= $item3Limit){
			array_push($item3Array, $latencyList[$i]);
		}else if($latencyList[$i] <= $item4Limit){
			array_push($item4Array, $latencyList[$i]);
		}else if($latencyList[$i] <= $item5Limit){
			array_push($item5Array, $latencyList[$i]);
		}
	}
		
	//Build the result of the page to return to the front end for parsing.
	$postResult = $postResult."-".$item1Limit.":".count($item1Array)." ".
	$item2Limit.":".count($item2Array)." ". 
	$item3Limit.":".count($item3Array)." ".
	$item4Limit.":".count($item4Array)." ".
	$item5Limit.":".count($item5Array);
} // End ifset PolarChart
	
/* If SubmitNote has been posted, we want to get the note info from the post, submit a new note to
 * The database. Then we want to read and parse the notes into a new note section that jquery
 * will replace on the frontend. */ 	 
if(isset($_POST['SubmitNote'])){
	$deviceID = $_POST['deviceID'];
	$time = $_POST['time'];
	$noteName = $_POST['noteName'];
	$noteContent = $_POST['noteContent'];
	$userID = getUserID($noteName);
/*
	//mysqli_real_escape_string
	$sanitizedContent = mysqli_real_escape_string($con, $noteContent);
	//submit note to database
	$resultList = Array(); //the structure we are reading latency results to.
	mysqli_select_db($con,"hostmon");
	$sql="INSERT INTO `hostmon`.`notes` (`id` ,`deviceID` ,`userID` ,`timestamp` ,`content`) VALUES (NULL , '".$deviceID."', '".$userID."', '".$time."', '".$sanitizedContent."');";
	$result2 = mysqli_query($con,$sql);
*/
//	error_log("submitting note to db ".$deviceID." ".$userID." ".$time." ".$noteContent);
	error_log("about to submitting note");
	submitNote($deviceID, $userID, $time, $noteContent);
	//Record the latency results to the latency list.	
    $notes = getNotes($deviceID); //db query get array of notes from device id.
	$postResult = buildNotesGrid($notes);	
} // End ifset SubmitNote
	
//user decides to remove a specific note, referenced by it's timestamp and deviceId
if(isset($_POST['RemoveNote'])){
	$deviceID = $_POST['deviceID'];
	$timestamp = $_POST['timestamp'];
	
	mysqli_select_db($con,$dbOptions["DB"]);
	$sql='DELETE FROM `.$dbOptions["DB"].`.`notes` WHERE `notes`.`timestamp` = '.$timestamp.' AND `notes`.`deviceID` = '.$deviceID.';';
	$result2 = mysqli_query($con,$sql);
	
	$notes = getNotes($deviceID); //db query get array of notes from device id.
	$postResult = buildNotesGrid($notes);
} // End if iset RemoveNote
echo $postResult; //sends result to front end.
?>
