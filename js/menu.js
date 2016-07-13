/**
 * Used by the slide out menu
 */
var menuTimeout; // Will control if we are updating menu or not.
var getBackendRunningTimeout;
var focusedMenuItemName = 'none';
var buttonLocked;
var redMuted = false;
var redslider;
var yellowMuted = false;
var slider;
var menuOpen = false;

//Event Handler called when document is first loaded.
$(document).ready(function() {
	
	
	//Event Handler when a user clicks on the menu. Opens the menu.
	$('.menu').click(function() {
		setMenuConfigInfo();
		getBackendRunningTimeout = setTimeout(updateBackendRunning, 5000); //retrieve every 5 secs
		//menuTimeout = setTimeout(setMenuConfigInfo, 5000);
		$('nav').addClass('open');
		$('body').addClass('menu-open');
		menuOpen = true;
		//If tour is running, have it advance when menu is open.
		if(!tour.ended())tour.next();
		return false;
	});

	//Event Handler when a user clicks anywhere but the menu, when the menu is open. Closes the menu.
	$(".grid").click(function() {
		if(menuOpen){
			//the following code is repeated here and in grid.php
			console.log("close menu");
			clearTimeout(getBackendRunningTimeout); // Remove the timer.
			clearTimeout(menuTimeout); // Remove the timer.
			$('body').removeClass('menu-open');
			$('nav').removeClass('open');
			$(".ajax-file-upload-container").fadeOut(); //cause upload messages to disappear
			$("#eventsmessage").fadeOut(); //cause upload messages to disappear
			menuOpen = false;
		}
	});
	
	//We need to check the class of id stopStartButton. and set the buttons text
	buttonLocked = false; // don't allow ajax calls to be sent if buttonLock is true
	var buttonClass = $("#stopStartButton").attr('class');
	var newButtonText = (buttonClass == 'backendRunning' ? 'STOP' : 'START');
	var newErrorText = (buttonClass == 'backendRunning' ? 'Backend Running' : 'Backend Stopped');
	$("#stopStartButton").text(newButtonText);
	$("#stopStartLabel").text(newButtonText + " Backend");
	$(".startBackendErrorOutput").text(newErrorText);


	 /*setup alarms*/
        redAudioElement = document.createElement('audio');
        redAudioElement.setAttribute('src', 'alarms/'+redAlarm);
        redAudioElement.setAttribute('preload', 'preload');
        redAudioElement.setAttribute('id', 'redAudioElement');

        yellowAudioElement = document.createElement('audio');
        yellowAudioElement.setAttribute('src', 'alarms/'+yellowAlarm);
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

	 $('.version').click(function(){
                tour.restart();
                yellowAudioElement.play();
        });


	//When the volume icon is clicked it toggles that alarms mute
	$(".redvolume").click(function() {
		if(redMuted){
			console.log("unmute red Volume");
			redMuted = false;
			redAudioElement.volume = (redslider.slider('value') / 100);
			$("#redMute").hide();	
		}else{
			console.log("mute red volume");
			redMuted = true;
			redAudioElement.volume = 0;	
			$("#redMute").show();
		}		

	});

	 $(".yellowvolume").click(function() {
                if(yellowMuted){
                        console.log("unmute yellow Volume");
                        yellowMuted = false;
                        yellowAudioElement.volume = (slider.slider('value') / 100);
                        $("#yellowMute").hide();
                }else{
                        console.log("mute yellow volume");
                        yellowMuted = true;
                        yellowAudioElement.volume = 0;
                        $("#yellowMute").show();
                }

        });



	/* Toggle between adding and removing the "active" and "show" classes when the user 
 	 * clicks on one of the "Section" buttons. The "active" class is used to add a
 	 * background color to the current button when its belonging panel is open. 
 	 * The "show" class is used to open the specific accordion panel */
	var acc = document.getElementsByClassName("accordion");
	var i;

	for (i = 0; i < acc.length; i++) {
	    acc[i].onclick = function(){
	        this.classList.toggle("active");
	        this.nextElementSibling.classList.toggle("show");
	    }
	}
	
$("#yellowuploader").uploadFile({
        url:"php/uploadFile.php",
        acceptFiles: "audio/*",
        fileName:"myfile",
	formData: {alarmType: "yellow"},
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
		yellowAlarm = files.toString();
        	yellowAudioElement.setAttribute('src', 'alarms/'+yellowAlarm);
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
	formData: {alarmType: "red"},
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
        	redAlarm = files.toString();
		redAudioElement.setAttribute('src', 'alarms/'+redAlarm);
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


	//Setup the volume control functions
	$(function() {

		redslider = $('#redslider'),
                redtooltip = $('.redtooltip');
                redtooltip.hide();
                redslider.slider({
                        range: "min",
                        min: 0,
                        max: 100,
                        step: 1,
                        value: 35,
                        start: function(event,ui) {
                                redtooltip.fadeIn('fast');
                        },
                        slide: function(event, ui) {
                                var value = redslider.slider('value'),
                                redvolume = $('.redvolume');
                                redtooltip.css('left', value*3).text(ui.value);
                                if(value <= 5) {
                                        redvolume.css('background-position', '0 0');
                                }else if (value <= 25) {
                                        redvolume.css('background-position', '0 -25px');
                                }else if (value <= 75) {
                                        redvolume.css('background-position', '0 -50px');
                                }else{
                                       redvolume.css('background-position', '0 -75px');
                                };
                        },

                        stop: function(event,ui) {
                                redtooltip.fadeOut('fast');
                                redAudioElement.volume = (redslider.slider('value') / 100);
                                console.log("Red Alarm Volume: " + redslider.slider('value'));
                        },
                });


		slider = $('#yellowslider'),
		yellowtooltip = $('.yellowtooltip');
		yellowtooltip.hide();
		slider.slider({
			range: "min",
			min: 0,
			max: 100,
			step: 1,
			value: 35,
			start: function(event,ui) {
				yellowtooltip.fadeIn('fast');
			},
 			slide: function(event, ui) {
				var value = slider.slider('value'),
				volume = $('.yellowvolume');
				yellowtooltip.css('left', value*3).text(ui.value);
				if(value <= 5) { 
					volume.css('background-position', '0 0');
				}else if (value <= 25) {
					volume.css('background-position', '0 -25px');
				}else if (value <= 75) {
					volume.css('background-position', '0 -50px');
				}else{
					volume.css('background-position', '0 -75px');
				};
			},
 
			stop: function(event,ui) {
				yellowtooltip.fadeOut('fast');
				yellowAudioElement.volume = (slider.slider('value') / 100);
				console.log("Yellow Alarm Volume: " + slider.slider('value'));	
			},
		});
 
	});
});	

/** Ajax call used to put the current backend status, "backendRunning" or "backendStopped
 * 	in the startStopButton class" */
function updateBackendRunning(){
	clearTimeout(getBackendRunningTimeout); // Remove the timer.
	(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
		postData = {getBackendRunning:true};
		 // Send the request to the server.
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/menu-backend.php', 
			success: function(result,status,xhr) {
				var jsonData = JSON.parse(result);
				if(jsonData['success']){ // we succedded
					var newButtonText = (jsonData['backendStatus'] == 'backendRunning' ? 'STOP' : 'START');
					var newErrorText = (jsonData['backendStatus'] == 'backendRunning' ? 'Backend Running' : 'Backend Stopped');
					
					$("#stopStartButton").removeClass("backendRunning");
					$("#stopStartButton").removeClass("backendStopped");
					$("#stopStartButton").addClass(jsonData['backendStatus']);
					$("#stopStartButton").text(newButtonText);
					$("#stopStartLabel").text(newButtonText + " Backend");
					$(".startBackendErrorOutput").text(newErrorText);
					$(".startBackendErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}else{ //Not an admin, tell the user
					$(".startBackendErrorOutput").text(jsonData['err']);
					$(".startBackendErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}
				
			},
			error: function(xhr,status,error){
				$(".startBackendErrorOutput").text("Error in updateBackendRunning ajax call.");
			}
		}); // End of ajax call.
	})(); //End of worker thread.
	getBackendRunningTimeout = setTimeout(updateBackendRunning, 5000);
}

/** Will send an ajax call to stop the backend if it is already started,
 *  or start the backend if it is stopped.
 */
function stopStartBackend(){
	var buttonClass = $("#stopStartButton").attr('class');
	//tells us if our backend is already running based on button class.
	var backendRunning = (buttonClass == 'backendRunning' ? true : false); 
	if(!buttonLocked){ // We don't want to allow the user to continuously push a dangerous button.
		buttonLocked = true; // start lock
		(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
			var startOrStop = (buttonClass == 'backendRunning' ? 'stop' : 'start'); 
			postData = {startStopBackend:startOrStop};
			 // Send the request to the server.
			$.ajax({
				type:"POST",
				data : postData,
				url: 'php/menu-backend.php', 
				success: function(result,status,xhr) {
					var jsonData = JSON.parse(result);
					$("#stopStartButton").text(jsonData['newButtonVal']);
					$("#stopStartButton").toggleClass('backendRunning'); // change the button class
					$("#stopStartButton").toggleClass('backendStopped');
					$("#stopStartLabel").text(jsonData['newButtonVal'] + " Backend");
					$('.startBackendErrorOutput').text(jsonData['returnVal']); // Report back
					$(".startBackendErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				error: function(xhr,status,error){
					$(".startBackendErrorOutput").text("Error in ajax call.");
					$(".startBackendErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}
			}); // End of ajax call.
		})(); //End of worker thread.
	}else{ // the button is currently locked
		$(".startBackendErrorOutput").text("Don't Spam This Button."); //Scold the admin
	}
	setTimeout(clearButtonLock, 5000);
}

/** sets the buttonLocked variable to false. */
function clearButtonLock(){
	$(".startBackendErrorOutput").text("-"); // set text back to default when lock is reset
	buttonLocked = false; //stop lock
}

// A Set button was pressed on the config menu, we are setting that value in the db here.
function setConfigValue(configToSet){
	var newVal = $('.'+configToSet).val();
	//alert("setConfigValue: " + configToSet + " " + newVal);
	(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
		postData = {setConfigValue:true,
					name:configToSet,
					value:newVal};
		 // Send the request to the server.
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/menu-backend.php', 
			success: function(result,status,xhr) {
				//setMenuData(result); we might not need to do anything at all if successful.
				setMenuItemDisplay(result);
			},
			complete: function(result) {
				// Schedule the next request when the current one's complete
				//alert("complete" + result);
				//setMenuItemDisplay(result);
				
			},
			error: function(xhr,status,error){
				alert("error" + error);
			}
		}); // End of ajax call.
	})(); //End of worker thread.
}

/** Parses a single config value from json and updates the display on the menu. */
function setMenuItemDisplay(data){
	var jsonData = JSON.parse(data);
	var name = jsonData.name;
	var value = jsonData.value;
	$('.' + name).val(value);
	$('.' + name).stop().css("background-color", "#3366FF")
    	.animate({ backgroundColor: "#FFFFFF"}, 1500); // Flashes a new color to the value changed.
	//alert("menu item displayed");
}

/** Sets the menu's specific menu items, parses with json. */
function setMenuData(data){
	var jsonData = JSON.parse(data);
	for(var i = 0; i < jsonData.length; i++){
		var nameSelector = jsonData[i].name;
		var value = $(' '+jsonData[i].value);
		var description = $(' '+jsonData[i].description);
		if(nameSelector != focusedMenuItemName){ // Do not update the displayed value, if that input is focused.
			$('.'+nameSelector).val(value.selector);
		}
	}	
}

/** Retrieves the several menu config settings
 *  from the backend through a ajax and php db call.
 *  populates the values in the menu.
 */
function setMenuConfigInfo(norepeat){ // Called by grid.php and device.php document ready.
	//alert("setMenuConfigInfo");
	(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
		postData = {getConfigData:true};
		 // Send the request to the server.
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/menu-backend.php', 
			success: function(result,status,xhr) {
				setMenuData(result);
			},
			complete: function(result) {
				// Schedule the next request when the current one's complete
				//alert("complete" + result);
			},
			error: function(xhr,status,error){
				alert("error" + error);
			}
		}); // End of ajax call.
	})(); //End of worker thread.
	if(!norepeat) menuTimeout = setTimeout(setMenuConfigInfo, 5000);
}

