<?php
include_once( 'collage.php' );
function admin_page_handler() {
    add_menu_page("Collage Maker", "Collage Maker", "manage_options", "collage-maker", "populate_admin_page");
}

function populate_admin_page() {
    $urls = refresh_cloudinary_urls("upload");
    echo "<h1>Collage Admin Page</h1>";
    echo '<form method="POST" action="http://localhost:8000/wp-admin/admin-post.php" enctype="multipart/form-data">';
    echo '<input type="hidden" name="action" value="upload_image">';
    echo '<input type="file" text="Upload image/.zip" name="uploadFile">';
    echo '<input type="Submit" value="Upload Image/Zip"/>';
    echo "</form>";
    
    echo "<select>";
    $tags = get_all_tags();
    if ($tags == null){$tags=array();}
    echo '<option value="all">all</option>';
    foreach ($tags as $tag){
        echo '<option value="$tag">'.$tag->tag_name.'</option>';
    }
    echo "</select>";
    
    echo '<div class="admin-photo-wrapper">';
    $p_urls = get_all_images();
    foreach ($p_urls as $url) {
        $image_tags = get_image_tags($url->p_url);
        $image_caption = get_image_caption($url->p_url);
        if ($image_tags == null){$image_tags="";}
        if ($image_caption == null){$image_caption="";}
        echo '<div class="admin-img">';
        echo '<a href="#" onclick="admin_image_popup(this);">';
        echo '<img src="'.$url->p_url.'"/>';
        echo '<p hidden="hidden">'.$image_tags.'</p>';
        echo '<p hidden="hidden">'.$image_caption.'</p>';
        echo "</a>";
        echo '</div>';
    }
    
    echo '<div id="popup-back" class="popup-background"></div>';
    echo '<div id="popup-front" class="popup-foreground"></div>';
}