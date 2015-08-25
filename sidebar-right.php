    <div class="col-md-2 disable-mobile right-sidebar">
        <?php
			if ( is_front_page() && is_home() ) {
			    dynamic_sidebar( 'qa-right-sidebar' );
			} elseif ( is_front_page() ) {
			  	dynamic_sidebar( 'qa-right-sidebar' );
			} elseif ( is_home() || is_singular( 'post' ) ) {
			  	dynamic_sidebar( 'qa-blog-right-sidebar' );
			} else {
			  	dynamic_sidebar( 'qa-right-sidebar' );
			} 
			//this is for single quesiton only
			if(is_singular( 'question' )){
				dynamic_sidebar( 'qa-question-right-sidebar' );
			}
        ?>       
    </div><!-- END RIGHT-SIDEBAR -->