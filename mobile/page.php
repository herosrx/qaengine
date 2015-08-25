<?php
/**
 * Template: Default Page
 * version 1.0
 * @author: ThaiNT
 **/
	et_get_mobile_header();
    global $post;
    the_post();
?>
<!-- CONTAINER -->
<div class="wrapper-mobile bg-white">
	<!-- TAGS BAR -->
    <section class="blog-bar">
    	<div class="container">
            <div class="row">
            	<div class="col-xs-12">
                	<div class="blog-content">
                        <!-- <span class="tag"><?php //the_category( '-' ); ?></span><span class="cmt"><i class="fa fa-comments"></i><?php //comments_number(); ?></span> -->
                        <h2 class="title-blog"><a href="<?php the_permalink(); ?>"><?php the_title() ?></a></h2>                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- TAGS BAR / END -->
    
	<!-- MIDDLE BAR -->
    <section class="blog-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="blog-list single-blog">
                    <!-- <div class="col-xs-2">
                        <a href="<?php echo get_author_posts_url( $post->post_author ); ?>" class="profile-avatar">
                            <?php echo et_get_avatar( $post->post_author, 65, array('class' => 'avatar img-responsive','alt' => '') ); ?>
                        </a>
                    </div> -->
                    <div class="col-xs-12" id="page_content">
                        <div class="blog-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-12">
                <?php //comments_template(); ?>                
                </div>
    		</div>
        </div>
    </section>
	<!-- MIDDLE BAR / END -->
    
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>