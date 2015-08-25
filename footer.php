			</div><!-- END ROW -->
		</div><!-- END CONTAINER-FLUID -->
	<div style="display:none;">
		<?php wp_editor( '', 'temp_id', editor_settings() ); ?>
	</div>

    <!-- MODAL LOGIN / REGISTER -->
    <?php qa_login_register_modal() ?>
	<!-- MODAL LOGIN / REGISTER -->

	<!-- MODAL RESET PASSWORD -->
    <?php qa_reset_password_modal() ?>
	<!-- MODAL RESET PASSWORD -->

    <!-- MODAL EDIT PROFILE / CHANGE PASS -->
	<?php
		qa_edit_profile_modal();
	?>
	<!-- MODAL EDIT PROFILE / CHANGE PASS -->

	<!-- MODAL INSERT NEW QUESTION -->
	<?php qa_insert_question_modal() ?>
	<!-- MODAL INSERT NEW QUESTION -->

	<!-- MODAL UPLOAD IMAGE -->
	<?php get_template_part( 'template/modal', 'upload-images' ); ?>
	<!-- MODAL UPLOAD IMAGE -->

	<!-- TAG TEMPLATE -->
	<?php qa_tag_template() ?>
	<!-- TAG TEMPLATE -->
	<!-- MODAL REPORT -->
    <?php qa_report_modal() ?>
	<!-- END MODAL REPORT -->
	<!-- CONTACT REPORT -->
    <?php qa_contact_modal() ?>
	<!-- END CONTACT REPORT -->
	<?php
		if( is_singular( 'question' ) || is_singular( 'answer' ) ){
			qa_answer_template();
			qa_comment_template();
		}
	?>
	<!-- SEARCH PREVIEW TEMPLATE -->
	<?php get_template_part( 'template-js/search', 'preview' ); ?>
	<!-- SEARCH PREVIEW TEMPLATE -->
	<?php wp_footer(); ?>
	</body><!-- END BODY -->
</html>
