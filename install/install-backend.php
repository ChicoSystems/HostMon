<?php 
//echo getcwd();

include_once("../php/db.php");
include_once("../php/functions.php");

error_reporting(0);


/*
$_POST['install'] = true;
$_POST['type'] = 'user';
$_POST['dbName'] = 'hostmon';
$_POST['dbAddress'] = '127.0.0.1';
$_POST['dbUsername'] = 'HostMonUser2';
$_POST['dbPassword'] = 'Micheal1';
$_POST['adminUsername'] = 'qwer';
$_POST['adminPassword'] = '962012d09b8170d912f0669f6d7d9d07';
*/

/*
$_POST['adminUsername'] = 'root';
$_POST['adminPassword'] = 'testpass';
$_POST['checkAdminDB'] = true;
*/

/*
$_POST['install'] = true;
$_POST['type'] = 'admin';
$_POST['SQLadminUsername'] = 'root';
$_POST['SQLadminPassword'] = 'Micheal1123581';
$_POST['SQLaddress'] = '127.0.0.1';
$_POST['adminDBName'] = 'Hostmon2';
$_POST['adminUsername'] = 'qwer';
$_POST['adminPassword'] = '962012d09b8170d912f0669f6d7d9d07';
*/

if(isset($_POST['checkAdminDB'])){
	$ajaxReturnVal = array();
	$errorNum = install_testAdminDB($_POST['SQLaddress'], $_POST['SQLadminUsername'], $_POST['SQLadminPassword']);
        $errorMsg = getMySQLErrorMessageFromNum($errorNum);

        //echo ":".$errorNum.":";
        if($errorNum == 0 || $errorNum == 1){
                $ajaxReturnVal['success'] = true;
        }else{
                $ajaxReturnVal['success'] = false;
        }

        $ajaxReturnVal['errorType'] = getMySQLErrorTypeFromNum($errorNum);
        $ajaxReturnVal['errorMessage'] = $errorMsg;
	$ajaxReturnVal['errorNum'] = $errorNum;
        $ajaxReturnVal = json_encode($ajaxReturnVal);

        echo $ajaxReturnVal;

}else if(isset($_POST['checkDB'])){
	$ajaxReturnVal = array();
	$errorNum = install_testDB();
	$errorMsg = getMySQLErrorMessageFromNum($errorNum);
	
	//echo ":".$errorNum.":";
	if($errorNum == 0){
		$ajaxReturnVal['success'] = true;
	}else{
		$ajaxReturnVal['success'] = false;
	}

	$ajaxReturnVal['errorType'] = getMySQLErrorTypeFromNum($errorNum);
	$ajaxReturnVal['errorMessage'] = $errorMsg;
	$ajaxReturnVal = json_encode($ajaxReturnVal);

	echo $ajaxReturnVal;
}else if(isset($_POST['install'])){
	/**
	  * Checks if we are doing an admin or user install
	  * If it is a user install we are assuming that a
	  * DB and DB user with appropriate permissions
	  * have already been created. In this case we will:
	  * 1. Save the settings to connect to the db in db.cfg
	  * 2. Setup the initial hostmon admin account in the db.
	  * 3. Configure File Permissions.
	  * 4. Notify the DB that install is complete. Allowing
	  *    the app to be used.
	  * IF type = admin is chosen then we will:
	  * 1. Create a DB with the Given Name
	  * 2. Create a new DB user with all permissions on the DB.
	  * 3. Populate DB tables using new DB user.
	  * 4. Then we must do everything in the usermod 1-4.
	  */	
	$ajaxReturnVal = array();

	//Code executed for admin mode, before user mode.
	if($_POST['type'] == 'admin'){
		//echo "admin Mode";
		$address = $_POST['SQLaddress'];
		$dbName = $_POST['adminDBName'];
		$sqlAdmin = $_POST['SQLadminUsername'];
		$sqlPass = $_POST['SQLadminPassword'];
		$dbUser = $_POST['dbUser'];
		$dbPass = $_POST['dbPass'];

		$errorNum = install_testAdminDB($address, $sqlAdmin, $sqlPass);
		//echo " testAdminDB: ".$errorNum;

		if($errorNum == 1){//DB is creatable with these creds.
			$errorNum = createNewDB($address, $dbName, $sqlAdmin, $sqlPass);
			//echo " createdNewDB: ".$errorNum;
			$newUsername = $dbUser;//substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
			$newPass = $dbPass; //substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
			$errorNum = createNewSQLUser($address, $dbName, $newUsername, $newPass, $sqlAdmin, $sqlPass);
			//echo "error: ".$errorNum;
			
			$errorNum = givePrivilegesToSQLUser($address, $dbName, $newUsername, $newPass, $sqlAdmin, $sqlPass);

			$errorNum = populateNewDB($address, $dbName, $newUsername, $newPass);
			//We don't set ajax return values here, because it will fall through
			//and be set in the user part.
		}else{
			$ajaxReturnVal['success'] = 'false';
			$ajaxReturnVal['errorMessage'] = 'Could not Create New DB.';
		}
	}

	if($_POST['type'] == 'admin'){
		$addr = $address;
		$user = $newUsername;
		$pass = $newPass;
		$dName = $dbName;
	}else{
		$addr = $_POST['dbAddress'];
		$user = $_POST['dbUsername'];
		$pass = $_POST['dbPassword'];
		$dName = $_POST['dbName'];
	}
	//Code executed for both admin and user mode
	$errorNum = install_testDB($addr, $user, $pass, $dName);
	$errorMsg = getMySQLErrorMessageFromNum($errorNum);
	if($errorNum == 0){//if there is no problem.
		recordDBSettings($dName, $user, $pass, $addr);
		if(setupAdminAccount()){
			error_log("admin Account Is SETUP");
			if(configureFilePermissions()){
				setDBInstalled();
				$ajaxReturnVal['success'] = 'true';
				$ajaxReturnVal['errorMessage'] = 'Install A Success.';
			}else{
				$ajaxReturnVal['success'] = 'false';
				$ajaxReturnVal['errorMessage'] = 'Problem configuring file permissions.';
			}
		}else{
			$ajaxReturnVal['success'] = 'false';
			$ajaxReturnVal['errorMessage'] = 'Problem Setting up Admin Account.';
		}
	}else{
		$ajaxReturnVal['success'] = 'false';
		$ajaxReturnVal['errorMessage'] = $errorMsg;
	}


	$ajaxReturnVal = json_encode($ajaxReturnVal);
	echo $ajaxReturnVal;


}

