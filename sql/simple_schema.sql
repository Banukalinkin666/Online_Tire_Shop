-- Simple VIN Lookup Database Schema
-- Free-only public web application

-- Vehicle cache table (stores decoded VIN data)
CREATE TABLE IF NOT EXISTS vehicle_cache (
    vin VARCHAR(17) PRIMARY KEY,
    year INT NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    trim VARCHAR(100) DEFAULT NULL,
    body_class VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_year_make_model (year, make, model)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tire specifications table (stores tire sizes by vehicle)
CREATE TABLE IF NOT EXISTS tire_specs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    trim VARCHAR(100) DEFAULT NULL,
    front_tire VARCHAR(20) NOT NULL,
    rear_tire VARCHAR(20) DEFAULT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicle (year, make, model, trim),
    INDEX idx_fallback (year, make, model)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample tire data (for testing)
INSERT INTO tire_specs (year, make, model, trim, front_tire, rear_tire, verified) VALUES
(2020, 'Toyota', 'Camry', 'LE', '215/55R17', NULL, TRUE),
(2020, 'Toyota', 'Camry', 'XLE', '215/55R17', NULL, TRUE),
(2020, 'Toyota', 'Camry', NULL, '215/55R17', NULL, FALSE), -- Fallback
(2020, 'Honda', 'Accord', 'LX', '235/45R18', NULL, TRUE),
(2020, 'Honda', 'Accord', NULL, '235/45R18', NULL, FALSE), -- Fallback
(2015, 'Chevrolet', 'Equinox', NULL, '225/65R17', NULL, FALSE), -- Fallback
(2015, 'Chevrolet', 'Silverado', NULL, '265/70R17', NULL, FALSE), -- Fallback
(2017, 'Hyundai', 'Elantra', NULL, '205/55R16', NULL, FALSE); -- Fallback
