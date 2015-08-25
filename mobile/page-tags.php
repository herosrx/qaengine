<?php
/**
 * Template: TAGS LISTING
 * version 1.0
 * @author: ThaiNT
 **/
et_get_mobile_header();

global $qa_tag_pages;
$paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;      
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
	<!-- TAGS BAR -->
    <section class="tag-bar bg-white">
    	<div class="container">
            <div class="row">
            	<div class="col-md-4 col-xs-4">
                	<h1 class="title-page"><?php _e('Tags', ET_DOMAIN) ?></h1>
                </div>
                <div class="col-md-8 col-xs-8">
                	<form class="find-tag-form" action="<?php echo et_get_page_link('tags') ?>">
                    	<i class="fa fa-chevron-circle-right"></i>
                    	<input type="text" name="tkey" id="tkey" value="<?php echo isset($_GET['tkey']) ? $_GET['tkey'] : ''; ?>" placeholder="<?php _e('Find a tag', ET_DOMAIN) ?>">
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- TAGS BAR / END -->
    
	<!-- MIDDLE BAR -->
    <section class="middle-bar bg-white">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<ul class="menu-middle-bar">
                        <li class="<?php if( !isset($_GET['sort']) ) echo 'active'; ?>">
                            <a href="<?php echo et_get_page_link('tags') ?>">
                                <?php _e("Name", ET_DOMAIN); ?>
                            </a>
                        </li>
                        <li class="<?php if( isset($_GET['sort']) && $_GET['sort'] == 'popular' ) echo 'active'; ?>">
                            <a href="<?php echo add_query_arg(array('sort' => 'popular'), et_get_page_link('tags') ); ?>">
                                <?php _e("Popular", ET_DOMAIN); ?>
                            </a>
                        </li>
                    </ul>
                </div>
    		</div>
        </div> 
    </section>
	<!-- MIDDLE BAR / END -->
    
    <!-- LIST TAG -->
    <section class="list-tags-wrapper">
    	<div class="container">
            <div class="row">
            	<ul class="list-tags">
                	<?php
                        if( (isset($_GET['sort']) && $_GET['sort'] == 'popular') || isset($_GET['tkey']) )
                            get_template_part('mobile/template/tags', 'orderby_popular');
                        else
                            get_template_part('mobile/template/tags', 'orderby_name');
                    ?>
                </ul>
            </div>
        </div>
    </section>
	<!-- LIST TAG / END -->
    <section class="list-pagination-wrapper">
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
    </section>
    <!-- PAGINATIONS QUESTION / END -->    
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>