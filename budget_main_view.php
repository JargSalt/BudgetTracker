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
            <div id='header'>
                <span>Your Budget</span>    
            </div>
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
                                            <span onclick="alert('this should go to a category specific page')" class="categoryName"><u><?php echo $category_name?></u></span>
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
			  <!-- This specifies the HTML for each -->
				<tr class='transaction' id='trans-<?php echo $transaction_id?>' transaction_id='<?php echo $transaction_id?>' category_id='<?php echo $category_id?>' name='<?php echo $transaction_name?>' amount='<?php echo $transaction_amount ?>' date='<?php echo $date?>'>
					<td><?php echo $date?></td>
					<td><?php echo $transaction_name ?></td>
					<td>$<?php echo $transaction_amount; ?></td> 
					<td><button class="editButton" name ='editButton' onclick="editTransaction(this)"><img src='resources/images/edit-icon.png' height='15px' /></button></td>
					<td><button class="deletButton" name='deleteButton' onclick="deleteTransaction(this)"><img src='resources/images/trashcan.png' height='15px' /></button></td>
				</tr>
			<?php endfor; ?>
				<tr class='transaction'>
					<td><input class="date" id="adddate" name="adddate" type="text"/></td>
					<td><input class="name" id="addname" name="addname" type="text"/></td>
                    <td><input class="amount" id="addamount" name="addamount" type="text" step=".01"/></td> 
					<td><button class="newButton" onclick="submitNewTransaction(this);"><img src='resources/images/plus.png' height='15px' /></button></td>
				</tr>
					</table>
					<span class="endOfCtg">
					<button class="addCategory" onclick="addCategoryOptions(this)">Add Subcategory</button>
					</span>
				</div>
			<?php  endfor;?>
			</div>
			<div class="container" id="aggregates">
				<h1>This Months Info:</h1>
				<ul>
					<li>Total budget:</li>
					<li>Total spent:</li>
					<li>Most expensive transaction:</li>
					<li>Most expensive category:</li>
				</ul>
			</div>
                        <div class="container" id="newcategory">
                        <button class="random" onclick="showCatForm(this)" type="button">Add Category</button>
                        </div>
			</div>

            
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
            
    </body>
</html>