<!DOCTYPE html>
<?php  
/**************************************************************
* Hostmon - grid.php
* Author - Isaac Assegai
* This page allows the user to actively or passively monitor
* Several devices at one time. The user has the ability to 
* re-arrange the page and resize the interface to the different
* devices being monitored.
**************************************************************/
include_once("php/functions.php");
include_once("php/db.php");

$userName = "Guest";
$adminLevel;
$loggedIn = false;
$yellowAlarm = getAlarm('yellow');
$redAlarm = getAlarm('red');
if(!isset($_SESSION)) session_start();

//this is really a bad idea to check if we are logged in with a get variable, 
//can't seem to get the session variable to set in login-backend.php like i was planning
if(isset($_GET['login'])){ //if login has just logged us in
	if($_SESSION['loggedIn']){
		$userName = $_SESSION['usr'];
		$adminLevel = $_SESSION['admin_level'];
		$loggedIn = true;
	}
}else if(isset($_SESSION)){ //if we are already logged in but just updating the page.
	if($_SESSION['loggedIn']){
		$userName = $_SESSION['usr'];
		$adminLevel = $_SESSION['admin_level'];
		$loggedIn = true;
	}
}
$pageTitle = $adminLevel."Hostmon - ".$userName;
$devices = getActiveDevices($userName); // returns the initial active devices
$gridPositions = getGridPositions(count($devices)); // returns a 2d array with initial grid positions and sizes 
?>


<?php if($loggedIn):?>
<html class="main_grid">
<!-- Grid.html is currently having issues with event listeners. -->
	<head>
		<title><?php echo $pageTitle ?></title>
		<link rel="stylesheet" type="text/css" href="css/gridster.css">
		<link rel="stylesheet" type="text/css" href="css/styles.css">
		<link href="css/bootstrap-tour-standalone.css" rel="stylesheet">
                <link rel="stylesheet" type="text/css" href="css/jquery-ui.css">
		<link href="css/uploadfile.css" rel="stylesheet">
		<a class="menu" href="#" id="tour-menu">
			<div class="bar"></div>
			<div class="bar"></div>
			<div class="bar"></div>
		</a>
		<div id="onlineDot" class="dotUnknown playPager"></div>
		<?php echo Menu();?>
		<!-- This is the entire page, where the grid can roam. -->
		<section class="grid">
		
			<div class="gridster" id="frontGrid">
			<h5 class="version"><?php echo getCurrentVersion();?></h5>
				<ul class='gridlist'>
					<!-- each grid is parsed from the devices array, each grid is placed using the gridPositions array. -->
					<?php for($i=0;$i<count($devices);$i++) : ?>
					<li href="device.php?ip=<?php echo $devices[$i]['ip'];?>"  id="first" rel="#overlay" data-row="<?php echo $gridPositions[$i]['yp'] ?>" data-col="<?php echo $gridPositions[$i]['xp'] ?>" data-sizex="<?php echo $gridPositions[$i]['xs'] ?>" data-sizey="<?php echo $gridPositions[$i]['ys'] ?>" onclick="loadDevice('0');">
			<button onClick="removeDevice('<?php echo $devices[$i]['id'];?>', '<?php echo $devices[$i]['ip']; ?>');" id="removeButton">Remove</button>				
                    	<img src="images/up-arrow.png" class="grow"><img src="images/down-arrow.png" class="shrink">
                  		<div class="device_record" >
                        	<h1><?php echo $devices[$i]['name']; ?></h1>
							<h2><?php echo $devices[$i]['ip']; ?></h2>
							<h3>1ms</h3>
							<canvas id="<?php echo $devices[$i]['ip'];?>"></canvas>
							<canvas class="graph secondImage" id="<?php echo $devices[$i]['ip'];?>" 
								    style="display:<?php if($gridPositions[$i]['ys'] != 4){ echo 'none';}else{echo 'block';}?>;"></canvas>
							<div id="statusmark"></div>
						</div>
					</li>
					<?php endfor; ?>
					<!-- embedded php based on the following code
					<li href="device.php?ip=gmail.com" rel="#overlay" data-row="1" data-col="5" data-sizex="4" data-sizey="4" onclick="loadDevice('0');">
                    	<img src="images/up-arrow.png" class="grow"><img src="images/down-arrow.png" class="shrink">
						<div class="device_record">
                        	<h1>Gmail Service</h1>
							<h2>gmail.com</h2>
							<h3>1ms</h3>
							<canvas class="graph" id="gmail.com"></canvas><canvas class="graph secondImage" id="gmail.com"></canvas>
							<div id="statusmark"></div>
                        </div>
					</li> -->
					 <li data-row="7" data-col="1" data-sizex="1" data-sizey="1" id="newDeviceOpener">
                    	<div id="addNewDeviceImageContainer">
                    		<img src="images/plus.png" id="addNewDeviceImage">
                        </div>
					</li>
                   
				</ul>
			</div>
			<div class="close"></div>   
            
           <div id="newDeviceDialog" title="Add New Device">
			  <form id="newDeviceForm">
				<fieldset>
				  <input type="text" name="deviceName" id="deviceName" placeholder="Device Name" class="text ui-widget-content ui-corner-all">
				  <input type="text" name="deviceIP" id="deviceIP" placeholder="Device IP" class="text ui-widget-content ui-corner-all">
				  <input type="textarea" name="deviceNote" id="deviceNote" placeholder="Initial Note" class="textarea ui-widget-content ui-corner-all">
				  <!-- Allow form submission with keyboard without duplicating the dialog button -->
				  <!--<input type="submit" id="newDeviceSubmit">-->
				</fieldset>
			  </form>
			</div>
		</section>
     
		<!-- overlayed element -->
		<div class="apple_overlay" id="overlay">
        
		<!-- the external content is loaded inside this tag -->
		<div class="contentWrap"></div>
        
