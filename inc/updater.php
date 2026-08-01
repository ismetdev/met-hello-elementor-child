<?php
/**
 * Automatic updates from GitHub Releases.
 *
 * Checks the repository for newer releases and shows the update on the
 * Appearance > Themes and Dashboard > Updates screens, so the theme updates like
 * any other. Uses the bundled Plugin Update Checker library (YahnisElsts, v5) in
 * theme mode, the same approach as the MetTranslate plugin.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build and return the update checker. Runs once, on the first call.
 *
 * @return YahnisElsts\PluginUpdateChecker\v5p7\Vcs\ThemeUpdateChecker
 */
function met_hello_child_update_checker() {
	static $checker = null;

	if ( null !== $checker ) {
		return $checker;
	}

	require_once MET_HELLO_CHILD_DIR . 'libs/plugin-update-checker/plugin-update-checker.php';

	$checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/ismetdev/met-hello-elementor-child/',
		MET_HELLO_CHILD_DIR . 'style.css',
		get_stylesheet()
	);
	$checker->setBranch( 'main' );

	// Authenticate with GitHub only when a token is provided. Not needed while the
	// repository is public. Define MET_HELLO_CHILD_GITHUB_TOKEN in wp-config.php to
	// use one; it is never committed to the repository.
	if ( defined( 'MET_HELLO_CHILD_GITHUB_TOKEN' ) && MET_HELLO_CHILD_GITHUB_TOKEN ) {
		$checker->setAuthentication( MET_HELLO_CHILD_GITHUB_TOKEN );
	}

	// Deliver updates from the zip attached to each GitHub Release, so the theme
	// unpacks into a clean met-hello-elementor-child/ folder. Without this, the
	// auto-generated source zip would install as a separate theme.
	$checker->getVcsApi()->enableReleaseAssets();

	return $checker;
}

met_hello_child_update_checker();
