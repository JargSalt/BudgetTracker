<?php
include_once 'includes/functions.php';
include_once 'includes/db_connect.php';

 sec_session_start();

if (login_check($mysqli) == true){
	
	$user_id = $_SESSION['user_id'];
	
	$unique_id = createStaticPage($user_id, $mysqli);
	
	if($unique_id){
		$url = "http://" . $_SERVER['SERVER_NAME']. "/BudgetTracker/public_budget.php?uid=".$unique_id;
		echo $url;
	}else{
		echo "failed to create public page";
	}	
}

?>