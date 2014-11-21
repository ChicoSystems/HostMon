/**
 * Used by the slide out menu
 */
var menuTimeout; // Will control if we are updating menu or not.

//Event Handler when a user clicks on the menu. Opens the menu.
$('.menu').click(function() {
	setMenuConfigInfo();
	//menuTimeout = setTimeout(setMenuConfigInfo, 5000);
	$('nav').addClass('open');
	$('body').addClass('menu-open');
	return false;
});

//Event Handler when a user clicks anywhere but the menu, when the menu is open. Closes the menu.
$(".grid").click(function() {
	clearTimeout(menuTimeout); // Remove the timer.
	$('body').removeClass('menu-open');
	$('nav').removeClass('open');
});

// A Set button was pressed on the config menu, we are setting that value in the db here.
function setConfigValue(configToSet){
	var newVal = $('.'+configToSet).val();
	alert("setConfigValue: " + configToSet + " " + newVal);
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
				//setMenuData(result); we might not need to do anything at all if sucessful.
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
}

/** Sets the menu's specific menu items, parses with json. */
function setMenuData(data){
	var jsonData = JSON.parse(data);
	for(var i = 0; i < jsonData.length; i++){
		var nameSelector = jsonData[i].name;
		var value = $(' '+jsonData[i].value);
		var description = $(' '+jsonData[i].description);
		$('.'+nameSelector).val(value.selector);
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