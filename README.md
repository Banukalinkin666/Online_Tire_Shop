# Tire Fitment Finder

A modular, privacy-safe tire fitment web application that allows users to find compatible tires by entering a VIN or selecting Year/Make/Model/Trim. The application is designed to be easily embedded into WordPress via a plugin and shortcode.

## Features

- **VIN Decoding**: Automatically decode vehicle information using NHTSA API
- **Year/Make/Model Search**: Traditional dropdown-based vehicle selection
- **Tire Matching**: Find compatible tires based on OEM fitment data
- **Staggered Support**: Handles vehicles with different front and rear tire sizes
- **Privacy-First**: VINs are never stored in the database
- **WordPress Compatible**: Easy integration via shortcode
- **Mobile-First UI**: Responsive design using Tailwind CSS
- **RESTful API**: Clean API endpoints for AJAX interactions

## Tech Stack

- **Backend**: PHP 8.2+ (OOP, PSR-4 autoloading)
- **Database**: MySQL/MariaDB with PDO
- **Frontend**: HTML5, Tailwind CSS, Alpine.js
- **External API**: NHTSA VIN Decode API (no API key required)

## Project Structure

```
Online_Tire_Shop/
├── public/
│   ├── index.php              # Main entry point
│   └── assets/
│       ├── css/
│       │   └── main.css       # Custom CSS
│       └── js/
│           └── app.js         # Frontend JavaScript
├── app/
│   ├── bootstrap.php          # Autoloader and initialization
│   ├── Controllers/           # (Reserved for future use)
│   ├── Models/
│   │   ├── VehicleFitment.php # Vehicle fitment model
│   │   └── Tire.php           # Tire inventory model
│   ├── Services/
│   │   ├── NHTSAService.php   # NHTSA API integration
│   │   └── TireMatchService.php # Tire matching logic
│   ├── Database/
│   │   └── Connection.php     # Database connection singleton
│   └── Helpers/
│       ├── ResponseHelper.php # JSON response utilities
│       └── InputHelper.php    # Input sanitization
├── api/
│   ├── vin.php                # VIN decode endpoint
│   ├── ymm.php                # Year/Make/Model endpoint
│   └── tires.php              # Tire matching endpoint
├── config/
│   └── database.php           # Database configuration
├── sql/
│   └── schema.sql             # Database schema and sample data
├── wordpress-plugin.php       # WordPress plugin wrapper
└── README.md                  # This file
```

## Installation

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE tire_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the schema:
```bash
mysql -u your_username -p tire_shop < sql/schema.sql
```

Or manually run the SQL file in your database management tool.

### 2. Configure Database Connection

Edit `config/database.php` with your database credentials:

```php
return [
    'host' => 'localhost',
    'dbname' => 'tire_shop',
    'username' => 'your_username',
    'password' => 'your_password',
    // ...
];
```

### 3. Web Server Configuration

#### Option A: Standalone Setup

Point your web server document root to the `public/` directory:

