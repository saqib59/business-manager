/*!
 * Business Manager JS file.
 */


/**
 * Parsley validators.
 */
window.Parsley.addValidator( 'gte', {
  validateString: function ( value, requirement ) {
    var end = Date.parse( value );
    var start = Date.parse( jQuery( requirement ).val() );


    return end >= start;
  },
  priority: 255,
} );

window.Business_Manager = window.Business_Manager || {};
( function ( window, document, $, app, undefined ) {
  'use strict';

  var $document;
  var defaults = {
    bm_metabox: $( '.cmb2-wrap.bm' ),
  };
  var $form = $( document.getElementById( 'post' ) );
  $( $form ).parsley();

  app.init = function () {
    $document = $( document );

    // Setup the CMB2 object defaults.
    $.extend( app, defaults );

    app.trigger( 'business_manager_init' );
    //$form.on( 'submit', app.checkValidation );
  };


  app.trigger = function ( evtName ) {
    var args = Array.prototype.slice.call( arguments, 1 );
    args.push( app );
    $document.trigger( evtName, args );
  };

  app.triggerElement = function ( $el, evtName ) {
    var args = Array.prototype.slice.call( arguments, 2 );
    args.push( app );
    $el.trigger( evtName, args );
  };

  app.checkValidation = function ( event ) {
    $form.parsley();
  }

  $( app.init );
  window.addEventListener( 'load', function () {
    $( '.dtheme-cmb2-tabs ul.ui-tabs-nav' ).each( function () {
      // Count the number of <li> elements within the current ul.ui-tabs-nav
      var liCount = $( this ).find( 'li' ).length;

      // If there is only one <li>, hide the current ul.ui-tabs-nav
      if ( liCount === 1 ) {
        $( this ).hide();
      }
    } );

    if ( !bm_l10.is_active_bm_custom_fields ) {
      var custom_tab = $( `<li class="bm-add-custom-tab ui-state-default"><a>${bm_l10.custom_tabs_text}</a></li>` );
      $( 'ul.ui-tabs-nav:first' ).append( custom_tab );
    }

    // on change event of leave start and end date.
    $( "#_bm_leave_end, #_bm_leave_start" ).on( "change", function () {
      let leave_start_date = $( "#_bm_leave_start" ).val();
      let leave_end_date = $( "#_bm_leave_end" ).val();

      if ( leave_start_date && leave_end_date ) {
        const weekdayCount = countWeekdays( leave_start_date, leave_end_date );
        $( "#_bm_leave_total_days" ).val( weekdayCount );
      }
    } );

  } )

  // To count number of days between two dates excluding weekends.
  function countWeekdays( startDate, endDate ) {
    const start = new Date( startDate );
    const end = new Date( endDate );
    const weekdays = [ 1, 2, 3, 4, 5 ]; // Monday to Friday.

    let count = 0;

    for ( let current = start; current <= end; current.setDate( current.getDate() + 1 ) ) {
      if ( weekdays.includes( current.getDay() ) ) {
        count++;
      }
    }

    return count;
  }

  $( document ).on( "click", ".bm-add-custom-tab", function () {
    $( ".bm-custom-tabs-install-modal" ).fadeIn();
  } );

  $( document ).on( "click", ".bm-add-custom-field-btn", function () {
    $( ".bm-custom-fields-install-modal" ).fadeIn();
  } );

  $( ".bm-modal-close" ).click( function () {
    $( ".bm-modal" ).fadeOut();
  } );

  $( "#_bm_employee_assets_box_inactive" ).click( function () {
    $( ".bm-assets-manager-install-modal" ).fadeIn();
  } );

  $( document ).on( "click", ".bm-add-contractors-btn", function () {
    $( ".bm-contractors-install-modal" ).fadeIn();
  } );

  $( ".bm-annoucement-title-row" ).click( function () {
    let annoucement_title = $( this ).find( ".name" ).text();
    let annoucement_desc = $( this ).find( ".desc" ).html();
    $( ".bm-announcement-modal .bm-annoucement-title" ).text( annoucement_title )
    $( ".bm-announcement-modal .bm-annoucement-desc" ).html( annoucement_desc )
    $( ".bm-announcement-modal" ).fadeIn();
  } );
  
  // Dismiss review notice.
  var ratingNotice = $( '.bm-notice-rating' );

  if ( ratingNotice.length ) {
    $( document ).on( "click", ".yes-leave-review, .dismissable-button, .notice-dismiss", function ( e ) {
      let dismiss = false;
      if ( !$( this ).hasClass( 'yes-leave-review' ) ) {
        e.preventDefault();
        dismiss = true;
      }

      $.ajax( {
        url: bm_l10.ajax_url,
        method: 'POST',
        data: {
          action: 'bm_ratings_nag_dismiss',
          nonce: bm_l10.nonce,
          isDismissed: dismiss
        },
        success: function ( response ) {
          if ( 200 == response.status ) {
            // Hide the notice.
            $( ratingNotice ).hide();
          }
        }
      } );
    } );
  }

} )( window, document, jQuery, window.Business_Manager );
