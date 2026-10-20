-- ExploreBangla Travel Database (Updated)
CREATE DATABASE IF NOT EXISTS ExploreBangla_Travel;
USE ExploreBangla_Travel;

-- Users
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS booking_seats;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS route_guides;
DROP TABLE IF EXISTS guides;
DROP TABLE IF EXISTS route_fares;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS transport_types;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('traveler','admin') NOT NULL DEFAULT 'traveler'
);

-- Default admin
INSERT INTO users (name, username, password, user_type)
VALUES ('Admin', 'admin', 'admin123', 'admin');

-- Transport Types
CREATE TABLE transport_types (
    transport_type_id INT AUTO_INCREMENT PRIMARY KEY,
    transport_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO transport_types (transport_name) VALUES
('Bus'),('Train'),('Plane');

-- Routes
CREATE TABLE routes (
    route_id INT AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(60) NOT NULL,
    destination VARCHAR(60) NOT NULL,
    UNIQUE KEY uniq_route (origin, destination)
);

INSERT INTO routes (origin, destination) VALUES
('Dhaka','Chittagong'),
('Chittagong','Dhaka'),
('Sylhet','Chittagong'),
('Chittagong','Sylhet'),
('Dhaka','Cox\'s Bazar'),
('Cox\'s Bazar','Dhaka');

-- Fare per (route + transport + class)
CREATE TABLE route_fares (
    fare_id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    transport_type_id INT NOT NULL,
    travel_class ENUM('Economy','Business') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'BDT',
    UNIQUE KEY uniq_fare (route_id, transport_type_id, travel_class),
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (transport_type_id) REFERENCES transport_types(transport_type_id) ON DELETE CASCADE
);

-- Default fares (BDT) for every route × transport × class
-- You can adjust anytime from Admin → Manage Fares.
INSERT INTO route_fares (route_id, transport_type_id, travel_class, amount, currency) VALUES
-- Dhaka → Chittagong (route_id = 1)
(1, 1, 'Economy', 800.00, 'BDT'),
(1, 1, 'Business', 1200.00, 'BDT'),
(1, 2, 'Economy', 600.00, 'BDT'),
(1, 2, 'Business', 1000.00, 'BDT'),
(1, 3, 'Economy', 4500.00, 'BDT'),
(1, 3, 'Business', 6500.00, 'BDT'),

-- Chittagong → Dhaka (route_id = 2)
(2, 1, 'Economy', 800.00, 'BDT'),
(2, 1, 'Business', 1200.00, 'BDT'),
(2, 2, 'Economy', 600.00, 'BDT'),
(2, 2, 'Business', 1000.00, 'BDT'),
(2, 3, 'Economy', 4500.00, 'BDT'),
(2, 3, 'Business', 6500.00, 'BDT'),

-- Sylhet → Chittagong (route_id = 3)
(3, 1, 'Economy', 900.00, 'BDT'),
(3, 1, 'Business', 1300.00, 'BDT'),
(3, 2, 'Economy', 700.00, 'BDT'),
(3, 2, 'Business', 1100.00, 'BDT'),
(3, 3, 'Economy', 4800.00, 'BDT'),
(3, 3, 'Business', 7000.00, 'BDT'),

-- Chittagong → Sylhet (route_id = 4)
(4, 1, 'Economy', 900.00, 'BDT'),
(4, 1, 'Business', 1300.00, 'BDT'),
(4, 2, 'Economy', 700.00, 'BDT'),
(4, 2, 'Business', 1100.00, 'BDT'),
(4, 3, 'Economy', 4800.00, 'BDT'),
(4, 3, 'Business', 7000.00, 'BDT'),

-- Dhaka → Cox's Bazar (route_id = 5)
(5, 1, 'Economy', 1200.00, 'BDT'),
(5, 1, 'Business', 1700.00, 'BDT'),
(5, 2, 'Economy', 900.00, 'BDT'),
(5, 2, 'Business', 1400.00, 'BDT'),
(5, 3, 'Economy', 5500.00, 'BDT'),
(5, 3, 'Business', 8000.00, 'BDT'),

-- Cox's Bazar → Dhaka (route_id = 6)
(6, 1, 'Economy', 1200.00, 'BDT'),
(6, 1, 'Business', 1700.00, 'BDT'),
(6, 2, 'Economy', 900.00, 'BDT'),
(6, 2, 'Business', 1400.00, 'BDT'),
(6, 3, 'Economy', 5500.00, 'BDT'),
(6, 3, 'Business', 8000.00, 'BDT');

-- Guides
CREATE TABLE guides (
    GID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    trips_done INT NOT NULL DEFAULT 0
);

-- Assign a guide to a route+transport (traveler doesn't choose)
CREATE TABLE route_guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    transport_type_id INT NOT NULL,
    GID INT NOT NULL,
    UNIQUE KEY uniq_route_transport (route_id, transport_type_id),
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (transport_type_id) REFERENCES transport_types(transport_type_id) ON DELETE CASCADE,
    FOREIGN KEY (GID) REFERENCES guides(GID) ON DELETE CASCADE
);

-- Bookings
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    route_id INT NOT NULL,
    transport_type_id INT NOT NULL,
    travel_class ENUM('Economy','Business') NOT NULL,
    journey_gender ENUM('Mixed','All Male','All Female') NOT NULL DEFAULT 'Mixed',
    guide_id INT NULL,
    seat_count INT NOT NULL DEFAULT 1,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Confirmed',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (transport_type_id) REFERENCES transport_types(transport_type_id) ON DELETE CASCADE,
    FOREIGN KEY (guide_id) REFERENCES guides(GID) ON DELETE SET NULL
);

-- Booking seats (1 to 6 seats per booking)
CREATE TABLE booking_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_code VARCHAR(10) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Payments
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'BDT',
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('Completed', 'Pending', 'Failed') NOT NULL DEFAULT 'Completed',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);


-- Trip Ratings (one rating per booking)
CREATE TABLE trip_ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    route_id INT NOT NULL,
    transport_type_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_booking_rating (booking_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (transport_type_id) REFERENCES transport_types(transport_type_id) ON DELETE CASCADE
);
