# Deployment Checklist - Render + GitHub

Follow this checklist step-by-step to deploy your Tire Fitment Finder to Render.

## Pre-Deployment

- [ ] All code is tested locally
- [ ] Database schema is ready (`sql/schema.sql`)
- [ ] Git repository is initialized
- [ ] `.gitignore` file is present

## Step 1: GitHub Setup

- [ ] Create GitHub account (if not already)
- [ ] Create new repository on GitHub
- [ ] Repository name: `tire-fitment-finder` (or your choice)
- [ ] Repository is set to Public or Private
- [ ] Push code to GitHub:
  ```bash
  git add .
  git commit -m "Initial commit"
  git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
  git push -u origin main
  ```
- [ ] Verify code is on GitHub (visit repository URL)

## Step 2: Database Setup

Choose one option:

### Option A: External MySQL (Recommended)
- [ ] Create MySQL database on external service
  - PlanetScale: https://planetscale.com (free tier)
  - Or your existing MySQL host
- [ ] Note down connection details:
  - [ ] Host
  - [ ] Database name
  - [ ] Username
  - [ ] Password
  - [ ] Port (usually 3306)
- [ ] Import `sql/schema.sql` into database
- [ ] Verify tables exist: `vehicle_fitment` and `tires`

### Option B: Render PostgreSQL
- [ ] Create PostgreSQL database on Render
- [ ] Note down connection details
- [ ] Convert SQL schema (requires modifications)

## Step 3: Render Account Setup

- [ ] Create Render account: https://render.com
- [ ] Connect GitHub account to Render
- [ ] Verify GitHub repositories are visible in Render

## Step 4: Create Render Web Service

- [ ] Click "New +" → "Web Service"
- [ ] Select repository: `tire-fitment-finder`
- [ ] Configure service:
  - [ ] Name: `tire-fitment-finder`
  - [ ] Region: Oregon (or closest)
  - [ ] Branch: `main`
  - [ ] Root Directory: (leave empty)
  - [ ] Runtime: `PHP`
  - [ ] Build Command: (leave empty)
  - [ ] Start Command: `php -S 0.0.0.0:$PORT -t public`
- [ ] Plan: Free (for testing)

## Step 5: Configure Environment Variables

In Render service settings → Environment:

- [ ] Add `DB_HOST` = your database host
- [ ] Add `DB_NAME` = `tire_shop`
- [ ] Add `DB_USER` = your database username
- [ ] Add `DB_PASS` = your database password
- [ ] Add `DB_PORT` = `3306` (or `5432` for PostgreSQL)
- [ ] Add `PHP_VERSION` = `8.2` (optional)

## Step 6: Deploy

- [ ] Click "Create Web Service" or "Manual Deploy"
- [ ] Wait for deployment (2-3 minutes)
- [ ] Check deployment status (should be "Live")
- [ ] Note your service URL: `https://tire-fitment-finder.onrender.com`

## Step 7: Verify Database Connection

- [ ] Visit your Render service URL
- [ ] Check if page loads (should show tire fitment form)
- [ ] Try to search for a vehicle
- [ ] If errors, check Render logs:
  - Dashboard → Service → "Logs" tab

## Step 8: Test Application

- [ ] Test VIN search:
  - [ ] Enter test VIN: `1HGBH41JXMN109186`
  - [ ] Verify vehicle info appears
  - [ ] Verify tires appear if available
- [ ] Test Year/Make/Model search:
  - [ ] Select Year: `2020`
  - [ ] Select Make: `Toyota`
  - [ ] Select Model: `Camry`
  - [ ] Verify tires appear
- [ ] Test on mobile device (responsive design)

## Step 9: Troubleshooting (If Needed)

### If site won't load:
- [ ] Check Render logs for errors
- [ ] Verify environment variables are set correctly
- [ ] Check that `public/` directory exists

### If database connection fails:
- [ ] Double-check all environment variables
- [ ] Verify database is accessible from Render's servers
- [ ] For external MySQL, check firewall/security settings
- [ ] Test database connection manually

### If API doesn't work:
- [ ] Open browser console (F12)
- [ ] Check for JavaScript errors
- [ ] Verify API URLs are correct
- [ ] Check Network tab for failed requests

## Step 10: Post-Deployment

- [ ] Bookmark your Render dashboard
- [ ] Bookmark your deployed site URL
- [ ] Set up monitoring/alerts (optional)
- [ ] Configure custom domain (optional)
- [ ] Test all features thoroughly

## Success Criteria

✅ Site loads without errors
✅ Database connection works
✅ VIN search returns results
✅ Year/Make/Model search returns results
✅ Tire matching displays correctly
✅ Mobile responsive design works

---

## Quick Reference

**Render Dashboard**: https://dashboard.render.com
**GitHub Repository**: https://github.com/YOUR_USERNAME/tire-fitment-finder
**Deployed Site**: https://tire-fitment-finder.onrender.com

**Support Documents**:
- `RENDER_SETUP.md` - Quick start guide
- `DEPLOYMENT.md` - Detailed deployment guide
- `README.md` - Application documentation
