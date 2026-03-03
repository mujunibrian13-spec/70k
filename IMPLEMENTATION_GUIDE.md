# 70K Savings & Loans System - Implementation Guide

## Complete System Overview

This comprehensive guide covers the entire 70K Savings & Loans management system, explaining how it works, how to set it up, and how to use all features.

---

## What This System Does

The 70K Savings & Loans System is a complete financial management platform for group savings and lending. Here's what makes it special:

### Core Functionality

1. **Savings Management**
   - Members contribute to a group savings pool
   - Minimum mandatory saving: 5,000 Ugandan Shillings
   - Members can contribute additional voluntary savings anytime
   - All contributions are tracked and recorded

2. **Loan Borrowing**
   - Only registered members with minimum savings can borrow
   - Loans have a 2% monthly interest rate
   - Loan amount limited to 2x member's total savings
   - Admin approval required for each loan

3. **Automatic Interest Distribution**
   - Monthly interest calculated on all outstanding loans
   - Interest automatically divided among members based on their savings percentage
   - Higher savers get larger interest shares
   - Fully automated monthly calculation and distribution

4. **Complete Reporting**
   - Members can view all their transactions
   - Generate reports and export to CSV
   - Track savings progress and loan history
   - Print financial statements

---

## System Architecture

### Frontend (User Interface)

```
Web Browser
    ↓
├── index.php (Dashboard)
├── savings.php (Add/view savings)
├── loans.php (Apply for loans)
├── reports.php (View reports)
├── profile.php (Account settings)
├── admin.php (Admin controls)
└── Styling: Bootstrap 5 + Custom CSS
```

### Backend (Server)

```
PHP Application
    ↓
├── config/db_config.php (Database setup)
├── config/functions.php (Core functions)
└── Various .php pages (Business logic)
```

### Database (Data Storage)

```
MySQL Database
    ↓
├── Members table (User accounts)
├── Savings table (Savings records)
├── Loans table (Loan applications)
├── Interest_distributions table (Monthly distributions)
├── Transactions table (Audit trail)
└── Other supporting tables
```

---

## Installation & Setup

### System Requirements

Before installing, make sure you have:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Modern web browser

### Installation Steps

#### Step 1: Download & Extract
```
Extract the project files to your web server's root directory:
- Windows (XAMPP): C:\xampp\htdocs\70k
- Linux: /var/www/html/70k
- macOS: /Library/WebServer/Documents/70k
```

#### Step 2: Create Database
```sql
-- Open phpMyAdmin or MySQL console and run:
CREATE DATABASE savings_loans_db;
USE savings_loans_db;
```

#### Step 3: Import Schema
```
- In phpMyAdmin: Import → Select database.sql → Import
- OR in command line: mysql -u root -p savings_loans_db < database.sql
```

#### Step 4: Configure Database Connection
Edit `config/db_config.php`:
```php
define('DB_HOST', 'localhost');    // Your host
define('DB_USER', 'root');         // Your user
define('DB_PASS', '');             // Your password
define('DB_NAME', 'savings_loans_db');
```

#### Step 5: Set Permissions (Linux/Mac)
```bash
chmod 755 uploads/
chmod 755 logs/
```

#### Step 6: Access the System
Open browser and go to: `http://localhost/70k/`

---

## User Guide

### For Members

#### Login
1. Go to http://localhost/70k/login.php
2. Enter username and password
3. Click "Login"

#### Create New Member Account
1. Click "Register" on login page
2. Enter: Full name, email, phone number
3. Choose password (min 6 characters)
4. Click "Register"
5. Log in with new credentials

#### Dashboard
```
Shows at a glance:
- Your total savings
- How much you've borrowed
- Your share of group interest
- Recent transactions
- Quick action buttons
```

#### Add Savings
1. Click "Savings" in navigation
2. Enter amount to save
3. Choose payment method (Cash, Bank Transfer, Mobile Money)
4. Optional: Enter receipt number and notes
5. Click "Record Savings"
6. Savings instantly added to your account

#### Apply for Loan
1. Click "Loans" in navigation
2. Enter loan amount (max 2x your savings)
3. Choose loan duration (1-24 months)
4. Explain purpose of loan
5. Click "Submit Application"
6. Admin will review and approve/reject

#### View Reports
1. Click "Reports" in navigation
2. View transactions or interest distributions
3. Download as CSV or print
4. Track your financial history

#### Update Profile
1. Click your name dropdown → "My Profile"
2. Update personal information
3. Click "Save Changes"

---

### For Admin

#### Login as Admin
- Username: `admin`
- Password: `admin123` (CHANGE THIS!)

#### Admin Dashboard
Shows:
- Total members
- Group savings total
- Outstanding loans
- Pending applications

#### Approve/Reject Loans
1. Go to Admin Dashboard
2. Click "Pending Loan Applications" tab
3. Review member and loan details
4. Click "Approve" or "Reject"
5. System updates automatically

