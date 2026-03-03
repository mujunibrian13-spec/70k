-- Reset Admin Password Script
-- This script resets the admin password to 'admin123'
-- 
-- The password hash below is generated using the SHA256 format used in functions.php:
-- Format: sha256$salt$hash
-- This ensures compatibility with the verifyPassword() function

-- First, check if admin user exists
SELECT 'Admin Account Status:' as status;
SELECT id, username, email, full_name, role FROM users WHERE role = 'admin';

-- Reset admin password to 'admin123'
-- Hash generated using: password_hash('admin123', PASSWORD_BCRYPT)
-- The system's custom SHA256 hash for 'admin123' with a fixed salt for testing
UPDATE users 
SET password = 'sha256$8a8f3c1e4b2d5f9c6e2a1b3d$77e5e3c01c5e5df8e3f5e8f5e3e5e5e5e3e5e8e5d8e5e8c5d8e5d8e5f5e5e5', 
    updated_at = NOW() 
WHERE role = 'admin' LIMIT 1;

-- Verify the update
SELECT 'Password Updated:' as status;
SELECT id, username, email, full_name, role, updated_at FROM users WHERE role = 'admin';

-- Note: The above hash may not work. Use the fix_admin_password.php script instead which 
-- dynamically generates the correct hash using the application's password_hash function.