/**
 * 
 * @param String $addess
 * @param String $dbName
 * @param String $user
 * @param String $pass
 */
function populateNewDB($address, $dbName, $user, $pass){
	$errorNum = 0;
	$con = new mysqli($address, $user, $pass);
	if (!$con) {
		$errorNum = 666;
	}else{
		mysqli_select_db($con, $dbName);
		$dbms_schema = '../cfg/hostmon.sql';
		$fileSize = @filesize($dbms_schema);
		$sql_query = @fread(@fopen($dbms_schema, 'r'), $fileSize) or die('problem ');
		$sql_query = remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, ';');
		$i=1;
		foreach($sql_query as $sql){
			$i++;
			$result = mysqli_query($con, $sql);
		}
		
	}
	
	if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();
	
	//echo "errorNum: ".$errorNum;
	return $errorNum;

}

/** Tells the db that installation has completed. */
function setDBInstalled(){
	$con = openDB();
	$sql = "UPDATE `configuration` SET `value` = '1' WHERE `configuration`.`name` = 'installed';";
	$result = mysqli_query($con,$sql);
}

/** 
  * setupAdminAccount(): Returns an SQL Result
  * Uses: $_POST['adminUsername'] and $_POST['adminPassword']
  * Checks the DB to see if referenced username already exists
  * if it doesn't it creates a hostmon user with admin level 10
  * using the referenced username and password.
  */
