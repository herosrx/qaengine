	<?php
		if(is_singular( 'question' )){
			qa_mobile_answer_template();
			qa_mobile_comment_template();
		}
		qa_tag_template();
		echo '<!-- GOOGLE ANALYTICS CODE -->';
        $google = ae_get_option('google_analytics');
        $google = implode("",explode("\\",$google));
        echo stripslashes(trim($google));
		echo '<!-- END GOOGLE ANALYTICS CODE -->';
	?>
    <?php wp_footer(); ?>
	</body>
</html>