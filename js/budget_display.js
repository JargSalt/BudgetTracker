function getCategoryName(category_id) {
	var category = document.getElementById("ctg-" + category_id);
	var parent_id = category.getAttribute('parent_id');
	var name = category.getAttribute('name');
	if (parent_id == "") {
		return name;
	} else {
		return getCategoryName(parent_id) + "->" + name;
	}
}

function orderCategories() {
	var categories = document.getElementsByClassName("category");
	for (var i = 0; i < categories.length; ++i) {
		var category = categories[i];
		var parent_id = category.getAttribute('parent_id');
		if (parent_id == "") {
			continue;
		} else {
			var parent_ctg = document.getElementById("ctg-" + parent_id);
			parent_ctg.appendChild(category);
		}
	}
}

function showHideCategory(category_id) {
	var category = document.getElementById(category_id);
	var children = category.children;
	for (var i = 1; i < children.length; i++) {
		var child = children[i];
		if (child.className != "noHide") {
			if (child.style.display == "none") {
				child.style.display = "inherit";
			} else {
				child.style.display = "none";
			}
		}
	}
}

var validateErrorMessage;
function validateTransaction(button) {
	form = button.parentElement.parentElement;
	validateErrorMessage = "The following fields were entered incorectly: \r\n";
	if (validateChildren(form)) {
		return true;
	} else {
		alert(validateErrorMessage);
		return false;
	}
}

function validateChildren(element) {
	var children = element.children;
	var isValid = true;
	if (children.length == 0) {
		return true;
	}
	for (var i = 0; i < children.length; i++) {
		var Child = children[i];
		if (Child.tagName.toUpperCase() == "INPUT") {
			var inClass = Child.className;
			if (inClass.toUpperCase() == "DATE") {
				regex = /^(19\d\d|20[01]\d)-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
				if (regex.test(Child.value)) {
					//Child.style.backgroundColor = "lightGreen";
				} else {
					Child.style.backgroundColor = "pink";
					Child.value = null;
					isValid = false;
					validateErrorMessage += Child.name + "\n";
				}
			} else if (inClass.toUpperCase() == "NAME") {
				if (Child.value == "" || Child.value == null) {
					Child.style.backgroundColor = "pink";
					Child.value = null;
					isValid = false;
					validateErrorMessage += Child.name + "\n";
				} else {
					//Child.style.backgroundColor = "lightGreen";
				}
			} else if (inClass.toUpperCase() == "AMOUNT") {
				regex = /^[0-9]*\.?[0-9]?[0-9?]?$/;
				if (regex.test(Child.value)) {
					//Child.style.backgroundColor = "lightGreen";
				} else {
					Child.style.backgroundColor = "pink";
					Child.value = null;
					isValid = false;
					validateErrorMessage += Child.name + "\n";
				}
			}
		} else {
			if (!validateChildren(Child)) {
				isValid = false;
			}
		}
	}
	return isValid;
}

function submitNewTransaction(that) {
	if (validateTransaction(that)) {
		addTransaction(that);
	}
}

function submitEditTransaction(that) {
	if (validateTransaction(that)) {
		updateServer(that);
	}
}

var _editRow = [];
var _newrow = [];
function editTransaction(button) {
	var parent = button.parentElement;
	var row = parent.parentElement;
	_editRow[row.getAttribute('transaction_id')] = row;
	var transid = row.getAttribute("transaction_id");
	var date = row.cells[0].innerHTML;
	var name = row.cells[1].innerHTML;
	var str = row.cells[2].innerHTML;
	row.style.display = 'none';
	var number = str.split("$");
	var amount = number[1];
	var table = row.parentElement;
	var newrow = table.insertRow(row.rowIndex);
	newrow.setAttribute("transaction_id", transid);
	var cell1 = newrow.insertCell(0);
	var cell2 = newrow.insertCell(1);
	var cell3 = newrow.insertCell(2);
	var cell4 = newrow.insertCell(3);
	var cell5 = newrow.insertCell(4);
	cell1.innerHTML = '<input name="date" class="date" id="edate" type="text" value="' + date + '" placeholder="yyyy-mm-dd"/>';
	cell2.innerHTML = '<input name="transaction name" class="name" id="ename" type="text" value="' + name + '" required="required"/>';
	cell3.innerHTML = '<input name="amount" id="eamount" type="number" value="' + amount + '" step=".01" required="required"/>';
	cell4.innerHTML = '<button class="saveButton" onclick="submitEditTransaction(this)"><img src="resources/images/checkmark.png" height="15px" /></button>';
	cell5.innerHTML = '<button class="cancelButton" onclick="cancelTransEdit(this)"><img src="resources/images/x.png" height="15px" /></button>';
	_newrow[row.getAttribute('transaction_id')] = newrow;
}