function setupAdminAccount(){
	$result = false;
	$accountExists = false;
	$con = openDB();
	$dbOptions = getDBOptions();
	$adminUsername = mysqli_real_escape_string($con, $_POST['adminUsername']); // Discourage some hackers.
	mysqli_select_db($con, $dbOptions["DB"]);
	$sql = "SELECT * FROM `users` WHERE `users`.`admin_level` = '10' AND `users`.`usr` = '".$adminUsername."';";
	$result = mysqli_query($con,$sql);
	while($row = mysqli_fetch_array($result)) {
		array_push($array_result, $row);
	}
	if(empty($array_result)){
		$result = false;
	}else{
		$result = true;
	}
	
	if(!$accountExists){
		// create account here
		$adminUsername = mysqli_real_escape_string($con, $_POST['adminUsername']); // Discourage some hackers.
		$adminPassword = mysqli_real_escape_string($con, $_POST['adminPassword']); // Discourage some hackers.
		$sql = "INSERT INTO `".$dbOptions["DB"]."`.`users` (`usr`, `admin_level`, `pass`) VALUES ('".$adminUsername."', '10', '".$adminPassword."');";
		$result = mysqli_query($con,$sql);
	}else{
		$result = false;
	}
	return $result;
}

/** Will record the db settings into cfg/db.cfg */
function recordDBSettings($dName, $user, $pass, $addr){
        $ajaxReturnVal['errorMessage'] = "Unable to open cfg/db.cfg, problem with permission?";
        $ajaxReturnVal = json_encode($ajaxReturnVal);
    $cwd = getcwd();
	//$myfile = fopen("../cfg/db.cfg", "w") or die($ajaxReturnVal);
    $myfile = fopen("../cfg/db.cfg", "w");
	$txt = "DB:".$dName.";\n";
	fwrite($myfile, $txt);
	
	$txt = "USER:".$user.";\n";
	fwrite($myfile, $txt);
	
	$txt = "PASS:".$pass.";\n";
	fwrite($myfile, $txt);
	
	$txt = "IP:".$addr.";\n";
	fwrite($myfile, $txt);
	
	fclose($myfile);
}

/** Configurs the file permissions for the app, making sure web users can't access
 *  secure files. Web users shouldn't be able to read cfg file or install files.
 */
function configureFilePermissions(){
	error_log("configureFilePermissions() 1");
	$currentUser = get_current_user();
	//echo "current user is: ".$currentUser;
	//chown('../install/*', 'asterisk');
	//chgrp('../install/*', 'root');

	/*
	$chmodSuccess;
	$chmodSuccess=chmod("../alarms/", 0777);
	$chmodSuccess=chmod("../css/", 0755);
	$chmodSuccess=chmod("../images/", 0755);
	$chmodSuccess=chmod("../backend/", 0755);
	$chmodSuccess=chmod("../js/", 0755);
	$chmodSuccess=chmod("../php/", 0755);
	$chmodSuccess=chmod("../test/", 0440);
	$chmodSuccess=chmod("../cfg", 0600);
	*/

	error_log("configureFilePermissions() 2");
	chchange("../alarms/", 0770, $currentUser);
	error_log("configureFilePermissions() 3");
	chchange("../css/", 0540, $currentUser);
	error_log("configureFilePermissions() 4");
	chchange("../images/", 0540, $currentUser);
	error_log("configureFilePermissions() 5");
	chchange("../backend/", 0540, $currentUser);
	error_log("configureFilePermissions() 6");
	chchange("../js/", 0540, $currentUser);
	error_log("configureFilePermissions() 7");
	chchange("../php/", 0540, $currentUser);
	error_log("configureFilePermissions() 8");
	chchange("../cfg/", 0500, $currentUser);
	error_log("configureFilePermissions() 9");

	chmod_r("../install/", 0000);
	//chown_r("../install/", 'asterisk');
	error_log("configureFilePermissions() ending");
	return true;
}

/**
  * void chchange(String $path, OCT $val, String $name);
  * Input: A Path or File Name, and a username, and
  * an octal value representing permissions.
  * This will recursively chmod, chown and chgrp
  * every file and folder under this path.
  */
