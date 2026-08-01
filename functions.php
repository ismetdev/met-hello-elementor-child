<?php
/**
 * Met Hello Elementor Child: theme bootstrap.
 *
 * This file only defines the version and loads the modules in inc/. Keep it that
 * way: put new behaviour in the matching inc/ file, or add a new one here.
 *
 * Scope: native WordPress blog Posts and their category/tag/date archives, plus
 * search, author and 404. Everything is namespaced with the met_hello_child_ /
 * MET_HELLO_CHILD_ prefix and never touches the parent theme, Elementor pages, or
 * the MetCPT plugin.
 *
 * @package MetHelloElementorChild
 */

// No direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version. Bump this together with the header in style.css. Used for asset
 * cache-busting, and read by the update checker.
 */
define( 'MET_HELLO_CHILD_VERSION', '1.5.0' );

/**
 * Absolute path to the theme directory, with a trailing slash.
 */
define( 'MET_HELLO_CHILD_DIR', trailingslashit( get_stylesheet_directory() ) );

/**
 * Public URL of the theme directory, with a trailing slash.
 */
define( 'MET_HELLO_CHILD_URI', trailingslashit( get_stylesheet_directory_uri() ) );

/**
 * Load the theme modules. Order matters: setup defines the view test that assets
 * relies on.
 */
require_once MET_HELLO_CHILD_DIR . 'inc/setup.php';
require_once MET_HELLO_CHILD_DIR . 'inc/updater.php';
require_once MET_HELLO_CHILD_DIR . 'inc/assets.php';
require_once MET_HELLO_CHILD_DIR . 'inc/template-tags.php';
require_once MET_HELLO_CHILD_DIR . 'inc/social.php';
require_once MET_HELLO_CHILD_DIR . 'inc/maintenance.php';
