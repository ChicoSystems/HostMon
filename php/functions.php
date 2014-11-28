<?php
/**************************************************************
* Hostmon - functions.php
* Author - Isaac Assegai
* Supplies functions that are used by several scripts.
**************************************************************/
/** Current Version of the app gets stored in
  * cfg/version.txt. We need to read and parse this file.   
  */
function getCurrentVersion(){
	$returnVal = "V 0.0.0";
	$myfile = fopen("cfg/version.txt", "r") or die("Unable to open version file!");
	$returnVal = fread($myfile,filesize("cfg/version.txt"));
	fclose($myfile);
	$returnVal = "V ".$returnVal;
	return $returnVal;
}

/** Checks if we are on linux or windows and starts the backend accordingly*/
function startBackend(){
	$javaDir = getJavaDir();
	$backendDir = getBackendDir();
	$cmd = '"'.$javaDir.'java" -Djava.awt.headless=true -cp ..\\backend\\ 2>&1 TestJava';
	$result = array();
	exec($cmd, $result);
	$hello = "hello";
}

/** Returns the installed backend dir. */
function getBackendDir(){
	$backendDir = 'C:\\xampp\\htdocs\\backend\\';
	return $backendDir;
}

/** Returns the installed java directory so we don't have to do classpaths. */
function getJavaDir(){
	$javaDir = 'C:\\Program Files\\Java\\jdk1.7.0_17\\bin\\';
	return $javaDir;
}

/** Sets backendRunning value in configuration table to false.
 *  The backend will check this and stop itself.
 */
function stopBackend(){
	$con = openDB();
	mysqli_select_db($con,"HostMon");
	$sql = "UPDATE `configuration` SET `configuration`.`value` = 'false' WHERE `configuration`.`name` = 'backendRunning'";
	$result = mysqli_query($con,$sql);
}

/** Queries the db to see if the java backend is running. */
function backendRunning(){
	$returnVal = false;
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql = "SELECT * FROM `configuration` WHERE `configuration`.`name` = 'backendRunning'";
	$result = mysqli_query($con,$sql);
	$array_result = array();
	while($row = mysqli_fetch_array($result)) {
		array_push($array_result, $row);
	}
	if(isset($array_result[0]['name'])){ // Value was retrieved from db.
		$s_isRunning = $array_result[0]['value'];
		$s_lastRanTime = $array_result[0]['timeStamp'];
		if($s_isRunning == 'true'){ // The Db says the backend is running.
			$currentTime =  time();
			if($currentTime - $s_lastRanTime < 60){ // last time it updated was less then 60 secs.
				$returnVal = true;
			}else{
				$returnVal = false;
			}
		}else{
			$returnVal = false;
		}
	}else{
		$returnVal = false;
	}
	return $returnVal;
}

function isInstalledAlready(){
	$returnVal = false;
	$result = false;
	$array_result = array();
	$con = openDB();
	$dbOptions = getDBOptions();mysqli_select_db($con, $dbOptions["DB"]);
	$sql = "SELECT * FROM `configuration` WHERE `configuration`.`name` = 'installed';";
	$result = mysqli_query($con,$sql);
	while($row = mysqli_fetch_array($result)) {
		array_push($array_result, $row);
	}
	if($array_result[0]['value']=='1'){
		$returnVal = true;
	}
	return $returnVal;
}