function cancelTransEdit(button) {
	trans_id = button.parentElement.parentElement.getAttribute('transaction_id');
	_editRow[trans_id].style.display = '';
	_newrow[trans_id].parentNode.removeChild(_newrow[trans_id]);
}

function updateServer(button) {
	var date = document.getElementById('edate').value;
	var name = document.getElementById('ename').value;
	var amount = document.getElementById('eamount').value;
	var data = button.parentElement;
	var row = data.parentElement;
	var id = row.getAttribute("transaction_id");
	var str = "date=" + date + "&name=" + name + "&amount=" + amount + "&id=" + id + "&button=true";
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			row.innerHTML = xhr.responseText;
		}
	};
	xhr.open('POST', 'updateserver.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(str);
}

function deleteTransaction(button) {

	var confirm = window.confirm("Are you sure you want to delete this transaction?");
	if (confirm === true) {
		var cell1 = button.parentElement;
		var row1 = cell1.parentElement;
		var table1 = row1.parentElement;
		var id1 = row1.getAttribute("transaction_id");
		var str1 = "id1=" + id1 + "&button1=true";
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				table1.deleteRow(row1.rowIndex);
			}
		};
		xhr.open('POST', 'updateserver.php', true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(str1);
	} else {
	}

}

function addTransaction(button) {
	var buttondata = button.parentElement;
	var buttonrow = buttondata.parentElement;
	var tbody = buttonrow.parentElement;
	var date = buttonrow.cells[0].firstChild.value;
	var name = buttonrow.cells[1].firstChild.value;
	var amount = buttonrow.cells[2].firstChild.value;
	var buttonindex = buttonrow.rowIndex;
	var categoryinfo = buttonrow.previousElementSibling;
	var categorytable = categoryinfo.parentElement;
	if (buttonindex > 1) {//If a transaction already exists, run this code
		var clonerow = categoryinfo.cloneNode(true);
		clonerow.cells[0].innerHTML = date;
		clonerow.cells[1].innerHTML = name;
		clonerow.cells[2].innerHTML = "$" + amount;
		categorytable.insertBefore(clonerow, categorytable.children[buttonindex]);
		clonerow.setAttribute("date", date);
		clonerow.setAttribute("name", name);
		clonerow.setAttribute("amount", amount);
		var catid = clonerow.getAttribute("category_id");
		var requestString = "date=" + date + "&name=" + name + "&amount=" + amount + "&catid=" + catid + "&button2=true";
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				tbody.innerHTML = xhr.responseText;
				/*var transid = xhr.responseText;
				 var idString = "trans-"+transid;
				 clonerow.setAttribute("transaction_id",transid);
				 clonerow.setAttribute("id",idString);*/
			}
		};
		xhr.open('POST', 'updateserver.php', true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(requestString);
	} else {//If a transaction does not already exist for the category, run this code
		var transactiontable = categorytable.parentElement;
		var category = transactiontable.parentElement;
		var catid = category.getAttribute("category_id");
		var newrow = transactiontable.insertRow(buttonindex);
		var cell1 = newrow.insertCell(0);
		var cell2 = newrow.insertCell(1);
		var cell3 = newrow.insertCell(2);
		var cell4 = newrow.insertCell(3);
		var cell5 = newrow.insertCell(4);
		var requestString = "date=" + date + "&name=" + name + "&amount=" + amount + "&catid=" + catid + "&button2=true";
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				var transid = xhr.responseText;
				var idString = "trans-" + transid;
				newrow.setAttribute("category_id", catid);
				newrow.setAttribute("transaction_id", transid);
				newrow.setAttribute("id", idString);
				cell1.innerHTML = date;
				cell2.innerHTML = name;
				cell3.innerHTML = amount;
				cell4.innerHTML = "<button class='editButton' name ='editButton' onclick='editTransaction(this)'><img src='resources/images/edit-icon.png' height='15px' /></button>";
				cell5.innerHTML = "<button class='deletButton' name='deleteButton' onclick='deleteTransaction(this)'><img src='resources/images/trashcan.png' height='15px' /></button>";
			}
		};
		xhr.open('POST', 'updateserver.php', true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(requestString);
	}

}

