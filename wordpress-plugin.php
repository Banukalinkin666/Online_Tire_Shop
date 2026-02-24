<?php
/**
 * Plugin Name: Tire Fitment Finder
 * Plugin URI: https://example.com/tire-fitment-finder
 * Description: Embed tire fitment finder into WordPress via shortcode. Use a URL (e.g. your app on Render) or local app files.
 * Version: 1.1.0
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
 * Usage:
 *   [tire_fitment]                                    – uses default URL from settings, or local app if no URL set
 *   [tire_fitment url="https://your-app.onrender.com"] – embed from this URL (iframe)
 *   [tire_fitment url="..." height="800"]             – optional height in pixels
 *
 * Optional attributes:
 *   url              – Full URL to the tire finder app (e.g. https://online-tire-shop-pro.onrender.com). If set, embeds via iframe.
 *   height           – Iframe height: number (pixels) or "full" for 100vh (default: 900)
 *   api_path         – (Local only) Custom path to API directory when not using url
 *   quote_form_id    – ID of the element containing your quote form (e.g. Fluent Forms). When user clicks "Request a quote", the page scrolls to this element. Default: tire-finder-quote-form
 */
function tire_fitment_shortcode($atts) {
    $atts = shortcode_atts([
        'url'           => '',
        'height'        => '900',
        'api_path'      => '',
        'quote_form_id' => 'tire-finder-quote-form',
    ], $atts, 'tire_fitment');

    // Option 1: Embed by URL (iframe) – use when your tire finder is hosted elsewhere (e.g. Render)
    $embed_url = !empty($atts['url']) ? $atts['url'] : get_option('tire_fitment_embed_url', '');
    $embed_url = esc_url_raw(rtrim($embed_url, '/'));

    if (!empty($embed_url)) {
        $height = $atts['height'];
        if ($height === 'full' || $height === '100vh') {
            $style = 'height: 100vh; min-height: 700px; width: 100%; border: none; display: block;';
        } else {
            $h = absint($height);
            if ($h < 400) {
                $h = 900;
            }
            $style = sprintf('height: %dpx; width: 100%%; border: none; display: block;', $h);
        }
        $quote_form_id = sanitize_key($atts['quote_form_id']);
        if ($quote_form_id === '') {
            $quote_form_id = 'tire-finder-quote-form';
        }
        tire_fitment_maybe_enqueue_quote_form_script();
        return sprintf(
            '<div class="tire-fitment-embed" data-quote-form-id="%s" style="width: 100%%;"><iframe src="%s" style="%s" title="Tire Fitment Finder"></iframe></div>',
            esc_attr($quote_form_id),
            esc_attr($embed_url),
            esc_attr($style)
        );
    }

    // Option 2: Local app – include app files from server (app must be on same server as WordPress)
    $plugin_dir = plugin_dir_path(__FILE__);
    $api_base_path = !empty($atts['api_path'])
        ? $atts['api_path']
        : dirname($plugin_dir) . '/api';
    $app_base_path = dirname($plugin_dir);
    $app_file = $app_base_path . '/public/index.php';

    ob_start();
    if (file_exists($app_file)) {
        if (!defined('TIRESHOP_WORDPRESS_MODE')) {
            define('TIRESHOP_WORDPRESS_MODE', true);
            define('TIRESHOP_API_BASE_PATH', $api_base_path);
            define('TIRESHOP_APP_BASE_PATH', $app_base_path);
        }
        include $app_file;
    } else {
        echo '<div class="tire-fitment-error notice notice-warning inline"><p><strong>Tire Fitment Finder:</strong> ';
        echo 'App files not found on this server. Either add the shortcode attribute <code>url="https://your-tire-app.onrender.com"</code> ';
        echo 'to embed your hosted app, or install the app files under the plugin directory.</p></div>';
    }
    return ob_get_clean();
}
add_shortcode('tire_fitment', 'tire_fitment_shortcode');

/**
 * When embed is iframe, enqueue script so "Request a quote" in finder scrolls to WordPress form (e.g. Fluent Forms).
 */
function tire_fitment_maybe_enqueue_quote_form_script() {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    add_action('wp_footer', 'tire_fitment_quote_form_footer_script', 20);
}

/**
 * Output script: wrap quote form in a modal popup; on TIRE_FINDER_REQUEST_QUOTE show popup with vehicle + tire summary.
 */
