<?php

class QA_Related_Questions_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to single sidebars to display a list of related questions.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('question_related_widget', __('QA Related Questions', ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if( $new_instance['number'] != $old_instance['number'] ){
			delete_transient( 'related_questions_query' );
		}
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'   => __('RELATED QUESTIONS', ET_DOMAIN) ,
			'number'  => '4',
			'base_on' => 'category',
			) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>">
				<?php _e('Number of questions to display:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('base_on'); ?>">
				<?php _e('Questions base on:', ET_DOMAIN) ?>
			</label>
			<select id="<?php echo $this->get_field_id('base_on'); ?>" name="<?php echo $this->get_field_name('base_on'); ?>">
				<option <?php selected( $instance['base_on'], "category" ); ?> value="category">
					<?php _e('Category', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['base_on'], "tag" ); ?> value="tag">
					<?php _e('Tag', ET_DOMAIN) ?>
				</option>
			</select>
		</p>
	<?php
	}

	function widget( $args, $instance ) {

		global $wpdb, $post;
		if(is_singular( 'question' )){
			if(get_transient( 'related_questions_query' ) === false){

				$arrSlug  = array();
				$taxonomy = $instance['base_on'] == "category" ? "question_category" : "qa_tag";
				$terms    = get_the_terms($post->ID, $taxonomy);

				if(!empty($terms)){
					foreach ($terms as $term) {
						$arrSlug[] = $term->slug;
					}
				}
				$args = array(
						'post_type'    => 'question',
						'showposts'    => $instance['number'],
						'post__not_in' => array($post->ID),
						'order'        => 'DESC'
					);
				if(!empty($arrSlug)){
					$args['tax_query'] = array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => 'slug',
								'terms'    => $arrSlug,
							)
						);
				}
				$query = new WP_Query($args);
				ob_start();
			?>
		    <div class="widget widget-hot-questions">
		        <h3><?php echo esc_attr($instance['title']) ?></h3>
		        <ul>
					<?php
						if($query->have_posts()){
							while ( $query->have_posts() ) {
								$query->the_post();
					?>
		            <li>
		                <a href="<?php echo get_permalink( $post->ID );?>">
		                    <span class="topic-avatar">
		                    	<?php echo et_get_avatar($post->post_author, 30) ?>
		                    </span>
		                    <span class="topic-title"><?php echo $post->post_title ?></span>
		                </a>
		            </li>
		            <?php
		        			}
			        	} else {
			        		echo '<li class="no-related">There is no related questions!</li>';
			        	}
			        	wp_reset_query();
			        ?>
		        </ul>
		    </div><!-- END widget-related-tags -->
			<?php
				$questions = ob_get_clean();
				set_transient( 'related_questions_query', $questions, apply_filters( 'qa_time_expired_transient', 24*60*60 ));
			} else {
				$questions = get_transient( 'related_questions_query' );
			}
			echo $questions;
			//delete_transient( 'related_questions_query' );
		} else {
		?>
		<div class="widget widget-hot-questions">
			<h3><?php echo esc_attr($instance['title']) ?></h3>
			<ul>
				<li>
					<?php _e('This widget should be placed in Single Question Sidebar', ET_DOMAIN) ?>
				</li>
			</ul>
		</div>
		<?php
		}
	}
}//End Related Questions