**Apache (.htaccess in public/)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
server {
    listen 80;
    server_name tire-shop.local;
    root /path/to/Online_Tire_Shop/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Option B: WordPress Integration

1. Copy `wordpress-plugin.php` to your WordPress plugins directory:
   ```
   wp-content/plugins/tire-fitment-finder/tire-fitment-finder.php
   ```

2. Ensure the tire fitment application files are accessible from the plugin location (adjust paths if needed).

3. Activate the plugin in WordPress admin.

4. Use the shortcode in any post or page:
   ```
   [tire_fitment]
   ```

### 4. Verify Installation

1. Access the application:
   - Standalone: `http://localhost/public/`
   - WordPress: Visit a page with the `[tire_fitment]` shortcode

2. Test VIN decoding:
   - Enter a valid 17-character VIN (e.g., `1HGBH41JXMN109186`)
   - Or use Year/Make/Model dropdowns

3. Verify tire matching:
   - After selecting a vehicle, compatible tires should display

## Documentation

- **README.md** - This file (installation and overview)
- **QUICK_START.md** - Fast deployment guide (10 minutes)
- **GITHUB_DESKTOP_GUIDE.md** - Detailed guide for GitHub Desktop users
- **RENDER_SETUP.md** - Complete Render deployment instructions
- **DEPLOYMENT_CHECKLIST.md** - Step-by-step checklist
- **EXAMPLES.md** - API usage examples and code samples
- **sql/schema.sql** - Database schema and sample data

## API Endpoints

### POST `/api/vin.php`

Decode a VIN using NHTSA API.

**Request:**
```json
{
    "vin": "1HGBH41JXMN109186"
}
```

**Response:**
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

### GET `/api/ymm.php`

Get Years, Makes, Models, or Trims.

**Examples:**
- `/api/ymm.php?action=year`
- `/api/ymm.php?action=make&year=2020`
- `/api/ymm.php?action=model&year=2020&make=Toyota`
- `/api/ymm.php?action=trim&year=2020&make=Toyota&model=Camry`

**Response:**
```json
{
    "success": true,
    "data": ["2020", "2021", "2022", "2023"]
}
```

### GET `/api/tires.php`

Get matching tires for a vehicle.

**Request:**
```
/api/tires.php?year=2020&make=Toyota&model=Camry&trim=LE
```

**Response:**
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
                    "price": "125.99",
                    "stock": 45,
                    ...
                }
            ],
            "rear": []
        }
    }
}
```

## Database Schema

### `vehicle_fitment`

Stores OEM tire fitment data for vehicles.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| year | YEAR | Vehicle year |
| make | VARCHAR(100) | Vehicle make |
| model | VARCHAR(100) | Vehicle model |
| trim | VARCHAR(150) | Vehicle trim (optional) |
| front_tire | VARCHAR(50) | Front tire size |
| rear_tire | VARCHAR(50) | Rear tire size (NULL if same as front) |
| notes | TEXT | Additional notes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |

**Indexes:** `year`, `make`, `model`, `(year, make, model)`, `trim`

### `tires`

Stores tire inventory.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| brand | VARCHAR(100) | Tire brand |
| model | VARCHAR(150) | Tire model |
| tire_size | VARCHAR(50) | Tire size (e.g., 225/65R17) |
| load_index | VARCHAR(10) | Load index |
| speed_rating | VARCHAR(2) | Speed rating |
| season | ENUM | Season type |
| price | DECIMAL(10,2) | Price per tire |
| stock | INT UNSIGNED | Stock quantity |
| description | TEXT | Description |
| image_url | VARCHAR(255) | Image URL |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |

**Indexes:** `tire_size`, `brand`, `season`, `stock`

## Security Features

- **Input Sanitization**: All user inputs are sanitized using `InputHelper`
- **SQL Injection Prevention**: PDO prepared statements
- **VIN Validation**: Format validation before API calls
- **Privacy**: VINs are never stored in the database
- **Error Handling**: Sensitive errors are logged, not displayed to users
- **CORS Headers**: Configured for API endpoints

## Customization

### Adding Tire Data

Insert new tires into the `tires` table:

```sql
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock)
VALUES ('Brand', 'Model', '225/65R17', '102', 'H', 'all-season', 149.99, 50);
```

### Adding Vehicle Fitment Data

Insert new vehicle fitments:

```sql
INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire)
VALUES (2024, 'Honda', 'Civic', 'EX', '215/55R16', NULL);
```

### Integrating Quote/Cart System

Update the `requestQuote()` and `addToCart()` functions in `public/assets/js/app.js` to integrate with your e-commerce system.

Example integration:
```javascript
async requestQuote(tire) {
    // Send to your quote API
    const response = await fetch('/your-quote-api.php', {
        method: 'POST',
        body: JSON.stringify({ tire_id: tire.id })
    });
    // Handle response
}
```

## Performance Considerations

- **Database Indexes**: Properly indexed for fast lookups on year/make/model and tire_size
- **Caching**: Consider implementing caching for frequently accessed data
- **API Rate Limiting**: NHTSA API has no rate limits, but consider throttling for high traffic
- **Database Optimization**: For 100k+ fitment rows, consider partitioning by year or make

## Troubleshooting

### VIN Decode Fails

- Verify VIN format (17 characters, alphanumeric, no I/O/Q)
- Check internet connection (NHTSA API requires external access)
- Check PHP cURL extension is enabled
- Review error logs for detailed messages

### No Tires Found

- Verify tire sizes match exactly (case-sensitive)
- Check stock levels in database
- Ensure tire sizes are in correct format (e.g., `225/65R17`)

### Database Connection Errors

- Verify credentials in `config/database.php`
- Ensure MySQL service is running
- Check PHP PDO MySQL extension is enabled

## License

This project is provided as-is. Adapt as needed for your business requirements.

## Support

For issues or questions, refer to:
- PHP error logs (check `error_log` setting in php.ini)
- Database query logs (if enabled)
- Browser console for JavaScript errors
- Network tab for API request/response debugging

## Future Enhancements

Potential improvements:
- Tire size search with tolerance/alternatives
- Price comparison across brands
- Wishlist functionality
- User accounts and saved vehicles
- Admin panel for managing inventory
- Analytics and reporting
- Multi-language support
- Enhanced mobile app experience
