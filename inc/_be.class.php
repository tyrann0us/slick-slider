<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Main class for all backend methods.
 */
class slick {

	/**
	 * Hooks all required Slick Slider actions and filters.
	 */
	public static function init() {

		add_action( 'init', array(
			 __CLASS__,
			'loadTextdomain' 
		) );
		add_action( 'admin_notices', array(
			 'slickFeedback',
			'rules' 
		) );
		add_action( 'network_admin_notices', array(
			 'slickFeedback',
			'network' 
		) );
		add_action( 'admin_notices', array(
			 'slickFeedback',
			'admin' 
		) );

		switch ( self::currentPage() ) {
			case 'post' :
			case 'post-new' :
				add_action( 'admin_init', array(
					'slickTemplate',
					'initTemplate'
				) );
				break;
			case 'options-media' :
				add_action( 'admin_init', array(
					'slickGui',
					'initSettings' 
				) );
				break;
			case 'options' :
				add_action( 'update_option',  array(
					 'slickGui',
					'saveChanges' 
				) );
				break;
			case 'plugins' :
				add_filter( 'plugin_action_links_' . SLICK_BASE, array(
					 __CLASS__,
					'addSettingsLinks' 
				) );
				add_filter( 'plugin_row_meta', array(
					 __CLASS__,
					'addThanksLink' 
				), 10, 2 );
				break;
			default:
				break;
		}

	}

	/**
	 * Initiate adding of all Slick Slider options.
	 */
	public static function install() {

		slickOptions::init();

	}

	/**
	 * Initiate removal of all Slick Slider options.
	 */
	public static function uninstall() {

		slickOptions::destroy();

	}

	/**
	 * Get URL to asset using plugins_url().
	 * @param  string $path URI to asset
	 * @return string        full URL to asset			
	 */
	public static function pluginUrl( $path ) {

		return plugins_url( $path, SLICK_FILE );

	}

	/**
	 * Gets current backend page.
	 * @return string        slug of curerent page (e. g. 'options-media').
	 */
	public static function currentPage() {

		return ( empty( $GLOBALS['pagenow'] ) ? 'index' : basename( $GLOBALS['pagenow'], '.php' ) );

	}

	/**
	 * Adds link to Slick Slider settings on plugin page.
	 * @param array $data current plugin action links
	 * @return array        merged array without link to plugin-editor and with link to Slick Slider settings
	 */
	public static function addSettingsLinks( $data ) {

		$output = array_filter( $data, function( $value ) { return ! strpos( $value, 'plugin-editor.php' ); } );
		if ( current_user_can( 'manage_options' ) ) {
			$output = array_merge( $output, array(
				 sprintf( '<a href="%s">%s</a>', admin_url( 'options-media.php#slick-settings' ), __( 'Settings' ) ) 
			) );
		}
		return $output;

	}

	/**
	 * Add link to PayPal donation page and WordPress rating page.
	 * @param array $data current plugin row links
	 * @param string $page plugin basename
	 * @return array $data merged array with links (see description)
	 */
	public static function addThanksLink( $data, $page ) {

		if ( SLICK_BASE != $page ) {
			return $data;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return array_merge( $data, array(
				 sprintf(
				 	'<a href="%s" target="blank">%s</a>',
				 	'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4347QQ8J3L54',
				 	__( 'Donate', 'slick-slider' )
				 ),
				 sprintf(
				 	'<a href="%s" target="blank">%s</a>',
				 	'https://wordpress.org/support/view/plugin-reviews/slick-slider#postform',
				 	__( 'Rate', 'slick-slider' )
				 )
			) );
		}
		return $data;

	}

	/**
	 * Check WordPress version.
	 * @param  string  $version WordPress version to check against.
	 * @return boolean          true if WordPress is at least of version $version, false otherwise
	 */
	public static function isMinWp( $version ) {

		return version_compare( $GLOBALS[ 'wp_version' ], $version . 'alpha', '>=' );

	}

	/**
	 * Check PHP version.
	 * @param  string  $version PHP version to check against.
	 * @return boolean          true if PHP is at least of version $version, false otherwise
	 */
	public static function isMinPhp( $version ) {

		return version_compare( phpversion(), $version, '>=' );

	}

	/**
	 * Loads plugin textdomain for internalization using load_plugin_textdomain().
	 */
	public static function loadTextdomain() {

		load_plugin_textdomain( 'slick-slider', false, dirname( SLICK_BASE ) . '/lang' );

	}

	/**
	 * Check nonce using check_admin_referer(). Currently not used.
	 * @param string $none name of nonce to check
	 */
	//public static function check_security( $nonce = '_slick_nonce' ) {
	//
	//	if ( ! current_user_can( 'manage_options' ) ) {
	//		wp_die( __( 'Cheatin&#8217; uh?' ) );
	//	}
	//	check_admin_referer( $nonce );
	//
	//}

	/**
	 * Wrapper for WordPress function of the same name. Gets plugin metadata value.
	 * @param  string $field meta field value to get
	 * @return string|array        single value if $field is set, array of all values otherwise
	 */
	public static function getPluginData( $field = NULL )	{

		if ( ! $plugin_data = slickCache::get( 'plugin_data' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( SLICK_FILE );
			slickCache::set( 'plugin_data', $plugin_data );
		}
		if ( ! empty( $field ) && isset( $plugin_data[$field] ) ) {
			return $plugin_data[$field];
		}
		return $plugin_data;

	}

}