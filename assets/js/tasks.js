/*!
 * jQuery namespaced 'Starter' plugin boilerplate
 * Author: @dougneiner
 * Further changes: @addyosmani
 * Licensed under the MIT license
 */

window.BusinessManagerTasks = window.BusinessManagerTasks || {};
(function(window, document, $, tasks, undefined){
    'use strict';

    var $document;

    var defaults = {
        wrap: $('#bm-tasks'),
        form: $('#add-edit-task'),
        uls: $('#bm-tasks .sortable'),
        nonce: business_manager_plugin.nonce,
        ajax_url: business_manager_plugin.ajax_url,
    };

    tasks.init = function() {
        $document = $( document );

         // Setup the CMB2 object defaults.
        $.extend( tasks, defaults );

        tasks.formInit();
        tasks.dashboardInit();

        tasks.wrap
            .on( 'click', '#new-task', tasks.newTask )
            .on( 'click', '#save-task', tasks.saveTask )
            .on( 'click', '#delete-task', tasks.deleteTask )
            .on( 'click', '#edit-task', tasks.editTask )
            .on( 'click', '#add-file', tasks.addFile )
            .on( 'click', '#remove-file', tasks.removeFile );

        tasks.trigger( 'business_manager_init' );

    };

    tasks.formInit = function() {
        
        tasks.resetForm();

        tasks.form.find( '.datepicker' ).datepicker();
        tasks.form.find( '.colorpicker' ).wpColorPicker();

        tasks.form.find( "#add-todo" ).todoList({
            removeLabel: business_manager_plugin.todo_remove,
            newItemPlaceholder: business_manager_plugin.todo_placeholder,
            editItemTooltip: business_manager_plugin.todo_tooltip,
            focusOnTitle: true,
            customActions: null,
            items: []
        });

        tasks.form.find( ".business-manager-todo-items" ).sortable({
            items: '.business-manager-todo-item',
        }).disableSelection();

        tasks.form.bind('keypress keydown keyup', function(e){
            if(e.keyCode == 13) {
                if ( $("#save-task").is(":focus") || $("#title").is(":focus") || $("#notes").is(":focus") ) {
                    // do notask, allow enter
                } else {
                    e.preventDefault();
                    return false;
                }
            }
        });

    };


    tasks.dashboardInit = function() {

        tasks.uls.sortable({
            connectWith: ".connected",
            handle: ".handle",
            stop : function(e, ui) {
                tasks.updateTasks();
            }
        }).disableSelection();

    };

    tasks.newTask = function() {
        var form = $('.col-left' );
        if( form.is(":visible") ) {
            $('.col-left').hide();
            $('#new-task').text( business_manager_plugin.add_task );
        } else {
            $('.col-left').show();
            $('#new-task').text( 'Hide' );
        }

        
    };
 
    tasks.updateTasks = function() {

        var post_id = $( '#post_ID' ).val();

        // work out the positions and put into array
        var positions = [];
        tasks.uls.each(function(){
            positions.push($(this).sortable('toArray', {attribute: 'data-id'}));
        });
        //console.log(positions);
        jQuery.ajax({
            url :       tasks.ajax_url,
            type :      'post',
            dataType:   'json',
            data : { 
                action: "update_tasks", 
                positions : positions,  
                post_id : post_id,  
                nonce : tasks.nonce, 
            },
            success : function(response) {
                //tasks.message( response.message );
            }
        });
    };


    tasks.getTodos = function() {
        // get todo list data
        var todos = [];
        $(".business-manager-todo-item").each(function(){
            var item = $(this).find(".business-manager-todo-item-title-text").text();
            var done = $(this).hasClass( 'business-manager-todo-item-done' ) ? 'true' : 'false';
            todos.push({ 'item': item, 'done': done });
        });
        return todos;
    };

    
    tasks.saveTask = function(e) {
        e.preventDefault(); 
        //$('.add-task').hide();
        var form = $('.col-left' );
        form.hide();
        $('#new-task').text( business_manager_plugin.add_task );
        // get all posted data
        var task_id = 1;
        var title   = tasks.form.find( '#title' ).val(),
            notes   = tasks.form.find( '#notes' ).val(),
            start   = tasks.form.find( '#start_date' ).val(),
            end     = tasks.form.find( '#end_date' ).val(),
            file    = tasks.form.find( '#file_data' ).val(),
            color   = tasks.form.find( '#color' ).val(),
            post_id = $( '#post_ID' ).val();
            task_id = tasks.form.find( '#task_id' ).val();
            if(task_id == undefined)
            {
                task_id = 1;
            }

        var posted  = { 
            'title': title,
            'notes': notes,
            'start_date': start,
            'end_date': end,
            'file': file,
            'color': color,
            'post_id': post_id,
            'task_id': task_id,
        };
           
        jQuery.ajax({
            url :       tasks.ajax_url,
            type :      'post',
            dataType:   'json',
            data : { 
                action : "save_task", 
                todos : tasks.getTodos(), 
                posted : posted,
                nonce : tasks.nonce,
            },
            success : function(response) {
                if(response.result == "success") {
                    // either add new task
                    // or update existing task
                    if( response.edit == '0' ) {
                        tasks.uls.first().prepend(response.data);
                    } else {
                        tasks.uls.find('[data-id="'+response.edit+'"]').replaceWith(response.data);
                    }

                    // update the tasks
                    tasks.updateTasks();

                    // reset the form
                    tasks.resetForm();

                }

                tasks.message( response.message );
            }
        });  

    };

    tasks.editTask = function(e) {
        e.preventDefault(); 
        tasks.newTask();
        // get id of the task
        var task_id = $(this).parent('.task').attr('data-id');
        var post_id = $( '#post_ID' ).val();
                
        jQuery.ajax({
            url :       tasks.ajax_url,
            type :      'post',
            dataType:   'json',
            data : { 
                action : "edit_task", 
                task_id : task_id, 
                post_id : post_id, 
                nonce : tasks.nonce,
            },
            success : function(response) {
                
                if(response.result == "success") {                    
                    tasks.populateForm( response.data );
                    tasks.wrap.find( '.add-task > h2' ).text( business_manager_plugin.edit_task );
                    $( '#task_id' ).val( task_id );                    
                }

                tasks.message( response.message );
            }
        });

    };

    tasks.deleteTask = function(e) {
    
        e.preventDefault(); 

        var elm = $(this);
        var yes = "<span class='confirm-delete'>"+ business_manager_plugin.delete +"</span>";
        var no = "<span class='cancel-delete'>"+ business_manager_plugin.cancel +"</span>";
        var task = $( elm ).parent('.task');
        var task_id = $( task ).attr('data-id');
        
        // show the buttons
        $( task ).find('.confirm').remove();
        $( task ).find('.inner').prepend( '<span class="confirm">' + no + yes + '</span>' );

        $(".confirm-delete").click( function(event) {
            $( elm ).parent( '.task' ).fadeOut("normal", function() {
                $(this).remove();
            });
            tasks.deleteConfirm( task_id );
        });

        $(".cancel-delete").click( function(event) {
            $( task ).find( '.confirm' ).fadeOut("normal", function() {
                $(this).remove();
            });
        });

        // if no action, hide it
        setTimeout( function() {
            $('.confirm').fadeOut('slow');
        }, 5000);
        
        

    };

    tasks.deleteConfirm = function( task_id ) {

        var post_id = $( '#post_ID' ).val();

        jQuery.ajax({
            url :       tasks.ajax_url,
            type :      'post',
            dataType:   'json',
            data : { 
                action : "delete_the_task", 
                task_id : task_id, 
                post_id : post_id, 
                nonce : tasks.nonce,
            },
            success : function(response) {
                
                if(response.result == "success") {
                    tasks.resetForm();
                    // update the tasks
                    tasks.updateTasks();
                }
                tasks.message( response.message );
            }
        });

    };

    tasks.message = function( message ) {
        $('.business-manager-message')
            .html('')
            .appendTo('#bm-tasks')
            .fadeIn(600)
            .css({'position':'absolute', 'top' : -30, 'right': 10})
            .prepend( message )
            .delay(1500)
            .fadeOut('slow');
    };

    tasks.populateForm = function(data) {

        tasks.resetForm();

        var $form = tasks.form;

        $.each(data, function(key, value) {
 
            var $ctrl = $form.find('[name='+key+']');

            if ($ctrl.is('select')){
                $('option', $ctrl).each(function() {
                    if (this.value == value)
                        this.selected = true;
                });
            } else if ($ctrl.is('textarea')) {
                $ctrl.val(value);
            } else {
                switch($ctrl.attr("type")) {
                    case "text":
                    case "hidden":
                        $ctrl.val(value);   
                        break;
                    case "checkbox":
                        if (value == '1')
                            $ctrl.prop('checked', true);
                        else
                            $ctrl.prop('checked', false);
                        break;
                } 
            }

            if( key == 'todos' ) {
                $.each(value, function(i, todo) {
                    var $el = $( business_manager_plugin.todo_item );
                    $( '.business-manager-todo-items' ).append( $el );
                    $el.find( '.business-manager-todo-item-title-text' ).text( todo.item );
                    if( todo.done === 'true' ) {
                        $el.addClass( 'business-manager-todo-item-done' );
                    }
                });
            }

            // if( key == 'tags' ) {

            //     $.each(value, function(i, tag) {
            //         // Set the value, creating a new option if necessary
            //         if ($('#tags').find("option[value='" + tag + "']").length) {
            //             $('#tags').val(tag).trigger('change');
            //         } else { 
            //             // Create a DOM Option and pre-select by default
            //             var newOption = new Option(tag, i, true, true);
            //             // Append it to the select
            //             $('#tags').append(newOption).trigger('change');
            //         }  
            //     });

            // }

            if( key == 'file' && value !== '' ) {
                tasks.insertFileData( value );
            }

            if( key == 'color' && value !== '' ) {
                $form.find( '.wp-color-result' ).css({'background-color': value })
                $form.find( 'input#color' ).val(value)
            }

        });

    };

    tasks.addFile = function( e ) {
        // Stop the anchor's default behavior
        e.preventDefault();
        // Display the media uploader
        tasks.mediaUploader();
    };

    tasks.removeFile = function( e ) {
        // Stop the anchor's default behavior
        e.preventDefault();
        // Display the media uploader
        tasks.resetUploadForm();
    };

    tasks.resetUploadForm = function() {
        // First, we'll hide the image
        $( '#file-container' )
            .children( 'img' )
            .remove();
     
        // Then display the previous container
        $( '#file-container' )
            .prev()
            .show();
     
        // Finally, we add the 'hidden' class back to this anchor's parent
        $( '#file-container' )
            .next()
            .hide()
            .addClass( 'hidden' );

        $( '#file_data' ).val('');
    };

    /**
     * Callback function for the 'click' event of the 'Set Footer Image'
     * anchor in its meta box.
     *
     * Displays the media uploader for selecting an image.
     *
     * @since 0.1.0
     */
    tasks.mediaUploader = function() {
     
        var file_frame, image_data;

        _wpMediaViewsL10n.insertIntoPost = business_manager_plugin.insert_file;
        /**
         * If an instance of file_frame already exists, then we can open it
         * rather than creating a new instance.
         */
        if ( undefined !== file_frame ) {
            file_frame.open();
            return;
        }
     
        /**
         * If we're this far, then an instance does not exist, so we need to
         * create our own.
         *
         * Here, use the wp.media library to define the settings of the Media
         * Uploader. We're opting to use the 'post' frame which is a template
         * defined in WordPress core and are initializing the file frame
         * with the 'insert' state.
         *
         * We're also not allowing the user to select more than one image.
         */
        file_frame = wp.media.frames.file_frame = wp.media( {
            frame:    'post',
            state:    'insert',
            multiple: false,
        });
            
        file_frame.on( 'menu:render:default', function( view ) {
            // Store our views in an object.
            var views = {};

            // Unset default menu items
            view.unset( 'library-separator' );
            view.unset( 'gallery' );
            view.unset( 'featured-image' );
            view.unset( 'embed' );

            // Initialize the views in our view object.
            view.set( views );
        } );

        /**
         * Setup an event handler for what to do when an image has been
         * selected.
         *
         * Since we're using the 'view' state when initializing
         * the file_frame, we need to make sure that the handler is attached
         * to the insert event.
         */
        file_frame.on( 'insert', function() {

            // Read the JSON data returned from the Media Uploader
            var json = file_frame.state().get( 'selection' ).first().toJSON();
            
            // First, make sure that we have the URL of an image to display
            if ( 0 > $.trim( json.url.length ) ) {
                return;
            }
            
            var img;

            // After that, set the properties of the image and display it
            if( json.type === 'image' ) {
                img = json.url;
            } else {
                img = json.icon;
            }

            var file_data = {};
            file_data.id = json.id;
            file_data.preview = img;
            file_data.filename = json.filename;
            file_data.date = json.dateFormatted;
            file_data.editurl = json.editLink;
            file_data.filesize = json.filesizeHumanReadable;
            file_data.subtype = json.subtype;
            file_data.url = json.url;

            tasks.insertFileData( file_data );
            
        });
     
        // Now display the actual file_frame
        file_frame.open();
     
    };


    tasks.insertFileData = function( file_data ) {

        $( '#file-container' )
            .append( '<img src="' + file_data.preview + '" />' )
                .show()
            .parent()
            .removeClass( 'hidden' );
     
        // Next, hide the anchor responsible for allowing the user to select an image
        $( '#file-container' )
            .prev()
            .hide();

        // Display the anchor for the removing the featured image
        $( '#file-container' )
            .next()
            .show();

        // populate our input with image data to be saved
        $( '#file_data' ).val( JSON.stringify( file_data ) );

    };


    tasks.resetForm = function() {
        tasks.wrap.find( '.add-task > h2' ).text( business_manager_plugin.add_task );
        tasks.form.find('input:text, input:hidden, input:password, input:file, textarea, select').val('');
        tasks.form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        tasks.form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        tasks.form.find('select').val(null).trigger('change');
        tasks.form.find('.business-manager-todo-items').html(null);
        tasks.form.find('.wp-color-result').css({'background-color':'#f7f7f7'});
        tasks.resetUploadForm();
        tasks.form.find("#title").focus();
    };

    tasks.trigger = function( evtName ) {
        var args = Array.prototype.slice.call( arguments, 1 );
        args.push( tasks );
        $document.trigger( evtName, args );
    };

    tasks.triggerElement = function( $el, evtName ) {
        var args = Array.prototype.slice.call( arguments, 2 );
        args.push( tasks );
        $el.trigger( evtName, args );
    };

     
    $( tasks.init );

})(window, document, jQuery, window.BusinessManagerTasks);