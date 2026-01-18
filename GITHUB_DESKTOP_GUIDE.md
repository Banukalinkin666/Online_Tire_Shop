# 🚀 Deploy to Render Using GitHub Desktop

This guide is specifically for users who have GitHub Desktop installed. It's much easier than using command line!

---

## Step 1: Push Code to GitHub with GitHub Desktop (5 minutes)

### 1.1 Initialize Repository in GitHub Desktop

1. Open **GitHub Desktop**
2. Click **"File"** → **"Add Local Repository"**
3. Click **"Choose..."** and navigate to: `F:\Online_Tire_Shop`
4. Click **"Add Repository"**

If it says "This directory does not appear to be a Git repository":
- Click **"Create a repository"** instead
- Choose the folder: `F:\Online_Tire_Shop`
- Click **"Create Repository"**

### 1.2 Commit Your Files

1. In GitHub Desktop, you'll see all your files on the left
2. At the bottom left, enter a commit message: `Initial commit: Tire Fitment Finder`
3. Click **"Commit to main"** button

### 1.3 Create GitHub Repository

1. Click **"Publish repository"** button (top right, or in the menu)
2. Configure:
   - ✅ **Name**: `tire-fitment-finder`
   - ✅ **Description**: `Tire fitment finder web application`
   - ❌ **Keep this code private**: Uncheck (or check if you want private)
3. Click **"Publish Repository"**

**That's it!** Your code is now on GitHub! ✅

---

## Step 2: Set Up Database (3 minutes)

### Option A: PlanetScale (Free MySQL - Recommended)

1. Go to https://planetscale.com and sign up (free)
2. Click **"Create database"**
3. Database name: `tire_shop`
4. Region: Choose closest to you
5. Click **"Create database"**
6. Go to **"Console"** tab
7. Copy the contents of `F:\Online_Tire_Shop\sql\schema.sql`
8. Paste into SQL console and click **"Run"**
9. Go to **"Settings"** → **"Passwords"** → Click **"New password"**
10. Copy the connection details:
    - **Host**: Something like `xxx.us-east-1.psdb.cloud`
    - **Database**: `tire_shop`
    - **Username**: Your username
    - **Password**: The password you just created
    - **Port**: `3306`

**Save these details! You'll need them in Step 4.**

### Option B: Your Existing MySQL

1. Import `sql/schema.sql` into your database using phpMyAdmin or MySQL Workbench
2. Note down connection details:
   - Host, Database name, Username, Password, Port (usually 3306)

---

## Step 3: Deploy to Render (5 minutes)

### 3.1 Create Render Account

1. Go to https://render.com
2. Click **"Get Started for Free"**
3. Sign up with **GitHub** (easiest option)
4. Authorize Render to access your GitHub account

### 3.2 Create Web Service

1. In Render dashboard, click **"New +"** → **"Web Service"**
2. You'll see your repositories - click **"Connect"** next to `tire-fitment-finder`
3. Render will show configuration options

### 3.3 Configure Service Settings

Fill in these exact values:

**Basic Settings:**
- **Name**: `tire-fitment-finder`
- **Region**: `Oregon` (or choose closest to you)
- **Branch**: `main`
- **Root Directory**: (leave **EMPTY** - don't type anything)

**Build & Deploy:**
- **Runtime**: Select **"PHP"** from dropdown
- **Build Command**: (leave **EMPTY**)
- **Start Command**: Copy and paste this exactly:
  ```
  php -S 0.0.0.0:$PORT -t public
  ```

**Plan:**
- Select **"Free"** (for testing)

### 3.4 Add Environment Variables

This is important! Scroll down to **"Environment Variables"** section:

Click **"Add Environment Variable"** for each of these:

1. **Name**: `DB_HOST`
   **Value**: Your database host (e.g., `xxx.us-east-1.psdb.cloud`)

2. **Name**: `DB_NAME`
   **Value**: `tire_shop`

3. **Name**: `DB_USER`
   **Value**: Your database username

4. **Name**: `DB_PASS`
   **Value**: Your database password

5. **Name**: `DB_PORT`
   **Value**: `3306` (or `5432` if using PostgreSQL)

**Double-check all 5 variables are added!**

### 3.5 Deploy

1. Scroll to bottom
2. Click **"Create Web Service"**
3. Wait 2-3 minutes while Render builds and deploys
4. You'll see progress in the dashboard
5. When complete, it will show: **"Your service is live at: https://tire-fitment-finder.onrender.com"**

**🎉 Your site is now live!**

---

## Step 4: Test Your Site (2 minutes)

1. Click the URL Render provided (or copy it)
2. You should see the Tire Fitment Finder homepage
3. Test VIN search:
   - Enter: `1HGBH41JXMN109186`
   - Click "Search by VIN"
   - Should show vehicle information and available trims
4. Test Year/Make/Model search:
   - Select: Year `2020` → Make `Toyota` → Model `Camry`
   - Click "Find Tires"
   - Should show matching tires

---

## 🔧 Troubleshooting

### Site won't load / Shows error page?

1. In Render dashboard, click your service
2. Click **"Logs"** tab
3. Look for error messages
4. Common issues:
   - **Database connection failed**: Check environment variables
   - **File not found**: Verify `Start Command` is correct
   - **500 Error**: Check logs for specific error

### Database connection fails?

1. Go to Render → Your Service → **"Environment"** tab
2. Verify all 5 environment variables are set:
   - DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT
3. Double-check the values match your database
4. For PlanetScale: Make sure you enabled "Public IP" access in PlanetScale settings

### API doesn't work / No tires showing?

1. Open your site in browser
2. Press **F12** to open Developer Tools
3. Click **"Console"** tab - look for JavaScript errors
4. Click **"Network"** tab - look for failed API requests (red)
5. Check that API endpoints are accessible

### Still having issues?

- Check Render **"Events"** tab for deployment errors
- Verify all files were pushed to GitHub (check in GitHub Desktop)
- Review `DEPLOYMENT.md` for detailed troubleshooting

---

## 🔄 Updating Your Site Later

After making code changes:

1. **In GitHub Desktop:**
   - Make your code changes in your editor
   - GitHub Desktop will show changed files
   - Enter commit message: e.g., "Fixed bug in tire matching"
   - Click **"Commit to main"**
   - Click **"Push origin"** button (top right)

2. **Render automatically redeploys:**
   - Go to Render dashboard
   - You'll see a new deployment starting automatically
   - Wait 2-3 minutes
   - Your changes are live!

---

## 📋 Quick Reference

**Your URLs:**
- **Live Site**: https://tire-fitment-finder.onrender.com (your actual URL may differ)
- **Render Dashboard**: https://dashboard.render.com
- **GitHub Repository**: https://github.com/YOUR_USERNAME/tire-fitment-finder

**Important Files:**
- `QUICK_START.md` - Quick reference guide
- `RENDER_SETUP.md` - Detailed setup instructions
- `DEPLOYMENT_CHECKLIST.md` - Checklist format
- `README.md` - Application documentation

---

## ✅ Success Checklist

- [ ] Code pushed to GitHub (via GitHub Desktop)
- [ ] Database created and schema imported
- [ ] Render account created
- [ ] Web service created on Render
- [ ] All 5 environment variables set
- [ ] Service deployed successfully
- [ ] Site loads in browser
- [ ] VIN search works
- [ ] Year/Make/Model search works
- [ ] Tires display correctly

---

**That's it! You're all set! 🎉**

Your Tire Fitment Finder is now live on Render and will automatically update whenever you push changes via GitHub Desktop.
