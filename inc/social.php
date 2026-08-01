<?php
/**
 * Social links and icons: author profile links, post share targets, inline SVGs.
 *
 * Adding a network is one entry in the icon map plus one entry in the share list.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author website + social links for the author profile header.
 *
 * Reads the WordPress website field plus any social contact methods (Yoast adds
 * twitter/facebook/linkedin/instagram/youtube and more). Values that are already
 * URLs pass through; bare handles for known networks are expanded. Returns an
 * array of items: array( 'url', 'label', 'icon' ). Degrades to an empty array.
 *
 * @param int $user_id Author user ID.
 * @return array<int,array<string,string>>
 */
function met_hello_child_get_author_links( $user_id ) {
	$links = array();

	$website = get_the_author_meta( 'user_url', $user_id );
	if ( $website ) {
		$links[] = array(
			'url'   => $website,
			'label' => __( 'Website', 'met-hello-child' ),
			'icon'  => 'globe',
		);
	}

	$methods = wp_get_user_contact_methods( null );
	foreach ( $methods as $key => $label ) {
		$value = get_the_author_meta( $key, $user_id );
		if ( ! $value ) {
			continue;
		}
		$url = met_hello_child_normalize_social_url( $key, $value );
		if ( ! $url ) {
			continue;
		}
		$links[] = array(
			'url'   => $url,
			'label' => $label,
			'icon'  => $key,
		);
	}

	return $links;
}

/**
 * Turn a social contact-method value into a full URL.
 *
 * @param string $key   Contact-method key (e.g. "twitter").
 * @param string $value Stored value (URL or bare handle).
 * @return string Full URL, or '' if it cannot be resolved.
 */
function met_hello_child_normalize_social_url( $key, $value ) {
	if ( preg_match( '#^https?://#i', $value ) ) {
		return $value;
	}

	$handle = ltrim( $value, '@/' );
	if ( '' === $handle ) {
		return '';
	}

	switch ( $key ) {
		case 'twitter':
			return 'https://twitter.com/' . rawurlencode( $handle );
		case 'facebook':
			return 'https://www.facebook.com/' . rawurlencode( $handle );
		case 'instagram':
			return 'https://www.instagram.com/' . rawurlencode( $handle );
		case 'linkedin':
			return 'https://www.linkedin.com/in/' . rawurlencode( $handle );
		case 'youtube':
			return 'https://www.youtube.com/' . rawurlencode( $handle );
	}

	return '';
}

/**
 * Inline SVG for a social/website icon. Returns trusted static markup.
 *
 * @param string $name Icon key.
 * @return string
 */