class QA_Hot_Questions_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to any sidebars to display a list of hot questions.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('question_hot_widget', __('QA Latest Questions / Hot Questions',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if($new_instance['normal_question'] != $old_instance['normal_question'] || $new_instance['number'] != $old_instance['number']){
			delete_transient( 'hot_questions_query' );
			delete_transient( 'latest_questions_query' );
		}
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('HOT QUESTIONS',ET_DOMAIN) , 'number' => '8', 'date' => '', 'normal_question' => 0) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>">
				<?php _e('Number of questions to display:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('normal_question'); ?>">
				<?php _e('Latest questions (sort by date)', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('normal_question'); ?>" name="<?php echo $this->get_field_name('normal_question'); ?>" value="1" type="checkbox" <?php checked( $instance['normal_question'], 1 ); ?> value="<?php echo esc_attr( $instance['normal_question'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('date'); ?>">
				<?php _e('Date range:', ET_DOMAIN) ?>
			</label>
			<select id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>">
				<option <?php selected( $instance['date'], "all" ); ?> value="all">
					<?php _e('All days', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['date'], "last7days" ); ?> value="last7days">
					<?php _e('Last 7 days', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['date'], "last30days" ); ?> value="last30days">
					<?php _e('Last 30 days', ET_DOMAIN) ?>
				</option>
			</select>
		</p>
	<?php
	}

	function widget( $args, $instance ) {

		global $wpdb;
		if(!isset($instance['normal_question'])){

			if(get_transient( 'hot_questions_query' ) === false){
				$hour       = 12;
				$today      = strtotime("$hour:00:00");
				$last7days  = strtotime('-7 day', $today);
				$last30days = strtotime('-30 day', $today);

				if($instance['date'] == "last7days"){
					$custom = "AND post_date >= '".date("Y-m-d H:i:s", $last7days)."' AND post_date <= '".date("Y-m-d H:i:s", $today)."' ";
				} elseif ($instance['date'] == "last30days") {
					$custom = "AND post_date >= '".date("Y-m-d H:i:s", $last30days)."' AND post_date <= '".date("Y-m-d H:i:s", $today)."' ";
				} else {
					$custom = "";
				}

				$query = "SELECT * FROM $wpdb->posts as post
						INNER JOIN $wpdb->postmeta as meta
						ON post.ID = meta.post_id
						AND meta.meta_key  = 'et_answers_count'
						WHERE post_status = 'publish'
						AND post_type = 'question'
					";

				$query .= $custom;
				$query .="	ORDER BY CAST(meta.meta_value AS SIGNED) DESC,post_date DESC
					LIMIT ".$instance['number']."
					";
				$questions = $wpdb->get_results($query);
				set_transient( 'hot_questions_query', $questions, apply_filters( 'qa_time_expired_transient', 24*60*60 ));
			} else {
				$questions = get_transient( 'hot_questions_query' );
			}

		} else {

			if(get_transient( 'latest_questions_query' ) === false){

				$query = "SELECT * FROM $wpdb->posts as post
						WHERE post_status = 'publish'
						AND post_type = 'question'
						ORDER BY post_date DESC
						LIMIT ".$instance['number']."
						";

			$questions = $wpdb->get_results($query);
			set_transient( 'latest_questions_query', $questions, apply_filters( 'qa_time_expired_transient', 24*60*60 ) );

			} else {
				$questions = get_transient( 'latest_questions_query' );
			}
		}
		// delete_transient( 'latest_questions_query' );
		// delete_transient( 'hot_questions_query' );
	?>
    <div class="widget widget-hot-questions">
        <h3><?php echo esc_attr($instance['title']) ?></h3>
        <ul>
			<?php
				foreach ($questions as $question) {
			?>
            <li>
                <a href="<?php echo get_permalink( $question->ID );?>">
                    <span class="topic-avatar">
                    	<?php echo et_get_avatar($question->post_author, 30) ?>
                    </span>
                    <span class="topic-title"><?php echo $question->post_title ?></span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div><!-- END widget-related-tags -->
	<?php
	}
}//End Class Hot Questions

class QA_Statistic_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the statistic of website.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('qa_statistic_widget', __('QA Statistics',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('STATISTICS WIDGET',ET_DOMAIN)) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
	<?php
	}

	function widget( $args, $instance ) {
		$questions = wp_count_posts('question');
		$result    = count_users();
	?>
    <div class="widget widget-statistic">
    	<p class="questions-count">
    		<?php _e("Questions",ET_DOMAIN) ?><br>
    		<span><?php echo  $questions->publish; ?></span>
    	</p>
    	<p class="members-count">
    		<?php _e("Members",ET_DOMAIN) ?><br>
    		<span><?php echo $result['total_users']; ?></span>
    	</p>
    </div><!-- END widget-statistic -->
	<?php
	}
}

class QA_Tags_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the list of tags.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('qa_tags_widget', __('QA Tags',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Tags Widget',ET_DOMAIN) , 'number' => '8') );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of tag to display:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
	<?php
	}

	function widget( $args, $instance ) {
		$tags = get_terms( 'qa_tag', array(
			'hide_empty' => 0 ,
			'orderby' 	 => 'count',
			'order'		 => 'DESC',
			'number'	 => $instance['number']
			));
	?>
    <div class="widget widget-related-tags">
        <h3><?php echo esc_attr($instance['title']) ?></h3>
        <ul>
        	<?php
        		foreach ($tags as $tag) {
        	?>
            <li>
            	<a class="q-tag" href="<?php echo get_term_link( $tag, 'qa_tag' ); ?>"><?php echo $tag->name ?></a> x <?php echo $tag->count ?>
            </li>
            <?php } ?>
        </ul>
        <a href="<?php echo et_get_page_link('tags') ?>"><?php _e("See more tags", ET_DOMAIN) ?></a>
    </div><!-- END widget-related-tags -->
	<?php
	}
}

class QA_Top_Users_Widget extends WP_Widget{

	function __construct() {
		$widget_ops = array(
			'classname'   => 'widget',
			'description' => __( 'Drag this widget to sidebar to display the list of top users.',ET_DOMAIN )
		);
		$control_ops = array(
			'width'  => 250,
			'height' => 100
		);
		parent::__construct('top_users_widget', __('QA Top Users',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if( $new_instance['number'] != $old_instance['number'] || $new_instance['orderby'] != $old_instance['orderby'] || $new_instance['latest_users'] != $old_instance['latest_users'] )
			delete_transient( 'top_users_query' );
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'        => __('TOP USERS',ET_DOMAIN) ,
			'number'       => '8',
			'orderby'      => 'point',
			'latest_users' => 0
		));
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of users to display:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('latest_users'); ?>">
				<?php _e('Latest users (sort by date)', ET_DOMAIN) ?>
			</label>
			<input class="widefat latest-checkbox" id="<?php echo $this->get_field_id('latest_users'); ?>" name="<?php echo $this->get_field_name('latest_users'); ?>" value="1" type="checkbox" <?php checked( $instance['latest_users'], 1 ); ?> value="<?php echo esc_attr( $instance['latest_users'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order By:', ET_DOMAIN) ?> </label>
			<select class="widefat" <?php disabled( $instance['latest_users'], 1); ?> id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
				<option value="point" <?php selected( esc_attr( $instance['orderby'] ), "point" ); ?>>
					<?php _e( 'Points', ET_DOMAIN ); ?>
				</option>
				<option value="question" <?php selected( esc_attr( $instance['orderby'] ), "question" ); ?>>
					<?php _e( 'Questions', ET_DOMAIN ); ?>
				</option>
				<option value="answer" <?php selected( esc_attr( $instance['orderby'] ), "answer" ); ?>>
					<?php _e( 'Answers', ET_DOMAIN ); ?>
				</option>
			</select>
		</p>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(document).on('change', 'input#<?php echo $this->get_field_id('latest_users'); ?>', function () {
					if (!this.checked) {
						$("select#<?php echo $this->get_field_id('orderby'); ?>").prop('disabled', false);
					} else {
						$("select#<?php echo $this->get_field_id('orderby'); ?>").prop('disabled', true);
					}
				});
			});
		</script>
	<?php
	}

	function widget( $args, $instance ) {
		global $wpdb;

		$latest_users = isset($instance['latest_users']) && $instance['latest_users'] == 1 ? 1 : 0;
		$widget_id    = $args['widget_id'];

		//top users
		if( !$latest_users ){
			if(get_transient( 'top_users_query_'.$widget_id ) === false){

				$orderby = $instance['orderby'];

				if($orderby == "point"){
					$str_order = "CAST(points AS SIGNED) DESC, CAST(question_count AS SIGNED) DESC, CAST(answer_count AS SIGNED) DESC";
				} elseif ($orderby == "question") {
					$str_order = "CAST(question_count AS SIGNED) DESC, CAST(points AS SIGNED) DESC, CAST(answer_count AS SIGNED) DESC";
				} else {
					$str_order = "CAST(answer_count AS SIGNED) DESC, CAST(points AS SIGNED) DESC, CAST(question_count AS SIGNED) DESC";
				}

				$query =
					"SELECT  user.ID as uid,
							 display_name,
							 points_count.meta_value as points,
							 answer.meta_value as answer_count,
							 question.meta_value as question_count

					FROM $wpdb->users as user

					INNER JOIN $wpdb->usermeta as answer
						 ON user.ID = answer.user_id
						 AND answer.meta_key  = 'et_answer_count'

					INNER JOIN $wpdb->usermeta as question
						 ON user.ID = question.user_id
						 AND question.meta_key = 'et_question_count'

					INNER JOIN $wpdb->usermeta as points_count
						 ON user.ID = points_count.user_id
						 AND points_count.meta_key = 'qa_point'

					GROUP BY user.ID

					ORDER BY ".$str_order."

					LIMIT ".$instance['number'];

				$users = $wpdb->get_results($query);
				set_transient( 'top_users_query', $users, apply_filters( 'qa_time_expired_transient', 24*60*60 ) );
			} else {
				$users = get_transient( 'top_users_query_'.$widget_id );
			}
		} else {
			if(get_transient( 'latest_users_query_'.$widget_id ) === false){
				$users = get_users( array(
					'orderby' => 'registered',
					'number'  => $instance['number'],
					'order'   => 'DESC'
					) );
			} else {
				$users = get_transient( 'latest_users_query_'.$widget_id );
			}
		}
		// delete_transient( 'top_users_query' );
		// delete_transient( 'latest_users_query' );
	?>
	<div class="widget user-widget">
		<h3 class="widgettitle"><?php echo esc_attr($instance['title']) ?></h3>
	    <div class="hot-user-question">
	    	<ul>
            <?php
            	$i = 1;
            	foreach ($users as $user) {
            		$uid = isset($user->uid) ? $user->uid : $user->ID;
            ?>
	        	<li>
                    <span class="number"><?php echo $i ?></span>
                    <span class="username <?php echo $latest_users ? 'latest' : ''; ?>">
                    	<a href="<?php echo get_author_posts_url($uid); ?>" title="<?php echo $user->display_name ?>">
                    		<?php echo $user->display_name ?>
                    	</a>
                    </span>
                    <?php
                    	if( !$latest_users ){
                    		if( $instance['orderby'] == "question" ){
                    ?>
                    <span class="questions-count" title="<?php printf( __('%d Question(s)'), $user->question_count > 0 ? $user->question_count : 0 ) ?>">
                    	<i class="fa fa-question-circle"></i>
                    	<span><?php echo $user->question_count > 0 ? custom_number_format($user->question_count) : 0 ?></span>
                    </span>
                    <?php
	               			} else if( $instance['orderby'] == "answer" ) {
	                ?>
                    <span class="answers-count" title="<?php printf( __('%d Answer(s)'), $user->answer_count > 0 ? $user->answer_count : 0 ) ?>">
                    	<i class="fa fa-comments"></i>
                    	<span><?php echo $user->answer_count > 0 ? custom_number_format($user->answer_count) : 0 ?></span>
                    </span>
                    <?php 	} else { ?>
                    <span class="points-count" title="<?php printf( __('%d Point(s)'), $user->points > 0 ? $user->points : 0 ) ?>">
                    	<i class="fa fa-star"></i>
                    	<span><?php echo $user->points > 0 ? custom_number_format($user->points) : 0 ?></span>
                    </span>
                    <?php
                			}
                		}//end if latest
                	?>
	            </li>
	        <?php $i++;} ?>
	        </ul>
	    </div>
	</div>
	<?php
	}
}

/**
 * QA_Recent_Activity widget class
 *
 * @since 1.0
 */
class QA_Recent_Activity extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the list of user\'s activities.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('qa_recent_activity', __('QA Recent Activities',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'number' => '8') );

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of activities to display:', ET_DOMAIN) ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
			</p>
		<?php

	}

	function widget( $args, $instance ) {
		global $user_ID;
		$param = array();

		if( !$user_ID ) return;

		if(isset($instance['number']) && $instance['number']) {
			$param['showposts']	=	$instance['number'];
		}

		?>
		<div class="widget widget-recent-activity">
			<?php if(esc_attr($instance['title']) != "" ){ ?>
				<h3><?php echo esc_attr($instance['title']) ?></h3>
			<?php }
			if(!get_transient( 'qa_changelog_'.$user_ID )) {
				ob_start();
				$content	=	qa_list_changelog($param);
				$content	=	ob_get_clean();
				set_transient( 'qa_changelog_'.$user_ID , $content, 300 );
			} else {
				$content	=	get_transient( 'qa_changelog_'.$user_ID );
			}
			echo $content;
		?>
		</div><!-- END widget-recent-activities -->

		<?php
	}
}