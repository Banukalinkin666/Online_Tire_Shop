-- Comprehensive Vehicle Fitment Data
-- Import this after the initial schema to add more vehicles
-- This expands coverage for common vehicles

-- Additional Toyota vehicles
INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire, notes) VALUES
(2015, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2015, 'Toyota', 'Corolla', 'S', '205/55R16', NULL, 'Sport trim'),
(2016, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2017, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2018, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2019, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2020, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2021, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2022, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2023, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),
(2024, 'Toyota', 'Corolla', 'LE', '205/55R16', NULL, 'Standard trim'),

-- Additional Honda vehicles
(2015, 'Honda', 'Civic', 'LX', '205/55R16', NULL, 'Base trim'),
(2016, 'Honda', 'Civic', 'LX', '205/55R16', NULL, 'Base trim'),
(2017, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2018, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2019, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2020, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2021, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2022, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2023, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),
(2024, 'Honda', 'Civic', 'LX', '215/55R16', NULL, 'Base trim'),

-- Additional Ford vehicles
(2015, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2016, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2017, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2018, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2019, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2020, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2022, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2023, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),
(2024, 'Ford', 'F-150', 'XL', '275/65R18', NULL, 'Base truck'),

-- Chevrolet vehicles
(2020, 'Chevrolet', 'Silverado', '1500', '265/70R17', NULL, 'Base truck'),
(2021, 'Chevrolet', 'Silverado', '1500', '265/70R17', NULL, 'Base truck'),
(2022, 'Chevrolet', 'Silverado', '1500', '265/70R17', NULL, 'Base truck'),
(2023, 'Chevrolet', 'Silverado', '1500', '265/70R17', NULL, 'Base truck'),
(2020, 'Chevrolet', 'Equinox', 'LS', '225/65R17', NULL, 'Base trim'),
(2021, 'Chevrolet', 'Equinox', 'LS', '225/65R17', NULL, 'Base trim'),
(2022, 'Chevrolet', 'Equinox', 'LS', '225/65R17', NULL, 'Base trim'),

-- Nissan vehicles
(2020, 'Nissan', 'Altima', 'S', '215/60R16', NULL, 'Base trim'),
(2021, 'Nissan', 'Altima', 'S', '215/60R16', NULL, 'Base trim'),
(2022, 'Nissan', 'Altima', 'S', '215/60R16', NULL, 'Base trim'),
(2023, 'Nissan', 'Altima', 'S', '215/60R16', NULL, 'Base trim'),
(2020, 'Nissan', 'Rogue', 'S', '225/65R17', NULL, 'Base trim'),
(2021, 'Nissan', 'Rogue', 'S', '225/65R17', NULL, 'Base trim'),
(2022, 'Nissan', 'Rogue', 'S', '225/65R17', NULL, 'Base trim'),

-- Hyundai vehicles
(2020, 'Hyundai', 'Elantra', 'SE', '205/55R16', NULL, 'Base trim'),
(2021, 'Hyundai', 'Elantra', 'SE', '205/55R16', NULL, 'Base trim'),
(2022, 'Hyundai', 'Elantra', 'SE', '205/55R16', NULL, 'Base trim'),
(2023, 'Hyundai', 'Elantra', 'SE', '205/55R16', NULL, 'Base trim'),
(2020, 'Hyundai', 'Tucson', 'SE', '225/60R17', NULL, 'Base trim'),
(2021, 'Hyundai', 'Tucson', 'SE', '225/60R17', NULL, 'Base trim'),

-- Kia vehicles
(2020, 'Kia', 'Forte', 'LX', '205/55R16', NULL, 'Base trim'),
(2021, 'Kia', 'Forte', 'LX', '205/55R16', NULL, 'Base trim'),
(2022, 'Kia', 'Forte', 'LX', '205/55R16', NULL, 'Base trim'),
(2020, 'Kia', 'Sportage', 'LX', '225/60R17', NULL, 'Base trim'),
(2021, 'Kia', 'Sportage', 'LX', '225/60R17', NULL, 'Base trim'),

-- Subaru vehicles
(2020, 'Subaru', 'Outback', 'Base', '225/65R17', NULL, 'Base trim'),
(2021, 'Subaru', 'Outback', 'Base', '225/65R17', NULL, 'Base trim'),
(2022, 'Subaru', 'Outback', 'Base', '225/65R17', NULL, 'Base trim'),
(2020, 'Subaru', 'Forester', 'Base', '225/60R18', NULL, 'Base trim'),
(2021, 'Subaru', 'Forester', 'Base', '225/60R18', NULL, 'Base trim'),

