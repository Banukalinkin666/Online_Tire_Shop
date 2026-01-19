-- Production-Ready Comprehensive Tire Inventory
-- Matches all tire sizes from production_data.sql
-- Covers all major brands and popular tire models

-- ============================================
-- COMMON PASSENGER CAR TIRES (195-235 width)
-- ============================================

-- 195/65R15 (Corolla, Sentra older models)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '195/65R15', '91', 'H', 'all-season', 98.99, 60, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '195/65R15', '91', 'H', 'all-season', 92.50, 55, 'Comfortable touring tire'),
('Goodyear', 'Assurance All-Season', '195/65R15', '91', 'H', 'all-season', 95.00, 58, 'Reliable all-season tire'),
('Continental', 'TrueContact Tour', '195/65R15', '91', 'H', 'all-season', 88.00, 52, 'Fuel-efficient tire')

ON CONFLICT DO NOTHING;

-- 205/55R16 (Corolla, Elantra, Forte, Sentra newer)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '205/55R16', '91', 'H', 'all-season', 115.99, 50, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '205/55R16', '91', 'V', 'all-season', 108.50, 45, 'Comfortable touring tire'),
('Goodyear', 'Assurance All-Season', '205/55R16', '91', 'H', 'all-season', 112.00, 42, 'Reliable all-season tire'),
('Continental', 'TrueContact Tour', '205/55R16', '91', 'H', 'all-season', 110.00, 40, 'Fuel-efficient tire'),
('Hankook', 'Kinergy GT', '205/55R16', '91', 'H', 'all-season', 105.00, 38, 'Budget-friendly all-season')

ON CONFLICT DO NOTHING;

-- 215/55R16 (Civic)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '215/55R16', '93', 'H', 'all-season', 118.99, 48, 'Long-lasting all-season tire'),
('Continental', 'TrueContact Tour', '215/55R16', '93', 'H', 'all-season', 112.00, 40, 'Fuel-efficient tire'),
('Bridgestone', 'Turanza EL400-02', '215/55R16', '93', 'V', 'all-season', 115.50, 38, 'Comfortable touring tire')

ON CONFLICT DO NOTHING;

