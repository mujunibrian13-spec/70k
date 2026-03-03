# 70K Savings and Loans Management System

## Overview
The 70K Savings and Loans Management System is a web-based application designed for the 70k group to manage member savings, loans, and automated interest calculations. Members contribute mandatory savings of 5,000 Ugandan Shillings (or more voluntarily) and can borrow money with a 2% monthly interest rate. Interest earned from loans is distributed among members proportionally based on their savings contributions.

## Features
- **Member Registration**: Register and manage group members
- **Savings Management**: Track individual and collective savings
- **Loan Management**: Process loans with automatic 2% monthly interest
- **Automated Interest Distribution**: Interest automatically divided among members based on savings ratio
- **Monthly Calculations**: Automatic end-of-month interest calculations and distributions
- **Reports & Analytics**: View savings, loans, and interest distribution reports
- **Admin Dashboard**: Monitor system-wide activity and perform administrative tasks
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

## Who It's For
The 70K Savings and Loans Management System is designed for:
- Members of the 70k group seeking to manage collective savings
- Group administrators overseeing financial operations
- Members wanting transparent tracking of savings and loan benefits

## Technology Stack
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript (ES6)
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Server**: Apache/Nginx
- **Version Control**: Git

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Modern web browser

### Step 1: Download/Clone Project
```bash
git clone <repository-url>
cd 70k-savings-loans
```

### Step 2: Create Database
```sql
CREATE DATABASE savings_loans_db;
USE savings_loans_db;
```

### Step 3: Import Database Schema
Execute `database.sql` file to create tables:
```bash
mysql -u root -p savings_loans_db < database.sql
```

### Step 4: Configure Database Connection
Edit `config/db_config.php`:
```php
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'savings_loans_db';
```

### Step 5: Set File Permissions
```bash
chmod 755 uploads/
chmod 755 logs/
```

### Step 6: Access the Application
Open your browser and navigate to:
```
http://localhost/70k-savings-loans/
```

## Project Structure
```
70k-savings-loans/
├── index.php                 # Dashboard
├── register.php              # Member registration
├── savings.php               # Savings management
├── loans.php                 # Loan management
├── reports.php               # Reports & analytics
├── admin.php                 # Admin panel
├── config/
│   ├── db_config.php         # Database configuration
│   └── functions.php         # Reusable PHP functions
├── css/
│   └── style.css             # Main stylesheet
├── js/
│   └── script.js             # Client-side JavaScript
├── database.sql              # Database schema
├── uploads/                  # Profile pictures & documents
├── logs/                     # System logs
└── README.md                 # This file
```

## How It Works

### Savings System
1. Members register in the system
2. Minimum mandatory savings: 5,000 UGX
3. Members can contribute additional savings at any time
4. All savings are recorded with timestamps

### Loan System
1. Registered members can apply for loans
2. Loans are approved by admins
3. Interest rate: 2% per month (calculated automatically)
4. Loan repayment tracked in the system

### Interest Distribution
- Monthly interest from all loans is pooled
- Interest distributed proportionally based on savings ratio
  - Example: If Member A has 100,000 UGX saved and Member B has 50,000 UGX saved:
    - Total savings: 150,000 UGX
    - Member A gets: 66.67% of interest pool
    - Member B gets: 33.33% of interest pool
- Distribution calculated automatically on the 1st of each month

### Monthly Process (Automated)
- Interest accrued on all active loans
- Interest pool calculated
- Distribution ratios computed based on current savings
- Interest distributed to member accounts
- All transactions logged

## Default Login Credentials
- **Admin User**: 
  - Username: `admin`
  - Password: `admin123` (Change immediately after first login!)

## Key Functions

### Calculate Interest
```php
calculateMonthlyInterest($loan_amount, $interest_rate = 0.02)
// Returns: $loan_amount * $interest_rate
```

### Distribute Interest
```php
distributeInterestToMembers($total_interest, $month, $year)
// Automatically divides interest based on savings ratio
```

### Get Member Savings Ratio
```php
getMemberSavingsRatio($member_id)
// Returns: Member's savings / Total group savings
```

## Security Features
- Input validation on all forms
- SQL injection prevention using prepared statements
- Session management for user authentication
- Password hashing using bcrypt
- CSRF token protection on forms

## Support & Documentation
For issues or feature requests, contact the development team or check the inline code comments in each file.

## License
This system is for the exclusive use of the 70k group members.

---
**Last Updated**: March 1, 2026
