# Online Tire Website

Next.js 14 (App Router) marketing site with CMS. Integrates the existing **Tire Size Finder** (embedded via iframe from your hosted app).

## Tech Stack

- Next.js 14 (App Router)
- TypeScript
- Tailwind CSS
- Prisma ORM
- SQLite

## Setup

```bash
cd website
npm install
cp .env.example .env
# Edit .env: set DATABASE_URL (default file:./dev.db) and TIRE_FINDER_URL (your tire finder app URL)
npx prisma generate
npx prisma db push
npm run db:seed
npm run dev
```

- **Site:** http://localhost:3000  
- **Admin:** http://localhost:3000/admin (login: `admin@example.com` / `admin123`)

## Folder Structure

```
website/
├── app/
│   ├── (site)/           # Public site (Navbar + Footer)
│   │   ├── page.tsx      # Home (hero = TireSizeFinder)
│   │   ├── about/
│   │   ├── services/
│   │   └── contact/
│   ├── admin/            # CMS (protected)
│   │   ├── login/
│   │   ├── dashboard/
│   │   ├── home/
│   │   ├── about/
│   │   ├── services/
│   │   ├── contact/
│   │   └── settings/
│   └── api/
│       ├── contact/      # Form submissions
│       └── admin/        # CMS CRUD
├── components/
│   ├── TireSizeFinder.tsx  # Embeds existing finder (iframe)
│   ├── Navbar.tsx
│   ├── Footer.tsx
│   ├── ContactForm.tsx
│   └── admin/
├── lib/
│   ├── prisma.ts
│   ├── auth.ts
│   └── data.ts
├── prisma/
│   ├── schema.prisma
│   └── seed.ts
└── middleware.ts         # Protects /admin/* (except login)
```

## Tire Size Finder

The **TireSizeFinder** component is in `components/TireSizeFinder.tsx`. It embeds your existing app (e.g. `https://online-tire-shop-pro.onrender.com`) in an iframe. Do not rebuild the finder; the URL is configurable in **Admin → Settings → Tire Finder URL**.

## Admin

- **Login:** `/admin/login` (cookie session)
- **Dashboard:** `/admin/dashboard`
- **Home:** Hero title/subtitle, CTA, Why Choose Us, Brands, Testimonials
- **About:** Intro, mission, vision, experience, certifications, images
- **Services:** Add/Edit/Delete services (title, description, icon)
- **Contact:** Address, phone, email, hours, WhatsApp, map embed; view form submissions
- **Settings:** Logo, footer text, social links, Tire Finder URL

## Database (Prisma)

- `users` – admin auth
- `homepage_content` – hero, CTA, why choose us (JSON)
- `about_content` – about page
- `services` – service cards
- `contact_content` – contact page
- `contact_messages` – form submissions
- `site_settings` – logo, footer, social, tire finder URL
- `brands` – logo grid
- `testimonials` – testimonials

## Scripts

- `npm run dev` – dev server
- `npm run build` / `npm run start` – production
- `npm run db:seed` – seed sample content and admin user
- `npm run db:studio` – Prisma Studio
