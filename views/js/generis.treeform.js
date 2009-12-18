/**
 * GenerisTreeFormClass is a easy to use container for the checkbox tree widget, 
 * it provides the common behavior for a selectable Class/Instance Rdf resource tree
 * 
 * @example new GenerisTreeClass('#tree-container', 'myData.php', {});
 * @see GenerisTreeClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require jstree >= 0.9.9 [http://jstree.com/]
 * 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */ 


/**
 * Constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options
 */
function GenerisTreeFormClass(selector, dataUrl, options){
	try{
		this.selector = selector;
		this.options = options;
		this.dataUrl = dataUrl;
		
		var instance = this;
		
		this.treeOptions = {
			data: {
				type: "json",
				async : true,
				opts: {
					method : "POST",
					url: instance.dataUrl
				}
			},
			types: {
			 "default" : {
					renameable	: false,
					deletable	: false,
					creatable	: false,
					draggable	: false
				}
			},
			ui: {
				theme_name : "checkbox"
			},
			callback : {
				onload: function(TREE_OBJ) {
					if(instance.options.checkedNodes){
						instance.check(instance.options.checkedNodes);
					}
					instance.getTree().open_all();
				}
			},
			plugins : {
				checkbox : { }
			}
		};
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
		$("#saver-action-" + this.options.actionId).click(function(){
			instance.saveData();
		});
	}
	catch(exp){
	//	console.log(exp);
	}
}

/**
 * get the tree reference
 * @return tree
 */
GenerisTreeFormClass.prototype.getTree = function(){
	return $.tree.reference(this.selector);
}

/**
 * Check the tree instances
 * @param {Array} elements the list of ids of instances to check
 */
GenerisTreeFormClass.prototype.check = function(elements){
	$.each(elements, function(i, elt){
		NODE = $("li[id="+elt+"]");
		if(NODE){
			$.tree.plugins.checkbox.check(NODE);
		}
	});
}

/**
 * save the checked instances in the tree by sending the ids using an ajax request
 */
GenerisTreeFormClass.prototype.saveData = function(){
	
	loading();
	var instance = this;
	var toSend = {};
	$.each($.tree.plugins.checkbox.get_checked(this.getTree()), function(i, NODE){
		if ($(NODE).hasClass('node-instance')) {
			toSend['instance_' + i] = $(NODE).attr('id');
		}
	});
	
	uriField = $("input[name=uri]");
	if (uriField) {
		toSend.uri = uriField.val();
	}
	classUriField = $("input[name=classUri]");
	if (classUriField) {
		toSend.classUri = classUriField.val();
	}
	
	$.ajax({
		url: this.options.saveUrl,
		type: "POST",
		data: toSend,
		dataType: 'json',
		success: function(response){
			if (response.saved) {
				createInfoMessage('Tree saved successfully');
			}
		},
		complete: function(){
			loaded();
		}
	});
}