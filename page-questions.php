<?php
/**
 * Template Name: Questions List Template
 * version 1.0
 * @author: enginethemes
 **/
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
        <?php qa_template_filter_questions(); ?>
        <div class="main-questions-list">
            <ul id="main_questions_list">
                <?php

                    if(get_query_var( 'page' )){
                        $paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
                    } else {
                        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    }

                    $args  = array(
                            'post_type' => 'question',
                            'paged'     => $paged
                        );

                    if( isset($_GET['numbers']) && $_GET['numbers'])
                        $args['posts_per_page'] = $_GET['numbers'];

                    // if ( isset($_GET['sort']) && $_GET["sort"] == "unanswer" ) {
                    //     add_filter("posts_join"      , array("QA_Front", "_post_unanswer_join") );
                    //     add_filter("posts_orderby"   , array("QA_Front", "_post_unanswer_where") );
                    // }

                    $query = QA_Questions::get_questions($args);

                    if($query->have_posts()){
                        while($query->have_posts()){
                            $query->the_post();
                            get_template_part( 'template/question', 'loop' );
                        }
                    }
                    wp_reset_query();
                ?>
            </ul>
        </div><!-- END MAIN-QUESTIONS-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php
                    qa_template_paginations($query, $paged);
                ?>
            </div>
        </div><!-- END MAIN-PAGINATIONS -->

        <div class="clearfix"></div>

        <?php do_action( 'qa_btm_quetions_listing' ); ?>
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>