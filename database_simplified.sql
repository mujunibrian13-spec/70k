-- ============================================
-- SIMPLIFIED DATABASE SCHEMA FOR OLDER MySQL
-- Compatible with MySQL 5.5+ and WampServer
-- ============================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS savings_loans_db;
USE savings_loans_db;

-- ============================================
-- TABLE: users (Login accounts)
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: members (Member profiles)
-- ============================================
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    nin VARCHAR(14) UNIQUE NOT NULL COMMENT 'National ID Number - 14 alphanumeric characters',
    identification_number VARCHAR(50),
    address TEXT,
    occupation VARCHAR(100),
    profile_picture VARCHAR(255),
    savings_amount DECIMAL(15, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    date_joined DATE NOT NULL,
    last_savings_date DATE,
    total_borrowed DECIMAL(15, 2) DEFAULT 0.00,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_nin (nin),
    INDEX idx_status (status),
    INDEX idx_date_joined (date_joined)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: savings (Savings contributions)
-- ============================================
CREATE TABLE savings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    savings_amount DECIMAL(15, 2) NOT NULL,
    savings_type ENUM('mandatory', 'voluntary') DEFAULT 'voluntary',
    payment_method ENUM('cash', 'bank_transfer', 'mobile_money') DEFAULT 'cash',
    receipt_number VARCHAR(50),
    notes TEXT,
    savings_date DATE NOT NULL,
    created_at DATETIME,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id),
    INDEX idx_savings_date (savings_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: loans (Loan applications)
-- ============================================
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    loan_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(5, 4) DEFAULT 0.02,
    total_payable DECIMAL(15, 2),
    status ENUM('pending', 'approved', 'active', 'cleared', 'completed', 'rejected') DEFAULT 'pending',
    purpose TEXT,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    approved_by INT,
    approval_date DATE,
    repayment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    amount_paid DECIMAL(15, 2) DEFAULT 0.00,
    remaining_balance DECIMAL(15, 2),
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_member_id (member_id),
    INDEX idx_status (status),
    INDEX idx_loan_date (loan_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: interest_distributions (Monthly interest)
-- ============================================
CREATE TABLE interest_distributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    interest_earned DECIMAL(15, 2) NOT NULL,
    distribution_month INT NOT NULL,
    distribution_year INT NOT NULL,
    savings_ratio DECIMAL(5, 4) NOT NULL,
    total_loan_amount DECIMAL(15, 2) NOT NULL,
    total_monthly_interest DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'distributed') DEFAULT 'pending',
    distribution_date DATE,
    created_at DATETIME,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id),
    UNIQUE KEY unique_distribution (member_id, distribution_month, distribution_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: loan_payments (Repayment records)
-- ============================================
CREATE TABLE loan_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    member_id INT NOT NULL,
    payment_amount DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_money') DEFAULT 'cash',
    receipt_number VARCHAR(50),
    notes TEXT,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    payment_date DATE NOT NULL,
    approved_by INT,
    approval_date DATETIME,
    created_at DATETIME,
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_member_id (member_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: transactions (Audit trail)
-- ============================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    transaction_type ENUM('savings', 'loan', 'loan_payment', 'interest_earned', 'interest_distributed', 'penalty', 'refund') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    transaction_month INT,
    transaction_year INT,
    transaction_date DATETIME,
    created_at DATETIME,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id),
    INDEX idx_transaction_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: settings (System configuration)
-- ============================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- TABLE: audit_log (Security log)
-- ============================================
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    action_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_date (action_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Admin user (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role, status, created_at) 
VALUES ('admin', 'admin@70k.local', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KeK', 'Administrator', 'admin', 'active', NOW());

-- Default settings
INSERT INTO settings (setting_key, setting_value, description, created_at) VALUES
('mandatory_savings', '5000', 'Minimum mandatory savings amount in UGX', NOW()),
('monthly_interest_rate', '0.02', 'Monthly loan interest rate (2%)', NOW()),
('app_name', '70K Savings & Loans', 'Application name', NOW()),
('app_currency', 'UGX', 'Application currency code', NOW());

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
