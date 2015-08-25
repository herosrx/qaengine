<?php
/**
 * Template Name: Categories List Template
 * version 1.0
 * @author: enginethemes
 **/
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("Categories", ET_DOMAIN) ?></span>
            </div>
            <div class="col-md-6 col-xs-6 select-categories input-find-tags">
                <form class="form-input-search" autocomplete="off" method="GET" action="<?php echo et_get_page_link('categories') ?>">
                    <input autocomplete="off" type="text" name="ckey" id="ckey" value="<?php echo isset($_GET['ckey']) ? $_GET['ckey'] : ''; ?>" class="search-users" placeholder="<?php _e('Find Categories',ET_DOMAIN) ?>" />
                    <i class="fa fa-chevron-circle-right"></i>
                </form>
            </div>                       
        </div><!-- END SELECT-CATEGORY -->
        <div class="row question-filter">
            <div class="col-md-6 col-xs-6 sort-questions">
                <ul>
                    <li>
                        <a class="<?php if( !isset($_GET['orderby']) ) { echo 'active'; } ?>" href="<?php echo remove_query_arg( 'orderby' ); ?>"><?php _e("Name", ET_DOMAIN); ?></a>
                    </li>
                    <li>
                        <a href="?orderby=popular" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == "popular") { echo 'active'; } ?>"><?php _e("Popular", ET_DOMAIN); ?></a>
                    </li>                    
                </ul>
            </div>
        </div><!-- END QUESTIONS-FILTER -->
        <div class="main-tag-list">
            <div class="tags-list row <?php echo isset($_GET['orderby']) && $_GET['orderby'] == "popular" ? 'cats-wrapper' : '' ; ?>">
            <?php 
            $args = array(
                'hide_empty' => 0 ,
                'orderby' => 'name'
                );
            if ( isset($_GET['ckey']) && $_GET['ckey'] != "" ) {
               $args['search']    = $_GET['ckey'];
            }   

            if(isset($_GET['orderby']) && $_GET['orderby'] == "popular") {
                $args['orderby']    = 'count';
            }

            $cats = get_terms( 'question_category', $args );
            if( !empty($cats) ) {
                if(!isset($_GET['orderby'])) {
                    $first_char = strtoupper(mb_substr($cats[0]->name, 0, 1));                        
            ?>
            		<div class="col-md-12">
                    	<span class="character"><?php echo $first_char ?></span>
                    </div>
                    <div class="clearfix"></div>
                    <?php
                    foreach ($cats as $cat) { 
                        
                        $second_char = strtoupper(mb_substr($cat->name, 0, 1));
                        if($second_char != $first_char){
                    ?>
                    	<div class="col-md-12">
                        	<div class="clearfix"></div>
                            <div class="grey-line"></div>
                        </div>
                        <div class="col-md-12">
                        	<span class="character"><?php echo $second_char ?></span>
                        </div>
                        <div class="clearfix"></div>
                        <?php
                                    }
                                    $first_char = $second_char;

                        ?>
                        <div class="tag-item cat-item col-md-2 col-xs-2">
                            <a href="<?php echo get_term_link( $cat, 'question_category' ); ?>" class="q-tag"><?php echo $cat->name ?></a>
                        </div>
                    <?php
                    }
                } else {

                    foreach ($cats as $cat) { 
                    ?>
                        <div class="tag-item cat-item col-md-2 col-xs-2">
                            <a href="<?php echo get_term_link( $cat, 'question_category' ); ?>" class="q-tag"><?php echo $cat->name ?></a>
                        </div>
                    <?php
                    }
                }
            } 
                ?>
                <div class="col-md-12">
                	<div class="clearfix grey-line <?php echo isset($_GET['orderby']) && $_GET['orderby'] == "popular" ? 'pull-left' : '' ; ?>"></div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div><!-- END MAIN-TAGS-LIST -->    
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>