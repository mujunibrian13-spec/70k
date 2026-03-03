# 70K Savings & Loans System - Project Structure & Features

## Project Overview

A complete web-based savings and loans management system built with HTML, CSS, PHP, JavaScript, and Bootstrap 5. Designed for the 70K group to manage collective savings, loans, and automated interest distributions.

---

## Directory Structure

```
70k-savings-loans/
│
├── config/
│   ├── db_config.php                # Database configuration & constants
│   │   • DB connection settings
│   │   • Application constants (savings amount, interest rate)
│   │   • Session initialization
│   │
│   └── functions.php                # Core reusable functions
│       • Database queries
│       • Calculations (interest, ratios)
│       • Utility functions
│       • Validation functions
│       • Logging functions
│
├── css/
│   └── style.css                    # Main stylesheet
│       • Bootstrap 5 customization
│       • Responsive design (mobile, tablet, desktop)
│       • Component styling
│       • Color variables & themes
│       • Media queries
│
├── js/
│   └── script.js                    # Client-side JavaScript
│       • Form validation
│       • AJAX requests
│       • UI interactions
│       • Table utilities (sort, filter, export)
│       • Currency formatting
│       • Notification system
│
├── uploads/                         # User uploads directory
│   └── (empty - created at runtime)
│
├── logs/                            # System logs directory
│   └── (empty - created at runtime)
│
├── Core Pages
│   ├── index.php                    # Member Dashboard
│   │   • Account overview
│   │   • Financial statistics
│   │   • Recent transactions
│   │   • Quick actions
│   │
│   ├── login.php                    # Authentication
│   │   • User login form
│   │   • Session management
│   │   • Error handling
│   │
│   ├── register.php                 # Member Registration
│   │   • New member signup
│   │   • Account creation
│   │   • Form validation
│   │
│   ├── savings.php                  # Savings Management
│   │   • Add savings contributions
│   │   • View savings history
│   │   • Progress tracking
│   │   • Statistics
│   │
│   ├── loans.php                    # Loan Management
│   │   • Apply for loans
│   │   • View loan history
│   │   • Borrowing limits
│   │   • Loan terms
│   │
│   ├── reports.php                  # Financial Reports
│   │   • Transaction history
│   │   • Interest distribution details
│   │   • Export to CSV
│   │   • Print reports
│   │
│   ├── profile.php                  # Member Profile
│   │   • Update personal info
│   │   • View account details
│   │   • Account management
│   │
│   ├── admin.php                    # Admin Dashboard
│   │   • System statistics
│   │   • Loan approvals
│   │   • Member management
│   │   • Interest distribution
│   │
│   └── logout.php                   # Session termination
│       • Logout handler
│       • Redirect to login
│
├── Database & Config
│   ├── database.sql                 # Database schema
│   │   • Table definitions
│   │   • Indexes
│   │   • Views for reporting
│   │   • Default data
│   │
│   └── .htaccess (optional)         # Apache configuration
│
└── Documentation
    ├── README.md                    # Project documentation
    ├── SETUP.md                     # Installation guide
    ├── PROJECT_STRUCTURE.md         # This file
    └── AGENTS.md                    # Development notes
```

---

## Database Schema

### Tables

#### `users` - User Accounts
- Stores login credentials
- Separates admin and member roles
- Tracks login history

#### `members` - Member Profiles
- Member personal information
- Savings account details
- Borrowing records
- Account status

#### `savings` - Savings Contributions
- Individual savings transactions
- Contribution tracking
- Payment method records
- Receipt management

#### `loans` - Loan Management
- Loan applications
- Approval workflow
- Interest calculations
- Repayment tracking

#### `interest_distributions` - Monthly Interest
- Calculated interest amounts
- Distribution history
- Savings ratio records
- Distribution status

#### `loan_payments` - Repayment Records
- Payment transactions
- Payment method tracking
- Receipt numbers
- Payment dates

#### `transactions` - Audit Trail
- All financial transactions
- Type categorization
- Complete history
- Month/year tracking

