/*
 * todoList
 * https://github.com/purtuga/jquery.todoList
 *
 * Copyright (c) 2014 Paul Tavares
 * Licensed under the MIT license.
 */
(function(factory){
    if ( typeof define === "function" && define.amd ) {

        // AMD. Request jQuery and then run the plugin
        define([ "jquery" ], factory );

    } else {

        // Global jQuery
        factory( jQuery );

    }
}(function($) {

    var setup = {
        isInitDone: false
    };

    setup.listTemplate = business_manager_plugin.todo_list;
    setup.itemTemplate = business_manager_plugin.todo_item;
    setup.itemEditTemplate = business_manager_plugin.todo_item_edit;
    setup.confirmButtonTemplate = business_manager_plugin.todo_button_confirm;
    // setup.menuTemplate = business_manager_plugin.todo_menu;

    /**
     * Creates a todoList within the given selection of elements.
     *
     * @param {Object} [options]
     * @param {String} [options.title="Todo List"]
     * @param {Array<Object>|Array<String>} [options.items]
     * @param {String} [options.removeLabel="delete?"]
     * @param {String} [options.newItemLabel="New Item"]
     * @param {String} [options.editItemTooltip="Click to Edit"]
     * @param {Boolean} [options.focusOnTitle=false]
     * @param {Array<Object>} [options.customActions=null]
     *
     * @return {jQuery}
     */
    $.fn.todoList = function(options) {

        if (!setup.isInitDone){
            ;
            setup.isInitDone = true;
            // $('<style>' + setup.styles + '</style>').appendTo('head');

            // Setup global listener: If a click is dont outside an edit
            // item, close it.
            $("body").on("click", function(ev){

                var $elements = $("div.business-manager-todo-edit"),
                    $cntr;

                // close edit Forms
                if ($elements.length) {

                    $cntr = $(ev.target).closest(".business-manager-todo-action-edit");

                    if (!$cntr.length) {

                        $elements.find(".business-manager-todo-edit-save").click();

                    } else {

                        // Close any edit item that does not have the current
                        // target in inside of it.
                        $elements.each(function(){

                            if (!$.contains($cntr[0], this)){

                                $(this).find(".business-manager-todo-edit-save").click();

                            }

                        });

                    }

                } //end: if: edit containers

                // Hide todo list menu
                $elements = $("div.business-manager-todo-menu:visible");

                if ($elements.length) {

                    // FIXME: need to hide menu if clicked outside of it.

                }


            });

        }

        var returnValue = this;
        
        this.each(function(){

            if (this.jqueryTodoList && typeof options === "string") {

                switch (String(options).toLowerCase()) {

                    // options = returns a copy of the internal options object.
                    case "getsetup":

                        returnValue = this.jqueryTodoList.getSetup();

                        break;

                }

                return this;

            } //end: options === string

            // Exit if this element already has a todo list
            if ($(this).hasClass("has-business-manager-todo")) {

                return this;

            }

            var
            todo    = {

                $ele: $(this),

                opt: $.extend(
                    {},
                    {
                        title: "Todo List",
                        items: [],
                        removeLabel: "delete?",
                        newItemPlaceholder: "New Item",
                        editItemTooltip: "Click to Edit",
                        focusOnTitle: false,
                        customActions: null
                    },
                    options
                ),

                /**
                 * Returns the setup for this todo instance.
                 *
                 * @return {Object}
                 */
                getSetup: function(){

                    var instSetup = {
                        title: todo.$ui.find(".business-manager-todo-title-text").text(),
                        items: []
                    };

                    todo.getItems().each(function() {

                        var $thisItem = $(this);

                        instSetup.items.push({
                            done: $thisItem.is(".business-manager-todo-item-done"),
                            title: $thisItem
                                    .find(".business-manager-todo-item-title-text")
                                        .text()
                        });

                    });

                    return instSetup;

                }, //end: getSetup()

                /**
                 * Adds an item to the todo list
                 *
                 * @param {Object} itemContent
                 *      An object that represents a todo list item. Object
                 *      must have a 'title' and 'done' property
                 *
                 * @return {jQuery}
                 *      the item created.
                 */
                addItem: function(itemContent, suppressEvents){

                    var $newItem = $(setup.itemTemplate)
                            .find(".business-manager-todo-item-title-text")
                                .append(itemContent.title)
                                .end()
                            .addClass(
                                (itemContent.done ? "business-manager-todo-item-done" : "")
                            )
                            .find(".business-manager-todo-action-edit")
                                .attr("title", todo.opt.editItemTooltip)
                                .end()
                            .appendTo(todo.$ui.$items);

                    if (!suppressEvents) {

                        todo.$ele.trigger("todolist-add", options, todo.$ele);
                        todo.$ele.trigger("todolist-change", options, todo.$ele);

                    }

                    return $newItem;

                }, //end: addItem()

                /**
                 * Gets the list of items from the UI
                 *
                 * @return {jQuery}
                 */
                getItems: function(){

                    return todo.$ui.$items.find("div.business-manager-todo-item");

                }, //end: getItems()

                /**
                 * Shows the edit form for the item clicked on
                 *
                 * @param {jQuery} $ele
                 *
                 */
                showEdit: function($ele){

                    var
                    $titleText = $ele.find(
                            ".business-manager-todo-item-title-text,.business-manager-todo-title-text"
                        ).eq(0),
                    $titleEdit = $(setup.itemEditTemplate)
                        .css("display", "none")
                        .find("input")
                            .val($titleText.text())
                            .end()
                        .appendTo($ele),
                    saveValue = function(){

                        $titleText.html($titleEdit.find("input").val());
                        $titleEdit.hide().delay("500").remove();
                        $titleText.show();
                        $ele.addClass("business-manager-todo-action");

                        todo.$ele.trigger("todolist-edit", options, todo.$ele);
                        todo.$ele.trigger("todolist-change", options, todo.$ele);

                    };

                    $ele.removeClass("business-manager-todo-action");
                    $titleText.hide();
                    $titleEdit.show().promise().then(function(){
                        $titleEdit.find("input").focus();
                    });

                    // Setup events
                    $titleEdit
                        .find(".business-manager-todo-edit-save")
                            .on("click", saveValue)
                            .end()
                        .find("input")
                            .on("keyup", function(ev){

                                if (ev.which === 13){
                                    saveValue();
                                }

                            });


                }, //end: showEdit()

                /**
                 * Sets up the list menu and stores it in todo.$ui.$menu
                 */
                setupListMenu: function(){

                    todo.$ui.$menu = $(setup.menuTemplate)
                                            .css("display", "none")
                                            .appendTo(todo.$ui);

                    // Add custom actions
                    if ($.isArray(todo.opt.customActions)) {

                        var itemTemplate = todo.$ui.$menu
                                .find(".business-manager-todo-menu-items > a:first")
                                    .clone(),
                            additionalItems = $();

                        $.each(todo.opt.customActions, function(){

                            if ($.isPlainObject(this)){

                                var thisCustomAction = this;

                                additionalItems = additionalItems.add(
                                    itemTemplate.clone()
                                        .text(this.title || "Action?")
                                        .attr("data-todolistaction", "custom action")
                                        .click(function(){
                                            if ($.isFunction(thisCustomAction.action)) {

                                                return thisCustomAction.action.call(
                                                    todo.$ele, options, thisCustomAction
                                                );

                                            }
                                        })
                                );

                            }

                        });

                        todo.$ui.$menu.find(".business-manager-todo-menu-items")
                            .append(additionalItems);

                    }


                } //end: setupListMenu()

            }; //end: todo instance

            todo.$ui = $(setup.listTemplate)
                        .appendTo(
                            todo.$ele.empty().addClass("has-business-manager-todo")
                        )
                        .find(".business-manager-todo-title-text")
                            .html(todo.opt.title)
                            .end()
                        .find(".business-manager-todo-add-input-text")
                            .attr("placeholder", todo.opt.newItemPlaceholder)
                            .end()
                        .find(".business-manager-todo-action-edit")
                            .attr("title", todo.opt.editItemTooltip)
                            .end();

            // Add references to the UI elements
            todo.$ui.$menu          = null;
            todo.$ui.$items         = todo.$ui.find("div.business-manager-todo-items");
            todo.$ui.$newItemInput  = todo.$ui.find("input.business-manager-todo-add-input-text");
            todo.$ui.$addButton     = todo.$ui.find(".business-manager-todo-add-action");

            // If there are items defined on input, create them
            if ($.isArray(todo.opt.items)) {

                $.each(todo.opt.items, function(i,item){

                    var thisItem = {
                        title: 'todo item',
                        done: false
                    };

                    if ($.isPlainObject(item)) {

                        $.extend(thisItem, item);

                    } else {

                        thisItem.title = String(item) || "Item " + (i + 1);
                    }

                    todo.addItem(thisItem, true);

                });

            }

            // Bind CLICK event handler
            todo.$ui.on("click.jqueryTodoList", ".business-manager-todo-action", function(){

                var $this   = $(this),
                    tmp1;

                // ADD NEW ITEM
                if ($this.is(todo.$ui.$addButton)) {

                    tmp1 = todo.$ui.$newItemInput.val();

                    if (tmp1) {

                        todo.addItem({title: tmp1});

                    }

                    todo.$ui.$newItemInput.val("");
                    todo.$ui.$newItemInput.focus();

                // SHOW ITEM REMOVE CONFIRM
                } else if ($this.is(".business-manager-todo-item-action-remove")) {

                    $this
                        .hide()
                        .closest(".business-manager-todo-item-actions-right")
                        .append(
                            $(setup.confirmButtonTemplate)
                                .find(".business-manager-todo-button-confirm")
                                    .addClass("ui-state-error business-manager-todo-item-action-remove-confirmed")
                                    .html(todo.opt.removeLabel)
                                    .end()
                                .find(".business-manager-todo-button-cancel")
                                    .addClass("business-manager-todo-item-action-remove-cancel")
                                    .end()
                        );

                // CANCEL DELETE OF ITEM
                } else if ($this.is(".business-manager-todo-item-action-remove-cancel")) {

                    $this
                        .closest(".business-manager-todo-item-actions-right")
                            .find(".business-manager-todo-item-action-remove")
                                .show()
                                .end()
                            .end()
                        .closest(".business-manager-todo-button")
                            .remove();

                // REMOVE ITEM
                } else if ($this.is(".business-manager-todo-item-action-remove-confirmed")) {

                    $this.closest(".business-manager-todo-item")
                        .slideUp().promise().then(function(){

                            $(this).remove();

                        });

                    $this.trigger("todolist-remove", options, $this);
                    $this.trigger("todolist-change", options, $this);

                // MARK CHECKED/UNCHECKED
                } else if ($this.is(".business-manager-todo-item-checkbox")) {

                    tmp1 = $this.closest(".business-manager-todo-item");

                    if (tmp1.hasClass("business-manager-todo-item-done")) {

                        tmp1.removeClass("business-manager-todo-item-done");

                        $this.trigger("todolist-unchecked", options, $this);
                        $this.trigger("todolist-change", options, $this);

                    } else {

                        tmp1.addClass("business-manager-todo-item-done");

                        $this.trigger("todolist-checked", options, $this);
                        $this.trigger("todolist-change", options, $this);

                    }

                // SHOW EDIT FORM?
                } else if ($this.is(".business-manager-todo-action-edit")) {

                    todo.showEdit($this);

                // SHOW THE TODOLIST MENU
                } else if ($this.is(".business-manager-todo-menu-show")) {

                    if (!todo.$ui.$menu) {

                        todo.setupListMenu();

                    }

                    if (todo.$ui.$menu.is(":visible")) {

                        todo.$ui.$menu.hide();

                    } else {

                        todo.$ui.$menu.show();

                    }


                // SHOW/HIDE DONE ITEMS
                } else if ($this.is(".business-manager-todo-menu-item")){


                    if ($this.data("todolistaction") === "toggle done") {

                        if (todo.$ui.$items.hasClass("business-manager-todo-hide-done")) {

                            todo.$ui.$items.removeClass("business-manager-todo-hide-done");

                        } else {

                            todo.$ui.$items.addClass("business-manager-todo-hide-done");

                        }

                    } //end: toggle done

                    $this.closest(".business-manager-todo-menu").hide();


                // CLOSE A MENU
                } else if ($this.data("todolistaction") === "close menu") {

                    $this.closest(".business-manager-todo-menu").hide();

                }

            });

            // Bind add input ENTER key handler
            todo.$ui.$newItemInput.on("keyup", function(ev){

                if (ev.which === 13) {

                    todo.$ui.$addButton.click();

                }

            });


            // Store this instance object on the dom element
            this.jqueryTodoList = todo;
            todo.$ui.css("display", "");

            // Focus on Title?
            if (todo.opt.focusOnTitle){

                setTimeout(function(){

                    todo.$ui.find("div.business-manager-todo-title")
                        .find(".business-manager-todo-title-text")
                            .click()
                            .end()
                        .find("input")
                            .select();

                }, 500);

            }

            return this;

        }); //end: this.each()

        return returnValue;

    }; //end: $.fn.todolist

}));
