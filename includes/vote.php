<?php

/**
 * catch action insert vote comment to add point to question/answer owner
 * @package qaengine
 */

add_action('wp_insert_comment', 'qa_comment_vote', 10, 2);
function qa_comment_vote($id, $comment) {
    global $user_ID;

    /**
     * get site qa badge point system
     */
    $point = qa_get_badge_point();

    /**
     * get comment post
     */
    $post = get_post($comment->comment_post_ID);

    /**
     * user can not vote for him self
     */
    if ($user_ID == $post->post_author) return false;

    // $change_log = QA_Log::get_instance();

    switch ($comment->comment_type) {
        case 'vote_up':

            /*
             * return false if user can not vote up
            */
            if (!qa_user_can('vote_up')) return false;

            if ( $post->post_type == 'answer') {
                $vote_up_point = $point->a_vote_up;
            } else {
                $vote_up_point = $point->q_vote_up;
            }

            /**
             * save point to comment, keep it to restore point to user if  the vote be undo
             */
            update_comment_meta($comment->comment_ID, 'qa_point', $vote_up_point);

            /**
             * updte user point
             */
            qa_update_user_point($post->post_author, $vote_up_point);

            /**
             * do action qa point vote up
             * @param $post the post be voted
             * @param $vote_up_point
             */
            do_action('qa_point_vote_up', $post, $vote_up_point );

            break;

        case 'vote_down':

            // return false if user can not vote down
            if (!qa_user_can('vote_down')) return false;

            if ($post->post_type == 'answer') {
                $vote_down_point = $point->a_vote_down;
            } else {
                $vote_down_point = $point->q_vote_down;
            }

            /**
             * save point to comment, keep it to restore point to user if  the vote be undo
             */
            update_comment_meta($comment->comment_ID, 'qa_point', $vote_down_point);

            /**
             * updte user point
             */
            qa_update_user_point($post->post_author, $vote_down_point);

            /**
             * update point to current user when he/she vote down a question/answer
             */
            qa_update_user_point($user_ID, $point->vote_down);

            /**
             * do action qa point vote down
             * @param $post the post be voted
             * @param $vote_down_point
             */
            do_action('qa_point_vote_down', $post, $vote_down_point );

            break;
    }
}

/**
 * delete a vote then should return point to user
 * @package qaengine
 * @author Dakachi
 * @package qaengine
 */
add_action('delete_comment', 'qa_comment_unvote');
function qa_comment_unvote($id) {
    global $user_ID;

    if (!$comment = get_comment($id)) return false;

    $post = get_post($comment->comment_post_ID);

    if($post->post_author == $user_ID) return ;

    /**
     * get comment qa_point
     */
    $point = get_comment_meta($id, 'qa_point', true);

    /**
     * update user point
     */
    qa_update_user_point($post->post_author, -(int)$point);
    /**
     * do action qa point unvote
     * @param $post the post be unvoted
     * @param -(int)$point
     */
    do_action('qa_point_unvote', $post, -(int)$point );
}

/**
 * catch event when user post a question or answer a question
 *
 */
add_action('wp_insert_post', 'qa_point_insert_post', 10, 3);
function qa_point_insert_post($post_id, $post, $update) {
	// return if is update post
	if($update)  return ;
    if($post->post_status != "publish") return;
    /**
     * update point for user if post new post
    */
	global $user_ID;

    /**
     * get site qa badge point system
     */
    $point = qa_get_badge_point();

	if($post->post_type == 'question') {
		if( !empty( $point->create_question ) ) {
			/**
             * update user point
             */
            qa_update_user_point( $user_ID, $point->create_question );
            /**
             * do action qa point insert question
             * @param $post the post be unvoted
             * @param -(int)$point
             */
            do_action('qa_point_insert_post', $post, $point->create_question );
		}

	}

	if($post->post_type == 'answer') {
        if( !empty( $point->post_answer ) ) {
			/**
             * update user point
             */
            qa_update_user_point( $user_ID, $point->post_answer );
            /**
             * do action qa point insert answer
             * @param $post the post be unvoted
             * @param $point
             */
            do_action('qa_point_insert_post', $post, $point->post_answer );
		}
	}



    return ;
}
/**
 * catch event when user delete question
 *
 */
