<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists("Infusionsoft_Oauth_Settings")) :

/* 
	Create example settings page for our plugin.
	
	- We show how to render our own controls using HTML.
	- We show how to get WordPress to render controls for us using do_settings_sections'
	
	WordPress Settings API tutorials
	http://codex.wordpress.org/Settings_API
	http://ottopress.com/2009/wordpress-settings-api-tutorial/
*/
class Infusionsoft_Oauth_Settings {

	public static $default_settings = 
		array( 	
			  	 
				'client_id' => '',
			  	'client_secret' => '',
			  	'redirect_url' => ''
				);
	var $pagehook, $page_id, $settings_field, $options;

	
	function __construct() {
 
		$this->page_id = 'infusionsoft_oauth';
		// This is the get_options slug used in the database to store our plugin option values.
		$this->settings_field = 'infusionsoft_oauth_options';
		$this->options = get_option( $this->settings_field );

		add_action('admin_init', array($this,'admin_init'), 20 );
		add_action( 'admin_menu', array($this, 'admin_menu'), 20);
		
		
	}
	 
	function admin_init() {
		register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
		add_option( $this->settings_field, Infusionsoft_Oauth_Settings::$default_settings );
		
		
		/* 
			This is needed if we want WordPress to render our settings interface
			for us using -
			do_settings_sections
			
			It sets up different sections and the fields within each section.
		*/
		add_settings_section('infusionsoft_main', '',  
			array($this, 'main_section_text'), 'infusionsoft_settings_page');

	 
		add_settings_field('client_id', 'Infusion Client ID', 
			array($this, 'render_oauth_clientid'), 'infusionsoft_settings_page', 'infusionsoft_main');	
		
		add_settings_field('client_secret', 'Infusion Client Secret', 
			array($this, 'render_oauth_clientsecret'), 'infusionsoft_settings_page', 'infusionsoft_main');	
		
		add_settings_field('redirect_url', 'Redirect URL', 
			array($this, 'render_oauth_redirecturl'), 'infusionsoft_settings_page', 'infusionsoft_main');				

	 
	}

	function admin_menu() {
		if ( ! current_user_can('update_plugins') )
			return;
	
		// Add a new submenu to the standard Settings panel
		$this->pagehook = $page =  add_menu_page(	
			__('Infusionsoft Oauth', 'infusionsoft_oauth'), __('Infusionsoft Oauth', 'infusionsoft_oauth'), 
			'administrator', $this->page_id, array($this,'render') );
		
		// Executed on-load. Add all metaboxes.
		add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

		// Include js, css, or header *only* for our settings page
		add_action("admin_print_scripts-$page", array($this, 'js_includes'));
//		add_action("admin_print_styles-$page", array($this, 'css_includes'));
		add_action("admin_head-$page", array($this, 'admin_head') );
		add_action( 'admin_head', array($this, 'icon_css') );
	}


function icon_css()



		{
?>
<style>
#toplevel_page_infusionsoft_oauth div.wp-menu-image:before { content:"" !important;}
</style>
<?php
		echo '<style type="text/css">
		
			#toplevel_page_infusionsoft_oauth div.wp-menu-image {

			  background:transparent url("'.trailingslashit(get_option('siteurl')).'wp-content/plugins/infusionsoft-oauth/iom-menu.png") no-repeat center -32px !important;

			} 

			#toplevel_page_infusionsoft_oauth:hover div.wp-menu-image, #toplevel_page_infusionsoft_oauth.current div.wp-menu-image, #toplevel_page_infusionsoft_oauth.wp-has-current-submenu div.wp-menu-image {

			  background:transparent url("'.trailingslashit(get_option('siteurl')).'wp-content/plugins/infusionsoft-oauth/iom-menu.png") no-repeat center 0px !important;

			}

			</style>';
		}
		
		
	function admin_head() { ?>
		<style>
		.settings_page_infusionsoft_oauth label { display:inline-block; width: 150px; }
		
		</style>
        <?php
		 
  }

     
	function js_includes() {
		// Needed to allow metabox layout and close functionality.
		wp_enqueue_script( 'postbox' );
	}


	/*
		Sanitize our plugin settings array as needed.
	*/	
	function sanitize_theme_options($options) {
		
		$options['client_id'] = stripcslashes($options['client_id']);
		$options['client_secret'] = stripcslashes($options['client_secret']);
		$options['redirect_url'] = stripcslashes($options['redirect_url']);
		return $options;
	}


	/*
		Settings access functions.
		
	*/
	protected function get_field_name( $name ) {

		return sprintf( '%s[%s]', $this->settings_field, $name );

	}

	protected function get_field_id( $id ) {

		return sprintf( '%s[%s]', $this->settings_field, $id );

	}

	protected function get_field_value( $key ) {

		return $this->options[$key];

	}
		

	/*
		Render settings page.
		
	*/
	
	function render() {
		global $wp_meta_boxes;

		$title = __('Infusionsoft Oauth', 'infusionsoft_oauth');
		?>
		<div class="wrap">   
			<h2><?php echo esc_html( $title ); ?></h2>
		
			<form method="post" action="options.php">
				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>
                
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 99%;">
                    <?php 
						// Render metaboxes
                        settings_fields($this->settings_field); 
                        do_meta_boxes( $this->pagehook, 'main', null );
                      	if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
 							do_meta_boxes( $this->pagehook, 'column2', null );
                    ?>
                    </div>
                </div>

				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>
			</form>
		</div>
        
        <!-- Needed to allow metabox layout and close functionality. -->
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
	<?php }
	
	
	function metaboxes() {

		// Example metabox showing plugin version and release date. 
		// Also includes and example input text box, rendered in HTML in the info_box function
		

		// Example metabox containing two example checkbox controls.
		// Also includes and example input text box, rendered in HTML in the condition_box function
		 

		// Example metabox containing an example text box & two example checkbox controls.
		// Example settings rendered by WordPress using the do_settings_sections function.
		add_meta_box( 	'infusionsoft-oauth-all', 
						__( 'Setup For Infusionsoft oAuth', 'infusionsoft_oauth' ), 
						array( $this, 'do_settings_box' ), $this->pagehook, 'main' );

	}

	 
	function do_settings_box() {
		do_settings_sections('infusionsoft_settings_page'); 
	}
	
	/* 
		WordPress settings rendering functions
		
		ONLY NEEDED if we are using wordpress to render our controls (do_settings_sections)
	*/
																	  
																	  
	function main_section_text() {
		echo '<p>Some example inputs.</p>';
	}
	

		 
	
		function render_oauth_clientid() { 
		?>
        <input id="client_id" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'client_id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'client_id' ) ); ?>" />	
		<?php 
	}
		function render_oauth_clientsecret() { 
		?>
        <input id="client_secret" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'client_secret' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'client_secret' ) ); ?>" />	
		<?php 
	}
	
	function render_oauth_redirecturl() { 
		?>
        <input id="redirect_url" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'redirect_url' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'redirect_url' ) ); ?>" />	
		<?php 
	}
	
	

} // end class
endif;
?>