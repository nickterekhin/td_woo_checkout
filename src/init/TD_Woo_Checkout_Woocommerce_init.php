<?php


namespace TdWooCheckout\src\init;


use function TdWooCheckout\src\framework\td_woo_checkout_api;

class TD_Woo_Checkout_Woocommerce_init
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

    }
    public function init()
    {
        add_filter('woocommerce_locate_template',array($this,'init_woo_commerce_templates'),10,3);
        //add_action('woocommerce_before_cart_table',array($this,'add_lv_discount'));
        add_filter('woocommerce_checkout_fields', array($this,'add_custom_fields'));
        add_action('woocommerce_checkout_update_order_meta',array($this,'save_lv_location'),10,2);
        add_filter('woocommerce_calculated_total',array($this,'calculate_total_with_lv_discount'),10,2);
    }

    function calculate_total_with_lv_discount($total,$cart)
    {
        return td_woo_checkout_api()->get_price_with_discount($total,$cart);
    }
    function add_custom_fields($fields)
    {

        if( td_woo_checkout_api()->is_user_lv_subscriber() ) {


            $res = td_woo_checkout_api()->get_location_from_lv();

            $fields['location'] = array('location_field' => array(
                'label' => 'Locations',
                'required' => true,
                'type' => 'select',
                'options' => array()
            ));

            foreach ($res as $key=>$val)
            {
                $fields['location']['location_field']['options'][$val->id."|".$val->title]=$val->title;
            }
        }else
        {
            $fields['location']=array();
        }

        return $fields;


    }
    function save_lv_location($oder_id,$data)
    {

        update_post_meta($oder_id,'lv_location',preg_split("/\|/",$data['location_field']));

        return true;
    }

    function init_woo_commerce_templates($template, $template_name, $template_path)
    {
        $plugin_path = TD_WOO_CHECKOUT_PLUGIN_DIR.'/templates/woo/';

        if(file_exists($plugin_path.$template_name))
            return  $plugin_path.$template_name;

        return $template;
    }

}