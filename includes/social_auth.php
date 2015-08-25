<?php

abstract class ET_SocialAuth extends AE_Base{
	protected $social_option;

	abstract protected function send_created_mail($user_id);

	protected $social_id = false;

	public function __construct($type, $social_option, $labels = array()){
		$this->social_type 		= $type;
		$this->social_option 	= $social_option;
		$this->auth_url 		= add_query_arg( 'type', $this->social_type, et_get_page_link('social-connect') );
		$this->labels 			= $labels;

		$this->add_action('wp_enqueue_scripts', 'enqueue_scripts');
		$this->add_action('template_redirect', 'social_redirect');
		$this->add_ajax('et_authentication_' . $type, 'authenticate_user');
		$this->add_ajax('et_confirm_username_' . $type, 'confirm_username');
	}

	public function enqueue_scripts(){
		if ( is_page_template( 'page-social-connect.php' ) ){
			$this->add_script('et-authentication', TEMPLATEURL . '/js/authentication.js', array('jquery', 'underscore', 'backbone'));
			if(isset($_SESSION['et_auth_type'])){
				wp_localize_script( 'et-authentication', 'ae_auth', array(
					'action_auth' 		=> 'et_authentication_' . $_SESSION['et_auth_type'],
					'action_confirm' 	=> 'et_confirm_username_'. $_SESSION['et_auth_type']
				) );
			}
			else{
				wp_redirect( home_url() );
				exit;
			}
		}
	}

	public function social_redirect(){
		if ( is_page_template( 'page-social-connect.php' ) && is_user_logged_in() ){
			wp_redirect( home_url() );
			exit;
		}

		if ( is_page_template( 'page-social-connect.php' ) ){
			global $et_data;
			if ( isset($_GET['type']) && $_GET['type'] == $this->social_type ){
				$et_data['auth_labels'] = $this->labels;
			}
		}
	}

	protected function get_user($social_id){
		$users = get_users(array(
			'meta_key' 		=> $this->social_option,
			'meta_value' 	=> $social_id,
			'number' 		=> 1
		));

		if ( !empty($users) && is_array($users) )
			return $users[0];
		else
			return false;
	}

	protected function logged_user_in($social_id){
		$user = $this->get_user($social_id);

		if ( $user != false ){
			wp_set_auth_cookie( $user->ID );
    		wp_set_current_user ( $user->ID );
			return true;
			// wp_redirect( home_url());
			// exit;
		} else {
			return false;
		}
	}

	protected function _create_user($params){
		// insert user
		$result = QA_Member::insert( $params );

		if ( !is_wp_error( $result ) ){
			// send email here
			$this->send_created_mail($result);

			// login
			$user = wp_signon( array(
				'user_login' 	=> $params['user_login'],
				'user_password' => $params['user_pass']
			) );

			if ( is_wp_error( $user ) ){
				return $user;
			} else {
				// Authenticate successfully
				return true;
			}
		} else {
			return $result;
		}
	}

	protected function connect_user( $email, $password ){
		if ( $this->social_id != false ){
			// get user first
			$user = get_user_by( 'email', $email );

			if ( $user == false  )
				return new WP_Error('et_password_not_matched', __("Username and password does not matched", ET_DOMAIN));
			// verify password
			if ( wp_check_password( $password, $user->data->user_pass, $user->ID ) ){
				// connect user
				update_user_meta( $user->ID, $this->social_option, $this->social_id );
				return true;
			} else {
				return new WP_Error('et_password_not_matched', __("Username and password does not matched", ET_DOMAIN));
			}

		} else {
			return new WP_Error('et_wrong_social_id', __("There is an error occurred", ET_DOMAIN));
		}
	}

	protected function social_connect_success(){
		wp_redirect( home_url() );
		exit;
	}

