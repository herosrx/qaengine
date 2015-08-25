<?php
/**
 * Template: ARCHIVE QUESTIONS LISTING
 * version 1.0
 * @author: ThaiNT
 **/
	et_get_mobile_header();
	global $wp_query;
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
	<!-- TOP BAR -->
	<section class="top-bar bg-white">
    	<div class="container">
            <div class="row">
                <div class="col-md-4 col-xs-4">
                    <span class="top-bar-title"><?php _e('Category',ET_DOMAIN);?></span>
                </div>
                <div class="col-md-8 col-xs-8">
                    <div class="select-categories-wrapper">
                        <div class="select-categories">
                            <select class="select-grey-bg" id="move_to_category">
                                <option value=""><?php _e("Select Categories",ET_DOMAIN) ?></option>
                                <?php qa_option_categories_redirect() ?>
                            </select>
                        </div>
                    </div>
                    <a href="javascript:void(0)" class="icon-search-top-bar"><i class="fa fa-search"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- TOP BAR / END -->
    
    <!-- MIDDLE BAR -->
    <section class="middle-bar bg-white">
        <?php qa_mobile_filter_search_questions() ?>  
    </section>
    <!-- MIDDLE BAR / END -->
    
    <!-- LIST QUESTION -->
    <section class="list-question-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<ul class="list-question">
                        <?php
                            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                            if(have_posts()){
                                while(have_posts()){
                                    the_post();
                                    get_template_part( 'mobile/template/question', 'loop' );
                                }
                            }
                            wp_reset_query();
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- LIST QUESTION / END -->
    <section class="list-pagination-wrapper">
        <?php 
            qa_template_paginations($wp_query, $paged);
        ?>
    </section>
    <!-- PAGINATIONS QUESTION / END -->
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>