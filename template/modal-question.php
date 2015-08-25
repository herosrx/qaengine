<?php
$privi  =   qa_get_privileges();
?>
<!-- MODAL SUBMIT QUESTIONS -->
<div class="modal fade modal-submit-questions" id="modal_submit_questions" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					<i class="fa fa-times"></i>
				</button>
				<h4 class="modal-title" id="myModalLabel"><?php _e('Ask a Question',ET_DOMAIN) ?></h4>
			</div>
			<div class="modal-body">
				<form id="submit_question">
					<input type="hidden" id="qa_nonce" name="qa_nonce" value="<?php echo wp_create_nonce( 'insert_question' ); ?>">
					<?php do_action( 'before_insert_question_form' ); ?>
					<input type="text" class="submit-input" id="question_title" name="post_title" placeholder="<?php _e('Your Question',ET_DOMAIN) ?>" />
					<?php qa_select_categories() ?>
					<div class="wp-editor-container">
						<textarea name="post_content" id="insert_question"></textarea>
					</div>

					<div id="question-tags-container">
						<input data-provide="typeahead" type="text" class="submit-input tags-input" id="question_tags" name="question_tags" placeholder="<?php _e('Tag(max 5 tags)',ET_DOMAIN) ?>" />
						<span class="tip-add-tag"><?php _e("Press enter to add new tag", ET_DOMAIN) ?></span>
						<ul class="tags-list" id="tag_list"></ul>
					</div>

					<input id="add_tag_text" type="hidden" value="<?php printf(__("You must have %d points to add tag. Current, you have to select existed tags.", ET_DOMAIN), $privi->create_tag  ); ?>" />

					<?php do_action( 'after_insert_question_form' ); ?>
					<button id="btn_submit_question" class="btn-submit-question"><?php _e('SUBMIT QUESTION',ET_DOMAIN) ?></button>
					<p class="term-texts">
						<?php qa_tos("question"); ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- MODAL SUBMIT QUESTIONS -->		
