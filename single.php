<?php
/**
 * Template: Blog Single Page
 * version 1.0
 * @author: enginethemes
 **/
global $wp_query, $post;
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-blog-fix">
        <div class="row">
            <div class="col-md-12">
                <div class="blog-classic-top">
                    <h2><?php _e("Blog", ET_DOMAIN) ?></h2>
                    <form id="search-bar" action="<?php echo home_url() ?>">
                        <i class="fa fa-search"></i>
                        <input type="text" name="s" id="" placeholder="<?php _e("Search at blog",ET_DOMAIN) ?>">
                    </form>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <?php
                    the_post();
                ?>
                <div class="blog-wrapper">
                    <div class="row">
                        <div class="col-md-3 col-xs-3">
                            <div class="author-wrapper">
                                <span class="avatar-author">
                                    <?php echo et_get_avatar($post->post_author, 65); ?>
                                </span>
                                <span class="date">
                                    <?php the_author();?><br>
                                    <?php the_time('M j');  ?> <sup><?php the_time('S');?></sup>, <?php the_time('Y');?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-8 col-xs-8">
                            <div class="blog-content">
                                <span class="tag"><?php the_category( '-' ); ?></span><span class="cmt"><i class="fa fa-comments"></i><?php comments_number(); ?></span>
                                <h2 class="title-blog"><a href="<?php the_permalink(); ?>"><?php the_title() ?></a></h2>
                                <?php
                                    if(is_single()){
                                        if(has_post_thumbnail())
                                            the_post_thumbnail( 'full' );
                                        the_content();
                                        wp_link_pages( array(
                                            'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', ET_DOMAIN ) . '</span>',
                                            'after'       => '</div>',
                                            'link_before' => '<span>',
                                            'link_after'  => '</span>',
                                        ) );
                                    } else {
                                        the_excerpt();
                                ?>
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php _e("READ MORE",ET_DOMAIN) ?><i class="fa fa-arrow-circle-o-right"></i>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <?php comments_template(); ?>
            </div>
        </div>
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>