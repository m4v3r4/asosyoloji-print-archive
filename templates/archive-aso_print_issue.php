<?php
/**
 * Printed issue archive template.
 *
 * @package AsosyolojiPrintArchive
 */

get_header();
wp_enqueue_style( 'asosyoloji-print-archive' );
?>
<div class="asoprint-page">
	<header class="asoprint-page__header">
		<div class="asoprint-archive__kicker"><?php esc_html_e( 'PDF Arşivi', 'asosyoloji-print-archive' ); ?></div>
		<h1 class="asoprint-page__title"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="asoprint-archive asoprint-archive--grid">
			<div class="asoprint-archive__items">
				<?php
				while ( have_posts() ) {
					the_post();
					echo asoprint_render_issue_card( get_the_ID(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Henüz basılı yayın eklenmedi.', 'asosyoloji-print-archive' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();

