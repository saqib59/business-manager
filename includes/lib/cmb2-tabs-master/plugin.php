<?php
/*
Plugin Name: Tabs for CMB2
Plugin URI: https://github.com/LeadSoftInc/cmb2-tabs
Description: Extensions the tabs to the library CMB2
Version: 1.2.3
Author: LeadSoft Inc.
Author URI: http://leadsoft.org/
*/

namespace cmb2_tabs;

if ( is_admin() ) {
	
	include_once( 'inc/assets.class.php' );
	include_once( 'inc/cmb2-tabs.class.php' );
	
	// Run autoloader
	//include __DIR__ . '/autoloader.php';

	// Connection css and js
	new inc\Assets();

	// Run global class
	new inc\CMB2_Tabs();
}
