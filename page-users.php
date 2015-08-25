<?php
/**
 * Template Name: Users List Template
 * version 1.0
 * @author: enginethemes
 **/
global $wp_rewrite;

add_action( 'pre_user_query', 'wps_pre_user_query' );
/*
* Modify the WP_User_Query appropriately
*
* Checks for the proper query to modify and changes the default user_login for $wpdb->usermeta.meta_value
*
* @param WP_User_Query Object $query User Query object before query is executed
*/
function wps_pre_user_query( &$query ) {
    global $wpdb;
    if ( isset( $query->query_vars['query_id'] ) && 'wps_last_name' == $query->query_vars['query_id'] )
    $query->query_orderby = str_replace( 'user_login', "$wpdb->usermeta.meta_value", $query->query_orderby );
}

get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("Users", ET_DOMAIN) ?></span>
            </div>
            <div class="col-md-6 col-xs-6 select-categories input-find-tags">
                <form class="form-input-search" autocomplete="off" method="GET" action="<?php echo et_get_page_link('users') ?>">
                    <input autocomplete="off" type="text" name="ukey" id="ukey" value="<?php echo isset($_GET['ukey']) ? $_GET['ukey'] : ''; ?>" class="search-users" placeholder="<?php _e('Find Users',ET_DOMAIN) ?>" />
                    <i class="fa fa-chevron-circle-right"></i>
                </form>
            </div>
        </div><!-- END SELECT-CATEGORY -->
        <div class="row question-filter">
            <div class="col-md-6 col-xs-6 sort-questions">
                <ul>
                    <li>
                        <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo et_get_page_link("users"); ?>"><?php _e("Name", ET_DOMAIN) ?></a>
                    </li>
                    <li>
                        <a class="<?php echo isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort'=>'points')); ?>"><?php _e("Points", ET_DOMAIN) ?></a>
                    </li>
                </ul>
            </div>
        </div><!-- END QUESTIONS-FILTER -->
        <div class="main-user-list">
            <ul id="main_users_list" class="row">
                <?php
                    $number     = apply_filters( 'qa_users_list_display', 10 );
                    $paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    $offset     = ($paged - 1) * $number;

                    $args = array(
                            'orderby' => 'display_name',
                            'offset'  => $offset,
                            'number'  => $number
                        );

                    if(isset($_GET['sort']) && $_GET['sort'] == "points"){
                        $args['meta_key'] = 'qa_point';
                        $args['orderby']  = 'meta_value_num';
                        $args['order']    = 'DESC';
                    }

                    if ( isset($_GET['ukey']) && $_GET['ukey'] != "" ) {
                        $search_string = esc_attr( trim( $_GET['ukey'] ) );
                        $args['search']            = "*{$search_string}*";
                        $args['search_columns']    = array( 'user_login', 'user_email', 'user_nicename', 'display_name' );
                    }

                    $query       = new QA_User_Query($args);
                    // echo "<pre>";
                    // print_r($query) ;
                    // echo "</pre>";
                    //$query->query_orderby = "ORDER BY CAST(wp36_usermeta.meta_value AS SIGNED) DESC";
                    $total_users = $query->total_users;
                    $total_query = count($query->results);
                    $total_pages = ceil($total_users / $number);
                    // echo '<pre>';
                    // print_r($query);
                    // echo '</pre>';
                    if ( ! empty( $query->results  ) ) {
                    foreach ($query->results as $user) {
                        $user  = QA_Member::convert($user);
                ?>
                <li class="user-item col-md-4 col-xs-6">
                    <span class="user-avatar">
                        <?php echo et_get_avatar( $user->ID, 30 ); ?>
                    </span>
                    <div class="left-info">
                        <a href="<?php echo get_author_posts_url( $user->ID );?>"><span class="display_name"><?php echo $user->display_name; ?></span></a><br>
                        <span class="location">
                            <i class="fa fa-map-marker"></i> <?php echo $user->user_location ? $user->user_location : __('Earth', ET_DOMAIN); ?>
                        </span>
                        <div class="question-cat">
                            <?php  qa_user_badge( $user->ID ); ?>
                            <span class="points"><?php printf(__("%d Points", ET_DOMAIN), (int)qa_get_user_point($user->ID) ) ?> </span>
                        </div>
                    </div>
                </li>
                <?php
                        }
                    } else {
                        _e("No users found.", ET_DOMAIN);
                    }
                ?>
            </ul>
        </div><!-- END MAIN-QUESTIONS-LIST -->
        <div class="clearfix grey-line"></div>
        <div class="row paginations home">
            <div class="col-md-12">
                <?php
                    if ($total_users > $total_query) {
                        echo paginate_links( array(
                            'base'      => str_replace('99999', '%#%', esc_url(get_pagenum_link( 99999 ))),
                            'format'    => $wp_rewrite->using_permalinks() ? 'page/%#%' : '?paged=%#%',
                            'current'   => max(1, $paged),
                            'total'     => $total_pages,
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