<script type="text/javascript" src="js/jquery.tools.min.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="js/jquery.md5.js"></script>
<script src="js/menu.js"></script>
<script src="js/hostmonChart.js"></script>
<script type="text/javascript" src="js/jquery.gridster.min.js" charster="utf-8"></script>
<script src="js/bootstrap-tour-standalone.min.js"></script>
<script src="js/uploadfile.min.js"></script>

<script type="text/javascript">
var gridster = 0;
var dragged = false; // Used to keep the device.php overlay from loading when a grid is being dragged.
var gridGraphTimeOut; //Used to disable ajax updating of the page when device.php is overlayed.
var tour; //used to construct tours
var overlay; //Overlay used to display device.
var adminLevel = <?php echo $adminLevel; ?>;
var redAudioElement; //used to play alarms with audioElement.play();
var yellowAudioElement; //used to play alarms with audioElement.play();
var yellowAlarm = "<?php echo $yellowAlarm; ?>";
var redAlarm = "<?php echo $redAlarm; ?>";
var newDeviceDialog = $("#newDeviceDialog");
var resizing = false; //Used to keep overlay from opening when pressed grow button.

//Initialize Gridster
gridster = $("#frontGrid > ul").gridster({
	widget_margins: [5, 5],
	widget_base_dimensions: [95, 95],
	min_cols: 8,
	draggable: {
		start: function(event, ui) {
			console.log("dragged");
			dragged = true; //keeps overlay from loading while we are dragging.
		},
		stop: function(event, ui) {
			console.log("stopped dragging");
		//	dragged = false;
		}
	} 
}).data('gridster');	


var onMouseWheel = function(e) {
	var browser = $.browser;
    e = e.originalEvent;
    var delta = e.wheelDelta>0||e.detail<0?1:-1;
    delta = 0 - delta;
   // alert(delta);
  
    //scrolling only works on mozilla with an html tag
    //scrolling only works on safari/opera with a body tag.
    if ($.browser.mozilla && $.browser.version >= "1.8" ){ 
    	var scrollTop = $("html").scrollTop();
   	 	$("html").scrollTop(scrollTop+(delta*15));
    }else{
    	 var scrollTop = $("body").scrollTop();
    	 $("body").scrollTop(scrollTop+(delta*15));
    }
}
$("body").bind("mousewheel DOMMouseScroll", onMouseWheel);

//Initialize and draw every canvas on the page.		
c = initializeCanvas("can1");
for(i = 0; i < c.length; i++){
	canvas = c[i];
	canvas.height = canvas.width/3;
    colorCanvas(canvas, "#51bbff");
    drawGraph(canvas);
}


// Separates a string in ajax. Probably Used for adding a new device?		
function getMessage(result){
	var message = result.substring(0, result.indexOf("|"));	
	return message;
}

// Separates a string in ajax. Probably Used for adding a new device?
function getDisplay(result){
	var display = result.substring(result.indexOf("|")+1, result.length);
	return display;	
}

//sends ajax call to server to remove device with $id from active_devices table
function removeDevice(id, ip){
	console.log("removeDevice("+id+");");
	var postData = {
		removeDevice:id
	};

	$.ajax({
                type:"POST",
                data : postData,
                url: 'php/grid-backend.php',
                success: function(result,status,xhr) {
                        var message = getMessage(result);
                        var display = getDisplay(result);
			console.log("removeDevice success: " + message);
                        if(message.indexOf("DeviceExists") != -1){
                                //$(".ui-dialog-title").html("holy crap")
                                //alert();
                        }
			//quick and dirty hack, reload the page
			location.reload();			
	
                //      alert("message: " + message);
                //      alert("display: " + display);
                },
                complete: function(result,status,xhr) {
                        //alert("complete: " + result);
                },
                error: function(xhr,status,error){
                        alert("Error in addNewDevice ajax call: " + error);
                }
        });

}

