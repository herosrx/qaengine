<?php
/**
 * Template Name: 404 page
 * version 1.0
 * @author: enginethemes
 **/
    get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="page-404-wrapper">
        	<p class="number-404">
                4<span><i class="fa fa-question-circle"></i></span>4
            </p>
            <p class="title-404">
                <?php _e("Ops! Lost your way?",ET_DOMAIN) ?>
            </p>
            <p class="text-404">
                <?php _e("Sorry, but the page you were looking for is not here.",ET_DOMAIN) ?>
            </p>
            <a href="<?php echo home_url(); ?>" class="link-back"><?php _e("Back to home page",ET_DOMAIN) ?> </a>
        </div>
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>