-- 215/55R17 (Camry, Accord)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '215/55R17', '94', 'H', 'all-season', 125.99, 45, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '215/55R17', '94', 'V', 'all-season', 118.50, 58, 'Comfortable touring tire'),
('Goodyear', 'Assurance All-Season', '215/55R17', '94', 'H', 'all-season', 122.00, 42, 'Reliable all-season tire'),
('Continental', 'ExtremeContact DWS06 Plus', '215/55R17', '94', 'V', 'all-season', 128.00, 50, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 215/60R16 (Altima, Camry older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender T+H', '215/60R16', '95', 'H', 'all-season', 122.99, 35, 'Long-lasting all-season tire'),
('Bridgestone', 'Turanza EL400-02', '215/60R16', '95', 'V', 'all-season', 115.50, 38, 'Comfortable touring tire'),
('Goodyear', 'Assurance All-Season', '215/60R16', '95', 'H', 'all-season', 118.00, 40, 'Reliable all-season tire')

ON CONFLICT DO NOTHING;

-- 225/45R17 (BMW 3 Series older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '225/45R17', '91', 'Y', 'performance', 165.99, 30, 'Ultra-high performance summer tire'),
('Continental', 'ExtremeContact DWS06 Plus', '225/45R17', '91', 'Y', 'all-season', 145.00, 45, 'Ultra-high performance all-season'),
('Bridgestone', 'Potenza RE980AS', '225/45R17', '91', 'Y', 'all-season', 155.00, 35, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 225/45R18 (BMW 3 Series)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '225/45R18', '91', 'Y', 'performance', 189.99, 32, 'Ultra-high performance summer tire'),
('Continental', 'ExtremeContact DWS06 Plus', '225/45R18', '91', 'Y', 'all-season', 145.00, 52, 'Ultra-high performance all-season'),
('Bridgestone', 'Potenza RE980AS', '225/45R18', '91', 'Y', 'all-season', 165.00, 40, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 235/40R19 (Accord Sport)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Bridgestone', 'Potenza RE980AS', '235/40R19', '96', 'Y', 'all-season', 205.00, 28, 'Ultra-high performance all-season'),
('Michelin', 'Pilot Sport 4S', '235/40R19', '96', 'Y', 'performance', 215.99, 25, 'Ultra-high performance summer tire'),
('Continental', 'ExtremeContact DWS06 Plus', '235/40R19', '96', 'Y', 'all-season', 195.00, 30, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 235/45R18 (Camry XSE, Model 3)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '235/45R18', '98', 'Y', 'performance', 189.99, 32, 'Ultra-high performance summer tire'),
('Michelin', 'Primacy MXM4', '235/45R18', '94', 'V', 'all-season', 152.99, 41, 'Quiet comfort tire'),
('Hankook', 'Ventus V12 evo2', '235/45R18', '98', 'W', 'performance', 135.00, 37, 'Ultra-high performance tire'),
('Continental', 'ExtremeContact DWS06 Plus', '235/45R18', '98', 'Y', 'all-season', 165.00, 45, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 255/40R17 (BMW 3 Series rear older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '255/40R17', '94', 'Y', 'performance', 175.99, 28, 'Ultra-high performance summer tire'),
('Continental', 'ExtremeContact DWS06 Plus', '255/40R17', '94', 'Y', 'all-season', 155.00, 35, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 255/40R18 (BMW 3 Series rear)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Continental', 'ExtremeContact DWS06 Plus', '255/40R18', '95', 'Y', 'all-season', 165.00, 38, 'Ultra-high performance all-season (rear)'),
('Michelin', 'Pilot Sport 4S', '255/40R18', '95', 'Y', 'performance', 195.99, 30, 'Ultra-high performance summer tire')

ON CONFLICT DO NOTHING;

-- ============================================
-- SUV/CROSSOVER TIRES (225-275 width)
-- ============================================

-- 225/60R17 (Outback, Forester older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '225/60R17', '99', 'H', 'all-season', 135.99, 30, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '225/60R17', '99', 'H', 'all-season', 128.00, 32, 'SUV touring tire'),
('Goodyear', 'Assurance All-Season', '225/60R17', '99', 'H', 'all-season', 132.00, 28, 'Reliable all-season tire')

ON CONFLICT DO NOTHING;

-- 225/60R18 (Forester)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'CrossClimate2', '225/60R18', '100', 'H', 'all-season', 155.99, 22, 'All-weather tire'),
('Continental', 'CrossContact LX25', '225/60R18', '100', 'H', 'all-season', 148.00, 20, 'SUV touring tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '225/60R18', '100', 'H', 'all-season', 152.00, 25, 'SUV touring tire')

ON CONFLICT DO NOTHING;

-- 225/65R17 (RAV4, CR-V, Equinox, Rogue, Escape)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '225/65R17', '102', 'H', 'all-season', 145.99, 28, 'SUV all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '225/65R17', '102', 'H', 'all-season', 138.00, 25, 'SUV tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '225/65R17', '102', 'H', 'all-season', 142.00, 30, 'SUV touring tire'),
('Continental', 'CrossContact LX25', '225/65R17', '102', 'H', 'all-season', 140.00, 27, 'SUV touring tire')

ON CONFLICT DO NOTHING;

-- 235/60R18 (CR-V newer, Pilot)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '235/60R18', '103', 'H', 'all-season', 165.99, 24, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '235/60R18', '103', 'H', 'all-season', 158.00, 22, 'SUV touring tire'),
('Goodyear', 'Assurance All-Season', '235/60R18', '103', 'H', 'all-season', 162.00, 20, 'Reliable all-season tire')

ON CONFLICT DO NOTHING;

-- 235/65R17 (Santa Fe, Sorento)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '235/65R17', '104', 'H', 'all-season', 155.99, 26, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '235/65R17', '104', 'H', 'all-season', 148.00, 24, 'SUV touring tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '235/65R17', '104', 'H', 'all-season', 152.00, 22, 'SUV tire')

ON CONFLICT DO NOTHING;

-- 235/70R16 (Santa Fe older, Sorento older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '235/70R16', '104', 'H', 'all-season', 148.99, 20, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '235/70R16', '104', 'H', 'all-season', 142.00, 18, 'SUV touring tire')

ON CONFLICT DO NOTHING;

-- 245/65R17 (Highlander, Explorer, Pilot)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '245/65R17', '107', 'H', 'all-season', 175.99, 22, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '245/65R17', '107', 'H', 'all-season', 168.00, 20, 'SUV touring tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '245/65R17', '107', 'H', 'all-season', 172.00, 18, 'SUV tire')

ON CONFLICT DO NOTHING;

-- 255/65R18 (Explorer newer)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '255/65R18', '111', 'H', 'all-season', 195.99, 18, 'SUV all-season tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '255/65R18', '111', 'H', 'all-season', 188.00, 16, 'SUV touring tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '255/65R18', '111', 'H', 'all-season', 192.00, 15, 'SUV tire')

ON CONFLICT DO NOTHING;

-- 255/45R19 (Model Y)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'CrossClimate2', '255/45R19', '104', 'Y', 'all-season', 225.00, 20, 'All-weather tire'),
('Continental', 'ExtremeContact DWS06 Plus', '255/45R19', '104', 'Y', 'all-season', 215.00, 18, 'Ultra-high performance all-season')

ON CONFLICT DO NOTHING;

-- 265/60R18 (Grand Cherokee)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Latitude Tour HP', '265/60R18', '110', 'H', 'all-season', 175.99, 20, 'SUV performance tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '265/60R18', '110', 'H', 'all-season', 168.00, 18, 'SUV touring tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '265/60R18', '110', 'H', 'all-season', 172.00, 16, 'SUV tire')

ON CONFLICT DO NOTHING;

-- 265/65R18 (Yukon)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '265/65R18', '114', 'H', 'all-season', 185.99, 15, 'SUV all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '265/65R18', '114', 'H', 'all-season', 178.00, 12, 'SUV tire'),
('Bridgestone', 'Dueler H/L Alenza Plus', '265/65R18', '114', 'H', 'all-season', 182.00, 14, 'SUV touring tire')

ON CONFLICT DO NOTHING;

-- 265/70R17 (Silverado, Sierra, Tahoe)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '265/70R17', '115', 'S', 'all-season', 195.99, 25, 'Truck all-season tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '265/70R17', '115', 'S', 'all-season', 188.00, 22, 'Truck tire'),
('BFGoodrich', 'All-Terrain T/A KO2', '265/70R17', '115', 'S', 'all-season', 225.00, 20, 'Off-road truck tire')

ON CONFLICT DO NOTHING;

-- ============================================
-- TRUCK/OFF-ROAD TIRES (275-315 width)
-- ============================================

-- 275/65R18 (F-150)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Goodyear', 'Wrangler All-Terrain Adventure', '275/65R18', '114', 'S', 'all-season', 165.75, 40, 'Rugged all-terrain tire'),
('Michelin', 'Defender LTX M/S', '275/65R18', '114', 'S', 'all-season', 185.99, 35, 'Truck all-season tire'),
('BFGoodrich', 'All-Terrain T/A KO2', '275/65R18', '114', 'S', 'all-season', 205.00, 30, 'Off-road truck tire')

ON CONFLICT DO NOTHING;

-- 275/70R18 (Ram 1500)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Defender LTX M/S', '275/70R18', '116', 'S', 'all-season', 205.99, 20, 'Truck all-season tire'),
('BFGoodrich', 'All-Terrain T/A KO2', '275/70R18', '116', 'S', 'all-season', 225.00, 18, 'Off-road truck tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '275/70R18', '116', 'S', 'all-season', 198.00, 22, 'Truck tire')

ON CONFLICT DO NOTHING;

-- 255/75R17 (Wrangler)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('BFGoodrich', 'All-Terrain T/A KO2', '255/75R17', '110', 'S', 'all-season', 195.99, 18, 'Off-road tire'),
('Goodyear', 'Wrangler All-Terrain Adventure', '255/75R17', '110', 'S', 'all-season', 185.00, 15, 'Rugged tire'),
('Michelin', 'Defender LTX M/S', '255/75R17', '110', 'S', 'all-season', 205.99, 12, 'All-season tire')

ON CONFLICT DO NOTHING;

-- 315/70R17 (F-150 Raptor)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('BFGoodrich', 'All-Terrain T/A KO2', '315/70R17', '113', 'R', 'all-season', 289.99, 15, 'Premium off-road tire'),
('Goodyear', 'Wrangler MT/R', '315/70R17', '113', 'R', 'all-season', 275.00, 12, 'Mud-terrain tire')

ON CONFLICT DO NOTHING;

-- ============================================
-- PERFORMANCE TIRES (255-285 width)
-- ============================================

-- 255/35R19 (BMW M3 older)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '255/35R19', '96', 'Y', 'performance', 245.99, 20, 'Ultra-high performance summer tire'),
('Pirelli', 'P Zero', '255/35R19', '96', 'Y', 'performance', 255.00, 18, 'Max performance summer tire')

ON CONFLICT DO NOTHING;

-- 275/35R19 (BMW M3)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Pirelli', 'P Zero', '275/35R19', '100', 'Y', 'performance', 285.00, 22, 'Max performance summer tire'),
('Michelin', 'Pilot Sport 4S', '275/35R19', '100', 'Y', 'performance', 275.99, 20, 'Ultra-high performance summer tire')

ON CONFLICT DO NOTHING;

-- 285/35R19 (BMW M3 rear)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Pirelli', 'P Zero', '285/35R19', '103', 'Y', 'performance', 295.00, 18, 'Max performance summer tire (rear)'),
('Michelin', 'Pilot Sport 4S', '285/35R19', '103', 'Y', 'performance', 285.99, 16, 'Ultra-high performance summer tire')

ON CONFLICT DO NOTHING;

-- 235/35R20 (Model 3 Performance front)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'CrossClimate2', '235/35R20', '92', 'Y', 'all-season', 245.00, 26, 'All-weather tire'),
('Pirelli', 'P Zero', '235/35R20', '92', 'Y', 'performance', 255.00, 22, 'Max performance summer tire')

ON CONFLICT DO NOTHING;

-- 275/30R20 (Model 3 Performance rear)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'CrossClimate2', '275/30R20', '97', 'Y', 'all-season', 265.00, 24, 'All-weather tire (rear)'),
('Pirelli', 'P Zero', '275/30R20', '97', 'Y', 'performance', 275.00, 20, 'Max performance summer tire')

ON CONFLICT DO NOTHING;

-- 255/40R21 (Model Y Performance front)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '255/40R21', '102', 'Y', 'performance', 295.99, 15, 'Ultra-high performance summer tire'),
('Pirelli', 'P Zero', '255/40R21', '102', 'Y', 'performance', 305.00, 12, 'Max performance summer tire')

ON CONFLICT DO NOTHING;

-- 275/40R21 (Model Y Performance rear)
INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) VALUES
('Michelin', 'Pilot Sport 4S', '275/40R21', '107', 'Y', 'performance', 315.99, 14, 'Ultra-high performance summer tire'),
('Pirelli', 'P Zero', '275/40R21', '107', 'Y', 'performance', 325.00, 12, 'Max performance summer tire')

ON CONFLICT DO NOTHING;
