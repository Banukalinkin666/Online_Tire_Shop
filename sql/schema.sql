-- Tire Shop Database Schema
-- Compatible with MySQL 5.7+ and MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS tire_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tire_shop;

-- Vehicle fitment table
CREATE TABLE IF NOT EXISTS vehicle_fitment (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    trim VARCHAR(150) DEFAULT NULL,
    front_tire VARCHAR(50) NOT NULL COMMENT 'Format: 225/65R17 or similar',
    rear_tire VARCHAR(50) DEFAULT NULL COMMENT 'NULL if same as front (non-staggered)',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (year),
    INDEX idx_make (make),
    INDEX idx_model (model),
    INDEX idx_year_make_model (year, make, model),
    INDEX idx_trim (trim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tires inventory table
CREATE TABLE IF NOT EXISTS tires (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(150) NOT NULL,
    tire_size VARCHAR(50) NOT NULL COMMENT 'Format: 225/65R17 or similar',
    load_index VARCHAR(10) DEFAULT NULL COMMENT 'e.g., 102',
    speed_rating VARCHAR(2) DEFAULT NULL COMMENT 'e.g., H, V, W',
    season ENUM('all-season', 'summer', 'winter', 'performance') DEFAULT 'all-season',
    price DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED DEFAULT 0,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tire_size (tire_size),
    INDEX idx_brand (brand),
    INDEX idx_season (season),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data insertion
INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire, notes) VALUES
(2020, 'Toyota', 'Camry', 'LE', '215/55R17', NULL, 'Standard trim'),
(2020, 'Toyota', 'Camry', 'XSE', '235/45R18', NULL, 'Sport trim'),
(2020, 'Honda', 'Accord', 'LX', '215/55R17', NULL, 'Base trim'),
(2020, 'Honda', 'Accord', 'Sport', '235/40R19', NULL, 'Sport trim'),
(2021, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2021, 'Ford', 'F-150', 'Raptor', '315/70R17', '315/70R17', 'Staggered setup'),
(2022, 'BMW', '3 Series', '330i', '225/45R18', '255/40R18', 'Staggered wheels'),
(2022, 'BMW', '3 Series', 'M3', '275/35R19', '285/35R19', 'Performance staggered'),
(2023, 'Tesla', 'Model 3', 'Standard', '235/45R18', NULL, 'Standard range'),
(2023, 'Tesla', 'Model 3', 'Performance', '235/35R20', '275/30R20', 'Performance wheels');

INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '215/55R17', '94', 'H', 'all-season', 125.99, 45, 'Long-lasting all-season tire'),
('Michelin', 'Pilot Sport 4S', '235/45R18', '98', 'Y', 'performance', 189.99, 32, 'Ultra-high performance summer tire'),
('Bridgestone', 'Turanza EL400-02', '215/55R17', '94', 'V', 'all-season', 118.50, 58, 'Comfortable touring tire'),
('Bridgestone', 'Potenza RE980AS', '235/40R19', '96', 'Y', 'all-season', 205.00, 28, 'Ultra-high performance all-season'),
('Goodyear', 'Wrangler All-Terrain Adventure', '275/65R18', '114', 'S', 'all-season', 165.75, 40, 'Rugged all-terrain tire'),
('BFGoodrich', 'All-Terrain T/A KO2', '315/70R17', '113', 'R', 'all-season', 289.99, 15, 'Premium off-road tire'),
('Continental', 'ExtremeContact DWS06 Plus', '225/45R18', '91', 'Y', 'all-season', 145.00, 52, 'Ultra-high performance all-season'),
('Continental', 'ExtremeContact DWS06 Plus', '255/40R18', '95', 'Y', 'all-season', 165.00, 38, 'Ultra-high performance all-season (rear)'),
('Pirelli', 'P Zero', '275/35R19', '100', 'Y', 'performance', 285.00, 22, 'Max performance summer tire'),
('Pirelli', 'P Zero', '285/35R19', '103', 'Y', 'performance', 295.00, 18, 'Max performance summer tire (rear)'),
('Michelin', 'Primacy MXM4', '235/45R18', '94', 'V', 'all-season', 152.99, 41, 'Quiet comfort tire'),
('Michelin', 'CrossClimate2', '235/35R20', '92', 'Y', 'all-season', 245.00, 26, 'All-weather tire'),
('Michelin', 'CrossClimate2', '275/30R20', '97', 'Y', 'all-season', 265.00, 24, 'All-weather tire (rear)'),
('Hankook', 'Ventus V12 evo2', '235/45R18', '98', 'W', 'performance', 135.00, 37, 'Ultra-high performance tire');
