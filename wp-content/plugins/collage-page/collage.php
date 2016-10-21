<?php

/*
Plugin Name: Collage Maker
*/

include_once( dirname(__FILE__).'/cloudinaryAPI/Cloudinary.php' );
include_once( dirname(__FILE__).'/cloudinaryAPI/Uploader.php' );
include_once( dirname(__FILE__).'/cloudinaryAPI/Api.php' );

$api = new \Cloudinary\Api();
$uploader = new \Cloudinary\Uploader();
\Cloudinary::config(array(
    "cloud_name" => "drfb5ts4d",
    "api_key" => "954541261474557",
    "api_secret" => "t4kXVm784U1zaYOq8Cbyycoq75g"
));
$MEDIA_FOLDER = 'wp-media/';

$table_tags = $wpdb->prefix."tags";
$table_pictures = $wpdb->prefix."pictures";
$table_picturetag = $wpdb->prefix."picturetag";

function refresh_cloudinary_urls() {
    global $api, $MEDIA_FOLDER;
    global $wpdb;
    $result = $api->resources(
        array("type" => "upload", "prefix" => $MEDIA_FOLDER));
    $urls = array();
    foreach($result['resources'] as $arr){
        array_push($urls, $arr['url']);
    }
    $table_name = $wpdb->prefix."pictures";
    foreach($urls as $url){
        $tmp = end(explode('/', $url));
        $sql = "INSERT INTO $table_name (p_url, p_name) VALUES (SELECT '$url', '$tmp' WHERE NOT EXISTS (SELECT 1 FROM $table_name WHERE p_name='$tmp'))";
        $wpdb->query($sql);
        echo $wpdb->last_query."<br/>";
        echo $wpdb->last_error."<br/>";
    }
    remove_deleted_images();
    delete_duplicate_p_urls();
    return;
}

function remove_deleted_images(){
    global $wpdb;
    $p_urls = get_all_images();
    foreach ($p_urls as $p_url){
        $headers = get_headers($p_url->p_url);
        if (strpos($headers[0], 'OK') == False){
            delete_image($p_url->p_url);
        }
    }
}

function delete_duplicate_p_urls(){
    global $wpdb, $table_pictures, $table_picturetag;
    $sql = "DELETE FROM $table_picturetag WHERE picture_id NOT IN
            (SELECT MIN(id) FROM $table_pictures GROUP BY p_name)";
    $wpdb->query($sql);
    $sql = "DELETE FROM $table_pictures WHERE id NOT IN
            (SELECT MIN(id) FROM $table_pictures GROUP BY p_name)";
    $wpdb->query($sql);
    return;
}

function delete_image($p_url){
    global $wpdb, $table_pictures, $table_picturetag;
    $sql = "DELETE FROM $table_picturetag pt 
                    INNER JOIN $table_pictures p ON pt.picture_id = p.id WHERE p.url = $p_url";
    $wpdb->query($sql);
    $wpdb->query("DELETE FROM $table_pictures WHERE p_url = $p_url");
}

function get_image_tags($url) {
    global $wpdb;
    $table_tags = $wpdb->prefix."tags";
    $table_pictures = $wpdb->prefix."pictures";
    $table_picturetag = $wpdb->prefix."picturetag";
    $sql = "SELECT tag_name FROM $table_tags t INNER JOIN $table_picturetag pt 
            ON t.id = pt.tag_id INNER JOIN $table_pictures p 
            ON p.id = pt.picture_id WHERE p.p_url = '$url'";
    $tags = $wpdb->get_results($sql);
    if ($tags != null) {
        $string_tags = "";
        foreach ($tags as $tag) {
            if ($string_tags == "") {
                $string_tags = $tag->tag_name;
            } else {
                $string_tags = $string_tags.', '.$tag->tag_name;
            }
        }
    }
    return $string_tags;
}