function tire_fitment_quote_form_footer_script() {
    ?>
    <style>
    .tire-finder-quote-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999999; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box; }
    .tire-finder-quote-modal-overlay.is-open { display: flex !important; }
    .tire-finder-quote-modal-content { background: #fff; padding: 24px; border-radius: 8px; max-width: 520px; width: 100%; max-height: 90vh; overflow: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; }
    .tire-finder-quote-modal-close { position: absolute; top: 12px; right: 12px; background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #6b7280; padding: 4px; }
    .tire-finder-quote-modal-close:hover { color: #111; }
    .tire-finder-quote-summary { margin-bottom: 1rem; padding: 1rem; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 6px; font-size: 0.9375rem; }
    </style>
    <script>
    (function() {
        function getQuoteFormId() {
            var embed = document.querySelector('.tire-fitment-embed');
            return embed ? embed.getAttribute('data-quote-form-id') || 'tire-finder-quote-form' : 'tire-finder-quote-form';
        }
        function openModal() {
            var overlay = document.getElementById('tire-finder-quote-modal-overlay');
            if (overlay) overlay.classList.add('is-open');
        }
        function closeModal() {
            var overlay = document.getElementById('tire-finder-quote-modal-overlay');
            if (overlay) overlay.classList.remove('is-open');
        }
        function initModal() {
            var id = getQuoteFormId();
            var el = document.getElementById(id);
            if (!el || document.getElementById('tire-finder-quote-modal-overlay')) return;
            var overlay = document.createElement('div');
            overlay.id = 'tire-finder-quote-modal-overlay';
            overlay.className = 'tire-finder-quote-modal-overlay';
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('role', 'dialog');
            var content = document.createElement('div');
            content.className = 'tire-finder-quote-modal-content';
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'tire-finder-quote-modal-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Close');
            closeBtn.onclick = closeModal;
            overlay.onclick = function(e) { if (e.target === overlay) closeModal(); };
            content.onclick = function(e) { e.stopPropagation(); };
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
            content.appendChild(closeBtn);
            el.parentNode.insertBefore(overlay, el);
            content.appendChild(el);
            overlay.appendChild(content);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModal);
        } else {
            initModal();
        }
        window.addEventListener('message', function(event) {
            if (!event.data || event.data.type !== 'TIRE_FINDER_REQUEST_QUOTE') return;
            var id = getQuoteFormId();
            var el = document.getElementById(id);
            if (!el) return;

            var v = event.data.vehicle || {};
            var year = v.year || '';
            var make = v.make || '';
            var model = v.model || '';
            var trim = v.trim || '';
            var frontTire = event.data.frontTire || '';
            var rearTire = event.data.rearTire || '';

            var vehicleText = [year, make, model].filter(Boolean).join(' ');
            if (trim) vehicleText += ' - ' + trim;
            var tireText = frontTire;
            if (rearTire && rearTire !== frontTire) tireText += ' / ' + rearTire;

            var summaryId = 'tire-finder-quote-summary';
            var summary = document.getElementById(summaryId);
            if (!summary) {
                summary = document.createElement('div');
                summary.id = summaryId;
                summary.className = 'tire-finder-quote-summary';
                el.insertBefore(summary, el.firstChild);
            }
            summary.innerHTML = '<strong>Quote for:</strong> ' + (vehicleText || '—') + '<br><strong>Tire size:</strong> ' + (tireText || '—');

            var formDataStr = vehicleText + ' | Tire: ' + tireText;
            var formEl = el.querySelector('form');
            if (formEl) {
                var hidden = formEl.querySelector('input[name="tire_finder_data"], input[name="tire_finder_vehicle"], input[id*="tire_finder"]');
                if (hidden) hidden.value = formDataStr;
            }

            openModal();
        });
    })();
    </script>
    <?php
}

/**
 * Enqueue scripts and styles if shortcode is used on page (for local app mode)
 */
function tire_fitment_enqueue_assets() {
    global $post;
    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'tire_fitment')) {
        return;
    }
    // When using iframe (url), no extra assets needed. When using local include, assets come from index.php.
}
add_action('wp_enqueue_scripts', 'tire_fitment_enqueue_assets');

/**
 * Register settings for default embed URL
 */
function tire_fitment_register_settings() {
    register_setting('tire_fitment_settings', 'tire_fitment_embed_url', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
}
add_action('admin_init', 'tire_fitment_register_settings');

/**
 * Add admin menu
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
 * Settings page
 */
function tire_fitment_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $embed_url = get_option('tire_fitment_embed_url', '');
    if (isset($_POST['tire_fitment_embed_url']) && check_admin_referer('tire_fitment_settings')) {
        $embed_url = esc_url_raw(wp_unslash($_POST['tire_fitment_embed_url']));
        update_option('tire_fitment_embed_url', $embed_url);
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Tire Fitment Finder</h1>
        <form method="post" action="">
            <?php wp_nonce_field('tire_fitment_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tire_fitment_embed_url">Default embed URL</label></th>
                    <td>
                        <input type="url" name="tire_fitment_embed_url" id="tire_fitment_embed_url"
                               value="<?php echo esc_attr($embed_url); ?>"
                               class="regular-text" placeholder="https://online-tire-shop-pro.onrender.com"/>
                        <p class="description">If your tire finder is hosted elsewhere (e.g. Render), enter its URL here. Then <code>[tire_fitment]</code> will embed it in an iframe. Leave blank to use local app files (if installed).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
        </form>
        <div class="card" style="max-width: 640px; margin-top: 20px;">
            <h2>Shortcode usage</h2>
            <p>Add the shortcode to any post or page:</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><code>[tire_fitment]</code> – uses the default URL above, or local app if no URL is set.</li>
                <li><code>[tire_fitment url="https://online-tire-shop-pro.onrender.com"]</code> – embed from this URL.</li>
                <li><code>[tire_fitment url="https://..." height="800"]</code> – set iframe height in pixels.</li>
                <li><code>[tire_fitment url="https://..." height="full"]</code> – full viewport height.</li>
            </ul>
        </div>
    </div>
    <?php
}
