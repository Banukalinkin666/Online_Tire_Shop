# Deploy with Docker - Render Guide

Using Docker ensures PHP is always available and provides consistent deployments.

---

## Step 1: Commit Dockerfile to GitHub

You should have:
- `Dockerfile` (created)
- `.dockerignore` (created)
- `public/healthz.php` (created)

1. Open GitHub Desktop
2. You should see these new files
3. Commit message: `Add Docker configuration`
4. Click "Commit to main"
5. Click "Push origin"

---

## Step 2: Update Render Service to Use Docker

### Option A: Update Existing Service

1. Go to Render → Your service (`Online_Tire_Shop`)
2. Click **"Settings"** tab
3. Look for **"Docker"** or **"Container"** section
4. Enable Docker/Container mode

### Option B: Create New Service with Docker (Recommended)

1. In Render dashboard, click **"New +"** → **"Web Service"**
2. Connect your repository: `Online_Tire_Shop`
3. Render should auto-detect the Dockerfile
4. Settings:
   - **Name**: `tire-fitment-finder` (or your choice)
   - **Region**: `Oregon`
   - **Branch**: `main`
   - **Dockerfile Path**: Leave empty (auto-detects `Dockerfile`)
   - **Docker Command**: Leave empty (uses CMD from Dockerfile)
5. Add environment variables (same as before):
   - `DB_TYPE` = `pgsql`
   - `DB_HOST` = `dpg-d5mk6are5dus73ej8620-a.oregon-postgres.render.com`
   - `DB_NAME` = `tire_shop`
   - `DB_USER` = `tire_shop_user`
   - `DB_PASS` = `GTWJRZrXsPybYJSv7B6IDbC7w0etDKv2`
   - `DB_PORT` = `5432`
   - `IMPORT_ALLOWED` = `true`
6. Click **"Create Web Service"**

---

## Step 3: Wait for Deployment

1. Render will build the Docker image
2. This takes 3-5 minutes (longer than regular deploy)
3. You'll see build logs in the "Logs" tab

---

## Step 4: Test Your Site

Once deployed:
1. Visit your site URL
2. Test VIN search: `1HGBH41JXMN109186`
3. Test YMM search

---

## Benefits of Docker Approach

✅ **Guaranteed PHP availability** - PHP is in the container
✅ **Consistent environment** - Works same locally and production
✅ **No runtime issues** - Self-contained
✅ **Easier debugging** - Can test Docker locally
✅ **Portable** - Can deploy to any Docker-compatible platform

---

## Troubleshooting

### Build fails?

Check logs in Render → "Logs" tab for specific errors.

### Container doesn't start?

- Verify Dockerfile CMD is correct
- Check environment variables are set
- Review logs for startup errors

### Still having issues?

1. Test Docker locally:
   ```bash
   docker build -t tire-fitment .
   docker run -p 8000:8000 -e PORT=8000 tire-fitment
   ```

---

## Local Testing (Optional)

Test Docker locally before deploying:

```bash
# Build image
docker build -t tire-fitment .

# Run container
docker run -p 8000:8000 \
  -e DB_TYPE=pgsql \
  -e DB_HOST=your-host \
  -e DB_NAME=tire_shop \
  -e DB_USER=your-user \
  -e DB_PASS=your-pass \
  -e DB_PORT=5432 \
  tire-fitment

# Visit http://localhost:8000
```

---

**Docker is the best solution for your deployment! 🐳**
