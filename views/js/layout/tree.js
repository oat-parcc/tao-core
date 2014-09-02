/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'lodash', 
    'context',
    'layout/actions',
    'uiBootstrap',
    'jsTree/plugins/jquery.tree.contextmenu',
], function($, _, context, actionManager, uiBootstrap){


    /**
     * @exports layout/tree
     */
    var treeFactory = function($elt, url, options){

        var lastOpened;
        var lastSelected;

        options = options || {};

        //these are the parameters added to the server call to load data
	    var serverParams = _.defaults(options.serverParameters || {}, {
            hideInstances   :  options.hideInstances || 0,
            filter          : '*',
            offset          : 0,
            limit           : 30
        });

        /**
         * Options given to the jsTree plugin
         */
        var treeOptions = {

            //data call
            data: {
                type: "json",
                async : true,
                opts: {
                    method : "POST",
                    url: url
                }
            },

            //theme
            ui: {
                theme_name : "css",
                theme_path : context.taobase_www + 'js/lib/jsTree/themes/css/style.css'
            },
        
            //nodes types
            types: {
                "default" : {
                    renameable	: false,
                    deletable	: true,
                    creatable	: true,
                    draggable	: function($node) {
                        return $node.hasClass('node-instance') && options.actions && options.actions.moveInstance;
                    }
                }
            },

            //lifecycle callbacks
            callback: {

                /**
                 * Additional parameters to send to the server to retrieve data.
                 * It uses the serverParams object previously defined
                 * @param {jQueryElement} [$node] - the node that represents a class. Used to add the classUri to the call
                 * @returns {Object} params
                 */
                beforedata: function($node) {

                    var params = _.clone(serverParams);
                    if($node && $node.length){
                        params.classUri = $node.attr('id');
                    }
                    params.selected = options.selectNode;

                    return params;
                },

                /**
                 * Called back once the data are received. 
                 * Used to modify them before building the tree.
                 * 
                 * @param {Object} data - the received data
                 * @param {Object} tree - the tree instance 
                 * @returns {Object} data the modified data
                 */
                ondata: function(data, tree) {

                    //automatically open the children of the received node
                    if (data.children) {
                        data.state = 'open';
                    }

                    return data;
                },


                /**
                 * Once the data are loaded and the tree is ready
                 * Used to modify them before building the tree.
                 * 
                 * @param {Object} tree - the tree instance 
                 *
                 * @fires layout/tree#change.taotree
                 */
                onload: function(tree){

                    //we open either the last selected node or the 1st branch
                    if (options.selectNode) {
                        tree.select_branch($("li[id='" + options.selectNode + "']"));
                        options.selectNode = false;
                    } else {
                        tree.open_branch($("li.node-class:first"));
                    }
                 
                    /**
                     * The tree state has changed
                     * @event layout/tree#change.taotree
                     * @param {Object} [context] - the tree context (uri, classUri)
                     */       
                    $elt.trigger('change.taotree');
                    $elt.trigger('ready.taotree');
                },

                /**
                 * Before a branch is opened
                 * @param {jQueryElement} $node - the opened node
                 */
                beforeopen: function($node) {
                    //TODO store this in the browser
                    lastOpened = $node;
                },

                /**
                 * A node is selected.
                 * 
                 * @param {HTMLElement} node - the opened node
                 * @param {Object} tree - the tree instance 
                 *
                 * @fires layout/tree#change.taotree
                 * @fires layout/tree#select.taotree
                 */
                onselect: function(node, tree) {

                    var action;
                    var $node           = $(node);
                    var nodeId          = $node.attr('id');
                    var $parentNode     = tree.parent($node);
                    var nodeContext     = {};

                    $('a.clicked', $elt).each(function() {
                        if ($(this).parent('li').attr('id') !==  nodeId) {
                            $(this).removeClass('clicked');
                        }
                    });

                    if ($node.hasClass('node-class')) {
                        if ($node.hasClass('closed')) {
                            tree.open_branch($node);
                        }
                        nodeContext.classUri = nodeId;

                        //execute the selectClass action
                        if(options.actions.selectClass){
                            actionManager.exec(options.actions.selectClass, nodeContext);
                        }
                    }

                    if ($node.hasClass('node-instance')){
                        nodeContext.uri = nodeId;
                        nodeContext.classUri = $parentNode.attr('id');

                        //execute the selectInstance action
                        if(options.actions.selectInstance){
                            actionManager.exec(options.actions.selectInstance, nodeContext);
                        }
                    }

                    /**
                     * A node has been selected
                     * @event layout/tree#select.taotree
                     * @param {Object} [context] - the tree context (uri, classUri)
                     */       
                    $elt
                      .trigger('select.taotree', [nodeContext])
                      .trigger('change.taotree', [nodeContext]);

                    return false;
                },

                //when a node is move by drag n'drop
                onmove: function(node, refNode, type, tree, rollback) {
                    if (!options.actions.moveInstance) {
                        return false;
                    }

                    //do not move an instance into an instance...
                    if ($(refNode).hasClass('node-instance') && type === 'inside') {
                        $.tree.rollback(rollback);
                        return false;
                    } 

                    if (type === 'after' || type === 'before') {
                        refNode = tree.parent(refNode);
                    }

                    //TODO call the move action

                    $elt.trigger('change.taotree');
                }
            }
        };

        //list of events callbacks to be bound to the tree       
        var events = {

            /**
             * Add a node to the tree. 
             *
             * @event layout/tree#addnode.taotree
             * @param {Object} data - the data about the node to add
             * @param {String} data.parent - the id/uri of the node that will contain the new node
             * @param {String} data.id - the id of the new node
             * @param {String} data.cssClass - the css class for the new node (node-instance or node-class at least).
             */       
            'addnode' : function(data){
                var tree =  $.tree.reference($elt);
                var parentNode = tree.get_node($('#' + data.parent, $elt).get(0));

                tree.select_branch(
                    tree.create({
                        data: data.label,
                        attributes: {
                            'id': data.id,
                            'class': data.cssClass
                        }
                    }, parentNode)
                );
           },

            /**
             * Remove a node from the tree.
             *
             * @event layout/tree#removenode.taotree
             * @param {Object} data - the data about the node to remove
             * @param {String} data.id - the id of the node to remove
             */       
            'removenode' : function(data){
                var tree =  $.tree.reference($elt);
                var node = tree.get_node($('#' + data.id, $elt).get(0));
                tree.remove(node);
           }
        };

        //bind events defined above 
        _.forEach(events, function(callback, name){
            $elt.on(name + '.taotree', function(){
                callback.apply(this, Array.prototype.slice.call(arguments, 1));
            });
        });

        // workaround to fix dublicate tree bindings on multiple page loads
        if (!$elt.hasClass('tree')) {

            //create the tree
            $elt.tree(treeOptions);
        }
    };

    /**
     * Add a css class to a list of nodes and their children, recursilvely.
     * @param {Array} nodes - the nodes to add the class to
     * @param {String} clazz - the css class
     */
    function addClassToNodes(nodes, clazz) {
        if(nodes.length){
           _.forEach(nodes, function(node){
 	            if (node.attributes && node.attributes['class'] && 
                    /node\-instance/.test(node.attributes['class'])) {
						
    				node.attributes['class'] = node.attributes['class'] + ' ' + clazz;
				}
                if (node.children) {
                    addClassToNodes(node.children, clazz);
                }
            });
        }
    }

    return treeFactory; 
});
