<?php


namespace TdWooCheckout\src\framework;


use function PHPSTORM_META\elementType;
use TdWooCheckout\src\api\client\ApiRequest;

class TD_Woo_Checkout_Framework
{


    function add_show_hide_css($hide=false)
    {

        if(get_transient('lv_choose')) {
            if ($hide)
                return "td-hide-cart-item";
            return "td-show-cart-item";
            }

        if($hide)
            return $this->is_user_has_lv_account()?"td-hide-cart-item":"td-show-cart-item";

        return !$this->is_user_has_lv_account()?"td-hide-cart-item":"td-show-cart-item";
    }


    function is_user_has_lv_account()
    {

        if(is_user_logged_in())
        {
            $lv_sub = get_user_meta(get_current_user_id(),'lv_sub',true);
            return $lv_sub;

        }
        return false;
    }

    function get_price_with_discount($total,$cart)
    {
         $discount = 0;

        if(td_woo_checkout_api()->is_user_has_lv_account())
            $discount = get_option('lv_member_discount');


        return round($total - ($total * $discount),$cart->dp);
    }

    function lv_response_handler()
    {


        if(isset($_GET['data'])) //&& preg_match('/localviking.com$/',$_SERVER['HTTP_REFERER'],$m)==1)
        {
            $args_data = json_decode(str_replace("\\",'',$_GET['data']),true);

            if(is_user_logged_in())
            {
                $userId = get_current_user_id();
                update_user_meta($userId,'lv_email',$args_data['email']);
                update_user_meta($userId,'lv_sub',isset($args_data['subscriber'])?true:false);
                update_user_meta($userId,'lv_token',isset($args_data['token'])?$args_data['token']:null);
                set_transient('lv_choose','yes');
                wp_redirect(get_permalink(get_page_by_path('cart')));
                exit;

            }else
            {
                $redirect_url = wp_login_url();

                if(!email_exists($args_data['email']))
                {

                    list($first_name, $last_name) = preg_match('/(.*?)@/', $args_data['email'], $m) == 1 ? $m[1] : array('guest', 'guest');
                    $args = array(
                        'user_login' => $args_data['email'],
                        'user_pass' => wp_generate_password(5),
                        'user_email' => $args_data['email'],
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'user_registered' => date('Y-m-d H:i:s'),
                        'role' => 'customer'

                    );
                    $userId = wp_insert_user($args);
                    if(!is_wp_error($userId))
                    {
                        add_user_meta($userId,'lv_email',$args_data['email']);
                        add_user_meta($userId,'lv_sub',isset($args_data['subscriber'])?1:0);
                        add_user_meta($userId,'lv_token',isset($args_data['token'])?$args_data['token']:'');
                        $redirect_url =add_query_arg(array('autologin'=>'true','user_login'=>$args['user_login'],'user_password'=>$args['user_pass']),$redirect_url);
                     }
                }

                wp_redirect($redirect_url);
                exit;
            }


        }
    }

    function get_location_from_lv()
    {
        if($this->is_user_has_lv_account())
        {
            try {
                $request = new ApiRequest();

                $request->initAuthorization(get_user_meta(get_current_user_id(), 'lv_token', true));

                $url = 'http://staging.localviking.com/api/dfy/locations';
                $res = $request->get($url);

                return $res;
            }catch (\Exception $ex)
            {
                echo $ex->getMessage();
            }
        }
        

    }

}

function td_woo_checkout_api()
{
    global $td_woo_checkout_api;
    if( !isset($td_woo_checkout_api) ) {

        $td_woo_checkout_api = new TD_Woo_Checkout_Framework();
    }

    return $td_woo_checkout_api;
}

td_woo_checkout_api();