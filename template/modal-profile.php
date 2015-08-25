<div class="modal fade modal-submit-questions" id="edit_profile" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel"><?php _e("Edit Profile", ET_DOMAIN) ?></h4>
      </div>
      <div class="modal-body">
        <div class="author-edit" id="user_avatar_container">
            <span class="author-avatar image" id="user_avatar_thumbnail">
                <?php
                    global $current_user;
                    if(is_user_logged_in()){
                        $user = QA_Member::convert($current_user);
                        echo et_get_avatar($current_user->ID, 80);
                        $user_email    = $user->user_email;
                        $user_location = $user->user_location;
                        $display_name  = $user->display_name;
                        $show_email    = $user->show_email;
                        $google        = $user->user_gplus;
                        $description   = $user->description;
                        $twitter       = $user->user_twitter;
                        $facebook      = $user->user_facebook;
                    } else {
                        $facebook = $twitter = $description = $google = $user_email = $user_location = $display_name = $show_email = '';
                    }
                ?>
            </span>
            <div class="edit-info-avatar">
                <a href="javascript:void(0);" class="upload-avatar-btn" id="user_avatar_browse_button">
                    <?php _e("Upload New Avatar", ET_DOMAIN) ?>
                </a>
                <a href="javascript:void(0);" class="link_change_password"><?php _e("Change Password", ET_DOMAIN) ?></a>
                <a href="javascript:void(0);" class="link_change_profile"><?php _e("Change Profile", ET_DOMAIN) ?></a>
            </div>
            <span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'user_avatar_et_uploader' ); ?>"></span>
        </div>

        <form id="submit_edit_profile" class="form_modal_style edit_profile_form">
            <label><?php _e("Full name", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" maxlength="40" id="display_name" name="display_name" value="<?php echo $display_name; ?>">

            <label><?php _e("Location", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" maxlength="40" id="user_location" name="user_location" value="<?php echo $user_location; ?>">

            <label><?php _e("Facebook", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" maxlength="80" id="user_facebook" name="user_facebook" value="<?php echo $facebook; ?>">

            <label><?php _e("Twitter", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" maxlength="80" id="user_twitter" name="user_twitter" value="<?php echo $twitter; ?>">

            <label><?php _e("Google+", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" maxlength="80" id="user_gplus" name="user_gplus" value="<?php echo $google; ?>">

            <label><?php _e("Email", ET_DOMAIN) ?></label>
            <input type="text" class="submit-input" id="user_email" name="user_email" value="<?php echo $user_email; ?>">
            <input type="checkbox" name="show_email" <?php checked( $show_email, "on"); ?> id="show_email" /> <label for="show_email" class="checkbox-email"><?php _e("Make this email public.", ET_DOMAIN) ?></label>

            <div class="clearfix"></div>

            <label><?php _e("Description", ET_DOMAIN) ?></label>
            <textarea  maxlength="350" class="submit-textarea" id="description" name="description"><?php echo $description; ?></textarea>

            <div class="clearfix"></div>

            <input type="submit" name="submit" value="<?php _e("Update Profile", ET_DOMAIN) ?>" class="btn-submit update_profile">
        </form>

        <form id="submit_edit_password" class="form_modal_style edit_password_form">
            <label><?php _e("Old Password", ET_DOMAIN) ?></label>
            <input type="password" class="submit-input" id="old_password" name="old_password">
            <label><?php _e("New Password", ET_DOMAIN) ?></label>
            <input type="password" class="submit-input" id="new_password1" name="new_password">
            <label><?php _e("Repeat New Password", ET_DOMAIN) ?></label>
            <input type="password" class="submit-input" id="re_password" name="re_password">
            <input type="submit" name="submit" value="<?php _e("Change Password", ET_DOMAIN) ?>" class="btn-submit update_profile">
        </form>
      </div>
    </div>
  </div>
</div>