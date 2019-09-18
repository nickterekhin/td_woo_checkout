<?php


namespace TdWooCheckout\src\init;


class TD_Woo_Checkout_Template_init
{
    private $templates = array();
    private static $instance;
    static function getInstance()
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
    function init()
    {
        if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

            // 4.6 and older
            add_filter('page_attributes_dropdown_pages_args',
                array( $this, 'register_project_templates' )
            );
        } else {
            // Add a filter to the wp 4.7 version attributes metabox
            add_filter(
                'theme_page_templates', array( $this, 'add_new_template' )
            );

        }
        add_filter('page_attributes_dropdown_pages_args',array($this,'register_project_templates'));
        add_filter('wp_insert_post_data',array($this,'register_project_templates'));
        add_filter('template_include',array($this,'view_project_template'),99);
        $this->templates = array(
            //'hf_billing_signup.php' => 'HF Billing Template',
            'lv_response_hook.php' => 'LocalViking Hook Handler',
        );

    }
    public function add_new_template( $posts_templates ) {
        //var_dump($posts_templates);
        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    public function register_project_templates( $attributes ) {


        //var_dump($attributes);
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
        $templates = wp_get_theme()->get_page_templates();

        if ( empty( $templates ) ) {
            $templates = array();
        }
        // var_dump(wp_cache_get($cache_key,'themes'));
        wp_cache_delete( $cache_key , 'themes');
        $templates = array_merge( $templates, $this->templates );

        wp_cache_add( $cache_key, $templates, 'themes', 1800 );
        //var_dump(wp_cache_get($cache_key,'themes'));
        return $attributes;
    }
    public function view_project_template( $template )
    {

        global $post;
        if(!$post)
            return $template;

        if (!isset($this->templates[get_post_meta($post->ID, '_wp_page_template', true)] ) ) {
            return $template;
        }

        $file = TD_WOO_CHECKOUT_PLUGIN_DIR. 'templates/'.get_post_meta($post->ID, '_wp_page_template', true);

        if( file_exists( $file ) ) {
            return $file;
        }else
        {
            echo $file;
        }

        return $template;
    }
}