function get_image_caption($url) {
    global $wpdb; 
    $table = $wpdb->prefix."pictures";
    $sql = "SELECT caption FROM $table WHERE p_url='$url'";
    $caption = $wpdb->get_results($sql);
    return $caption[0]->caption;
}

function get_all_images() {
    global $wpdb;
    $table = $wpdb->prefix."pictures";
    $sql = "SELECT p_url FROM $table";
    $p_urls = $wpdb->get_results( $sql );
    return $p_urls;
}

function get_all_tags() {
    global $wpdb;
    global $table_tags;
    $sql = "SELECT tag_name FROM $table_tags;";
    $tags = $wpdb->get_results( $sql );
    return $tags;
}

function insert_image_tag($url, $json_tags, $caption){
    global $wpdb;
    global $table_pictures; 
    global $table_tags;
    global $table_picturetag;
    
    $sql_picture_id = "SELECT id FROM $table_pictures WHERE p_url = '$url';";
    $p_id = $wpdb->get_results($sql_picture_id); 
    $p_id = $p_id[0]->id;
    
    $tags = explode(", ", $json_tags);
    foreach ($tags as $tag){
        $sql_tag_id = "SELECT id FROM $table_tags WHERE tag_name = '$tag'";
        $tag_id = $wpdb->get_results($sql_tag_id);
        if ($tag_id == null){
            $wpdb->query("INSERT INTO $table_tags (tag_name) VALUES ('$tag');");
            $tag_id = $wpdb->insert_id;
        } else {
            $tag_id = $tag_id[0]->id;
        }
        $wpdb->query("INSERT INTO $table_picturetag (picture_id, tag_id) SELECT '$p_id', '$tag_id' WHERE NOT EXISTS (SELECT 1 FROM $table_picturetag as pt WHERE pt.picture_id = '$p_id' and pt.tag_id = '$tag_id' );");
    }
    global $table_pictures;
    $sql_caption = "UPDATE $table_pictures SET caption = '$caption' WHERE p_url = '$url'";
    $wpdb->query($sql_caption);
    wp_redirect(admin_url('admin.php?page=collage-maker'));
}

function search_tags($tags){
    global $wpdb;
    global $table_pictures;
    global $table_tags;
    global $table_picturetag;
    
    if ($tags == 'all' Or $tags == ''){
        $sql = "SELECT p_url FROM $table_pictures;";
    } else {
        $sql = "SELECT p_url FROM $table_pictures INNER JOIN
                $table_picturetag ON $table_pictures.id = $table_picturetag.picture_id
                INNER JOIN $table_tags ON $table_tags.id = $table_picturetag.tag_id
                WHERE $table_tags.tag_name IN (".implode(',', $tags). ");";
    }
    $p_urls = $wpdb->get_results($sql);
    return $p_urls;
}

function make_collage($p_urls){
    echo '<div class="collage-wrapper">';
    foreach($p_urls as $p_url){
        echo '<div>';
        echo '<img src="'.$p_url->p_url.'"/>';
        echo '</div>';
    }
    echo '</div>';
}

function upload_image() {
    global $uploader, $MEDIA_FOLDER;
    global $POST;
    $zip = new ZipArchive();
    if(getimagesize($_FILES["uploadFile"]["tmp_name"])){
        \Cloudinary\Uploader::upload($_FILES["uploadFile"]["tmp_name"], array("folder" => $MEDIA_FOLDER));
    } elseif (is_resource($zip->open($_FILES["uploadFile"]["tmp_name"]))){
        for ($u = 0; $i < $zip->numFiles; $i++){
            $fp = $zip->GetStream($zip->getNameIndex($i));
            if ($fp){
                $contents=fread($fp, 8192);
                if(getimagesize($contents)){
                    $uploader::upload($contents, array("folder" => $MEDIA_FOLDER));
                }
            }
        }
    }
    wp_redirect(admin_url('admin.php?page=collage-maker'));
}


