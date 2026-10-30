# BoltMart Hardware — online fastener & hardware store

Laravel 13 e-commerce site for bolts, nuts, screws, washers, anchors and tools.
Plain Blade + CSS (no Node build needed), works on cheap shared hosting.

## Features

**Storefront**
- Catalogue by category, search (name / size / SKU / DIN standard), material & stock filters, sorting
- Products with multiple **sizes × pack quantities**, per-piece price, MRP strike-through, live stock
- **Automatic bulk pricing tiers** (e.g. 5% off at 10 packs, 10% at 50) shown live on the product page and in cart
- **Quick order by SKU**: paste `SKU, qty` lines (from Excel/WhatsApp) to fill the cart in one go
- **Bulk / project quote request** form (BOQ paste) for large B2B orders
- Checkout with **Cash on Delivery** and **Razorpay** (UPI / cards / netbanking), GSTIN capture for business buyers
- Order tracking timeline + **GST tax invoice** (CGST/SGST or IGST by state, HSN codes) printable as PDF
- WhatsApp enquiry buttons, SEO product schema, mobile-first responsive design

**Admin panel** (`/admin`)
- Sales dashboard (today / month, 14-day chart, low-stock alerts, open orders, new quotes)
- Products: details, specs, photo upload, sizes/packs/stock/price/MRP/bulk tiers in one screen
- Categories, orders (status, courier tracking no., mark COD paid, cancel → auto restock), quote replies via WhatsApp / email

## Local setup

Requires PHP 8.3+ and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Admin login comes from `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` (seeded by `php artisan db:seed`).
**Change the password before going live.**

Run tests: `php artisan test`

## Configuration (`.env`)

| Key | Purpose |
| --- | --- |
| `STORE_NAME`, `STORE_PHONE`, `STORE_WHATSAPP`, `STORE_EMAIL`, `STORE_ADDRESS` | Branding shown across the site and invoices |
| `STORE_STATE`, `STORE_GSTIN` | Seller GST details (state decides CGST+SGST vs IGST) |
| `STORE_SHIPPING_FEE`, `STORE_FREE_SHIPPING_ABOVE` | Flat shipping and free-shipping threshold (pre-GST) |
| `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET` | Leave empty for COD-only. Use `rzp_test_…` keys while testing |

## Deploying to shared hosting (Hostinger / cPanel)

1. Create a MySQL database and user in the hosting panel.
2. Upload the project (Git or zip) **outside** `public_html`, e.g. `~/boltmart`.
3. Point the domain's document root to `~/boltmart/public` (or symlink `public_html` → `boltmart/public`).
4. Over SSH: `composer install --no-dev --optimize-autoloader`
5. Create `.env` with `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain.in`,
   `DB_CONNECTION=mysql` + DB credentials, store details and Razorpay keys.
6. `php artisan key:generate && php artisan migrate --force --seed && php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Make `storage/`, `bootstrap/cache/` and `public/uploads/` writable.
8. Enable free SSL (Let's Encrypt) in the panel.