#### `settings` - System Configuration
- Configurable parameters
- System settings
- Feature flags

#### `audit_log` - Security Log
- User actions
- Data changes
- IP addresses
- Timestamps

### Views

#### `member_summary` - Member Overview
Shows aggregated member data with savings and loan counts

#### `loan_summary` - Loan Details
Provides loan information with estimated total costs

#### `monthly_interest_report` - Interest Analytics
Monthly aggregated interest distribution data

---

## Key Features

### Member Features

#### 1. Dashboard (index.php)
- **Statistics Cards**
  - Total savings balance
  - Total borrowed amount
  - Savings ratio percentage
  - Monthly interest earned

- **Recent Transactions**
  - Transaction history (last 10)
  - Transaction types (savings, loans, interest)
  - Amounts and dates

- **Quick Actions**
  - Add savings button
  - Apply for loan button
  - View account information

#### 2. Savings Management (savings.php)
- **Add Savings Form**
  - Amount input with validation
  - Payment method selection
  - Receipt number tracking
  - Optional notes

- **Savings Statistics**
  - Current total savings
  - Mandatory savings requirement
  - Progress bar visualization
  - Requirement status

- **Savings History**
  - All past contributions
  - Payment methods
  - Receipt references
  - Sorted by date

#### 3. Loan Management (loans.php)
- **Loan Application**
  - Amount input (max 2x savings)
  - Duration selection (1-24 months)
  - Purpose statement
  - Eligibility check

- **Loan Statistics**
  - Total borrowed
  - Active loan count
  - Interest rate display
  - Borrowing limits

- **Loan History**
  - Application status
  - Due dates
  - Loan amounts
  - Repayment status

#### 4. Reports (reports.php)
- **Summary Statistics**
  - Total saved
  - Total borrowed
  - Interest earned
  - Net position

- **Transaction Report**
  - Complete transaction log
  - Running balance calculation
  - Export to CSV
  - Print functionality

- **Interest Distribution Report**
  - Monthly distributions
  - Savings ratio
  - Interest share calculation
  - Distribution status

#### 5. Profile Management (profile.php)
- **Personal Information**
  - Full name, email, phone
  - Address, occupation
  - Account creation date
  - Membership status

- **Account Details**
  - Member ID
  - Join date
  - Account status
  - Financial summary

### Admin Features

#### 1. Admin Dashboard (admin.php)
- **System Statistics**
  - Total members count
  - Group total savings
  - Outstanding loans amount
  - Pending applications count

- **Loan Approvals**
  - List pending applications
  - Member information
  - Loan details
  - Approve/reject buttons
  - Eligibility verification

- **Member Management**
  - All members list
  - Savings balances
  - Account status
  - Join dates

- **Interest Distribution**
  - Month/year selection
  - Automatic calculation
  - Distribution to all members
  - Audit trail

---

## Interest Distribution System

### How It Works

1. **Loan Origination**
   - Member applies for loan
   - Admin approves
   - Loan status changed to "active"

2. **Interest Accrual**
   - 2% monthly interest calculated on outstanding loans
   - Interest pools across all active loans

3. **Distribution Calculation**
   - Total interest = Sum of (each loan × 2%)
   - Each member's ratio = Their savings / Total group savings
   - Member's share = Total interest × Their ratio

4. **Distribution Process**
   - Admin selects month/year
   - System calculates for all members
   - Interest added to savings automatically
   - Transaction logged

### Example

```
Group Savings:
- Member A: 100,000 (66.67%)
- Member B: 50,000 (33.33%)
- Total: 150,000

Outstanding Loans:
- Loan 1: 200,000
- Loan 2: 100,000
- Total: 300,000

Monthly Interest:
- Pool: 300,000 × 2% = 6,000

Distribution:
- Member A: 6,000 × 66.67% = 4,000
- Member B: 6,000 × 33.33% = 2,000
```

---

## Security Features

1. **Authentication**
   - Username/password login
   - Session management
   - Session timeout
   - Logout functionality

