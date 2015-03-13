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
		if (parent_id == 0) {
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
	newrow.setAttribute("class", "transaction");
	newrow.setAttribute("amount", amount);
	newrow.setAttribute("date", date);
	newrow.setAttribute("name", transid);
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
	var trans_id = button.parentElement.parentElement.getAttribute('transaction_id');
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
	row.setAttribute("transaction_id", id);
	row.setAttribute("date", date);
	row.setAttribute("amount", amount);
	row.setAttribute("name", name);
	row.setAttribute("class", "transaction");
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			row.innerHTML = xhr.responseText;
			_editRow[id].parentNode.removeChild(_editRow[id]);
			getCategoryTotals();
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
                                getCategoryTotals();
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
        var category = tbody.parentElement.parentElement;
        var catid = category.getAttribute("category_id");
	var date = buttonrow.cells[0].firstChild.value;
	var name = buttonrow.cells[1].firstChild.value;
	var amount = buttonrow.cells[2].firstChild.value;
	var requestString = "date=" + date + "&name=" + name + "&amount=" + amount + "&catid=" + catid + "&button2=true";
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			tbody.innerHTML = xhr.responseText;
                        getCategoryTotals();
		}
	};
	xhr.open('POST', 'updateserver.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(requestString);
        
}

function showSubCatForm(button) {
	var parent = button.parentElement;
        var category = parent.parentElement;
        var catid = category.getAttribute("category_id");
	parent.innerHTML = "<form method='POST' action='addcategory.php' style='display:inline'><input type='hidden' name='catid' value='"+catid+"'> Name: <input type='text' id='newcatname' name='name'> Goal: <input type='number' id='newcatgoal' name='goal'>" + "    <button id='save' class='saveButton' name='save'><img src='resources/images/checkmark.png' height='15px'/></button></form>" + "    <button id='cancel' class='cancelButton' onclick='cancelCat(this)'><img src='resources/images/x.png' height='15px' /></button>";
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
        var catid = 0;
	parent.innerHTML = "<form method='POST' style='display:inline' action='addcategory.php'><input type='hidden' name='catid' value='"+catid+"'><p>Name: <input type='text' id='bigcatname' name='name'></p> <p>Goal: <input type='number' id='bigcatgoal' name='goal'></p>" + "<button id='save' name='save' class='saveButton'><img src='resources/images/checkmark.png' height='15px'/></button></form>" + "<button id='cancel' class='cancelButton' onclick='cancelBigCat(this)'><img src='resources/images/x.png' height='15px' /></button>";
}

function cancelBigCat(button) {
        var parent = button.parentElement;
        parent.innerHTML = "<button class='random' onclick='showCatForm(this)' type='button'>Add Category</button>";
}
function deleteBigCategory(button) {
        var span = button.parentElement;
        var otherspan = span.parentElement;
        var div = otherspan.parentElement;
	var catid = div.getAttribute("category_id");
	var requestString = "catid=" + catid + "&button4=true";
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			if (xhr.responseText === "1") {
                            var confirm = window.confirm("Are you sure you want to delete this category?");
                            if (confirm) {
                            div.parentNode.removeChild(div);
                        }
                        }
                        if (xhr.responseText === "0") {
                            alert('Something has gone terribly wrong.');
                        }
		}
	};
	xhr.open('POST', 'updateserver.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(requestString);
}

