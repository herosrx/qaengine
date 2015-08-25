<?php
	global $user, $wp_roles;

	$user_role  =	$user->roles;
	$user_role  =	array_pop($user_role);
	$role_names =	$wp_roles->role_names;
	$user       = 	QA_Member::convert($user);
?>
<li class="et-member" data-id="<?php echo $user->ID; ?>">
	<div class="et-mem-container">
		<div class="et-mem-avatar">
			<?php echo get_avatar( $user->ID ); ?>
		</div>
		<!-- action change user role -->
		<div class="et-act">
			<?php if ( !$user->is_ban ) { ?>
				<span class="user-points">
					<input type="text" value="<?php echo $user->qa_point ? $user->qa_point : 0;  ?>" class="regular-input" name="qa_point" /> <?php _e('Points', ET_DOMAIN) ?>
				</span>
				<select name="role" class="role-change regular-input" >
					<?php foreach ( $role_names as $role_name => $role_label ) {
						if($role_name == $user_role )
							echo '<option value="'. $role_name .'" selected="selected">'. $role_label .'</option>';
						else
							echo '<option value="'. $role_name .'" >'. $role_label .'</option>';
					} ?>
				</select>
			<?php } else { ?>
				<span class="ban-badge">
					<?php printf( __('Banned until %s', ET_DOMAIN), $user->ban_expired )  ?>
				</span>
			<?php } ?>
			<!-- Ban Button -->
			<?php if ( $user_role != 'administrator' && $user->is_ban ) { ?>
				<a class="et-act-unban" href="#" title="<?php _e( 'Unban this user', ET_DOMAIN ) ?>">
					<span class="icon" data-icon=")"></span>
				</a>
			<?php } else if ( $user_role != 'administrator' ) { ?>
				<a class="et-act-ban" href="#" data-toggle="modal" data-target="#ban_modal" title="<?php _e( 'Ban this user', ET_DOMAIN ) ?>">
					<span class="icon" data-icon="("></span>
				</a>
			<?php } ?>
			<!-- End / Ban Button -->
			<?php if($user->register_status == "unconfirm"){ ?>
			<a class="action et-act-confirm" data-act="confirm" href="javascript:void(0);" title="<?php _e( 'Confirm this user', ET_DOMAIN ) ?>">
				<span class="icon" data-icon="3"></span>
			</a>
			<?php } ?>
		</div>

		<div class="et-mem-detail">

			<div class="et-mem-top">
				<span class="name">
					<?php echo $user->display_name; ?>
				</span>
				<span class="thread icon" data-icon="w" title="<?php _e("Posts", ET_DOMAIN); ?>">
					<?php echo $user->et_question_count; ?>
				</span>
				<span class="comment icon" data-icon="q" title="<?php _e("Comments", ET_DOMAIN); ?>" >
					<?php echo $user->et_answer_count; ?>
				</span>
			</div>

			<div class="et-mem-bottom">
				<span class="date">
					<?php
						printf(__("Join on %s", ET_DOMAIN), (string)date(get_option('date_format'), strtotime($user->user_registered) ));
					?>
				</span>
				<?php
					if($user->location) {
						echo '<span class="loc icon" data-icon="@">'. $user->location. '</span>';
					} else {
						echo '<span class="loc icon" data-icon="G"></span>';
					}
				?>
			</div>
		</div>
	</div>
</li>