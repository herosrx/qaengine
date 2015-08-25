<?php
    global $post, $qa_answer, $qa_question, $current_user;
   
    if($post->post_type == 'question') {
        $object =   $qa_question;
    }else {
        $object =   $qa_answer;
    }

    $vote_up_class  =  'action vote vote-up img-circle ' ;
    $vote_up_class  .= ($object->voted_up) ? 'active' : '';
    $vote_up_class  .= ($object->voted_down) ? 'disabled' : ''; 

    $vote_down_class = 'action vote vote-down img-circle ';
    $vote_down_class .= ($object->voted_down) ? 'active' : '';
    $vote_down_class .= ($object->voted_up) ? 'disabled' : ''; 

    /**
     * check privileges
    */
    $privi  =   qa_get_privileges();
    $vote_up_prover     =   '';
    $vote_down_prover   =   '';

    if( !qa_user_can('vote_up') && isset( $privi->vote_up ) ) {
        $content          = sprintf(__("You must have %d points to vote up.", ET_DOMAIN), $privi->vote_up )   ;
        $vote_up_prover =   'data-container="body" data-toggle="popover" data-content="'. $content .'"';
    }        

    if( !qa_user_can('vote_down') && isset( $privi->vote_down ) ) {
        $content          = sprintf(__("You must have %d points to vote down.", ET_DOMAIN), $privi->vote_down )   ;
        $vote_down_prover = ' data-container="body" data-toggle="popover" data-content="'. $content .'"';
    }

?>
<div class="col-md-2 col-xs-2 vote-block">
	<!-- vote group -->
    <ul>    
        <!-- vote up -->
        <li title="<?php _e("This is useful.", ET_DOMAIN); ?>">
        	<a <?php echo $vote_up_prover ?>  href="javascript:void(0)" data-name="vote_up"  
                class="<?php echo $vote_up_class; ?>" >
        		<i class="fa fa-chevron-up"></i>
        	</a>
        </li>
        <!--// vote up -->

        <!--vote point -->
        <li>
        	<span class="vote-count"><?php echo $object->et_vote_count ?></span>
        </li>
        <!--// vote point -->
        <!-- vote down -->
        <li title="<?php _e("This is not useful", ET_DOMAIN); ?>">
        	<a <?php echo $vote_down_prover ?>  href="javascript:void(0)" data-name="vote_down" 
                class="<?php echo $vote_down_class; ?>">
        		<i class="fa fa-chevron-down"></i>
        	</a>
        </li>	
        <!--// vote down -->
		<?php
            if( is_singular( 'question' ) ){

        		if($post->post_type == 'answer' )  {
                    $active    =  ( $qa_question->et_best_answer == $qa_answer->ID ) ? 'active' : '';

                    if( $current_user->ID == $qa_question->post_author || $active != '' ){

                        $answer_authorname  =   get_the_author_meta('display_name', $qa_answer->post_author  );

                        $data_name = 'data-name="'. ($qa_question->et_best_answer == $qa_answer->ID ? 'un-accept-answer' : 'accept-answer' ). '"';
                    
            		?>
                    <li title="<?php _e('Mark as best answer', ET_DOMAIN );//printf(__("Agree with %s", ET_DOMAIN), $answer_authorname ); ?>" >
                    	<a  href="javascript:void(0)" 
                    	   <?php echo $data_name; ?> class="action accept-answer img-circle <?php echo $active  ?>">
                    		<i class="fa fa-check"></i>
                    	</a>
                    </li>
            		<?php
                    } 
                }
            }
        ?>
    </ul>
    <!--// vote group -->
</div>