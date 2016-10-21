admin_image_popup = function (a_tag) {
    var p_url = a_tag.getElementsByTagName('img')[0].src;
    var tags = a_tag.getElementsByTagName('p')[0].textContent;
    var caption = a_tag.getElementsByTagName('p')[1].textContent;
    var background = document.getElementById("popup-back");
    var foreground = document.getElementById("popup-front");
    background.style.display='block';
    foreground.style.display='block';
    
    while (foreground.hasChildNodes()) {
        foreground.removeChild(foreground.lastChild);
    }
    
    //Close button
    close = document.createElement('a');
    close.className = "boxclose";
    close.textContent = "X";
    close.onclick = function () {admin_close_popup();};
    // Input form
    input_form = document.createElement("form");
    input_form.action = action="/wp-admin/admin-post.php";
    input_form.method = "POST";
    input_form.enctype = "application/x-www-form-urlencoded"
    
    tag_label = document.createElement("label");
    tag_label.textContent = "Image Tags: ";
    input_tag = document.createElement("input");
    input_tag.type = "text"; input_tag.name="tags";
    input_tag.value = tags;
    tag_label.appendChild(input_tag);
    
    caption_label = document.createElement("label");
    caption_label.textContent = "Image Caption: ";
    input_caption = document.createElement("input");
    input_caption.type = "text"; input_caption.name = "caption";
    input_caption.value = caption;
    caption_label.appendChild(input_caption);
    
    input_button = document.createElement("input");
    input_button.type = "submit"; input_button.value = "Save Changes"
    
    input_picture = document.createElement("input");
    input_picture.type = "text"; input_picture.name = "p_url"; input_picture.hidden = "hidden"; input_picture.value = p_url;
    input_action = document.createElement("input");
    input_action.value = "insert_image_tag";
    input_action.name = "action";
    input_action.type = "hidden";
    //picture element
    picture = document.createElement("img");
    picture.src = p_url; picture.className = "popup";
    foreground.appendChild(close);
    foreground.appendChild(picture);
    input_form.appendChild(tag_label);
    input_form.appendChild(caption_label);
    input_form.appendChild(input_button);
    input_form.appendChild(input_picture);
    input_form.appendChild(input_action);
    foreground.appendChild(input_form);
}

admin_close_popup = function () {
    var background = document.getElementById("popup-back");
    var foreground = document.getElementById("popup-front");
    background.style.display='none';
    foreground.style.display='none';
}
