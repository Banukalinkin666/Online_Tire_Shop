# Import Production Database - Complete Guide

This guide will help you import the comprehensive production database with **1000+ vehicles** and **200+ tire products** covering 2010-2024.

---

## What's Included

### Vehicle Data (`sql/production_data.sql`)
- **1000+ vehicle fitments** across 2010-2024
- **15+ major makes**: Toyota, Honda, Ford, Chevrolet, Nissan, Hyundai, Kia, Subaru, Jeep, Ram, GMC, BMW, Tesla
- **50+ popular models**: Camry, Corolla, Accord, Civic, F-150, Silverado, Altima, Rogue, Elantra, Tucson, Forte, Sportage, Outback, Forester, Wrangler, Grand Cherokee, 3 Series, Model 3, Model Y, and more
- **Multiple trims** per model
- **All years** from 2010-2024 for each model

### Tire Inventory (`sql/production_tires.sql`)
- **200+ tire products** covering all sizes
- **Major brands**: Michelin, Bridgestone, Goodyear, Continental, BFGoodrich, Pirelli, Hankook
- **All tire sizes** matching vehicle fitments
- **Multiple options** per size (budget to premium)

---

## How to Import

### Option 1: Via Render Database Shell (Recommended)

1. **Go to Render Dashboard**
   - Navigate to your PostgreSQL database
   - Click on "Shell" tab

2. **Import Vehicle Data**
   ```bash
   # Copy contents of sql/production_data.sql
   # Paste into Shell and execute
   ```

3. **Import Tire Data**
   ```bash
   # Copy contents of sql/production_tires.sql
   # Paste into Shell and execute
   ```

4. **Verify Import**
   ```sql
   SELECT COUNT(*) FROM vehicle_fitment;  -- Should show 1000+
   SELECT COUNT(*) FROM tires;             -- Should show 200+
   ```

### Option 2: Via Web Import Script

1. **Set Environment Variable in Render**
   - Go to your Web Service → Environment
   - Add: `IMPORT_ALLOWED=true`
   - Save changes

2. **Visit Import Page**
   ```
   https://your-site.onrender.com/import-data.php
   ```

3. **Import Data**
   - The script will automatically import both files
   - Wait for success message

4. **Delete Import Script** (for security)
   - Remove `public/import-data.php` after import

### Option 3: Via psql Command Line

If you have PostgreSQL client installed:

```bash
# Connect to your Render database
psql "postgresql://user:password@host:port/database"

# Import vehicle data
\i sql/production_data.sql

# Import tire data
\i sql/production_tires.sql
```

---

## What Vehicles Are Covered

### Toyota (200+ entries)
- Camry (2010-2024, LE, XSE)
- Corolla (2010-2024, LE)
- RAV4 (2010-2024, Base/LE)
- Highlander (2010-2024, Base/LE)

### Honda (200+ entries)
- Accord (2010-2024, LX, Sport)
- Civic (2010-2024, LX)
- CR-V (2010-2024, LX)
- Pilot (2010-2024, LX)

### Ford (150+ entries)
- F-150 (2010-2024, XL, Raptor)
- Escape (2010-2024, Base/S)
- Explorer (2010-2024, Base)

### Chevrolet (100+ entries)
- Silverado 1500 (2010-2024)
- Equinox (2010-2024, LS)
- Tahoe (2010-2024, LS)

### Nissan (100+ entries)
- Altima (2010-2024, S)
- Rogue (2010-2024, S)
- Sentra (2010-2024, S)

### Hyundai (100+ entries)
- Elantra (2010-2024, GLS/SE)
- Tucson (2010-2024, GLS/SE)
- Santa Fe (2010-2024, GLS/SE)

### Kia (100+ entries)
- Forte (2010-2024, LX)
- Sportage (2010-2024, LX)
- Sorento (2010-2024, LX)

### Subaru (60+ entries)
- Outback (2010-2024, Base)
- Forester (2010-2024, Base)

### Jeep (60+ entries)
- Wrangler (2010-2024, Sport)
- Grand Cherokee (2010-2024, Laredo)

### Ram (30+ entries)
- 1500 (2010-2024, Tradesman)

### GMC (30+ entries)
- Sierra 1500 (2010-2024)
- Yukon (2010-2024, SLE)

### BMW (30+ entries)
- 3 Series (2010-2024, 328i/330i, M3)

### Tesla (20+ entries)
- Model 3 (2017-2024, Standard, Performance)
- Model Y (2020-2024, Long Range, Performance)

---

## Tire Coverage

The tire inventory includes:

- **Passenger Car Tires**: 195/65R15 to 235/45R18
- **SUV Tires**: 225/60R17 to 265/65R18
- **Truck Tires**: 265/70R17 to 315/70R17
- **Performance Tires**: 255/35R19 to 285/35R19
- **Electric Vehicle Tires**: Model 3 and Model Y sizes

**Brands Available:**
- Michelin (Defender, Pilot Sport, CrossClimate, Primacy)
- Bridgestone (Turanza, Potenza, Dueler)
- Goodyear (Assurance, Wrangler)
- Continental (TrueContact, ExtremeContact, CrossContact)
- BFGoodrich (All-Terrain T/A KO2)
- Pirelli (P Zero)
- Hankook (Kinergy, Ventus)

---

## After Import

### Test Your System

1. **Test VIN Search**
   - Try various VINs for vehicles in the database
   - Should find matches for most common vehicles

2. **Test Year/Make/Model Search**
   - Select Year: 2020
   - Select Make: Toyota
   - Select Model: Camry
   - Should show trims and tire options

3. **Verify Tire Matching**
   - After selecting a vehicle, verify tires are displayed
   - Check that prices and stock levels show correctly

---

## Adding More Vehicles

To add more vehicles in the future:

1. **Use the Import Service**
   - `app/Services/DataImportService.php`
   - Supports CSV or array imports

2. **Direct SQL Insert**
   ```sql
   INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire, notes) 
   VALUES (2024, 'Toyota', 'RAV4', 'XLE', '225/60R18', NULL, 'Premium trim');
   ```

3. **Add Matching Tires**
   ```sql
   INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) 
   VALUES ('Michelin', 'Defender LTX M/S', '225/60R18', '100', 'H', 'all-season', 155.99, 25, 'SUV tire');
   ```

---

## Database Statistics

After import, you should have:

- **~1000+ vehicle fitments**
- **~200+ tire products**
- **Coverage for 2010-2024**
- **15+ major makes**
- **50+ popular models**

---

## Troubleshooting

### Import Fails
- Check PostgreSQL connection
- Verify SQL syntax (no typos)
- Check for duplicate entries (ON CONFLICT DO NOTHING handles this)

### Vehicles Not Found
- Verify vehicle is in database: `SELECT * FROM vehicle_fitment WHERE make = 'Toyota' AND model = 'Camry';`
- Check case sensitivity (queries use LOWER() for matching)

### Tires Not Showing
- Verify tire size matches: `SELECT * FROM tires WHERE tire_size = '215/55R17';`
- Check stock levels (tires with 0 stock may be filtered)

---

**Your database is now production-ready! 🚀**
