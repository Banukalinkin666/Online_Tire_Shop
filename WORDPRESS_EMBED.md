# Embed the Tire Fitment Finder in Your WordPress Site

Use the shortcode on your **existing WordPress site** to show the tire finder (e.g. the one hosted on Render).

## Option A: Tire finder hosted elsewhere (e.g. Render) – recommended

Your app is already live at **https://online-tire-shop-pro.onrender.com** (or your own URL). Embed it in WordPress with an iframe.

### 1. Install the plugin

1. In your project, the plugin file is **`wordpress-plugin.php`**.
2. On your WordPress server, create a plugin folder and put the file there:
   - **Path:** `wp-content/plugins/tire-fitment-finder/tire-fitment-finder.php`
3. Copy the **contents** of `wordpress-plugin.php` into that file (or upload the file).
4. In WordPress admin go to **Plugins** and **Activate** “Tire Fitment Finder”.

### 2. Set the default URL (optional)

1. Go to **Settings → Tire Fitment**.
2. In **Default embed URL** enter your tire finder URL, e.g.  
   `https://online-tire-shop-pro.onrender.com`
3. Click **Save**.

### 3. Add the shortcode to a page

1. Create or edit a **Page** (or Post).
2. Add the shortcode where you want the tire finder to appear:

   ```
   [tire_fitment]
   ```

   If you didn’t set a default URL, use the URL in the shortcode:

   ```
   [tire_fitment url="https://online-tire-shop-pro.onrender.com"]
   ```

3. Publish or update the page. The tire finder will load in an iframe on that page.

### Shortcode options

| Attribute | Example | Description |
|----------|---------|-------------|
| `url` | `url="https://online-tire-shop-pro.onrender.com"` | Tire finder app URL (overrides default). |
| `height` | `height="800"` | Iframe height in pixels (default: 900). |
| `height="full"` | `height="full"` | Iframe uses full viewport height. |
| `quote_form_id` | `quote_form_id="tire-finder-quote-form"` | ID of the element containing your quote form; page scrolls here when user clicks “Request a quote” (default: `tire-finder-quote-form`). |

**Examples:**

- `[tire_fitment]`  
  Uses default URL from Settings (or local app if no URL and files exist).

- `[tire_fitment url="https://online-tire-shop-pro.onrender.com"]`  
  Embeds that URL.

- `[tire_fitment url="https://online-tire-shop-pro.onrender.com" height="1000"]`  
  Same URL, 1000px tall.

- `[tire_fitment url="https://online-tire-shop-pro.onrender.com" height="full"]`  
  Same URL, full viewport height.

---

## Use your own “Request a quote” form (e.g. Fluent Forms)

When the finder is embedded in an **iframe**, clicking **“Request a quote”** inside the finder sends a message to your WordPress page. You can show your own form (e.g. Fluent Forms) on the same page and scroll to it when the user clicks the button.

**Steps:**

1. **Create your form** in Fluent Forms (or any plugin that provides a shortcode). Add fields such as Full name, Email, Phone, Message. Copy the shortcode (e.g. `[fluentform id="3"]`).

2. **On the same page** where you have the tire finder, add a **wrapper div** with the ID `tire-finder-quote-form` (or another ID you prefer) and put your form shortcode inside it:

   ```
   [tire_fitment url="https://online-tire-shop-pro.onrender.com"]

   <div id="tire-finder-quote-form">
     [fluentform id="3"]
   </div>
   ```

3. When the user finds a tire size and clicks **“Request a quote”** in the finder, the page will **smoothly scroll** to your form so they can fill it out.

**Optional:** If you use a different ID for the form container, pass it in the shortcode:

- `[tire_fitment url="..." quote_form_id="my-quote-form"]`  
  Then use `<div id="my-quote-form">[fluentform id="3"]</div>` on the same page.

The finder also sends **vehicle and tire data** to the parent page (vehicle year/make/model, front and rear tire sizes). If Fluent Forms or your theme supports hidden fields or pre-filling from URL/JS, you can use that data later to pre-fill or store with the submission.

---

## Option B: Tire finder on the same server as WordPress

If the **full tire finder app** (this repo: `public/`, `api/`, `app/`, etc.) is on the same server as WordPress:

1. Put the whole project in a folder WordPress can read, e.g.  
   `wp-content/plugins/tire-fitment-finder/` with the app files inside (e.g. `public/`, `api/`, `app/`, `config/`).
2. Use **`tire-fitment-finder.php`** as the main plugin file in that folder (as in Option A).
3. **Do not** set a default embed URL (leave it blank).
4. Use **`[tire_fitment]`** on a page. The plugin will load the local `public/index.php` instead of an iframe.

---

## Summary

- **Pre-created WordPress site + tire finder on Render:**  
  Use **Option A**: install plugin, set default URL (or use `url="..."` in shortcode), add `[tire_fitment]` to a page.
- **Tire finder files on same server as WordPress:**  
  Use **Option B**: install plugin and app files, add `[tire_fitment]` with no URL.
