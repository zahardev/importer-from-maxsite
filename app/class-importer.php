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
	 * @var array
	 */
	private $terms;

	/**
	 * @var array
	 */
	private $category_term_map;

	/**
	 * @var int
	 */
	private $terms_counter = 0;

	/**
	 * @var int
	 */
	private $posts_counter = 0;

	/**
	 * @var array
	 */
	private $errors = [];

	const CATEGORIES_ENDPOINT = 'export_api/v1/categories';

	const POSTS_ENDPOINT = 'export_api/v1/pages';


	/**
	 * Importer constructor.
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
	 * Function init
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
		$this->import_terms( $maxsite_url );
		$this->import_posts( $maxsite_url );

		if(count($this->errors)){
			$res = __( 'Data imported with errors!' ) . '</br>';
			$res .= __('Errors:') . '</br>';
			foreach ( $this->errors as $error ) {
				$res .= $error . '</br>';
			}
		} else {
			$res = __( 'Data imported successfully!' );
		}

		$res .= sprintf( __( 'Imported %d categories and %d pages' ), $this->terms_counter, $this->posts_counter );

		wp_send_json_success( $res );
	}

	/**
	 * @param $maxsite_url
	 */
	private function import_posts( $maxsite_url ) {
		$posts = $this->get_posts( $maxsite_url );
		foreach ( $posts as $post ) {
			$post_category = [];
			foreach ( $post['page_categories'] as $page_category ) {
				$post_category[] = $this->category_term_map[ $page_category ];
			}
			$res = wp_insert_post( [
				'post_content'  => str_replace( '[cut]', '<!--more-->', $post['page_content'] ),
				'post_title'    => $post['page_title'],
				'post_status'   => $post['page_status'],
				'post_type'     => 'post',
				'post_password' => $post['page_password'],
				'guid'          => $post['page_slug'],
				'post_date'     => $post['page_date_publish'],
				'post_category' => $post_category,
			] );

			if ( is_wp_error( $res ) ) {
				$this->errors[] = __( 'Could not import page' ) . ' ' .
				                  $post['page_id'] . ": ( {$post['page_title']} ) ";
			} else {
				$this->posts_counter ++;
			}
		}
	}

	/**
	 * @param $maxsite_url
	 */
	private function import_terms( $maxsite_url ) {
		$terms = $this->get_terms( $maxsite_url );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$this->create_hierarchical_terms( $term );
			}
		}
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

		if ( is_wp_error( $res ) ) {
			$this->errors[] = __('Could not import category') . ' ' . $current_term['category_name'];
		} else {
			$this->terms_counter++;
			$wp_term_id                                                = $res['term_id'];
			$this->category_term_map[ $current_term['category_id'] ] = $wp_term_id;
			if ( isset( $current_term['childs'] ) ) {
				foreach ( $current_term['childs'] as $child ) {
					$this->create_hierarchical_terms( $child, $wp_term_id );
				}
			}
		}
	}

	/**
	 * @param $maxsite_url
	 *
	 * @return array|mixed|object|string
	 */
	private function get_terms( $maxsite_url ) {
		if ( empty( $this->terms ) ) {
			$url         = $maxsite_url . '/' . self::CATEGORIES_ENDPOINT;
			$res         = $this->get( $url );
			$this->terms = json_decode( $res, true );
		}

		return $this->terms;
	}

	/**
	 * @param $maxsite_url
	 *
	 * @return array|mixed|object|string
	 */
	private function get_posts( $maxsite_url ) {
		$url = $maxsite_url . '/' . self::POSTS_ENDPOINT;
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

			curl_close( $curl );

			return $response;
		} catch ( \Exception $e ) {
			$this->errors[] = $e->getMessage();
		} catch ( \Throwable $e ) {
			$this->errors[] = $e->getMessage();
		}
		return false;
	}
}
