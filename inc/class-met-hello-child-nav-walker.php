<?php
/**
 * Desktop navigation walker. Renders the primary menu (menu-1) as the design's
 * header nav: each top-level item is classified structurally, with no hardcoded
 * titles, into one of three shapes.
 *
 *   - flat: no children -> a plain link.
 *   - drop: children but no grandchildren -> a single dropdown column.
 *   - mega: any grandchild -> a multi-column mega panel, one column per child.
 *
 * Against the live menu this yields Business = mega, About Us / Media = drop,
 * the rest flat, entirely from the menu's own shape.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom walker that renders the whole menu itself rather than item by item, so
 * the three shapes can be built cleanly from the parent/child tree.
 */
class Met_Hello_Child_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Render the entire menu from the flat item list.
	 *
	 * @param array $elements  Flat list of menu item objects.
	 * @param int   $max_depth Maximum depth (unused; we read the whole tree).
	 * @param mixed ...$args   Additional args (unused).
	 * @return string
	 */
	public function walk( $elements, $max_depth, ...$args ) {
		if ( empty( $elements ) ) {
			return '';
		}

		$children = array();
		foreach ( $elements as $element ) {
			$parent                = (int) $element->menu_item_parent;
			$children[ $parent ][] = $element;
		}

		$top = isset( $children[0] ) ? $children[0] : array();

		$output = '';
		foreach ( $top as $item ) {
			$output .= $this->render_top_item( $item, $children );
		}

		return $output;
	}

	/**
	 * Render one top-level item and its shape.
	 *
	 * @param object $item     Menu item object.
	 * @param array  $children Parent-ID keyed map of child items.
	 * @return string
	 */
	protected function render_top_item( $item, $children ) {
		$kids = isset( $children[ $item->ID ] ) ? $children[ $item->ID ] : array();

		if ( empty( $kids ) ) {
			return sprintf(
				'<a class="met-navlink" href="%s">%s</a>',
				esc_url( $item->url ),
				esc_html( $item->title )
			);
		}

		$has_grandchildren = false;
		foreach ( $kids as $kid ) {
			if ( ! empty( $children[ $kid->ID ] ) ) {
				$has_grandchildren = true;
				break;
			}
		}

		return $has_grandchildren
			? $this->render_mega( $item, $kids, $children )
			: $this->render_drop( $item, $kids );
	}

	/**
	 * Render a single-column dropdown.
	 *
	 * @param object $item Parent item.
	 * @param array  $kids Child items.
	 * @return string
	 */
	protected function render_drop( $item, $kids ) {
		$links = '';
		foreach ( $kids as $kid ) {
			$links .= sprintf(
				'<a href="%s">%s</a>',
				esc_url( $kid->url ),
				esc_html( $kid->title )
			);
		}

		return sprintf(
			'<div class="met-has-drop">%s<div class="met-dropp">%s</div></div>',
			$this->trigger( $item, true ),
			$links
		);
	}

	/**
	 * Render a multi-column mega panel, one column per child, each grandchild a
	 * link. The column heading carries a sector dot when the child resolves to a
	 * sector.
	 *
	 * @param object $item     Parent item.
	 * @param array  $kids     Child items (the columns).
	 * @param array  $children Full parent-ID keyed map.
	 * @return string
	 */
	protected function render_mega( $item, $kids, $children ) {
		$cols = '';
		foreach ( $kids as $kid ) {
			$grandkids = isset( $children[ $kid->ID ] ) ? $children[ $kid->ID ] : array();

			$links = '';
			foreach ( $grandkids as $grandkid ) {
				$links .= sprintf(
					'<a href="%s">%s</a>',
					esc_url( $grandkid->url ),
					esc_html( $grandkid->title )
				);
			}

			$sector = $this->resolve_sector( $kid );
			$dot    = $sector ? '<span class="met-cdot met-cdot--' . esc_attr( $sector ) . '"></span>' : '';

			$cols .= sprintf(
				'<div class="met-mega__col"><h3 class="met-mega__h">%s%s</h3>%s</div>',
				$dot,
				esc_html( $kid->title ),
				$links
			);
		}

		return sprintf(
			'<div class="met-has-mega">%s<div class="met-mega">%s</div></div>',
			$this->trigger( $item, true ),
			$cols
		);
	}

	/**
	 * The clickable/hoverable trigger for a drop or mega item: a real link when
	 * the item has a URL, otherwise a non-linking span (a header-only item).
	 *
	 * @param object $item     Menu item.
	 * @param bool   $with_chev Whether to append the chevron.
	 * @return string
	 */
	protected function trigger( $item, $with_chev ) {
		$chev = $with_chev ? '<svg class="met-chev" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' : '';
		$url  = trim( (string) $item->url );

		if ( '' === $url || '#' === $url ) {
			return sprintf( '<span class="met-navlink" tabindex="0">%s %s</span>', esc_html( $item->title ), $chev );
		}

		return sprintf( '<a class="met-navlink" href="%s">%s %s</a>', esc_url( $url ), esc_html( $item->title ), $chev );
	}

	/**
	 * Resolve a mega column's sector: an explicit met-sector-* menu-item class
	 * first, then a case-insensitive title match, else ''.
	 *
	 * @param object $item Child (column) menu item.
	 * @return string Sector slug or ''.
	 */
	protected function resolve_sector( $item ) {
		if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
			foreach ( $item->classes as $class ) {
				if ( 0 === strpos( $class, 'met-sector-' ) ) {
					$slug = substr( $class, strlen( 'met-sector-' ) );
					if ( function_exists( 'met_hello_child_sectors' ) && in_array( $slug, met_hello_child_sectors(), true ) ) {
						return $slug;
					}
				}
			}
		}

		if ( function_exists( 'met_hello_child_sector_from_eyebrow' ) ) {
			return met_hello_child_sector_from_eyebrow( $item->title );
		}

		return '';
	}
}
