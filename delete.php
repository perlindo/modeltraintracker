<?php
if(count($_POST) == 0) {
    Header("Location: items.php");
    die();
}
require_once('auth.php');

require_once('config.php');
//Connect to mysql server
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if(!$link) {
	die('Failed to connect to server: ' . mysql_error());
}

//Select database
$db = mysql_select_db(DB_DATABASE);
if(!$db) {
	die("Unable to select database");
}

switch($_POST['ref']) {
	case "items":
		$delete_query = "DELETE FROM item WHERE i_index=".$_POST['id'];
		if($delete_result = mysql_query($delete_query)) {
			echo "ok";
		} else {
			echo json_encode(mysql_error());
		}
	break;
	case "del_file":
		chdir('temp');
		$file = explode('/', $_POST['filename']);
		unlink(trim($file[1]));
	break;
}




?>