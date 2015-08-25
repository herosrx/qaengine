<?php 
    global $qa_category_pages;
    // init number of tags perpage
    //$number     = apply_filters( 'qa_popular_tags_per_page' , ( 4*get_option( 'posts_per_page', 10 )) ) ;
    $number     = get_option( 'posts_per_page', 10 );
    $paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;  
    // offset query
    $offset     = ($paged - 1) * $number;

    $args   = array(    'hide_empty' => false,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'number'     => $number,
                        'offset'     => $offset
            );
    $tags   =   get_terms( 'question_category', 'hide_empty=0' );
    // get tags by query 
    $query      = get_terms( 'question_category', $args ); 

    if( !empty($query) ) {
        ?>
        <div class="col-md-12 col-xs-12">
            <div class="categories-wrapper">
                <ul class="categories mobile-categories-list popular-list">        
        <?php
        foreach ($query as $key => $tag) {
        ?>
                    <li class="popular">
                        <span class="tag">
                            <a href="<?php echo get_term_link( $tag, 'question_category' ); ?>"><?php echo $tag->name ?></a>
                        </span>
                    </li>
        <?php 
        }
        ?>
                </ul>
            </div>
        </div>
        <?php
    }

    $qa_category_pages  =   ceil( count($tags)/$number );
?>