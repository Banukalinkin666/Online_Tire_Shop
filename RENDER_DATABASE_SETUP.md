# Setting Up Database in Render (Easiest Option!)

Yes! You can create a database directly in Render. Here's how:

---

## Step 1: Create PostgreSQL Database in Render (2 minutes)

### 1.1 Create Database Service

1. Go to your Render dashboard: https://dashboard.render.com
2. Click **"New +"** → **"PostgreSQL"**
3. Configure the database:
   - **Name**: `tire-fitment-db` (or your choice)
   - **Database**: `tire_shop` (this is important!)
   - **User**: Auto-generated (you can change it)
   - **Region**: Choose same region as your web service (recommended)
   - **PostgreSQL Version**: Use default (latest)
   - **Plan**: Select **"Free"** (for testing)
4. Click **"Create Database"**
5. Wait 1-2 minutes for database to be created

### 1.2 Get Connection Details

1. Once created, click on your database service
2. You'll see connection details:
   - **Internal Database URL**: This is what you'll use!
   - **Host**: Something like `dpg-xxxxx-a.oregon-postgres.render.com`
   - **Port**: `5432`
   - **Database**: `tire_shop`
   - **User**: Your username
   - **Password**: Click "Show" to see password (save this!)

**⚠️ Important**: Copy these details - you'll need them in Step 2!

---

## Step 2: Import Database Schema (3 minutes)

### Option A: Using Render Shell (Easiest)

1. In Render dashboard, go to your **database service**
2. Click **"Shell"** tab
3. You'll get a PostgreSQL command line interface
4. Copy the SQL from `sql/schema.sql` file
5. **Important**: You need to modify it for PostgreSQL first (see below)
6. Paste and run the SQL

### Option B: Using psql Command (Local)

1. Install PostgreSQL client tools on your computer (optional)
2. Use the **External Database URL** from Render
3. Connect using psql and import schema

### Option C: Using pgAdmin or GUI Tool

1. Download pgAdmin or another PostgreSQL GUI
2. Connect using the **External Database URL** from Render
3. Import the schema file

---

## Step 3: Convert SQL Schema for PostgreSQL (Important!)

Our schema is for MySQL, but Render uses PostgreSQL. We need to make small changes:

### Changes Needed:

1. **Remove** `ENGINE=InnoDB` and `CHARSET` clauses
2. **Change** `YEAR` type to `INTEGER`
3. **Remove** `AUTO_INCREMENT` (use `SERIAL` instead)
4. **Change** `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` syntax
5. **Adjust** some data types

---

## Step 4: Use Updated PostgreSQL Schema

I'll create a PostgreSQL-compatible version of the schema for you.

---

## Step 5: Connect Web Service to Database

When you create your web service in Render:

1. Use these environment variables:
   ```
   DB_HOST = (host from Render database - no port)
   DB_NAME = tire_shop
   DB_USER = (username from Render database)
   DB_PASS = (password from Render database)
   DB_PORT = 5432
   ```

2. **OR** use the Internal Database URL that Render provides (easier!)

---

## Alternative: Use MySQL on Render (Requires Custom Service)

Render doesn't offer managed MySQL, but you can:
1. Use an external MySQL service (PlanetScale, etc.)
2. Or create a custom service with MySQL (more complex)

**For simplicity, I recommend using Render's PostgreSQL!**
