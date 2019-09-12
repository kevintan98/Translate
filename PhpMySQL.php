<?php
	/**
	*	New lines in text files don't work because when stored new line characters are replaced with sanitized and won't match word entered when trying to translate.
	*   Translates only 1 word at a time.
	*	Default model manually added into database. 
	*   Database is called dictionary, 4 columns langName, English, Translated, user
	*   Default model manually added in langName = DefaultLang, user = Default 
	*   When user signs in and tries to translate will always use the default model unless user adds and enters the name of new translation model
	*   Format checker for email isn't completely correct
	*/
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	if($conn->connect_error) die($conn->connect_error);
	
	session_start();
	if(isset($_SESSION['username'])){
		$username = $_SESSION['username'];
		echo "Hello $username you are signed in currently <br></br>";
		if(isset($_POST['eng'] )&& isset($_POST['name']) &&isset($_POST['lang'])){
			$en = get_post($conn, 'eng');
			$name = get_post($conn, 'name');
			$la = get_post($conn, 'lang');
			addToTable($en, $name, $la, $username, $conn);
		}
		
		if(isset($_POST['toTran'])){						
			if(isset($_POST['langChosen'])){
				
				$lc = get_post($conn, 'langChosen');
				$lc = mysql_fix_string($conn, $lc);
				$lc = sanitizeString($lc);
				if(strlen($lc) == 0){
					$query = "SELECT * FROM dictionary WHERE user='Default'";
				}
				else{
					$query = "SELECT * FROM dictionary WHERE user='$username'";
				}
				$untranslated = get_post($conn, 'toTran');
				$untranslated = mysql_fix_string($conn, $untranslated);
				$untranslated = sanitizeString($untranslated);
				$result =$conn->query($query);
				if(!$result) die($connection->error);
				else if($result->num_rows){
					if(strlen($lc) == 0){
						$row = $result->fetch_array(MYSQLI_NUM);
						$result->close();
						$english = $row[1];
						$translated = $row[2];
						$translatedWord = trans($untranslated, $english, $translated);
						echo "$untranslated$translatedWord";
					}
					else{
						$rows = $result->num_rows;
						for($j = 0; $j < $rows; ++$j){
							$result ->data_seek($j);
							$row = $result->fetch_array(MYSQLI_NUM);						
							if($row[0] === $lc){
								$english = $row[1];
								$translated = $row[2];
								$translatedWord = trans($untranslated, $english, $translated);
								echo "$untranslated$translatedWord";
							}
						}
						$result->close();
					}
				}
			}
			
		}
		
		echo "<br></br><a href='signout.php'>SIGN OUT</a><br></br>";
		echo<<<_END
		<form action="PhpMySQL.php" method="post"><pre>
		English<input type="file" name="eng" accept=".txt">
		New language Name<input type="text" name="name">		
		New language<input type="file" name="lang" accept=".txt">		
		<input type="submit" value="Add Language">
		Format for files: Separate words by commas only for both files. First word in English file is the linked to first word of other language file and so on.
		</pre></form>
		_END;
		
		echo<<<_END
		<br></br>
		Enter a word to translate. Default dictionary used if no language is chosen.
		<form action="PhpMySQL.php" method="post"><pre>
		English Word <input type="text" name="toTran">
		Enter Translated Language Name <input type="text" name="langChosen">
		<input type="submit" value="Translate">
		Your languages stored are: 
		</pre></form>
		_END;
		
		$query = "SELECT * FROM dictionary WHERE user='$username'";
		$result =$conn->query($query);
		if(!$result) die($connection->error);
		$rows = $result->num_rows;
		for($j = 0; $j < $rows; ++$j){
			$result ->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
			echo " $row[0]";
		}
	}
	else{
		echo "<a href='loginPage.php'>click here</a> to login<br></br>";
		echo "<a href='signupPage.php'>click here</a> to signup<br></br>";
		if(isset($_POST['toTranslate'])){
			$untranslated = get_post($conn, 'toTranslate');
			$untranslated = mysql_fix_string($conn, $untranslated);
			$untranslated = sanitizeString($untranslated);
			$query = "SELECT * FROM dictionary WHERE user='Default'";
			$result =$conn->query($query);
			if(!$result) die($connection->error);
			else if($result->num_rows){
				$row = $result->fetch_array(MYSQLI_NUM);
				$result->close();
				$english = $row[1];
				$translated = $row[2];
				$translatedWord = trans($untranslated, $english, $translated);
				echo "$untranslated$translatedWord";
			}
		}
		
		echo<<<_END
		<br></br>
		Enter a word to translate.
		<form action="PhpMySQL.php" method="post"><pre>
		Word<input type="text" name="toTranslate">
		<input type="submit" value="Translate">
		To use your own translation dictionary please login
		</pre></form>
		_END;
	}
	
	function destroy_session_and_data(){
		$_SESSION =array();
		setcookie(session_name(),'', time()-2592000, '/');
		session_destroy();
	}
	
	function trans($word, $english, $transLang){
		$eng_ar = explode(',', $english);
		$tran_ar = explode(',', $transLang);
		for($j = 0; $j < sizeof($eng_ar); ++$j){
			$temp = $eng_ar[$j];
			$temp = trim($temp);
			if($temp === $word){
				return " translation: $tran_ar[$j]";
			}
		}
		return ": No translation found";
		
	}
	
	function addToTable($en, $na, $la, $user, $conn){
		$na = mysql_fix_string($conn, $na);
		$na = sanitizeString($na);
		
		if(!file_exists($en) || !file_exists($la) || is_null($na)){
			
		}
		
		else{
			$cont = file_get_contents($en);
			$cont2 = file_get_contents($la);
			$cont = mysql_fix_string($conn, $cont);
			$cont = sanitizeString($cont);
			$cont2 = mysql_fix_string($conn, $cont2);
			$cont2 = sanitizeString($cont2);
			$query = "INSERT INTO dictionary VALUES"."('$na', '$cont', '$cont2', '$user')";
			$result = $conn->query($query);
			if(!$result) echo "INSERT FAILED: $query<br>".$conn->error."<br><br>";
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
	
	//$result->close();
	$conn->close();
?>