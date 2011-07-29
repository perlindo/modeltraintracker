<?php

if (eregi("menu.php",$_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}
$request = explode('/',$_SERVER['REQUEST_URI']);

switch($request[1])
{
	case "index.php";
	case "logout.php";
	case "";
		$login_out = '<a href="login.php">Login</a>';
		break;
	default:
		if(isset($_SESSION['SESS_MEMBER_ID'])) {
			$login_out = '<a href="logout.php">Logout</a>';
		} else {
			$login_out = '<a href="login.php">Login</a>';
		}
		break;
}

if(file_exists('config.php')) {
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

	if(isset($_SESSION['SESS_MEMBER_ID'])) {
		$query = "SELECT admin FROM members WHERE member_id=".$_SESSION['SESS_MEMBER_ID'];
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$admin_link = '';
		if($row[0] == 1) {
				$is_admin = true;
				$admin_link = '<a href="admin/index.php">Admin</a>';
		}
	}	
}

?>
	<div id="container">
		<div id="top">
			<h1>Model Train Inventory</h1>
		</div>
		<div id="leftnav">
			<p><a href="items.php">Items</a></p>
			<p><a href="manufacturers.php">Manufacturers</a></p>
			<p><a href="types.php">Types</a></p>
			<p><a href="roadnames.php">Roadnames</a></p>
			<p></p>
			<p>
				<a href="account.php">Your Account</a>
			</p>			
			<p>
				<a href="print.php">Print Inventory</a>
			</p>
			<p>
				<a href="backup.php">Backup/restore</a>
			</p>			
			<p>
				<?php echo $login_out; ?>
			</p>
			<p>
				<?php echo $admin_link; ?>
			</p>
		</div>