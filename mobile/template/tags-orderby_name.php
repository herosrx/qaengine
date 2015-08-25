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

            if( $second_char != $current_char ) { $i ++; 
                
                $current_char = $second_char;

                if( $i > $offset && $i <= ($offset + $number) ) { 

            ?>
            <li>
                <div class="col-md-1 col-xs-1">
                    <span class="big-charater"><?php echo $current_char ?></span>
                </div>
                <div class="col-md-11 col-xs-11">
                    <div class="tags-wrapper">
                        <ul class="tags mobile-tags-list">
            <?php
                }
            }
                if( $i > $offset && $i <= ($offset + $number) ) {
                    $j++;
            ?>
                            <li data-id="<?php echo $j ?>">
                                <span class="tag"><a href="<?php echo get_term_link( $tag, 'qa_tag' ); ?>"><?php echo $tag->name ?></a>x <?php echo $tag->count ?></span>
                                <span class="time-tag"><?php echo qa_count_post_in_tags( $tag->slug ) ?></span>
                            </li>
            <?php

                if( ($key+1) == count($query) ) { 
            ?>
                        </ul>
                        <!-- <a href="javascript:void(0)" class="more-tag-link"><?php printf(__("Touch here to see more %s tags", ET_DOMAIN), $current_char) ?></a> -->
                    </div>
                </div>
            </li>   
            <?php 
                } else {
                    $next_char  =  mb_strtoupper (mb_substr($query[$key+1]->name, 0, 1, "utf-8"), "utf-8" );
                    if( $next_char != $current_char ){
            ?>
                        </ul>
                        <!-- <a href="javascript:void(0)" class="more-tag-link"><?php printf(__("Touch here to see more %s tags", ET_DOMAIN), $current_char) ?></a> -->
                    </div>
                </div>
            </li>
            <?php
                    }
                }
                
            }
             
        }
    }
    $qa_tag_pages  =   ceil( $i/$number );
    ?>