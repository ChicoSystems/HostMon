<?php 
/**************************************************************
* Hostmon - login.php
* Author - Isaac Assegai
* Implements a secure login utilizing ajax to allow
* user to see error codes.
**************************************************************/
include_once("php/db.php");
include_once("php/functions.php");
session_start(); // Need to start the session, to check session variables.

if(isset($_GET['logout'])){ // User is logging out.
	session_unset(); // Session variables ended.
	session_destroy(); // Session ended.
}else if(isset($_POST['submit'])){ // User has clicked the submit button to login.
	if($_POST['submit']=='Login'){ // double check value
		$resp = Array(); // Used to send the response back to the user.
		if(!$_POST['username'] || !$_POST['password']){ // One of the fields are not filled in.
			$resp[] = 'ALL THE FIELDS MUST BE FILLED IN'; // Notification to user.
		}
		if(!count($resp)){ // If reponse hasn't been handled yet.
			$con = openDB(); // Make a MySQL Connection
			// Here we are querying the db for the username that comes with the md5 hashed password.
			$sql = "SELECT id,usr,admin_level FROM users WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'";
			$row = queryDB($con, $sql);
			if(isset($row['usr'])){ // The user with that password exists.
				$_SESSION['admin_level'] = $row['admin_level']; // Set the users admin level, from db.
	
				// If admin_level is 0 it means user has not been approved by admin yet
				if($_SESSION['admin_level'] == 0){
					$resp[]='ACCOUNT HAS NOT BEEN APPROVED'; // Error user will see.
				}else{ // Login has worked
					$_SESSION['loggedIn']=true;
					$_SESSION['id'] = $row['id'];
					$_SESSION['usr']=$row['usr'];
					$resp[]='Success '; // Ajax will look for this string.  
				}
			}else{ // A user with that user/pass combo does not exist in the db.
				$resp[]='WRONG USERNAME/PASSWORD '.print_r($row); // Error send to user.
			}
		}
	} // end of if login
	$response = implode(",", $resp); // Convert the response to a string. We could use JSON here.
	echo $response; // This is what the ajax call see's. This echo.
}
?>
<?php if(!isInstalledAlready()){header("Location: install/install.php"); die();}?>
<?php if(!isset($_POST['submit'])): // We don't want to send the whole file back to ajax ?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script src="js/jquery.tools.min.js"></script>
</head>
<body class='main-body'>
	<h5 class="version"><?php  echo getCurrentVersion(); ?></h5>
	<div class='login-form'>
		<h1>Hostmon</h1>
    	<div class="input-wrapper">
            <div class="img-wrapper"><img src="images/username2.png" /></div>
            <input type="text" name="username" placeholder="username" id="username" class="pictureInput" />
        </div>
        <div class="input-wrapper">
            <div class="img-wrapper"><img src="images/password2.png" /></div>
            <input type="password" name="password" placeholder="password" id="password" class="pictureInput">
        </div>
        <input type="hidden" name="submit" value="Login">
		<input type="submit" value="LOG IN" id="submit">
		<div id="error_msg"> <!-- The Error message will be displayed here. --></div>
	</div>
	<div class="ajax-spinner-bars"> <!-- The loading animation displayed on the submit button. -->
		<div class="bar-1"></div><div class="bar-2"></div><div class="bar-3"></div><div class="bar-4"></div>
		<div class="bar-5"></div><div class="bar-6"></div><div class="bar-7"></div><div class="bar-8"></div>
		<div class="bar-9"></div><div class="bar-10"></div><div class="bar-11"></div><div class="bar-12"></div>
		<div class="bar-13"></div><div class="bar-14"></div><div class="bar-15"></div><div class="bar-16"></div>
	</div>


<script type="text/javascript">
/** Event called when document is loaded. */
$(document).ready(function() {

	//If user presses enter, we want to click submit button
	$('#password').keypress(function(e){
		if(e.which == 13){
			jQuery('#submit').focus().click();
		}
	});
	

	// Register an event listener on the submit id. 
	$("#submit").click(function(){
		var buttontext = $("#submit").val();
		$("#submit").val("");
		$(".ajax-spinner-bars").show();
		$("#error_msg").fadeOut();
		username = $("#username").val(); 
		password = $("#password").val(); 
		remember = $("#remember").val();
		
		// Post to login-backend.php to see if this is a good login.
		$.post("login.php",{
			submit:"Login",
			username:username,
			password:password, // We may want to take and hash this value.
			remember:remember
    },
    function(data,status){ // Receiving data back from login-backend.php
		if(data.indexOf("Success") != -1){
			var pos = data.indexOf("Success");
			pos += 8; //account for the word success, and the space that will be after it.
			var userName = data.substring(pos);
			//we don't really need this here if we change the corresponding check in grid.php, sessions take care of it.
			window.location.href = "grid.php?login=true"; 
		}else{
			$("#error_msg").text(data); 
			$("#error_msg").fadeIn();
			$(".ajax-spinner-bars").hide();
			$("#submit").val(buttontext);
		}
    }); //End function
	}); // Submit Listener

	// Check if JavaScript is enabled
	$('body').addClass('js');
 
	// Make the checkbox checked on load
	$('.login-form span').addClass('checked').children('input').attr('checked', true);

	// Click function
	$('.login-form span').on('click', function() {
		if($(this).children('input').attr('checked')) {
			$(this).children('input').attr('checked', false);
			$(this).removeClass('checked');
		}else{
			$(this).children('input').attr('checked', true);
			$(this).addClass('checked');
		}
	}); // End login-form span click listener.
});
</script>
</body>
</html>
<?php endif;?>
