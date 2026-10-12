<?php
/**
 * Interactive PDF flipbook reader.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add module support to the locally bundled flipbook entry script.
 *
 * @param string $tag    Script HTML.
 * @param string $handle Script handle.
 * @return string
 */
function asoprint_flipbook_script_type( $tag, $handle ) {
	if ( 'asoprint-flipbook' !== $handle || false !== strpos( $tag, ' type=' ) ) {
		return $tag;
	}

	return str_replace( '<script ', '<script type="module" ', $tag );
}
add_filter( 'script_loader_tag', 'asoprint_flipbook_script_type', 10, 2 );

/**
 * Load the reader only on issue pages that contain a PDF.
 */
function asoprint_enqueue_flipbook_assets() {
	wp_enqueue_style( 'asosyoloji-print-archive' );
	wp_enqueue_script(
		'asoprint-flipbook',
		ASOPRINT_URL . 'assets/flipbook.js',
		array(),
		ASOPRINT_VERSION,
		true
	);
}

/**
 * Render the accessible shell that is enhanced into a 3D book by JavaScript.
 *
 * @param string $pdf_url PDF URL.
 * @param string $title   Publication title.
 * @return string
 */
function asoprint_render_flipbook( $pdf_url, $title ) {
	if ( ! $pdf_url ) {
		return '';
	}

	ob_start();
	?>
	<section class="asoprint-flipbook" data-asoprint-flipbook data-pdf-url="<?php echo esc_url( $pdf_url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s PDF okuyucu', 'asosyoloji-print-archive' ), $title ) ); ?>">
		<div class="asoprint-flipbook__heading">
			<div>
				<span class="asoprint-flipbook__eyebrow"><?php esc_html_e( 'Dijital okuma', 'asosyoloji-print-archive' ); ?></span>
				<h2><?php esc_html_e( 'Dergiyi çevirerek inceleyin', 'asosyoloji-print-archive' ); ?></h2>
			</div>
			<span class="asoprint-flipbook__hint"><?php esc_html_e( 'Sayfayı sürükleyin veya okları kullanın', 'asosyoloji-print-archive' ); ?></span>
		</div>

		<div class="asoprint-flipbook__stage" data-asoprint-stage tabindex="0">
			<div class="asoprint-flipbook__loading" data-asoprint-loading role="status" aria-live="polite">
				<span class="asoprint-flipbook__spinner" aria-hidden="true"></span>
				<span data-asoprint-loading-text><?php esc_html_e( 'Dergi hazırlanıyor…', 'asosyoloji-print-archive' ); ?></span>
			</div>
			<div class="asoprint-flipbook__book" data-asoprint-book></div>
		</div>

		<div class="asoprint-flipbook__controls">
			<div class="asoprint-flipbook__nav">
				<button type="button" class="asoprint-flipbook__control" data-asoprint-prev disabled aria-label="<?php esc_attr_e( 'Önceki sayfa', 'asosyoloji-print-archive' ); ?>">← <span><?php esc_html_e( 'Önceki', 'asosyoloji-print-archive' ); ?></span></button>
				<span class="asoprint-flipbook__counter" data-asoprint-counter aria-live="polite">– / –</span>
				<button type="button" class="asoprint-flipbook__control" data-asoprint-next disabled aria-label="<?php esc_attr_e( 'Sonraki sayfa', 'asosyoloji-print-archive' ); ?>"><span><?php esc_html_e( 'Sonraki', 'asosyoloji-print-archive' ); ?></span> →</button>
			</div>
			<div class="asoprint-flipbook__actions">
				<button type="button" class="asoprint-flipbook__control asoprint-flipbook__fullscreen" data-asoprint-fullscreen><?php esc_html_e( 'Tam ekran', 'asosyoloji-print-archive' ); ?></button>
				<a class="asoprint-flipbook__download" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PDF’yi indir', 'asosyoloji-print-archive' ); ?> ↗</a>
			</div>
		</div>
		<noscript><p><a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PDF belgesini açın', 'asosyoloji-print-archive' ); ?></a></p></noscript>
	</section>
	<?php
	return (string) ob_get_clean();
}
