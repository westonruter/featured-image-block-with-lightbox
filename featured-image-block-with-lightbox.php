<?php
/**
 * Plugin Name: Featured Image Block with Lightbox
 * Plugin URI: https://github.com/westonruter/featured-image-block-with-lightbox
 * Description: Automatically enables "Enlarge on click" (lightbox) for the Featured Image block when "Link to Post" is not enabled. This is a workaround to implement <a href="https://github.com/WordPress/gutenberg/issues/57849">gutenberg#57849</a>. There are no settings for this plugin.
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Version: 0.1.0
 * Author: Weston Ruter
 * Author URI: https://weston.ruter.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Update URI: https://github.com/westonruter/featured-image-block-with-lightbox
 * GitHub Plugin URI: https://github.com/westonruter/featured-image-block-with-lightbox
 * Primary Branch: main
 *
 * @package FeaturedImageBlockWithLightbox
 */

namespace FeaturedImageBlockWithLightbox;

const BLOCK_NAME = 'core/post-featured-image';

/**
 * Filters the Featured Image block to add a caption on the singular template.
 *
 * @param string|mixed                              $block_content The block content.
 * @param array{ attrs: array{ isLink?: boolean } } $attributes The block attributes.
 * @return string The filtered block content.
 */
function filter_featured_image_block( mixed $block_content, array $attributes ): string {
	// Because other plugins can do bad things.
	if ( ! is_string( $block_content ) ) {
		$block_content = '';
	}

	// Bail if "Link to Post" is enabled.
	if ( isset( $attributes['attrs']['isLink'] ) && $attributes['attrs']['isLink'] ) {
		return $block_content;
	}

	$attachment_id = get_post_thumbnail_id();
	if ( 0 === (int) $attachment_id ) {
		return $block_content;
	}

	// Enqueue the required view script and style from the Image block.
	wp_enqueue_script_module( '@wordpress/block-library/image/view' );
	wp_enqueue_style( 'wp-block-image' );

	return block_core_image_render_lightbox(
		$block_content,
		array(
			'attrs' => array(
				'id'       => $attachment_id,
				'lightbox' => array(
					'enabled' => true,
				),
			),
		)
	);
}
add_filter(
	'render_block_' . BLOCK_NAME,
	filter_featured_image_block( ... ),
	15, // After Duotone filter and Duotone styles are applied, which apparently is needed.
	2
);
