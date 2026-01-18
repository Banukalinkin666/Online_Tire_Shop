# Create Render Web Service - Step by Step

Follow these exact steps to create your web service on Render.

---

## Step 1: Go to Render Dashboard

1. Go to https://dashboard.render.com
2. Make sure you're logged in

---

## Step 2: Create New Web Service

1. Click **"New +"** button (top right)
2. Click **"Web Service"** from the dropdown

---

## Step 3: Connect GitHub Repository

1. You should see your GitHub repositories listed
2. Find **"Online_Tire_Shop"** (or your repository name)
3. Click **"Connect"** next to it

**If you don't see your repository:**
- Click **"Configure account"** to connect GitHub
- Authorize Render to access your repositories
- Then select your repository

---

## Step 4: Configure Web Service

Fill in these **exact** settings:

### Basic Settings:
- **Name**: `tire-fitment-finder` (or your choice)
- **Region**: `Oregon` (same as your database, if possible)
- **Branch**: `main`
- **Root Directory**: (leave **EMPTY** - don't type anything)
- **Runtime**: Select **"PHP"** from dropdown

### Build & Deploy:
- **Build Command**: (leave **EMPTY**)
- **Start Command**: Copy this exactly:
  ```
  php -S 0.0.0.0:$PORT -t public
  ```
- **Plan**: Select **"Free"** (for testing)

---

## Step 5: Add Environment Variables

**This is important!** Scroll down to **"Environment Variables"** section.

Click **"Add Environment Variable"** for each of these (6 variables total):

### For Render PostgreSQL Database:

1. **Name**: `DB_TYPE`
   **Value**: `pgsql` ⚠️ **Important!**

2. **Name**: `DB_HOST`
   **Value**: Your database host from Render
   - Go to your database → "Connect" → Copy the host (without port)
   - Example: `dpg-xxxxx-a.oregon-postgres.render.com`

3. **Name**: `DB_NAME`
   **Value**: `tire_shop` (or the database name you set)

4. **Name**: `DB_USER`
   **Value**: Your database username from Render

5. **Name**: `DB_PASS`
   **Value**: Your database password from Render
   - Go to database → "Connect" → Click "Show" to reveal password

6. **Name**: `DB_PORT`
   **Value**: `5432`

7. **Name**: `IMPORT_ALLOWED`
   **Value**: `true` (this allows schema import - we'll remove it later)

---

## Step 6: Deploy

1. Scroll to bottom of page
2. Click **"Create Web Service"**
3. Render will start building and deploying
4. Wait 2-3 minutes
5. You'll see: **"Your service is live at: https://tire-fitment-finder.onrender.com"**

**🎉 Your site is deploying!**

---

## Step 7: Import Database Schema

Once your site is live:

1. Visit: `https://your-site.onrender.com/import-schema.php`
2. You should see the import page
3. Click **"Import Schema"** button
4. Wait for import to complete (should take a few seconds)
5. You should see success messages

**⚠️ IMPORTANT:** After import is complete, delete `import-schema.php` for security!

---

## Step 8: Test Your Site

1. Visit your main site: `https://your-site.onrender.com`
2. Test features:
   - **VIN Search**: Enter `1HGBH41JXMN109186`
   - **YMM Search**: Select Year `2020` → Make `Toyota` → Model `Camry`
3. Verify tires appear!

---

## Troubleshooting

### Database connection fails?

1. Go to Render → Your Web Service → **"Environment"** tab
2. Verify all 7 environment variables are set:
   - `DB_TYPE` = `pgsql` (not `mysql`!)
   - `DB_HOST` = host without port
   - `DB_NAME` = `tire_shop`
   - `DB_USER` = correct username
   - `DB_PASS` = correct password
   - `DB_PORT` = `5432`
   - `IMPORT_ALLOWED` = `true`

### Import page shows "Access denied"?

- Check that `IMPORT_ALLOWED=true` is set in environment variables
- Or add `?key=change-this-secret-key` to URL (temporary)

### Site shows error 500?

1. Go to Render → Your Web Service → **"Logs"** tab
2. Look for error messages
3. Common issues:
   - Database connection error → Check environment variables
   - Missing files → Check that all files were pushed to GitHub

---

## Success Checklist

- [ ] Web service created
- [ ] All 7 environment variables set correctly
- [ ] Service deployed successfully
- [ ] Site loads in browser
- [ ] Import schema page accessible
- [ ] Schema imported successfully
- [ ] VIN search works
- [ ] YMM search works
- [ ] Tires display correctly
- [ ] Deleted `import-schema.php` after import

---

**You're all set! 🚀**
