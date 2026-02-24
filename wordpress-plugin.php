<?php
/**
 * Plugin Name: Tire Fitment Finder
 * Plugin URI: https://example.com/tire-fitment-finder
 * Description: Embed tire fitment finder into WordPress via shortcode. Use a URL (e.g. your app on Render) or local app files.
 * Version: 1.3.0
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
 */
function tire_fitment_shortcode($atts) {
    $atts = shortcode_atts([
        'url'    => '',
        'height' => '900',
        'api_path' => '',
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
        $iframe_html = sprintf(
            '<div class="tire-fitment-embed" style="width: 100%%;"><iframe src="%s" style="%s" title="Tire Fitment Finder"></iframe></div>',
            esc_attr($embed_url),
            esc_attr($style)
        );
        tire_fitment_enqueue_quote_script();
        return $iframe_html . tire_fitment_get_quote_modal_html();
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
 * Enqueue quote modal script via WordPress (so it is not stripped from shortcode output).
 */
function tire_fitment_enqueue_quote_script() {
    static $done = false;
    if ($done) return;
    $done = true;

    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'tireFitmentQuote', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('tire_fitment_quote'),
    ]);

    $script = <<<'JS'
(function() {
    var cfg = typeof tireFitmentQuote !== 'undefined' ? tireFitmentQuote : { ajaxUrl: '', nonce: '' };

    function showInlineForm() {
        var el = document.getElementById('tire-finder-quote-inline');
        if (el) { el.style.display = 'block'; el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    }
    function hideInlineForm() {
        var el = document.getElementById('tire-finder-quote-inline');
        if (el) el.style.display = 'none';
    }
    function showMsg(text, isError) {
        var msgEl = document.getElementById('tire-finder-quote-msg');
        if (!msgEl) return;
        msgEl.textContent = text;
        msgEl.className = 'tire-finder-quote-msg ' + (isError ? 'error' : 'success');
        msgEl.style.display = 'block';
    }

    window.addEventListener('message', function(event) {
        if (!event.data || event.data.type !== 'TIRE_FINDER_REQUEST_QUOTE') return;
        var container = document.getElementById('tire-finder-quote-inline');
        if (!container) return;

        var v = event.data.vehicle || {};
        var year = v.year || '', make = v.make || '', model = v.model || '', trim = v.trim || '';
        var frontTire = event.data.frontTire || '', rearTire = event.data.rearTire || '';
        var vehicleText = [year, make, model].filter(Boolean).join(' ');
        if (trim) vehicleText += ' - ' + trim;
        var tireText = frontTire;
        if (rearTire && rearTire !== frontTire) tireText += ' / ' + rearTire;

        var summaryEl = document.getElementById('tire-finder-quote-summary');
        var formEl = document.getElementById('tire-finder-quote-form');
        if (summaryEl) {
            summaryEl.innerHTML = '<strong>Quote for:</strong> ' + (vehicleText || '—') + '<br><strong>Tire size:</strong> ' + (tireText || '—');
            summaryEl.style.display = 'block';
        }
        var vehicleInput = document.getElementById('tire_finder_vehicle');
        var tireInput = document.getElementById('tire_finder_tire');
        if (vehicleInput) vehicleInput.value = vehicleText;
        if (tireInput) tireInput.value = tireText;
        if (formEl) formEl.reset();
        if (vehicleInput) vehicleInput.value = vehicleText;
        if (tireInput) tireInput.value = tireText;
        var msgEl = document.getElementById('tire-finder-quote-msg');
        if (msgEl) { msgEl.style.display = 'none'; msgEl.textContent = ''; }

        showInlineForm();
    });

    document.body.addEventListener('submit', function(e) {
        if (!e.target || e.target.id !== 'tire-finder-quote-form' || !cfg.ajaxUrl) return;
        e.preventDefault();
        var f = e.target;
        showMsg('', false);
        var msgEl = document.getElementById('tire-finder-quote-msg');
        if (msgEl) msgEl.style.display = 'none';
        var submitBtn = f.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Sending...'; }
        var formData = new FormData(f);
        formData.append('action', 'tire_fitment_submit_quote');
        formData.append('nonce', cfg.nonce);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.ajaxUrl);
        xhr.onload = function() {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Request a quote'; }
            var res;
            try { res = JSON.parse(xhr.responseText || '{}'); } catch (err) { res = {}; }
            var msg = (res.data && res.data.message) ? res.data.message : (res.message || '');
            var m = document.getElementById('tire-finder-quote-msg');
            if (m) {
                m.textContent = msg || (res.success ? 'Thank you.' : 'Something went wrong.');
                m.className = 'tire-finder-quote-msg ' + (res.success ? 'success' : 'error');
                m.style.display = 'block';
            }
        };
        xhr.onerror = function() {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Request a quote'; }
            var m = document.getElementById('tire-finder-quote-msg');
            if (m) { m.textContent = 'Network error. Please try again.'; m.className = 'tire-finder-quote-msg error'; m.style.display = 'block'; }
        };
        xhr.send(formData);
    });
})();
JS;
    wp_add_inline_script('jquery', $script, 'after');
}

/**
 * Return built-in quote form HTML: inline block below the iframe (no popup).
 */
function tire_fitment_get_quote_modal_html() {
    ob_start();
    ?>
    <style>
    .tire-finder-quote-inline { display: none; margin-top: 24px; padding: 24px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .tire-finder-quote-inline .tire-finder-quote-summary { margin-bottom: 1rem; padding: 1rem; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 6px; font-size: 0.9375rem; }
    .tire-finder-quote-inline .tire-finder-quote-form label { display: block; margin-bottom: 4px; font-weight: 600; font-size: 14px; }
    .tire-finder-quote-inline .tire-finder-quote-form input, .tire-finder-quote-inline .tire-finder-quote-form textarea { width: 100%; max-width: 400px; padding: 8px 12px; margin-bottom: 12px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
    .tire-finder-quote-inline .tire-finder-quote-msg { margin-bottom: 12px; padding: 8px 12px; border-radius: 6px; font-size: 14px; max-width: 400px; }
    .tire-finder-quote-inline .tire-finder-quote-msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .tire-finder-quote-inline .tire-finder-quote-msg.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .tire-finder-quote-inline button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .tire-finder-quote-inline button[type="submit"]:hover { background: #1d4ed8; }
    .tire-finder-quote-inline button[type="submit"]:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
    <div id="tire-finder-quote-inline" class="tire-finder-quote-inline">
        <div id="tire-finder-quote-summary" class="tire-finder-quote-summary" style="display: none;"></div>
        <form id="tire-finder-quote-form" class="tire-finder-quote-form">
            <input type="hidden" name="tire_finder_vehicle" id="tire_finder_vehicle" value="">
            <input type="hidden" name="tire_finder_tire" id="tire_finder_tire" value="">
            <div>
                <label for="tire_finder_name">Name <span style="color:#dc2626">*</span></label>
                <input type="text" name="name" id="tire_finder_name" required placeholder="Your name">
            </div>
            <div>
                <label for="tire_finder_email">Email <span style="color:#dc2626">*</span></label>
                <input type="email" name="email" id="tire_finder_email" required placeholder="your@email.com">
            </div>
            <div>
                <label for="tire_finder_phone">Phone</label>
                <input type="tel" name="phone" id="tire_finder_phone" placeholder="Your phone number">
            </div>
            <div>
                <label for="tire_finder_message">Message</label>
                <textarea name="message" id="tire_finder_message" rows="3" placeholder="Any additional details..."></textarea>
            </div>
            <div id="tire-finder-quote-msg" class="tire-finder-quote-msg" style="display: none;"></div>
            <button type="submit">Request a quote</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
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
 * Register settings for default embed URL and quote notification email
 */
function tire_fitment_register_settings() {
    register_setting('tire_fitment_settings', 'tire_fitment_embed_url', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('tire_fitment_settings', 'tire_fitment_quote_email', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
    ]);
}
add_action('admin_init', 'tire_fitment_register_settings');

/**
 * AJAX: submit quote request and send email
 */
function tire_fitment_ajax_submit_quote() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'tire_fitment_quote')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $vehicle = isset($_POST['tire_finder_vehicle']) ? sanitize_text_field(wp_unslash($_POST['tire_finder_vehicle'])) : '';
    $tire    = isset($_POST['tire_finder_tire']) ? sanitize_text_field(wp_unslash($_POST['tire_finder_tire'])) : '';

    if (empty($name) || empty($email)) {
        wp_send_json_error(['message' => 'Please enter your name and email.']);
    }
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address.']);
    }

    $to = get_option('tire_fitment_quote_email', get_option('admin_email'));
    if (!is_email($to)) {
        $to = get_option('admin_email');
    }

    $subject = 'Tire quote request: ' . $vehicle;
    $body    = "A quote request has been submitted.\n\n";
    $body   .= "--- Vehicle & tire ---\n";
    $body   .= "Vehicle: " . $vehicle . "\n";
    $body   .= "Tire size: " . $tire . "\n\n";
    $body   .= "--- Contact ---\n";
    $body   .= "Name: " . $name . "\n";
    $body   .= "Email: " . $email . "\n";
    $body   .= "Phone: " . $phone . "\n\n";
    $body   .= "--- Message ---\n" . $message . "\n";

    $from_email = get_option('tire_fitment_quote_from_email', '');
    if (!is_email($from_email)) {
        $from_email = get_option('admin_email');
    }
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'From: Tire Fitment <' . $from_email . '>',
    ];

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(['message' => 'Thank you. Your quote request has been sent. We will get back to you soon.']);
    } else {
        wp_send_json_error(['message' => 'Unable to send your request. Please try again or contact us directly.']);
    }
}
add_action('wp_ajax_tire_fitment_submit_quote', 'tire_fitment_ajax_submit_quote');
add_action('wp_ajax_nopriv_tire_fitment_submit_quote', 'tire_fitment_ajax_submit_quote');

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
    $quote_email = get_option('tire_fitment_quote_email', get_option('admin_email'));
    if (isset($_POST['tire_fitment_embed_url']) && check_admin_referer('tire_fitment_settings')) {
        $embed_url = esc_url_raw(wp_unslash($_POST['tire_fitment_embed_url']));
        update_option('tire_fitment_embed_url', $embed_url);
        $quote_email = isset($_POST['tire_fitment_quote_email']) ? sanitize_email(wp_unslash($_POST['tire_fitment_quote_email'])) : $quote_email;
        update_option('tire_fitment_quote_email', $quote_email);
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
                <tr>
                    <th scope="row"><label for="tire_fitment_quote_email">Quote request notification email</label></th>
                    <td>
                        <input type="email" name="tire_fitment_quote_email" id="tire_fitment_quote_email"
                               value="<?php echo esc_attr($quote_email); ?>"
                               class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>"/>
                        <p class="description">Email address where quote requests (Name, Email, Phone, Message, Vehicle, Tire size) are sent when a user clicks "Request a quote" and submits the form. Default: site admin email.</p>
                        <p class="description">If you don't receive emails, many hosts block PHP mail. Install an SMTP plugin (e.g. <strong>WP Mail SMTP</strong> or <strong>FluentSMTP</strong>) and send via Gmail, SendGrid, or your provider so messages are delivered reliably.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
        </form>
        <div class="card" style="max-width: 640px; margin-top: 20px;">
            <h2>Shortcode usage</h2>
            <p>Add the shortcode to any post or page. When the user clicks "Request a quote" in the tire finder, the form appears in the area below the finder (vehicle/tire details and fields: Name, Email, Phone, Message). Submissions are sent to the email address above.</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><code>[tire_fitment]</code> – uses the default URL above, or local app if no URL is set.</li>
                <li><code>[tire_fitment url="https://online-tire-shop-pro.onrender.com"]</code> – embed from this URL.</li>
                <li><code>[tire_fitment url="https://..." height="800"]</code> – set iframe height in pixels.</li>
                <li><code>[tire_fitment url="https://..." height="full"]</code> – full viewport height.</li>
            </ul>
        </div>
        <div class="card" style="max-width: 640px; margin-top: 16px;">
            <h2>Iframe code</h2>
            <p>To embed using raw HTML (e.g. in a <strong>Custom HTML</strong> block or another site), copy the code below. Replace the URL if needed. Using the shortcode is recommended so the "Request a quote" form works.</p>
            <?php
            $iframe_url = !empty($embed_url) ? $embed_url : 'https://online-tire-shop-pro.onrender.com';
            $iframe_code = '<div style="width: 100%;">
  <iframe
    src="' . $iframe_url . '"
    style="height: 900px; width: 100%; border: none; display: block;"
    title="Tire Fitment Finder"
  ></iframe>
</div>';
            ?>
            <textarea id="tire_fitment_iframe_code" readonly rows="8" style="width: 100%; font-family: monospace; font-size: 12px;"><?php echo esc_textarea($iframe_code); ?></textarea>
            <p class="description">Change <code>900px</code> to another height (e.g. <code>800px</code>) or <code>100vh</code> for full viewport height.</p>
        </div>
    </div>
    <?php
}
