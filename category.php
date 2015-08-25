<?php
/**
 * Template Name: Questions List Template
 * version 1.0
 * @author: enginethemes
 **/
global $wp_query;
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-blog-fix">
        <div class="row">
            <div class="col-md-12">
                <div class="blog-classic-top">
                    <h2><?php _e("Blog",ET_DOMAIN) ?></h2>
                    <form id="search-bar" action="<?php echo home_url() ?>">
                        <i class="fa fa-search"></i>
                        <input type="text" name="s" id="" placeholder="<?php _e("Search at blog",ET_DOMAIN) ?>">
                    </form>
                </div>
            </div>      
            <div class="clearfix"></div>
            <div class="col-md-12">
                <ul id="main_posts_list">
                    <?php
                        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

                        if(have_posts()){
                            while(have_posts()){
                                the_post();
                                echo '<li>';
                                get_template_part( 'template/post', 'loop' );
                                echo '</li>';
                            }
                        }  
                        wp_reset_query();
                    ?>                                                                                             
                </ul>
            </div><!-- END MAIN-QUESTIONS-LIST -->            
        </div><!-- END SELECT-CATEGORY -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php 
                    qa_template_paginations($wp_query, $paged);
                ?>                 
            </div>
        </div><!-- END MAIN-PAGINATIONS -->      
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>