//Sends an ajax call to the server to add a new device, then it displays it.	
function addNewDevice(){
	var deviceName = $("#deviceName").val();
	var deviceIP = $("#deviceIP").val();
	var deviceNote = $("#deviceNote").val();			
	//alert(deviceName + deviceIP + deviceNote);	
	var postData = {
		addNewDevice:'true',
		deviceName:deviceName,
		deviceIP:deviceIP,
		deviceNote:deviceNote,
		userName:'<?php echo $userName; ?>'
	};
//	alert(JSON.stringify(postData));
	$.ajax({
		type:"POST",
		data : postData,
		url: 'php/grid-backend.php', 
		success: function(result,status,xhr) {
			var message = getMessage(result);
			var display = getDisplay(result);
			if(message.indexOf("DeviceExists") != -1){
				//$(".ui-dialog-title").html("holy crap")
				//alert();
			}
			var ps = getNewGridPositionAndSize(); //Finds where we are supposed to place the new grid.
			var widget = gridster.add_widget( display, ps[0], ps[1], ps[2], ps[3]); //adds a new grid to gridster
			//overlay.overlay(overlay.getConf());//re-register overlay listeners?
			widget = widget.get(0);
			var grow = $(widget).find(".grow");
			location.reload(); //shortcut hack for adding new devices.
		//	alert("message: " + message);
		//	alert("display: " + display);
		},
		complete: function(result,status,xhr) {
			//alert("complete: " + result);
			 $("li[rel]").overlay(overlay.getConf());
		},
		error: function(xhr,status,error){
			alert("Error in addNewDevice ajax call: " + error);
		}
	});
	newDeviceDialog.dialog( "close" ); //closes the add new device dialog.
}
		
// Finds the last grid position, and positions a device	at the position of the newDeviceOpener
function getNewGridPositionAndSize(){
	var sizeX = $("#newDeviceOpener").attr("data-sizex");
	var sizeY = $("#newDeviceOpener").attr("data-sizey");
	var posX = $("#newDeviceOpener").attr("data-col");
	var posY = $("#newDeviceOpener").attr("data-row");
	var returnVal = [sizeX, sizeY, posX, posY];
	return returnVal;
}

// Used to be used to load the device, now it only makes sure that when a device is loaded, drag resets. 
function loadDevice(id) {
	//if(!dragged){
		//loading the device now happens when user clicks the li[rel]
		//this function only resets the dragged variable now.
	//}	// RESET DRAGGED SINCE CLICK EVENT IS FIRED AFTER drag stop
	//dragged = 0;
}
	
	
// Resizes a grid when the user clicks on a "grow button".
//$('.grow').on('click', function(event) {
//COME BACK
$(".gridlist").on('click', '.grow', function(event){
	resizing = true;
	event.stopImmediatePropagation(); // this stops the overlay from popping up.
	//event.preventDefault(); // this stops the overlay from popping up.
	var grid = gridster;
	var widget = $(this).parent();
	var xSize = widget.attr("data-sizex");
	var ySize = widget.attr("data-sizey");
	
	// We need to figure out the size to grow to, based on the current grid size.
	var x = 0;
	var y = 0;
	if(xSize == 1 && ySize == 1){
		x = 2;
		y = 2;
	}else if(xSize == 2 && ySize == 2){
		x = 4;
		y = 2;	
	}else if(xSize == 4 && ySize == 2){
		x = 4;
		y = 4;
	}else{ // do nothing, we are as big as we get.
		x = xSize;
		y = ySize;	
	}
	
	var can = $(widget).children(".device_record").children("canvas");
	var data = updateGridGraphData(can, x, y); // Redraw the Canvas charts in this grid.		
	if(y == 4){ //If our grid is 4 rows high, we want to show the hourly graph as well.
		updateGridGraphData(can[1], x, y); // Get info from server and draw the hourly graph.
		$(can[1]).show(); //make sure it's visible
	}else{
		updateGridGraphData(can[1], x, y); //draw the hourly graph even if it's hidden in this case.
		$(can[1]).hide();	//make sure it's invisible
	}
	grid.resize_widget(widget, x, y, true); // Resizes the grid itself. May want to do this before redrawing the graphs.
}); // End of grow code.
	
// Resizes a grid widget whenever a user clicks it's "shrink button"	
$('.gridlist').on('click', '.shrink', function(event) {
	event.stopImmediatePropagation(); // Stop the device.php page from popping up when shrink button is clicked.
    var grid = gridster;
	var widget = $(this).parent();
	var xSize = widget.attr("data-sizex");
	var ySize = widget.attr("data-sizey");
	
	//Figure out what size to shrink the widget to based on its current size.
	var x = 0;
	var y = 0;
	if(xSize == 1 && ySize == 1){ //we are as small as we get, keep this size.
		x = xSize;
		y = ySize;	
	}else if(xSize == 2 && ySize == 2){
		x = 1;
		y = 1;	
	}else if(xSize == 4 && ySize == 2){
		x = 2;
		y = 2;
	}else{ // do nothing, we are as big as we get.
		x = 4;
		y = 2;	
	} 
	
	// Get the graph on this widget and update it.
	var can = $(widget).children(".device_record").children("canvas");
	var data = updateGridGraphData(can, x, y); // Retrieve data from server and display on graph.
	if(y == 4){ // A widget with 4 rows should display the hourly graph as well as the minute one.
		updateGridGraphData(can[1], x, y); // Get info from server and draw the hourly graph.
		$(can[1]).show(); //make sure it's visible
	}else{
		$(can[1]).hide();	//make sure it's invisible
	}
	grid.resize_widget(widget, x, y, true); // Resize the widget itself. May want to put this before the drawing code above.
}); //End of Shrink event.

