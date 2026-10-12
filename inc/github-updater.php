<?php
/**
 * GitHub release updater.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASOPRINT_GITHUB_REPOSITORY', 'm4v3r4/asosyoloji-print-archive' );
define( 'ASOPRINT_RELEASE_ASSET', 'asosyoloji-print-archive.zip' );

/**
 * Fetch the latest public GitHub release.
 *
 * @param bool $force Ignore the cached response.
 * @return array|WP_Error
 */
function asoprint_latest_release( $force = false ) {
	$cache_key = 'asoprint_github_release';

	if ( ! $force ) {
		$cached = get_site_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . ASOPRINT_GITHUB_REPOSITORY . '/releases/latest',
		array(
			'headers' => array(
				'Accept'               => 'application/vnd.github+json',
				'X-GitHub-Api-Version' => '2022-11-28',
				'User-Agent'           => 'Asosyoloji-Print-Archive/' . ASOPRINT_VERSION,
			),
			'timeout' => 8,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'asoprint_release_http', __( 'GitHub sürüm bilgisi alınamadı.', 'asosyoloji-print-archive' ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
		return new WP_Error( 'asoprint_release_invalid', __( 'GitHub sürüm yanıtı geçersiz.', 'asosyoloji-print-archive' ) );
	}

	$download_url = '';
	foreach ( $data['assets'] ?? array() as $asset ) {
		if ( ASOPRINT_RELEASE_ASSET === ( $asset['name'] ?? '' ) ) {
			$download_url = esc_url_raw( $asset['browser_download_url'] ?? '' );
			break;
		}
	}

	$release = array(
		'version'      => ltrim( (string) $data['tag_name'], 'vV' ),
		'html_url'     => esc_url_raw( $data['html_url'] ?? '' ),
		'download_url' => $download_url,
		'body'         => wp_kses_post( $data['body'] ?? '' ),
	);

	set_site_transient( $cache_key, $release, HOUR_IN_SECONDS );

	return $release;
}

/**
 * Add a newer GitHub release to the native plugin update screen.
 *
 * @param stdClass $transient Plugin update data.
 * @return stdClass
 */
function asoprint_plugin_update( $transient ) {
	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}

	$release = asoprint_latest_release();
	if (
		is_wp_error( $release ) ||
		empty( $release['version'] ) ||
		empty( $release['download_url'] ) ||
		version_compare( $release['version'], ASOPRINT_VERSION, '<=' )
	) {
		return $transient;
	}

	if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
		$transient->response = array();
	}

	$plugin_file = plugin_basename( ASOPRINT_FILE );
	$transient->response[ $plugin_file ] = (object) array(
		'id'           => 'github.com/' . ASOPRINT_GITHUB_REPOSITORY,
		'slug'         => 'asosyoloji-print-archive',
		'plugin'       => $plugin_file,
		'new_version'  => $release['version'],
		'url'          => $release['html_url'],
		'package'      => $release['download_url'],
		'requires'     => '6.0',
		'requires_php' => '8.0',
	);

	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'asoprint_plugin_update' );

/**
 * Supply the plugin details modal for GitHub releases.
 *
 * @param mixed  $result Existing result.
 * @param string $action Requested API action.
 * @param object $args   Request arguments.
 * @return mixed
 */
function asoprint_plugin_information( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || 'asosyoloji-print-archive' !== $args->slug ) {
		return $result;
	}

	$release = asoprint_latest_release();
	if ( is_wp_error( $release ) ) {
		return $result;
	}

	return (object) array(
		'name'          => 'Asosyoloji Basılı Yayın Arşivi',
		'slug'          => 'asosyoloji-print-archive',
		'version'       => $release['version'],
		'author'        => 'Asosyoloji',
		'homepage'      => $release['html_url'],
		'requires'      => '6.0',
		'requires_php'  => '8.0',
		'download_link' => $release['download_url'],
		'sections'      => array(
			'description' => __( 'Basılı dergi sayılarını kapak, sayı, tarih ve PDF belgesiyle yönetir; widget ve kısa kodla yayınlar.', 'asosyoloji-print-archive' ),
			'changelog'   => wpautop( wp_kses_post( $release['body'] ) ),
		),
	);
}
add_filter( 'plugins_api', 'asoprint_plugin_information', 20, 3 );

/**
 * Clear cached release data during a manual update check.
 */
function asoprint_clear_release_cache() {
	delete_site_transient( 'asoprint_github_release' );
}
add_action( 'wp_update_plugins', 'asoprint_clear_release_cache' );
