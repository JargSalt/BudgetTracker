<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
    if (login_check($mysqli) == true){
        
    $user_id = $_SESSION['user_id'];
        

    if(isset($_POST['save'])) {
    $parid = strip_tags($_POST['catid']);
    $name = strip_tags($_POST[$parid.'-name']);
    $goal = strip_tags($_POST[$parid.'-goal']);
    $stmt = $mysqli->prepare("INSERT INTO categories (parent_ID, category_name, category_goal, user_id) VALUES (?,?,?,?)");
    $stmt->bind_param('isdi', $parid, $name, $goal, $user_id);
    if ($stmt->execute()) {
        header("Location: ". getBaseUrl() ."budget_main_view.php"); /* Redirect browser */
        exit();
    }
    }
    else {
        
    }
    if(isset($_POST['button5'])) {  
    $name = strip_tags($_POST['name']);
    $goal = strip_tags($_POST['goal']);
    $catid = strip_tags($_POST['catid']);
    $stmt = $mysqli->prepare("UPDATE categories SET category_name=?, category_goal=? WHERE category_ID=? ");
    $stmt->bind_param('sdi', $name, $goal, $catid);
        if ($stmt->execute()) {
        echo "1";
        }
        }
    else {
        
    }
    }
    else{
   	echo "login check failed";
	header("Location: ../index.php");
    }
?>

