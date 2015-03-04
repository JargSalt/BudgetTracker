<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Your Budget</title>
        <link rel="stylesheet" href="styles/main.css" />
        <script src="js/budget_display.js"></script>
    </head>
    <body onload="orderCategories()">

        <?php if (login_check($mysqli) == true) : ?>
            <p>Welcome <?php echo htmlentities($_SESSION['username']); ?>!</p>
            <p>Return to <a href="index.php">login page</a></p>
            <h1>Budget</h1>
            <div id="containers">
            <div class="container" id='budget_display'>


			<!-- Here all categories are fetched from the data base -->
            <?php 
            $categories =  get_categories($mysqli);
			//print_r($categories);
			for($i = 0; $i < count($categories); ++$i):
				$category_id = $categories[$i]['category_id'];
				$parent_id = $categories[$i]['parent_id'];
				$category_name = $categories[$i]['category_name'];
				$category_goal = $categories[$i]['category_goal'];
			?>	
			
				<!-- This specifies the html for each category -->		
				<div class='category' id='ctg-<?php echo $category_id?>' category_id="<?php echo $category_id?>" parent_id='<?php echo $parent_id?>' name='<?php echo $category_name?>' goal='<?php echo $category_goal ?>'>
					<span class="noHide">
						<span onclick="alert('this should go to a category specific page')" class="categoryName"><?php echo $category_name?></span>
						<span class="categoryGoal">Goal: $<?php echo $category_goal ?></span> 
						<span class="categoryAmount">Actual: $?</span>
						<span class="categoryEdit"><button class="editButton" onclick="alert('This should make all fields editable and/or show a form to edit the category')"><img src='resources/images/edit-icon.png' height='20px' /></button></span>
						<button class="categoryShowHide" onclick="showHideCategory('ctg-<?php echo $category_id?>')">show/hide details</button>
					</span>
					<table class="transaction_table" id="ttbl-<?php echo $category_id ?>">
						<tr>
							<th name="date" onclick="alert('sort by date')">Date</th>
							<th name="name" onclick="alert('sort by name')">Name</th>
							<th name="amount" onclick="alert('sort by amount')">Amount</th>
						</tr>
						
			<!-- Here a given category has all of its transactions loaded from the database -->			
			 <?php
			  $transactions =  get_ctg_transactions($mysqli,$category_id);
			  
			  for($j = 0; $j < count($transactions); ++$j):
			  $transaction_id = $transactions[$j]["transaction_id"];
			  $category_id = $transactions[$j]["category_id"];
			  $transaction_name = $transactions[$j]["transaction_name"];
			  $transaction_amount = $transactions[$j]["transaction_amount"];
			  $date = $transactions[$j]["date"];
			  ?>
			  <!-- This specifies the html for each -->
				<tr class='transaction' id='trans-<?php echo $transaction_id?>' transaction_id="<?php echo $transaction_id?>" category_id="<?php echo $category_id?>" name='<?php echo $transaction_name?>' amount='<?php echo $transaction_amount ?>' date='<?php echo $date?>'>
					<td><?php echo $date?></td>
					<td><?php echo $transaction_name ?></td>
					<td>$<?php echo $transaction_amount; ?></td> 
					<td><button class="editButton" onclick="alert('This should make all fields editable and/or show a form to edit the transaction')"><img src='resources/images/edit-icon.png' height='15px' /></button></td>
					<td><button class="deletButton" onclick="alert('This should delete the transaction')"><img src='resources/images/trashcan.png' height='15px' /></button></td>
				</tr>
			<?php endfor; ?>
				<tr class='transaction'>
					<form>
					<td><input name="date" type="text" placeholder="mm/dd/yyyy"required="required"/></td>
					<td><input name="name" type="text" placeholder="Enter transaction name" required="required"/></td>
					<td><input name="amount" type="number" step=".01" required="required"/></td> 
					<td><button class="newButton" onclick="alert('TODO: this should validate then add the transaction')"><img src='resources/images/plus.png' height='15px' /></button></td>
					</form>
				</tr>
					</table>
					<span class="endOfCtg">
					<button class="addCategory" onclick="alert('this should show a form for creating a new subcategory')">Add subcategory</button>
					</span>
				</div>
			<?php  endfor;?>
			</div>
			<div class="container" id="aggregates">
				<h1>This months info</h1>
				<ul>
					<li>Total budget:</li>
					<li>Total spent:</li>
					<li>Most expensive transaction:</li>
					<li>Most expensive category:</li>
				</ul>
			</div>
			</div>

            
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
    </body>
</html>