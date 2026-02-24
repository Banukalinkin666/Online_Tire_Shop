# Standalone site: quote request email (receiver)

When someone submits the **Request a quote** form on the standalone site (e.g. **https://online-tire-shop-pro.onrender.com**), the app emails the submission to the address you set in **QUOTE_NOTIFICATION_EMAIL**.

**Important:** PHP `mail()` does **not** work on Render and many other hosts. You must configure **SMTP** (e.g. Gmail) so emails are actually sent.

---

## 1. Receiver email

Set the address that should receive quote requests:

- **Render:** Environment → **QUOTE_NOTIFICATION_EMAIL** = `your@email.com`
- **Local:** In **`.env`**: `QUOTE_NOTIFICATION_EMAIL=your@email.com`

---

## 2. SMTP (required for delivery)

Without SMTP, no email is sent. You can use **Mailgun** (free tier), **Gmail**, or another SMTP provider.

### Mailgun (free tier – recommended)

**Free service:** 100 emails per day (about 3,000/month), no credit card required. Good for quote requests.

1. Sign up: [https://www.mailgun.com](https://www.mailgun.com) → start free.
2. Add a **sending domain** (or use the sandbox domain for testing).
3. Get SMTP credentials: **Sending** → **Domain settings** → your domain → **SMTP credentials** (create/reset password if needed). Username is usually like `postmaster@yourdomain.mailgun.org`.
4. Set in **Render** (Environment) or **`.env`**:

| Key | Value |
|-----|--------|
| **SMTP_HOST** | `smtp.mailgun.org` |
| **SMTP_PORT** | `587` |
| **SMTP_SECURE** | `tls` |
| **SMTP_USER** | Your Mailgun SMTP username (e.g. `postmaster@sandboxXXX.mailgun.org`) |
| **SMTP_PASS** | Your Mailgun SMTP password (from Domain settings → SMTP credentials) |

Set **QUOTE_MAIL_FROM** to an address on your verified domain (e.g. `noreply@yourdomain.com` or the sandbox sender Mailgun shows).

5. Save and redeploy. Quote request emails will be sent via Mailgun.

### Gmail SMTP

1. Use a Gmail account. Turn on **2-Step Verification**: Google Account → Security.
2. Create an **App Password**: [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords) → Mail, copy the 16-character password.
3. Set in **Render** or **`.env`**:

| Key | Value |
|-----|--------|
| **SMTP_HOST** | `smtp.gmail.com` |
| **SMTP_PORT** | `587` |
| **SMTP_SECURE** | `tls` |
| **SMTP_USER** | Your Gmail address |
| **SMTP_PASS** | The 16-character App Password |

**QUOTE_MAIL_FROM** = same Gmail address.

### Other SMTP providers

- **SendGrid:** Free tier available; use their SMTP host and credentials from the dashboard.
- **Outlook/Office365:** SMTP_HOST=`smtp.office365.com`, PORT=587, SMTP_SECURE=tls.

---

## Summary

| Goal | What to set |
|------|-------------|
| **Who receives quotes** | `QUOTE_NOTIFICATION_EMAIL=your@email.com` |
| **Send via Mailgun** | `SMTP_HOST=smtp.mailgun.org`, `SMTP_PORT=587`, `SMTP_USER` / `SMTP_PASS` from Mailgun dashboard |
| **Send via Gmail** | `SMTP_HOST=smtp.gmail.com`, `SMTP_PORT=587`, `SMTP_USER=your@gmail.com`, `SMTP_PASS=app-password` |
| **From address** | `QUOTE_MAIL_FROM` = sender address (e.g. noreply@yourdomain.com for Mailgun) |

If SMTP is not set, the app falls back to PHP `mail()`, which usually does **not** work on Render and you will not receive emails.
