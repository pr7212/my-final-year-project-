CREATE DATABASE IF NOT EXISTS garbage_tracker;
USE garbage_tracker;

-- =========================
-- USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    role ENUM('resident','collector','officer','admin')
        NOT NULL DEFAULT 'resident',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_role (role),
    INDEX idx_email (email)
);

-- =========================
-- TRUCKS
-- =========================
CREATE TABLE trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,

    status ENUM('available','busy','maintenance')
        NOT NULL DEFAULT 'available',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status)
);

-- =========================
-- AREAS
-- =========================
CREATE TABLE areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,

    assigned_truck_id INT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_areas_truck
        FOREIGN KEY (assigned_truck_id)
        REFERENCES trucks(id)
        ON DELETE SET NULL,

    INDEX idx_name (name)
);

-- =========================
-- REQUESTS (CORE SYSTEM)
-- =========================
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    area_id INT NOT NULL,
    truck_id INT DEFAULT NULL,

    status ENUM('pending','assigned','in-progress','completed','cancelled')
        NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_requests_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_requests_area
        FOREIGN KEY (area_id)
        REFERENCES areas(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_requests_truck
        FOREIGN KEY (truck_id)
        REFERENCES trucks(id)
        ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_area_id (area_id),
    INDEX idx_truck (truck_id)
);

-- =========================
-- REPORTS (COMPLAINTS)
-- =========================
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,

    status ENUM('pending','resolved')
        NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reports_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_status (status),
    INDEX idx_user (user_id)
);

-- =========================
-- SCHEDULES
-- =========================
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,

    location VARCHAR(255) NOT NULL,
    collection_date DATE NOT NULL,

    status ENUM('scheduled','completed')
        NOT NULL DEFAULT 'scheduled',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_date (collection_date),
    INDEX idx_status (status)
);

-- =========================
-- SEED DATA
-- =========================

INSERT INTO trucks (name, status) VALUES
('Truck A', 'available'),
('Truck B', 'available'),
('Truck C', 'maintenance');

INSERT INTO areas (name, assigned_truck_id) VALUES
('Kasarani', 1),
('Westlands', 2),
('Embakasi', NULL);
