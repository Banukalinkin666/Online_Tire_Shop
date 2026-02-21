# Deploy to Render (same URL: online-tire-shop-pro.onrender.com)

The repo is set up so **the Next.js site** is the main app at **https://online-tire-shop-pro.onrender.com/** and the **PHP Tire Fitment Finder** runs as a second service and is embedded on the home page.

## What gets deployed

| Service | Name | URL | Purpose |
|--------|------|-----|--------|
| **Primary** | `online-tire-shop-pro` | https://online-tire-shop-pro.onrender.com | Next.js site (Home, About, Services, Contact + CMS) |
| **Tire Finder** | `tire-fitment-finder` | https://tire-fitment-finder.onrender.com | PHP Tire Fitment Finder (embedded in Home hero) |
| **Database** | `online-tire-shop-db` | (internal) | PostgreSQL for Next.js CMS |

## One-time setup after first deploy

1. In Render dashboard, open the **online-tire-shop-pro** (Next.js) service.
2. Go to **Environment** and add:
   - **TIRE_FINDER_URL** = `https://tire-fitment-finder.onrender.com`  
     (use the exact URL Render shows for the `tire-fitment-finder` service.)
3. Save. The site will redeploy and the home page will embed the finder from that URL.

## PHP Tire Finder database

The **tire-fitment-finder** service still needs its own database (for VIN/vehicle/tires). In the dashboard:

- Either attach an existing PostgreSQL database to `tire-fitment-finder` and set **DATABASE_URL** (or DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT),  
- Or create a new Postgres DB and link it to `tire-fitment-finder`.

The **online-tire-shop-db** database in the blueprint is only for the Next.js CMS (admin, pages, contact messages).

## Custom domain

Point your domain to the **online-tire-shop-pro** service so the main URL is your domain. The Tire Finder will still load in an iframe from `tire-fitment-finder.onrender.com` unless you add a custom domain to that service too.

## Local dev

- Next.js (with Postgres): set `DATABASE_URL` to a local or cloud Postgres URL. Run `npm run db:seed` once.
- Tire Finder URL: set `TIRE_FINDER_URL` to `https://tire-fitment-finder.onrender.com` (or your local PHP app URL if you run it locally).
