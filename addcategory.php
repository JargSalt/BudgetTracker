<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
    if (login_check($mysqli) == true){
        
    $user_id = $_SESSION['user_id'];
        
    if(isset($_POST['button'])) {
        
    $name = strip_tags($_POST['name']);
    $goal = strip_tags($_POST['goal']);
    $parid = strip_tags($_POST['parid']);
    $stmt = $mysqli->prepare("INSERT INTO categories (parent_ID, category_name, category_goal, user_id) VALUES (?,?,?,?)");
    $stmt->bind_param('isddi', $parid, $name, $amount, $goal, $user_id);
    if ($stmt->execute()) {
        $catid_select = "SELECT category_id FROM categories WHERE category_name='".$name."'";
        $result = mysqli_query($mysqli, $catid_select);
        $row = mysqli_fetch_assoc($result);
        echo $row['category_id'];
    }
    else {
    }
    }
    
    if(isset($_POST['save'])) {
        
    $name = strip_tags($_POST['name']);
    $goal = strip_tags($_POST['goal']);
    $stmt = $mysqli->prepare("INSERT INTO categories (parent_ID, category_name, category_goal, user_id) VALUES (0,?,?,?)");
    $stmt->bind_param('sdi', $name, $goal, $user_id);
    if ($stmt->execute()) {
        header("Location: ../BudgetBuddy/budget_main_view.php"); /* Redirect browser */
        exit();
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