	public function authenticate_user(){
		try {
			// turn on session
			session_start();
			$data = $_POST['content'];

			// find user first
			if ( empty($data['user_email']) || empty($data['user_pass']) ) throw new Exception( __('Login information is missing', ET_DOMAIN) );

			$pattern = '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/';
			//if (!preg_match($pattern, $data['user_email']))
			//var_dump(filter_var($data['user_email'],FILTER_VALIDATE_EMAIL));
			if(filter_var($data['user_email'],FILTER_VALIDATE_EMAIL) === false)
				throw new Exception( __('Please provide a valid email', ET_DOMAIN) );

			$email 	= $data['user_email'];
			$pass 	= $data['user_pass'];

			$user 	= get_user_by( 'email', $email );

			$return = array();

			// if user doesn't exist, create one
			if ( $user == false ){
				// save to session, waiting for username input
				if ( isset($_SESSION['et_auth']) )
					$auth_info = unserialize($_SESSION['et_auth']);
				else
					$auth_info = array();

				$auth_info = wp_parse_args( array(
					'user_email' 	=> $email,
					'user_pass' 	=> $pass
				) , $auth_info );
				$_SESSION['et_auth'] = serialize($auth_info);

				if ( isset($auth_info['user_login']) ){
					$user = get_user_by( 'login', $auth_info['user_login'] );
					$user = QA_Member::convert($user);
					if ( $user == false ){
						$result = QA_Member::insert($auth_info);

						if ( $result == false || is_wp_error( $result ) )
							throw new Exception(__("Can't not authenticate user", ET_DOMAIN));
						else if( empty($_SESSION['et_social_id']) ) {
							throw new Exception(__("Can't find Social ID", ET_DOMAIN));
						} else {
							update_user_meta( $result, $this->social_option, $_SESSION['et_social_id'] );
							do_action('et_after_register', $result);
							wp_set_auth_cookie( $result, 1 );
							unset($_SESSION['et_auth']);
						}

						$return = array(
							'status' 		=> 'linked',
							'user' 			=> $user,
							'redirect_url' 	=> home_url( )
						);
					}
					else {
						$return = array(
							'status' 	=> 'wait'
						);
					}
				} else {
					$return = array(
						'status' 	=> 'wait'
					);
				}
			}
			// if user does exist, connect them
			else {
				// khi ti`m thay user bang email, kiem tra password
				// neu password dung thi dang nhap luon
				if ( wp_check_password( $pass, $user->data->user_pass, $user->ID ) ){
					// connect user
					update_user_meta( $user->ID, $this->social_option, $_SESSION['et_social_id'] );
					//
					wp_set_auth_cookie( $user->ID, 1 );
					unset($_SESSION['et_auth']);

					$return = array(
						'status' 	=> 'linked',
						'user' 		=> $user,
						'redirect_url' 	=> home_url( )
					);
				} else {
					throw new Exception( __("This email is already existed. If you are the owner, please enter the right password", ET_DOMAIN) );
				}
			}
			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> $return
			);
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		wp_send_json($resp);
	}

	public function confirm_username(){
		try {
			session_start();
			// get data
			$data 		= $_POST['content'];
			$auth_info 	= unserialize($_SESSION['et_auth']);
			$username 	= $data['user_login'];

			// verify username
			$user 		= get_user_by( 'login', $username);
			$return 	= array();
			if ( $user != false )
				throw new Exception(__('Username is existed, please choose another one', ET_DOMAIN));
			else {
				$auth_info['user_login'] = $username;
				// create user
				$result = QA_Member::insert( $auth_info );

				if ( is_wp_error( $result ) )
					throw new Exception( $result->get_error_message() );
				else if ( empty( $_SESSION['et_social_id'] ) ){
					throw new Exception( __("Can't find Social ID", ET_DOMAIN) );
				}
				else {
					// creating user successfully
					update_user_meta( $result, $this->social_option, $_SESSION['et_social_id'] );
					do_action('et_after_register', $result);

					wp_set_auth_cookie( $result, 1 );
					unset($_SESSION['et_auth']);

					$return = array(
						'user_id' 		=> $result,
						'redirect_url' 	=> home_url(  )
					);
				}
			}
			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> $return
			);

			///$auth_info['user_login'] = $username;

			// if ( $auth_info ){

			// }

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		wp_send_json($resp);
	}
}

class ET_TwitterAuth extends ET_SocialAuth{

	const OPT_CONSUMER_KEY 		= 'et_twitter_key';
	const OPT_CONSUMER_SECRET 	= 'et_twitter_secret';

	protected $consumer_key;
	protected $consumer_secret;
	protected $oath_callback;