add_action('wp_trash_post', 'qa_point_trash_post');
function qa_point_trash_post($post_id) {
    global $post, $user_ID;
    /**
     * get site qa badge point system
     */
    $point = qa_get_badge_point();
    $post  = get_post($post_id);

    if($post->post_type == 'question') {
        if( !empty( $point->create_question ) ) {
            /**
             * update user point
             */
            qa_update_user_point( $post->post_author, -(int)$point->create_question );
            /**
             * do action qa point insert question
             * @param $post the post be unvoted
             * @param -(int)$point
             */
            do_action('qa_point_trash_post', $post, -(int)$point->create_question );
        }

    }

    if($post->post_type == 'answer') {
        if( !empty( $point->post_answer ) ) {
            /**
             * update user point
             */
            $best_point = get_post_meta($post->ID, 'et_best_answer_point', true);
            qa_update_user_point( $post->post_author, -( (int)$point->post_answer + (int)$best_point ) );
            /**
             * do action qa point insert answer
             * @param $post the post be unvoted
             * @param $point
             */
            do_action('qa_point_trash_post', $post, -( (int)$point->post_answer + (int)$best_point ) );
        }
    }

    return $post_id;
}
/**
 * catch action when user sign up, init point for user it should be one.
 * @package qaengine
 * @author Dakachi
 */
add_action('et_insert_user', 'qa_init_user_point');
function qa_init_user_point($user_id) {
    qa_update_user_point($user_id, 1);
}

/**
 * catch action when an answer is mark best answer
 * add point to answer owner
 * @package qaengine
 * @author Dakachi
 */
add_action('qa_mark_answer', 'qa_mark_answer_point', 10, 2);
function qa_mark_answer_point($question_id, $answer_id) {
    $answer = get_post($answer_id);
    if ($answer) {

        // answer is valid
        global $user_ID;
        if ($user_ID != $answer->post_author) {

            /**
             * get site qa badge point system
             */
            $point = qa_get_badge_point();

            /**
             * update use point by answer accepted point
             */
            qa_update_user_point($answer->post_author, $point->a_accepted);

            QA_Answers::update_field($answer_id, 'et_best_answer_point', $point->a_accepted);
            /**
             * do action qa point answer mark answered
             * @param $answer the answer be mark
             * @param $point
             */
            do_action('qa_point_answer_marked', $answer, $point->a_accepted );
        }
    }
}

/**
 * catch action when an answer is change from best answer to normal answer
 * minus point to answer owner
 * @package qaengine
 * @author Dakachi
 */
add_action('qa_remove_answer', 'qa_remove_answer_point');
function qa_remove_answer_point($answer_id) {

    // get the point added to answer owner
    $point = get_post_meta($answer_id, 'et_best_answer_point', true);
    $answer = get_post($answer_id);

    if (!$answer) return;

    /**
     * update use point by answer accepted point
     */
    qa_update_user_point($answer->post_author, (int)(-$point));

    /**
     * remove no need data
     */
    delete_post_meta($answer_id, 'et_best_answer_point');
    /**
     * do action qa point answer unmark answered
     * @param $post the post be unmark
     * @param $point
     */
    do_action('qa_point_answer_unmarked', $answer, (int)(-$point) );
}

/**
 * qa update user point
 * @package qaengine
 * @author Dakachi
 * @package qaengine
 */
function qa_update_user_point($user_id, $point) {

    /**
     * get current user qa_point
     */
    $current_point = get_user_meta($user_id, 'qa_point', true);
    $new_point = $current_point + (int)($point);

    /**
     * reset to 1 if point is lose to 0
     */
    if ($new_point <= 0) $new_point = 1;

    /**
     * update user meta qa_point
     */
    update_user_meta($user_id, 'qa_point', $new_point);
}
