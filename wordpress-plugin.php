<?php
/**
 * Plugin Name: Tire Fitment Finder
 * Plugin URI: https://example.com/tire-fitment-finder
 * Description: Embed tire fitment finder into WordPress via shortcode
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tire-fitment-finder
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register shortcode for tire fitment finder
 * 
 * Usage: [tire_fitment]
 * 
 * Optional attributes:
 * - api_path: Custom path to API directory (default: plugin_dir_path/../api)
 */
function tire_fitment_shortcode($atts) {
    $atts = shortcode_atts([
        'api_path' => '',
    ], $atts, 'tire_fitment');
    
    // Get plugin directory path
    $plugin_dir = plugin_dir_path(__FILE__);
    
    // Determine API path
    // If not specified, assume API is one level up from plugin
    $api_base_path = !empty($atts['api_path']) 
        ? $atts['api_path'] 
        : dirname($plugin_dir) . '/api';
    
    // Determine app base path
    $app_base_path = dirname($plugin_dir);
    
    // Buffer output
    ob_start();
    
    // Include the main application file
    $app_file = $app_base_path . '/public/index.php';
    
    if (file_exists($app_file)) {
        // Set a constant so bootstrap knows it's WordPress
        if (!defined('TIRESHOP_WORDPRESS_MODE')) {
            define('TIRESHOP_WORDPRESS_MODE', true);
            define('TIRESHOP_API_BASE_PATH', $api_base_path);
            define('TIRESHOP_APP_BASE_PATH', $app_base_path);
        }
        
        include $app_file;
    } else {
        echo '<div class="tire-fitment-error"><p>Error: Tire Fitment application files not found.</p></div>';
    }
    
    return ob_get_clean();
}
add_shortcode('tire_fitment', 'tire_fitment_shortcode');

/**
 * Enqueue scripts and styles if shortcode is used on page
 */
function tire_fitment_enqueue_assets() {
    global $post;
    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'tire_fitment')) {
        // Tailwind CSS is loaded via CDN in the main HTML file
        // Alpine.js is also loaded via CDN
        // Custom CSS and JS will be loaded inline in the shortcode output
        
        // Optionally, you can enqueue custom CSS/JS files here if needed
        // wp_enqueue_style('tire-fitment-css', plugin_dir_url(__FILE__) . '../public/assets/css/main.css');
        // wp_enqueue_script('tire-fitment-js', plugin_dir_url(__FILE__) . '../public/assets/js/app.js', [], '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'tire_fitment_enqueue_assets');

/**
 * Add admin menu for configuration (optional)
 */
function tire_fitment_admin_menu() {
    add_options_page(
        'Tire Fitment Settings',
        'Tire Fitment',
        'manage_options',
        'tire-fitment-settings',
        'tire_fitment_settings_page'
    );
}
add_action('admin_menu', 'tire_fitment_admin_menu');

/**
 * Settings page callback (optional)
 */
function tire_fitment_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('tire_fitment_settings');
            do_settings_sections('tire_fitment_settings');
            submit_button('Save Settings');
            ?>
        </form>
        <div class="card">
            <h2>Usage</h2>
            <p>To embed the tire fitment finder in any post or page, use the shortcode:</p>
            <code>[tire_fitment]</code>
            <p>Optional attributes:</p>
            <ul>
                <li><code>api_path</code> - Custom path to API directory (default: auto-detect)</li>
            </ul>
        </div>
    </div>
    <?php
}
