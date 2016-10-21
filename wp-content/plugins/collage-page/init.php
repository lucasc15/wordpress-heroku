<?php
    /*
    Plugin Name: Cloudinary Collage Maker
    Plugin URI: http://www.doesnotexist.com
    Description: Plugin for creating collages and adding tags to images loaded on cloudinary
    Author: Lucas Currah
    Version: 0.1.0
    Author URI: http://lucas15.github.io
    */

include_once( 'collage.php' );
include_once( 'admin_page.php' );

register_activation_hook(__FILE__, 'init_db' );

/*Admin shortcode and hooks*/
add_shortcode("show-image-urls", "show_image_handler");
add_action('admin_menu', 'admin_page_handler');
add_action("admin_post_upload_image", "upload_image_handle");
add_action("admin_post_insert_image_tag", "insert_image_tag_handle");

function admin_plugin_css_js() {
    wp_register_style('admin-collage-style', plugins_url('/css/admin-collage-maker.css',__FILE__ ));
    wp_enqueue_style('admin-collage-style');
    wp_register_script( 'tagUI', plugins_url('/js/admin.js',__FILE__ ));
    wp_enqueue_script('tagUI');
}
add_action('admin_enqueue_scripts', 'admin_plugin_css_js');

/*Front end shortcode and scipts*/
function front_end_css_js() {
    wp_register_style('collage-style', plugins_url('/css/collage-maker.css', __FILE__));
    wp_enqueue_style('collage-style');
}
add_action('wp_enqueue_scripts', 'front_end_css_js');
add_shortcode('make-collage', 'make_collage_handler');

function make_collage_handler($atts = [], $content=null, $tags=''){
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $wp_atts = shortcode_atts([
                                    'tags' => 'all',
                                 ], $atts, $tag);
    echo $wp_att['tags'];
    $p_urls = search_tags($wp_att['tags']);
    make_collage($p_urls);
}

function upload_image_handle() {
    upload_image();
    refresh_cloudinary_urls('upload');
}

function insert_image_tag_handle() {
    global $_POST;
    insert_image_tag($_POST['p_url'], $_POST['tags'], $_POST['caption']);
}

/* SQLite init_db, may need to change syntax for PostgreSQL */
function init_db(){
    global $wpdb;
    $charset_collate = $wpdb -> get_charset_collate(); 

    $table_picture = $wpdb->prefix."pictures"; 
    $table_tags = $wpdb->prefix."tags"; 
    $table_picturetag = $wpdb->prefix."picturetag"; 

    $sql_pictures = "CREATE TABLE $table_picture ( 
        id SERIAL PRIMARY KEY,
        p_url varchar(200) NOT NULL,
        p_name varchar(100) NOT NULL,
        caption text 
    )";

    $sql_tags = "CREATE TABLE $table_tags (
        id SERIAL PRIMARY KEY,
        tag_name varchar(55) NOT NULL
    )";

    $sql_picturetag = "CREATE TABLE $table_picturetag (
        id SERIAL PRIMARY KEY,
        tag_id INTEGER NOT NULL, 
        picture_id INTEGER NOT NULL
    )";


    require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql_pictures );
    dbDelta( $sql_tags );
    dbDelta( $sql_picturetag);
}