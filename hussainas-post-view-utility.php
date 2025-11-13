<?php
/**
 * Post View Counter Utility
 *
 * This file contains the procedural functions for tracking, displaying,
 * and managing post view counts.
 *
 * @package     HussainasPostViewCounter
 * @version     1.0.0
 * @author      Hussain Ahmed Shrabon
 * @license     MIT
 * @link        https://github.com/iamhussaina
 * @textdomain  hussainas
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the meta key used to store the view count.
if ( ! defined( 'HUSSAINAS_POST_VIEW_META_KEY' ) ) {
	define( 'HUSSAINAS_POST_VIEW_META_KEY', 'hussainas_post_view_count' );
}

/**
 * Tracks the post view.
 *
 * Fires on the 'wp' hook, which runs after the query is parsed.
 * This function increments the post meta value for view count.
 *
 * - Does not count for logged-in users who can edit posts.
 * - Only counts on single 'post' pages (is_singular('post')).
 * - Only counts for the main query.
 */
function hussainas_track_post_views() {
	// Ensure we are on a single post and it's the main query.
	if ( ! is_singular( 'post' ) || ! is_main_query() ) {
		return;
	}

    $post_id = get_the_ID();

    // Check if post ID is valid.
    if ( ! $post_id ) {
        return;
    }

	// Do not count views for administrators or editors.
	if ( current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Get the current view count.
	$count = (int) get_post_meta( $post_id, HUSSAINAS_POST_VIEW_META_KEY, true );
	
	// Increment the count.
	$count++;

	// Update the post meta.
	// update_post_meta() will add the meta key if it does not exist.
	update_post_meta( $post_id, HUSSAINAS_POST_VIEW_META_KEY, $count );
}
add_action( 'wp', 'hussainas_track_post_views' );

/**
 * Retrieves the raw view count for a specific post.
 *
 * @param int $post_id The ID of the post.
 * @return int The total view count.
 */
function hussainas_get_post_views( $post_id ) {
	$count = (int) get_post_meta( $post_id, HUSSAINAS_POST_VIEW_META_KEY, true );
	return $count;
}

/**
 * Retrieves the formatted view count string (e.g., "1,250 Views").
 *
 * @param int|null $post_id The ID of the post. Defaults to the current post ID.
 * @return string The formatted view count string.
 */
function hussainas_get_formatted_post_views( $post_id = null ) {
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}

	if ( empty( $post_id ) ) {
		return '';
	}

	$count = hussainas_get_post_views( $post_id );

	// Format the number.
	$formatted_count = number_format_i18n( $count );

	// Determine plural or singular for "View".
	$views_text = ( $count === 1 ) ? 'View' : 'Views';

	// Apply a filter if developers want to change the output format.
	return apply_filters(
		'hussainas_formatted_post_views',
		sprintf(
			'<span class="post-views-count" aria-label="%1$s %2$s">%1$s %2$s</span>',
			esc_html( $formatted_count ),
			esc_html( $views_text )
		),
		$count,
		$post_id
	);
}

/**
 * Displays (echoes) the formatted view count.
 *
 * This is a template function for easy use in theme files.
 *
 * @param int|null $post_id The ID of the post. Defaults to the current post ID.
 */
function hussainas_display_post_views( $post_id = null ) {
	echo hussainas_get_formatted_post_views( $post_id );
}

/**
 * Optional: Automatically adds the view count to the end of post content.
 *
 * This is commented out by default. Uncomment the add_filter() line
 * below this function if you want to enable this feature.
 *
 * @param string $content The post content.
 * @return string Modified post content with view count.
 */
function hussainas_add_views_to_content( $content ) {
	if ( is_singular( 'post' ) && is_main_query() ) {
		$views_html = hussainas_get_formatted_post_views( get_the_ID() );
		$content   .= '<div class="post-views-footer">' . $views_html . '</div>';
	}
	return $content;
}
// add_filter( 'the_content', 'hussainas_add_views_to_content' );


// --- Admin Column Functions ---

/**
 * Adds a 'Views' column to the 'Posts' list table in the admin.
 *
 * @param array $columns The existing columns.
 * @return array The modified columns.
 */
function hussainas_posts_column_views( $columns ) {
	$columns['post_views'] = 'Views';
	return $columns;
}
add_filter( 'manage_posts_columns', 'hussainas_posts_column_views' );

/**
 * Populates the 'Views' column with the view count.
 *
 * @param string $column_name The name of the column being processed.
 * @param int    $post_id     The ID of the current post.
 */
function hussainas_posts_custom_column_views( $column_name, $post_id ) {
	if ( $column_name === 'post_views' ) {
		$count = hussainas_get_post_views( $post_id );
		echo ( $count > 0 ) ? esc_html( number_format_i18n( $count ) ) : '0';
	}
}
add_action( 'manage_posts_custom_column', 'hussainas_posts_custom_column_views', 10, 2 );

/**
 * Registers the 'Views' column as sortable.
 *
 * @param array $columns The existing sortable columns.
 * @return array The modified sortable columns.
 */
function hussainas_register_sortable_views_column( $columns ) {
	$columns['post_views'] = 'post_views';
	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'hussainas_register_sortable_views_column' );

/**
 * Handles the sorting logic for the 'Views' column in the admin.
 *
 * Modifies the main query to order by the numeric meta value.
 *
 * @param WP_Query $query The main WP_Query object.
 */
function hussainas_views_column_orderby( $query ) {
	// Check if we are in the admin, this is the main query,
	// and the orderby request is for 'post_views'.
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->get( 'orderby' ) === 'post_views' ) {
		$query->set( 'meta_key', HUSSAINAS_POST_VIEW_META_KEY );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'hussainas_views_column_orderby' );