#### Manage Members
1. Go to "All Members" tab
2. View member list with savings amounts
3. Check member status and join dates
4. Monitor savings contributions

#### Distribute Monthly Interest
1. Go to "Interest Distribution" tab
2. Select month and year
3. Click "Distribute Interest"
4. System calculates and distributes automatically
5. Each member gets their share based on savings ratio

---

## How Interest Distribution Works

### The Process (Simplified)

```
Step 1: Calculate Total Interest
├─ Look at all active loans
├─ Multiply each by 2% (monthly rate)
└─ Add up all interest → This is the interest pool

Step 2: Calculate Each Member's Share
├─ For each member:
│  ├─ Calculate their savings ratio
│  │  (their savings ÷ total group savings)
│  └─ Multiply by total interest pool
└─ Result: Their monthly interest share

Step 3: Add Interest to Savings
├─ Update each member's savings account
├─ Add their interest share
└─ Log the transaction
```

### Real Example

**Scenario:**
- Member A saved: 100,000
- Member B saved: 50,000
- Group total: 150,000

- Loan 1: 200,000 (generates 4,000 interest)
- Loan 2: 100,000 (generates 2,000 interest)
- Total interest: 6,000

**Distribution:**
- Member A: (100,000 ÷ 150,000) × 6,000 = 4,000
- Member B: (50,000 ÷ 150,000) × 6,000 = 2,000

**Result:**
- Member A's savings increases to 104,000
- Member B's savings increases to 52,000

---

## Key Features Explained

### Mandatory Savings

Every member MUST save at least 5,000 Ugandan Shillings to:
- Be eligible for loans
- Participate in interest distributions
- Maintain membership

Members can add MORE than 5,000, and higher savings = higher interest earnings.

### Interest Rate

- **Rate**: 2% per month on borrowed amount
- **Who pays**: Members who borrow
- **Who receives**: All members (divided by savings ratio)
- **Automatic**: Calculated every month

### Borrowing Limits

- **Maximum**: 2x your total savings
- **Example**: If you saved 100,000, you can borrow up to 200,000
- **Purpose**: Ensures loans can be repaid and protects the group

### Savings Ratio

Your percentage share of group savings:
```
Your Ratio = Your Savings ÷ Total Group Savings
Example: 100,000 ÷ 150,000 = 66.67%
```

This ratio determines your interest share percentage.

---

## Page-by-Page Walkthrough

### index.php (Member Dashboard)
**What you see:**
- 4 statistics cards (savings, loans, ratio, interest)
- Recent 10 transactions
- Account information
- Quick action buttons

**What you can do:**
- View financial overview
- See recent activity
- Access savings/loans
- Update profile

### login.php (Authentication)
**What you see:**
- Login form
- Register link
- Demo credentials (for testing)

**What you can do:**
- Enter username/password
- Click register for new account
- Access the system

### register.php (New Member)
**What you see:**
- Registration form
- Required fields
- Password confirmation

**What you can do:**
- Create new member account
- Set up profile
- Start saving/borrowing

### savings.php (Savings Management)
**What you see:**
- Add savings form
- Current savings balance
- Progress bar
- Savings history table

**What you can do:**
- Add new savings
- View past contributions
- Track progress
- See payment methods

### loans.php (Loan Management)
**What you see:**
- Loan application form
- Borrowing statistics
- Loan history
- Eligibility status

**What you can do:**
- Apply for loan
- View loan history
- Check borrowing limits
- Track loan status

### reports.php (Financial Reports)
**What you see:**
- Summary statistics
- Transaction history
- Interest distribution history
- Export/print options

**What you can do:**
- View all transactions
- See interest distributions
- Export to CSV
- Print reports

### profile.php (Account Settings)
**What you see:**
- Personal information form
- Account details
- Financial summary

**What you can do:**
- Update name/email/phone
- Change address/occupation
- View account info
- Monitor finances

### admin.php (Admin Dashboard)
**What you see:**
- System statistics
- Pending loans
- All members
- Interest distribution controls

**What you can do:**
- Approve/reject loans
- View member list
- Distribute monthly interest
- Monitor system

---

## Common Workflows

### Workflow 1: New Member Registration & First Contribution

```
1. New member registers at register.php
   ↓
2. Member logs in
   ↓
3. Goes to savings.php
   ↓
4. Contributes 5,000+ (mandatory minimum)
   ↓
5. Now eligible to borrow loans
   ↓
6. Can apply for loans at loans.php
```

### Workflow 2: Member Borrows Money

```
1. Member has 5,000+ saved
   ↓
2. Goes to loans.php
   ↓
3. Applies for loan (max 2x savings)
   ↓
4. Admin reviews at admin.php
   ↓
5. Admin clicks "Approve"
   ↓
6. Loan status changes to "approved" then "active"
   ↓
7. Loan starts accruing 2% monthly interest
```

### Workflow 3: Monthly Interest Distribution