2. **Data Protection**
   - Prepared statements (SQL injection prevention)
   - Input sanitization
   - Output escaping
   - CSRF token support

3. **Password Security**
   - Bcrypt hashing
   - No plain text passwords
   - Password validation

4. **Access Control**
   - Role-based access (admin/member)
   - Session verification
   - Permission checking
   - Redirect unauthorized access

5. **Audit Trail**
   - Transaction logging
   - User action tracking
   - Change history
   - IP logging

---

## Responsive Design

### Mobile (max-width: 480px)
- Stack all columns vertically
- Full-width buttons
- Adjusted font sizes
- Touch-friendly elements
- Optimized tables

### Tablet (max-width: 768px)
- Two-column layout
- Adjusted card spacing
- Responsive forms
- Optimized images
- Reduced padding

### Desktop (1024px+)
- Full multi-column layout
- Optimal spacing
- All features visible
- Hover effects
- Full functionality

---

## Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | HTML5 | Semantic markup |
| | CSS3 | Styling & layout |
| | Bootstrap 5 | Responsive framework |
| | JavaScript ES6 | Client-side logic |
| | Font Awesome | Icons |
| **Backend** | PHP 7.4+ | Server logic |
| **Database** | MySQL 5.7+ | Data storage |
| **Server** | Apache/Nginx | Web server |

---

## Code Comments & Documentation

### HTML Comments
```html
<!-- Section Title - Purpose of this section -->
<!-- Button/Form - What this element does -->
```

### PHP Comments
```php
/**
 * Function description
 * @param type $param Parameter description
 * @return type Return value description
 */
```

### JavaScript Comments
```javascript
// Action - What this does
/**
 * Function description
 * @param {type} param - Parameter description
 */
```

### CSS Comments
```css
/* Section Name */
/* Component-specific styles */
```

---

## Configuration Files

### db_config.php
Sets up database connection and global constants.

```php
define('DB_HOST', 'localhost');
define('MANDATORY_SAVINGS', 5000);
define('LOAN_INTEREST_RATE', 0.02);
```

### functions.php
Contains all reusable PHP functions organized by category:
- Database functions
- Calculation functions
- Validation functions
- Utility functions
- Logging functions

### style.css
Organized CSS with sections:
- CSS Variables
- General Styles
- Component Styles
- Utility Classes
- Responsive Media Queries

### script.js
Organized JavaScript with sections:
- Form Validation
- API Utilities
- Table Utilities
- Notification System
- Local Storage Management

---

## File Naming Conventions

- **PHP Files**: lowercase with hyphens (savings.php, loan-payments.php)
- **CSS Files**: style.css (single stylesheet)
- **JavaScript**: script.js (single file)
- **Database**: database.sql
- **Images**: lowercase with hyphens (member-profile.png)
- **Documentation**: UPPERCASE.md (README.md, SETUP.md)

---

## Deployment Checklist

- [ ] Database created and schema imported
- [ ] config/db_config.php updated with correct credentials
- [ ] Admin password changed from default
- [ ] File permissions set (755 for directories, 644 for files)
- [ ] SSL certificate installed (if HTTPS)
- [ ] Error logging enabled
- [ ] Backups configured
- [ ] Test all features
- [ ] Security headers configured
- [ ] Monitoring set up

---

## Performance Metrics

- **Page Load**: < 2 seconds (with optimization)
- **Database Queries**: < 100ms average
- **Form Submission**: < 1 second
- **Report Generation**: < 5 seconds

---

## Support & Maintenance

### Regular Backups
```bash
# Daily automated backup recommended
mysqldump -u root -p savings_loans_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Log Monitoring
```bash
# Monitor error logs
tail -f logs/error.log
```

### Database Optimization
```sql
-- Monthly optimization
OPTIMIZE TABLE members;
OPTIMIZE TABLE loans;
OPTIMIZE TABLE savings;
OPTIMIZE TABLE transactions;
```

---

**Document Version**: 1.0  
**Last Updated**: March 1, 2026  
**Status**: Complete & Production Ready
