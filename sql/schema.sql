-- Ilala Smart Referee Management System
-- Database Schema

CREATE DATABASE IF NOT EXISTS ilala_referees CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ilala_referees;

-- Users & Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Referees
CREATE TABLE referees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    license_number VARCHAR(50),
    category ENUM('FIFA', 'National', 'Regional', 'Local') DEFAULT 'Local',
    level ENUM('Beginner', 'Intermediate', 'Advanced', 'Elite') DEFAULT 'Beginner',
    specialization ENUM('Center', 'Assistant', 'Fourth Official', 'VAR') DEFAULT 'Center',
    years_experience INT DEFAULT 0,
    date_of_birth DATE,
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    bank_name VARCHAR(100),
    bank_account VARCHAR(50),
    registration_status ENUM('Pending', 'Approved', 'Rejected', 'Suspended') DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Venues
CREATE TABLE venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) DEFAULT 'Ilala',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    capacity INT,
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    facilities TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Matches
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    venue_id INT,
    match_date DATE NOT NULL,
    kickoff_time TIME NOT NULL,
    competition VARCHAR(100),
    season VARCHAR(20),
    match_type ENUM('League', 'Cup', 'Friendly', 'Training') DEFAULT 'League',
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled', 'Postponed') DEFAULT 'Scheduled',
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Referee Assignments
CREATE TABLE referee_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    referee_id INT NOT NULL,
    role ENUM('Center Referee', 'Assistant Referee 1', 'Assistant Referee 2', 'Fourth Official', 'VAR') NOT NULL,
    assignment_status ENUM('Pending', 'Accepted', 'Declined', 'Confirmed') DEFAULT 'Pending',
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_at TIMESTAMP NULL,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (referee_id) REFERENCES referees(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_match_role (match_id, role)
);

-- Arrival Confirmations
CREATE TABLE arrival_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    referee_id INT NOT NULL,
    match_id INT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    arrival_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('On Time', 'Late', 'Early') DEFAULT 'On Time',
    notes TEXT,
    FOREIGN KEY (assignment_id) REFERENCES referee_assignments(id),
    FOREIGN KEY (referee_id) REFERENCES referees(id),
    FOREIGN KEY (match_id) REFERENCES matches(id)
);

-- Match Reports
CREATE TABLE match_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    referee_id INT NOT NULL,
    report_type ENUM('Pre-Match', 'Post-Match', 'Incident') DEFAULT 'Post-Match',
    summary TEXT NOT NULL,
    incidents TEXT,
    cards_yellow INT DEFAULT 0,
    cards_red INT DEFAULT 0,
    weather_conditions VARCHAR(100),
    pitch_condition ENUM('Excellent', 'Good', 'Fair', 'Poor') DEFAULT 'Good',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Draft', 'Submitted', 'Reviewed', 'Approved') DEFAULT 'Submitted',
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (referee_id) REFERENCES referees(id)
);

-- Videos
CREATE TABLE match_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT,
    duration_seconds INT,
    video_type ENUM('Full Match', 'Highlights', 'Incident', 'Training') DEFAULT 'Full Match',
    upload_status ENUM('Processing', 'Ready', 'Failed') DEFAULT 'Ready',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Video Assessments
CREATE TABLE video_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    assessor_id INT NOT NULL,
    referee_id INT,
    decision_accuracy DECIMAL(5, 2),
    positioning_score DECIMAL(5, 2),
    communication_score DECIMAL(5, 2),
    fitness_score DECIMAL(5, 2),
    overall_score DECIMAL(5, 2),
    feedback TEXT,
    key_moments TEXT,
    assessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES match_videos(id),
    FOREIGN KEY (assessor_id) REFERENCES users(id),
    FOREIGN KEY (referee_id) REFERENCES referees(id)
);

-- Payments & Allowances
CREATE TABLE match_allowances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_type ENUM('Center Referee', 'Assistant Referee', 'Fourth Official', 'VAR') NOT NULL,
    match_type ENUM('League', 'Cup', 'Friendly', 'Training') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TZS',
    effective_from DATE NOT NULL,
    effective_to DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referee_id INT NOT NULL,
    match_id INT,
    assignment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('Match Allowance', 'Travel', 'Bonus', 'Training') DEFAULT 'Match Allowance',
    status ENUM('Pending', 'Verified', 'Paid', 'Rejected') DEFAULT 'Pending',
    payment_method ENUM('Bank Transfer', 'Mobile Money', 'Cash') DEFAULT 'Bank Transfer',
    reference_number VARCHAR(100),
    verified_by INT,
    verified_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referee_id) REFERENCES referees(id),
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (assignment_id) REFERENCES referee_assignments(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Training
CREATE TABLE training_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    trainer_id INT,
    venue_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    max_participants INT DEFAULT 30,
    training_type ENUM('Fitness', 'Rules', 'VAR', 'Physical', 'Workshop') DEFAULT 'Rules',
    status ENUM('Scheduled', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id),
    FOREIGN KEY (venue_id) REFERENCES venues(id)
);

CREATE TABLE training_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    referee_id INT NOT NULL,
    attendance_status ENUM('Registered', 'Present', 'Absent', 'Excused') DEFAULT 'Registered',
    score DECIMAL(5, 2),
    feedback TEXT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES training_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (referee_id) REFERENCES referees(id),
    UNIQUE KEY unique_training_referee (training_id, referee_id)
);

-- Licenses
CREATE TABLE licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referee_id INT NOT NULL,
    license_type ENUM('FIFA', 'National', 'Regional', 'Local') NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    issuing_authority VARCHAR(100) DEFAULT 'TFF',
    status ENUM('Active', 'Expired', 'Suspended', 'Revoked') DEFAULT 'Active',
    document_path VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referee_id) REFERENCES referees(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('Assignment', 'Payment', 'Training', 'License', 'System', 'Match') DEFAULT 'System',
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity Log
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50),
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed Data
INSERT INTO roles (name, description) VALUES
('Admin', 'System administrator with full access'),
('Referee', 'Registered match official'),
('Assigner', 'Match assignment coordinator'),
('Assessor', 'Video and performance assessor'),
('Finance', 'Payment and allowance manager');

INSERT INTO users (role_id, username, email, password_hash, full_name, phone) VALUES
(1, 'admin', 'admin@ilala-referees.go.tz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+255700000001');

INSERT INTO venues (name, address, city, latitude, longitude, capacity, contact_person, contact_phone) VALUES
('National Stadium', 'Taifa Road, Dar es Salaam', 'Ilala', -6.8160, 39.2803, 60000, 'Stadium Manager', '+255700000100'),
('Uhuru Stadium', 'Mwalimu Nyerere Road, Ilala', 'Ilala', -6.8235, 39.2695, 25000, 'Venue Coordinator', '+255700000101'),
('Azam Complex', 'Chamazi, Temeke', 'Temeke', -6.9500, 39.3200, 10000, 'Azam FC Admin', '+255700000102');

INSERT INTO match_allowances (role_type, match_type, amount, effective_from) VALUES
('Center Referee', 'League', 150000.00, '2024-01-01'),
('Assistant Referee', 'League', 100000.00, '2024-01-01'),
('Fourth Official', 'League', 75000.00, '2024-01-01'),
('Center Referee', 'Cup', 200000.00, '2024-01-01'),
('Assistant Referee', 'Cup', 130000.00, '2024-01-01');

-- Default password for admin: password
