<?php
/**
 * Plugin Name: Quick Search And Only One Category Posting
 * Description: Quick Search And Only One Category Posting support quickly selecting category via search box when there are many categories. Besides, you can use radio button instead of checkbox when you want clients to choose 1 category only. Quick Search And Only One Category Posting supports all custom post types and custom taxonomy activated in theme and plugin.
 * Version: 1.0.3
 * Author: Hoang Quoc Long
 * Author URI: http://hoangquoclong.com
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Quick Search And Only One Category Posting plugin, Copyright 2017 hoangquoclong.com
 * Quick Search And Only One Category Posting is distributed under the terms of the GNU GPL
 *
 * Requires at least: 4.4
 * Tested up to: 4.8.2
 * Text Domain: qsoocp
 * Domain Path: /languages/
 *
 * @package Quick Search And Only One Category Posting
 * @subpackage Quick Search And Only One Category Posting
 */

define( 'QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_DIR', plugin_dir_url(__FILE__) );
define( 'QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_PATH', plugin_dir_path(__FILE__) );

add_action( 'plugins_loaded', array( 'Quick_Search_Only_One_Category_Posting', 'plugins_loaded' ) );	
add_action( 'after_setup_theme', array( 'Quick_Search_Only_One_Category_Posting', 'after_setup_theme' ), 5 );

class Quick_Search_Only_One_Category_Posting {

	function __construct(){		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 15 );
		add_filter( 'admin_menu', 'qsoocp_update_term_box', 9999 );

		require_once QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_PATH . 'inc/config.php';
		require_once QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_PATH . 'inc/walker.php';
		require_once QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_PATH . 'inc/settings-page.php';
	}

	function admin_enqueue_scripts(){
		global $pagenow;
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'edit.php', 'options-general.php' ) ) ) {
			wp_enqueue_style( 'qsoocp-style', QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_DIR . 'assets/css/style.css', NULL, NULL );	

			wp_enqueue_script( 'repeater', QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_DIR . 'assets/js/jquery.repeater.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'qsoocp', QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_DIR . 'assets/js/script.js', array( 'jquery' ), null, true );
		}
	}

	public static function plugins_loaded() {
		load_plugin_textdomain( 'qsoocp', false, QUICK_SEARCH_ONLY_ONE_CATEGORY_POSTING_PATH . '/languages/' );
	}

	public static function after_setup_theme() {
		new Quick_Search_Only_One_Category_Posting();							
	}

}
