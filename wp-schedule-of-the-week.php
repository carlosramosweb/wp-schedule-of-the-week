<?php
/*---------------------------------------------------------
Plugin Name: WP Schedule of the Week
Author: carlosramosweb
Author URI: https://criacaocriativa.com
Donate link: https://donate.criacaocriativa.com
Description: Esse plugin é uma versão BETA. Shortcode [schedule_of_the_week]
Text Domain: wp-schedule-of-the-week
Domain Path: /languages/
Version: 1.0.0
Requires at least: 3.5.0
Tested up to: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 
------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! class_exists( 'WP_Schedule_of_the_Week' ) ) {   

    class WP_Schedule_of_the_Week {

        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init_functions' ) );
            register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
            //register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );
        }
        //=>

        public function init_functions() {
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links_settings' ) );
            add_action( 'init', array( $this, 'register_posttype' ) ); 
            add_shortcode( 'schedule_of_the_week', array( $this, 'get_schedule_of_the_week' ) );  
        }
        //=>

        public static function plugin_action_links_settings( $links ) { 
            $settings_url   = esc_url( admin_url( 'edit.php?post_type=schedule-of-the-week' ) );
            $donate_url     = esc_url( 'https://donate.criacaocriativa.com' );
            $settings_text  = __( 'Settings Plugin', 'wp-schedule-of-the-week' );
            $donate_text    = __( 'Donation Plugin', 'wp-schedule-of-the-week' );

            $action_links = array(
                'settings'  => '<a href="' . $settings_url . '" title="'. $settings_text .'" class="error">'. $settings_text .'</a>',
                'donate'    => '<a href="' . $donate_url . '" title="'. $donate_text .'" class="error">'. $donate_text .'</a>',
            );  
            return array_merge( $action_links, $links );
        }
        //=>

        public static function load_plugin_textdomain() {
            load_plugin_textdomain( 'wp-schedule-of-the-week', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
        }
        //=>

        public function register_posttype() {
            $args = array(
                'public'                => true,
                'label'                 => 'Programação',
                'public_queryable'      => true,
                'exclude_from_search'   => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'show_in_nav_menus'     => true,
                'show_in_admin_bar'     => true,
                'capability_type'       => 'post',
                'query_var'             => true,
                'menu_icon'             => 'dashicons-calendar-alt',
                'supports'              => array( 'title', 'editor' ), 
                //'taxonomies'            => array( 'category' ),
                'rewrite'               => array(
                    'slug'          => 'schedule-week',
                    'with_front'    => false
                ),
                // 'title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt', 'page-attributes', 'thumbnail', 'custom-fields', and 'post-formats'
            );
            register_post_type( 'schedule-week', $args );

            $args = array(
                'hierarchical'      => true,
                'labels'            => 'Dias da Semana',
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'days-week' ),
            );
         
            register_taxonomy( 'days-week', array( 'schedule-week' ), $args );
        }
        //=>

        public function register_meta_boxes() {
            add_meta_box( 
                'meta-box-id', 
                __( 'Configuração', 'wp-schedule-of-the-week' ), 
                array( $this, 'schedule_of_the_week_display_callback' ),
                'schedule-week',
                'advanced',
                'high'
            );
        }
        //=>

        public function get_accordion_item( $item ) {
            switch ( $item ) {
                case 0:
                    return "One";
                    break;
                case 1:
                    return "Two";
                    break;
                case 2:
                    return "Three";
                    break;
                case 3:
                    return "Four";
                    break;
                case 4:
                    return "Five";
                    break;
                case 5:
                    return "Six";
                    break;
            }
        }
        //=>

        public function get_schedule_of_the_week( $atts ) {
            global $post;

            $categories = get_terms( 
                'days-week', 
                array( 'hide_empty' => false ) 
            );

            if ( $categories ) {
                $i = 0;
                echo '<div class="accordion" id="accordion-schedule-week">';
                foreach ( $categories as $key => $category ) {

                    echo '<div class="accordion-item">';
                    echo '<h2 class="accordion-header" id="heading' . $this->get_accordion_item( $i ) . '">';
                    echo '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $this->get_accordion_item( $i ) . '" aria-expanded="true" aria-controls="collapse' . $this->get_accordion_item( $i ) . '">';
                    echo $category->name;
                    echo '</button>';
                    echo '</h2>';

                    $args = array(
                        'numberposts'   => 20,
                        'post_type'     => 'schedule-week',
                        'orderby'       => 'menu_order',
                        'sort_order'    => 'asc',
                        'tax_query'     => array(
                            array(
                            'taxonomy'    => 'days-week',
                            'field'       => 'term_id', 
                            'terms'       => $category->term_id,
                            'include_children' => false
                            )
                        )
                    );

                    $professional = get_posts( $args );

                    if ( $professional ) {
                        echo '<div id="collapse' . $this->get_accordion_item( $i ) . '" class="accordion-collapse collapse show" aria-labelledby="heading' . $this->get_accordion_item( $i ) . '" data-bs-parent="#accordion-schedule-week">';
                        foreach ( $professional as $post ) { 
                            setup_postdata( $post ); 
                            $the_title = get_the_title();
                            $the_content = get_the_content();
                            echo '<div class="accordion-body">';
                            echo '<strong class="title" style="display:block;">' . $the_title . '</strong>';
                            echo $the_content;
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    $i++;
                }
                echo '</div>';
            }
            ?>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
            <?php
        }
        //=>

    }
    //=>
    new WP_Schedule_of_the_Week();
}