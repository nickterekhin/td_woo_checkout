<?php


namespace TdWooCheckout\src;

use Exception;
use TdWooCheckout\src\init\TD_Woo_Checkout_Init;
use TdWooCheckout\src\services\TD_Woo_Checkout_Autoloader;

if(!defined('ABSPATH'))
{
    die('-1');
}
if(!class_exists('TD_Woo_Checkout_Main')) {
    class TD_Woo_Checkout_Main
    {
        protected static $instance;

        public static function getInstance()
        {
            if(!self::$instance)
            {
                self::$instance = new self;
            }
            return self::$instance;
        }
        private function __construct(){

            register_activation_hook(TD_WOO_CHECKOUT_ROOT_PATH,array($this,'activate'));
            register_deactivation_hook(TD_WOO_CHECKOUT_ROOT_PATH,array($this,'deactivate'));
            $this->init_autoloader();
            add_action('plugins_loaded',array($this,'init_td_woo_checkout'),0);
        }

        public function init_td_woo_checkout()
        {
            TD_Woo_Checkout_Init::getInstance()->init();
        }
        function activate()
        {
            update_option('lv_member_discount',0.20);

            $page = get_page_by_path('lv_hook_handler');
            if(!$page)
            {
                $args = array(

                    'post_author' => 1,
                    'post_date' => date("Y-m-d",time()),
                    'post_date_gmt' => date("Y-m-d",time()),
                    'post_name' => 'lv_hook_handler',
                    'post_title' => 'LV Hook Handler',
                    'post_status' =>'publish',
                    'post_type' =>'page',
                    'meta_input' => array(
                        '_wp_page_template'=>'lv_response_hook.php',
                    )

                );
                $post_ID = wp_insert_post($args);

                if(!is_wp_error($post_ID))
                {

                }

            }

        }
        function deactivate()
        {


            $page = get_page_by_path('lv_hook_handler');
            if($page)
            {
                wp_delete_post($page->ID);
            }

            delete_option('lv_member_discount');
        }

        private function init_autoloader()
        {
            if(!class_exists('TD_Woo_Checkout_Autoloader'))
            {
                require_once TD_WOO_CHECKOUT_PLUGIN_DIR.'src/services/TD_Woo_Checkout_Autoloader.php';
            }

            $autoLoader = TD_Woo_Checkout_Autoloader::getInstance();
            $autoLoader->setPrefixes(array("TdWooCheckout"=>TD_WOO_CHECKOUT_PLUGIN_DIR));
            $autoLoader->register_auto_loader();

        }


    }
}