// Event Called when user opens a device. This load's and overlays the device.php page over grid.php.			  
overlay = $("li[rel]").overlay({
	top:top,
	mask: 'darkred',
	effect: 'apple',
	fixed: false,
	top: '1%',
	onBeforeLoad: function(event) {
			if(dragged){
				console.log("yes dragged");
				dragged = false;
				return false; //when dragging, don't popup.
			}else{
				console.log("no dragged");
			}

			var targ = event.target.className;
			console.log("target: " + targ);
			console.log("loading overlay menu Open: " + menuOpen);
			if(menuOpen){
				//The following code is repeaded here and in menu.js
				console.log("close menu");
                        	clearTimeout(getBackendRunningTimeout); // Remove the timer.
                        	clearTimeout(menuTimeout); // Remove the timer.
                        	$('body').removeClass('menu-open');
                        	$('nav').removeClass('open');
                        	$(".ajax-file-upload-container").fadeOut(); //cause upload messages to disappear
                        	$("#eventsmessage").fadeOut(); //cause upload messages to disappear
                        	menuOpen = false;
				return false;
			}
					
	
			if(targ == "grow" || targ == "shrink"){
				console.log("target was grow or shrink");
				//return false;
				this.getOverlay().close(); //prevent overlay from opening.
			}

			clearTimeout(gridGraphTimeOut); // disable updating of the main page, when second page is open.
			var wrap = this.getOverlay().find(".contentWrap"); // grab wrapper element inside content
			console.log(wrap);
			wrap.load(this.getTrigger().attr("href")); // load the page specified in the trigger (the device clicked) to the overlay		
		
	},
	onClose: function() {
		$('.menu').fadeIn(); // Fade the menu button back in when the overlay is closed.
		gridGraphTimeOut = setTimeout(updateGridGraphs, 5000); // Re-allow updating of main page when second page closes.
		
		// Are we re-initializing gridster here? Do we need to do this?
		gridster = $("#frontGrid > ul").gridster({
			widget_margins: [5, 5],
			widget_base_dimensions: [95, 95],
			min_cols: 8,
			draggable: {
				start: function(event, ui) {
					dragged = true;
				}
			}
		}).data('gridster');
		if(!tour.ended())tour.next();	
	},// End onClose.
	onLoad: function() {
//	tour.redraw();
		if(!tour.ended()) tour.next();
	},
	api: true
}); // End overlay Event.
	
//$("#newDeviceDialog").parents('div').css("border-color", "white");
	
// Contructs and adds a new device dialog to the screen.
newDeviceDialog.dialog({
	autoOpen: false,
	show: {
		effect: "blind",
		duration: 1000
	},
	hide: {
		effect: "explode",
		duration: 1000
	},
	buttons: [ { text: "Add Device", id: "addDeviceButton", click: function() { addNewDevice(); } } ]
}); //End of newDeviceDialog.

// Handles when a user clicks on the new Device Button.
$( "#newDeviceOpener" ).click( function(event) {
	if(!dragged){
		if(!menuOpen) //only want to open dialog if the menu is not open
			 newDeviceDialog.dialog( "open" ); // Opens the new device dialog.
	}else{
		//Don't do anything, but set dragged to false.
		dragged = false;
	}
	if(!tour.ended())tour.next();
}); // End of newDeviceOpener click handler

