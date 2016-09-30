<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Main class for all backend methods.
 *
 * @since 0.1
 */
class slick {

	/**
	 * Hook all Slick Slider actions and filters.
	 *
	 * @since 0.1
	 */
	public static function init() {

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
	 * Initiate setting of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function install() {

		slickOptions::init();

	}

	/**
	 * Initiate removing of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function uninstall() {

		slickOptions::destroy();

	}

	/**
	 * Get URL relative to given path.
	 *
	 * @since 0.1
	 * 
	 * @param string $path URI to asset.
	 * @return string      Full URL to asset.
	 */
	public static function pluginUrl( $path ) {

		return plugins_url( $path, SLICK_FILE );

	}

	/**
	 * Get current backend page.
	 *
	 * @since 0.1
	 * 
	 * @return string Slug of curerent page (e. g. 'options-media').
	 */
	public static function currentPage() {

		return ( empty( $GLOBALS['pagenow'] ) ? 'index' : basename( $GLOBALS['pagenow'], '.php' ) );

	}

	/**
	 * Add link to Slick Slider settings on plugin page.
	 * 
	 * @since 0.1
	 * 
	 * @param array $data Current plugin action link.
	 * @return array      Merged array without link to plugin-editor and with link to Slick Slider settings.
	 */
	public static function addSettingsLinks( $data ) {

		$output = array_filter( $data, function( $value ) { return ! strpos( $value, 'plugin-editor.php' ); } );
		if ( current_user_can( 'manage_options' ) ) {
			$output = array_merge( $output, array(
				 sprintf( '<a href="%s">%s</a>', admin_url( 'options-media.php#slick-slider-settings' ), __( 'Settings', 'slick-slider' ) ) 
			) );
		}
		return $output;

	}

	/**
	 * Add link to PayPal donation page and w.org rating page.
	 *
	 * @since 0.1
	 * 
	 * @param array $data  Current plugin row links.
	 * @param string $page Plugin basename.
	 * @return array $data Merged array with links (see description).
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
	 *
	 * @since 0.1
	 * 
	 * @param string $version WordPress version to check against.
	 * @return boolean        True if WordPress is at least of version $version, false otherwise.
	 */
	public static function isMinWp( $version ) {

		return version_compare( $GLOBALS[ 'wp_version' ], $version . 'alpha', '>=' );

	}

	/**
	 * Check PHP version.
	 *
	 * @since 0.1
	 * 
	 * @param string $version PHP version to check against.
	 * @return boolean        True if PHP is at least of version $version, false otherwise.
	 */
	public static function isMinPhp( $version ) {

		return version_compare( phpversion(), $version, '>=' );

	}

	/**
	 * Check nonce.
	 * Note: Currently not used.
	 *
	 * @since 0.1
	 * 
	 * @param string $nonce Name of nonce to check.
	 */
	public static function check_security( $nonce = '_slick_nonce' ) {
	
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		check_admin_referer( $nonce );
	
	}

	/**
	 * Get plugin metadata value.
	 *
	 * @since 0.1
	 * 
	 * @param string $field Meta field value to get.
	 * @return string|array Single value if $field is set, array of all values otherwise.
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