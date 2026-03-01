# WP Blog Category Filter

A WordPress plugin that adds AJAX-powered category filtering to the default blog page, ensuring consistent post formatting and maintainable code.

## Features

- **AJAX Filtering**: Filter posts by category without page reloads
- **Consistent Formatting**: One place to maintain post display format
- **GeneratePress Integration**: Automatic compatibility with GP theme settings
- **Responsive Design**: Works on all device sizes
- **Pagination Support**: Navigate through filtered results
- **Customizable**: Extensive settings for display and behavior
- **Theme Integration**: Option to use your theme's post format
- **SEO Friendly**: Updates URL for bookmarking and sharing

## Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/wp-blog-category-filter/`
3. Activate the plugin through the WordPress admin
4. Configure settings at **Settings > Blog Filter**

## Usage

The plugin automatically adds category filter buttons to your blog page (the page showing your latest posts). No additional setup required!

### Settings Configuration

Access settings at **Settings > Blog Filter** to customize:

#### Filter Display
- **"All Posts" Button Text**: Customize the "show all" button
- **Loading Text**: Text shown during AJAX loading
- **Layout**: Horizontal or vertical button layout
- **Show Post Counts**: Display number of posts per category
- **Hide Empty Categories**: Don't show categories with no posts

#### Post Display
- **Use Theme Format**: Use your theme's `content.php` format
- **Show Featured Images**: Include post thumbnails
- **Image Size**: Choose thumbnail size
- **Show Categories**: Display post categories
- **Show Post Meta**: Show date and author
- **Content Display**: Excerpt or full content
- **Read More Link**: Show "Read More" button

#### Ordering & Pagination
- **Category Order**: Sort categories by name, count, or ID
- **Post Order**: Sort posts by date, title, etc.
- **Show Pagination**: Enable page navigation

## How It Works

### AJAX vs Direct Links Decision

This plugin uses **AJAX filtering** rather than direct category page links because:

1. **Consistency**: Posts maintain the same format and layout
2. **User Experience**: No page reloads, smooth filtering
3. **Maintainability**: One place to control post display
4. **Performance**: Faster navigation between categories
5. **SEO**: URLs update for bookmarking without losing position

### Post Format Control

The plugin provides two ways to control post formatting:

1. **Custom Format** (default): Uses the plugin's built-in post template
2. **Theme Format**: Uses your theme's `content.php` or `content-post.php`

## Customization

### CSS Classes

- `.wp-blog-filter-container`: Main filter container
- `.wp-blog-filter-btn`: Individual filter buttons
- `.wp-blog-filter-post`: Filtered post container
- `.wp-blog-filter-pagination`: Pagination controls

### JavaScript Events

The plugin triggers these events for custom JavaScript:

```javascript
$(document).on('wp-blog-filter-start', function(e, categoryId, page) {
    // Filter starting
});

$(document).on('wp-blog-filter-success', function(e, data) {
    // Filter completed successfully
});

$(document).on('wp-blog-filter-error', function(e, error) {
    // Filter failed
});
```

### PHP Filters

```php
// Modify filter buttons HTML
add_filter('wp_blog_filter_buttons_html', function($html, $categories) {
    // Customize button HTML
    return $html;
}, 10, 2);

// Modify individual post HTML
add_filter('wp_blog_filter_post_html', function($html, $post_id) {
    // Customize post HTML
    return $html;
}, 10, 2);
```

## GeneratePress Integration

This plugin includes seamless integration with the [GeneratePress](https://generatepress.com/) theme and GP Premium blog module. When GeneratePress is detected and integration is enabled, the plugin automatically uses your GeneratePress blog settings for:

### Automatic Settings Detection
- **Post Layout**: Uses GeneratePress blog content layout settings
- **Featured Images**: Respects GP image display and sizing settings
- **Content Display**: Uses GP excerpt/full content settings
- **Read More Links**: Uses GP read more button styling and text
- **Post Meta**: Shows/hides date, author, categories, tags based on GP settings
- **Loading Text**: Uses GP masonry loading text for consistency

### Layout Compatibility
- **Sidebar Layouts**: Works with all GeneratePress sidebar configurations
- **Container Widths**: Respects GP container width settings
- **Responsive Design**: Integrates with GP's mobile-first approach

### How to Enable
1. Install and activate GeneratePress theme
2. Install GP Premium (for blog module features)
3. Go to **Settings > Blog Filter**
4. Check **"Enable GeneratePress Integration"**
5. Configure your blog settings in **Customizer > Layout > Blog**

### Benefits
- **Consistent Styling**: Filter buttons match your GP theme design
- **Unified Settings**: One place to manage all blog display options
- **Theme Compatibility**: Automatic updates when you change GP settings
- **Performance**: No duplicate settings or conflicting styles

## Support

For support, please check:
1. WordPress.org support forums
2. GitHub issues (if applicable)
3. Plugin documentation

## Changelog

### 1.0.0
- Initial release
- AJAX category filtering
- Customizable post display
- Responsive design
- Pagination support
- Settings page

## License

GPL v2 or later

## Credits

Developed for maintainable, consistent blog filtering.