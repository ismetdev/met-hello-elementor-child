<?php
/**
 * Homepage companies grid: the nine /business/ child Pages, colour-coded by
 * sector, with an industry filter. The filter bar is printed hidden and revealed
 * by JS, so with JS off every card shows and there are no dead controls.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_companies = met_hello_child_get_companies();

if ( empty( $met_companies ) ) {
	return;
}
?>
<section class="met-section met-companies" id="companies">
	<div class="met-container">
		<div class="met-section__head met-reveal">
			<span class="met-eyebrow"><?php esc_html_e( 'Our portfolio', 'met-hello-child' ); ?></span>
			<h2><?php esc_html_e( 'Nine companies, three industries.', 'met-hello-child' ); ?></h2>
			<p><?php esc_html_e( 'Each company is wholly owned and works within one of our three divisions.', 'met-hello-child' ); ?></p>
		</div>

		<div class="met-companies__filters met-reveal" role="group" aria-label="<?php esc_attr_e( 'Filter by industry', 'met-hello-child' ); ?>" hidden>
			<button type="button" data-filter="all" aria-pressed="true"><?php esc_html_e( 'All', 'met-hello-child' ); ?></button>
			<?php foreach ( met_hello_child_sectors() as $met_co_sector ) : ?>
				<button type="button" data-filter="<?php echo esc_attr( $met_co_sector ); ?>" aria-pressed="false"><span class="met-cdot met-cdot--<?php echo esc_attr( $met_co_sector ); ?>"></span><?php echo esc_html( met_hello_child_sector_label( $met_co_sector ) ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="met-companies__grid">
			<?php foreach ( $met_companies as $met_company ) : ?>
				<?php $met_co_mod = $met_company['sector'] ? ' met-co-card--' . $met_company['sector'] : ''; ?>
				<a class="met-co-card<?php echo esc_attr( $met_co_mod ); ?> met-reveal" data-cat="<?php echo esc_attr( $met_company['sector'] ); ?>" href="<?php echo esc_url( $met_company['url'] ); ?>">
					<div class="met-co-card__media">
						<?php if ( $met_company['sector_label'] ) : ?>
							<span class="met-co-badge"><?php echo esc_html( $met_company['sector_label'] ); ?></span>
						<?php endif; ?>
						<?php met_hello_child_home_media( $met_company['image_id'], 'met-card', '', $met_company['sector'] ); ?>
					</div>
					<div class="met-co-card__body">
						<h3 class="met-co-card__title"><?php echo esc_html( $met_company['title'] ); ?></h3>
						<?php if ( $met_company['description'] ) : ?>
							<p class="met-co-card__desc"><?php echo esc_html( $met_company['description'] ); ?></p>
						<?php endif; ?>
						<span class="met-co-card__link"><?php esc_html_e( 'View company', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
