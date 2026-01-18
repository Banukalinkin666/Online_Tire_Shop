# API Usage Examples

This document provides examples of how to use the Tire Fitment API endpoints directly.

## Base URL

Assuming the application is deployed at `https://example.com`:

- Standalone: `https://example.com/api/`
- WordPress: Depends on your installation path

## 1. VIN Decode

Decode a vehicle VIN to get vehicle information.

### Request

```bash
curl -X POST https://example.com/api/vin.php \
  -H "Content-Type: application/json" \
  -d '{"vin": "1HGBH41JXMN109186"}'
```

### JavaScript Example

```javascript
async function decodeVIN(vin) {
    const response = await fetch('/api/vin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ vin: vin })
    });
    
    const data = await response.json();
    return data;
}

// Usage
const result = await decodeVIN('1HGBH41JXMN109186');
console.log(result);
```

### Response

```json
{
    "success": true,
    "data": {
        "vehicle": {
            "year": 2020,
            "make": "Honda",
            "model": "Accord",
            "body_class": "Sedan",
            "drive_type": "FWD",
            "fuel_type": "Gasoline"
        },
        "trims": ["LX", "Sport"],
        "message": "VIN decoded successfully. Please select a trim to continue."
    }
}
```

## 2. Get Years

Get all available years in the database.

### Request

```bash
curl https://example.com/api/ymm.php?action=year
```

### Response

```json
{
    "success": true,
    "data": [2023, 2022, 2021, 2020]
}
```

## 3. Get Makes for Year

Get all makes available for a specific year.

### Request

```bash
curl "https://example.com/api/ymm.php?action=make&year=2020"
```

### Response

```json
{
    "success": true,
    "data": ["Ford", "Honda", "Toyota"]
}
```

## 4. Get Models for Year/Make

Get all models available for a year/make combination.

### Request

```bash
curl "https://example.com/api/ymm.php?action=model&year=2020&make=Toyota"
```

### Response

```json
{
    "success": true,
    "data": ["Camry", "Corolla", "RAV4"]
}
```

## 5. Get Trims for Year/Make/Model

Get all trims available for a year/make/model combination.

### Request

```bash
curl "https://example.com/api/ymm.php?action=trim&year=2020&make=Toyota&model=Camry"
```

### Response

```json
{
    "success": true,
    "data": ["LE", "XLE", "XSE"]
}
```

## 6. Get Matching Tires

Get compatible tires for a vehicle configuration.

### Request

```bash
curl "https://example.com/api/tires.php?year=2020&make=Toyota&model=Camry&trim=LE"
```

### Response

```json
{
    "success": true,
    "data": {
        "vehicle": {
            "year": 2020,
            "make": "Toyota",
            "model": "Camry",
            "trim": "LE"
        },
        "fitment": {
            "front_tire": "215/55R17",
            "rear_tire": "215/55R17",
            "is_staggered": false,
            "notes": "Standard trim"
        },
        "tires": {
            "front": [
                {
                    "id": 1,
                    "brand": "Michelin",
                    "model": "Defender T+H",
                    "tire_size": "215/55R17",
                    "load_index": "94",
                    "speed_rating": "H",
                    "season": "all-season",
                    "price": "125.99",
                    "stock": 45,
                    "description": "Long-lasting all-season tire"
                }
            ],
            "rear": []
        }
    }
}
```

## Complete Workflow Example

Here's a complete example of finding tires for a vehicle:

```javascript
// Step 1: Decode VIN
const vinResult = await decodeVIN('1HGBH41JXMN109186');
if (!vinResult.success) {
    console.error('VIN decode failed:', vinResult.message);
    return;
}

const vehicle = vinResult.data.vehicle;
const trims = vinResult.data.trims;

// Step 2: If multiple trims, select one (or use first available)
const selectedTrim = trims.length > 0 ? trims[0] : null;

// Step 3: Get matching tires
const tiresUrl = new URL('/api/tires.php', window.location.origin);
tiresUrl.searchParams.append('year', vehicle.year);
tiresUrl.searchParams.append('make', vehicle.make);
tiresUrl.searchParams.append('model', vehicle.model);
if (selectedTrim) {
    tiresUrl.searchParams.append('trim', selectedTrim);
}

const tiresResponse = await fetch(tiresUrl);
const tiresData = await tiresResponse.json();

if (tiresData.success) {
    console.log('Found tires:', tiresData.data.tires);
    console.log('Vehicle fitment:', tiresData.data.fitment);
} else {
    console.error('No tires found:', tiresData.message);
}
```

## Error Handling

All endpoints return errors in a consistent format:

```json
{
    "success": false,
    "message": "Error description",
    "errors": ["Additional error details"]
}
```

HTTP status codes:
- `200` - Success
- `400` - Bad Request (validation errors)
- `404` - Not Found (no matching data)
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Rate Limiting

The NHTSA API has no official rate limits, but consider:
- Implementing client-side throttling for VIN requests
- Caching decoded VIN results if needed (remember: don't store VINs long-term)
- Using the YMM endpoints instead of VIN when possible (no external API calls)
