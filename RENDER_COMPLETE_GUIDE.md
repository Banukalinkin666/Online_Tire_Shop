# Complete Guide: Deploy to Render with PostgreSQL Database

This guide walks you through creating both the database AND web service in Render.

---

## Step 1: Create PostgreSQL Database in Render (3 minutes)

### 1.1 Create Database Service

1. Go to https://dashboard.render.com
2. Make sure you're signed in
3. Click **"New +"** button (top right)
4. Click **"PostgreSQL"** from the dropdown

### 1.2 Configure Database

Fill in these settings:

- **Name**: `tire-fitment-db` (or your choice)
- **Database**: `tire_shop` ⚠️ **Important: Type this exactly!**
- **User**: Leave default or change it
- **Region**: `Oregon` (or choose closest to you)
- **PostgreSQL Version**: Use default (latest)
- **Plan**: Select **"Free"** (for testing)

5. Scroll down and click **"Create Database"**
6. Wait 1-2 minutes for database to be created

### 1.3 Get Connection Details

Once the database is ready:

1. Click on your database service (`tire-fitment-db`)
2. You'll see **"Connection Info"** section with:
   - **Internal Database URL**: `postgresql://user:pass@host:5432/tire_shop`
   - **Host**: Something like `dpg-xxxxx-a.oregon-postgres.render.com`
   - **Port**: `5432`
   - **Database**: `tire_shop`
   - **User**: Your username
   - **Password**: Click **"Show"** to reveal it

**📝 Save these details!** You'll need them in Step 3.

---

## Step 2: Import Database Schema (5 minutes)

### Option A: Using Render Shell (Recommended)

1. In your database service page, click **"Shell"** tab
2. You'll get a PostgreSQL command prompt
3. Open `sql/schema_postgresql.sql` file from your project
4. Copy **ALL** the SQL content
5. Paste into the Shell and press Enter
6. Wait for it to complete (should see "INSERT 0 10" etc.)

**✅ Schema imported!**

### Option B: Using pgAdmin (Alternative)

1. Download pgAdmin: https://www.pgadmin.org/download/
2. Install and open pgAdmin
3. Right-click "Servers" → "Register" → "Server"
4. In "Connection" tab:
   - **Host**: Your Render database host
   - **Port**: `5432`
   - **Database**: `tire_shop`
   - **Username**: Your Render database user
   - **Password**: Your Render database password
5. Click "Save" to connect
6. Right-click database `tire_shop` → "Query Tool"
7. Copy/paste contents of `sql/schema_postgresql.sql`
8. Click "Execute" (▶️ button)

---

## Step 3: Create Web Service in Render (5 minutes)

### 3.1 Connect GitHub Repository

1. In Render dashboard, click **"New +"** → **"Web Service"**
2. If not connected, click **"Connect account"** to connect GitHub
3. Select your repository: **`Online_Tire_Shop`**
4. Click **"Connect"**

### 3.2 Configure Web Service

Fill in these exact settings:

**Basic Settings:**
- **Name**: `tire-fitment-finder` (or your choice)
- **Region**: **Same region as your database** (Oregon if you chose that)
- **Branch**: `main`
- **Root Directory**: (leave **EMPTY** - don't type anything)

**Build & Deploy:**
- **Runtime**: Select **"PHP"** from dropdown
- **Build Command**: (leave **EMPTY**)
- **Start Command**: Copy this exactly:
  ```
  php -S 0.0.0.0:$PORT -t public
  ```
- **Plan**: Select **"Free"** (for testing)

### 3.3 Add Environment Variables

Scroll down to **"Environment Variables"** section:

Click **"Add Environment Variable"** for each of these:

1. **Name**: `DB_TYPE`
   **Value**: `pgsql` ⚠️ **Important for PostgreSQL!**

2. **Name**: `DB_HOST`
   **Value**: Your database host (from Step 1.3, without port)
   - Example: `dpg-xxxxx-a.oregon-postgres.render.com`

3. **Name**: `DB_NAME`
   **Value**: `tire_shop`

4. **Name**: `DB_USER`
   **Value**: Your database username (from Step 1.3)

5. **Name**: `DB_PASS`
   **Value**: Your database password (from Step 1.3)

6. **Name**: `DB_PORT`
   **Value**: `5432`

**Double-check all 6 variables are added correctly!**

### 3.4 Deploy

1. Scroll to bottom of page
2. Click **"Create Web Service"**
3. Render will start building and deploying
4. Wait 2-3 minutes
5. You'll see: **"Your service is live at: https://tire-fitment-finder.onrender.com"**

**🎉 Your site is deploying!**

---

## Step 4: Test Your Site (2 minutes)

1. Click the URL Render provided (or copy it)
2. You should see the Tire Fitment Finder homepage
3. Test features:
   - **VIN Search**: Enter `1HGBH41JXMN109186`
   - **YMM Search**: Select Year `2020` → Make `Toyota` → Model `Camry`
4. Verify tires appear!

---

## Troubleshooting

### Database connection fails?

1. Go to Render → Your Web Service → **"Environment"** tab
2. Verify all 6 environment variables:
   - `DB_TYPE` = `pgsql` (not `mysql`!)
   - `DB_HOST` = host without port
   - `DB_NAME` = `tire_shop`
   - `DB_USER` = correct username
   - `DB_PASS` = correct password
   - `DB_PORT` = `5432`

### "No tires found" or empty results?

1. Check if schema was imported correctly:
   - Go to database → "Shell" tab
   - Run: `SELECT COUNT(*) FROM tires;`
   - Should show 14 rows
2. If 0 rows, re-import `sql/schema_postgresql.sql`

### Site shows error 500?

1. Go to Render → Your Web Service → **"Logs"** tab
2. Look for error messages
3. Common issues:
   - Database connection error → Check environment variables
   - Missing files → Check that all files were pushed to GitHub
   - PHP errors → Check logs for specific error

### Still having issues?

- Check Render **"Events"** tab for deployment errors
- Verify database is running (should show "Available" status)
- Review logs in both database and web service

---

## Success Checklist

- [ ] Database created in Render
- [ ] Schema imported (tables exist)
- [ ] Sample data imported (14 tires, 10 vehicles)
- [ ] Web service created
- [ ] All 6 environment variables set correctly
- [ ] Service deployed successfully
- [ ] Site loads without errors
- [ ] Database connection works
- [ ] VIN search works
- [ ] YMM search works
- [ ] Tires display correctly

---

## Quick Reference

**Your URLs:**
- **Live Site**: https://tire-fitment-finder.onrender.com (your URL may differ)
- **Render Dashboard**: https://dashboard.render.com
- **Database**: Render → `tire-fitment-db`
- **Web Service**: Render → `tire-fitment-finder`

**Important Files:**
- `sql/schema_postgresql.sql` - Use this for Render PostgreSQL (NOT schema.sql)
- `RENDER_COMPLETE_GUIDE.md` - This guide

---

**You're all set! Your application is now live with a Render-managed database! 🚀**