	public function __construct(){
		parent::__construct('twitter', 'et_twitter_id', array(
			'title' 		=> __("SIGN IN WITH TWITTER", ET_DOMAIN),
			'content' 		=> __("This seems to be your first time signing in using your Twitter account.If you already have an account  , please log in using the form below to link it to your Twitter account. Otherwise, please enter an email address and a password on the form, and a username on the next page to create an account.You will only do this step ONCE. Next time, you'll get logged in right away.", ET_DOMAIN),
			'content_confirm' => __("Please provide a username to continue", ET_DOMAIN)
		));
		$this->consumer_key 		= ae_get_option(self::OPT_CONSUMER_KEY, ''); // 'H7ggzgE4rNubSq09SKQJGw';
		$this->consumer_secret 		= ae_get_option(self::OPT_CONSUMER_SECRET, ''); //'zUrMVznhHvrMEKBE5LhipfvRODLlPsvEJLvYiaf4yqE';
		$this->oath_callback 		= add_query_arg( 'action', 'twitterauth_callback', home_url( ));

		// only run if options are given
		if (!empty($this->consumer_key ) && !empty($this->consumer_secret) && !is_user_logged_in()){
			//$this->add_action('init', 'redirect');
			$this->redirect();
		}
	}

	/**
	 * Return if twitter auth are ready to run
	 */
	public static function is_active(){
		$consumer_key 			= ae_get_option(self::OPT_CONSUMER_KEY, '');
		$consumer_secret 		= ae_get_option(self::OPT_CONSUMER_SECRET, '');

		return (!empty($consumer_key) && !empty($consumer_secret));
	}

	protected function send_created_mail($user_id){
		do_action('et_after_register', $user_id);
	}

	/**
	 * Redirect and auth twitter account
	 */
	public function redirect(){
		if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'twitterauth' ){
			// request token
			session_start();
			require_once dirname(__FILE__) . '/twitteroauth/twitteroauth.php';

			// create connection
			$connection 	= new TwitterOAuth($this->consumer_key, $this->consumer_secret);

			// request token
			$request_token 	= $connection->getRequestToken($this->oath_callback);

			//
			if ( $request_token ){
				// var_dump($request_token);
				// exit;
				$token 							= $request_token['oauth_token'];
				$_SESSION['oauth_token'] 		= $token;
				$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

				// redirect to twitter
				switch ($connection->http_code)
			    {
			        case 200:
			            $url = $connection->getAuthorizeURL($request_token);
			            //redirect to Twitter .
			            header('Location: ' . $url);
			            exit;
			            break;
			        default:
			            echo "Coonection with twitter Failed";
			            exit;
			            break;
			    }
			} else {
				echo __("Error Receiving Request Token", ET_DOMAIN);
				exit;
			}

		}
		// twitter auth callback
		else if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'twitterauth_callback' && $_GET['oauth_token']){
			// request access token and
			// create account here
			session_start();
			require_once dirname(__FILE__) . '/twitteroauth/twitteroauth.php';

			// create connection
			$connection 	= new TwitterOAuth($this->consumer_key, $this->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

			// request access token
			$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

			//
			if ( $access_token && isset( $access_token['oauth_token'] ) ){
				// recreate connection
				$connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);

				$account 	=	$connection->get('account/verify_credentials');

				// create account
				if ( $account && isset($account->screen_name) && isset($account->name) ){
					// find
					$users = get_users(array(
						'meta_key' 		=> 'et_twitter_id',
						'meta_value' 	=> $account->id
						));
					if ( !empty($users) && is_array($users) ){
						$user = $users[0];
						wp_set_auth_cookie( $user->ID, 1 );
						wp_redirect( home_url());
						exit;
					}

					$avatars = array();
					$sizes 		= get_intermediate_image_sizes();
					foreach ($sizes as $size) {
						$avatars[$size] = array($account->profile_image_url);
					}

					// save user info for saving later
					$_SESSION['user_login']        = $account->screen_name;
					$_SESSION['display_name']      = $account->name;
					$_SESSION['et_twitter_id']     = $account->id;
					$_SESSION['user_location']     = $account->location;
					$_SESSION['description']       = $account->description;
					$_SESSION['profile_image_url'] = $account->profile_image_url;
					$_SESSION['et_auth']           = serialize(array(
						'user_login'    => $account->screen_name,
						'display_name'  => $account->name,
						'user_location' => $account->location,
						'user_twitter'  => 'https://twitter.com/'.$account->screen_name,
						'description'   => $account->description,
						'et_avatar'     => $avatars,
					));
					$_SESSION['et_social_id'] = $account->id;
					$_SESSION['et_auth_type'] = 'twitter';

					wp_redirect( $this->auth_url );
					exit;
				}
			}
			exit;
		}
 		// create user
		else if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'twitterauth_login' ){
			session_start();
			if ( !empty($_POST['user_email']) ){
				$password    = wp_generate_password();
				$new_account = array(
					'user_login'    => $_SESSION['user_login'],
					'display_name'  => $_SESSION['display_name'],
					'et_twitter_id' => $_SESSION['et_twitter_id'],
					'user_location' => $_SESSION['user_location'],
					'user_twitter'  => 'https://twitter.com/'.$_SESSION['user_login'],
					'description'   => $_SESSION['description'],
					'user_email'    => $_POST['user_email'],
					'user_pass'     => $password,
					'et_avatar'     => array(
						'thumbnail' => array( $_SESSION['profile_image_url'] )
					)
				);

				$user = get_user_by( 'login', $new_account['user_login'] );
				if ( $user != false ){
					$new_account['user_login'] = str_replace('@', '', $_POST['user_email']);
				}

				$result = QA_Member::insert( $new_account );
				if ( !is_wp_error( $result ) ){
					// send email here
					do_action('et_after_register', $result);
					// login
					$user = wp_signon( array(
						'user_login' 	=> $new_account['user_login'],
						'user_password' => $new_account['user_pass']
					) );

					if ( is_wp_error( $user ) ){
						global $et_error;
						$et_error = $user->get_error_message();
						//echo $user->get_error_message();
					} else {
						wp_redirect( home_url() );
						exit;
					}
				} else {
					global $et_error;
					$et_error = $result->get_error_message();
				}
			}

			// ask people for password
			include TEMPLATEPATH . '/page-twitter-auth.php';
			exit;
		}
	}

}

