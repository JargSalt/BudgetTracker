<?php sec_session_start();
include_once 'includes/functions.php';
include_once 'db_connect.php';

if (login_check($mysqli) == true){
	$user_id = $_SESSION['user_id'];
	$unique_id = createStaticPage($user_id);
	if($unique_id){
		$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']."/public_budget.php?uid=".$unique_id;
		echo esc_url($url);
	}	
}

?>