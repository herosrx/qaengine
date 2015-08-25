<?php
/**
 * Template: USERS LISTING
 * version 1.0
 * @author: ThaiNT
 **/
	et_get_mobile_header();
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
	<!-- TAGS BAR -->
    <section class="tag-bar bg-white">
    	<div class="container">
            <div class="row">
            	<div class="col-md-4 col-xs-4">
                	<h1 class="title-page"><?php _e('Users', ET_DOMAIN) ?></h1>
                </div>
                <div class="col-md-8 col-xs-8">
                	<form class="find-tag-form" action="<?php echo et_get_page_link('users') ?>">
                    	<i class="fa fa-chevron-circle-right"></i>
                    	<input type="text" name="ukey" id="ukey" value="<?php echo isset($_GET['ukey']) ? $_GET['ukey'] : ''; ?>" placeholder="<?php _e("Find a user", ET_DOMAIN) ?> ">
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
                            <a href="<?php echo et_get_page_link('users') ?>">
                                <?php _e("Name", ET_DOMAIN); ?>
                            </a>
                        </li>
                        <li class="<?php if( isset($_GET['sort']) && $_GET['sort'] == 'points' ) echo 'active'; ?>">
                            <a href="<?php echo add_query_arg(array('sort' => 'points'), et_get_page_link('users') ); ?>">
                                <?php _e("Point", ET_DOMAIN); ?>
                            </a>
                        </li>
                    </ul>
                </div>
    		</div>
        </div> 
    </section>
	<!-- MIDDLE BAR / END -->
    
    <!-- LIST USER -->
    <section id="user-list">
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
            // echo '<pre>';
            // print_r($query);            
            // echo '</pre>';
            $total_users = $query->total_users;
            $total_query = count($query->results);  
            $total_pages = ceil($total_users / $number); 

            if ( ! empty( $query->results  ) ) {
            foreach ($query->results as $user) {
                $user  = QA_Member::convert($user);                       
        ?>
        <div class="profile-user-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-xs-3 padding-right-0">
                        <a href="<?php echo get_author_posts_url( $user->ID );?>" class="profile-avatar">
                            <?php echo et_get_avatar( $user->ID, 65 ); ?>
                        </a>
                    </div>
                    <div class="col-md-9 col-xs-9">
                        <div class="profile-wrapper">
                            <span class="user-name-profile"><?php echo $user->display_name; ?></span>
                            <span class="address-profile"><i class="fa fa-map-marker"></i> <?php echo $user->user_location ? $user->user_location : __('Earth', ET_DOMAIN); ?></span>
                            <span class="email-profile"><i class="fa fa-envelope"></i> <?php echo $user->show_email == "on" ? $user->user_email : __('Email is hidden.', ET_DOMAIN); ?></span>
                            <div class="list-bag-profile-wrapper user-list">
                                <?php  qa_user_badge( $user->ID ); ?>                        
                                <span class="point-profile"><span><?php echo qa_get_user_point($user->ID)?><i class="fa fa-star"></i></span><?php _e("points", ET_DOMAIN) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <?php }} ?>  
    </section>
    <!-- LIST USER / END -->
    <section class="list-pagination-wrapper">
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
    </section>
    <!-- PAGINATIONS QUESTION / END -->
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>