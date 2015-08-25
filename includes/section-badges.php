<?php
class QA_SectionBadge {
	/**
     * Field Constructor.
     *
     * @param array $params 
     * - html tag
     * - id
     * - name 
     * - placeholder 
     * - readonly 
     * - class 
     * - title 
     * @param $groups 
     * @param $parent
     * @since AEFramework 1.0.0
    */
    function __construct( $params = array(), $groups , $parent ) {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $params;
        $temp   =   array ();
        foreach ($groups as $key => $group) {
            $temp[] =   new AE_group ( $group['args'] , $group['fields'] , $parent );
        }
        $this->groups = $temp;

    }

    function render( $first  =   false) {
    	/**
         * show the first section
        */
        $display   =    '';
        if(!$first) {
            $display    =   'style="display:none"';
        }

        $badges		=	QA_Pack::query(array());
        $pack_list	=	array();

    	echo '<div '. $display .' class="et-main-main clearfix inner-content '. $this->field['class'] .'" id="'. $this->field['id'] .'" >';
    ?>
    	<div class="title font-quicksand"><?php _e("User Level", ET_DOMAIN); ?></div>
		<div class="desc">
			<!-- <div class="inner"> -->
				<div id="payment_lists">
					<ul class="pay-plans-list">	
						<?php 
						while( $badges->have_posts() ) { $badges->the_post();
							global $post;
							$pack		 =	QA_Pack::qa_convert($post);
							$pack_list[] =  $pack;
						?>					
							<li class="pack-item item" id="pack_<?php echo $pack->ID; ?>" data-ID="<?php echo $pack->ID; ?>">
								<span class="" style="background:<?php echo $pack->qa_badge_color ?>; width:10px;height:10px;margin-right:10px;"></span>
								<span><?php echo $pack->post_title ?> </span>  
								<?php printf(__("%d points", ET_DOMAIN), $pack->qa_badge_point); ?>						
								<div class="actions">
									<a href="javascript:void(0)" title="Edit" class="icon act-edit" rel="<?php echo $pack->ID; ?>" data-icon="p"></a>
									<a href="javascript:void(0)" title="Delete" class="icon act-del" rel="<?php echo $pack->ID; ?>" data-icon="D"></a>
								</div>
							</li>
						<?php }
						update_option( 'qa_level' , $pack_list );
						 ?>
						<!-- json data for pack view -->
						<script type="application/json" id="ae_pack_list">
							<?php echo json_encode($pack_list); ?>
						</script>
					</ul>
					<input id="confirm_delete_pack" value="<?php _e("Are you sure you want to delete this badge?", ET_DOMAIN); ?>" type="hidden" />
				</div>
				<div class="item">
					<form id="" action="qa-add-bage" class="engine-payment-form add-pack-form">
						<div class="form payment-plan">
							<div class="form-item">
								<div class="label"><?php _e("Enter a name for your badge", ET_DOMAIN); ?></div>
								<input class="bg-grey-input not-empty required" name="post_title" type="text">
							</div>
							<div class="form-item f-left-all clearfix">
								<div class="width33p">
									<div class="label"><?php _e("Point", ET_DOMAIN); ?></div>
									<input class="bg-grey-input width50p not-empty is-number required number" name="qa_badge_point" type="text" /> 
								</div>
								<div class="width33p">
									<div class="label"><?php _e("Color", ET_DOMAIN); ?></div>
									<input class="color-picker bg-grey-input width50p not-empty is-number required" type="text" name="qa_badge_color" /> 							
								</div>
							</div>
							
							<div class="submit">
								<button class="btn-button engine-submit-btn add_payment_plan">
									<span><?php _e("Add badge", ET_DOMAIN); ?></span><span class="icon" data-icon="+"></span>
								</button>
							</div>
						</div>
					</form>
				

				<script type="text/template" id="ae-post-item">

					<span class="" style="background:{{= qa_badge_color }}; width:10px;height:10px;margin-right:10px;"></span>
					<span>{{= post_title }}</span>  
					{{= qa_point_text }}
					<div class="actions">
						<a href="javascript:void(0)" title="Edit" class="icon act-edit" rel="665" data-icon="p"></a>
						<a href="javascript:void(0)" title="Delete" class="icon act-del" rel="665" data-icon="D"></a>
					</div>					
				</script>

				<script type="text/template" id="template_edit_form">

					<form action="qa-update-badge" class="edit-plan engine-payment-form">
						<input type="hidden" name="id" value="{{= id }}">
						<input type="hidden" name="qa_point_text" value="{{= qa_point_text }}">
						<div class="form payment-plan">
							<div class="form-item">
								<div class="label"><?php _e("Enter a name for your badge", ET_DOMAIN); ?></div>
								<input value="{{= post_title }}" class="bg-grey-input not-empty required" name="post_title" type="text">
							</div>
							<div class="form-item f-left-all clearfix">
								<div class="width33p">
									<div class="label"><?php _e("Point", ET_DOMAIN); ?></div>
									<input value="{{= qa_badge_point }}" class="bg-grey-input width50p not-empty is-number required number" name="qa_badge_point" type="text" /> 
								</div>
								<div class="width33p">
									<div class="label"><?php _e("Color", ET_DOMAIN); ?></div>
									<input value="{{= qa_badge_color }}" class="color-picker bg-grey-input width50p not-empty is-number required" type="text" name="qa_badge_color" /> 							
								</div>
							</div>
							<div class="submit">
								<button  class="btn-button engine-submit-btn add_payment_plan">
									<span>Save Plan</span><span class="icon" data-icon="+"></span>
								</button>
								or <a href="javascript:void(0)" class="cancel-edit">Cancel</a>
							</div>
						</div>
					</form>
				</script>
			</div>
		</div>
    <?php 
    	echo '</div>';
    }

    function render_menu ( $first =  false ) {
        $class= '';
        if($first) $class= 'active';

        if( isset( $this->field['title'] )) {

            echo '<li>
                <a href="#'. $this->field['id'] .'" menu-data="" class="'. $class .'">
                    <span class="icon" data-icon="'. $this->field['icon'] .'"></span>'. $this->field['title'] .
                '</a>
            </li>';
        }
    }
}