// Event Handler called when document is first loaded. Intializes the update of the grid graphs.
$(document).ready(function() {
	newDeviceDialog = $("#newDeviceDialog");
	setTimeout('updateGridGraphs()',10);
	//alert("about to set menu config info");
	setMenuConfigInfo(true); //we don't want it to start repeating

	/*setup alarms*/
	/*
	redAudioElement = document.createElement('audio');
        redAudioElement.setAttribute('src', 'alarms/firePager.mp3');
        redAudioElement.setAttribute('preload', 'preload');
	redAudioElement.setAttribute('id', 'redAudioElement');

	yellowAudioElement = document.createElement('audio');
	yellowAudioElement.setAttribute('src', 'alarms/bleep.mp3');
	yellowAudioElement.setAttribute('preload', 'preload');
	yellowAudioElement.setAttribute('id', 'yellowAudioElement');

        $.get();

        redAudioElement.addEventListener("load", function() {
        }, true);

        $('.playPager').click(function() {
            redAudioElement.play();
        });

        $('.pausePager').click(function() {
            redAudioElement.pause();
        });

	yellowAudioElement.addEventListener("load", function() {
        }, true);

        $('.playBleep').click(function() {
            yellowAudioElement.play();
        });

        $('.pauseBleep').click(function() {
            yellowAudioElement.pause();
        });
*/
/*
$("#yellowuploader").uploadFile({
	url:"php/uploadFile.php",
	acceptFiles: "audio/*",
	fileName:"myfile",
	onLoad:function(obj){
	},
	onSubmit:function(files){
		if (files.toString().toLowerCase().indexOf("mp3") >= 0){
		}else{
			$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Error, wrong file format. .mp3 ONLY!");
			return false;
		}
	},
	onSuccess:function(files,data,xhr,pd){
		$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Success for: "+JSON.stringify(data));
	},
	afterUploadAll:function(obj){
		$("#eventsmessage").html($("#eventsmessage").html()+"<br/>All files are uploaded");
	},
	onError: function(files,status,errMsg,pd){
		$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Error for: "+JSON.stringify(errMsg));
	},
	onCancel:function(files,pd){
		$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Canceled  files: "+JSON.stringify(files));
	}
});

$("#reduploader").uploadFile({
        url:"php/uploadFile.php",
        acceptFiles: "audio/*",
        fileName:"myfile",
        onLoad:function(obj){
        },
        onSubmit:function(files){
                if (files.toString().toLowerCase().indexOf("mp3") >= 0){
                }else{
                        $("#eventsmessage").html($("#eventsmessage").html()+"<br/>Error, wrong file format. .mp3 ONLY!");
                        return false;
                }
        },
        onSuccess:function(files,data,xhr,pd){
                $("#eventsmessage").html($("#eventsmessage").html()+"<br/>Success for: "+JSON.stringify(data));
        },
        afterUploadAll:function(obj){
                $("#eventsmessage").html($("#eventsmessage").html()+"<br/>All files are uploaded");
        },
        onError: function(files,status,errMsg,pd){
                $("#eventsmessage").html($("#eventsmessage").html()+"<br/>Error for: "+JSON.stringify(errMsg));
        },
        onCancel:function(files,pd){
                $("#eventsmessage").html($("#eventsmessage").html()+"<br/>Canceled  files: "+JSON.stringify(files));
        }
});

*/

tour = new Tour({
 debug: true,
  steps: [
  {
    element: "#tour-menu",
    title: "Welcome To Hostmon!",
    content: "You can use hostmon to continously monitor the latency to any device on the public internet."
  },
  {
    element: "#newDeviceOpener",
    title: "Adding A Device",
    content: "Click the + symbol to add a new device.",
    onShow: function(){
      //we want to close the $newDeviceDialog incaase its open from the next step of the tour
      //$("#newDeviceDialog").dialog("close");
      newDeviceDialog.dialog("close");
    }
  },
  {
    element: "#deviceName",
    title: "Insert Device Name",
    content: "This is the name you will be referring to this device by in Hostmon.",
    onShow: function(){
     //$( "#newDeviceDialog" ).dialog( "open" ); // Opens the new device dialog.
     newDeviceDialog.dialog( "open" ); // Opens the new device dialog.
    }
  },
  {
    element: '#deviceIP',
    title: "Insert Ip / Hostname",
    content: "This can be an IP, Hostname, or Domain name. Anything you can ping should work."
  },
  {
    element: '#deviceNote',
    title: "Make A Note About Device",
    content: "Make a note to help yourself remember why you are adding this device. This note will show in the device page."
  },
  {
    element: '#addDeviceButton',
    title: "Click Add Device",
    content: "The device you added will starting being monitored immediately."
  },
  {
    element: '#first',
    title: "You first Device",
    content: "Each Device Box shows you the status of that device at a glance.",
    onShow: function(){
      //$("#newDeviceDialog").dialog("close");
      newDeviceDialog.dialog("close");
    }
  },
  {
    element: '#first',
    placement: "bottom",
    title: "Resizing",
    content: "Use the White arrows to resize the box. You can also drag and drop all boxes to different locations.",
    onShow: function(){
      //$("#newDeviceDialog").dialog("close");
      newDeviceDialog.dialog("close");
    }
  },
  {
    element: '#first',
    title: "Click the Graph",
    content: "This will bring up the device page. ",
    
    template: "<div class='popover tour'> \
	      <div class='arrow'></div> \
 	      <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
  		</div> \
		</div>",
  },
  {
    element: '#FiveMinuteLine',
    title: "5 Minute Graph",
    content: "The 5 Minute graph shows the last 5 minutes of history in 15 second increments.",
  },
  {
    title: "Hourly Graph",
    content: "Click here to pull up the Hourly graph. The Hourly graph shows the last Hour of history in 5 minute increments.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "bottom"
  },
  {
    element: '#dayLineHandle',
    title: "Daily Graph",
    content: "Click here to pull up the Daily Graph. The Daily Graph shows the last Day of History in Hourly increments.",
    placement: "bottom",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",
  },
  {
    element: '#FiveMinutePolar',
    title: "Five Minute Polar Chart",
    content: "This chart gives quick reference so we can see the distribution of values at a glance.",
    placement: "left"
  },
  {
    element: '#hourPolarHandle',
    title: "Click Hourly Polar Graph",
    content: "The hourly Polar Graph gives us a quick reference so we can see the hourly distribution of values at a glance.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "left"
  },
  {
    element: '#dayPolarHandle',
    title: "Click Day Polar Graph",
    content: "The daily Polar Graph gives us a quick reference so we can see the daily distribution of values at a glance.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "left"
  },
  {
    element: '.plus',
    title: "Click + to Add Notes",
    content: "Adding notes is useful for tracking issues with a device. Notes can be left and reviewed for every use. Click the + button to start adding a new note now.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "bottom"
  },
  {
    element: '#noteInputText',
    title: "Input Your Note Here",
    content: "Input any notes or observations you have about this device here.",
    placement: "left"
  },
  {
    element: '#noteSubmitButton',
    title: "Click Submit to submit note.",
    content: "This note will be viewable by all other users.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "bottom"
  },
  {
    element: 'a.close',
    title: "Click the X to close this device.",
    content: "All devices are monitored in the background all the time. Click the X and you will be able to explore other devices.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "left",
  },
  {
    element: "#onlineDot",
    title: "Backend Indicator",
    content: "Indicator Light shows Green when the backend is running, and red when it is not.",
    placement: "bottom"
  },
  {
    element: '#tour-menu',
    title: "Click the Menu Icon",
    content: "The menu gives you the ability to adjust account and machine settings.",
     template: "<div class='popover tour'> \
              <div class='arrow'></div> \
              <h3 class='popover-title'></h3> \
              <div class='popover-content'></div> \
              <div class='popover-navigation'> \
              <button class='btn btn-default' data-role='prev'>« Prev</button> \
              <span data-role='separator'>|</span> \
              <button class='btn btn-default' data-role='end'>End tour</button> \
                </div> \
                </div>",

    placement: "left",
  },
  {
    element: ".changePassword2",
    title: "Change Password",
    content: "You can change your password here. Input your new password two times, and click the set button. The password field will blink blue if your password has been changed successfully, and it will blink red if not.",
    placement: "bottom"
  }

]});

//Check if user is an admin, if they are then show them the admin tour of the menu.
if(adminLevel == 10){

//Since we already have some of the data we want encoded at the title attirbute of several
//menu options, we are just going to grab that info straight from the DOM and display
//it in our tour.
var avgGoalTitle = $("#avgGoalTitle").attr("title");
var startingThreadsTitle = $("#startingThreadsTitle").attr("title");
var maxThreadsTitle = $("#maxThreadsTitle").attr("title");
var tRemovalTitle = $("#tRemovalTitle").attr("title");
var tAddTitle = $("#tAddTitle").attr("title");
var runsPerThreadTitle = $("#runsPerThreadTitle").attr("title");
var pingsDBTitle = $("#pingsDBTitle").attr("title");
var minuteAgeTitle = $("#minuteAgeTitle").attr("title");
var hourAgeTitle = $("#hourAgeTitle").attr("title");
var dayAgeTitle = $("#dayAgeTitle").attr("title");
var weekAgeTitle = $("#weekAgeTitle").attr("title");
var pingMinuteTitle = $("#pingMinuteTitle").attr("title");
var pingHourTitle = $("#pingHourTitle").attr("title");
var pingDayTitle = $("#pingDayTitle").attr("title");
var pingWeekTitle = $("#pingDayTitle").attr("title");
	tour.addStep({
		element: "#stopStartButton",
		title: "Start/Stop the Backend.",
		content: "<font color='red'>Admin Only Option.</font> <br> Hit the start button and a command will be sent to the backend to start. <br> Hit the stop button and a command will be sent to the backend to stop. <br> If succussfully started the Message 'Backend Started' will flash in green letters. <br> If succussfully stopped the words 'Backend Stopped' will flash in red.",
		placement: "right"
	});
	tour.addStep({
                element: ".adminLvl",
                title: "Add New User",
                content: "<font color='red'>Admin Only Option.</font> <br> Set username and password. <br> <br> Admin LVL's: <br> 0: Unapproved sign up. - This user cannot yet login. <br> 1 - 9: Normal Users - Cannot access admin functions. <br> 10: Admin - Can do anything.",
                placement: "bottom"
        });

	tour.addStep({
                element: ".removeUserErrorOutput",
                title: "Remove User",
                content: "<font color='red'>Admin Only Option.</font> <br> Input user name and hit set to remove given user.",
                placement: "bottom"
        });

	tour.addStep({
                element: "#averageGoalButton",
                title: "Average Goal Time Per Ping",
                content: "<font color='red'>Admin Only Option.</font> <br> " + avgGoalTitle + " <br> A Minimum of 5000ms is recommended.",
                placement: "bottom"
        });
	
	tour.addStep({
                element: "#startingThreadsButton",
                title: "Starting Threads",
                content: "<font color='red'>Admin Only Option.</font> <br> " + startingThreadsTitle + " <br> The higher this value, the larger the memory requirements of the backend when initially ran.",
                placement: "right"
        });
	
	tour.addStep({
                element: "#maxThreadsButton",
                title: "The maximum amount of threads the backend can run at a single time.",
                content: "<font color='red'>Admin Only Option.</font> <br> " + maxThreadsTitle + " <br> A Minimum of 10 and a maximum of 75 threads is recommended.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#tRemovalButton",
                title: "Thread Removal Co-efficient",
                content: "<font color='red'>Admin Only Option.</font> <br> " + tRemovalTitle,
                placement: "right"
        });
	
        tour.addStep({
                element: "#tAddButton",
                title: "Thread Adding Co-efficient",
                content: "<font color='red'>Admin Only Option.</font> <br> " + tAddTitle,
                placement: "right"
        });
	
        tour.addStep({
                element: "#runsPerThreadButton",
                title: "Runs / Thread",
                content: "<font color='red'>Admin Only Option.</font> <br> " + runsPerThreadTitle + " <br> A value of 5 seems normal.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#pingsDBButton",
                title: "Pings / DB Call",
                content: "<font color='red'>Admin Only Option.</font> <br> " + pingsDBTitle + " <br> A higher value means the DB gets written to less, and will be under less stress. However the time between updates on the front end will suffer accordingly.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#minuteAgeButton",
                title: "Minute Graph Aging",
                content: "<font color='red'>Admin Only Option.</font> <br> " + minuteAgeTitle + " <br> 9,000,000 ms is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#hourAgeButton",
                title: "Hour Graph Aging",
                content: "<font color='red'>Admin Only Option.</font> <br> " + hourAgeTitle + " <br> 14,400,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#dayAgeButton",
                title: "Day Graph Aging",
                content: "<font color='red'>Admin Only Option.</font> <br> " + dayAgeTitle + " <br> 345,600,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#weekAgeButton",
                title: "Week Graph Aging",
                content: "<font color='red'>Admin Only Option.</font> <br> " + weekAgeTitle + " <br> 2,419,200,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#pingMinuteButton",
                title: "Time Averaged in Hour Table",
                content: "<font color='red'>Admin Only Option.</font> <br> " + pingMinuteTitle + " <br> 300,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#pingHourButton",
                title: "Time Averaged in Day Table",
                content: "<font color='red'>Admin Only Option.</font> <br> " + pingHourTitle + " <br> 3,600,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#pingDayButton",
                title: "Time Averaged in Week Table",
                content: "<font color='red'>Admin Only Option.</font> <br> " + pingDayTitle + " <br>  86,400,000 is default.",
                placement: "right"
        });
	
        tour.addStep({
                element: "#pingWeekButton",
                title: "Time Averaged in Year Table",
                content: "<font color='red'>Admin Only Option.</font> <br> " + pingWeekTitle + " <br> 604,800,000 is default.",
                placement: "right"
        });
}

//Last Step of tour, used if user is an admin, or not
tour.addStep({
                element: "#logoutButton",
                title: "Logout Here",
                content: "Logout to keep those dirty good for nothings away from your precious hostmon.",
                placement: "top"
        });



// Initialize the tour
tour.init();

// Start the tour
tour.start();

});			 

