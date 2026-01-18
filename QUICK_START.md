# 🚀 Quick Start: Deploy to Render in 10 Minutes

## Prerequisites
- GitHub account
- Render account (sign up at https://render.com - free)
- GitHub Desktop (recommended) or Git command line

---

## Step 1: Push to GitHub (2 minutes)

### Option A: Using GitHub Desktop (Easier! 🎉)

1. Open **GitHub Desktop**
2. Click **"File"** → **"Add Local Repository"**
3. Choose folder: `F:\Online_Tire_Shop`
4. If not a Git repo yet, click **"Create a repository"** instead
5. Enter commit message: `Initial commit: Tire Fitment Finder`
6. Click **"Commit to main"**
7. Click **"Publish repository"** button
8. Name: `tire-fitment-finder`
9. Click **"Publish Repository"**

**✅ Done! Code is on GitHub!**

### Option B: Using Command Line

Open PowerShell/Terminal in your project folder (`F:\Online_Tire_Shop`):

```powershell
# Initialize Git (if not done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: Tire Fitment Finder"

# Create repository on GitHub first, then:
# Replace YOUR_USERNAME with your GitHub username
git remote add origin https://github.com/YOUR_USERNAME/tire-fitment-finder.git

# Push to GitHub
git branch -M main
git push -u origin main
```

**Don't have a GitHub repository yet?**
1. Go to https://github.com/new
2. Repository name: `tire-fitment-finder`
3. Click "Create repository"
4. Then run the commands above or use GitHub Desktop

**👉 For GitHub Desktop users, see `GITHUB_DESKTOP_GUIDE.md` for detailed instructions!**

---

## Step 2: Set Up Database (3 minutes)

### Option A: Use PlanetScale (Free MySQL - Recommended)

1. Go to https://planetscale.com and sign up
2. Create a database named `tire_shop`
3. Get connection details (host, username, password)
4. Import schema:
   - Go to "Console" tab
   - Copy contents of `sql/schema.sql`
   - Paste and run in SQL console

### Option B: Use Your Existing MySQL

1. Import `sql/schema.sql` into your database
2. Note down connection details

---

## Step 3: Deploy to Render (5 minutes)

### 3.1 Connect GitHub to Render

1. Go to https://render.com
2. Sign up/Login (use GitHub for easy setup)
3. Click **"New +"** → **"Web Service"**
4. Select your GitHub repository: `tire-fitment-finder`

### 3.2 Configure Service

Fill in these exact settings:

```
Name: tire-fitment-finder
Region: Oregon (or closest)
Branch: main
Root Directory: (leave EMPTY)
Runtime: PHP
Build Command: (leave EMPTY)
Start Command: php -S 0.0.0.0:$PORT -t public
```

### 3.3 Set Environment Variables

Click **"Advanced"** → Scroll to "Environment Variables" → Click **"Add Environment Variable"**

Add these 5 variables (use values from your database):

```
Name: DB_HOST         Value: your-database-host
Name: DB_NAME         Value: tire_shop
Name: DB_USER         Value: your-database-username
Name: DB_PASS         Value: your-database-password
Name: DB_PORT         Value: 3306
```

**For PlanetScale:**
- DB_HOST: Usually looks like `xxx.us-east-1.psdb.cloud`
- DB_PORT: `3306`
- Use the credentials from PlanetScale dashboard

### 3.4 Deploy

1. Scroll down
2. Click **"Create Web Service"**
3. Wait 2-3 minutes for deployment
4. Render will show: "Your service is live at: https://tire-fitment-finder.onrender.com"

---

## Step 4: Test Your Site (1 minute)

1. Visit your Render URL: `https://tire-fitment-finder.onrender.com`
2. Test features:
   - **VIN Search**: Enter `1HGBH41JXMN109186`
   - **YMM Search**: Select Year 2020 → Make Toyota → Model Camry
3. Verify tires appear!

---

## ✅ Success!

Your tire fitment finder is now live!

**Your URLs:**
- **Live Site**: https://tire-fitment-finder.onrender.com
- **Render Dashboard**: https://dashboard.render.com
- **GitHub Repo**: https://github.com/YOUR_USERNAME/tire-fitment-finder

---

## 🔧 Troubleshooting

### Site won't load?
1. Check Render Dashboard → "Events" tab for errors
2. Verify environment variables are set correctly
3. Check "Logs" tab for detailed errors

### Database connection fails?
1. Double-check all 5 environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT)
2. Verify database allows external connections
3. For PlanetScale: Enable "Public IP" access in settings

### API doesn't work?
1. Open browser console (F12)
2. Check for JavaScript errors
3. Verify API URLs in Network tab

---

## 📚 Need More Help?

- **Detailed Guide**: See `RENDER_SETUP.md`
- **Complete Checklist**: See `DEPLOYMENT_CHECKLIST.md`
- **Full Documentation**: See `DEPLOYMENT.md`
- **Application Docs**: See `README.md`

---

## 🔄 Updating Your Site

After making code changes:

```powershell
git add .
git commit -m "Your update description"
git push
```

Render will automatically redeploy! 🎉

---

**That's it! Your application is now live on Render.**
