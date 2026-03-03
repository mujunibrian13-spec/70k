-- ============================================
-- Create deleted_members table for backup
-- ============================================
CREATE TABLE IF NOT EXISTS deleted_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    national_id VARCHAR(50),
    address TEXT,
    savings_amount DECIMAL(15, 2),
    status VARCHAR(50),
    date_joined DATE,
    nin VARCHAR(14),
    identification_number VARCHAR(50),
    profile_picture VARCHAR(255),
    occupation VARCHAR(100),
    deleted_by INT,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    member_data LONGTEXT,
    can_restore TINYINT DEFAULT 1,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at)
);

-- ============================================
-- Create deletion_log table for audit trail
-- ============================================
CREATE TABLE IF NOT EXISTS deletion_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    member_email VARCHAR(100),
    member_name VARCHAR(100),
    deleted_by INT,
    reason VARCHAR(255),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    restored TINYINT DEFAULT 0,
    restored_at DATETIME,
    restored_by INT,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at)
);

-- ============================================
-- Create interest_distributions table
-- ============================================
CREATE TABLE IF NOT EXISTS interest_distributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    distribution_month INT NOT NULL,
    distribution_year INT NOT NULL,
    savings_ratio DECIMAL(10, 6),
    total_monthly_interest DECIMAL(15, 2),
    interest_earned DECIMAL(15, 2),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id),
    INDEX idx_month_year (distribution_month, distribution_year)
);

-- ============================================
-- Create member_interest table
-- ============================================
CREATE TABLE IF NOT EXISTS member_interest (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    interest_amount DECIMAL(15, 2) DEFAULT 0,
    distribution_date DATE,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id)
);
