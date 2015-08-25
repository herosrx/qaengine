<?php 
    global $qa_tag_pages;
    // init number of tags perpage
    $number     = get_option( 'posts_per_page', 10 );
    $paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;  
    // offset query
    $offset     = ($paged - 1) * $number;

    $args   = array(    'hide_empty' => false,
                        'orderby'    => 'name'
            );
    // get tags by query 
    $query      = get_terms( 'qa_tag', $args ); 

    $i = 0;
    $j = 0;

    if( !empty($query) ) {
        $current_char = '';
        foreach ( $query  as $key =>  $tag ) { 
            $second_char = mb_strtoupper (mb_substr( $tag->name, 0, 1, "utf-8"), "utf-8" );
            

            if( $current_char != '' && in_array( $second_char, array('1', '2', '8' , '3', '4' , '5', '6', '7', '9', '0') ) ) {
                $second_char   =   $current_char;
            }

            if( $second_char != $current_char  ) { $i ++; 

                $current_char = $second_char;

                if( $i > $offset && $i <= ($offset + $number) ) { 

                echo '<div class="wrap-tag-list">';
            ?>
                
                <?php if( $j != 0 ) { /*echo '<div class="clearfix grey-line"></div>';*/} ?>
                    <span class="character"><?php if( in_array( $current_char, array('1', '2', '8' , '3', '4' , '5', '6', '7', '9', '0') ) )  { _e("Digit", ET_DOMAIN);} else { echo $second_char; } ?></span>
                    <div class="clearfix"></div>
                    <!--// character -->
                <!--// tag item -->
            <?php
                $j++;
                }
            }
            
                if( $i > $offset && $i <= ($offset + $number) ) {
            ?>
                <div class="tag-item">
                    <a href="<?php echo get_term_link( $tag, 'qa_tag' ); ?>" class="q-tag">
                        <?php echo $tag->name ?>
                    </a> x <?php echo $tag->count ?>
                    <p><?php echo qa_count_post_in_tags( $tag->slug ) ?></p>
                </div>
            <?php

                if( ($key+1) == count($query) ) { echo '</div>'; 
                } else {
                    $next_char  =  mb_strtoupper (mb_substr($query[$key+1]->name, 0, 1, "utf-8"), "utf-8" );
                    if( $next_char != $current_char && !in_array( $next_char, array('1', '2', '8' , '3', '4' , '5', '6', '7', '9', '0') ) ) echo '</div>';
                }
                
            }
             
        }
    }
    $qa_tag_pages  =   ceil( $i/$number );
    ?>