class ET_FaceAuth extends ET_SocialAuth{

	public function __construct(){
		parent::__construct('facebook', 'et_facebook_id', array(
			'title' 		=> __('SIGN IN WITH FACEBOOK', ET_DOMAIN),
			'content' 		=> __("This seems to be your first time signing in using your Facebook account.If you already have an account, please log in using the form below to link it to your Facebook account. Otherwise, please enter an email address and a password on the form, and a username on the next page to create an account.You will only do this step ONCE. Next time, you'll get logged in right away.", ET_DOMAIN),
			'content_confirm' => __("Please provide a username to continue", ET_DOMAIN)
		));

		//$this->add_action('init', 'auth_facebook');
		$this->add_action('wp_enqueue_scripts', 'add_scripts', 20);
		$this->add_ajax('et_facebook_auth', 'auth_facebook');
	}

	public function add_scripts(){
		//$this->add_script('facebook_auth', '//connect.facebook.net/en_US/all.js', array(), false, true);
		$this->add_script('facebook_auth', TEMPLATEURL . '/js/facebookauth.js', array('jquery'), false, true);
		wp_localize_script( 'facebook_auth', 'facebook_auth', array(
			'appID' 		=> ae_get_option('et_facebook_key'),
			'auth_url' 		=> home_url( '?action=authentication')
		) );
	}

	// implement abstract method
	protected function send_created_mail($user_id){
		do_action('et_after_register', $user_id);
	}

	public function auth_facebook(){
		try {
			// turn on session
			session_start();

			$data 	= $_POST['content'];
			// find usser
			$user 	= false;
			$return = array( 'redirect_url' => home_url( ) );

			// if user is already authenticated before
			if ( isset($data['id']) && $user = $this->get_user($data['id']) ){
				//
				$result 	= $this->logged_user_in($data['id']);
				$userdata 	= QA_Member::convert( $user );
				$nonce 		= array(
					'reply_thread' => wp_create_nonce( 'insert_reply' ),
					'upload_img'   => wp_create_nonce( 'et_upload_images' ),
				);

				$return = array(
					'user' 		=> $userdata,
					'nonce' 	=> $nonce
				);
			}
			// if user is never authenticated before
			else {
				// avatar
				$ava_response 	= wp_remote_get( 'http://graph.facebook.com/' . $data['id'] . '/picture?type=large&redirect=false');
				if ( !is_wp_error( $ava_response ) )
					$ava_response 	= json_decode( $ava_response['body'] );
				else
					$ava_response 	= false;

				$sizes 		= get_intermediate_image_sizes();
				$avatars 	= array();
				if ( $ava_response ){
					foreach ($sizes as $size) {
						$avatars[$size] = array($ava_response->data->url);
					}
				} else {
					$avatars = false;
				}

				// username
				// $user 		= get_user_by( 'login', $data['username'] );
				//$username 	= $data['username'];
				$username 	= $data['name'];
				// if ( $user != false )
				// 	$username = $data['email'];

				$params = array(
					'user_login' 	=> $username,
					'user_email' 	=> isset($data['email']) ? $data['email'] : false,
					'description' 	=> isset($data['bio']) ? $data['bio'] : false,
					'user_location' => isset($data['location']) ? $data['location']['name'] : false,
					'et_avatar' 	=> $avatars,
				);
				//remove avatar if cant fetch avatar
				foreach ($params as $key => $param) {
					if ( $param == false )
						unset($params[$key]);
				}

				$_SESSION['et_auth'] 		= serialize($params);
				$_SESSION['et_social_id'] 	= $data['id'];
				$_SESSION['et_auth_type'] 	= 'facebook';
				$return['params'] 			= $params;
				$return['redirect_url'] 	= $this->auth_url;// et_get_page_link('social-connect');
			}
			$resp = array(
				'success' 	=> true,
				'msg' 		=> __('You have logged in successfully', ET_DOMAIN),
				'redirect' 	=> home_url(),
				'data'		=> $return
			);
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		wp_send_json($resp);
	}
}

class ET_GoogleAuth extends ET_SocialAuth{

