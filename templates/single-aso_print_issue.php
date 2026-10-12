<?php
/**
 * Printed issue detail template.
 *
 * @package AsosyolojiPrintArchive
 */

get_header();
wp_enqueue_style( 'asosyoloji-print-archive' );

while ( have_posts() ) :
	the_post();
	$issue_id = get_the_ID();
	$number   = sanitize_text_field( get_post_meta( $issue_id, '_asoprint_issue_number', true ) );
	$date     = asoprint_format_publication_date( get_post_meta( $issue_id, '_asoprint_publication_date', true ) );
	$pdf_url  = asoprint_get_pdf_url( $issue_id );
	?>
	<div class="asoprint-page">
		<article class="asoprint-single">
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

				<?php if ( trim( (string) get_the_content() ) ) : ?>
					<div class="asoprint-single__description"><?php the_content(); ?></div>
				<?php endif; ?>

				<?php if ( $pdf_url ) : ?>
					<a class="asoprint-single__button" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PDF’yi aç', 'asosyoloji-print-archive' ); ?></a>
				<?php endif; ?>
			</div>
		</article>
	</div>
	<?php
endwhile;

get_footer();