function chchange($path, $val, $name){
	
	chown_r($path, $name);
	chmod_r($path, $val);
	chgrp_r($path, $name);
}

/**
  * void chgrp_r(String $path, String $name);
  * Input: A Path or File Name, and a username
  * from the OS to make its group own that file.
  */
function chgrp_r($path, $name){
	$dir = new DirectoryIterator($path);
	foreach($dir as $item){
		chgrp($item->getPathname(), $name);
		if($item->isDir() && !$item->isDot()){
			chgrp_r($item->getPathname(), $name);
		}
	}
}

/**
  * void chown_r(String $path, String $name);
  * Input: A path or file name and a username
  * from the OS to make own that file.
  */
function chown_r($path, $name){
	$dir = new DirectoryIterator($path);
	foreach($dir as $item){
		//console.log("chown: ".$item->getPathname(), 1);
		$n = $item->getPathname();
		chown($item->getPathname(), $name);
		if($item->isDir() && !$item->isDot()){
			chown_r($item->getPathname(), $name);
		}
	}
}

/**
  * void chmod_r(String $path, OCT $val);
  * Input: A Path Name, or Filename, and 
  * an octal value representing unix file permissions.
  * chmods a file, or a directory and
  * ALL files under that directory.
  */
function chmod_r($path, $val) {
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        chmod($item->getPathname(), $val);
        if ($item->isDir() && !$item->isDot()) {
            chmod_r($item->getPathname(), $val);
        }
    }
}

/** Tests if the db is installed and available with the supplied credentials.
  * if it works it returns 0, if it fails it returns the errornumber. */
function install_testDB($dbAddress, $dbUser, $dbPass, $dbName){
	//echo "install_testDB(): ".$_POST['dbAddress']." ".$_POST['dbUsername']." ".$_POST['dbPassword']." ".$_POST['dbName'];
/** checks to see if the admin account is already set up, otherwise sets it up. */
	$con = mysqli_connect($dbAddress, $dbUser, $dbPass, $dbName);
	if (!$con) {
		//echo "failed";
		$returnVal = false;
	}else{
		//echo "didn'tfail";
		$returnVal = true;
	}
	$errorNum = 0;
	if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();

	//echo "errorNum: ".$errorNum;	
	return $errorNum;
}

function install_testAdminDB($address, $sqlAdmin, $sqlPass){
	$errorNum = 0;
        $con = new mysqli($address, $sqlAdmin, $sqlPass);
	if (!$con) {
                $errorNum = 666;
        }else{
		$result = mysqli_query($con, 'CREATE DATABASE hostmonTest');
		if(!$result){
			//failed to creaate db
			//echo "failed to create db";
			$errorNum = 666;
		}else{
			//echo "created db";
			$errorNum = 1;
			mysqli_query($con, 'DROP DATABASE hostmonTest');
		}
       }

        if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();

        //echo "errorNum: ".$errorNum;
        return $errorNum;
}

function givePrivilegesToSQLUser($address, $dbName, $user, $pass, $sqlAdmin, $sqlPass){
	 $errorNum = 0;
        $con = new mysqli($address, $sqlAdmin, $sqlPass);
        if(!$con){
                $errorNum = false;
        }else{
              $sql = "GRANT ALL PRIVILEGES ON ".$dbName.".* TO '".$user."'@'localhost'";
              $result = mysqli_query($con, $sql);
              //echo 'result2: '.$sql;
                if(!$result){
                        //failed to creaate db
                        //echo "failed to create db";
                        $errorNum = 666;
                }else{
                        //echo "created db";
                        $errorNum = 1;
                }

        }
        if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();
        return $errorNum;

}

