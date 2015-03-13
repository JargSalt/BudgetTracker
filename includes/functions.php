<?php
include_once 'psl-config.php';

function sec_session_start() {//initializes a session that only uses cookies and regenerates the session id to help prevent cross site scripting attacks 
//code based on this article http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
		
	 //set config option so session uses only cookies, this helps prevent cross-site scripting attacks
	  if (ini_set('session.use_only_cookies', 1) === FALSE) {
	 	echo "failed to set session.use_only_cookies";
		exit();
	 }
	 // Gets current cookies params.
	$cookieParams = session_get_cookie_params();
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], SECURE, true);
	session_name('sec_session_id');
	session_start();
	session_regenerate_id(true); 
}

function login($email, $password, $mysqli) {//code based on this article http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
	 
	
		//Get users info that coresponds to the given email
		$stmt = $mysqli -> prepare("SELECT id, username, password, salt FROM members WHERE email = ?");
		$stmt->bind_param('s', $email);
		$stmt->execute();	
		$stmt->store_result();
		$stmt -> bind_result($user_id, $username, $db_password, $salt);
		$stmt -> fetch();

			// the password in the database has been hashed with the stored salt
			// we hash the provided password with the salt to verify it
		  $password = hash('sha512', $password . $salt);
		if ($stmt -> num_rows == 1) { //does the user exist that coresponds to $email
			
			if (checkbrute($user_id, $mysqli) == true) {
				return false; //acount has had too many recent logins
			} else {
				// Check if the password in the database matches
				
				if ($db_password == $password) {
					// Password is correct!
					// Get the user-agent string of the user.
					$user_browser = $_SERVER['HTTP_USER_AGENT'];
					$_SESSION['user_id'] = $user_id; 
					//username is printed and was provided by user, so it should not be interpreted as html
					$username = htmlspecialchars($username);
					$_SESSION['username'] = $username;			
					//hashing the password with the user browser so that more is needed that just a hashed password to highjack the session
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);

					return true;
				} else {
					
					//record the time of failed attempt to check for brute force attempts
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

function checkbrute($user_id, $mysqli) {//code based on this article http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
	
	$now = time();
	$valid_attempts = $now - (2 * 60 * 60);//only iclude attempts from the last 2 hours (2hrs * 60min * 60s)

	if ($stmt = $mysqli -> prepare("SELECT time FROM login_attempts WHERE user_id = ? AND time > ?")) {
		$stmt -> bind_param('ii', $user_id, $valid_attempts);
		$stmt -> execute();
		$stmt -> store_result();

		// If there have been more than 5 failed logins in the last 2 hrs then we lock the acount
		if ($stmt -> num_rows > 5) {
			return true;
		} else {
			return false;
		}
	}
}


function login_check($mysqli) {//code based on this article http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) { 
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];
 
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
 
 		//get password from db
        $stmt = $mysqli->prepare("SELECT password FROM members WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute(); 
        $stmt->store_result();
 
            if ($stmt->num_rows == 1) {//there is 1 and only 1 user with that id
                
                $stmt->bind_result($password);
                $stmt->fetch();//get the password
                $login_check = hash('sha512', $password . $user_browser);
 
                if ($login_check == $login_string) {
                    return true;
                } 
            } 
        } 
return false;//else do login_check fails
}

//this sanitizing the result of $_SERVER['PHP_SELF']
function esc_url($url) {//code based on this article http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
 
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
	$stmt->bind_param("s", $uid);
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
	$result_array = array();
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
	$result_array = array();
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
	
		$result_array = array();
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

function deleteCategory($categoryDeleteId, $mysqli) {
        $response = "1";
        $stmt = $mysqli->prepare("DELETE FROM categories WHERE category_ID=?");
        $stmt->bind_param('i', $categoryDeleteId);
        $stmt2 = $mysqli->prepare("DELETE FROM transactions WHERE category_id=?");
        $stmt2->bind_param('i', $categoryDeleteId);
        if ($stmt->execute()) {
            if ($stmt2->execute()) {
            $stmt3 = $mysqli->prepare("SELECT category_ID FROM categories WHERE parent_ID =?");
            $stmt3->bind_param('i', $categoryDeleteId);
            $stmt3->execute();
            $stmt3->bind_result($childid);
            $children = array();
            $i = 0;
            while ($stmt3->fetch()) {
               
              //  deleteCategory($childid,$mysqli);
                $children[$i] = $childid;
                ++$i;
            }
            $stmt3->close();
            $stmt->close();
            $stmt2->close();
            
            for($j = 0; $j < $i; ++$j){
                 if (deleteCategory( $children[$j] ,$mysqli)=="0") {
                 $reponse = "0";
                 }

            }
            
            }
            
            else {
                $response = "0";
                $stmt->close();
                $stmt2->close();
            }
        }
        else {
            $stmt->close();
            $stmt2->close();
            $response = "0";
        }
        return $response;       
}
?>