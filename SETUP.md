# 70K Savings & Loans System - Setup Guide

## Quick Start Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3+
- Apache/Nginx web server
- phpMyAdmin or MySQL command-line client

### Step-by-Step Installation

#### 1. Extract Project Files
```bash
# Extract the project zip file to your web root
# Windows: C:\xampp\htdocs\70k
# Linux: /var/www/html/70k
# macOS: /Library/WebServer/Documents/70k
```

#### 2. Create Database

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Click "New" to create a new database
3. Name it: `savings_loans_db`
4. Click "Create"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p
CREATE DATABASE savings_loans_db;
USE savings_loans_db;
```

#### 3. Import Database Schema

**Option A: Using phpMyAdmin**
1. Select the `savings_loans_db` database
2. Go to "Import" tab
3. Click "Choose File" and select `database.sql`
4. Click "Import"

**Option B: Using Command Line**
```bash
mysql -u root -p savings_loans_db < database.sql
```

#### 4. Update Database Configuration

Open `config/db_config.php` and update:

```php
define('DB_HOST', 'localhost');        // Your database host
define('DB_USER', 'root');             // Your database user
define('DB_PASS', '');                 // Your database password
define('DB_NAME', 'savings_loans_db'); // Database name
```

#### 5. Set Directory Permissions (Linux/Mac)

```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 config/db_config.php
```

#### 6. Access the Application

Open your browser and navigate to:
```
http://localhost/70k/
```

Or if using a domain:
```
http://yourdomain.com/70k/
```

---

## Login Credentials

### Admin Account
- **URL**: http://localhost/70k/login.php
- **Username**: `admin`
- **Password**: `admin123`

**IMPORTANT**: Change the admin password immediately after first login!

### Create New Member Account
1. Click "Register" on the login page
2. Fill in member details
3. Click "Register"
4. Login with the new account

---

## System Configuration

### Modify System Settings

Edit `config/db_config.php` to customize:

```php
// Minimum mandatory savings amount
define('MANDATORY_SAVINGS', 5000);

// Monthly loan interest rate (2% = 0.02)
define('LOAN_INTEREST_RATE', 0.02);

// Currency symbol
define('CURRENCY_SYMBOL', '₤');
```

Edit `database.sql` to change settings in the `settings` table:

```sql
UPDATE settings 
SET setting_value = '10000' 
WHERE setting_key = 'mandatory_savings';
```

---

## File Structure Explained

```
70k-savings-loans/
├── config/
│   ├── db_config.php           # Database connection & constants
│   └── functions.php           # Core reusable functions
│
├── css/
│   └── style.css               # Main stylesheet
│
├── js/
│   └── script.js               # Client-side JavaScript
│
├── uploads/                    # User-uploaded files
├── logs/                       # System log files
│
├── index.php                   # Member dashboard
├── login.php                   # User login
├── register.php                # New member registration
├── savings.php                 # Savings management
├── loans.php                   # Loan management
├── reports.php                 # Financial reports
├── profile.php                 # Member profile
├── admin.php                   # Admin dashboard
├── logout.php                  # Logout handler
│
├── database.sql                # Database schema
├── README.md                   # Project documentation
├── SETUP.md                    # This file
└── AGENTS.md                   # AI agent instructions
```

---

## Features Overview

### Member Features
- **Dashboard**: Overview of savings, loans, and interest
- **Savings**: Track and add savings contributions
- **Loans**: Apply for loans (max 2x savings, 2% monthly interest)
- **Reports**: View transaction history and interest distributions
- **Profile**: Update personal information

### Admin Features
- **Dashboard**: System statistics and overview
- **Loan Approvals**: Review and approve/reject loan applications
- **Member Management**: View all members and their accounts
- **Interest Distribution**: Process monthly interest calculations and distributions

### Automatic Features
- Monthly interest calculation (2% on outstanding loans)
- Automatic interest distribution based on savings ratio
- Transaction logging for audit trail
- Loan repayment tracking

---

## How the Interest System Works

### Example Scenario

**Group Status:**
- Member A: 100,000 UGX savings
- Member B: 50,000 UGX savings
- Total Group Savings: 150,000 UGX

**Outstanding Loans:**
- Loan 1: 200,000 UGX
- Loan 2: 100,000 UGX
- Total: 300,000 UGX

**Monthly Interest Calculation:**
```
Total Monthly Interest = 300,000 × 2% = 6,000 UGX

