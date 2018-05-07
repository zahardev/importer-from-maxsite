<?php

namespace Importer_From_Maxsite;


/**
 * Class Importer
 * @package Importer_From_Maxsite
 */
/**
 * Class Importer
 * @package MCI
 */
class Importer {
	/**
	 * @var Importer
	 */
	private static $instance;

	/**
	 *
	 */
	const CATEGORIES_ENDPOINT = 'export_api/v1/categories';


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

	/**
	 *
	 */
	public function init() {
		add_action( 'wp_ajax_import_maxsite_content', [
			$this,
			'import_maxsite_content'
		] );
	}

	/**
	 * Function import_maxsite_content
	 */
	public function import_maxsite_content() {
		$maxsite_url = filter_input( INPUT_GET, 'maxsite_url' );
		$terms       = $this->get_terms( $maxsite_url );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$this->create_hierarchical_terms( $term );
			}
		}
		wp_send_json_success( __( 'All data have been imported successfully!' ) );
	}

	/**
	 * @param $current_term
	 * @param int $parent_term_id
	 */
	private function create_hierarchical_terms( $current_term, $parent_term_id = 0 ) {
		$args = [
			'description' => $current_term['category_desc'],
			'slug'        => $current_term['category_slug'],
		];

		if ( $parent_term_id ) {
			$args['parent'] = $parent_term_id;
		}
		$res = wp_insert_term(
			$current_term['category_name'],
			'category',
			$args
		);

		if ( ! is_wp_error( $res ) && isset( $current_term['childs'] ) ) {
			foreach ( $current_term['childs'] as $child ) {
				$this->create_hierarchical_terms( $child, $res['term_id'] );
			}
		}
	}

	/**
	 * @param $maxsite_url
	 *
	 * @return array|mixed|object|string
	 */
	private function get_terms( $maxsite_url ) {
		$url = $maxsite_url . '/' . self::CATEGORIES_ENDPOINT;
		$res = $this->get( $url );
		$res = json_decode( $res, true );

		return $res;
	}

	/**
	 * @param $url
	 *
	 * @return mixed|string
	 */
	private function get( $url ) {
		try {
			$curl = curl_init();

			curl_setopt_array( $curl, array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "GET",
				CURLOPT_HTTPHEADER     => array(
					"cache-control: no-cache"
				),
			) );

			$response = curl_exec( $curl );
			//$err = curl_error($curl);

			curl_close( $curl );

			return $response;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		} catch ( \Throwable $e ) {
			return $e->getMessage();
		}
	}
}