```
1. Month ends (any day)
   ↓
2. Admin goes to admin.php
   ↓
3. Clicks "Interest Distribution" tab
   ↓
4. Selects month and year
   ↓
5. Clicks "Distribute Interest"
   ↓
6. System automatically:
   • Calculates total interest from all loans
   • Calculates each member's ratio
   • Adds interest to each member's savings
   • Logs all transactions
   ↓
7. All members now have more savings!
```

---

## Database Schema Overview

### Key Tables

**members** - Stores member information
- Full name, email, phone
- Total savings amount
- Account status
- Date joined

**savings** - Records savings contributions
- Member ID
- Amount saved
- Payment method
- Receipt number
- Date

**loans** - Loan applications
- Member ID
- Loan amount
- Interest rate (2%)
- Status (pending, approved, active, etc.)
- Dates

**interest_distributions** - Monthly interest
- Member ID
- Interest earned
- Month/year
- Distribution status
- Savings ratio used

**transactions** - Complete audit trail
- What happened
- When it happened
- Who it affected
- How much

---

## Troubleshooting

### Problem: "Can't connect to database"
**Solution:**
- Check database username/password in db_config.php
- Ensure MySQL is running
- Verify database exists

### Problem: "Loan application not showing"
**Solution:**
- Admin not logged in? Use admin credentials
- Loan still pending? It's waiting for approval
- Check admin.php pending loans tab

### Problem: "Can't add savings"
**Solution:**
- Amount must be greater than 0
- Amount must be a number
- Check form for errors

### Problem: "CSS/JavaScript not loading"
**Solution:**
- Clear browser cache (Ctrl+Shift+Delete)
- Verify file paths are correct
- Check web server permissions

---

## Best Practices

### For Members

1. **Keep Savings High**
   - Higher savings = Higher interest earnings
   - Aim to save more than minimum

2. **Repay Loans On Time**
   - Helps other members get their interest
   - Maintains group trust

3. **Check Account Regularly**
   - Monitor savings
   - Review transactions
   - Update profile

4. **Document Everything**
   - Keep receipt numbers
   - Note payment methods
   - Track loan progress

### For Admins

1. **Review Loans Promptly**
   - Don't leave applications pending
   - Check member eligibility
   - Approve or reject quickly

2. **Distribute Interest Monthly**
   - Run on same day each month (e.g., 1st)
   - Verify calculations
   - Log all actions

3. **Monitor System Health**
   - Check error logs
   - Backup database regularly
   - Update admin password

4. **Maintain Security**
   - Don't share admin credentials
   - Change default passwords
   - Keep system updated

---

## Security Checklist

Before going live:

- [ ] Change admin password from default
- [ ] Configure database user permissions
- [ ] Set up backups
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall
- [ ] Test all features
- [ ] Create member accounts
- [ ] Test loan workflow
- [ ] Test interest distribution
- [ ] Review error logs

---

## Performance Tips

1. **Database**
   - Run optimization monthly: `OPTIMIZE TABLE members;`
   - Create regular backups
   - Monitor slow queries

2. **Caching**
   - Enable browser caching
   - Use CDN for assets
   - Implement query caching

3. **Code**
   - Minimize CSS/JavaScript
   - Compress images
   - Use prepared statements

4. **Server**
   - Enable gzip compression
   - Configure timeouts appropriately
   - Monitor resource usage

---

## Support & Help

### Where to Find Help

1. **This Guide**: Read PROJECT_STRUCTURE.md for technical details
2. **Setup**: Follow SETUP.md for installation
3. **Code**: All PHP/JS/CSS files have detailed comments
4. **Database**: See database.sql for schema details

### Common Questions

**Q: How do I reset a member's password?**
A: Update the users table directly in MySQL with a new hashed password

**Q: Can I change the 5,000 savings requirement?**
A: Yes, edit MANDATORY_SAVINGS in config/db_config.php

**Q: How do I change the 2% interest rate?**
A: Edit LOAN_INTEREST_RATE in config/db_config.php

**Q: Can I export member list?**
A: Yes, go to admin.php → All Members → Use browser export

**Q: How do I backup the database?**
A: Use phpMyAdmin or command: `mysqldump -u root -p savings_loans_db > backup.sql`

---

## System Statistics

Current system has:
- **8 main PHP pages**
- **8 database tables**
- **1,000+ lines of PHP code**
- **1,000+ lines of CSS code**
- **1,500+ lines of JavaScript code**
- **200+ lines of SQL schema**

Total: **4,000+ lines of production code**

---

**Version**: 1.0.0  
**Status**: Production Ready  
**Last Updated**: March 1, 2026

---

## Getting Started

1. Follow SETUP.md to install
2. Read this guide for understanding
3. Login with admin/admin123
4. Create test members
5. Add test savings
6. Apply for test loans
7. Approve loans in admin
8. Distribute interest
9. View reports

**You're ready to use the system!**
