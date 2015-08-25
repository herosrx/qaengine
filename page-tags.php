<?php
/**
 * Template Name: Tags List Template
 * version 1.0
 * @author: enginethemes
 **/
get_header();
global $qa_tag_pages;
$paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;  

?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e('Tags', ET_DOMAIN) ?></span>
            </div>
            <div class="col-md-6 col-xs-6 select-categories input-find-tags">
                <form class="form-input-search" autocomplete="off" method="GET" action="<?php echo et_get_page_link('tags') ?>">
                    <input autocomplete="off" type="text" name="tkey" id="tkey" value="<?php echo isset($_GET['tkey']) ? $_GET['tkey'] : ''; ?>" class="search-users" placeholder="<?php _e('Find Tags',ET_DOMAIN) ?>" />
                    <i class="fa fa-chevron-circle-right"></i>
                </form>
            </div>            
        </div><!-- END SELECT-CATEGORY -->
        <div class="row question-filter">
            <div class="col-md-6 col-xs-6 sort-questions">
                <!-- order by -->
                <ul>
                    <li>
                        <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo et_get_page_link("tags"); ?>"><?php _e("Name", ET_DOMAIN); ?></a>
                    </li>
                    <li>
                        <a class="<?php echo isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort'=>'popular')); ?>"><?php _e("Popular", ET_DOMAIN); ?></a>
                    </li>
                    <!-- <li>
                        <a href="javascript:void(0)"><?php _e("Latest", ET_DOMAIN); ?></a>
                    </li>  -->                   
                </ul>
                <!--// order by -->
            </div>
        </div><!-- END QUESTIONS-FILTER -->

        <div class="main-tag-list">
            <div class="tags-list <?php echo isset($_GET['tkey']) ? 'is-search' : ''; ?>">
                <?php
                    // get orderby param
                    if( (isset($_GET['sort']) && $_GET['sort'] == "popular") || isset($_GET['tkey']) ){
                       get_template_part( 'template/tags', 'orderby_popular' );
                    } else {
                        get_template_part( 'template/tags', 'orderby_name' );
                    }
                 ?>
            </div>
            <div class="clearfix"></div>
        </div><!-- END MAIN-TAGS-LIST -->
        
        <!-- tags paginate -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php

                    if ( $qa_tag_pages  > 1 ) {  
                        echo paginate_links( array(
                            'base'      => str_replace('99999', '%#%', esc_url(get_pagenum_link( 99999 ))),
                            'format'    => $wp_rewrite->using_permalinks() ? 'page/%#%' : '?paged=%#%',
                            'current'   => max(1, $paged),
                            'total'     => $qa_tag_pages,
                            'mid_size'  => 1,
                            'prev_text' => '<',
                            'next_text' => '>',
                            'type'      => 'list'
                        ) );
                    } 
                ?>  
            </div>
        </div><!-- END MAIN-PAGINATIONS -->     
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>