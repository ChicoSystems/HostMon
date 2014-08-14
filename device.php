<?php
/* Renders the device page and delivers it to user */
	error_reporting(-1);
	include_once("php/db.php");	
	$loggedIn = true; //later we will check this in the session variable.
	$_SESSION['username'] = "itravers";
	if(!$_GET['ip'])$_GET['ip'] = "earlhart.com"; //default to earlhart if no ip is given to page.
	//$_GET['ip'] = "earlhart.com";
	$ip = $_GET['ip'];
	$deviceID = getDeviceID($ip); //db query get device id from device ip
	$deviceName = getDeviceName($deviceID); // db query get device name from device id.
	$notes = getNotes($deviceID); //db query get array of notes from device id.
	$page = buildPage($deviceID, $deviceName, $ip, $notes); //build each section of the page for printing.
	printPage($page); //output the page
	
/* Helper Functions. */

/** Builds the entire page into a string getting it ready for printing. */
function buildPage($deviceID, $deviceName, $ip, $notes){
	$page = "";
	$header = buildHeader($deviceName, $ip);
	$body = buildBody($deviceID, $deviceName, $ip, $notes);
	$footer = buildFooter();
	$page = $header.$body.$footer;
	return $page;
}

/** Builds the header into a string. */
function buildHeader($deviceName, $ip){
	$returnVal = "
<!DOCTYPE html>
<html class='main_device'>
	<head>
		<title>".$deviceName." - ".$ip."</title>
		<link rel='stylesheet' type='text/css' href='css/gridster.css'>
		<link rel='stylesheet' type='text/css' href='css/styles.css'>  
	</head>";
	return $returnVal;
}
	
/** Builds the body into a string.*/
function buildBody($deviceID, $deviceName, $ip, $notes){
	$returnVal = "";
	$openTag = "<body>";
	$menu = buildMenu();
	$grid = buildGrid($deviceID, $deviceName, $ip, $notes);
	$scripts = buildScripts($ip, $notes);
	$closingTag = "</body>";
	$returnVal = $openTag.$menu.$grid.$scripts.$closingTag;
	return $returnVal;
}
	
