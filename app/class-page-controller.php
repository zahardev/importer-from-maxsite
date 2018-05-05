<?php

namespace Importer_From_Maxsite;


/**
 * Class Page_Controller
 * @package Importer_From_Maxsite
 */
class Page_Controller {
	/**
	 * @var
	 */
	private static $instance;

	const IMPORTER_URL = 'importer-from-maxsite';


	/**
	 * API constructor.
	 */
	private function __construct() {
	}

	/**
	 * @return Page_Controller
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function init() {
		if ( ! is_admin() ) {
			return;
		}
		add_filter( 'plugin_action_links_' . IFM_PLUGIN_BASENAME, function ( $links ) {
			$mylinks = [
				'<a href="' . admin_url( 'admin.php?page=' . self::IMPORTER_URL )
				. '">Importer</a>',
			];

			return array_merge( $links, $mylinks );
		} );
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		$this->enqueue_assets();
	}


	public function enqueue_assets() {
		wp_enqueue_script(
			self::IMPORTER_URL . '-js',
			IFM_PLUGIN_URL . '/assets/js/importer.js',
			[ 'jquery' ],
			IFM_ASSETS_VERSION
		);
	}


	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		$title = __( 'Importer From MaxSite', IFM_TEXT_DOMAIN );
		add_menu_page(
			$title,
			$title,
			'manage_options',
			self::IMPORTER_URL,
			array( $this, 'render_plugin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function render_plugin_page() {
		include IFM_PLUGIN_DIR . '/templates/importer.php';
	}
}
