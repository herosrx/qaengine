<div class="modal fade modal-submit-questions" id="upload_images" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
				<h4 class="modal-title modal-title-sign-in" id="myModalLabel"><?php _e("Insert Images", ET_DOMAIN) ?></h4>
			</div>
			<div class="modal-body">
				<div class="upload-location" id="images_upload_container">
					<span class="title"><?php _e( 'Upload an image', ET_DOMAIN ) ?></span>
					<div class="input-file">
						<?php
							$disabled  = !ae_get_option('ae_upload_images') ? 'disabled="disabled" style="opacity:0.7;"' : '' ;

							if(ae_get_option('ae_upload_images')){
								$file_text = is_user_logged_in() ? __("No file chosen.",ET_DOMAIN) : __("Please log in to use this function.",ET_DOMAIN);								
							} else {
								$file_text = __('Admin has disabled this function.', ET_DOMAIN );		
							}
						?>                     
						<input type="button" <?php echo $disabled ?> value="<?php _e("Browse",ET_DOMAIN);?>" class="bg-button-file button" id="images_upload_browse_button">                        
						<span class="filename"><?php echo $file_text; ?></span>
						<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'et_upload_images' ); ?>"></span> 
					</div>
				</div> 
                <div class="upload-url">
                    <span class="title"><?php _e( 'Add an image via URL', ET_DOMAIN ) ?></span>
                    <div class="input-url">
                      	<input type="text" placeholder="https://www.domain.com/images.jpg" id="external_link" class="form-control">
	                    <div class="button-event">
	                  		<button type="button" id="insert" class="btn"><?php _e( 'Insert', ET_DOMAIN ) ?></button>
	                    	<a href="javascript:void(0)" class="btn-cancel collapse" data-dismiss="modal">
	                    		<?php _e( 'Cancel', ET_DOMAIN ) ?>
	                    	</a>
	                    </div>
                    </div>                  
                </div>					 
			</div>
		</div>
	</div>
</div>