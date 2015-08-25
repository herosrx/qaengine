<?php 
/**
 * AE overview 
 * show all post, payment, order status on site
 * @package AE
 * @version 1.0
 * @author Dakachi
*/
class AE_Overview extends AE_Page {

	public function __construct( $post_types = array() ) {
		if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'et-overview' ) {
			$this->add_action('admin_enqueue_scripts' , 'overview_scripts');
			$this->add_action('admin_print_styles' , 'overview_styles');	
		}
		
		$this->post_types = $post_types;
	}
    /**
     * render container element
    */
    function render() {

    	// get all custom post type
    	$args = array(
		   'public'   => true,
		   '_builtin' => false
		);

		$output 	= 'names'; // names or objects, note names is the default
		$operator 	= 'and'; // 'and' or 'or'
    	$post_types = $this->post_types; 

    	$daily_data		=	array();
    	$weekly_data	=	array();
    	$monthly_data	=	array();    	

		foreach ( $post_types  as $post_type ) {
			$obj = get_post_type_object( $post_type );

		   	$monthly	=	$this->get_monthly_stat( $post_type );
			$monthly_data[]	=	array('label' => $obj->labels->name, 'data' => $monthly  , 'title' => __("Overview", ET_DOMAIN));
			

			$weekly	=	$this->get_weekly_stat( $post_type );
			$weekly_data[]	=	array('label' => $obj->labels->name, 'data' => $weekly , 'title' => __("3 Months Overview", ET_DOMAIN) );

			$daily	=	$this->get_daily_stat( $post_type );
			$daily_data[]	=	array('label' => $obj->labels->name, 'data' => $daily , 'title' => __("2 weeks Overview", ET_DOMAIN) );
		}
		

		$daily_data[]	=	array( 'label' => __("Signup", ET_DOMAIN), 'data' => $this->get_daily_registration(), 'title' => __("Overview", ET_DOMAIN) );
		$weekly_data[]	=	array( 'label' => __("Signup", ET_DOMAIN), 'data' => $this->get_weekly_registration() , 'title' => __("Overview", ET_DOMAIN) );
		$monthly_data[]	=	array( 'label' => __("Signup", ET_DOMAIN), 'data' => $this->get_monthly_registration(), 'title' => __("Overview", ET_DOMAIN) );

    ?>	
    	<script type="application/json" id="monthly_data">
    	<?php echo json_encode( $monthly_data ); ?>
    	</script>

    	<script type="application/json" id="weekly_data">
    	<?php echo json_encode( $weekly_data ); ?>
    	</script>

    	<script type="application/json" id="daily_data">
    	<?php echo json_encode( $daily_data ); ?>
    	</script>

    	<div class="ae-overview">
	    	<div class="charts" style="">
	    		<div id="daily_chart" style=""></div>
	    		<div id="weekly_chart" style=""></div>
	    		<div id="monthly_chart" style=""></div>
	    	</div> 
	    	<!-- <div class="details" style="">
		    	<ul>
		    		<strong>Today</strong>
		    		<label for=""></label>
		    		<li>dakl dlkasd aslk kldja l</li>
		    		Yesterday
		    		<li>dakl dlkasd aslk kldja l</li>
		    		This week
		    		<li>dakl dlkasd aslk kldja l</li>
		    		Lastweek
		    		<li>dakl dlkasd aslk kldja l</li>

		    	</ul>
	    	</div>	 -->
    	</div>
    <?php 
    } 

    /**
	 * get daily registration
	*/
    protected function get_daily_registration(){
		global $wpdb;

		$key = $wpdb->prefix . 'capabilities';

		$from = strtotime('-2 weeks');
		$from_date 	= date('Y-m-d 00:00:00', $from);
		

		$sql = "SELECT date({$wpdb->users}.user_registered) as date, count({$wpdb->users}.ID) as count FROM {$wpdb->users} 
				INNER JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID AND {$wpdb->usermeta}.meta_key = '$key' 
				WHERE 
					STRCMP(user_registered, '$from_date') >= 0
				GROUP BY date({$wpdb->users}.user_registered)";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();

		foreach ($result as $index => $row) {
			if( $index > 0 ) {
				$distance	=	(strtotime($row['date']) - strtotime($result[($index-1)]['date']))/(24*3600);

				if($distance > 1) {
					for ( $i=0; $i < $distance -1 ; $i++) { 
						$week	=	$i +1;
						$statistic[] = array( date('F j, Y', strtotime( $result[($index-1)]['date'] ) + $week*60*60*24  ) , 0 );
					}
				}
			}
			$statistic[] = array( date( 'F j, Y', strtotime($row['date']) ), $row['count']);
		}

		return $statistic;
	}
	/**
	 * get weekly registration
	*/
	protected function get_weekly_registration(){
		global $wpdb;

		$key = $wpdb->prefix . 'capabilities';

		$from = strtotime('-3 months');
		$from_date 	= date('Y-m-d 00:00:00', $from);
		

		$sql = "SELECT WEEK({$wpdb->users}.user_registered) as `date`, count({$wpdb->users}.ID) as count FROM {$wpdb->users} 
				INNER JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID AND {$wpdb->usermeta}.meta_key = '$key' 
				WHERE 
					STRCMP(user_registered, '$from_date') >= 0
				GROUP BY `date`";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();


		foreach ( $result as $index => $row ) {
			$date		=	$row['date'] * 7;
			
			if( $index > 0 ) {
				$distance	=	$row['date'] - $result[($index-1)]['date'];
				if($distance > 1) {
					for ($i=0; $i < $distance -1 ; $i++) { 
						$week	=	($result[($index-1)]['date'] + ($i +1) ) * 7;
						$statistic[] = array( date('F j, Y', strtotime('01 January 2014' ) + $week*60*60*24  ) , 0 );
					}
				}
			}
			
			$statistic[] = array( date('F j, Y', strtotime('01 January 2014' ) + $date*60*60*24  ) , $row['count']);
		}

		return $statistic;
	}

	/**
	 * get monthly registration
	*/
	protected function get_monthly_registration(){
		global $wpdb;

		$key = $wpdb->prefix . 'capabilities';

		// if ( $from == false ) $from = strtotime('-2 weeks');
		$from_date 	= date( 'Y-m-d 00:00:00', strtotime('01-01-'.date("Y") ) );
		

		$sql = "SELECT MONTH({$wpdb->users}.user_registered) as date, user_registered as post_date , count({$wpdb->users}.ID) as count FROM {$wpdb->users} 
				INNER JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID AND {$wpdb->usermeta}.meta_key = '$key' 
				WHERE 
					STRCMP(user_registered, '$from_date') >= 0
				GROUP BY date";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();

		foreach ($result as $index => $row) {
			$year		 = date("Y", strtotime( $row['post_date']) );
			$statistic[] = array( date( 'F j, Y',  strtotime( '01-' . $row['date'] . '-'. $year) ) , $row['count']);
		}

		return $statistic;
	}

    /**
	 * Retrieve site's monthly stat
	 * @param String $post_type The post type want to retrieve
	 * @since 1.0
	 * @author Toan
	 */
	protected function get_monthly_stat( $post_type, $from = 0 , $to = 0 ){

		global $wpdb;

		$from_date 	= date( 'Y-m-d 00:00:00', strtotime('01-01-'.date("Y") ) );
		// $to_date 	= date('Y-m-d 00:00:00', $to);

		$sql = "SELECT MONTH(post_date) AS `date`, post_date ,  COUNT(ID) as `count` FROM {$wpdb->posts} 
				WHERE 	post_type = '$post_type' AND 
						post_status IN ('publish','pending','closed') 
				GROUP BY `date`";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();

		foreach ($result as $index => $row) {
			$year		 = date("Y", strtotime($row['post_date']));
			$statistic[] = array( date( 'F j, Y',  strtotime( '01-' . $row['date'] . '-'. $year) ) , $row['count']);
		}
		
		return $statistic;

	}

	/**
	 * Retrieve site's weekly stat
	 * @param String $post_type The post type want to retrieve
	 * @since 1.0
	 * @author Dakachi
	 */
	protected function get_weekly_stat( $post_type, $from = 0 , $to = 0 ){

		global $wpdb;

		$from = strtotime('-3 months');
		$from_date 	= date('Y-m-d 00:00:00', $from);

		$sql = "SELECT WEEK(post_date) AS `date`, post_date ,  COUNT(ID) as `count` FROM {$wpdb->posts} 
				WHERE 	post_type = '$post_type' AND 
						STRCMP(post_date, '$from_date') >= 0 AND 
						post_status IN ('publish','pending','closed') 
				GROUP BY `date`";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();
		
		foreach ($result as $index => $row) {

			$date	=	$row['date'] * 7;
			if( $index > 0 ) {
				$distance	=	$row['date'] - $result[($index-1)]['date'];
				if($distance > 1) {
					for ($i=0; $i < $distance -1; $i++) { 
						$week	=	($result[($index-1)]['date'] + ($i +1) ) * 7;
						$statistic[] = array( date('F j, Y', strtotime('01 January 2014' ) + $week*60*60*24  ) , 0 );
					}
				}
			}
			$statistic[] = array( date('F j, Y', strtotime('01 January 2014' ) + $date*60*60*24  ) , $row['count']);

		}

		return $statistic;

	}

    /**
	 * Retrieve site's daily stat
	 * @param String $post_type The post type want to retrieve
	 * @since 1.0
	 * @author Dakachi
	 */
	protected function get_daily_stat( $post_type, $from = 0 , $to = 0 ){

		global $wpdb;

		$from = strtotime('-2 weeks');
		$from_date 	= date('Y-m-d 00:00:00', $from);
		

		$sql = "SELECT date(post_date) AS `date` , post_date, COUNT(ID) as `count` FROM {$wpdb->posts} 
				WHERE 	post_type = '$post_type' AND 
						STRCMP(post_date, '$from_date') >= 0 AND 
						post_status IN ('publish','pending','closed') 
				GROUP BY `date`";
		
		$result = $wpdb->get_results( $sql, ARRAY_A  );
		$statistic = array();

		foreach ($result as $index => $row) {

			if( $index > 0 ) {
				$distance	=	(strtotime($row['date']) - strtotime($result[($index-1)]['date']))/(24*3600);

				if($distance > 1) {
					for ( $i=0; $i < $distance -1 ; $i++) { 
						$week	=	$i +1;
						$statistic[] = array( date('F j, Y', strtotime( $result[($index-1)]['date'] ) + $week*60*60*24  ) , 0 );
					}
				}
			}

			$statistic[] = array( $row['date'] , $row['count']);
		}
		
		return $statistic;

	}

    public function overview_scripts() {
    	?>
    	<!--[if lt IE 9]> <?php $this->add_script( 'excanvas', ae_get_url().'/assets/js/excanvas.min.js' ); ?> <![endif]-->
    	<?php 

    	$this->add_script( 'jqplot', ae_get_url().'/assets/js/jquery.jqplot.min.js', array('jquery') );
		$this->add_script( 'jqplot-plugins', ae_get_url().'/assets/js/jqplot.plugins.js', array('jquery', 'jqplot') );

		$this->add_script( 'ae-overview', ae_get_url().'/assets/js/overview.js', array('jquery', 'jqplot','appengine') );
    }

    public function overview_styles() {
    	$this->add_style( 'jqplot_style', ae_get_url().'/assets/css/jquery.jqplot.min.css', array(), false, 'all' );
    }
    
}
