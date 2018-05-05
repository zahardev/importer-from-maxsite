<?php

namespace Importer_From_Maxsite;


/**
 * Class Importer
 * @package Importer_From_Maxsite
 */
class Importer {
	/**
	 * @var Importer
	 */
	private static $instance;


	/**
	 * API constructor.
	 */
	private function __construct() {
	}

	/**
	 * @return Importer
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function init() {
		add_action( 'wp_ajax_import_maxsite_content', [
			$this,
			'import_maxsite_content'
		] );
	}

	public function import_maxsite_content() {
		$maxsite_url = filter_input( INPUT_GET, 'maxsite_url' );
		wp_send_json_success();
	}
}
