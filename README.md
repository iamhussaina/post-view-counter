# Post View Counter Utility

A lightweight, high-performance, and plugin-free PHP utility for WordPress to track and display post view counts.

This utility is designed to be included directly within a WordPress theme. It provides core functionality for tracking post views, displaying the count on the frontend, and adding a sortable "Views" column in the WordPress admin area.

## Features

* **Plugin-Free:** No need for another plugin. Integrates directly into your theme.
* **Lightweight:** Minimal, procedural code focused on performance.
* **Smart Tracking:**
    * Tracks views only on single `post` pages.
    * Does **not** count views from logged-in administrators or editors, ensuring more accurate public view counts.
* **Admin Column:** Adds a "Views" column to the "All Posts" screen in the admin.
* **Sortable Column:** The "Views" column is fully sortable, allowing you to quickly find your most popular posts.
* **Template Functions:** Easy-to-use functions for displaying the view count anywhere in your theme templates.

## Installation

1.  **Download:** Download the `hussainas-post-view-counter` directory.
2.  **Include:** Add the following line to your theme's `functions.php` file:

    ```php
    // Load the Post View Counter Utility
    require_once( get_template_directory() . 'inc/hussainas-post-view-utility.php' );
    ```

That's it! The utility will now start tracking views.

---

## How to Use

### 1. Tracking

Tracking is enabled automatically once the utility is installed. It will start counting views for non-admin visitors on your single post pages.

### 2. Displaying Views in Theme Templates

You can display the view count anywhere in your theme files (like `single.php`, `content.php`, or in your post meta information).

Use the following function inside The Loop:

```php
<?php
if ( function_exists( 'hussainas_display_post_views' ) ) {
    hussainas_display_post_views();
}
?>
```

This will output a formatted string, for example: `<span class="post-views-count" aria-label="1,250 Views">1,250 Views</span>`

### 3. Advanced Template Functions

For more control, you can use these helper functions:

* **`hussainas_get_formatted_post_views( $post_id )`**
    * Returns the formatted HTML string.
    * `@param int $post_id` (Optional) The ID of the post. Defaults to the current post in The Loop.
    * **Usage:** `$views = hussainas_get_formatted_post_views( get_the_ID() );`

* **`hussainas_get_post_views( $post_id )`**
    * Returns the raw, unformatted view count as an integer.
    * `@param int $post_id` (Required) The ID of the post.
    * **Usage:** `$count = hussainas_get_post_views( get_the_ID() );`

### 4. Automatic Display (Optional)

If you want to automatically append the view count to the end of all post content, you can enable the `the_content` filter.

1.  Open `inc/hussainas-post-view-utility.php`.
2.  Uncomment add_filter( 'the_content', 'hussainas_add_views_to_content' ):

    ```php
    // FROM:
    // add_filter( 'the_content', 'hussainas_add_views_to_content' );
    
    // TO:
    add_filter( 'the_content', 'hussainas_add_views_to_content' );
    ```

This will automatically add the view count wrapped in a `<div class="post-views-footer">...</div>` at the bottom of every post.
