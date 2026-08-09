<?php
/**
 * Homepage stats band. Numbers and labels come from the Customizer
 * (Homepage section). An empty stat number is skipped; all four empty hides the
 * whole band.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_stats = met_hello_child_get_stats();

if ( empty( $met_stats ) ) {
	return;
}
?>
<section class="met-section met-stats" data-count="<?php echo esc_attr( count( $met_stats ) ); ?>">
	<div class="met-container">
		<div class="met-stats__row met-reveal">
			<?php foreach ( $met_stats as $met_stat ) : ?>
				<div class="met-stat">
					<div class="met-stat__n"><?php echo esc_html( $met_stat['number'] ); ?></div>
					<?php if ( '' !== $met_stat['label'] ) : ?>
						<div class="met-stat__t"><?php echo esc_html( $met_stat['label'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
