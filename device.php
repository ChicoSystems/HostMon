<?php
/**************************************************************
* Hostmon - device.php
* Author - Isaac Assegai
* This page allows the user to actively or passively monitor
* A single device. The user will have access to several
* Graphs helping to monitor the device over several time frames
* The user will also be able to add and share notes about it.
* This page shows the HTML embedded in PHP technique.
**************************************************************/
//error_reporting(-1);
include_once("php/db.php");
include_once("php/functions.php");
session_start();
//$_SESSION['username'] = "itravers";

if(!isset($_SESSION['loggedIn'])){ //User is not logged in, redirect to login.php
	header("Location: login.php"); die();
}else{
	if(!isset($_GET['ip']))$_GET['ip'] = "two.com"; //default to earlhart if no ip is given to page.
	$ip = $_GET['ip'];
	$newCount = 0;
	$deviceID = getDeviceID($ip); //db query get device id from device ip
	$deviceName = getDeviceName($deviceID); // db query get device name from device id.
	$notes = getNotes($deviceID); //db query get array of notes from device id.
	$page = buildPage($deviceID, $deviceName, $ip, $notes); //build each section of the page for printing.
	printPage($page); //output the page
}

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
	//$menu = Menu();
	$grid = buildGrid($deviceID, $deviceName, $ip, $notes);
	$scripts = buildScripts($ip, $deviceID, $notes);
	$closingTag = "</body>";
	//$returnVal = $openTag.$menu.$grid.$scripts.$closingTag;
	$returnVal = $openTag.$grid.$scripts.$closingTag;
	return $returnVal;
}

/** returns the newcount*/
function getNewCount($newCount){
	$newCount++;
	return $newCount;
}

function getIncrementCount($newCount){
	$newCount++;
}
	
