<?php
/**
 * Homepage RISE2030 band: the five-year plan, its revenue target, and the four
 * R/I/S/E thrusts. Static editorial content; the letters use the serif face for
 * numerals-and-emblems, matching the design system.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_rise_thrusts = array(
	array(
		'letter' => 'R',
		'text'   => __( 'Realise growth opportunities', 'met-hello-child' ),
	),
	array(
		'letter' => 'I',
		'text'   => __( 'Improve efficiency and cost control', 'met-hello-child' ),
	),
	array(
		'letter' => 'S',
		'text'   => __( 'Secure financial sustainability', 'met-hello-child' ),
	),
	array(
		'letter' => 'E',
		'text'   => __( 'Elevate people and governance', 'met-hello-child' ),
	),
);
?>
<section class="met-section met-rise" id="rise">
	<div class="met-container">
		<div class="met-rise__grid">
			<div class="met-reveal">
				<span class="met-eyebrow met-eyebrow--on-dark"><?php esc_html_e( 'Looking ahead', 'met-hello-child' ); ?></span>
				<h2 class="met-rise__title"><?php esc_html_e( 'RISE2030: our plan for the next five years.', 'met-hello-child' ); ?></h2>
				<p class="met-rise__lead"><?php esc_html_e( 'A five year blueprint, from 2026 to 2030, built on four priorities.', 'met-hello-child' ); ?></p>
				<div class="met-rise__target">
					<span class="met-rise__big"><?php esc_html_e( 'RM419 million', 'met-hello-child' ); ?></span>
					<span class="met-rise__lbl"><?php esc_html_e( 'group revenue target by 2030', 'met-hello-child' ); ?></span>
				</div>
			</div>
			<div class="met-reveal">
				<ul class="met-rise__thrusts">
					<?php foreach ( $met_rise_thrusts as $met_thrust ) : ?>
						<li class="met-thrust">
							<span class="met-thrust__l" aria-hidden="true"><?php echo esc_html( $met_thrust['letter'] ); ?></span>
							<h3 class="met-thrust__t"><?php echo esc_html( $met_thrust['text'] ); ?></h3>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>