/** Echo's the menu  */
function Menu(){
	$menu = '
	<!-- This is the Menu that is handled in Javascript -->
		<nav class="left">
			<ul>
				<li style="height: 90%; font-color="white"; class="config_list">		
			';
	
	
	$menu = $menu.'<h4 style="right:150px;"
						title="Set a new password for your account."
					>Change Password</h4>
					<input text="NEW PASS" type="password" class="changePassword1" style="display:inline; width:90px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);"><br>
					<input type="password" class="changePassword2" style="display:inline; width:90px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="changePassword()">SET</button><br>
					<h5 class="errorOutput" title="Shows the user an error message if change password is bad.">
					-</h5>
				
			';
	if($_SESSION['admin_level'] == '10'){
		(backendRunning() ? $class = 'backendRunning' : $class = 'backendStopped'); //fancy if
		$menu = $menu.'
					<h4 style="right:150px;"
						id="stopStartLabel"
						title="Allows the admin to stop or start the java backend."
					>Start Backend</h4>
					<button id ="stopStartButton"
							class = "'.$class.'" 
							onClick="stopStartBackend();"
						>START</button><br>
					<h5 class="startBackendErrorOutput" 
						title="Shows the user an error message if change password is bad.">
					-</h5>
		';
		$hello = "hello";
	}
	
	if($_SESSION['admin_level'] == '10'){
		$menu = $menu.'
					<h4 style="right:150px;"
						title="Allows the admin to add a new user account."
					>New User</h4>
					<input type="text" class="newUserName" style="display:inline; width:90px;"
							onClick="$(this).val(\'\'); setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);"><br>
					<h4 style="right:150px;">Pass</h4>
					<input type="text" class="newUserPass" style="display:inline; width:90px;"
							onClick="$(this).val(\'\'); setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);"><br>
					<h4 style="right:150px;"
						title="LVL 0: Unapproved Account, LVL 1: User Account, LVL 10: Admin Account">Admin LVL</h4>
					<input type="text" class="adminLvl" style="display:inline; width:90px;"
							onClick="$(this).val(\'\'); setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="addNewUser();">SET</button><br>
					<h5 class="newUserErrorOutput" title="Shows the user an error message if change password is bad.">
					-</h5>
					
					<h4 style="right:150px;"
						title="Allows the admin to remove a user by name."
					>Remove User</h4>
					<input type="text" class="removeUsername" style="display:inline; width:90px;"
							onClick="$(this).val(\'\'); setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="removeUser();">SET</button><br>
					<h5 class="removeUserErrorOutput" title="Shows the user an error message if change password is bad.">
					-</h5>
				
					<h4 style="right:112px;"
						title="The time, in milliseconds, that we are aiming to have each record updated in. This will have an effect on the number of threads running in backend."
					>Avg. Goal Time (ms) </h4>
					<input type="text" class="averageGoalTime" style="display:inline; width:42px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'averageGoalTime\')">SET</button><br>
					
					<h4 title="The number of threads the backend starts with. The thread number will change as the backend runs."
					>Starting Threads (ms)</h4>
					<input type="text" class="startingThreads" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'startingThreads\');">SET</button><br>
					
					<h4 title="The maximum number of threads the backend will be able to run."
					>Max Threads </h4>
					<input type="text" class="maxThreads" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'maxThreads\');">SET</button><br>
					
					<h4 title="The Value that decides when threads are removed. The Lower the value the sooner an unneeded thread is removed."
					>T. Removal Co-eff.</h4>
					<input type="text" class="threadRemovalCoefficient" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'threadRemovalCoefficient\');">SET</button><br>
					
					<h4 title="The Value that decides when threads are added. The Higher the value the sooner a needed thread is added."
					>T. Add Co-Eff.</h4>
					<input type="text" class="threadAddCoefficient" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'threadAddCoefficient\');">SET</button><br>
					
					<h4 title="Every x amount of times a thread is run we check if we need to add or remove a thread."
					>Run / Thread Check</h4>
					<input type="text" class="runPerThreadCheck" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'runPerThreadCheck\');">SET</button><br>
					
					<h4 title="The number of times we will ping before we make a call to the database."
					># Pings B4 DB</h4>
					<input type="text" class="numPingRunsBeforeDBRecord" style="display:inline;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'numPingRunsBeforeDBRecord\');">SET</button><br>
					
					<h4 style="right:112px;"
						title="The age each record in the minute table should get before being deleted. In Milliseconds."
					>Minute Age Limit </h4>
					<input type="text" class="minuteRecordAgeLimit" style="display:inline; width:50px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'minuteRecordAgeLimit\');">SET</button><br>
					
					<h4 style="right:132px;"
						title="The age each record in the hour table should get before being deleted. In Milliseconds."
					>Hour Age Limit</h4>
					<input type="text" class="hourRecordAgeLimit" style="display:inline; width:70px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'hourRecordAgeLimit\');">SET</button><br>
					
					<h4 style="right:140px;"
						title="The age each record in the day table should get before being deleted. In Milliseconds."
					>Day Age Limit</h4>
					<input type="text" class="dayRecordAgeLimit" style="display:inline; width:80px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'dayRecordAgeLimit\');">SET</button><br>
					
					<h4 style="right:150px;"
						title="The age each record in the week table should get before being deleted. In Milliseconds."
					>Week Age Limit</h4>
					<input type="text" class="weekRecordAgeLimit" style="display:inline; width:90px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'weekRecordAgeLimit\');">SET</button><br>
					
					<h4 style="right:112px;"
						title="The amount of milliseconds we want to retrieve to average out new pings to add to hour table default was 5 minutes or 300000"
					>New Ping Minutes</h4>
					<input type="text" class="newestPingMinutes" style="display:inline; width:50px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'newestPingMinutes\');">SET</button><br>
					
					<h4 style="right:132px;"
						title="The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 hour or 3600000 millis"
					>New Ping Hours</h4>
					<input type="text" class="newestPingHours" style="display:inline; width:70px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'newestPingHours\');">SET</button><br>
					
					<h4 style="right:140px;"
						title="The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 day or 86400000 millis"
					>New Ping Days</h4>
					<input type="text" class="newestPingDays" style="display:inline; width:80px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'newestPingDays\');">SET</button><br>
					
					<h4 style="right:150px;"
						title="The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 week or 604800000 millis"
					>New Ping Weeks</h4>
					<input type="text" class="newestPingWeeks" style="display:inline; width:90px;"
							onClick="setMenuInputFocusIn(this);"
							onfocus="setMenuInputFocusIn(this);" onblur="setMenuInputFocusOut(this);"
							infocusin="setMenuInputFocusIn(this);" onfocusout="setMenuInputFocusOut(this);">
					<button onClick="setConfigValue(\'newestPingWeeks\');">SET</button><br>
				';
	}
	$menu = $menu .'
			</li>
        		<li style="height: 10%;"><a href="login.php?logout=true">Logout</a></li>
			</ul>
		</nav>';
	return $menu;
}
/** Builds the opening tags for the notes grid section. */
function buildNotesOpening(){
	$returnVal = "
		<li data-row='1' data-col='7' data-sizex='4' data-sizey='3' id='actions'>    
			<!-- root element for scrollable -->
			<div class='scrollable vertical'>
				<!-- root element for the scrollable elements -->
				<div class='items' id='accordion3'>
					<!-- first element. contains three rows -->";
	return $returnVal;	
}