-- Jeep vehicles
(2020, 'Jeep', 'Wrangler', 'Sport', '255/75R17', NULL, 'Base trim'),
(2021, 'Jeep', 'Wrangler', 'Sport', '255/75R17', NULL, 'Base trim'),
(2022, 'Jeep', 'Wrangler', 'Sport', '255/75R17', NULL, 'Base trim'),
(2020, 'Jeep', 'Grand Cherokee', 'Laredo', '265/60R18', NULL, 'Base trim'),
(2021, 'Jeep', 'Grand Cherokee', 'Laredo', '265/60R18', NULL, 'Base trim'),

-- Ram vehicles
(2020, 'Ram', '1500', 'Tradesman', '275/70R18', NULL, 'Base truck'),
(2021, 'Ram', '1500', 'Tradesman', '275/70R18', NULL, 'Base truck'),
(2022, 'Ram', '1500', 'Tradesman', '275/70R18', NULL, 'Base truck'),

-- GMC vehicles
(2020, 'GMC', 'Sierra', '1500', '265/70R17', NULL, 'Base truck'),
(2021, 'GMC', 'Sierra', '1500', '265/70R17', NULL, 'Base truck'),
(2020, 'GMC', 'Yukon', 'SLE', '265/65R18', NULL, 'Base trim'),

-- Additional tire sizes for these vehicles
ON CONFLICT DO NOTHING;

-- Add more tire inventory to match new vehicle sizes
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
-- 205/55R16 tires (Corolla, Elantra, Forte)
('Michelin', 'Defender T+H', '205/55R16', '91', 'H', 'all-season', 115.99, 50, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '205/55R16', '91', 'V', 'all-season', 108.50, 45, 'Comfortable touring tire'),
('Goodyear', 'Assurance All-Season', '205/55R16', '91', 'H', 'all-season', 112.00, 42, 'Reliable all-season tire'),

-- 215/55R16 tires (Civic)
('Michelin', 'Defender T+H', '215/55R16', '93', 'H', 'all-season', 118.99, 48, 'Long-lasting all-season tire'),
('Continental', 'TrueContact Tour', '215/55R16', '93', 'H', 'all-season', 110.00, 40, 'Fuel-efficient tire'),

-- 215/60R16 tires (Altima)
('Michelin', 'Defender T+H', '215/60R16', '95', 'H', 'all-season', 122.99, 35, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '215/60R16', '95', 'V', 'all-season', 115.50, 38, 'Comfortable touring tire'),

-- 225/60R17 tires (Tucson, Sportage)
('Michelin', 'Defender T+H', '225/60R17', '99', 'H', 'all-season', 135.99, 30, 'Long-lasting all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '225/60R17', '99', 'H', 'all-season', 128.00, 32, 'SUV touring tire'),

-- 225/65R17 tires (Equinox, Rogue)
('Michelin', 'Defender LTX M/S', '225/65R17', '102', 'H', 'all-season', 145.99, 28, 'SUV all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '225/65R17', '102', 'H', 'all-season', 138.00, 25, 'SUV tire'),

-- 225/60R18 tires (Forester)
('Michelin', 'CrossClimate2', '225/60R18', '100', 'H', 'all-season', 155.99, 22, 'All-weather tire'),
('Continental', 'CrossContact LX25', '225/60R18', '100', 'H', 'all-season', 148.00, 20, 'SUV touring tire'),

-- 255/75R17 tires (Wrangler)
('BFGoodrich', 'All-Terrain T/A KO2', '255/75R17', '110', 'S', 'all-season', 195.99, 18, 'Off-road tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '255/75R17', '110', 'S', 'all-season', 185.00, 15, 'Rugged tire'),

-- 265/60R18 tires (Grand Cherokee)
('Michelin', 'Latitude Tour HP', '265/60R18', '110', 'H', 'all-season', 175.99, 20, 'SUV performance tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '265/60R18', '110', 'H', 'all-season', 168.00, 18, 'SUV touring tire'),

-- 265/65R18 tires (Yukon)
('Michelin', 'Defender LTX M/S', '265/65R18', '114', 'H', 'all-season', 185.99, 15, 'SUV all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '265/65R18', '114', 'H', 'all-season', 178.00, 12, 'SUV tire'),

-- 265/70R17 tires (Silverado, Sierra)
('Michelin', 'Defender LTX M/S', '265/70R17', '115', 'S', 'all-season', 195.99, 25, 'Truck all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '265/70R17', '115', 'S', 'all-season', 188.00, 22, 'Truck tire'),

-- 275/70R18 tires (Ram 1500)
('Michelin', 'Defender LTX M/S', '275/70R18', '116', 'S', 'all-season', 205.99, 20, 'Truck all-season tire'),
('BFGoodrich', 'All-Terrain T/A KO2', '275/70R18', '116', 'S', 'all-season', 225.00, 18, 'Off-road truck tire')

ON CONFLICT DO NOTHING;
