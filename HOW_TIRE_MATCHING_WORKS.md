# How Tire Availability is Determined

## Complete Flow Explanation

### Step 1: Vehicle Lookup
When you search by VIN or Year/Make/Model:

1. **System searches `vehicle_fitment` table**
   - Looks for: Year, Make, Model, Trim
   - Returns: `front_tire` and `rear_tire` sizes
   - Example: `225/65R17` for 2015 Toyota RAV4

**Code Location:** `app/Models/VehicleFitment.php` → `getFitment()`

```sql
SELECT * FROM vehicle_fitment 
WHERE year = 2015 
AND LOWER(make) = 'toyota'
AND LOWER(model) = 'rav4'
```

---

### Step 2: Tire Size Extraction
From the vehicle fitment, system extracts:
- **Front tire size**: `225/65R17`
- **Rear tire size**: `225/65R17` (or different if staggered)

**Code Location:** `app/Services/TireMatchService.php` → `getMatchingTires()`

```php
$frontSize = $fitment['front_tire'];  // e.g., "225/65R17"
$rearSize = $fitment['rear_tire'] ?: $fitment['front_tire'];
```

---

### Step 3: Tire Inventory Search
System searches `tires` table for matching sizes:

**Code Location:** `app/Models/Tire.php` → `findBySizes()`

```sql
SELECT * FROM tires 
WHERE tire_size IN ('225/65R17')
AND stock > 0
ORDER BY tire_size ASC, brand ASC, price ASC
```

**Key Points:**
- ✅ **Exact size match only** (e.g., `225/65R17` must match exactly)
- ✅ **Only shows tires with stock > 0** (in-stock items)
- ✅ **Case-sensitive matching** (must be exact format)

---

### Step 4: Results Organization
Tires are organized by position:
- **Front tires**: All tires matching front size
- **Rear tires**: All tires matching rear size (if different)

**Code Location:** `app/Services/TireMatchService.php`

```php
'tires' => [
    'front' => $tiresBySize[$frontSize] ?? [],  // Array of tire products
    'rear' => $isStaggered ? ($tiresBySize[$rearSize] ?? []) : []
]
```

---

## Why "No Tires Found" Appears

### Reason 1: No Tires in Database for That Size
**Problem:** The `tires` table doesn't have any products with size `225/65R17`

**Check:**
```sql
SELECT * FROM tires WHERE tire_size = '225/65R17';
```

**Solution:** Add tires to inventory:
```sql
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) 
VALUES ('Michelin', 'Defender LTX M/S', '225/65R17', '102', 'H', 'all-season', 145.99, 50, 'SUV all-season tire');
```

---

### Reason 2: Tires Exist But Stock = 0
**Problem:** Tires exist in database but `stock = 0`

**Check:**
```sql
SELECT * FROM tires WHERE tire_size = '225/65R17' AND stock = 0;
```

**Solution:** Update stock levels:
```sql
UPDATE tires SET stock = 50 WHERE tire_size = '225/65R17' AND brand = 'Michelin';
```

---

### Reason 3: Tire Size Format Mismatch
**Problem:** Size in database doesn't match exactly

**Examples:**
- Database has: `225/65 R17` (with space)
- Vehicle needs: `225/65R17` (no space)
- ❌ Won't match!

**Check:**
```sql
SELECT DISTINCT tire_size FROM tires WHERE tire_size LIKE '%225%65%17%';
```

**Solution:** Ensure consistent format (no spaces, exact match)

---

### Reason 4: Production Data Not Imported
**Problem:** You haven't imported the production tire data

**Check:**
```sql
SELECT COUNT(*) FROM tires;  -- Should be 200+ if imported
```

**Solution:** Import `sql/production_tires.sql`

---

## Data Sources

### Vehicle Fitment Data
**Source:** `vehicle_fitment` table
- **Location:** `sql/production_data.sql` (1000+ vehicles)
- **Contains:** Year, Make, Model, Trim, Tire Sizes

### Tire Inventory Data
**Source:** `tires` table
- **Location:** `sql/production_tires.sql` (200+ tire products)
- **Contains:** Brand, Model, Size, Price, Stock

---

## How to Check Your Database

### Check if Vehicle Exists:
```sql
SELECT * FROM vehicle_fitment 
WHERE year = 2015 AND make = 'Toyota' AND model = 'RAV4';
```

### Check Tire Size for Vehicle:
```sql
SELECT front_tire, rear_tire 
FROM vehicle_fitment 
WHERE year = 2015 AND make = 'Toyota' AND model = 'RAV4';
```

### Check if Tires Exist for Size:
```sql
SELECT * FROM tires 
WHERE tire_size = '225/65R17' AND stock > 0;
```

### Check All Tire Sizes Available:
```sql
SELECT DISTINCT tire_size, COUNT(*) as tire_count 
FROM tires 
WHERE stock > 0 
GROUP BY tire_size 
ORDER BY tire_size;
```

---

## Current Issue: 2015 Toyota RAV4

For your specific case (2015 Toyota RAV4, size 225/65R17):

1. **Check vehicle exists:**
   ```sql
   SELECT * FROM vehicle_fitment WHERE year = 2015 AND make = 'Toyota' AND model = 'RAV4';
   ```

2. **Check tire size:**
   ```sql
   SELECT front_tire FROM vehicle_fitment WHERE year = 2015 AND make = 'Toyota' AND model = 'RAV4';
   ```

3. **Check if tires exist:**
   ```sql
   SELECT * FROM tires WHERE tire_size = '225/65R17' AND stock > 0;
   ```

**Most Likely Issue:** The tire size `225/65R17` exists in `vehicle_fitment` but no tires with that size (and stock > 0) exist in the `tires` table.

**Solution:** Import `sql/production_tires.sql` or manually add tires for size `225/65R17`.

---

## Summary

**Tire availability is determined by:**
1. ✅ Vehicle found in `vehicle_fitment` table → Get tire size
2. ✅ Search `tires` table for exact size match
3. ✅ Filter by `stock > 0` (only in-stock items)
4. ✅ Return matching tires

**"No tires found" means:**
- ❌ No tires in database for that size, OR
- ❌ All tires for that size have `stock = 0`

**Fix:** Add tires to inventory with correct size and stock > 0.
