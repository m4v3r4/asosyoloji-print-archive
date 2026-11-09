<?php
/**
 * Printed issue post type and admin list.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register printed issues and their REST-visible metadata.
 */
function asoprint_register_post_type() {
	$labels = array(
		'name'                  => __( 'Basılı Yayınlar', 'asosyoloji-print-archive' ),
		'singular_name'         => __( 'Basılı Yayın', 'asosyoloji-print-archive' ),
		'menu_name'             => __( 'Basılı Yayınlar', 'asosyoloji-print-archive' ),
		'add_new'               => __( 'Yeni yayın ekle', 'asosyoloji-print-archive' ),
		'add_new_item'          => __( 'Yeni basılı yayın ekle', 'asosyoloji-print-archive' ),
		'edit_item'             => __( 'Basılı yayını düzenle', 'asosyoloji-print-archive' ),
		'new_item'              => __( 'Yeni basılı yayın', 'asosyoloji-print-archive' ),
		'view_item'             => __( 'Basılı yayını görüntüle', 'asosyoloji-print-archive' ),
		'view_items'            => __( 'Basılı yayınları görüntüle', 'asosyoloji-print-archive' ),
		'search_items'          => __( 'Basılı yayınlarda ara', 'asosyoloji-print-archive' ),
		'not_found'             => __( 'Basılı yayın bulunamadı.', 'asosyoloji-print-archive' ),
		'not_found_in_trash'    => __( 'Çöpte basılı yayın bulunamadı.', 'asosyoloji-print-archive' ),
		'all_items'             => __( 'Tüm basılı yayınlar', 'asosyoloji-print-archive' ),
		'archives'              => __( 'Basılı yayın arşivi', 'asosyoloji-print-archive' ),
		'featured_image'        => __( 'Kapak görseli', 'asosyoloji-print-archive' ),
		'set_featured_image'    => __( 'Kapak görseli seç', 'asosyoloji-print-archive' ),
		'remove_featured_image' => __( 'Kapak görselini kaldır', 'asosyoloji-print-archive' ),
		'use_featured_image'    => __( 'Kapak görseli olarak kullan', 'asosyoloji-print-archive' ),
	);

	register_post_type(
		'aso_print_issue',
		array(
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-media-document',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'has_archive'        => 'basili-yayinlar',
			'rewrite'            => array( 'slug' => 'basili-yayin' ),
			'menu_position'      => 22,
			'publicly_queryable' => true,
			'show_in_nav_menus'  => true,
		)
	);

	register_post_meta(
		'aso_print_issue',
		'_asoprint_issue_number',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'aso_print_issue',
		'_asoprint_publication_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'asoprint_sanitize_date',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'aso_print_issue',
		'_asoprint_pdf_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => static function () {
				return current_user_can( 'upload_files' );
			},
		)
	);
}
add_action( 'init', 'asoprint_register_post_type' );

/**
 * Sanitize a YYYY-MM-DD publication date.
 *
 * @param string $value Raw date.
 * @return string
 */
function asoprint_sanitize_date( $value ) {
	$value = sanitize_text_field( (string) $value );
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts ) ) {
		return '';
	}

	return checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ? $value : '';
}

/**
 * Add useful issue columns to the admin table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function asoprint_admin_columns( $columns ) {
	return array(
		'cb'                      => $columns['cb'],
		'thumbnail'               => __( 'Kapak', 'asosyoloji-print-archive' ),
		'title'                   => __( 'Başlık', 'asosyoloji-print-archive' ),
		'asoprint_issue_number'   => __( 'Sayı', 'asosyoloji-print-archive' ),
		'asoprint_publication'    => __( 'Yayın tarihi', 'asosyoloji-print-archive' ),
		'asoprint_pdf'            => __( 'Belge', 'asosyoloji-print-archive' ),
		'date'                    => $columns['date'],
	);
}
add_filter( 'manage_aso_print_issue_posts_columns', 'asoprint_admin_columns' );

/**
 * Render issue admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Issue ID.
 */
function asoprint_admin_column_content( $column, $post_id ) {
	if ( 'thumbnail' === $column ) {
		echo get_the_post_thumbnail( $post_id, array( 54, 72 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	if ( 'asoprint_issue_number' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_asoprint_issue_number', true ) );
		return;
	}

	if ( 'asoprint_publication' === $column ) {
		echo esc_html( asoprint_format_publication_date( get_post_meta( $post_id, '_asoprint_publication_date', true ) ) );
		return;
	}

	if ( 'asoprint_pdf' === $column ) {
		$pdf_id  = absint( get_post_meta( $post_id, '_asoprint_pdf_id', true ) );
		$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
		if ( $pdf_url && 'application/pdf' === get_post_mime_type( $pdf_id ) ) {
			printf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url( $pdf_url ),
				esc_html__( 'PDF’yi aç', 'asosyoloji-print-archive' )
			);
		} else {
			echo '<span aria-hidden="true">—</span>';
		}
	}
}
add_action( 'manage_aso_print_issue_posts_custom_column', 'asoprint_admin_column_content', 10, 2 );

/**
 * Sort the public archive by publication date, then creation date.
 *
 * @param WP_Query $query Current query.
 */
function asoprint_order_public_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'aso_print_issue' ) ) {
		return;
	}

	$query->set(
		'meta_query',
		array(
			'relation'                 => 'OR',
			'asoprint_publication'     => array(
				'key'     => '_asoprint_publication_date',
				'compare' => 'EXISTS',
			),
			'asoprint_without_date'    => array(
				'key'     => '_asoprint_publication_date',
				'compare' => 'NOT EXISTS',
			),
		)
	);
	$query->set(
		'orderby',
		array(
			'asoprint_publication' => 'DESC',
			'date'                 => 'DESC',
		)
	);
}
add_action( 'pre_get_posts', 'asoprint_order_public_archive' );

