<?php
/**
 * Gutenberg archive block.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the dynamic archive block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function asoprint_render_archive_block( $attributes ) {
	return asoprint_render_archive(
		array(
			'title'            => sanitize_text_field( $attributes['title'] ?? __( 'Basılı Yayın Arşivi', 'asosyoloji-print-archive' ) ),
			'count'            => absint( $attributes['count'] ?? 12 ),
			'layout'           => sanitize_key( $attributes['layout'] ?? 'grid' ),
			'order'            => sanitize_key( strtoupper( (string) ( $attributes['order'] ?? 'DESC' ) ) ),
			'show_description' => ! empty( $attributes['showDescription'] ),
		)
	);
}

/**
 * Register the block and its editor assets.
 */
function asoprint_register_archive_block() {
	wp_register_script(
		'asoprint-archive-block-editor',
		ASOPRINT_URL . 'assets/block-editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render' ),
		ASOPRINT_VERSION,
		true
	);

	register_block_type(
		'asosyoloji/print-archive',
		array(
			'api_version'     => 3,
			'editor_script'   => 'asoprint-archive-block-editor',
			'render_callback' => 'asoprint_render_archive_block',
			'attributes'      => array(
				'title'           => array(
					'type'    => 'string',
					'default' => 'Basılı Yayın Arşivi',
				),
				'count'           => array(
					'type'    => 'number',
					'default' => 12,
				),
				'layout'          => array(
					'type'    => 'string',
					'default' => 'grid',
				),
				'order'           => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'showDescription' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}
add_action( 'init', 'asoprint_register_archive_block' );
