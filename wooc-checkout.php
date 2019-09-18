<?php

/*
Plugin Name: Wooc Checkout
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: terekhin
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

namespace TdWooCheckout;

use TdWooCheckout\src\TD_Woo_Checkout_Main;

if ( !function_exists( 'add_action' ) ) {
    echo 'It is a plugin and you can\'t run it directly ';
    exit;
}


define('TD_WOO_CHECKOUT_VERSION','1.0.5');
define('TD_WOO_CHECKOUT_ROOT_PATH',__FILE__);
define('TD_WOO_CHECKOUT_PLUGIN_URL',plugin_dir_url(TD_WOO_CHECKOUT_ROOT_PATH));
define('TD_WOO_CHECKOUT_PLUGIN_DIR',dirname(__FILE__)."/");

require TD_WOO_CHECKOUT_PLUGIN_DIR.'src/TD_Woo_Checkout_Main.php';
include TD_WOO_CHECKOUT_PLUGIN_DIR.'src/framework/TD_Woo_Checkout_Framework.php';

TD_Woo_Checkout_Main::getInstance();