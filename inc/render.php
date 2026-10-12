<?php
/**
 * Frontend renderers, shortcode, and templates.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format an issue date in the site locale.
 *
 * @param string $date YYYY-MM-DD date.
 * @return string
 */
function asoprint_format_publication_date( $date ) {
	$date = asoprint_sanitize_date( $date );
	if ( ! $date ) {
		return '';
	}

	$timestamp = strtotime( $date . ' 12:00:00' );
	return $timestamp ? wp_date( get_option( 'date_format' ), $timestamp ) : '';
}

/**
 * Return a valid PDF attachment URL for an issue.
 *
 * @param int $post_id Issue ID.
 * @return string
 */
function asoprint_get_pdf_url( $post_id ) {
	$pdf_id = absint( get_post_meta( $post_id, '_asoprint_pdf_id', true ) );
	if ( $pdf_id && 'application/pdf' === get_post_mime_type( $pdf_id ) ) {
		return (string) wp_get_attachment_url( $pdf_id );
	}

	$content = (string) get_post_field( 'post_content', $post_id );
	if ( $content ) {
		if ( preg_match_all( '/(?:href|src)=["\']([^"\']+)["\']/i', $content, $matches ) ) {
			foreach ( $matches[1] as $candidate ) {
				$candidate = html_entity_decode( $candidate, ENT_QUOTES, get_bloginfo( 'charset' ) );
				$path      = (string) wp_parse_url( $candidate, PHP_URL_PATH );
				if ( $path && preg_match( '/\.pdf$/i', $path ) ) {
					return esc_url_raw( $candidate );
				}
			}
		}

		if ( preg_match( '#(?:https?:)?//[^\s"\'<>]+\.pdf(?:\?[^\s"\'<>]*)?#i', $content, $match ) ) {
			return esc_url_raw( html_entity_decode( $match[0], ENT_QUOTES, get_bloginfo( 'charset' ) ) );
		}
	}

	$attachments = get_attached_media( 'application/pdf', $post_id );
	if ( $attachments ) {
		$attachment = reset( $attachments );
		if ( $attachment instanceof WP_Post ) {
			return (string) wp_get_attachment_url( $attachment->ID );
		}
	}

	return '';
}

/**
 * Return cover markup from the featured image or WordPress PDF preview.
 *
 * @param int $post_id Issue ID.
 * @return string
 */
