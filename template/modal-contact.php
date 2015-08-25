<div class="modal fade reportFormModal" id="contactFormModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					<i class="fa fa-times"></i>
				</button>
				<h4 class="modal-title">
					<?php _e( 'Contact ', ET_DOMAIN ) ?>
				</h4>
			</div>
            <div class="modal-body">
	            <form id="contact_form" class="form_modal_style">
	            	<input type="hidden" id="user_id" value="<?php echo get_query_var( 'author' ); ?>" />
					<label><?php _e( 'Your message:', ET_DOMAIN ) ?></label>
					<textarea id="txt_msg" name="txt_msg" rows="5" placeholder="<?php _e( 'Got something to say? Type your message here.', ET_DOMAIN ) ?>" name= "message"></textarea>
					<input type="submit" data-loading-text="<?php _e("Loading...", ET_DOMAIN); ?>" class="btn" value ="<?php _e( 'Send', ET_DOMAIN ) ?>" />
	            </form>
            </div>
        </div>
    </div>
</div>