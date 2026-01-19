# Data Import Guide - Expanding Your Tire Fitment Database

This guide helps you import comprehensive vehicle and tire data for your business.

---

## Quick Start: Import Comprehensive Data

### Option 1: Import via Web Interface (Easiest)

1. Visit: `https://your-site.onrender.com/import-data.php?key=your-secret-key`
   - Or set `IMPORT_ALLOWED=true` in Render environment variables
2. Select "Comprehensive Vehicle Data"
3. Click "Import Data"
4. Wait for success message
5. **Delete `import-data.php` after importing!**

### Option 2: Import via SQL File

1. Go to Render → Your Database → "Shell" tab
2. Open `sql/import_vehicle_data.sql`
3. Copy all SQL content
4. Paste into Shell and execute
5. Done!

---

## What Gets Imported

The comprehensive import adds:
- **100+ vehicles** across multiple years (2015-2024)
- **Popular makes**: Toyota, Honda, Ford, Chevrolet, Nissan, Hyundai, Kia, Subaru, Jeep, Ram, GMC
- **Common models**: Corolla, Civic, F-150, Silverado, Altima, Elantra, etc.
- **Matching tire inventory** for all new vehicle sizes

---

## Adding Your Own Vehicle Data

### Method 1: Direct SQL Insert

```sql
INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire, notes) 
VALUES (2024, 'Toyota', 'RAV4', 'LE', '225/65R17', NULL, 'Standard trim');
```

### Method 2: CSV Import (Coming Soon)

1. Create CSV file with columns:
   - year, make, model, trim, front_tire, rear_tire, notes
2. Use the import tool to upload

### Method 3: Bulk Import Script

Create a PHP script to import from your existing data sources.

---

## Adding Tire Inventory

### Method 1: Direct SQL

```sql
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) 
VALUES ('Michelin', 'Defender T+H', '225/65R17', '102', 'H', 'all-season', 145.99, 50, 'Long-lasting tire');
```

### Method 2: Via Import Tool

Use the import tool to add tires in bulk.

---

## Data Sources for Real-World Fitment Data

### Free Sources:
1. **NHTSA Database** - Vehicle specifications
2. **Tire Rack** - OEM tire sizes (research only)
3. **Manufacturer Websites** - Official specifications

### Commercial Sources:
1. **Tire Guides** - Comprehensive fitment databases
2. **Parts Catalogs** - Aftermarket fitment data
3. **OEM Data Providers** - Direct from manufacturers

---

## Best Practices

### 1. Data Quality
- ✅ Verify tire sizes from multiple sources
- ✅ Include trim variations (different trims = different tire sizes)
- ✅ Note staggered setups (different front/rear)
- ✅ Keep notes field for special cases

### 2. Regular Updates
- Update yearly for new model years
- Add popular vehicles as they're requested
- Remove discontinued models (optional)

### 3. Tire Inventory
- Match tire sizes to vehicle fitments
- Keep stock levels updated
- Add new tire models regularly

---

## Sample Data Structure

### Vehicle Fitment CSV Format:
```csv
year,make,model,trim,front_tire,rear_tire,notes
2024,Toyota,RAV4,LE,225/65R17,,Standard trim
2024,Toyota,RAV4,XLE,225/60R18,,Premium trim
```

### Tire Inventory CSV Format:
```csv
brand,model,tire_size,load_index,speed_rating,season,price,stock,description
Michelin,Defender T+H,225/65R17,102,H,all-season,145.99,50,Long-lasting tire
```

---

## Maintenance Schedule

**Weekly:**
- Update tire stock levels
- Add new tire models

**Monthly:**
- Add new vehicle model years
- Review and update popular vehicles

**Yearly:**
- Add new model year vehicles
- Archive old/discontinued models

---

## Scaling Tips

### For 10,000+ Vehicles:
- Use database indexing (already set up)
- Consider partitioning by year
- Use caching for popular searches
- Optimize queries

### For 100,000+ Vehicles:
- Consider separate fitment database
- Use search optimization
- Implement pagination
- Add vehicle search/filtering

---

## Need Help?

- Check `sql/import_vehicle_data.sql` for examples
- Use `app/Services/DataImportService.php` for programmatic imports
- Review existing data structure in `sql/schema_postgresql.sql`

---

**Your database is now ready to scale! 🚀**