// Query the server and redraw a specific graphs data. 
function drawGridGraph(c, data, col, row){
	var array_data = String(data).split(" "); // Seperate the data into an array.
	var parent = $(c).parent(); // The Canvas' Parent
	var grandparent = $(parent).parent(); //The Canvas' Grandparent.
	var width = $(grandparent).width(); // We need the Grandparents dimensions to know the canvas' limits.
	var height = $(grandparent).height();
	var widthLimit = width;
	var heightLimit = height;
	// The problem is, we aren't checking if this is a 2nd image, it's taking
	// the value from the hour table.
	if(array_data[0] == "737"){
		var hello = "hello";
	} 
	if(!$(c).hasClass("secondImage")){ // we don't want to update color when hour graph is processed
		updateMSDisplay(c, array_data[0]); // Updates the Milli-seconds label in the widget.
		updateGridColor(c, array_data[0]); // Updates the grid's color based on the latest millisecond reading.
	}
	
	// Calculate the Graph/Canvas' limits from the widgets column and row sizes.
	if(col == 1 && row == 1){
		height = 150;
		heightLimit = 50;
		width = 150;
		widthLimit = 120;
	}else if(col == 2 && row == 2){
		height = 130;
		heightLimit = 130;
		width = 400;
		widthLimit = 400;
	}else if(col == 4 && row == 2){
		width = 400;
		widthLimit = 400;
		height = 130;
		heightLimit = 130;
	}else if(col == 4 && row == 4){
		width = 400;
		widthLimit = 400;
		height = 120;
		heightLimit = 120;
	}
	
	c.height = height; // Set the dimension of the graph to the calculated limits.
	c.width = width;
	
	var context = c.getContext('2d');
	var sections = widthLimit / array_data.length-1; // How many seconds the graph has is based on how much data we retrieve from server.
	context.beginPath(); // Begin drawing the graph to the canvas.
	if(arrayIsZero(array_data)){ // If array only has zero's in it, draw a single dead line, denoting a unreachable device.
		context.moveTo(0, heightLimit-0); // We do heightLimit-0 to reverse the up/down orientation of the graph.
		context.lineTo(sections * 22, heightLimit-0);
		context.strokeStyle = "#FF0000";
	}else{ // Ff array has data in it, draw that
		var newValue;
		for(var i = 0; i < array_data.length-1; i++){ // Loop through the data, drawing each line.
			newValue = map(array_data[i], Math.min.apply(Math, array_data), Math.max.apply(Math, array_data), 0, heightLimit);
			if(i == 0){ //First data point in the data set.
				context.moveTo(widthLimit-(sections * i), heightLimit-newValue);
			}else{
				context.lineTo(widthLimit-(sections * i), heightLimit-newValue);
			}	
		}
		context.strokeStyle = "#FFFFFF";
	}
	context.lineWidth = 2;
	context.stroke();	
}

