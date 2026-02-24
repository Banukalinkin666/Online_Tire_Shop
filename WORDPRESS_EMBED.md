# Embed the Tire Fitment Finder in Your WordPress Site

Use the shortcode on your **existing WordPress site** to show the tire finder (e.g. the one hosted on Render).

---

## Iframe code (paste into any page)

If you prefer **raw HTML** instead of the shortcode (e.g. in a **Custom HTML** block or another CMS), use this iframe code. Replace `https://online-tire-shop-pro.onrender.com` with your tire finder URL if different.

```html
<div style="width: 100%;">
  <iframe
    src="https://online-tire-shop-pro.onrender.com"
    style="height: 900px; width: 100%; border: none; display: block;"
    title="Tire Fitment Finder"
  ></iframe>
</div>
```

- **Height:** Change `900px` to any value (e.g. `800px`) or use `100vh` for full viewport height.
- **With the plugin:** Using the shortcode `[tire_fitment]` is better on WordPress because it also adds the “Request a quote” form and email. The iframe code above only shows the finder.

---

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

## Request a quote (built-in popup and email)

When the finder is embedded in an **iframe**, clicking **“Request a quote”** opens a **popup** on your WordPress page with:

- **Vehicle and tire summary:** Year, Make, Model and selected tire size
- **Form fields:** Name, Email, Phone, Message
- **Request a quote** button

When the user submits the form, an **email** is sent to the address you set in **Settings → Tire Fitment** (“Quote request notification email”). The email includes vehicle, tire size, name, email, phone, and message.

**Steps:**

1. Add **`[tire_fitment]`** (or `[tire_fitment url="https://online-tire-shop-pro.onrender.com"]`) to your page. No extra form or div is needed.
2. In **Settings → Tire Fitment**, set **Quote request notification email** to the address that should receive quote requests (default: site admin email).
3. Save. When a visitor clicks “Request a quote” in the finder and submits the form, that email receives the full details.

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
