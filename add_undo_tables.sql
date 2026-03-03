-- ============================================
-- Add Undo/Restore Functionality Tables
-- For Member Deletion Recovery
-- ============================================

-- Create table to backup deleted members
CREATE TABLE IF NOT EXISTS deleted_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL COMMENT 'Original member ID',
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    national_id VARCHAR(50),
    address TEXT,
    savings_amount DECIMAL(15, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    date_joined DATE,
    deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_by INT COMMENT 'Admin user ID who deleted',
    member_data JSON COMMENT 'Full member data in JSON format',
    can_restore TINYINT(1) DEFAULT 1,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table to track deletion history
CREATE TABLE IF NOT EXISTS deletion_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    member_email VARCHAR(100),
    member_name VARCHAR(100),
    deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_by INT,
    reason TEXT,
    restored TINYINT(1) DEFAULT 0,
    restored_at DATETIME,
    restored_by INT,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
