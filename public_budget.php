<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
if(isset($_GET["uid"])){
$unique_id = $_GET["uid"];
$user_id = getUserIdfromUnique($unique_id, $mysqli);
if($user_id){
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo getUserName($user_id, $mysqli)."'s";?> budget</title>
        <link rel="stylesheet" href="styles/main.css" />
        <script src="js/budget_display.js"></script>
    </head>

    <body onload="orderCategories();">

 <div id='header'>
                <span><?php echo getUserName($user_id, $mysqli)."'s";?> Budget</span>    
            </div>
            <div id="homebutton">
            <p><button onclick="window.location = 'index.php';">Login</button></p>
            </div>
            <div id="containers">
            <div class="container" id='budget_display'>
                
			<!-- Here all categories are fetched from the data base -->
            <?php 
            $categories =  get_categories($mysqli,$user_id);
            $result = mysqli_query($mysqli, "SELECT SUM(category_goal) AS total_goal FROM categories WHERE parent_ID=0 AND user_id=".$user_id); 
            $row = mysqli_fetch_assoc($result); 
            $totalgoal = $row['total_goal'];
            $result2 = mysqli_query($mysqli, "SELECT SUM(transaction_amount) AS total_spent FROM transactions WHERE user_id=".$user_id); 
            $row2 = mysqli_fetch_assoc($result2); 
            $totalspent = $row2['total_spent'];

            $categories = get_public_categories($mysqli, $user_id);
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
						<button class="categoryShowHide" onclick="showHideCategory('ctg-<?php echo $category_id?>')">show/hide</button>						
						<span class="categoryGoal">Goal: $<?php echo $category_goal ?></span> 
						<span class="categoryAmount">Actual: $<?php  echo get_public_total($category_id)?></span>
						
					</span>
				</div>
			<?php  endfor;?>
			</div>
                        <div class="container" id="sidebar">
			<div class="container" id="aggregates">
				<h1>Budget Info:</h1>
				<ul>
					<li>Total budget: $<?php echo $totalgoal ?></li>
					<li>Total spent: $<?php echo $totalspent ?></li>

				</ul>
			</div>
			           <div class="container" id="changeColor">
                    Change Color scheme: 
                    <select name="colors" onchange='changeColor(this);'>
						<option value="#009933">Green</option>
						<option value="#0000FF">Blue</option>
						<option value="#FF99FF">Pink</option>
						<option value="#FF0000">Red</option>
					</select>
            </div>
                        </div>
			</div>

<?php }else{ echo "<p>The id is not valid!</p>"; }
}else{
	"<p>No user page specified</p>";
} ?>
</body>
</html>