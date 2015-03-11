<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();

    if(isset($_POST['button'])) {
        
    $name = strip_tags($_POST['name']);
    $goal = strip_tags($_POST['goal']);
    $parid = strip_tags($_POST['parid']);
    $userid = strip_tags($_POST['userid']);
    $data = "INSERT INTO categories (parent_ID, category_name, category_goal, user_id) VALUES ('".$parid."','".$name."','".$goal."','".$userid."')";
    $query = mysqli_query($mysqli, $data);
    if ($query) {
        $catid_select = "SELECT category_id FROM categories WHERE category_name='".$name."'";
        $result = mysqli_query($mysqli, $catid_select);
        $row = mysqli_fetch_assoc($result);
        echo $row['category_id'];
    }
    else {
    }
    }
?>