/** Builds the javascript functionality into a string for output. */
function buildScripts($ip, $notes){
	$millitime = round(microtime(true) * 1000);
	$date = getFormattedDate($millitime);
	$time = getFormattedTime($millitime);
	
	$newCount = count($notes)+1;
	$returnVal = "
<script type='text/javascript' src='js/jquery.tools.min.js'></script>
<script type='text/javascript' src='js/jquery.gridster.min.js' charster='utf-8'></script>
<script src='js/hostmonChart.js'></script>
<script src='js/Chart.min.js'></script>
<script type='text/javascript'>
	var gridster;
	var dragged = 0;
	
	var demoData = getMinuteDemoData(); //demo data for the line chart.
	var mainDeviceChart = getMainDeviceChart('FiveMinuteLine', '#51bbff', demoData);
		  
	//setup accordion sliders
	$(\"#accordion\").tabs(
		\"#accordion div.pane\",
		{tabs: 'h2', effect: 'slide', initialIndex: null}
	);
	
	$(\"#accordion2\").tabs(
		\"#accordion2 div.pane\",
		{tabs: 'h2', effect: 'slide', initialIndex: null}
	);
		  
	$(\"#accordion3\").tabs(
		\"#accordion3 div.pane\",
		{tabs: 'h2', effect: 'slide', initialIndex: null}
	);
	
	//keeps track of the line chart that is currently updating.	  
	function setLineChart(name){
		currentLineChart = name; 
		quickUpdateGraph(); 
	}

	//keeps track of the polar chart that is currently updating.		  
	function setPolarChart(name){
		var c = document.getElementById(name);
		currentPolarChart = name;
		if(currentPolarChart == 'MinutePolar'){
			polarChart = minPolarChart; 
		}else if(currentPolarChart == 'HourPolar'){
			polarChart = hourPolarChart; 
		}else if(currentPolarChart == 'DayPolar'){
			polarChart = dayPolarChart;
		}
			quickUpdateGraph();
	}
	
	//creates a note that the user can edit, this is the first step in a user creating a new note
	function createEditableNote(){
		//alert(\"plus clicked\");
		var divToAddTo = $('#accordion3 div:first');
		var divToHide = $('#accordion3 div h2:first');
		var paneToHide = $('#accordion3 div .pane');
		var imgToHide = $('#accordion3 div img');
		imgToHide.hide();
		//alert(paneToHide.text());
		paneToHide.css('display', 'none');
		divToHide.toggleClass('current');
		

	
			var toPrepend = \"<h2 class='item current'> \
				<div id='notenum'>".$newCount."</div> \
				<div id='notename'>".$_SESSION['username']."</div> \
				<div id='notedate'>".$date."</div> \
				<div id='notetime'>".$time."</div> \
				<img class='minus' src='images/minus.png'></img> \
			</h2> \
			<div class='pane' style='display:block'><button id='noteSubmitButton'>Submit</button><textarea id='noteInputText'></textarea></div>\";
		divToAddTo.prepend(toPrepend);
		
	}
	
	//triggered when document is loaded.
	$(document).ready(function(){
		console.log( 'ready!' );
		setupCharts('".$ip."');
		setTimeout('updateGraph()',10);
	});
		
	//sets the dragged variable to 0 when a device is loaded.  
	function loadDevice(id) {
		if(!dragged){
			//alert('loadDevice ' + id);
		}	
		// RESET DRAGGED SINCE CLICK EVENT IS FIRED AFTER drag stop
			dragged = 0;
	}

	//setup menu scroll section, and grids
	$(function() {
		//make the menu clickable. Set it to open menu.
		$('.menu').click(function() {
			$('nav').addClass('open');
			$('body').addClass('menu-open');
			return false;
		});	
		//make the menu clickable. Set it to open menu.
		$('.plus').click(function() {
			
			createEditableNote();
			return false;
		});	
		//make menu close when document is clicked.
		$(document).click(function() {
			$('body').removeClass('menu-open');
			$('nav').removeClass('open');
		});
		$('.scrollable').scrollable({ vertical: true, mousewheel: true });
		gridtster = $('.device .gridster > ul').gridster({
			widget_margins: [5, 5],
			widget_base_dimensions: [95, 95],
			min_cols: 10,
			draggable: {
				start: function(event, ui) {
					//Set dragged, to keep windows from opening when dragging.
					dragged = 1;
				}
			}	 
		}).data('gridster');
	});
</script>	";
	return $returnVal;
}

/** Sets up gridsters opening tags. */
function buildGridOpening(){
	$returnVal = "
		<section class='device'>
			<div class='gridster'>
				<ul class='gridlist'>";
	return $returnVal;	
}

/** Builds the grid section the device name is shown. */
function buildNameGrid($deviceName, $ip){
	$returnVal = "
		<li data-row='1' data-col='1' data-sizex='4' data-sizey='1' onclick='loadDevice('0');'>
			<h1>".$deviceName."</h1>
			<h2>".$ip."</h2></li>";	
	return $returnVal;
}

/** Builds the grid section where the menu is shown. */
function buildMenuGrid(){
	$returnVal = "
		<li class='gridmenu' data-row='1' data-col='5' data-sizex='2' data-sizey='1'>
			<a class='menu' href='#'>
				<div class='bar'></div>
				<div class='bar'></div>
				<div class='bar'></div>
			</a>
		</li>";
	return $returnVal;	
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
			<div class='pane' style='display:".$display."'>".$note['content']."</div>".$divMedium;	
		$i++;
		$j--;
	}
	$returnVal = $returnVal.$divFooter;
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

