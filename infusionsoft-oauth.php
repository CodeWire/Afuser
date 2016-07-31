<?php
/*
Plugin Name: Infusionsoft oAuth
Plugin URI: http://connectify.io
Description: Sample Content
Version: 0.0.1
Author: Hariharan
Author URI: http://connectify.com
*/

 
// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define( 'INFUSIONSOFT_OAUTH_VERSION', '1.6.2' );
define( 'INFUSIONSOFT_OAUTH_RELEASE_DATE', date_i18n( 'F j, Y', '1397937230' ) );
define( 'INFUSIONSOFT_OAUTH_DIR', plugin_dir_path( __FILE__ ) );
define( 'INFUSIONSOFT_OAUTH_URL', plugin_dir_url( __FILE__ ) );


if (!class_exists("Infusionsoft_Oauth")) :

class Infusionsoft_Oauth {
	var $settings, $options_page;
	
	function __construct() {	
 		
		if (is_admin()) {
			// Load example settings page
			if (!class_exists("Infusionsoft_Oauth_Settings"))
			require(INFUSIONSOFT_OAUTH_DIR . 'infusionsoft-oauth-settings.php');
			$this->settings = new Infusionsoft_Oauth_Settings();
	
			 
		}
		
		add_action('init', array($this,'init') );
		add_action('admin_init', array($this,'admin_init') );
		add_action('admin_menu', array($this,'admin_menu') );
	 
		 
		add_shortcode( 'infusionsoft_oauth_login', array( $this, 'infusionsoft_oauth_login' ) ); 
		register_activation_hook( __FILE__, array($this,'activate') );
		register_deactivation_hook( __FILE__, array($this,'deactivate') );
		
		add_action('login_message', array( $this, 'infusionsoft_oauth_login' ) ); 
		
		 
		
		$this->settings_field = 'infusionsoft_oauth_options'; 
		$this->options = get_option( $this->settings_field );
		 add_action( 'wp_ajax_infusion_oauth_geturl', array( $this, 'infusionsoft_oauth_login_ajax' ) ); 
	    add_action( 'wp_ajax_nopriv_infusion_oauth_geturl',array( $this, 'infusionsoft_oauth_login_ajax' ) ); 
		
	}

 	 
	 
	
	 protected function get_field_name( $name ) {

		return sprintf( '%s[%s]', $this->settings_field, $name );

	}

	protected function get_field_id( $id ) {

		return sprintf( '%s[%s]', $this->settings_field, $id );

	}

	protected function get_field_value( $key ) {

		return $this->options[$key];

	}
	  
	
	function addWithDupCheck($infusionsoft) {
			
				$contact = array('FirstName' => 'John', 'LastName' => 'Doe', 'Email' => 'johndoe@mailinator.com');
			
				return $infusionsoft->contacts->addWithDupCheck($contact, 'Email');
			}
	
		
		
