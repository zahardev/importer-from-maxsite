<?php

namespace Importer_From_Maxsite;


/**
 * Class API
 * @package Importer_From_Maxsite
 */
class API {

	use Singleton;

	const CATEGORIES_ENDPOINT = 'export_api/v1/categories';

	const POSTS_ENDPOINT = 'export_api/v1/pages';

	const COMUSERS_ENDPOINT = 'export_api/v1/comusers';

	/**
	 * @var array
	 */
	private $terms;


	/**
	 * @param $maxsite_url
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function get_terms( $maxsite_url ) {
		if ( empty( $this->terms ) ) {
			$res         = $this->get( $maxsite_url . '/' . self::CATEGORIES_ENDPOINT );
			$this->terms = json_decode( $res, true );
		}

		return $this->terms;
	}

	/**
	 * @param $maxsite_url
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function get_pages( $maxsite_url ) {
		$res = $this->get( $maxsite_url . '/' . self::POSTS_ENDPOINT );

		return json_decode( $res, true );
	}

	/**
	 * @param $src
	 *
	 * @return bool|string
	 */
	public function download_image( $src ) {

		$file_name = basename( $src );
		$file      = wp_upload_dir()['path'] . '/' . $file_name;

		$tmpfile = $this->download_url( $src, 10 );

		if ( ! is_wp_error( $tmpfile ) ) {
			copy( $tmpfile, $file );
			unlink( $tmpfile );
		}

		return file_exists( $file ) ? $file : false;
	}


	/**
	 * This function is the same as WP download_url,
	 * but it uses just wp_remote_get instead of wp_safe_remote_get
	 *
	 *
	 * @param $url
	 * @param int $timeout
	 *
	 * @return string|\WP_Error
	 */
	private function download_url( $url, $timeout = 300 ) {

		if ( ! $url ) {
			return new \WP_Error( 'http_no_url', __( 'Invalid URL Provided.' ) );
		}

		$url_filename = basename( parse_url( $url, PHP_URL_PATH ) );

		$tmpfname = wp_tempnam( $url_filename );
		if ( ! $tmpfname ) {
			return new \WP_Error( 'http_no_file', __( 'Could not create Temporary file.' ) );
		}

		$response = wp_remote_get( $url, [ 'timeout'  => $timeout,
		                                   'stream'   => true,
		                                   'filename' => $tmpfname,
		] );

		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );

			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			unlink( $tmpfname );

			return new \WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
		if ( $content_md5 ) {
			$md5_check = verify_file_md5( $tmpfname, $content_md5 );
			if ( is_wp_error( $md5_check ) ) {
				unlink( $tmpfname );

				return $md5_check;
			}
		}

		return $tmpfname;
	}

	/**
	 * @param $maxsite_url
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_comusers( $maxsite_url ) {
		$res = $this->get( $maxsite_url . '/' . self::COMUSERS_ENDPOINT );

		return json_decode( $res, true );
	}

	/**
	 * @param $url
	 *
	 * @return mixed|string
	 * @throws \Exception
	 */
	private function get( $url ) {
		$response  = wp_remote_get( $url );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 != $http_code ) {
			throw new \Exception( sprintf( __( 'Url %s can not be reached', IFM_TEXT_DOMAIN ), $url ) );
		}

		return wp_remote_retrieve_body( $response );
	}
}
