# Sample VINs for Testing

## Valid Test VINs (17 characters, no I, O, or Q)

### Toyota Vehicles
- **2015 Toyota Camry LE**: `4T1BF1FK5FU123456`
- **2020 Toyota Camry XSE**: `4T1C11AK0LU123456`
- **2018 Toyota Corolla LE**: `5YFB3HHE8JP123456`
- **2021 Toyota RAV4**: `JTMB1RFV8MD123456`
- **2019 Toyota Highlander**: `5TDKZ3DC9KS123456`

### Honda Vehicles
- **2017 Honda Accord LX**: `1HGCV1F30HA123456`
- **2020 Honda Accord Sport**: `1HGCY2F30LA123456`
- **2018 Honda Civic**: `19XFC2F59JE123456`
- **2021 Honda CR-V**: `5J6RM4H77ML123456`
- **2019 Honda Pilot**: `5FNYF4H90KB123456`

### Ford Vehicles
- **2016 Ford F-150**: `1FTFW1ET6GFA12345`
- **2020 Ford F-150 Raptor**: `1FTFW1RG0LFA12345`
- **2018 Ford Explorer**: `1FM5K8D82JGA12345`
- **2021 Ford Mustang**: `1FA6P8TH1M5123456`
- **2019 Ford Escape**: `1FMCU9GD0KUA12345`

### Chevrolet Vehicles
- **2017 Chevrolet Silverado**: `1GCVKREC5HZ123456`
- **2020 Chevrolet Equinox**: `2GNAXKEV0L6123456`
- **2018 Chevrolet Malibu**: `1G1ZD5ST5JF123456`
- **2021 Chevrolet Tahoe**: `1GNSKJKC1MR123456`
- **2019 Chevrolet Traverse**: `1GNKVGKD9KJ123456`

### BMW Vehicles
- **2018 BMW 3 Series**: `WBA3A5C59EK123456`
- **2020 BMW 3 Series 330i**: `WBA3A5C50LJ123456`
- **2019 BMW X5**: `5UXCR6C09L9123456`
- **2021 BMW 5 Series**: `WBA5A5C58ED123456`

### Mercedes-Benz Vehicles
- **2017 Mercedes C-Class**: `WDDWF4KB7HR123456`
- **2020 Mercedes E-Class**: `WDD2130041A123456`
- **2019 Mercedes GLE**: `4JGDA5HB1KA123456`

### Other Popular Makes
- **2018 Nissan Altima**: `1N4AL3AP8JC123456`
- **2020 Hyundai Elantra**: `5NPE34AF0LH123456`
- **2019 Kia Sorento**: `5XYPG4A36KG123456`
- **2021 Subaru Outback**: `4S4BTACC1M3123456`
- **2018 Mazda CX-5**: `JM3KE2DY0J0123456`

## Testing Scenarios

### 1. Test Basic VIN Decode
Use any of the above VINs to test if:
- VIN decodes successfully
- Vehicle information displays (Year, Make, Model)
- Tire sizes show (if in database)

### 2. Test VIN Caching
1. Enter a VIN (e.g., `4T1BF1FK5FU123456`)
2. Wait for results
3. Enter the same VIN again
4. Should be faster (uses cache)

### 3. Test AI Tire Size Detection
Use a VIN that might not be in database:
- `2GNALBEK5F6123456` (Chevrolet Equinox - you tested this earlier)
- `1HGCV1F30HA123456` (Honda Accord)

### 4. Test Fallback Matching
Use a VIN where trim might not match exactly:
- System should fallback to most common tire size for that model/year

### 5. Test Invalid VIN
Try these to test error handling:
- `12345678901234567` (too short or invalid format)
- `ABCDEFGHIJKLMNOPQ` (contains invalid characters)
- `1HGBH41JXMN109186` (valid format but might not decode)

## Quick Test VINs (Most Likely to Work)

**Recommended for quick testing:**
1. `4T1BF1FK5FU123456` - 2015 Toyota Camry
2. `1HGCV1F30HA123456` - 2017 Honda Accord
3. `1FTFW1ET6GFA12345` - 2016 Ford F-150
4. `2GNALBEK5F6123456` - 2015 Chevrolet Equinox (you tested this)
5. `19XFC2F59JE123456` - 2018 Honda Civic

## Notes

- All VINs above follow the 17-character format
- No I, O, or Q characters (as per VIN standards)
- Last 6 digits are placeholder (123456) - NHTSA API will decode the actual vehicle info from the first 11 characters
- Some VINs might not decode if they're not in NHTSA database
- The system will show "Vehicle not available in database" if tire sizes aren't found, but you can still see the decoded vehicle info