function asoprint_get_cover_markup( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			'medium_large',
			array(
				'class'    => 'asoprint-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	}

	$pdf_id = absint( get_post_meta( $post_id, '_asoprint_pdf_id', true ) );
	if ( $pdf_id ) {
		$preview = wp_get_attachment_image(
			$pdf_id,
			'medium_large',
			false,
			array(
				'class'    => 'asoprint-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		if ( $preview ) {
			return $preview;
		}
	}

	return '<span class="asoprint-card__placeholder" aria-hidden="true"><span>PDF</span></span>';
}

/**
 * Render one issue card.
 *
 * @param int  $post_id          Issue ID.
 * @param bool $show_description Whether to show the description.
 * @return string
 */
function asoprint_render_issue_card( $post_id, $show_description = false ) {
	$post_id = absint( $post_id );
	$pdf_url = asoprint_get_pdf_url( $post_id );
	$number  = sanitize_text_field( get_post_meta( $post_id, '_asoprint_issue_number', true ) );
	$date    = asoprint_format_publication_date( get_post_meta( $post_id, '_asoprint_publication_date', true ) );
	$title   = get_the_title( $post_id );
	$link    = get_permalink( $post_id );

	ob_start();
	?>
	<article class="asoprint-card">
		<a class="asoprint-card__cover" href="<?php echo esc_url( $link ); ?>">
			<?php echo wp_kses_post( asoprint_get_cover_markup( $post_id ) ); ?>
		</a>

		<div class="asoprint-card__content">
			<?php if ( $number ) : ?>
				<div class="asoprint-card__number"><?php echo esc_html( sprintf( __( 'Sayı %s', 'asosyoloji-print-archive' ), $number ) ); ?></div>
			<?php endif; ?>

			<h3 class="asoprint-card__title">
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
			</h3>

			<?php if ( $date ) : ?>
				<time class="asoprint-card__date" datetime="<?php echo esc_attr( get_post_meta( $post_id, '_asoprint_publication_date', true ) ); ?>"><?php echo esc_html( $date ); ?></time>
			<?php endif; ?>

			<?php if ( $show_description && has_excerpt( $post_id ) ) : ?>
				<p class="asoprint-card__excerpt"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
			<?php endif; ?>

			<a class="asoprint-card__button" href="<?php echo esc_url( $link ); ?>">
				<?php echo $pdf_url ? esc_html__( 'Dergiyi oku', 'asosyoloji-print-archive' ) : esc_html__( 'Yayını görüntüle', 'asosyoloji-print-archive' ); ?>
			</a>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Query and render an issue collection.
 *
 * @param array $args Display arguments.
 * @return string
 */
function asoprint_render_archive( $args = array() ) {
	$defaults = array(
		'title'            => __( 'Basılı Yayın Arşivi', 'asosyoloji-print-archive' ),
		'count'            => 12,
		'layout'           => 'grid',
		'order'            => 'DESC',
		'show_description' => false,
	);
	$args     = wp_parse_args( $args, $defaults );
	$layout   = in_array( $args['layout'], array( 'grid', 'list' ), true ) ? $args['layout'] : 'grid';
	$order    = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';
	$count    = min( 50, max( 1, absint( $args['count'] ) ) );

	$query = new WP_Query(
		array(
			'post_type'      => 'aso_print_issue',
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation'              => 'OR',
				'asoprint_publication'  => array(
					'key'     => '_asoprint_publication_date',
					'compare' => 'EXISTS',
				),
				'asoprint_without_date' => array(
					'key'     => '_asoprint_publication_date',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'        => array(
				'asoprint_publication' => $order,
				'date'                  => $order,
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'asosyoloji-print-archive' );
	ob_start();
	?>
	<section class="asoprint-archive asoprint-archive--<?php echo esc_attr( $layout ); ?>">
		<?php if ( $args['title'] ) : ?>
			<header class="asoprint-archive__header">
				<div class="asoprint-archive__kicker"><?php esc_html_e( 'PDF Arşivi', 'asosyoloji-print-archive' ); ?></div>
				<h2 class="asoprint-archive__title"><?php echo esc_html( $args['title'] ); ?></h2>
			</header>
		<?php endif; ?>

		<div class="asoprint-archive__items">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				echo asoprint_render_issue_card( get_the_ID(), (bool) $args['show_description'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * Printed archive shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function asoprint_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'title'       => __( 'Basılı Yayın Arşivi', 'asosyoloji-print-archive' ),
			'count'       => 12,
			'layout'      => 'grid',
			'order'       => 'DESC',
			'description' => '0',
		),
		$atts,
		'asosyoloji_basili_yayinlar'
	);

	return asoprint_render_archive(
		array(
			'title'            => sanitize_text_field( $atts['title'] ),
			'count'            => absint( $atts['count'] ),
			'layout'           => sanitize_key( $atts['layout'] ),
			'order'            => sanitize_key( strtoupper( (string) $atts['order'] ) ),
			'show_description' => '1' === (string) $atts['description'],
		)
	);
}
add_shortcode( 'asosyoloji_basili_yayinlar', 'asoprint_shortcode' );

/**
 * Use plugin templates for issue archives and detail pages.
 *
 * @param string $template Selected theme template.
 * @return string
 */
function asoprint_template_include( $template ) {
	if ( is_post_type_archive( 'aso_print_issue' ) ) {
		return ASOPRINT_DIR . 'templates/archive-aso_print_issue.php';
	}

	if ( is_singular( 'aso_print_issue' ) ) {
		return ASOPRINT_DIR . 'templates/single-aso_print_issue.php';
	}

	return $template;
}
add_filter( 'template_include', 'asoprint_template_include' );

