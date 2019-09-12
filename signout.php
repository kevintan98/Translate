<?php
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	if($conn->connect_error) die($conn->connect_error);
	
	destroy_session_and_data();
	echo "Sign out completele ";
	echo "<a href='loginPage.php'>click here</a> to log in<br></br>";
	echo "<a href='PhpMySQL.php'> click here </a> to return to home page";
	
	function destroy_session_and_data(){
		session_start();
		$_SESSION =array();
		session_destroy();
	}
?>