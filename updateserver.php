<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();

    if(isset($_POST['button'])) {
        
    $date = strip_tags($_POST['date']);
    $name = strip_tags($_POST['name']);
    $amount = strip_tags($_POST['amount']);
    $id = strip_tags($_POST['id']);
    $data = "UPDATE transactions SET date='".$date."',"
            ."transaction_name='".$name."', transaction_amount='".$amount."' WHERE transaction_id='".$id."'";
    $query = mysqli_query($mysqli, $data);
    if ($query) {
        echo "<td>".$date."</td>".
              "<td>".$name."</td>".
               "<td>$".$amount."</td>".
                "<td><button class='editButton' onclick='editTransaction(this)'>".
                 "<img src='resources/images/edit-icon.png' height='15px' /></button></td>".
		  "<td><button class='deletButton' onclick='deleteTransaction(this)'>".
                   "<img src='resources/images/trashcan.png' height='15px' /></button></td>";
    }
    else {
        echo "Something went wrong.";
    }
    }
    elseif(isset($_POST['button1'])) {
        $id1 = $_POST['id1'];
        $data1 = "DELETE from transactions WHERE transaction_id='".$id1."'";
        $query1 = mysqli_query($mysqli, $data1);
    }
    elseif(isset($_POST['button2'])) {
        $date1 = strip_tags($_POST['date']);
        $name1 = strip_tags($_POST['name']);
        $amount1 = strip_tags($_POST['amount']);
        $catid1 = strip_tags($_POST['catid']);
        $userid1 = strip_tags($_POST['userid']);
        $data2 = "INSERT INTO transactions (date,transaction_name,transaction_amount,category_id,user_id) VALUES ('".$date1."','".$name1."','".$amount1."','".$catid1."','".$userid1."')";
        $query2 = mysqli_query($mysqli, $data2);
        if ($query2) {
            $id_select = "SELECT * FROM transactions WHERE transaction_name='".$name1."' AND transaction_amount='".$amount1."'";
            $result = mysqli_query($mysqli, $id_select);
            if ($result) {
                while ($row = mysqli_fetch_array($result)) {
                    echo $row['transaction_id'];
                }
            }
        }
    }
    else {
        echo "Something went wrong";
    }