function createNewSQLUser($address, $dbName, $user, $pass, $sqlAdmin, $sqlPass){
	// echo " creating new sql user: ".$address." ".$user." ".$pass;
        $errorNum = 0;
        $con = new mysqli($address, $sqlAdmin, $sqlPass);
        if(!$con){
                $errorNum = false;
        }else{
		$sql = "CREATE USER '".$user."'@'localhost' IDENTIFIED BY '".$pass."'";
                $result = mysqli_query($con, $sql);
		//echo 'result1: '.$result;
//		$sql = "GRANT ALL PRIVILEGES ON ".$dbName." TO '".$user."'@'localhost'";
  //              $result = mysqli_query($con, $sql);
//		echo 'result2: '.$sql;
                if(!$result){
                        //failed to creaate db
                        //echo "failed to create db";
                        $errorNum = 666;
                }else{
                        //echo "created db";
                        $errorNum = 1;
                }

        }
        if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();
        return $errorNum;

}

function createNewDB($address, $dbName, $sqlAdmin, $sqlPass){
	$errorNum = 0;
	$con = new mysqli($address, $sqlAdmin, $sqlPass);
	if(!$con){
		$errorNum = 666;
	}else{
		$result = mysqli_query($con, 'CREATE DATABASE '.$dbName);
                if(!$result){
                        //failed to creaate db
                        //echo "failed to create db";
                        $errorNum = 666;
                }else{
                        //echo "created db";
                        $errorNum = 1;
                }
	
	}
        if(mysqli_connect_errno()) $errorNum = mysqli_connect_errno();
	return $errorNum;	
}

