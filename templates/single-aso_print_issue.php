<?php
/**
 * Printed issue detail template.
 *
 * @package AsosyolojiPrintArchive
 */

$queried_issue_id = get_queried_object_id();
$queried_pdf_url  = asoprint_get_pdf_url( $queried_issue_id );

if ( $queried_pdf_url ) {
	asoprint_enqueue_flipbook_assets();
} else {
	wp_enqueue_style( 'asosyoloji-print-archive' );
}

get_header();

while ( have_posts() ) :
	the_post();
	$issue_id = get_the_ID();
	$number   = sanitize_text_field( get_post_meta( $issue_id, '_asoprint_issue_number', true ) );
	$date     = asoprint_format_publication_date( get_post_meta( $issue_id, '_asoprint_publication_date', true ) );
	$pdf_url  = asoprint_get_pdf_url( $issue_id );
	?>
	<div class="asoprint-page">
		<article class="asoprint-single<?php echo $pdf_url ? ' asoprint-single--has-viewer' : ''; ?>">
			<div class="asoprint-single__cover">
				<?php echo wp_kses_post( asoprint_get_cover_markup( $issue_id ) ); ?>
			</div>

			<div class="asoprint-single__content">
				<?php if ( $number ) : ?>
					<div class="asoprint-card__number"><?php echo esc_html( sprintf( __( 'Sayı %s', 'asosyoloji-print-archive' ), $number ) ); ?></div>
				<?php endif; ?>
				<h1 class="asoprint-single__title"><?php the_title(); ?></h1>
				<?php if ( $date ) : ?>
					<time class="asoprint-single__date" datetime="<?php echo esc_attr( get_post_meta( $issue_id, '_asoprint_publication_date', true ) ); ?>"><?php echo esc_html( $date ); ?></time>
				<?php endif; ?>

				<?php if ( $pdf_url ) : ?>
					<?php echo asoprint_render_flipbook( $pdf_url, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>

				<?php if ( trim( (string) get_the_content() ) ) : ?>
					<div class="asoprint-single__description"><?php the_content(); ?></div>
				<?php endif; ?>

			</div>
		</article>
	</div>
	<?php
endwhile;

get_footer();

