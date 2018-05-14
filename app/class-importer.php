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
	 * @var int
	 */
	private $fields_counter = 0;

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
			'import_maxsite_content',
		] );
	}

	/**
	 * Function import_maxsite_content
	 */
	public function import_maxsite_content() {
		try {
			set_time_limit( 0 );
			ini_set( 'display_errors', 0 );
			ini_set( 'display_startup_errors', 0 );
			$maxsite_url = filter_input( INPUT_GET, 'maxsite_url', FILTER_VALIDATE_URL );
			if ( ! $maxsite_url ) {
				throw new \Exception( __( 'Wrong url!', IFM_TEXT_DOMAIN ) );
			}
			$maxsite_url = rtrim( $maxsite_url, '/' );
			$this->import_terms( $maxsite_url );
			$this->import_posts( $maxsite_url );

			$res = __( 'All data has been imported successfully!', IFM_TEXT_DOMAIN );

			$res .= '<br><br><br>' . __( 'Errors:', IFM_TEXT_DOMAIN ) . ' ' . count( $this->errors ) . '<br>';

			if ( count( $this->errors ) ) {
				$res .= '<ul>';
				foreach ( $this->errors as $error ) {
					$res .= '<li>' . $error . '</li>';
				}
				$res .= '</ul><br><br>';
			}

			$res .= sprintf(
				__( 'Imported %d categories, %d pages, %d fields and %d images', IFM_TEXT_DOMAIN ),
				$this->terms_counter,
				$this->posts_counter,
				$this->fields_counter,
				$this->images_counter
			);

			wp_send_json_success( $res );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * @param $maxsite_url
	 *
	 * @throws \Exception
	 */
	private function import_posts( $maxsite_url ) {
		$posts       = $this->get_posts( $maxsite_url );
		$acf_enabled = function_exists( "register_field_group" );
		if ( $acf_enabled ) {
			$fields_map = $this->get_fields_map( $posts );
			$this->register_fields( $fields_map );
		} else {
			$this->errors[] = __( "Could not find Advanced Custom Fields plugin, skipped importing meta fields", IFM_TEXT_DOMAIN );
		}

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
			$post_id = wp_insert_post( [
				'post_content'  => $content,
				'post_title'    => $post['page_title'],
				'post_status'   => $post['page_status'],
				'post_type'     => 'post',
				'post_password' => $post['page_password'],
				'guid'          => $post['page_slug'],
				'post_date'     => $post['page_date_publish'],
				'post_category' => $post_category,
			] );

			if ( is_wp_error( $post_id ) ) {
				$this->errors[] = __( 'Could not import page', IFM_TEXT_DOMAIN ) . ' ' .
				                  $post['page_id'] . ": ( {$post['page_title']} ) ";
			} else {
				$this->posts_counter ++;
				if ( $acf_enabled ) {
					$this->import_post_fields( $post_id, $post, $fields_map, $maxsite_url );
				}
			}
		}
	}


	/**
	 * @param $post_id
	 * @param $post
	 * @param $fields_map
	 * @param $maxsite_url
	 */
	private function import_post_fields( $post_id, $post, $fields_map, $maxsite_url ) {
		foreach ( $post['page_meta'] as $field_name => $field ) {
			$field_value = $field[0];
			if ( $fields_map[ $field_name ]['is_image'] ) {
				$res = $this->import_image( $field_value, $maxsite_url );
				add_post_meta( $post_id, $field_name, $res['attachment_id'] );
			} else {
				add_post_meta( $post_id, $field_name, $field_value );
			}
			$this->fields_counter ++;
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
		$content = str_replace( "'", '"', $content );
		$images  = [];
		preg_match_all( '@src="([^"]+)"@', $content, $images );

		$srcs = $images[1];

		$images_map = [];

		if ( count( $srcs ) ) {
			$upload_url = str_replace( site_url(), '', wp_upload_dir()['baseurl'] );
			foreach ( $srcs as $src ) {
				$res                = $this->import_image( $src, $maxsite_url );
				$images_map[ $src ] = $upload_url . '/' . $res['attachment_data']['file'];
			}
		}

		return $images_map;
	}

	/**
	 * @param $src
	 * @param $maxsite_url
	 *
	 * @return array|false
	 */
	private function import_image( $src, $maxsite_url ) {
		$image_extensions = wp_get_ext_types()['image'];
		$extension        = wp_check_filetype( $src )['ext'];
		if ( ! in_array( $extension, $image_extensions ) ) {
			return false; //download only images
		}
		$src_url = ( false === strpos( $src, 'http' ) ) ? $maxsite_url . $src : $src;
		if ( $file = $this->download_image( $src_url ) ) {
			$this->images_counter ++;

			return $this->insert_attachment( $file );
		} else {
			$this->errors[] = __( 'Could not download image' ) . ' ' . $src;
		}
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
			'post_status'    => 'inherit',
		];

		$attachment_id = wp_insert_attachment( $attachment, $file );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		return [
			'attachment_id'   => $attachment_id,
			'attachment_data' => $attachment_data,
		];
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
		$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		fclose( $fp );

		if ( 200 != $httpcode ) {
			unlink( $file );
		}

		return ( 200 == $httpcode ) && file_exists( $file ) ? $file : false;
	}

	/**
	 * @param $maxsite_url
	 *
	 * @throws \Exception
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
	 * @param $fields_map
	 */
	private function register_fields( $fields_map ) {
		$group_id = $this->register_field_group();
		$fields   = $this->get_fields( $fields_map );
		foreach ( $fields as $field_name => $field_settings ) {
			add_post_meta( $group_id, $field_name, $field_settings );
		}
	}

	/**
	 * @param $fields_map
	 *
	 * @return array
	 */
	private function get_fields( $fields_map ) {
		$fields = [];

		// Now we can generate acf fields for them
		$i = 0;
		foreach ( $fields_map as $field_name => $field ) {
			$key = 'field_' . wp_generate_password( 13, false );

			$common_settings = [
				'key'               => $key,
				'label'             => $field_name,
				'name'              => $field_name,
				'instructions'      => '',
				'required'          => '0',
				'conditional_logic' => [
					'status'   => '0',
					'rules'    => [
						0 => [
							'field'    => 'null',
							'operator' => '==',
						],
					],
					'allorany' => 'all',
				],
				'order_no'          => $i ++,
			];

			if ( $field['is_image'] ) {
				$settings = [
					'type'         => 'image',
					'save_format'  => 'object',
					'preview_size' => 'thumbnail',
					'library'      => 'all',
				];
			} else {
				$settings = [
					'type'          => 'text',
					'default_value' => '',
					'placeholder'   => '',
					'prepend'       => '',
					'append'        => '',
					'formatting'    => 'html',
					'maxlength'     => '',
				];
			}

			$fields[ $key ] = array_merge( $common_settings, $settings );
		}

		return $fields;
	}

	/**
	 * @param $posts
	 *
	 * @return array
	 */
	private function get_fields_map( $posts ) {
		$image_extensions = wp_get_ext_types()['image'];

		$fields_map = [];

		foreach ( $posts as $post ) {
			foreach ( $post['page_meta'] as $meta_name => $meta_value ) {
				if ( isset( $fields_map[ $meta_name ] ) && $fields_map[ $meta_name ]['is_image'] ) {
					// we need to check all values for image value - some of them may be empty,
					// but if we already know it is image field, we can skip it
					continue;
				}
				$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';
				$is_image   = false;

				if ( $meta_value ) {
					$pathinfo  = pathinfo( $meta_value );
					$extension = isset( $pathinfo['extension'] ) ? $pathinfo['extension'] : '';
					if ( in_array( $extension, $image_extensions ) ) {
						$is_image = true;
					}
				}

				$fields_map[ $meta_name ] = [ 'is_image' => $is_image ];
			}
		}

		return $fields_map;
	}

	/**
	 * Create post with post type acf to store field settings
	 * */
	private function register_field_group() {
		$group_id = wp_insert_post( [
			'post_content'  => '',
			'post_title'    => __( 'Post Fields', IFM_TEXT_DOMAIN ),
			'post_status'   => 'publish',
			'post_type'     => 'acf',
			'post_password' => '',
		] );

		if ( is_wp_error( $group_id ) ) {
			return false;
		}

		$rule = [
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'post',
			'order_no' => 0,
			'group_no' => 0,
		];
		add_post_meta( $group_id, 'rule', $rule );
		add_post_meta( $group_id, 'position', 'normal' );
		add_post_meta( $group_id, 'layout', 'no_box' );
		add_post_meta( $group_id, 'hide_on_screen', '' );

		return $group_id;
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
			$this->errors[] = __( 'Could not import category', IFM_TEXT_DOMAIN ) . ' ' . $current_term['category_name'];
		} else {
			$this->terms_counter ++;
			$wp_term_id                                              = $res['term_id'];
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
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @throws \Exception
	 */
	private function get( $url ) {
		$curl = curl_init();

		curl_setopt_array( $curl, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_HTTPHEADER     => [
				"cache-control: no-cache",
			],
		] );

		$response = curl_exec( $curl );
		$httpcode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		if ( 200 != $httpcode ) {
			throw new \Exception( sprintf( __( 'Url %s can not be reached', IFM_TEXT_DOMAIN ), $url ) );
		}

		return $response;
	}
}
