<?php
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	if($conn->connect_error) die($conn->connect_error);
	
	
	echo "<a href='signupPage.php'>click here</a> to signup ";
	echo<<<_END
		<form action="loginPage.php" method="post"><pre>
		Username<input type="text" name="username">
		password<input type="password" name="password">
		<input type="submit" value="Login">
		</pre></form>
		_END;
	

	if(isset($_POST['username'] )&& isset($_POST['password'])){
		$username_temp = get_post($conn, 'username');
		$username_temp = mysql_entities_fix_string($conn, $username_temp);
		$username_temp = sanitizeString($username_temp);
		$password_temp = get_post($conn, 'password');
		$password_temp = mysql_entities_fix_string($conn, $password_temp);
		$password_temp = sanitizeString($password_temp);
		$query = "SELECT * FROM users WHERE username='$username_temp'";
		$result =$conn->query($query);
		if(!$result) die($connection->error);
		else if($result->num_rows){
			$row = $result->fetch_array(MYSQLI_NUM);
			$result->close();
			$salt = $row[2];
			$token = hash('ripemd128', "$salt$password_temp");
			if($token == $row[1]){
				session_start();
				$_SESSION['username'] = $username_temp;
				$_SESSION['password'] = $password_temp;
				echo "Hello $row[0] you are now logged in";
				//$_SERVER = array();
				die("<p><a href=PhpMySQL.php>Click here to continue</a></p>");
			}
			else{ 
				die ("Invalid username or password");
			}
		}
		else{
			die("Invalid username or password");
		}
	}
	
	/**
	* Code for sanitizeString, sanitizieMySQL, mysql_entities_fix_string, mysql_fix_string and get_post from slides by Fabio Di Troia
	*/
	function sanitizeString($var){
		$var = stripslashes($var);
		$var = strip_tags($var);
		$var = htmlentities($var);
		return $var;
	}
	function sanitizeMySQL($connection, $var){
		$var = $connection->real_escape_string($var);
		$var = sanitizeString($var);
		return $var;
	}	
	
	function get_post($conn, $var){
		return $conn->real_escape_string($_POST[$var]);
	}
	function mysql_entities_fix_string($connection, $string){
		return htmlentities(mysql_fix_string($connection, $string));
	}
	function mysql_fix_string($connection, $string){
		if(get_magic_quotes_gpc()) $string = stripslashes($string);
		return $connection->real_escape_string($string);
	}
	function destroy_session_and_data(){
		$_SESSION =array();
		setcookie(session_name(),'', time()-2592000, '/');
		session_destroy();
	}
	
?>