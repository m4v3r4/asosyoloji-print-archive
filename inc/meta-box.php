<?php
/**
 * Issue details meta box.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the issue details panel.
 */
function asoprint_add_meta_boxes() {
	add_meta_box(
		'asoprint-issue-details',
		__( 'Yayın Bilgileri', 'asosyoloji-print-archive' ),
		'asoprint_issue_meta_box',
		'aso_print_issue',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'asoprint_add_meta_boxes' );

/**
 * Render issue fields.
 *
 * @param WP_Post $post Current issue.
 */
function asoprint_issue_meta_box( $post ) {
	$number   = get_post_meta( $post->ID, '_asoprint_issue_number', true );
	$date     = get_post_meta( $post->ID, '_asoprint_publication_date', true );
	$pdf_id   = absint( get_post_meta( $post->ID, '_asoprint_pdf_id', true ) );
	$pdf_url  = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
	$pdf_name = $pdf_id ? basename( (string) get_attached_file( $pdf_id ) ) : '';

	wp_nonce_field( 'asoprint_save_issue', 'asoprint_issue_nonce' );
	?>
	<style>
		.asoprint-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.asoprint-admin-field label{display:block;margin-bottom:6px;font-weight:600}.asoprint-admin-field input[type=text],.asoprint-admin-field input[type=date]{width:100%}.asoprint-admin-pdf{grid-column:1/-1;padding:16px;border:1px solid #dcdcde;background:#f6f7f7}.asoprint-admin-pdf__name{display:block;margin:0 0 10px;font-weight:600}.asoprint-admin-help{margin:8px 0 0;color:#646970}@media(max-width:782px){.asoprint-admin-grid{grid-template-columns:1fr}}
	</style>
	<div class="asoprint-admin-grid">
		<div class="asoprint-admin-field">
			<label for="asoprint-issue-number"><?php esc_html_e( 'Sayı', 'asosyoloji-print-archive' ); ?></label>
			<input id="asoprint-issue-number" name="asoprint_issue_number" type="text" value="<?php echo esc_attr( $number ); ?>" placeholder="Örn. 12 veya 2026/1">
		</div>

		<div class="asoprint-admin-field">
			<label for="asoprint-publication-date"><?php esc_html_e( 'Yayın tarihi', 'asosyoloji-print-archive' ); ?></label>
			<input id="asoprint-publication-date" name="asoprint_publication_date" type="date" value="<?php echo esc_attr( $date ); ?>">
		</div>

		<div class="asoprint-admin-field asoprint-admin-pdf" data-asoprint-pdf-field>
			<label><?php esc_html_e( 'PDF belgesi', 'asosyoloji-print-archive' ); ?></label>
			<input type="hidden" name="asoprint_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>" data-asoprint-pdf-id>
			<span class="asoprint-admin-pdf__name" data-asoprint-pdf-name><?php echo $pdf_name ? esc_html( $pdf_name ) : esc_html__( 'Henüz PDF seçilmedi.', 'asosyoloji-print-archive' ); ?></span>
			<button type="button" class="button button-primary" data-asoprint-select-pdf><?php esc_html_e( 'PDF yükle veya seç', 'asosyoloji-print-archive' ); ?></button>
			<button type="button" class="button-link-delete" data-asoprint-remove-pdf<?php echo $pdf_id ? '' : ' hidden'; ?>><?php esc_html_e( 'PDF’yi kaldır', 'asosyoloji-print-archive' ); ?></button>
			<?php if ( $pdf_url ) : ?>
				<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener noreferrer" data-asoprint-open-pdf><?php esc_html_e( 'Belgeyi aç', 'asosyoloji-print-archive' ); ?></a>
			<?php else : ?>
				<a href="#" target="_blank" rel="noopener noreferrer" data-asoprint-open-pdf hidden><?php esc_html_e( 'Belgeyi aç', 'asosyoloji-print-archive' ); ?></a>
			<?php endif; ?>
			<p class="asoprint-admin-help"><?php esc_html_e( 'Yalnızca Medya Kütüphanesindeki PDF dosyaları kabul edilir. Kapak için sağdaki “Kapak görseli” alanını kullanabilirsiniz.', 'asosyoloji-print-archive' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Save issue metadata.
 *
 * @param int $post_id Issue ID.
 */
function asoprint_save_issue_meta( $post_id ) {
	if ( ! isset( $_POST['asoprint_issue_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asoprint_issue_nonce'] ) ), 'asoprint_save_issue' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$number = isset( $_POST['asoprint_issue_number'] ) ? sanitize_text_field( wp_unslash( $_POST['asoprint_issue_number'] ) ) : '';
	$date   = isset( $_POST['asoprint_publication_date'] ) ? asoprint_sanitize_date( wp_unslash( $_POST['asoprint_publication_date'] ) ) : '';
	$pdf_id = isset( $_POST['asoprint_pdf_id'] ) ? absint( $_POST['asoprint_pdf_id'] ) : 0;

	update_post_meta( $post_id, '_asoprint_issue_number', $number );
	update_post_meta( $post_id, '_asoprint_publication_date', $date );

	if ( $pdf_id && 'attachment' === get_post_type( $pdf_id ) && 'application/pdf' === get_post_mime_type( $pdf_id ) ) {
		update_post_meta( $post_id, '_asoprint_pdf_id', $pdf_id );
	} else {
		delete_post_meta( $post_id, '_asoprint_pdf_id' );
	}
}
add_action( 'save_post_aso_print_issue', 'asoprint_save_issue_meta' );

