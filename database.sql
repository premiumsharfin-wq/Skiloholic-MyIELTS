-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    status ENUM('active', 'banned') DEFAULT 'active',
    email_verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(6),
    verification_expiry DATETIME,
    reset_token VARCHAR(64),
    reset_expiry DATETIME,
    profile_pic VARCHAR(255),
    mobile VARCHAR(20),
    candidate_id VARCHAR(50),
    target_score DECIMAL(3,1),
    exam_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tests Table
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('MyIELTS', 'Cambridge') NOT NULL,
    type ENUM('Full', 'Task 1', 'Task 2') NOT NULL,
    question_data TEXT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Test Results Table
CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    type ENUM('Full', 'Task 1', 'Task 2') NOT NULL,
    submission_data TEXT,
    status ENUM('pending', 'evaluated') DEFAULT 'pending',
    evaluated_by INT,
    feedback TEXT, -- JSON structure for detailed criteria
    score_band DECIMAL(3,1),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    evaluated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Custom Evaluations Table
CREATE TABLE IF NOT EXISTS custom_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    status ENUM('pending', 'evaluated') DEFAULT 'pending',
    evaluated_by INT,
    feedback TEXT,
    score_band DECIMAL(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    evaluated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT
);

-- Email Changes Table (for double verification)
CREATE TABLE IF NOT EXISTS email_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    new_email VARCHAR(100) NOT NULL,
    old_email_code VARCHAR(6) NOT NULL,
    new_email_code VARCHAR(6) NOT NULL,
    old_verified TINYINT(1) DEFAULT 0,
    new_verified TINYINT(1) DEFAULT 0,
    expiry DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin (Password: admin123 - should be changed)
-- Insert Default Admin (Password: admin123)
INSERT INTO users (name, email, password, role, status, email_verified) VALUES
('System Admin', 'admin@myielts.com', '$2y$10$tU9HAeF31C/rwJxRctUHq.7dwND3ZYFlkcINPqptEhp.Q7I9OzFzq', 'admin', 'active', 1);
