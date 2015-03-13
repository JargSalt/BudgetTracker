<?php
include_once 'psl-config.php';

function sec_session_start() {
	$session_name = 'sec_session_id';
	// Set a custom session name
	$secure = SECURE;
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if (ini_set('session.use_only_cookies', 1) === FALSE) {
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
	// Sets the session name to the one set above.
	session_name($session_name);
	session_start();
	// Start the PHP session
	session_regenerate_id(true);
	// regenerated the session, delete the old one.
}

function login($email, $password, $mysqli) {
	// Using prepared statements means that SQL injection is not possible.
	if ($stmt = $mysqli -> prepare("SELECT id, username, password, salt 
        FROM members
       WHERE email = ?
        LIMIT 1")) {
		$stmt -> bind_param('s', $email);
		// Bind "$email" to parameter.
		$stmt -> execute();
		// Execute the prepared query.
		$stmt -> store_result();

		// get variables from result.
		$stmt -> bind_result($user_id, $username, $db_password, $salt);
		$stmt -> fetch();

		// hash the password with the unique salt.
		$password = hash('sha512', $password . $salt);
		if ($stmt -> num_rows == 1) {
			// If the user exists we check if the account is locked
			// from too many login attempts

			if (checkbrute($user_id, $mysqli) == true) {
				// Account is locked
				// Send an email to user saying their account is locked
				return false;
			} else {
				// Check if the password in the database matches
				// the password the user submitted.
				if ($db_password == $password) {
					// Password is correct!
					// Get the user-agent string of the user.
					$user_browser = $_SERVER['HTTP_USER_AGENT'];
					// XSS protection as we might print this value
					$user_id = preg_replace("/[^0-9]+/", "", $user_id);
					$_SESSION['user_id'] = $user_id;
					// XSS protection as we might print this value
					$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
					$_SESSION['username'] = $username;
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
					// Login successful.
					return true;
				} else {
					// Password is not correct
					// We record this attempt in the database
					$now = time();
					$mysqli -> query("INSERT INTO login_attempts(user_id, time)
                                    VALUES ('$user_id', '$now')");
					return false;
				}
			}
		} else {
			// No user exists.
			return false;
		}
	}
}

function checkbrute($user_id, $mysqli) {
	// Get timestamp of current time
	$now = time();

	// All login attempts are counted from the past 2 hours.
	$valid_attempts = $now - (2 * 60 * 60);

	if ($stmt = $mysqli -> prepare("SELECT time 
                             FROM login_attempts 
                             WHERE user_id = ? 
                            AND time > '$valid_attempts'")) {
		$stmt -> bind_param('i', $user_id);

		// Execute the prepared query.
		$stmt -> execute();
		$stmt -> store_result();

		// If there have been more than 5 failed logins
		if ($stmt -> num_rows > 5) {
			return true;
		} else {
			return false;
		}
	}
}


function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], 
                        $_SESSION['username'], 
                        $_SESSION['login_string'])) {
 
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];
 
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
 
        if ($stmt = $mysqli->prepare("SELECT password 
                                      FROM members 
                                      WHERE id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();
 
            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);
 
                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Not logged in 
            return false;
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {
 
    if ('' == $url) {
        return $url;
    }
 
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
 
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
 
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
 
    $url = str_replace(';//', '://', $url);
 
    $url = htmlentities($url);
 
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
 
    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function getUserIdfromUnique($uid, $mysqli){

	$stmt = $mysqli->prepare("SELECT `user_id` FROM `shared_urls` WHERE unique_id = ?");
	$stmt->bind_param("i", $uid);
	$stmt->execute();
	$stmt->bind_result($user_id);
	if($stmt->fetch()){
		$stmt->close();
		return $user_id;
	}else{
		$stmt->close();
		return false;
	}
}

function getUserName($user_id, $mysqli){
	$stmt = $mysqli->prepare("SELECT `username` FROM `members` WHERE id = ?");
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$stmt->bind_result($user_name);
	if($stmt->fetch()){
		$stmt->close();
		return $user_name;
	}else{
		$stmt->close();
		return false;
	}
}

function get_public_total($category_id){
	$total = get_category_total_rec($category_id);
	return $total;
}

function get_category_total_rec($category_id){
	$total = 0.0;	
	$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
	
	//get all transactions with $category_id
	$stmt = $mysqli->prepare("SELECT `transaction_amount` FROM `transactions` WHERE `category_id` = ?");
	$stmt->bind_param('i',$category_id);
    $stmt->execute();   // Execute the prepared query.
    $stmt->bind_result($transaction_amount);
    while($stmt->fetch()){
    	//add all transaction amounts to total
    	$total += $transaction_amount;
		
		
    }

//	$stmt->close();	

	//get all category id's with $category_id as parent_id
	$stmt =  $mysqli->prepare("SELECT `category_ID` FROM `categories` WHERE parent_ID = ?");
	$stmt->bind_param('i',$category_id);
    $stmt->execute();   // Execute the prepared query.
    $stmt->bind_result($child_id);
    while($stmt->fetch()){
    	//call get_category_total_rec on categories and add result to total
    	$total +=  get_category_total_rec($child_id, $mysqli);
	}

	return $total;	
}

function get_public_categories($mysqli, $user_id){
	//prepare query
	$stmt = $mysqli->prepare("SELECT `category_ID`, `parent_ID`, `category_name`, `category_goal` FROM `categories` WHERE `user_id` = ? order by category_name");
	$stmt->bind_param('i', $user_id);//use bind_param for better security from sql injections and such
        $stmt->execute();   // Execute the prepared query.
	$stmt->bind_result($category_id, $parent_id, $category_name, $category_goal);
	$result_array = [];
	$i = 0;
	while($stmt->fetch()){
		$tmp_array = array(
			"category_id" => $category_id,
			"parent_id" => $parent_id,
			"category_name" => $category_name,
			"category_goal" => $category_goal
		);
		//echo "\r\n".$i;
		//print_r($tmp_array);
		$result_array[$i] = $tmp_array;
		++$i;
	}
	$stmt->close();
	return $result_array;
}

function get_categories($mysqli){
	$user_id = $_SESSION['user_id'];
	//prepare query
	$stmt = $mysqli->prepare("SELECT `category_ID`, `parent_ID`, `category_name`, `category_goal` FROM `categories` WHERE `user_id` = ? order by category_name");
	$stmt->bind_param('i', $user_id);//use bind_param for better security from sql injections and such
        $stmt->execute();   // Execute the prepared query.
	$stmt->bind_result($category_id, $parent_id, $category_name, $category_goal);
	$result_array = [];
	$i = 0;
	while($stmt->fetch()){
		$tmp_array = array(
			"category_id" => $category_id,
			"parent_id" => $parent_id,
			"category_name" => $category_name,
			"category_goal" => $category_goal
		);
		//echo "\r\n".$i;
		//print_r($tmp_array);
		$result_array[$i] = $tmp_array;
		++$i;
	}
	$stmt->close();
	return $result_array;
	
}

function get_transactions($mysqli){
	$user_id = $_SESSION['user_id'];
	$stmt = $mysqli->prepare("SELECT `transaction_id`, `category_id`, `transaction_name`, `transaction_amount`, `date` FROM `transactions` WHERE `user_id` = ? order by transaction_name");
	$stmt->bind_param('i', $user_id);
    $stmt->execute();   // Execute the prepared query.
    $stmt->bind_result($transaction_id, $category_id, $transaction_name, $transaction_amount, $date);
	return $stmt->get_result();
}

function get_ctg_transactions($mysqli, $ctg_id){
//	mysqli_report(MYSQLI_REPORT_ALL);
	$user_id = $_SESSION['user_id'];
	$stmt = $mysqli->prepare("SELECT `transaction_id`, `category_id`, `transaction_name`, `transaction_amount`, `date` FROM `transactions` WHERE `user_id` = ? AND `category_id` = ?");
	$stmt->bind_param('ii', $user_id, $ctg_id);
    $stmt->execute();   // Execute the prepared query.
    $stmt->bind_result($transaction_id, $category_id, $transaction_name, $transaction_amount, $date);
	
		$result_array = [];
	$i = 0;
	while($stmt->fetch()){
		$tmp_array = array(
			"transaction_id" => $transaction_id,
			"category_id" => $category_id,
			"transaction_name" => $transaction_name,
			"transaction_amount" => $transaction_amount,
			"date" => $date
		);
		//echo "\r\n".$i;
		//print_r($tmp_array);
		$result_array[$i] = $tmp_array;
		++$i;
	}
	return $result_array;
}

function createStaticPage($user_id, $mysqli){
	
	$stmt = $mysqli->prepare("SELECT `unique_id` FROM `shared_urls` WHERE `user_id` = ?");
	$stmt->bind_param("i",$user_id);
	$stmt->execute(); 
    $stmt->bind_result($unique_id);
    if($stmt->fetch()){
    	return $unique_id;
    }else{
    $stmt->close();
	$unique_id = uniqid('',true);
	$stmt = $mysqli->prepare("INSERT INTO `shared_urls`(`unique_id`, `user_id`) VALUES (?,?)");
	$stmt->bind_param("si", $unique_id, $user_id);
	if($stmt->execute()){
		
		return $unique_id;	
	}else{
		return false;
	}
	}
}
?>