function addCategoryOptions(button) {
	var parent = button.parentElement;
	parent.innerHTML = "Name: <input type='text' id='newcatname' name='name'> Goal: <input type='number' id='newcatgoal' name='goal'>" + "    <button id='save' class='saveButton' onclick='addCat(this)'><img src='resources/images/checkmark.png' height='15px'/></button>" + "    <button id='cancel' class='cancelButton' onclick='cancelCat(this)'><img src='resources/images/x.png' height='15px' /></button>";
}

function addCat(button) {
	var parent = button.parentElement;
	var div = parent.parentElement;
	var parid = div.getAttribute("category_id");
	var name = document.getElementById("newcatname").value;
	var goal = document.getElementById("newcatgoal").value;
	var requestString = "name=" + name + "&goal=" + goal + "&parid=" + parid + "&button=true";
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			parent.innerHTML = "<button class='addCategory' onclick='addCategoryOptions(this)'>Add Subcategory</button>";
			var newdiv = document.createElement("div");
			newdiv.setAttribute("class", "category");
			newdiv.setAttribute("id", "ctg-" + xhr.responseText);
			newdiv.setAttribute("category_id", xhr.responseText);
			newdiv.setAttribute("parent_id", parid);
			newdiv.setAttribute("name", name);
			newdiv.setAttribute("goal", goal);
			div.appendChild(newdiv);
			newdiv.innerHTML = "<span class='noHide'>" + "<span onclick='alert(\"this should go to a category specific page\")' class='categoryName'><u>" + name + "</u></span>" + "<span class='categoryGoal'>Goal: $" + goal + "</span>" + "<span class='categoryAmount'>Actual: $?</span>" + "<span class='categoryEdit'><button class='editButton' onclick='alert(\"This should make all fields editable and/or show a form to edit the category\")'><img src='resources/images/edit-icon.png' height='20px' /></button></span>" + "<button class='categoryShowHide' onclick='showHideCategory('ctg-" + xhr.responseText + "')'>show/hide details</button>" + "</span>" + "<table class='transaction_table' id='ttbl-" + xhr.responseText + "'>" + "<tr>" + "<th name='date' onclick='alert('sort by date')'>Date</th>" + "<th name='name' onclick='alert('sort by name')'>Name</th>" + "<th name='amount' onclick='alert('sort by amount')'>Amount</th>" + "</tr>" + "<tr class='transaction'>" + "<td><input id='adddate' name='adddate' type='text'/></td>" + "<td><input id='addname' name='addname' type='text'/></td>" + "<td><input id='addamount' name='addamount' type='number' step='.01'/></td>" + "<td><button class='newButton' onclick='addTransaction(this)'><img src='resources/images/plus.png' height='15px' /></button></td>" + "</tr>" + "</table>" + "<span class='endOfCtg'>" + "<button class='addCategory' onclick='addCategoryOptions(this)'>Add Subcategory</button>" + "</span>";
		}
	};
	xhr.open('POST', 'addcategory.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(requestString);
}

function cancelCat(button) {
	var parent = button.parentElement;
	parent.innerHTML = "<button class='addCategory' onclick='addCategoryOptions(this)'>Add Subcategory</button>";
}

function showCatForm(button) {
	var parent = button.parentElement;
	parent.innerHTML = "<form method='POST' action='addcategory.php'>Name: <input type='text' id='bigcatname' name='name'><br> Goal: <input type='number' id='bigcatgoal' name='goal'><br><br>" + "<button id='save' name='save' class='saveButton'><img src='resources/images/checkmark.png' height='15px'/></button></form>" + "<button id='cancel' class='cancelButton' onclick='cancelBigCat()'><img src='resources/images/x.png' height='15px' /></button>";
}

;