// Updates the Millisecond display on a widget
function updateMSDisplay(canvas, newPing){
	var h3 = $(canvas).siblings("h3");
	$(h3).text(newPing+"ms")
	//alert($(h3).text(newPing));
}

/** Updates the grid's color based on the latest data received. */
function updateGridColor(canvas, newPing){
	var blueLimit = 700; // The limit below which the widget will display blue. We will later retrieve these values from a db.
	var grandParent = $(canvas).parent().parent();
	var yellowLimit = 2000; // The limit below which the widget will display yellow. Above this it will display red.
	var newClass; // The color we are adding to the widgets class.
	if(newPing == 0 || newPing > yellowLimit){
		newClass = 'red';
		//Play Audio Alarm, only when a change happens. If we already had red class then don't play it.
		if(! $(grandParent).hasClass("red")){

			redAudioElement.play();
		}
	}else if(newPing < blueLimit){
		newClass = '';
	}else if(newPing <= yellowLimit){
		newClass = 'yellow';
		//Play Audio Alarm, only when a change happens. If we already had yellow class then don't play it.
		if(! $(grandParent).hasClass("yellow")){

                        yellowAudioElement.play();
                }

	}
	
	//Find the widget this canvas is in, and add the correct class to it, turning it the correct color.
	$(grandParent).removeClass("red");
	$(grandParent).removeClass("yellow");
	$(grandParent).addClass(newClass);
}

