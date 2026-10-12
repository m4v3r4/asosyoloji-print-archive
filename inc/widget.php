<?php
/**
 * Printed issue archive widget.
 *
 * @package AsosyolojiPrintArchive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive widget.
 */
class Asoprint_Archive_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'asoprint_archive_widget',
			__( 'Asosyoloji: Basılı Yayın Arşivi', 'asosyoloji-print-archive' ),
			array(
				'description' => __( 'Kaydedilmiş basılı yayınları kapak, sayı, başlık, tarih ve PDF bağlantısıyla listeler.', 'asosyoloji-print-archive' ),
			)
		);
	}

	/**
	 * Frontend output.
	 *
	 * @param array $args     Widget wrappers.
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$content = asoprint_render_archive(
			array(
				'title'            => isset( $instance['title'] ) ? $instance['title'] : __( 'Basılı Yayınlar', 'asosyoloji-print-archive' ),
				'count'            => isset( $instance['count'] ) ? absint( $instance['count'] ) : 6,
				'layout'           => isset( $instance['layout'] ) ? $instance['layout'] : 'grid',
				'order'            => isset( $instance['order'] ) ? $instance['order'] : 'DESC',
				'show_description' => ! empty( $instance['show_description'] ),
			)
		);

		if ( ! $content ) {
			return;
		}

		echo wp_kses_post( $args['before_widget'] );
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Sanitize widget settings.
	 *
	 * @param array $new_instance Submitted settings.
	 * @param array $old_instance Previous settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'            => sanitize_text_field( $new_instance['title'] ?? '' ),
			'count'            => min( 50, max( 1, absint( $new_instance['count'] ?? 6 ) ) ),
			'layout'           => in_array( $new_instance['layout'] ?? 'grid', array( 'grid', 'list' ), true ) ? $new_instance['layout'] : 'grid',
			'order'            => 'ASC' === ( $new_instance['order'] ?? 'DESC' ) ? 'ASC' : 'DESC',
			'show_description' => ! empty( $new_instance['show_description'] ) ? 1 : 0,
		);
	}

	/**
	 * Widget settings form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$title            = $instance['title'] ?? __( 'Basılı Yayınlar', 'asosyoloji-print-archive' );
		$count            = min( 50, max( 1, absint( $instance['count'] ?? 6 ) ) );
		$layout           = $instance['layout'] ?? 'grid';
		$order            = $instance['order'] ?? 'DESC';
		$show_description = ! empty( $instance['show_description'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Başlık', 'asosyoloji-print-archive' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Gösterilecek yayın sayısı', 'asosyoloji-print-archive' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="50" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_html_e( 'Görünüm', 'asosyoloji-print-archive' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
				<option value="grid" <?php selected( $layout, 'grid' ); ?>><?php esc_html_e( 'Kapak grid', 'asosyoloji-print-archive' ); ?></option>
				<option value="list" <?php selected( $layout, 'list' ); ?>><?php esc_html_e( 'Liste', 'asosyoloji-print-archive' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Sıralama', 'asosyoloji-print-archive' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Yeniden eskiye', 'asosyoloji-print-archive' ); ?></option>
				<option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Eskiden yeniye', 'asosyoloji-print-archive' ); ?></option>
			</select>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_description' ) ); ?>" value="1" <?php checked( $show_description ); ?>> <?php esc_html_e( 'Kısa açıklamayı göster', 'asosyoloji-print-archive' ); ?></label>
		</p>
		<?php
	}
}

/**
 * Register the plugin widget and hide the theme's superseded manual widget.
 */
function asoprint_register_widget() {
	if ( class_exists( 'Asosyoloji_Magazine_Archive_Widget' ) ) {
		unregister_widget( 'Asosyoloji_Magazine_Archive_Widget' );
	}
	register_widget( 'Asoprint_Archive_Widget' );
}
add_action( 'widgets_init', 'asoprint_register_widget', 30 );

