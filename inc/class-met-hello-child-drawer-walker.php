<?php
/**
 * Mobile drawer navigation walker. Renders the same primary menu (menu-1) a
 * second time for the slide-in drawer, using native <details>/<summary> for the
 * accordions so it works with no JavaScript.
 *
 *   - flat top item: a plain link.
 *   - drop top item (children only): a <details> with a flat child list.
 *   - mega top item (grandchildren): a <details> whose child titles become
 *     group headings and grandchildren become the links.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drawer walker: renders the whole menu as accordion markup.
 */
class Met_Hello_Child_Drawer_Walker extends Walker_Nav_Menu {

	/**
	 * Render the entire drawer menu from the flat item list.
	 *
	 * @param array $elements  Flat list of menu item objects.
	 * @param int   $max_depth Maximum depth (unused).
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
	 * Render one top-level drawer item.
	 *
	 * @param object $item     Menu item object.
	 * @param array  $children Parent-ID keyed map of child items.
	 * @return string
	 */
	protected function render_top_item( $item, $children ) {
		$kids = isset( $children[ $item->ID ] ) ? $children[ $item->ID ] : array();

		if ( empty( $kids ) ) {
			return sprintf(
				'<a class="met-drawer__flat" href="%s">%s</a>',
				esc_url( $item->url ),
				esc_html( $item->title )
			);
		}

		$inner = '';
		foreach ( $kids as $kid ) {
			$grandkids = isset( $children[ $kid->ID ] ) ? $children[ $kid->ID ] : array();

			if ( empty( $grandkids ) ) {
				$inner .= sprintf(
					'<a href="%s">%s</a>',
					esc_url( $kid->url ),
					esc_html( $kid->title )
				);
				continue;
			}

			// A column with grandchildren: title becomes a group heading.
			$inner .= sprintf( '<div class="met-drawer__grp">%s</div>', esc_html( $kid->title ) );
			foreach ( $grandkids as $grandkid ) {
				$inner .= sprintf(
					'<a href="%s">%s</a>',
					esc_url( $grandkid->url ),
					esc_html( $grandkid->title )
				);
			}
		}

		return sprintf(
			'<details class="met-drawer__acc"><summary>%s</summary><div class="met-drawer__sub">%s</div></details>',
			esc_html( $item->title ),
			$inner
		);
	}
}
