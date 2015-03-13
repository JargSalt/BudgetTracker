<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
if (login_check($mysqli) == true) {

    $user_id = $_SESSION['user_id'];

    if (isset($_POST['button'])) {
        $date = strip_tags($_POST['date']);
        $name = strip_tags($_POST['name']);
        $amount = strip_tags($_POST['amount']);
        $id = strip_tags($_POST['id']);

        //new way is secure against sql injection
        //added user_id in where clause so that users can only edit their own transaction
        $stmt = $mysqli->prepare("UPDATE transactions SET date = ?, transaction_name= ? , transaction_amount= ? WHERE transaction_id= ? AND user_id = ? ");
        $stmt->bind_param('ssdii', $date, $name, $amount, $id, $user_id);

        //old way: not secure against sql injection 
        /* $data = "UPDATE transactions SET date='".$date."',"
          ."transaction_name='".$name."', transaction_amount='".$amount."' WHERE transaction_id='".$id."'";
          $query = mysqli_query($mysqli, $data); */
        //  if ($query) {
        if ($stmt->execute()) {
            echo "<td>" . $date . "</td>" .
            "<td>" . $name . "</td>" .
            "<td>$" . $amount . "</td>" .
            "<td><button class='editButton' onclick='editTransaction(this)'>" .
            "<img src='resources/images/edit-icon.png' height='15px' /></button></td>" .
            "<td><button class='deletButton' onclick='deleteTransaction(this)'>" .
            "<img src='resources/images/trashcan.png' height='15px' /></button></td>";
        } else {
            echo "Something went wrong.";
        }
        $stmt->close();
    } elseif (isset($_POST['button1'])) {//delete a transaction
        $id1 = $_POST['id1'];
        $stmt = $mysqli->prepare("DELETE from transactions WHERE transaction_id= ? AND user_id = ?");
        $stmt->bind_param('ii', $id1, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['button2'])) {
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
          if ($query2) { */

        //secured from sql injection using prepared statement
        $stmt = $mysqli->prepare("INSERT INTO transactions (date,transaction_name,transaction_amount,category_id,user_id) VALUES (?,?,?,?,?)");
        $stmt->bind_param('ssdii', $date1, $name1, $amount1, $catid1, $userid1);
        if ($stmt->execute()) {
            ?>
                <tr>
                    <th name="date" onclick="alert('sort by date')">Date</th>
                    <th name="name" onclick="alert('sort by name')">Name</th>
                    <th name="amount" onclick="alert('sort by amount')">Amount</th>
		</tr>

            <?php $transactions = get_ctg_transactions($mysqli, $catid1);
            for ($j = 0; $j < count($transactions); ++$j):
                $transaction_id = $transactions[$j]["transaction_id"];
                $category_id = $transactions[$j]["category_id"];
                $transaction_name = $transactions[$j]["transaction_name"];
                $transaction_amount = $transactions[$j]["transaction_amount"];
                $date = $transactions[$j]["date"];
                ?>
                <!-- This specifies the HTML for each -->
                <tr class='transaction' id='trans-<?php echo $transaction_id ?>' transaction_id='<?php echo $transaction_id ?>' category_id='<?php echo $category_id ?>' name='<?php echo $transaction_name ?>' amount='<?php echo $transaction_amount ?>' date='<?php echo $date ?>'>
                    <td><?php echo $date ?></td>
                    <td><?php echo $transaction_name ?></td>
                    <td>$<?php echo $transaction_amount; ?></td> 
                    <td><button class="editButton" name ='editButton' onclick="editTransaction(this)"><img src='resources/images/edit-icon.png' height='15px' /></button></td>
                    <td><button class="deletButton" name='deleteButton' onclick="deleteTransaction(this)"><img src='resources/images/trashcan.png' height='15px' /></button></td>
                </tr>
            <?php endfor; ?>
            <tr class='transaction'>
                <td><input id="adddate" name="adddate" type="text"/></td>
                <td><input id="addname" name="addname" type="text"/></td>
                <td><input id="addamount" name="addamount" type="number" step=".01"/></td> 
                <td><button class="newButton" onclick="if (validateTransaction(this)) {
                                                                addTransaction(this);
                                                            }"><img src='resources/images/plus.png' height='15px' /></button></td>
            </tr>
            </table>

            </div>
            <?php
            //TODO: FIX WHAT HAPPENS NEXT. currently if the new item has same name and amount as an existing transaction it will behave wrong
            /* $id_select = $mysqli->prepare("SELECT * FROM transactions WHERE transaction_name=? AND transaction_amount= ? AND user_id = ?");
              $id_select->bind_param('sdi',$name1,$amount1,$userid1);
              if($id_select->execute()){

              } */
            /*  $id_select = "SELECT * FROM transactions WHERE transaction_name='".$name1."' AND transaction_amount='".$amount1."'";
              $result = mysqli_query($mysqli, $id_select);
              if ($result) {
              while ($row = mysqli_fetch_array($result)) {
              echo $row['transaction_id'];
              }
              } */
        }
        $stmt->close();
        // $id_select->close();
    } else {
        
    }
    if (isset($_POST['button4'])) {
        $catid = strip_tags($_POST['catid']);
        echo deleteCategory($catid, $mysqli);
    }
    
} else {
    echo "login check failed";
    header("Location: ../index.php");
}

