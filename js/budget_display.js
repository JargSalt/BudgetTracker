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
	if(children.length == 0){
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
			if(!validateChildren(Child)){
				isValid = false;	
			}
		}
	}
	return isValid;
}

function submitTransaction(that) {
	if (validateTransaction(that)) {
		addTransaction(that);
	}
}
function submitEditTransaction(that){
		if (validateTransaction(that)) {
		updateServer(that);
	}
}
;