var _oldCatName=[];
var _oldCatGoal = [];
var _newCatName=[];
var _newCatGoal=[];
function showBigCategoryForm(button) {
    var span = button.parentElement;
    var spandiv = span.parentElement;
    var category = spandiv.parentElement;
    var name = spandiv.getElementsByClassName("categoryName")[0];
    var goal = spandiv.getElementsByClassName("categoryGoal")[0];
    _oldCatName[category.getAttribute('category_id')] = name;
    _oldCatGoal[category.getAttribute('category_id')] = goal;
    name.style.display = 'none';
    goal.style.display = 'none';
    var newname = document.createElement("span");
    newname.setAttribute("class", "categoryName");
    var newgoal = document.createElement("span");
    newgoal.setAttribute("class", "categoryGoal");
    spandiv.appendChild(newname);
    spandiv.appendChild(newgoal);
    newname.innerHTML = "Name: <input type='text' id='bigcname'>";
    newgoal.innerHTML = "Goal: <input type='number' id='bigcgoal'>";
    span.innerHTML = '<button class="saveButton" onclick="submitEditCategory(this)"><img src="resources/images/checkmark.png" height="15px" /></button><button class="cancelButton" onclick="cancelCategoryEdit(this)"><img src="resources/images/x.png" height="15px" /></button>';
    _newCatName[category.getAttribute('category_id')] = newname;
    _newCatGoal[category.getAttribute('category_id')] = newgoal;
}

function cancelCategoryEdit(button) {
    var span = button.parentElement;
    var category_id = button.parentElement.parentElement.getAttribute('category_id');
	_oldCatName[category_id].style.display = '';
        _oldCatGoal[category_id].style.display = '';
	_newCatName[category_id].parentNode.removeChild(_newCatName[category_id]);
        _newCatGoal[category_id].parentNode.removeChild(_newCatGoal[category_id]);
        span.innerHTML = '<button class="editButton" onclick="showBigCategoryForm(this)"><img src="resources/images/edit-icon.png" height="15px" /></button>';
}

function submitEditCategory(button) {
    var span = button.parentElement;
    var spandiv = button.parentElement.parentElement;
    var category = button.parentElement.parentElement.parentElement;
    var category_id = category.getAttribute("category_id");
    var name = document.getElementById("bigcname").value;
    var goal = document.getElementById("bigcgoal").value;
    var requestString = "name=" + name + "&goal=" + goal + "&catid="+category_id+"&button5=true";
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
                            category.setAttribute("name", name);
                            category.setAttribute("goal", goal);
                            //spandiv.getElementsByClassName("categoryName")[0].innerHTML = name;
                            //spandiv.getElementsByClassName("categoryGoal")[0].innerHTML = "Goal: $"+goal;
                            _newCatName[category_id].innerHTML = "<u>"+name+"</u>";
                            _newCatGoal[category_id].innerHTML = "Goal: $"+goal;
                            _oldCatName[category_id].parentNode.removeChild(_oldCatName[category_id]);
                            _oldCatGoal[category_id].parentNode.removeChild(_oldCatGoal[category_id]);
                            span.innerHTML = '<button class="editButton" onclick="showBigCategoryForm(this)"><img src="resources/images/edit-icon.png" height="15px" /></button>';
                            getCategoryTotals();
                            orderCategories();
		}
	};
	xhr.open('POST', 'addcategory.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(requestString);
}

function getCategoryTotals(){
	categories = document.getElementsByClassName("category");
	for(var i = 0; i < categories.length; ++i){
		getCategoryTotal(categories[i]);
	}
}

function getCategoryTotal(category){
	var total = 0;
	var ttables = category.getElementsByTagName("TABLE");
	for(var i = 0; i < ttables.length; ++i){
		var transactions =  ttables[i].getElementsByClassName("transaction");
		for(var j = 0; j < transactions.length; ++j){
		var transaction = ttables[i].getElementsByClassName("transaction")[j];
		if(transaction.hasAttribute("amount")){
			total += parseFloat(transaction.getAttribute("amount"));
			}
		}
	}
	var amount = category.getElementsByClassName("categoryAmount");
	amount[0].innerHTML= "Actual: $" + Number(total.toFixed(2));
	
}

function createPublicPage(button){
	var parent = button.parentElement;
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			var url = document.createElement("P");
			url.setAttribute("class","publicUrl");
			url.innerHTML = "Your public url is: " + xhr.responseText;
			parent.appendChild(url);
			parent.removeChild(button);
		}
	};
	xhr.open('POST', 'createStaticPage.php', true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send();
	
}

;

