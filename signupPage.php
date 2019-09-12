<?php
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	if($conn->connect_error) die($conn->connect_error);
	
	if(isset($_POST['username'] )&& isset($_POST['password']) && isset($_POST['email'])){
			$un = get_post($conn, 'username');
			$pw = get_post($conn, 'password');
			$salt = "0v3rW4tcH";
			$email = get_post($conn, 'email');
			sign_up($conn, $un, $pw, $salt, $email);
		}
	echo "<a href='loginPage.php'>click here</a> to login ";
	echo<<<_END
		<form action="signupPage.php" method="post"><pre>
		Username<input type="text" name="username">
		password<input type="password" name="password">
		email<input type="email" name="email">
		<input type="submit" value="SIGN UP">
		</pre></form>
		_END;
	
	$conn->close();
	
	
	function sign_up($conn, $un, $pw, $salt, $email){
		$un = mysql_entities_fix_string($conn, $un);
		$un = sanitizeString($un);
		$pw = mysql_entities_fix_string($conn, $pw);
		$pw = sanitizeString($pw);
		$salt = mysql_entities_fix_string($conn, $salt);
		$email = mysql_entities_fix_string($conn, $email);
		$email = sanitizeString($email);
		$token = hash('ripemd128', "$salt$pw");
		$query = "SELECT * FROM users WHERE username = '$un' OR email = '$email'";
		$result = $conn->query($query);
		$rows = $result->num_rows;
		if($rows > 0){
			echo "Username or email invalid try a different one \n";	
		}
		else{		
			$query = "INSERT INTO users VALUES('$un', '$token', '$salt', '$email')";
			$result = $conn->query($query);
			if(!$result) die($conn->error);
			else{
				echo "Congrats you have signed up ";
				
			}
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