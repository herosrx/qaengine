    <div class="col-md-2 disable-mobile left-sidebar">

        <div class="widget widget-btn">
        	<button type="button" data-toggle="modal" class="action ask-question">
                <i class="fa fa-plus"></i> <?php _e("ASK A QUESTION",ET_DOMAIN) ?>
            </button>
        </div><!-- END BUTTON MODAL QUESTION -->
        
        <div class="widget widget-menus">
            <?php
                if(has_nav_menu('et_left')){
                    wp_nav_menu( array(
                        'theme_location' => 'et_left',
                        'depth'          => '1',
                        'walker'         => new QA_Custom_Walker_Nav_Menu()
                    ) );
                }
            ?>
        </div><!-- END LEFT MENU -->

        <?php
            if ( is_front_page() && is_home() ) {
                dynamic_sidebar( 'qa-left-sidebar' );
            } elseif ( is_front_page() ) {
                dynamic_sidebar( 'qa-left-sidebar' );
            } elseif ( is_home() || is_singular( 'post' ) ) {
                dynamic_sidebar( 'qa-blog-left-sidebar' );
            } else {
                dynamic_sidebar( 'qa-left-sidebar' );
            }        
        ?>

        <div class="copyright">
        	&copy;<?php echo date('Y') ?> <?php echo ae_get_option( 'copyright' ); ?> <br>
			<a href="<?php echo et_get_page_link("term"); ?>"><?php _e("Term & Privacy", ET_DOMAIN) ?></a>
        </div>
    </div><!-- END LEFT-SIDEBAR -->