/** Focus handler for menu input items. Needed to disable race condition.
  *  between timer updates, and human input.
  * @param inputName The class name of the input we are focused on.
  */
function setMenuInputFocusIn(x){
	//alert(inputName + " has focus.");
	if($(x).is(":focus")){ // due to cross browswer difficulties, we need to check if focused or nto.
		focusedMenuItemName = $(x).attr('class');
		x.style.background = "yellow";
	}else{
		setMenuInputFocusOut(x); //if not we call this handler.
	}
	
}

/** Focus handler for menu input items. Needed to disable race condition.
 *  between timer updates, and human input.
 * @param x The Dom Object that lost focus.
 */
function setMenuInputFocusOut(x){
		//console.log(x);
		focusedMenuItemName = 'none';
		x.style.background = "white";
}

/** Called When the users presses the password set button. */
function changePassword(){
	var pass1 = $('.changePassword1').val();
	var pass2 = $('.changePassword2').val();
	if(pass1 == '' || pass2 == ''){
		$('.errorOutput').text("Both Fields Must Contain a Password.");
		$(".errorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else if(pass1 != pass2){
		$('.errorOutput').text("Passwords Don't Match.");
		$(".errorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else{
		var newPassword = $.md5(pass1);
		$('.errorOutput').text("Changing Password");
		$(".errorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
		(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
			postData = {changePassword:true,
						newPass:newPassword};
			 // Send the request to the server.
			$.ajax({
				type:"POST",
				data : postData,
				url: 'php/menu-backend.php', 
				success: function(result,status,xhr) {
					$('.errorOutput').text("Password Changed.");
					$(".errorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				complete: function(result) {
					//$('.errorOutput').text("Password Changed.");
				},
				error: function(xhr,status,error){
					$('.errorOutput').text("Error Changing Password.");
					$(".errorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}
			}); // End of ajax call.
		})(); //End of worker thread.
	}
}

/** Called by the menu when the admin adds a new user. */
function addNewUser(){
	var userName = $('.newUserName').val();
	var pass = $('.newUserPass').val();
	pass = $.md5(pass);
	var adminLvl = $('.adminLvl').val();
	
	// Can't add a user if any field is empty
	if(userName == '' || pass == '' || adminLvl == ''){ 
		$('.newUserErrorOutput').text("All fields must be filled.");
		$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else if(isNaN(adminLvl)){
		$('.newUserErrorOutput').text("Admin lvl must be between 0 and 10, inclusive.");
		$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else if(parseInt(adminLvl) > 10 || parseInt(adminLvl) < 0){
		$('.newUserErrorOutput').text("Admin lvl must be between 0 and 10, inclusive.");
		$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else{
		//$('.newUserErrorOutput').text("Adding user " + userName + " " + pass + " " + adminLvl);
		(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
			postData = {addUser:true,
						newUserName:userName,
						newPassword:pass,
						newAdminLvl:adminLvl};
			 // Send the request to the server.
			$.ajax({
				type:"POST",
				data : postData,
				url: 'php/menu-backend.php', 
				success: function(result,status,xhr) {
					var jsonData = JSON.parse(result);
					$('.newUserErrorOutput').text(jsonData['returnVal']);
					$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				complete: function(result) {
					var jsonData = JSON.parse(result);
					$('.newUserErrorOutput').text(jsonData['returnVal']);
					$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				error: function(xhr,status,error){
					$('.newUserErrorOutput').text("Error Adding User.");
					$(".newUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}
			}); // End of ajax call.
		})(); //End of worker thread.
		
	}
}

/** Called by frontend when admin removes a user. */
function removeUser(){
	var userName = $('.removeUsername').val();
	if(userName == ''){
		$('.removeUserErrorOutput').text("Username is Blank!");
		$(".removeUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
	}else{
		$('.removeUserErrorOutput').text("Removing User " + userName);
		$(".removeUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
		(function worker() { // Start a worker thread to grab the data so we don't freeze anything on our page.
			postData = {removeUser:true,
						removeUsername:userName};
			 // Send the request to the server.
			$.ajax({
				type:"POST",
				data : postData,
				url: 'php/menu-backend.php', 
				success: function(result,status,xhr) {
					var jsonData = JSON.parse(result);
					$('.removeUserErrorOutput').text(jsonData['returnVal']);
					$(".removeUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				complete: function(result) {
					var jsonData = JSON.parse(result);
					$('.removeUserErrorOutput').text(jsonData['returnVal']);
					$(".removeUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				},
				error: function(xhr,status,error){
					$('.removeUserErrorOutput').text("Error Removing.");
					$(".removeUserErrorOutput").stop().css("color", "#FFFFFF").animate({ color: "#FF0000"}, 1500);
				}
			}); // End of ajax call.
		})(); //End of worker thread.
	}
}
