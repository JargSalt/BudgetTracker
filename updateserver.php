<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
 if (login_check($mysqli) == true){
 	
	$user_id = $_SESSION['user_id'];

    if(isset($_POST['button'])) {	
    $date = strip_tags($_POST['date']);
    $name = strip_tags($_POST['name']);
    $amount = strip_tags($_POST['amount']);
    $id = strip_tags($_POST['id']);
	
	//new way is secure against sql injection
	//added user_id in where clause so that users can only edit their own transaction
	$stmt = $mysqli->prepare("UPDATE transactions SET date = ?, transaction_name= ? , transaction_amount= ? WHERE transaction_id= ? AND user_id = ? ");
	$stmt->bind_param('ssdii', $date, $name, $amount, $id, $user_id);
    
	 //old way: not secure against sql injection 
	 /*$data = "UPDATE transactions SET date='".$date."',"
            ."transaction_name='".$name."', transaction_amount='".$amount."' WHERE transaction_id='".$id."'";
    $query = mysqli_query($mysqli, $data);*/
  //  if ($query) {
  if ($stmt->execute()) {   	
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
    $stmt->close();
    }
    elseif(isset($_POST['button1'])) {//delete a transaction
        $id1 = $_POST['id1'];
       /* $data1 = "DELETE from transactions WHERE transaction_id='".$id1."'";
        $query1 = mysqli_query($mysqli, $data1);*/
        
        $stmt = $mysqli->prepare("DELETE from transactions WHERE transaction_id= ? AND user_id = ?");
        $stmt->bind_param('ii', $id1, $user_id);
    	$stmt->execute();
		$stmt->close();
    }
    elseif(isset($_POST['button2'])) {
        $date1 = strip_tags($_POST['date']);
        $name1 = strip_tags($_POST['name']);
        $amount1 = strip_tags($_POST['amount']);
        $catid1 = strip_tags($_POST['catid']);
        //cannot trust the post requests to provide this honestly
        //userid1 = strip_tags($_POST['userid']);
        //instead trust the cookie that was verified with login_check()
        $userid1 = $_SESSION['user_id'];
		
		/*
        $data2 = "INSERT INTO transactions (date,transaction_name,transaction_amount,category_id,user_id) VALUES ('".$date1."','".$name1."','".$amount1."','".$catid1."','".$userid1."')";
        $query2 = mysqli_query($mysqli, $data2);
        if ($query2) {*/
        
        //secured from sql injection using prepared statement
        $stmt = $mysqli->prepare("INSERT INTO transactions (date,transaction_name,transaction_amount,category_id,user_id) VALUES (?,?,?,?,?)");
		$stmt->bind_param('ssdii', $date1,$name1, $amount1,$catid1,$userid1);
        if($stmt->execute()){
		
		//TODO: FIX WHAT HAPPENS NEXT. currently if the new item has same name and amount as an existing transaction it will behave wrong
		/*$id_select = $mysqli->prepare("SELECT * FROM transactions WHERE transaction_name=? AND transaction_amount= ? AND user_id = ?");
		$id_select->bind_param('sdi',$name1,$amount1,$userid1);
		if($id_select->execute()){
			
		}*/
          /*  $id_select = "SELECT * FROM transactions WHERE transaction_name='".$name1."' AND transaction_amount='".$amount1."'";
            $result = mysqli_query($mysqli, $id_select);
            if ($result) {
                while ($row = mysqli_fetch_array($result)) {
                    echo $row['transaction_id'];
                }
            }*/
		}
            $stmt->close();
            $id_select->close();
    }  else {
        echo "Something went wrong";
    }
    }else{
   		     echo "login check failed";
			 header("Location: ../index.php");
    }

