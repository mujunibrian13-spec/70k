-- ============================================
-- 70K Savings & Loans Management System
-- Database Schema SQL File
-- Created: March 2026
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS savings_loans_db;
USE savings_loans_db;

-- ============================================
-- TABLE: Users/Admin
-- Stores system administrator accounts
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
    last_logout DATETIME,
    created_at DATETIME,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_last_login (last_login),
    INDEX idx_last_logout (last_logout)
);

-- ============================================
-- TABLE: Members
-- Stores group member information
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
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_nin (nin),
    INDEX idx_status (status),
    INDEX idx_date_joined (date_joined)
);

-- ============================================
-- TABLE: Savings
-- Records individual member savings contributions
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
    INDEX idx_savings_date (savings_date),
    INDEX idx_savings_type (savings_type)
);

-- ============================================
-- TABLE: Loans
-- Records member loan applications and details
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
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_member_id (member_id),
    INDEX idx_status (status),
    INDEX idx_loan_date (loan_date),
    INDEX idx_due_date (due_date)
);

-- ============================================
-- TABLE: Interest Distributions
-- Records monthly interest calculations and distributions
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
    INDEX idx_distribution_month (distribution_month),
    INDEX idx_distribution_year (distribution_year),
    UNIQUE KEY unique_distribution (member_id, distribution_month, distribution_year)
);

-- ============================================
-- TABLE: Loan Payments
-- Records loan repayment transactions
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
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
);

-- ============================================
-- TABLE: Transactions
-- General transaction log for audit trail
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
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_month_year (transaction_month, transaction_year)
);

-- ============================================
-- TABLE: Settings
-- System configuration and settings
-- ============================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at DATETIME,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- ============================================
-- TABLE: Audit Log
-- Tracks all system actions for security
-- ============================================
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    action_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_date (action_date),
    INDEX idx_table_name (table_name)
);

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert admin user (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@70k.local', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KeK', 'Administrator', 'admin', 'active');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('mandatory_savings', '5000', 'Minimum mandatory savings amount in UGX'),
('monthly_interest_rate', '0.02', 'Monthly loan interest rate (2%)'),
('app_name', '70K Savings & Loans', 'Application name'),
('app_currency', 'UGX', 'Application currency code'),
('interest_distribution_day', '1', 'Day of month to distribute interest');

-- ============================================
-- CREATE VIEWS FOR REPORTING
-- ============================================

-- View: Member Summary
CREATE VIEW member_summary AS
SELECT 
    m.id,
    m.full_name,
    m.email,
    m.phone,
    m.savings_amount,
    m.total_borrowed,
    m.status,
    m.date_joined,
    COUNT(DISTINCT l.id) AS total_loans,
    COUNT(DISTINCT s.id) AS total_savings,
    MAX(s.savings_date) AS last_savings_date
FROM members m
LEFT JOIN loans l ON m.id = l.member_id
LEFT JOIN savings s ON m.id = s.member_id
GROUP BY m.id, m.full_name, m.email, m.phone, m.savings_amount, m.total_borrowed, m.status, m.date_joined;

-- View: Loan Summary
CREATE VIEW loan_summary AS
SELECT 
    l.id,
    l.member_id,
    m.full_name,
    l.loan_amount,
    l.interest_rate,
    l.status,
    l.loan_date,
    l.due_date,
    l.remaining_balance,
    (l.loan_amount * (1 + (l.interest_rate * 12))) AS estimated_total_with_annual_interest
FROM loans l
JOIN members m ON l.member_id = m.id;

-- View: Monthly Interest Report
CREATE VIEW monthly_interest_report AS
SELECT 
    id.distribution_month,
    id.distribution_year,
    COUNT(DISTINCT id.member_id) AS total_members,
    SUM(id.total_monthly_interest) AS total_interest_pool,
    SUM(id.interest_earned) AS total_interest_distributed,
    SUM(id.total_loan_amount) AS total_loans_outstanding
FROM interest_distributions id
GROUP BY id.distribution_month, id.distribution_year;

-- ============================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================

CREATE INDEX idx_member_savings_date ON savings(member_id, savings_date);
CREATE INDEX idx_loan_member_date ON loans(member_id, loan_date);
CREATE INDEX idx_transaction_member_type ON transactions(member_id, transaction_type);
CREATE INDEX idx_payment_loan_date ON loan_payments(loan_id, payment_date);
CREATE INDEX idx_audit_date_action ON audit_log(action_date, action);

-- ============================================
-- TABLE: Login/Logout History
-- ============================================
CREATE TABLE IF NOT EXISTS login_logout_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(20) NOT NULL COMMENT 'login or logout',
    action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_time (action_time),
    INDEX idx_action (action)
);

-- ============================================
-- TABLE: Deleted Members (Backup/Undo)
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
-- TABLE: Deletion Log (Audit Trail)
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
-- INSERT SAMPLE MEMBER DATA (OPTIONAL)
-- ============================================

-- Insert sample members (optional - comment out if not needed)
-- INSERT INTO members (user_id, full_name, email, phone, nin, savings_amount, status, date_joined) VALUES
-- (NULL, 'Emiru Jackson', 'emiru@example.com', '+256701234567', '12345678901234', 150000.00, 'active', '2025-01-15'),
-- (NULL, 'Sarah Kamukama', 'sarah@example.com', '+256702345678', '23456789012345', 120000.00, 'active', '2025-01-20'),
-- (NULL, 'Ibrahim Hassan', 'ibrahim@example.com', '+256703456789', '34567890123456', 200000.00, 'active', '2025-02-01'),
-- (NULL, 'Grace Nalwoga', 'grace@example.com', '+256704567890', '45678901234567', 85000.00, 'active', '2025-02-10');

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
