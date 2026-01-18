# Quick Start: Deploy to Render from GitHub

## Prerequisites Checklist
- [ ] GitHub account
- [ ] Render account (free at https://render.com)
- [ ] Git installed locally
- [ ] Code ready in your local folder

---

## Step 1: Push to GitHub (5 minutes)

### 1.1 Initialize Git (if not done)
```bash
cd F:\Online_Tire_Shop
git init
```

### 1.2 Create GitHub Repository
1. Go to https://github.com/new
2. Repository name: `tire-fitment-finder`
3. Description: `Tire fitment finder web application`
4. **Choose Public or Private**
5. **DO NOT check** "Initialize this repository with a README"
6. Click **"Create repository"**

### 1.3 Push Your Code
Copy the commands GitHub shows you, or use these (replace YOUR_USERNAME):

```bash
git add .
git commit -m "Initial commit: Tire Fitment Finder"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/tire-fitment-finder.git
git push -u origin main
```

**Important:** Replace `YOUR_USERNAME` with your actual GitHub username!

---

## Step 2: Set Up Database (10 minutes)

### Option A: External MySQL (Recommended - No Code Changes)
Use a free MySQL service:
- **PlanetScale** (free tier): https://planetscale.com
- **Free MySQL Hosting**: https://www.freemysqlhosting.net
- Or your existing MySQL database

**After creating database:**
1. Import schema: Run `sql/schema.sql` in your MySQL database
2. Note down connection details (host, username, password, database name)

### Option B: Render PostgreSQL (Requires SQL Changes)
1. In Render: **New +** → **PostgreSQL**
2. Name: `tire-fitment-db`
3. Plan: **Free**
4. Copy the **Internal Database URL** or connection details

---

## Step 3: Create Render Web Service (5 minutes)

### 3.1 Connect GitHub to Render
1. Go to https://render.com
2. Sign up/Login (use GitHub to sign in)
3. Click **"New +"** → **"Web Service"**
4. Connect your GitHub account if prompted
5. Select your repository: `tire-fitment-finder`

### 3.2 Configure Web Service
Fill in these settings:

```
Name: tire-fitment-finder
Region: Oregon (or closest to you)
Branch: main
Root Directory: (leave empty)
Runtime: PHP
Build Command: (leave empty)
Start Command: php -S 0.0.0.0:$PORT -t public
```

### 3.3 Set Environment Variables
Click **"Advanced"** → **"Add Environment Variable"**

Add these (use values from your database setup):

```
DB_HOST = your-database-host
DB_NAME = tire_shop
DB_USER = your-database-username
DB_PASS = your-database-password
DB_PORT = 3306
```

**For Render PostgreSQL**, use:
```
DB_PORT = 5432
```

### 3.4 Deploy
1. Click **"Create Web Service"**
2. Render will start deploying automatically
3. Wait 2-3 minutes for deployment

---

## Step 4: Import Database (5 minutes)

### If using External MySQL:
1. Use phpMyAdmin, MySQL Workbench, or command line
2. Import `sql/schema.sql` file
3. Verify tables exist: `vehicle_fitment` and `tires`

### If using Render PostgreSQL:
You'll need to convert the SQL schema. For now, use external MySQL for simplicity.

---

## Step 5: Test Your Site (2 minutes)

1. Render will provide a URL: `https://tire-fitment-finder.onrender.com`
2. Visit the URL in your browser
3. Test features:
   - Try VIN search: `1HGBH41JXMN109186`
   - Try Year/Make/Model dropdown
   - Check if tires appear

### Troubleshooting

**Site won't load?**
- Check Render dashboard → "Events" tab for errors
- Verify environment variables are set correctly

**Database connection error?**
- Double-check DB_HOST, DB_USER, DB_PASS, DB_NAME
- Ensure database is accessible from Render's servers
- Check firewall/security settings

**API not working?**
- Open browser console (F12) → Check for JavaScript errors
- Verify API URLs are correct
- Check Network tab for failed requests

---

## Step 6: Monitor & Update

### View Logs
- Render Dashboard → Your Service → "Logs" tab
- See real-time application logs

### Make Updates
1. Edit code locally
2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Your update message"
   git push
   ```
3. Render automatically deploys on push!

---

## Common Issues & Solutions

### Issue: "Could not connect to database"
**Solution:** 
- Verify environment variables match your database
- Check database allows connections from Render IPs
- For PlanetScale, enable "Public IP" access

### Issue: "500 Internal Server Error"
**Solution:**
- Check Render logs for specific error
- Verify all PHP files are uploaded correctly
- Check file permissions

### Issue: "API endpoints return 404"
**Solution:**
- Verify API files are in `/api` directory
- Check that `public/` is the web root
- Verify URL paths in JavaScript

---

## Next Steps

- [ ] Set up custom domain (optional)
- [ ] Configure email notifications
- [ ] Add monitoring
- [ ] Scale up plan if needed (free tier has limitations)

---

## Need Help?

- Render Docs: https://render.com/docs
- Check `DEPLOYMENT.md` for detailed deployment guide
- Check `README.md` for application documentation
