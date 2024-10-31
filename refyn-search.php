<?php
/*
 * Plugin Name:       REFYN Search
 * Plugin URI:        http://refyn.org
 * Description:       REFYN is Smarter than Google. Text & Product search with Artificial Intelligence and Never Zero Results or Not Found.
 * Version:           2.1.0
 * Author:            REFYN
 * Author URI:        http://www.refyn.org
 * WP tested up to: 5.7
 * Requires PHP: 7.1
 * License: GPLv2 or later
 * REFYN Search. Plugin for the REFYN plugin. Copyright © 2016, All Rights Reserved.
 */
 
defined( 'ABSPATH' ) || exit;
define( 'plugin_path', plugin_dir_path( dirname(__FILE__ , 2) ) );
define( 'plugin_url', untrailingslashit(plugins_url('/', __FILE__)) );
define( 'plugin', plugin_basename( dirname(__FILE__ , 3) ) );

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/
	
require_once 'includes/class-refyn-search.php';
require_once 'includes/class-refyn-search-shortcodes.php';
new Refyn_Search();