//
// remove_comments will strip the sql comment lines out of an uploaded sql file
// specifically for mssql and postgres type files in the install....
//
function remove_comments(&$output)
{
	$lines = explode("\n", $output);
	$output = "";

	// try to keep mem. use down
	$linecount = count($lines);

	$in_comment = false;
	for($i = 0; $i < $linecount; $i++)
	{
		if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
		{
			$in_comment = true;
		}

		if( !$in_comment )
		{
			$output .= $lines[$i] . "\n";
		}

		if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
		{
			$in_comment = false;
		}
	}

	unset($lines);
	return $output;
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql)
{
	$lines = explode("\n", $sql);

	// try to keep mem. use down
	$sql = "";

	$linecount = count($lines);
	$output = "";

	for ($i = 0; $i < $linecount; $i++)
	{
		if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
		{
			if (isset($lines[$i][0]) && ($lines[$i][0] != "#"))
			{
				if($lines[$i][0] == "-" || $lines[$i][0] == "/"){
					
				}else{
					$output .= $lines[$i] . "\n";
				}
				
			}
			else
			{
				$output .= "\n";
			}
			// Trading a bit of speed for lower mem. use here.
			$lines[$i] = "";
		}
	}

	return $output;

}

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
function split_sql_file($sql, $delimiter)
{
	// Split up our string into "possible" SQL statements.
	$tokens = explode($delimiter, $sql);

	// try to save mem.
	$sql = "";
	$output = array();

	// we don't actually care about the matches preg gives us.
	$matches = array();

	// this is faster than calling count($oktens) every time thru the loop.
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++)
	{
		// Don't wanna add an empty string as the last thing in the array.
		if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

			$unescaped_quotes = $total_quotes - $escaped_quotes;

			// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
			if (($unescaped_quotes % 2) == 0)
			{
				// It's a complete sql statement.
				$output[] = $tokens[$i];
				// save memory.
				$tokens[$i] = "";
			}
			else
			{
				// incomplete sql statement. keep adding tokens until we have a complete one.
				// $temp will hold what we have so far.
				$temp = $tokens[$i] . $delimiter;
				// save memory..
				$tokens[$i] = "";

				// Do we have a complete statement yet?
				$complete_stmt = false;

				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
				{
					// This is the total number of single quotes in the token.
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					// Counts single quotes that are preceded by an odd number of backslashes,
					// which means they're escaped quotes.
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

					$unescaped_quotes = $total_quotes - $escaped_quotes;

					if (($unescaped_quotes % 2) == 1)
					{
						// odd number of unescaped quotes. In combination with the previous incomplete
						// statement(s), we now have a complete statement. (2 odds always make an even)
						$output[] = $temp . $tokens[$j];

						// save memory.
						$tokens[$j] = "";
						$temp = "";
						//
						// remove_comments will strip the sql comment lines out of an uploaded sql file
						// specifically for mssql and postgres type files in the install....
						//
						function remove_comments(&$output)
						{
							$lines = explode("\n", $output);
							$output = "";
						
							// try to keep mem. use down
							$linecount = count($lines);
						
							$in_comment = false;
							for($i = 0; $i < $linecount; $i++)
							{
								if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
								{
									$in_comment = true;
								}
						
								if( !$in_comment )
								{
									$output .= $lines[$i] . "\n";
								}
						
								if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
								{
									$in_comment = false;
								}
							}
						
							unset($lines);
							return $output;
						}
						
						//
						// remove_remarks will strip the sql comment lines out of an uploaded sql file
						//
						function remove_remarks($sql)
						{
							$lines = explode("\n", $sql);
						
							// try to keep mem. use down
							$sql = "";
						
							$linecount = count($lines);
							$output = "";
						
							for ($i = 0; $i < $linecount; $i++)
							{
								if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
								{
									if (isset($lines[$i][0]) && $lines[$i][0] != "#")
									{
										$output .= $lines[$i] . "\n";
									}
									else
									{
										$output .= "\n";
									}
									// Trading a bit of speed for lower mem. use here.
									$lines[$i] = "";
								}
							}
						
							return $output;
						
						}
						
						//
						// split_sql_file will split an uploaded sql file into single sql statements.
						// Note: expects trim() to have already been run on $sql.
						//
						function split_sql_file($sql, $delimiter)
						{
							// Split up our string into "possible" SQL statements.
							$tokens = explode($delimiter, $sql);
						
							// try to save mem.
							$sql = "";
							$output = array();
						
							// we don't actually care about the matches preg gives us.
							$matches = array();
						
							// this is faster than calling count($oktens) every time thru the loop.
							$token_count = count($tokens);
							for ($i = 0; $i < $token_count; $i++)
							{
								// Don't wanna add an empty string as the last thing in the array.
								if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
								{
									// This is the total number of single quotes in the token.
									$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
									// Counts single quotes that are preceded by an odd number of backslashes,
									// which means they're escaped quotes.
									$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
						
									$unescaped_quotes = $total_quotes - $escaped_quotes;
						
									// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
									if (($unescaped_quotes % 2) == 0)
									{
										// It's a complete sql statement.
										$output[] = $tokens[$i];
										// save memory.
										$tokens[$i] = "";
									}
									else
									{
										// incomplete sql statement. keep adding tokens until we have a complete one.
										// $temp will hold what we have so far.
										$temp = $tokens[$i] . $delimiter;
										// save memory..
										$tokens[$i] = "";
						
										// Do we have a complete statement yet?
										$complete_stmt = false;
						
										for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
										{
											// This is the total number of single quotes in the token.
											$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
											// Counts single quotes that are preceded by an odd number of backslashes,
											// which means they're escaped quotes.
											$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
						
											$unescaped_quotes = $total_quotes - $escaped_quotes;
						
											if (($unescaped_quotes % 2) == 1)
											{
												// odd number of unescaped quotes. In combination with the previous incomplete
												// statement(s), we now have a complete statement. (2 odds always make an even)
												$output[] = $temp . $tokens[$j];
						
												// save memory.
												$tokens[$j] = "";
												$temp = "";
						
												// exit the loop.
												$complete_stmt = true;
												// make sure the outer loop continues at the right point.
												$i = $j;
											}
											else
											{
												// even number of unescaped quotes. We still don't have a complete statement.
												// (1 odd and 1 even always make an odd)
												$temp .= $tokens[$j] . $delimiter;
												// save memory.
												$tokens[$j] = "";
											}
						
										} // for..
									} // else
								}
							}
						
							return $output;
						}
						
						// exit the loop.
						$complete_stmt = true;
						// make sure the outer loop continues at the right point.
						$i = $j;
					}
					else
					{
						// even number of unescaped quotes. We still don't have a complete statement.
						// (1 odd and 1 even always make an odd)
						$temp .= $tokens[$j] . $delimiter;
						// save memory.
						$tokens[$j] = "";
					}

				} // for..
			} // else
		}
	}

	return $output;
}

?>
