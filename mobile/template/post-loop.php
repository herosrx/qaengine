<?php
    global $post;
?>
<li>
    <div class="col-xs-12">
        <a href="<?php the_permalink(); ?>" class="post-thumbnail">
            <?php the_post_thumbnail( 'full' );?>
        </a>
    </div>
    <div class="col-xs-12">
        <div class="blog-content">
            <!-- Post Info -->
            <div class="blog-info">
                <span class="author">
                    <?php the_author();?>
                </span>
                <span>
                    <?php the_time('M j');  ?> <sup><?php the_time('S');?></sup>, <?php the_time('Y');?>
                </span>
                <br>
                <span class="tag">
                    <?php the_category( '-' ); ?>
                </span>
                <span class="cmt">
                    <i class="fa fa-comments"></i><?php comments_number(); ?>
                </span>
            </div>
            <!-- End / Post Info -->
            <h2 class="title-blog"><a href="<?php the_permalink(); ?>"><?php the_title() ?></a></h2>
            <?php
                if(is_single()){
                    the_content();
                } else {
                    the_excerpt();
            ?>
            <a href="<?php the_permalink(); ?>" class="read-more">
                <?php _e("READ MORE",ET_DOMAIN) ?><i class="fa fa-arrow-circle-o-right"></i>
            </a>
            <?php } ?>
        </div>
    </div>
</li>