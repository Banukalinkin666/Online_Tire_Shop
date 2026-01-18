# Deployment Guide for Render

This guide will walk you through deploying the Tire Fitment Finder to Render via GitHub.

## Prerequisites

- GitHub account
- Render account (sign up at https://render.com)
- Git installed on your local machine

## Step-by-Step Instructions

### Step 1: Prepare Your Project for GitHub

1. **Initialize Git Repository** (if not already done)
   ```bash
   git init
   ```

2. **Create .gitignore** (already created)
   - Ensures sensitive files aren't committed

3. **Update Database Configuration for Environment Variables**
   - We'll modify `config/database.php` to read from environment variables

### Step 2: Create GitHub Repository

1. Go to https://github.com and sign in
2. Click the "+" icon → "New repository"
3. Repository name: `tire-fitment-finder` (or your choice)
4. Description: "Tire fitment finder web application"
5. Choose Public or Private
6. **DO NOT** initialize with README, .gitignore, or license (we already have these)
7. Click "Create repository"

### Step 3: Push Code to GitHub

Run these commands in your project directory:

```bash
# Add all files
git add .

# Commit files
git commit -m "Initial commit: Tire Fitment Finder application"

# Add GitHub remote (replace YOUR_USERNAME and REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git

# Push to GitHub
git branch -M main
git push -u origin main
```

**Note:** Replace `YOUR_USERNAME` and `REPO_NAME` with your actual GitHub username and repository name.

### Step 4: Create Render Account & Service

1. Go to https://render.com and sign up/login
2. Click "New +" → "Web Service"
3. Connect your GitHub account if prompted
4. Select your repository (`tire-fitment-finder`)
5. Configure the service:
   - **Name**: `tire-fitment-finder` (or your choice)
   - **Region**: Choose closest to your users
   - **Branch**: `main`
   - **Root Directory**: Leave empty (or `public` if needed)
   - **Runtime**: `PHP`
   - **Build Command**: Leave empty (or `composer install` if using Composer)
   - **Start Command**: `php -S 0.0.0.0:$PORT -t public`

### Step 5: Set Up Database on Render

1. In Render dashboard, click "New +" → "PostgreSQL" (or MySQL)
   - **Note**: Render offers managed PostgreSQL by default, but you can also use external MySQL
   - For MySQL, you might need to use an external service like PlanetScale, or install MySQL in a separate service

2. **Option A: Use Render PostgreSQL** (requires minor SQL adjustments)
   - Name: `tire-fitment-db`
   - Database: `tire_shop`
   - User: Auto-generated
   - Password: Auto-generated (save this!)

3. **Option B: Use External MySQL** (recommended for minimal changes)
   - Use services like:
     - PlanetScale (free tier available)
     - AWS RDS
     - DigitalOcean Managed Databases
     - Or keep existing MySQL database

4. **Copy Database Connection Details**
   - Internal Database URL (Render PostgreSQL)
   - Or External MySQL connection string

### Step 6: Configure Environment Variables in Render

1. Go to your Web Service in Render dashboard
2. Click "Environment" tab
3. Add these variables:

```
DB_HOST=your-database-host
DB_NAME=tire_shop
DB_USER=your-database-user
DB_PASS=your-database-password
DB_PORT=5432
```

**For Render PostgreSQL:**
- Use the internal database URL provided by Render
- Or use the individual connection parameters

**For External MySQL:**
- Use your external MySQL connection details
- Port will typically be `3306` for MySQL

### Step 7: Deploy and Test

1. Render will automatically deploy when you push to GitHub
2. Wait for deployment to complete (check "Events" tab)
3. Your site will be available at: `https://your-app-name.onrender.com`
4. Test the application:
   - Visit the homepage
   - Try VIN search
   - Try Year/Make/Model search

### Step 8: Troubleshooting

**If deployment fails:**
- Check "Events" tab for error messages
- Verify environment variables are set correctly
- Check that `public/` directory structure is correct

**If database connection fails:**
- Verify database credentials in environment variables
- Check that database is running and accessible
- Review application logs in Render dashboard

**If site loads but API doesn't work:**
- Check API endpoint URLs in browser console
- Verify CORS headers are set correctly
- Check that API files are accessible

## Render-Specific Configuration Files

The project includes:
- `render.yaml` - Render configuration (optional but recommended)
- Updated `config/database.php` - Reads from environment variables

## Continuous Deployment

Once connected:
- Every push to `main` branch automatically triggers deployment
- Render shows deployment status in dashboard
- You can view logs in real-time

## Next Steps

- Set up custom domain (optional, in Render dashboard)
- Configure SSL (automatic on Render)
- Set up monitoring and alerts
- Configure auto-scaling if needed
