<?php
//We need functions.php and db.php so we can set the alarm file in the db
include_once("db.php");
include_once("functions.php");
$output_dir = "../alarms/";
if(isset($_FILES["myfile"]))
{
	
error_log("myfile: ".$_FILES["myfile"]["name"]);
	//echo "myfile is set";
	$ret = array();
	
//	This is for custom errors;	
/*	$custom_error= array();
	$custom_error['jquery-upload-file-error']="File already exists";
	echo json_encode($custom_error);
	die();
*/
	$error =$_FILES["myfile"]["error"];
	//You need to handle  both cases
	//If Any browser does not support serializing of multiple files using FormData() 
	if(!is_array($_FILES["myfile"]["name"])) //single file
	{
 	 	$fileName = $_FILES["myfile"]["name"];
 		move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$fileName);
		if(isset($_POST["alarmType"])){
	//		echo "alarmType is set";
			setAlarm($_POST["alarmType"], $fileName);
			error_log("alarmType: ".$_POST["alarmType"]);
		}else{
			error_log("alarmType is not set");
	//		echo "alarmType is not set";
		}
    	$ret[]= $fileName;
	}
	else  //Multiple files, file[]
	{
	  $fileCount = count($_FILES["myfile"]["name"]);
	  for($i=0; $i < $fileCount; $i++)
	  {
	  	$fileName = $_FILES["myfile"]["name"][$i];
		move_uploaded_file($_FILES["myfile"]["tmp_name"][$i],$output_dir.$fileName);
	  	$ret[]= $fileName;
	  }
	
	}
    echo json_encode($ret);
 }else{
	error_log("myfile doesn't exist in fileupload.php");
	//echo "myfile is not set";
}
 ?>