	public function __construct(){
		parent::__construct('google', 'et_google_id', array(
			'title'           => __('SIGN IN WITH GOOGLE+', ET_DOMAIN),
			'content'         => __("This seems to be your first time signing in using your Google+ account.If you already have an account, please log in using the form below to link it to your Facebook account. Otherwise, please enter an email address and a password on the form, and a username on the next page to create an account.You will only do this step ONCE. Next time, you'll get logged in right away.", ET_DOMAIN),
			'content_confirm' => __("Please provide a username to continue", ET_DOMAIN)
		));

		$this->add_action('wp_enqueue_scripts', 'add_scripts', 20);
		$this->add_ajax('et_google_auth', 'auth_google');
	}

	public function add_scripts(){
		//session_start();
		//if( !is_page_template( 'page-social-connect.php' ) && ( !isset($_SESSION['et_auth_type']) || $_SESSION['et_auth_type'] != "google" )  ){
		if(!et_load_mobile()){
			$this->add_script('google_auth', TEMPLATEURL . '/js/googleauth.js', array('jquery'), false, true);
			wp_localize_script( 'google_auth', 'google_auth', array(
				'appID' 		=> ae_get_option('gplus_client_id'),
				'auth_url' 		=> home_url( '?action=authentication')
			) );
		}
	}

	// implement abstract method
	protected function send_created_mail($user_id){
		do_action('et_after_register', $user_id);
	}

	public function auth_google(){
		try {
			// turn on session
			session_start();

			$data 	= $_POST['content'];
			// find usser
			$user 	= false;
			$return = array( 'redirect_url' => home_url( ) );

			// if user is already authenticated before
			if ( isset($data['id']) && $user = $this->get_user($data['id']) ){
				//
				$result 	= $this->logged_user_in($data['id']);
				$userdata 	= QA_Member::convert( $user );
				$nonce 		= array(
					'reply_thread' => wp_create_nonce( 'insert_reply' ),
					'upload_img'   => wp_create_nonce( 'et_upload_images' ),
				);

				$return = array(
					'user' 		=> $userdata,
					'nonce' 	=> $nonce
				);
			}
			// if user is never authenticated before
			else {
				// avatar
				$ava_response = isset($data['picture'])  ? $data['picture'] : '';

				$sizes        = get_intermediate_image_sizes();
				$avatars      = array();
				if ( $ava_response ){
					foreach ($sizes as $size) {
						$avatars[$size] = array($ava_response);
					}
				} else {
					$avatars = false;
				}

				// username
				//$username 	= $data['displayName'];
				$username       = $data['name'];

				$params = array(
					'user_login' 	=> $username,
					'user_email' 	=> isset($data['email']) ? $data['email'] : false,
					'et_avatar' 	=> $avatars,
				);
				//remove avatar if cant fetch avatar
				foreach ($params as $key => $param) {
					if ( $param == false )
						unset($params[$key]);
				}

				$_SESSION['et_auth']      = serialize($params);
				$_SESSION['et_social_id'] = $data['id'];
				$_SESSION['et_auth_type'] = 'google';
				$return['params']         = $params;
				$return['redirect_url']   = $this->auth_url;// et_get_page_link('social-connect');
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> __('You have logged in successfully', ET_DOMAIN),
				'redirect' 	=> home_url(),
				'data'		=> $return
			);

		} catch (Exception $e) {

			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);

		}
		wp_send_json($resp);
	}
}
?>