/** Builds the closing tags for the notes grid section. */
function buildNotesClosing(){
	$returnVal = "
				</div>
			</div>
			<img src='images/up-arrow.png' class='prev'></img>
			<img src='images/down-arrow.png' class='next'></img>
			<img class='plus' src='images/plus.png'></img>
		</li>";
	return $returnVal;	
}

/** Builds the individual note items, only 3 per individual div to keep scrolling working. */
function buildNoteItems($notes){
	$returnVal = "";
	$i = 0;
	$current = "";
	$display = "";
	$divHeader = "<div>";
	$divFooter = "</div>";
	$returnVal = $returnVal.$divHeader;
	$j = count($notes);
	foreach (array_reverse($notes) as $note){
		if($i == 0){ // decides which note is the currently displaying one.
			$current = "current";	
			$display = "block";
		}else{
			$current = "";
			$display = "none";	
		}
		if($i % 3 == 0 && $i != 0){//insert a div seperator every 3 notes, but not on the first one.
			$divMedium = "</div><div>";
		}else{
			$divMedium = "";
		}
		$returnVal = $returnVal."
			<h2 class='item ".$current."'>
				<div id='notenum'>".($j)."</div>
				<div id='notename'>".$note['username']."</div>
				<div id='notedate'>".$note['date']."</div>
				<div id='notetime'>".$note['time']."</div>
				<img class='minus' src='images/minus.png'></img>
			</h2>
			<div class='pane' style='display:".$display."'>".$note['content']."</div>
			<div style='display:none'>".$note['timestamp']."</div>".$divMedium;	
		$i++;
		$j--;
	}
	$returnVal = $returnVal.$divFooter;
	return $returnVal;	
}

/** Fetches the Device ID from the database, uses the ip. */
function getDeviceID($ip){
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT id FROM `devices` WHERE ip = '".$ip."'";
	$result = mysqli_query($con,$sql);
	$id = '';
	while($row = mysqli_fetch_array($result)) {
		$id = $row['id'];
	}
	return $id;	
}

/** Query's the database for the devices name, using the ID. */
function getDeviceName($deviceID){
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT name FROM `devices` WHERE id = '".$deviceID."'";
	$result = mysqli_query($con,$sql);
	$name = '';
	while($row = mysqli_fetch_array($result)) {
		$name = $row['name'];
	}
	return $name;
}

/** Query's the database for a users name, from their ID. */
function getUserName($id){
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT usr FROM `users` WHERE id = '".$id."'";
	$result = mysqli_query($con,$sql);
	$name = '';
	while($row = mysqli_fetch_array($result)) {
		$name = $row['usr'];
	}
	return $name;
}

/** Returns a formatted date, based on a timestamp in millis. */
function getFormattedDate($timestamp){
	$returnVal = date("M d Y", ($timestamp/1000));
	return $returnVal;	
}
	
