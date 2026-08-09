<?php
/**
 * Template Name: Homepage
 *
 * The IIUM Holdings homepage. Assigned per Page in Page Attributes, then set as
 * the front page in Settings > Reading. Deliberately a Page Template, not
 * front-page.php, which would outrank it and make the Template dropdown a lie.
 * See DECISIONS D37.
 *
 * No loop and no the_content(): every section is composed from its own partial
 * in template-parts/home/, each reading published content through the helpers
 * in inc/homepage.php.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="met-home">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/announcements' );
	get_template_part( 'template-parts/home/actions' );
	get_template_part( 'template-parts/home/about' );
	get_template_part( 'template-parts/home/stats' );
	get_template_part( 'template-parts/home/companies' );
	get_template_part( 'template-parts/home/rise' );
	get_template_part( 'template-parts/home/newsroom' );
	get_template_part( 'template-parts/home/cta' );
	?>
</main>
<?php
get_footer();