Distribution:
- Member A ratio: 100,000 / 150,000 = 66.67%
  Interest earned: 6,000 × 66.67% = 4,000 UGX

- Member B ratio: 50,000 / 150,000 = 33.33%
  Interest earned: 6,000 × 33.33% = 2,000 UGX
```

---

## Common Issues & Solutions

### Issue: "Connection failed: Access denied"
**Solution**: 
- Check database username and password in `config/db_config.php`
- Verify MySQL service is running
- Ensure user has correct database permissions

### Issue: "Page not found" or 404 errors
**Solution**:
- Check file path in URL
- Ensure web server is configured correctly
- Verify `.htaccess` if using Apache

### Issue: CSS or JavaScript not loading
**Solution**:
- Clear browser cache (Ctrl+Shift+Delete)
- Verify file paths are correct
- Check web server file permissions

### Issue: Cannot upload files
**Solution**:
```bash
chmod 755 uploads/
chmod 755 logs/
```

---

## Maintenance Tasks

### Daily Tasks
- Monitor system logs in `logs/` directory
- Review any error notifications

### Weekly Tasks
- Backup database regularly
- Check member account status
- Review pending loan applications

### Monthly Tasks
- Run interest distribution (1st of month)
- Generate financial reports
- Review transaction logs

### Backup Database

```bash
# Backup
mysqldump -u root -p savings_loans_db > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p savings_loans_db < backup_20260301.sql
```

---

## Security Recommendations

1. **Change Default Credentials**
   - Change admin password immediately
   - Create new admin accounts for additional staff

2. **Database Security**
   - Use strong database passwords
   - Limit database user permissions
   - Regular backups

3. **File Permissions**
   ```bash
   chmod 644 *.php
   chmod 755 uploads/ logs/
   chmod 600 config/db_config.php
   ```

4. **HTTPS Configuration**
   - Install SSL certificate
   - Redirect HTTP to HTTPS
   - Update URLs in configuration

5. **Regular Updates**
   - Keep PHP updated
   - Update database server
   - Monitor security advisories

---

## Support & Troubleshooting

### Enable Debug Mode

Open `config/db_config.php` and uncomment:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Check System Logs

```bash
tail -f logs/error.log
```

### Database Backup Location
Store backups in a secure location outside web root.

### Database Health Check

```sql
-- Check for corruption
REPAIR TABLE members;
REPAIR TABLE loans;
REPAIR TABLE savings;

-- Verify data integrity
SELECT COUNT(*) FROM members;
SELECT SUM(savings_amount) FROM members;
SELECT SUM(loan_amount) FROM loans WHERE status='active';
```

---

## API Endpoints Reference

### AJAX Endpoints (for JavaScript)

```javascript
// Format currency
formatCurrency(1000) // Returns: "₤ 1,000.00"

// Show notifications
showSuccess("Operation completed successfully!")
showError("An error occurred")
showWarning("Please review this")
showInfo("Information message")

// Filter and export tables
filterTable('searchInput', 'dataTable')
exportTableToCSV('dataTable', 'filename.csv')
printTable('dataTable')
```

---

## Default Database Users

### Admin User
- Username: `admin`
- Email: `admin@70k.local`
- Password: `admin123` (hashed with bcrypt)
- Role: `admin`

---

## Performance Optimization Tips

1. **Database Optimization**
   ```sql
   OPTIMIZE TABLE members;
   OPTIMIZE TABLE loans;
   OPTIMIZE TABLE transactions;
   ```

2. **Caching**
   - Enable PHP opcode caching
   - Use browser caching
   - Implement database query caching

3. **Indexing**
   - Database indexes are already created in schema
   - Monitor slow queries

4. **Asset Compression**
   - Enable gzip compression
   - Minify CSS and JavaScript

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Mar 2026 | Initial release |

---

## Contact & Support

For technical support or inquiries:
- Email: admin@70k.local
- Documentation: See README.md
- Database Schema: See database.sql

---

**Last Updated**: March 1, 2026
**Status**: Production Ready
