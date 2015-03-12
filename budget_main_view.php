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
					<td><input id="adddate" name="adddate" type="text"/></td>
					<td><input id="addname" name="addname" type="text"/></td>
                                        <td><input id="addamount" name="addamount" type="number" step=".01"/></td> 
					<td><button class="newButton" onclick="if(validateTransaction(this)){addTransaction(this);}"><img src='resources/images/plus.png' height='15px' /></button></td>
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
			</div>

            
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
            
        <script>
        var _editRow;
        var newrow;
            function editTransaction(button) {
                var parent = button.parentElement;
                var row = parent.parentElement;
                _editRow = row;
                var transid = row.getAttribute("transaction_id");
                var date = row.cells[0].innerHTML;
                var name = row.cells[1].innerHTML;
                var str = row.cells[2].innerHTML;
                row.style.display = 'none';
                var number = str.split("$");
                var amount = number[1];
                var table = row.parentElement;
                newrow = table.insertRow(row.rowIndex);
                newrow.setAttribute("transaction_id", transid);
                var cell1 = newrow.insertCell(0);
                var cell2 = newrow.insertCell(1);
                var cell3 = newrow.insertCell(2);
                var cell4 = newrow.insertCell(3);
                var cell5 = newrow.insertCell(4);
                cell1.innerHTML = '<input name="date" class="date" id="edate" type="text" value="'+date+'" placeholder="yyyy-mm-dd"/>';
                cell2.innerHTML = '<input name="transaction name" class="name" id="ename" type="text" value="'+name+'" required="required"/>';
                cell3.innerHTML = '<input name="amount" id="eamount" type="number" value="'+amount+'" step=".01" required="required"/>';
                cell4.innerHTML = '<button class="saveButton" onclick="submitEditTransaction(this)"><img src="resources/images/checkmark.png" height="15px" /></button>';
                cell5.innerHTML = '<button class="cancelButton" onclick="cancelTransEdit()"><img src="resources/images/x.png" height="15px" /></button>';
            }
            function cancelTransEdit(){
            	_editRow.style.display = '';
            	newrow.parentNode.removeChild(newrow);
            }
            
            function updateServer(button) {
                var date = document.getElementById('edate').value;
                var name = document.getElementById('ename').value;
                var amount = document.getElementById('eamount').value;
                var data = button.parentElement;
                var row = data.parentElement;
                var id = row.getAttribute("transaction_id");
                var str = "date="+date+"&name="+name+"&amount="+amount+"&id="+id+"&button=true";
                var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) 
                    {
                        row.innerHTML = xhr.responseText;
                    }
                };
                xhr.open('POST','updateserver.php',true);
                xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xhr.send(str);
            }
            
            function deleteTransaction(button) {
                var confirm = window.confirm("Are you sure you want to delete this transaction?");
                if (confirm === true) {
                var cell1 = button.parentElement;
                var row1 = cell1.parentElement;
                var table1 = row1.parentElement;
                var id1 = row1.getAttribute("transaction_id");
                var str1 = "id1="+id1+"&button1=true";
                var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) 
                    {
                        table1.deleteRow(row1.rowIndex);
                        
                    }
                };
                xhr.open('POST','updateserver.php',true);
                xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xhr.send(str1);
                }
                else {
                }
            
            }
            
            function addTransaction(button) {
                var buttondata = button.parentElement;
                var buttonrow = buttondata.parentElement;
                var date = buttonrow.cells[0].firstChild.value;
                var name = buttonrow.cells[1].firstChild.value;
                var amount = buttonrow.cells[2].firstChild.value;
                var buttonindex = buttonrow.rowIndex;
                var categoryinfo = buttonrow.previousElementSibling;
                var categorytable = categoryinfo.parentElement;
                if (buttonindex > 1) { //If a transaction already exists, run this code
                    var clonerow = categoryinfo.cloneNode(true);
                    clonerow.cells[0].innerHTML = date;
                    clonerow.cells[1].innerHTML = name;
                    clonerow.cells[2].innerHTML = "$"+amount;
                    categorytable.insertBefore(clonerow,categorytable.children[buttonindex]);
                    clonerow.setAttribute("date", date);
                    clonerow.setAttribute("name", name);
                    clonerow.setAttribute("amount", amount);
                    var catid = clonerow.getAttribute("category_id");
                    var userid = <?php echo $_SESSION['user_id']; ?>;
                    var requestString = "date="+date+"&name="+name+"&amount="+amount+"&catid="+catid+"&userid="+userid+"&button2=true";
                    var xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) 
                        {
                            var transid = xhr.responseText;
                            var idString = "trans-"+transid;
                            clonerow.setAttribute("transaction_id",transid);
                            clonerow.setAttribute("id",idString);
                        }
                    };
                    xhr.open('POST','updateserver.php',true);
                    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                    xhr.send(requestString);
                }
                else { //If a transaction does not already exist for the category, run this code
                    var transactiontable = categorytable.parentElement;
                    var category = transactiontable.parentElement;
                    var catid = category.getAttribute("category_id");
                    var newrow = transactiontable.insertRow(buttonindex);
                    var cell1 = newrow.insertCell(0);
                    var cell2 = newrow.insertCell(1);
                    var cell3 = newrow.insertCell(2);
                    var cell4 = newrow.insertCell(3);
                    var cell5 = newrow.insertCell(4);
                    var userid = <?php echo $_SESSION['user_id']; ?>;
                    var requestString = "date="+date+"&name="+name+"&amount="+amount+"&catid="+catid+"&userid="+userid+"&button2=true";
                    var xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) 
                        {
                            var transid = xhr.responseText;
                            var idString = "trans-"+transid;
                            newrow.setAttribute("category_id",catid);
                            newrow.setAttribute("transaction_id",transid);
                            newrow.setAttribute("id",idString);
                            cell1.innerHTML = date;
                            cell2.innerHTML = name;
                            cell3.innerHTML = amount;
                            cell4.innerHTML = "<button class='editButton' name ='editButton' onclick='editTransaction(this)'><img src='resources/images/edit-icon.png' height='15px' /></button>";
                            cell5.innerHTML = "<button class='deletButton' name='deleteButton' onclick='deleteTransaction(this)'><img src='resources/images/trashcan.png' height='15px' /></button>";
                        }
                    };
                    xhr.open('POST','updateserver.php',true);
                    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                    xhr.send(requestString);
                }
                
            }
            
            function addCategoryOptions(button) {
                var parent = button.parentElement;
                parent.innerHTML = "Name: <input type='text' id='newcatname' name='name'> Goal: <input type='number' id='newcatgoal' name='goal'>"+
                                    "    <button id='save' class='saveButton' onclick='addCat(this)'><img src='resources/images/checkmark.png' height='15px'/></button>"+
                                     "    <button id='cancel' class='cancelButton' onclick='cancelCat(this)'><img src='resources/images/x.png' height='15px' /></button>";
            }
            
            function addCat(button) {
                var parent = button.parentElement;
                var div = parent.parentElement;
                var parid = div.getAttribute("category_id");
                var userid = <?php echo $_SESSION['user_id']; ?>;
                var name = document.getElementById("newcatname").value;
                var goal = document.getElementById("newcatgoal").value;
                var requestString = "name="+name+"&goal="+goal+"&parid="+parid+"&userid="+userid+"&button=true";
                var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) 
                    {
                        var newdiv = document.createElement("div");
                        newdiv.setAttribute("class","category");
                        newdiv.setAttribute("id","ctg-"+xhr.responseText);
                        newdiv.setAttribute("category_id", xhr.responseText);
                        newdiv.setAttribute("parent_id",parid);
                        newdiv.setAttribute("name",name);
                        newdiv.setAttribute("goal",goal);
                        div.appendChild(newdiv);
                        newdiv.innerHTML = "<span class='noHide'>"+
                "<span onclick='alert(\"this should go to a category specific page\")' class='categoryName'><u>"+name+"</u></span>"+
                    "<span class='categoryGoal'>Goal: $"+goal+"</span>"+ 
                        "<span class='categoryAmount'>Actual: $?</span>"+
                            "<span class='categoryEdit'><button class='editButton' onclick='alert(\"This should make all fields editable and/or show a form to edit the category\")'><img src='resources/images/edit-icon.png' height='20px' /></button></span>"+
                                "<button class='categoryShowHide' onclick='showHideCategory('ctg-"+xhr.responseText+"')'>show/hide details</button>"+
                                    "</span>"+
					"<table class='transaction_table' id='ttbl-"+xhr.responseText+"'>"+
                                            "<tr>"+
						"<th name='date' onclick='alert('sort by date')'>Date</th>"+
                                                    "<th name='name' onclick='alert('sort by name')'>Name</th>"+
							"<th name='amount' onclick='alert('sort by amount')'>Amount</th>"+
                                                            "</tr>"+
                "<tr class='transaction'>"+
                    "<td><input id='adddate' name='adddate' type='text'/></td>"+
                        "<td><input id='addname' name='addname' type='text'/></td>"+
                            "<td><input id='addamount' name='addamount' type='number' step='.01'/></td>"+ 
                                "<td><button class='newButton' onclick='addTransaction(this)'><img src='resources/images/plus.png' height='15px' /></button></td>"+
                                    "</tr>"+
					"</table>"+
                                            "<span class='endOfCtg'>"+
                                                "<button class='addCategory' onclick='addCategoryOptions(this)'>Add Subcategory</button>"+
                                                    "</span>";  
                    }
                };
                xhr.open('POST','addcategory.php',true);
                xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xhr.send(requestString);
            }
            
            function cancelCat(button) {
                var parent = button.parentElement;
                parent.innerHTML = "<button class='addCategory' onclick='addCategoryOptions(this)'>Add Subcategory</button>";
            }
         
            
            
        </script>
    </body>
</html>