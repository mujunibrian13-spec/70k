# Undo/Restore Member Deletion Feature

## Overview
This feature allows admins to restore deleted members within 24 hours of deletion.

## Database Tables Added

### 1. deleted_members
Stores backup data of deleted members:
- `member_id` - Original member ID
- `full_name`, `email`, `phone`, `national_id`, `address`
- `savings_amount`, `status`, `date_joined`
- `deleted_at` - Timestamp of deletion
- `deleted_by` - Admin user ID who deleted
- `member_data` - Full JSON backup
- `can_restore` - Flag to enable/disable restoration

### 2. deletion_log
Tracks deletion history:
- `member_id` - Member ID
- `member_email`, `member_name`
- `deleted_at` - Deletion timestamp
- `deleted_by` - Admin who deleted
- `reason` - Deletion reason
- `restored` - Flag if restored
- `restored_at` - Restoration timestamp
- `restored_by` - Admin who restored

## Setup Instructions

### Step 1: Create Database Tables
Run the following SQL in your MySQL client:

```sql
CREATE TABLE IF NOT EXISTS deleted_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    national_id VARCHAR(50),
    address TEXT,
    savings_amount DECIMAL(15, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    date_joined DATE,
    deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_by INT,
    member_data JSON,
    can_restore TINYINT(1) DEFAULT 1,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
```

### Step 2: Check File Updates
The following files have been updated:
- `edit_member.php` - Added backup functionality during deletion
- `admin.php` - Added undo panel and restore functionality
- `config/functions.php` - Added `getDeletedMembers()` and `restoreDeletedMember()` functions

## Features

### 1. Automatic Backup on Deletion
When a member is deleted:
- All member data is backed up in `deleted_members` table
- Deletion is logged in `deletion_log` table
- Member's savings, loans, transactions, and interest data are removed

### 2. Undo Panel on Dashboard
The admin dashboard shows:
- Recently deleted members (up to 10)
- Member name, email, and deletion date
- One-click "Restore" button
- 24-hour countdown for undo availability

### 3. Restore Function
When restoring:
- Member record is recreated with original data
- Original member ID is preserved
- Deletion log is updated to mark as restored
- Backup is marked as no longer restorable

## How to Use

### Deleting a Member:
1. Go to Admin Dashboard → All Members
2. Click "Delete" button next to the member
3. Confirm deletion by typing member's email
4. Click "Delete Permanently"
5. Success message shows "You can undo this action for the next 24 hours"

### Restoring a Member:
1. Go to Admin Dashboard
2. Look for "Recently Deleted Members" section
3. Find the member you want to restore
4. Click "Restore" button
5. Member is instantly restored with all original data
6. Success message confirms restoration

## Time Limit
- Undo is available for **24 hours** after deletion
- After 24 hours, deleted members cannot be restored
- Multiple members can be restored within the same 24-hour window

## Security Features
- Email confirmation required for deletion
- Admin user ID logged for all deletions and restorations
- Complete audit trail of who deleted and who restored
- Prevents double-deletion of same member
- Transaction-based restoration for data integrity

## Audit Trail
All deletions and restorations are tracked:
- View in `deletion_log` table
- Shows admin who performed action
- Timestamps for all operations
- Can be used for compliance and auditing

## Notes
- Deleted members' savings, loans, and transactions are permanently removed
- Only member profile data is restored
- If a member was re-registered under same email, restore will fail
- Each deletion backup is unique and can only be restored once
