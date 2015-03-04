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
		if(parent_id == ""){
			continue;
		}else{
			var parent_ctg = document.getElementById("ctg-" + parent_id);
			parent_ctg.appendChild(category);
		}
	}
}

function showHideCategory(category_id){
	var category = document.getElementById(category_id);
	var children = category.children;
	for (var i = 1; i < children.length; i++){
		var child = children[i];
		if(child.className != "noHide"){
			if(child.style.display == "none"){
				child.style.display = "inherit";
			}else{
				child.style.display = "none";
			}
		}
	}
}

;

