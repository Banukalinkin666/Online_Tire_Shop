# Standalone site: quote request email (receiver)

When someone submits the **Request a quote** form on the standalone site (e.g. **https://online-tire-shop-pro.onrender.com**), the app can email the submission to a **receiver email** you configure.

## How to add / configure the receiver email

### Option 1: Render (production)

1. Open your service on **Render**: https://dashboard.render.com
2. Select the **online-tire-shop-pro** (or your tire finder) service.
3. Go to **Environment** (left sidebar).
4. Click **Add Environment Variable**.
5. Add:
   - **Key:** `QUOTE_NOTIFICATION_EMAIL`
   - **Value:** the email that should receive quote requests (e.g. `sales@yoursite.com`).
6. Optional – set the “From” address (some hosts require it):
   - **Key:** `QUOTE_MAIL_FROM`
   - **Value:** e.g. `noreply@yourdomain.com`
7. Click **Save Changes**. Render will redeploy; the new value is used after deploy.

Without `QUOTE_NOTIFICATION_EMAIL`, submissions are still saved to `data/quote-requests.json` but **no email is sent**.

### Option 2: Local development (.env)

1. In the project root, copy the example env file:
   ```bash
   copy .env.example .env
   ```
   (On Mac/Linux: `cp .env.example .env`.)

2. Edit **`.env`** and set the receiver email:
   ```
   QUOTE_NOTIFICATION_EMAIL=your@email.com
   ```

3. Optional – set the “From” address:
   ```
   QUOTE_MAIL_FROM=noreply@yourdomain.com
   ```

4. Run the app as usual. The API reads these variables and sends the notification email to `QUOTE_NOTIFICATION_EMAIL`.

## Summary

| Goal                         | What to set                          |
|-----------------------------|--------------------------------------|
| **Receiver email**          | `QUOTE_NOTIFICATION_EMAIL=email@example.com` (Render env or .env) |
| **From address** (optional) | `QUOTE_MAIL_FROM=noreply@yourdomain.com` |

If you still don’t receive emails on Render, the host may block PHP `mail()`. In that case use the **WordPress embed** and an SMTP plugin there, or add a transactional email provider (e.g. SendGrid/Mailgun) and send via their API instead of `mail()`.