/* Build the Grid the line Chart is in. */
function buildLineChartGrid(){
	$returnVal = "
		<li data-row='2' data-col='1' data-sizex='6' data-sizey='5'>
			<div id='accordion'>
				<h2 class='current' onClick=\"setLineChart('FiveMinuteLine')\">5 Minutes / 15 Seconds</h2>
				<div class='pane' style='display:block'><canvas id='FiveMinuteLine' width='600px' height='425px'></div>
				<h2 onClick=\"setLineChart('HourLine')\">1 Hour / 5 Minutes</h2>
				<div class='pane'><canvas id='HourLine' width='600px' height='425px'></div>
				<h2 onClick=\"setLineChart('DayLine')\">1 Day / 1 Hour</h2>
				<div class='pane'><canvas id='DayLine' width='600px' height='425px'></div>
			</div>
		</li>";
	return $returnVal;	
}

/** Build the Grid the Polar Chart is in. */
function buildPolarChartGrid(){
	$returnVal = " 
		<li data-row='4' data-col='7' data-sizex='4' data-sizey='3'>
			<div id='accordion2'>
				<h2 class='current' onClick=\"setPolarChart('FiveMinutePolar')\">5 Minutes / 15 Seconds</h2>
				<div class='pane' style='display:block'><canvas id='FiveMinutePolar' width='440px' height='280px'></div>
				<h2 onClick=\"setPolarChart('HourPolar')\">1 Hour / 5 Minutes</h2>
				<div class='pane'><canvas id='HourPolar' width='430px' height='255px'></div>
				<h2 onClick=\"setPolarChart('DayPolar')\">1 Day / 1 Hour</h2>
				<div class='pane'><canvas id='DayPolar' width='420px' height='230px'></div>
			</div>
		</li> ";
	return $returnVal;	
}
	
/** Build the Closing Tag to the grid section. */
function buildGridClosing(){
	$returnVal = "
			</ul>
		</div>
	</section>";
	return $returnVal;	
}
	
/** Builds the Main Grid all the content is located in. */
function buildGrid($deviceID, $deviceName, $ip, $notes){
	$returnVal = "";
	$gridOpening = buildGridOpening();
	$nameGrid = buildNameGrid($deviceName, $ip);
	$menuGrid = buildMenuGrid();
	$notesGrid = buildNotesGrid($notes);
	$lineChartGrid = buildLineChartGrid();
	$polarChartGrid = buildPolarChartGrid();
	$gridClosing = buildGridClosing();
	$returnVal = $gridOpening.$nameGrid.$menuGrid.$notesGrid.$lineChartGrid.$polarChartGrid.$gridClosing;
	return $returnVal;
}
	
/** Build the Slide Out Menu */
function buildMenu(){
	$returnVal = "
		<nav class='left'>
			<ul>
				<li>
					<a href='#'>Home</a>
				</li>
				<li>
					<a href='#'>Options</a>
				</li>
				<li>
					<a href='#'>Logout</a>
				</li>
			</ul>
		</nav>";
	return $returnVal;
}
	
/** Builds the Pages Footer. */
function buildFooter(){
	$returnVal = "</html>";
	return $returnVal;	
}
	
/** Prints out the page, in the form of one long string. */
function printPage($page){
	echo $page;
}

/** Fetches the Device ID from the database, uses the ip. */
function getDeviceID($ip){
	$con = openDB();
	mysqli_select_db($con,"HostMon");
	$sql="SELECT id FROM `Devices` WHERE ip = '".$ip."'";
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
	mysqli_select_db($con,"HostMon");
	$sql="SELECT name FROM `Devices` WHERE id = '".$deviceID."'";
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
	mysqli_select_db($con,"HostMon");
	$sql="SELECT usr FROM `Users` WHERE id = '".$id."'";
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
	
/** Returns an Array of notes from the DB, based on deviceID. */
function getNotes($deviceID){
	$con = openDB();
	mysqli_select_db($con,"HostMon");
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
					  "content" => $item['content']);
		array_push($notes, $note);
	}
	return $notes;	
}
?>