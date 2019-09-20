<?php


namespace TdWooCheckout\src\init;


use function TdWooCheckout\src\framework\td_woo_checkout_api;
use WC_Coupon;

class TD_Woo_Checkout_Init
{
    private static $instance;

    public static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_nopriv_set_lv_choose', array($this, 'set_lv_choose'));
        add_action('wp_ajax_set_lv_choose', array($this, 'set_lv_choose'));
        TD_Woo_Checkout_Template_init::getInstance()->init();
        TD_Woo_Checkout_Woocommerce_init::getInstance()->init();
    }

    function init()
    {
        add_action('wp',array($this,'init_front'));
        add_action('after_setup_theme',array($this,'init_auto_login'));
        add_action('wp_enqueue_scripts',array($this,'init_front_end_resources'));


    }

        function init_front()
        {

            if(!is_ajax() && !is_page('cart')) delete_transient('lv_choose');
        }

        function init_auto_login()
        {
            if (isset($_GET['autologin']) && !empty($_GET['autologin']) && $_GET['autologin'] == 'true') { //need check referer

                $creds=array(); //needs refactoring
                $creds['user_login'] = $_GET['user_login'];

                $creds['user_password'] = $_GET['user_password'];

                $creds['remember'] = true;
                $autologin_user = wp_signon( $creds, false );
                //set_transient('lv_choose','yes');
                if ( !is_wp_error($autologin_user) )
                    wp_redirect(get_permalink(get_page_by_path('cart')));
            }
        }



    function init_front_end_resources()
    {
        if (is_front_page()) wp_dequeue_script('wc-cart-fragments');
        //wp_enqueue_script('td_woo_checkout_js',TD_WOO_CHECKOUT_PLUGIN_URL.'/assets/js/jquery.js');
        wp_enqueue_script('td_woo_checkout_js',TD_WOO_CHECKOUT_PLUGIN_URL.'/assets/js/td_woo_checkout.js');

        wp_enqueue_style('td_woo_checkout_css',TD_WOO_CHECKOUT_PLUGIN_URL.'/assets/css/td_woo_checkout_style.css');

    }

    function set_lv_choose()
    {
        if(isset($_POST['choose']))
        {
            set_transient('lv_choose',$_POST['choose']);
        }
        die();
    }

}