	function infusionsoft_oauth_login() {
	
			session_start();
			
			require_once INFUSIONSOFT_OAUTH_DIR.'vendor/autoload.php';
			
			$client_id =  $this->get_field_value( 'client_id' ); 
			$client_secret =  $this->get_field_value( 'client_secret' ); 
			$redirect_url =  $this->get_field_value( 'redirect_url' ); 
			
			$infusionsoft = new \Infusionsoft\Infusionsoft(array(
				'clientId' => $client_id,
				'clientSecret' => $client_secret,
				'redirectUri' => $redirect_url,
			));
			
			unset($_SESSION['token']);
			
			// By default, the SDK uses the Guzzle HTTP library for requests. To use CURL,
			// you can change the HTTP client by using following line:
			// $infusionsoft->setHttpClient(new \Infusionsoft\Http\CurlClient());
			
			// If the serialized token is available in the session storage, we tell the SDK
			// to use that token for subsequent requests.
			if (isset($_SESSION['token'])) {
				$infusionsoft->setToken(unserialize($_SESSION['token']));
			}
			
			// If we are returning from Infusionsoft we need to exchange the code for an
			// access token.
			if (isset($_GET['code']) and !$infusionsoft->getToken()) {
				$infusionsoft->requestAccessToken($_GET['code']);
			}

			if ($infusionsoft->getToken()) {
				try {
					
					$cid = $this->addWithDupCheck($infusionsoft);
				} catch (\Infusionsoft\TokenExpiredException $e) {
					// If the request fails due to an expired access token, we can refresh
					// the token and then do the request again.
					$infusionsoft->refreshAccessToken();
			
					$cid = $this->addWithDupCheck($infusionsoft);
				}
			
				$contact = $infusionsoft->contacts->load($cid, array('Id', 'FirstName', 'LastName', 'Email'));
			
				//var_dump($contact);
			
				// Save the serialized token to the current session for subsequent requests
				$_SESSION['token'] = serialize($infusionsoft->getToken());
			} else {
				echo '<a style="background: none repeat scroll 0 0 #409440;color: #ffffff;display: block;font-size: 14px;padding: 3% 0;text-align: center;
    text-decoration: none;" href="' . $infusionsoft->getAuthorizationUrl() . '">Login / Register vis Infusionsoft</a>';
		?>
        	
        <?php
				 
			}	 
	}
	
	
	function infusionsoft_oauth_login_ajax() {
	
			session_start();
			
			require_once INFUSIONSOFT_OAUTH_DIR.'vendor/autoload.php';
			
			$client_id =  $this->get_field_value( 'client_id' ); 
			$client_secret =  $this->get_field_value( 'client_secret' ); 
			$redirect_url =  $this->get_field_value( 'redirect_url' ); 
			
			$infusionsoft = new \Infusionsoft\Infusionsoft(array(
				'clientId' => $client_id,
				'clientSecret' => $client_secret,
				'redirectUri' => $redirect_url,
			));
			
			unset($_SESSION['token']);
			
			// By default, the SDK uses the Guzzle HTTP library for requests. To use CURL,
			// you can change the HTTP client by using following line:
			// $infusionsoft->setHttpClient(new \Infusionsoft\Http\CurlClient());
			
			// If the serialized token is available in the session storage, we tell the SDK
			// to use that token for subsequent requests.
			if (isset($_SESSION['token'])) {
				$infusionsoft->setToken(unserialize($_SESSION['token']));
			}
			
			// If we are returning from Infusionsoft we need to exchange the code for an
			// access token.
			if (isset($_GET['code']) and !$infusionsoft->getToken()) {
				$infusionsoft->requestAccessToken($_GET['code']);
			}

			if ($infusionsoft->getToken()) {
				try {
					
					$cid = $this->addWithDupCheck($infusionsoft);
				} catch (\Infusionsoft\TokenExpiredException $e) {
					// If the request fails due to an expired access token, we can refresh
					// the token and then do the request again.
					$infusionsoft->refreshAccessToken();
			
					$cid = $this->addWithDupCheck($infusionsoft);
				}
			
				$contact = $infusionsoft->contacts->load($cid, array('Id', 'FirstName', 'LastName', 'Email'));
			
				//var_dump($contact);
			
				// Save the serialized token to the current session for subsequent requests
				$_SESSION['token'] = serialize($infusionsoft->getToken());
			} else {
				echo '<a  target="_blank" style="background: none repeat scroll 0 0 #409440;color: #ffffff;display: block;font-size: 14px;padding: 3% 0;text-align: center;text-decoration: none;" href="' . $infusionsoft->getAuthorizationUrl() . '">Login / Register vis Infusionsoft</a>';
				 die(0);
			}	 
	}