/** Returns a formatted time, based on a timestamp in millis. */
function getFormattedTime($timestamp){
	$returnVal = date("g i A", ($timestamp/1000));
	return $returnVal;	
}

/** Build the Notes section grid. */
function buildNotesGrid($notes){
	$notesOpening = buildNotesOpening();
	$noteItems = buildNoteItems($notes);
	$noteClosing = buildNotesClosing();
	$returnVal = $notesOpening.$noteItems.$noteClosing;
	return $returnVal;
}

/** Returns an Array of notes from the DB, based on deviceID. */
function getNotes($deviceID){
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT * FROM `notes` WHERE deviceID = '".$deviceID."'";
	$result = mysqli_query($con,$sql);
	$returnArray = Array();
	while($row = mysqli_fetch_array($result)) {
		array_push($returnArray, $row);
	}
	$notes = Array();
	foreach($returnArray as $item){
		$note = array("username" => getUserName($item['userID']), 
					  "date" => getFormattedDate($item['timestamp']), 
					  "time" => getFormattedTime($item['timestamp']), 
					  "timestamp" => $item['timestamp'],
					  "content" => $item['content']);
		array_push($notes, $note);
	}
	return $notes;	
}

/* Will Return an array of active devices for the specified username. */
function getActiveDevices($username){
	$con = openDB();
	mysqli_select_db($con,"hostmon");
	$sql="SELECT * FROM `active_devices`";
	$result = mysqli_query($con,$sql);
	$activeDeviceNumbers = Array(); // a 2d array where the first d is different devices, and the 2nd d has the number at 0
	$activeDevices = Array();
	while($row = mysqli_fetch_array($result)) {
		array_push($activeDeviceNumbers, $row);
	}
	
	for($i = 0; $i < count($activeDeviceNumbers); $i++){  //go through each active device number, build an activeDevice array with the name and ip
		$sql="SELECT * FROM `devices` WHERE id = '".$activeDeviceNumbers[$i][0]."'";
		$result = mysqli_query($con,$sql);
		$dev = Array();
		while($row = mysqli_fetch_array($result)) {
			array_push($dev, $row);
		}
		//echo " ".$dev[$i]["name"]." ";
		//echo print_r($dev)." <br><br>";
		//add items here
		if(!empty($dev)){
			$array = array( // will represent a device in grid.php
				"name" => $dev[0]["name"],
				"ip" => $dev[0]["ip"]
			);
		array_push($activeDevices, $array); // push this device to the list of active Devices.
		}
	}
	return $activeDevices;
}

/* gets the correct array of grid positions, based on how many grids there are. */
function getGridPositions($numGrids){
	$gridPositions = Array();
	if($numGrids <= 6){
		array_push($gridPositions, array("yp" => 1,"xp" => 1,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 1,"xp" => 5,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 9,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 1,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 5,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 9,"xs" => 4,"ys" => 2));
	}else if($numGrids <= 11){
		array_push($gridPositions, array("yp" => 1,"xp" => 1,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 5,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 1,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 10,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 1,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 5,"xp" => 5,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 9,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 7,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 5,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 7,"xs" => 4,"ys" => 2));
	}else if($numGrids <= 20){
		array_push($gridPositions, array("yp" => 1,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 5,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 1,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 11,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 3,"xp" => 11,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 5,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 5,"xp" => 11,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 5,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 7,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 11,"xs" => 2,"ys" => 2));
	}else if($numGrids <= 41 || $numGrids >= 41){
		array_push($gridPositions, array("yp" => 1,"xp" => 1,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 3,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 5,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 1,"xp" => 9,"xs" => 2,"ys" => 2));
		array_push($gridPositions, array("yp" => 1,"xp" => 11,"xs" => 2,"ys" =>2));
		array_push($gridPositions, array("yp" => 3,"xp" => 1,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 2,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 3,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 4,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 9,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 10,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 11,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 3,"xp" => 12,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 1,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 2,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 3,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 4,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 9,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 10,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 11,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 4,"xp" => 12,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 1,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 2,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 3,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 4,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 5,"xs" => 4,"ys" => 4));
		array_push($gridPositions, array("yp" => 5,"xp" => 9,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 10,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 11,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 5,"xp" => 12,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 1,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 2,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 3,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 4,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 9,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 10,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 11,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 6,"xp" => 12,"xs" => 1,"ys" => 1));
		array_push($gridPositions, array("yp" => 7,"xp" => 1,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 5,"xs" => 4,"ys" => 2));
		array_push($gridPositions, array("yp" => 7,"xp" => 9,"xs" => 4,"ys" => 2));
	}
	return $gridPositions;
}
?>