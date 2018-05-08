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
	 * @var int
	 */
	private $images_counter = 0;

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
		set_time_limit(0);
		$maxsite_url = filter_input( INPUT_GET, 'maxsite_url', FILTER_VALIDATE_URL );
		if ( ! $maxsite_url ) {
			wp_send_json_error( __( 'Wrong url!' ) );
		}
		$maxsite_url = rtrim($maxsite_url, '/');
		$this->import_terms( $maxsite_url );
		$this->import_posts( $maxsite_url );

		$res = __( 'All data has been imported successfully!' );

		$res .= '<br><br><br>' . __( 'Errors:' ) . ' ' . count( $this->errors ) . '<br>';

		if(count($this->errors)){
			$res .= '<ul>';
			foreach ( $this->errors as $error ) {
				$res .= '<li>' . $error . '</li>';
			}
			$res .= '</ul><br><br>';
		}

		$res .= sprintf(
			__( 'Imported %d categories, %d pages and %d images' ),
			$this->terms_counter,
			$this->posts_counter,
			$this->images_counter
		);

		wp_send_json_success( $res );
	}

	/**
	 * @param $maxsite_url
	 */
	private function import_posts( $maxsite_url ) {
		$posts = $this->get_posts( $maxsite_url );
		foreach ( $posts as $post ) {
			$images_map = $this->import_post_images( $post, $maxsite_url );
			$content    = $post['page_content'];
			$content    = str_replace( '[cut]', '<!--more-->', $content );
			foreach ( $images_map as $from => $to ) {
				$content = str_replace( $from, $to, $content );
			}
			$post_category = [];
			foreach ( $post['page_categories'] as $page_category ) {
				$post_category[] = $this->category_term_map[ $page_category ];
			}
			$res = wp_insert_post( [
				'post_content'  => $content,
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
	 * @param $post
	 * @param $maxsite_url
	 *
	 * @return array
	 */
	private function import_post_images( $post, $maxsite_url ) {
		$content = $post['page_content'];
		$content = str_replace("'", '"', $content);
		$images = array();
		preg_match_all( '@src="([^"]+)"@' , $content, $images );

		$srcs = $images[1];

		$images_map = [];

		if ( count( $srcs ) ) {
			$image_extensions = wp_get_ext_types()['image'];
			$upload_url = str_replace( site_url(), '', wp_upload_dir()['baseurl'] );
			foreach ( $srcs as $src ) {
				$extension = wp_check_filetype( $src )['ext'];
				if ( ! in_array( $extension, $image_extensions ) ) {
					continue; //download only images
				}
				$src_url = ( false === strpos( $src, 'http' ) ) ? $maxsite_url . $src : $src;
				if ( $file = $this->download_image( $src_url ) ) {
					$this->images_counter++;
					$attach_data = $this->insert_attachment( $file );
					$images_map[$src] = $upload_url . '/' . $attach_data['file'];
				} else {
					$this->errors[] = __( 'Could not download image' ) . ' ' . $src;
				}
			}
		}
		return $images_map;
	}

	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	private function insert_attachment( $file ) {
		$filename = basename( $file );

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = [
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		];

		$attach_id = wp_insert_attachment( $attachment, $file );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		return $attach_data;
	}


	/**
	 * @param $src
	 *
	 * @return bool|string
	 */
	private function download_image( $src ) {
		$ch        = curl_init( $src );
		$file_name = basename( $src );
		$file      = wp_upload_dir()['path'] . '/' . $file_name;
		$fp        = fopen( $file, 'wb' );
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_exec( $ch );
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close( $ch );
		fclose( $fp );

		if ( 200 != $httpcode ) {
			unlink( $file );
		}

		return ( 200 == $httpcode ) && file_exists( $file ) ? $file : false;
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