	function network_propagate($pfunction, $networkwide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function 
			// for each blog id
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					call_user_func($pfunction, $networkwide);
				}
				switch_to_blog($old_blog);
				return;
			}	
		} 
		call_user_func($pfunction, $networkwide);
	}

	function activate($networkwide) {
		$this->network_propagate(array($this, '_activate'), $networkwide);
	}

	function deactivate($networkwide) {
		$this->network_propagate(array($this, '_deactivate'), $networkwide);
	}

	/*
		Enter our plugin activation code here.
	*/
	function _activate() {}

	/*
		Enter our plugin deactivation code here.
	*/
	function _deactivate() {}
	

	/*
		Load language translation files (if any) for our plugin.
	*/
	function init() {
	  
	 if ( !is_user_logged_in() && ! is_admin() ) {
	
			session_start();
			
			require_once INFUSIONSOFT_OAUTH_DIR.'vendor/autoload.php';
			
			$client_id =  $this->get_field_value( 'client_id' ); 
			$client_secret =  $this->get_field_value( 'client_secret' ); 
			$redirect_url =  $this->get_field_value( 'redirect_url' ); 
			
			$infusionsoft = new \Infusionsoft\Infusionsoft(array(
				'clientId' => $client_id,
				'clientSecret' => $client_secret,
				'redirectUri' => $redirect_url,
			));

			
			// By default, the SDK uses the Guzzle HTTP library for requests. To use CURL,
			// you can change the HTTP client by using following line:
			// $infusionsoft->setHttpClient(new \Infusionsoft\Http\CurlClient());
			
			// If the serialized token is available in the session storage, we tell the SDK
			// to use that token for subsequent requests.
			if (isset($_SESSION['token'])) {
				$infusionsoft->setToken(unserialize($_SESSION['token']));
			}
			
			// If we are returning from Infusionsoft we need to exchange the code for an
			// access token.
			if (isset($_GET['code']) and !$infusionsoft->getToken()) {
				$infusionsoft->requestAccessToken($_GET['code']);
			}
			
			
			
			if ($infusionsoft->getToken()) {
				try {

					 $userinfo = $infusionsoft->data()->getUserInfo();
					 $username = $userinfo['casUsername'];

					$error = '';
					
					 
					$uname = $username;
					$email = $uname;
					 
 
					 
					$user_id = email_exists( $email );
					if ( !$user_id ) {
						$random_password = wp_generate_password( 12, false );
						$user_id = wp_create_user( $uname, $random_password, $email );
						
					} 
					
					$user = get_user_by( 'id', $user_id ); 
						if( $user ) {
							wp_set_current_user( $user_id, $user->user_login );
							wp_set_auth_cookie( $user_id );
							do_action( 'wp_login', $user->user_login );
						}
					wp_new_user_notification($user_id,$random_password);
			 
					//$cid = $this->addWithDupCheck($infusionsoft);
				} catch (\Infusionsoft\TokenExpiredException $e) {
					// If the request fails due to an expired access token, we can refresh
					// the token and then do the request again.
					$infusionsoft->refreshAccessToken();
			
					$cid = $this->addWithDupCheck($infusionsoft);
				}
			
				//$contact = $infusionsoft->contacts->load($cid, array('Id', 'FirstName', 'LastName', 'Email'));
			
				//var_dump($contact);
			
				// Save the serialized token to the current session for subsequent requests
				$_SESSION['token'] = serialize($infusionsoft->getToken());
			} else {
				/*echo '<a style="background: none repeat scroll 0 0 #409440;color: #ffffff;display: block;font-size: 14px;padding: 3% 0;text-align: center;
    text-decoration: none;" href="' . $infusionsoft->getAuthorizationUrl() . '">Login / Register vis Infusionsoft</a>';*/
			}

}
	
	 
		
		load_plugin_textdomain( 'infusionsoft_oauth', INFUSIONSOFT_OAUTH_DIR . 'lang', 
							   basename( dirname( __FILE__ ) ) . '/lang' );
	}

	function admin_init() {
	}

	function admin_menu() {
	}


	/*
		Example print function for debugging. 
	*/	
	function print_example($str, $print_info=TRUE) {
		if (!$print_info) return;
		__($str . "<br/><br/>\n", 'infusionsoft_oauth' );
	}

	 
	function javascript_redirect($location) {
		// redirect after header here can't use wp_redirect($location);
		?>
		  <script type="text/javascript">
		  <!--
		  window.location= <?php echo "'" . $location . "'"; ?>;
		  //-->
		  </script>
		<?php
		exit;
	}

} // end class
endif;

// Initialize our plugin object.
global $infusionsoft_oauth;
if (class_exists("Infusionsoft_Oauth") && !$infusionsoft_oauth) {
    $infusionsoft_oauth = new Infusionsoft_Oauth();	
}	
?>