/** Returns true if every data value in the passed in array is 0, false if else. */
function arrayIsZero(a){
	var answer = true;
	for(var i = 0; i < a.length; i++){
		if(a[i] != 0){
			answer = false;
		}			
	}
	return answer;
}

/** Uses ajax to get our grid graph data from the server. */
function updateGridGraphData(canvas, x, y){
	var returnVal = "";
	returnVal = (function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
		var currentIP = $(canvas).attr("id");
		var curClass = $(canvas).attr("class");
		var s_curClass = String(curClass);
		
		if(s_curClass.indexOf("secondImage") == -1){ //We don't need to get info for the hour graph, but the minute graph
			postData = {getGridGraphData:true,
						ip:currentIP,
						timeRange:"fiveMinute"};
		}else{ // We do need to get information from the hour graph.
			postData = {getGridGraphData:true,
						ip:currentIP,
						timeRange:"hour"};
		}
		 console.log(JSON.stringify(postData));
		 // Send the request to the server.
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/grid-backend.php', 
			success: function(result,status,xhr) {
				if(result == ""){//if result is empty we do not want to update gridGraphData

				}else{
					drawGridGraph(canvas, result, x, y); // We received our data, go head and draw the appropriate graph.
				}
				console.log("success: " + currentIP);
			},
			complete: function(result) {
				// Schedule the next request when the current one's complete
				//alert("complete" + result);
			},
			error: function(xhr,status,error){
				//alert("error" + error);
				console.log("error: getGridGraphData ajax call"+ currentIP);
			}
		}); // End of ajax call.
	})(canvas, x, y); //End of worker thread.
} //End of updateGridGraphData

/** Updates all the graphs on page from info retrieved from the device-backend. */
function updateGridGraphs(){
	//update backendOnline.
	updateBackendOnline();
	var canvases = $("canvas");
	console.log(canvases.length);
	for(var i = 0; i< canvases.size(); i++){ //Loop through every canvas on the page.
		var c = canvases.get(i);
		var parent = c.parentElement;
		var grandparent = parent.parentElement;
		var col = $(grandparent).attr("data-sizex");
		var row = $(grandparent).attr("data-sizey");
		var id = c.id;
		var isVisible = $(c).is(":visible"); //may need to change this line to something else checking if it's hidden.
		if(isVisible){ //If the device is visible, draw it.
			var gridData = updateGridGraphData(c, col, row);
			//drawGridGraph(c, gridData, col, row);
		}
	}
	clearTimeout(gridGraphTimeOut); // Remove the timer and re-add it. Why?
	gridGraphTimeOut = setTimeout(updateGridGraphs, 5000);
} 

function updateBackendOnline(){
	(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
		postData = {getBackendRunning:true};
		 // Send the request to the server.
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/grid-backend.php', 
			success: function(result,status,xhr) {
				var jsonData = JSON.parse(result);
				if(jsonData['success']){ // we succedded
					if(jsonData['backendStatus'] == 'backendRunning'){
						$("#onlineDot").removeClass("dotOffline");
						$("#onlineDot").removeClass("dotUnknown");
						$("#onlineDot").addClass("dotOnline");
					}else{
						$("#onlineDot").removeClass("dotOnline");
						$("#onlineDot").removeClass("dotUnknown");
						$("#onlineDot").addClass("dotOffline");
					}
				}else{ //Not an admin, tell the user
					$("#onlineDot").removeClass("dotOnline");
					$("#onlineDot").removeClass("dotOffline");
					$("#onlineDot").addClass("dotUnknown");
				}				
				
			},
			error: function(xhr,status,error){
				$("#onlineDot").removeClass("dotOnline");
				$("#onlineDot").removeClass("dotOffline");
				$("#onlineDot").addClass("dotUnknown");
			}
		}); // End of ajax call.
	})(); //End of worker thread.
}
</script>  
	</body>
</html>
<?php else: header("Location: login.php"); die();	?>
<?php endif;?>