function met_hello_child_social_icon( $name ) {
	$icons = array(
		'globe'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" stroke="currentColor" stroke-width="1.7"/></svg>',
		'twitter'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 3h3l-6.6 7.5L21.8 21h-5.9l-4.2-5.5L6.8 21H3.8l7-8L2.5 3h6l3.8 5 5.2-5z"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.7"/><circle cx="16.6" cy="7.4" r="1.1" fill="currentColor"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.5 8.5v9H4v-9h2.5zM5.2 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM20 17.5h-2.5v-4.7c0-1.2-.4-2-1.5-2-.8 0-1.3.6-1.5 1.1-.1.2-.1.5-.1.7v4.9H12s.03-8.2 0-9h2.5v1.3c.3-.5 1-1.2 2.3-1.2 1.7 0 3.2 1.1 3.2 3.6v5.3z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="12" rx="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M11 9.5l3.5 2.5L11 14.5v-5z" fill="currentColor"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 3h3l-6.6 7.5L21.8 21h-5.9l-4.2-5.5L6.8 21H3.8l7-8L2.5 3h6l3.8 5 5.2-5z"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M4 20l1.3-4A8 8 0 1112 20a8 8 0 01-4-1L4 20z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5.4 0 .7-.4.7-.8v-1c0-.3-.2-.5-.5-.6l-1-.2c-.3 0-.5.1-.7.3-.6-.3-1.1-.8-1.4-1.4.2-.2.3-.4.3-.7l-.2-1c-.1-.3-.3-.5-.6-.5h-1c-.4 0-.8.3-.8.7z" fill="currentColor"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M21.9 4.3l-3 14.1c-.2 1-.8 1.2-1.7.8l-4.6-3.4-2.2 2.1c-.2.2-.5.4-.9.4l.3-4.7 8.5-7.7c.4-.3-.1-.5-.6-.2L7.4 12.6l-4.5-1.4c-1-.3-1-1 .2-1.4l17.6-6.8c.8-.3 1.5.2 1.2 1.3z"/></svg>',
		'threads'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12.4 2c2.6 0 4.6.8 6 2.5 1.2 1.5 1.9 3.5 2 6v1c-.1 2.5-.8 4.5-2 6-1.4 1.7-3.4 2.5-6 2.5s-4.6-.8-6-2.5c-1.2-1.5-1.9-3.5-2-6v-1c.1-2.5.8-4.5 2-6C7.8 2.8 9.8 2 12.4 2Zm0 1.8c-2.1 0-3.6.6-4.6 1.8-1 1.2-1.5 2.9-1.6 5v.8c.1 2.1.6 3.8 1.6 5 1 1.2 2.5 1.8 4.6 1.8s3.6-.6 4.6-1.8c.4-.5.7-1.1 1-1.9-.3-.2-.7-.4-1.1-.6-.3 1.1-.8 1.9-1.5 2.5-.8.6-1.7.8-2.8.7-1-.1-1.8-.4-2.3-1-.6-.6-.8-1.3-.8-2 .1-1.6 1.5-2.6 3.6-2.6.6 0 1.2 0 1.7.1v-.2c0-.7-.2-1.2-.6-1.6-.4-.4-1-.6-1.7-.6-1 0-1.6.4-2.1 1.1l-1.4-.9c.7-1.1 1.8-1.6 3.3-1.6 1.2 0 2.1.4 2.8 1.1.7.7 1 1.6 1.1 2.8v.5c.4.2.8.5 1.1.7v-.6c-.1-2.1-.6-3.8-1.6-5-1-1.2-2.5-1.8-4.6-1.8Zm.3 8.6c-1.1 0-1.9.5-1.9 1.2 0 .4.1.7.4.9.3.3.7.4 1.2.4.7 0 1.2-.2 1.6-.6.3-.3.5-.8.6-1.4-.6-.1-1.2-.1-1.9-.1Z"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['globe'];
}

/**
 * Share targets for a single post. Order follows common share-bar convention.
 *
 * @param string $url   Canonical post URL.
 * @param string $title Post title.
 * @return array<int,array<string,string>> Items: array( 'icon', 'label', 'url' ).
 */
function met_hello_child_get_share_links( $url, $title ) {
	$u = rawurlencode( $url );
	$t = rawurlencode( $title );

	return array(
		array(
			'icon'  => 'x',
			'label' => __( 'Share on X', 'met-hello-child' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t,
		),
		array(
			'icon'  => 'facebook',
			'label' => __( 'Share on Facebook', 'met-hello-child' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $u,
		),
		array(
			'icon'  => 'linkedin',
			'label' => __( 'Share on LinkedIn', 'met-hello-child' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u,
		),
		array(
			'icon'  => 'whatsapp',
			'label' => __( 'Share on WhatsApp', 'met-hello-child' ),
			'url'   => 'https://api.whatsapp.com/send?text=' . $t . '%20' . $u,
		),
		array(
			'icon'  => 'telegram',
			'label' => __( 'Share on Telegram', 'met-hello-child' ),
			'url'   => 'https://t.me/share/url?url=' . $u . '&text=' . $t,
		),
		array(
			'icon'  => 'threads',
			'label' => __( 'Share on Threads', 'met-hello-child' ),
			'url'   => 'https://www.threads.net/intent/post?text=' . $t . '%20' . $u,
		),
	);
}