/** Builds the javascript functionality into a string for output. */
function buildScripts($ip, $deviceID, $notes){
	$millitime = round(microtime(true) * 1000);
	$date = getFormattedDate($millitime);
	$time = getFormattedTime($millitime);
	
	$newCount = count($notes);
	$returnVal = "
<script type='text/javascript' src='js/jquery.tools.min.js'></script>
<script type='text/javascript' src='js/jquery.gridster.min.js' charster='utf-8'></script>
<script src='js/hostmonChart.js'></script>
<script src='js/Chart.min.js'></script>
<script type='text/javascript'>
	var gridster;
	var dragged = 0; // Used to let us know if the grid is being dragged or not.
	
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
		  
	bindAccordion();
	bindScrollable();
	
	// Allows the mouse wheel to scroll.
	function bindScrollable(){
		$('.scrollable').scrollable({ vertical: true, mousewheel: true });	
	}
	
	function bindAccordion(){
		$(\"#accordion3\").tabs(
			\"#accordion3 div.pane\",
			{tabs: 'h2', effect: 'slide', initialIndex: null}
		);
	}
	
	//keeps track of the line chart that is currently updating.	  
	function setLineChart(name){
                if(name == 'HourLine' || name == 'DayLine'){
			if(!tour.ended()) tour.next();
		}
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
			tour.next();
			polarChart = hourPolarChart; 
		}else if(currentPolarChart == 'DayPolar'){
			polarChart = dayPolarChart;
			tour.next();
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
				<div id='notenum'>".getNewCount($newCount)."</div> \
				<div id='notename'>".$_SESSION['usr']."</div> \
				<div id='notedate'>".$date."</div> \
				<div id='notetime'>".$time."</div> \
				<img class='minus' src='images/minus.png'></img> \
			</h2> \
			<div class='pane' style='display:block'><button id='noteSubmitButton' onclick='clickNoteSubmitButton();'>Submit</button><textarea id='noteInputText'></textarea></div><div style='display:none'>".$millitime."</div> \";
		divToAddTo.prepend(toPrepend);
		toPrepend = '';
		tour.next();
	}
	
	//triggered when document is loaded.
	$(document).ready(function(){
		console.log( 'ready!' );
		setupCharts('".$ip."');
		setTimeout('updateGraph()',10);
	});
	
	function clickNoteSubmitButton(){
		var noteNumElement = $('#notenum:first');
		var noteNameElement = $('#notename:first');
		var noteContentElement = $('#noteInputText:first');
		var noteNum = noteNumElement.text();
		var noteName = noteNameElement.text();
		var noteContent = noteContentElement.val();
		var time = '".$millitime."';
		var deviceID = '".$deviceID."';
		//alert(noteContent);
		var postData = {SubmitNote:'true',
						noteNum:noteNum,
						noteName:noteName,
						noteContent:noteContent,
						time:time,
						deviceID:deviceID};
		$.ajax({
			type:\"POST\",
			data : postData,
			url: 'php/device-backend.php', 
			success: function(result,status,xhr) {
				
				//strip of the first and last line of result.
				//var replaceElementText = $('#actions');
				//replaceElementText.replaceWith(result);
				var translatedResult = result.replace(\"<li data-row='1' data-col='7' data-sizex='4' data-sizey='3' id='actions'>\", \" \"); 
				translatedResult = translatedResult.replace(\"</li>\", \" \");
				//alert(translatedResult);
				$('#actions').empty().append(translatedResult);  
				bindAccordion();
				bindScrollable();
			},
			complete: function(result,status,xhr) {
				// Schedule the next request when the current one's complete
				//alert(\"complete\" + result);
			},
			error: function(xhr,status,error){
				alert(\"error\" + error);
			}
		});
		tour.next();
	}
		
	//sets the dragged variable to 0 when a device is loaded.  
	function loadDevice(id) {
		if(!dragged){
			//alert('loadDevice ' + id);
		}	
		// RESET DRAGGED SINCE CLICK EVENT IS FIRED AFTER drag stop
			dragged = 0;
	}
	
	//user clicks the plus sign to add new note
	function plusClicked(){
		createEditableNote();
	}
	
	//user clicks the minus sign to delete a note.
	function minusClicked(){
		var currentNote = $('#accordion3 h2.current');
		currentNote.hide();
		currentNote.next().hide();
		var timestamp = currentNote.next().next().text();
		
		var postData = {RemoveNote:'true',
						timestamp:timestamp,
						deviceID:".$deviceID."};
		$.ajax({
			type:\"POST\",
			data : postData,
			url: 'php/device-backend.php', 
			success: function(result,status,xhr) {
				
				//strip of the first and last line of result.
				
				var translatedResult = result.replace(\"<li data-row='1' data-col='7' data-sizex='4' data-sizey='3' id='actions'>\", \" \"); 
				translatedResult = translatedResult.replace(\"</li>\", \" \");
				//alert(translatedResult);
				$('#actions').empty().append(translatedResult);  
				bindAccordion();
				bindScrollable();
				
				//alert(result);
			},
			complete: function(result,status,xhr) {
				// Schedule the next request when the current one's complete
				//alert(\"complete\" + result);
			},
			error: function(xhr,status,error){
				alert(\"error\" + error);
			}
		});
		
	}

	//setup menu scroll section, and grids
	$(function() {
		//make the add note plus sign clickable. Even if it is replaced
		$(document).on('click', '.plus', plusClicked);
		$(document).on('click', '.minus', minusClicked);
		gridster = $('.device .gridster > ul').gridster({
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


/* Build the Grid the line Chart is in. */
function buildLineChartGrid(){
	$returnVal = "
		<li data-row='2' data-col='1' data-sizex='6' data-sizey='5'>
			<div id='accordion'>
				<h2 class='current' id='minuteLineHandle' onClick=\"setLineChart('FiveMinuteLine')\">5 Minutes / 15 Seconds</h2>
				<div class='pane' style='display:block'><canvas id='FiveMinuteLine' width='600px' height='425px'></div>
				<h2 id='hourLineHandle' onClick=\"setLineChart('HourLine')\">1 Hour / 5 Minutes</h2>
				<div class='pane'><canvas id='HourLine' width='600px' height='425px'></div>
				<h2 id='dayLineHandle' onClick=\"setLineChart('DayLine')\">1 Day / 1 Hour</h2>
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
				<h2 class='current' id='minutePolarHandle' onClick=\"setPolarChart('FiveMinutePolar')\">5 Minutes / 15 Seconds</h2>
				<div class='pane' style='display:block'><canvas id='FiveMinutePolar' width='440px' height='280px'></div>
				<h2 id='hourPolarHandle' onClick=\"setPolarChart('HourPolar')\">1 Hour / 5 Minutes</h2>
				<div class='pane'><canvas id='HourPolar' width='430px' height='255px'></div>
				<h2 id='dayPolarHandle' onClick=\"setPolarChart('DayPolar')\">1 Day / 1 Hour</h2>
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
	//$menuGrid = buildMenuGrid();
	$notesGrid = buildNotesGrid($notes);
	$lineChartGrid = buildLineChartGrid();
	$polarChartGrid = buildPolarChartGrid();
	$gridClosing = buildGridClosing();
	//$returnVal = $gridOpening.$nameGrid.$menuGrid.$notesGrid.$lineChartGrid.$polarChartGrid.$gridClosing;
	$returnVal = $gridOpening.$nameGrid.$notesGrid.$lineChartGrid.$polarChartGrid.$gridClosing;
	
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


	

?>
