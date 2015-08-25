<?php
/**
 * The template for displaying question pages
 *
 * @package QAEngine
 * @since QnA Engine 1.0
 */
global $wp_query;
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        
        <?php do_action( 'qa_top_quetions_listing' ); ?>  

        <div class="clearfix"></div>

        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("All Questions", ET_DOMAIN ); ?></span>
            </div>
            <div class="col-md-6 col-xs-6">
                <?php qa_tax_dropdown() ?>
            </div>            
        </div><!-- END SELECT-CATEGORY -->
        <div class="clearfix"></div>
        <?php qa_template_filter_questions() ?>
        <div class="main-questions-list">
            <ul id="main_questions_list">
                <?php
                    if(have_posts()){
                        while(have_posts()){
                            the_post();
                            get_template_part( 'template/question', 'loop' );
                        }
                    } else {
                        echo '<h2>';
                        _e('No questions has been created yet.', ET_DOMAIN);
                        echo '</h2>';
                    }  
                    wp_reset_query();
                ?>                                                                                             
            </ul>
        </div><!-- END MAIN-QUESTIONS-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php 
                    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    qa_template_paginations($wp_query,$paged);
                ?>                
            </div>
        </div><!-- END MAIN-PAGINATIONS -->

        <div class="clearfix"></div>   

        <?php do_action( 'qa_btm_quetions_listing' ); ?>             
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>