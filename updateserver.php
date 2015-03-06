<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();

    if(isset($_POST)) {
        
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
		  "<td><button class='deletButton' onclick='alert('This should delete the transaction')'>".
                   "<img src='resources/images/trashcan.png' height='15px' /></button></td>";
    }
    else {
        echo "Something went wrong.";
    }
    }
    else {
        echo "Didn't make it into POST";
    }

