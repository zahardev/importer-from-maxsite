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
			$url         = $maxsite_url . '/' . self::CATEGORIES_ENDPOINT;
			$res         = $this->get( $url );
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
		$url = $maxsite_url . '/' . self::POSTS_ENDPOINT;
		$res = $this->get( $url );
		$res = json_decode( $res, true );

		return $res;
	}

	/**
	 * @param $src
	 *
	 * @return bool|string
	 */
	public function download_image( $src ) {
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
