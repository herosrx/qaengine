<?php 
    global $qa_tag_pages;
    // init number of tags perpage
    $number     = apply_filters( 'qa_popular_tags_per_page' , ( 4*get_option( 'posts_per_page', 10 )) ) ;
    $paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;  
    // offset query
    $offset     = ($paged - 1) * $number;

    $args   = array(    'hide_empty' => false,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'number'     => $number,
                        'offset'     => $offset
            );
    $total_args =   array ('hide_empty' => 0 );
    if ( isset($_GET['tkey']) && $_GET['tkey'] != "" ) {
       $total_args['search']  = $args['search']    = $_GET['tkey'];
    }

    $tags   =   get_terms( 'qa_tag', $total_args );
    // get tags by query 
    $query      = get_terms( 'qa_tag', $args ); 

    if( !empty($query) ) {
        foreach ($query as $key => $tag) {
        ?>
            <div class="tag-item">
                <a href="<?php echo get_term_link( $tag, 'qa_tag' ); ?>" class="q-tag"><?php echo $tag->name ?></a> x <?php echo $tag->count ?>
                <p><?php echo qa_count_post_in_tags( $tag->slug ) ?></p>
            </div>
        <?php 
        }
    }

    $qa_tag_pages  =   ceil( count($tags)/$number );
?>
    <div class="clearfix grey-line"></div>