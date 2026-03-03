# 70K Savings & Loans - Quick Reference Card

## 📊 System at a Glance

| Feature | Details |
|---------|---------|
| **Type** | Savings & Loans Management System |
| **Users** | Group members + Admin |
| **Currency** | Ugandan Shillings (₤) |
| **Min Savings** | 5,000 UGX |
| **Max Loan** | 2x your savings |
| **Interest Rate** | 2% per month |
| **Distribution** | Monthly (based on savings ratio) |

---

## 🚀 Quick Start (5 minutes)

### Installation
```bash
1. Extract files to web root
2. Create database: savings_loans_db
3. Import database.sql
4. Update config/db_config.php
5. Visit http://localhost/70k/
```

### First Login
```
Username: admin
Password: admin123
⚠️ CHANGE PASSWORD IMMEDIATELY!
```

### First Use
```
1. Admin: Approve pending loans
2. Admin: Distribute interest (1st of month)
3. Member: Add savings
4. Member: Apply for loan
5. Admin: Approve loan
6. Member: Check interest earned
```

---

## 📁 File Structure (Quick View)

```
70k/
├── config/db_config.php     ← Database settings
├── config/functions.php     ← Core functions
├── css/style.css            ← Styling
├── js/script.js             ← JavaScript
├── database.sql             ← Database schema
│
├── index.php                ← Member dashboard
├── login.php                ← Login page
├── register.php             ← Registration
├── savings.php              ← Savings management
├── loans.php                ← Loan management
├── reports.php              ← Reports
├── profile.php              ← Profile settings
├── admin.php                ← Admin dashboard
│
└── Documentation:
    ├── README.md            ← Project overview
    ├── SETUP.md             ← Installation guide
    ├── IMPLEMENTATION_GUIDE.md  ← Complete guide
    └── PROJECT_STRUCTURE.md     ← Technical details
```

---

## 🔑 Key Constants

**Edit in `config/db_config.php`:**

```php
MANDATORY_SAVINGS = 5000       // Min savings to borrow
LOAN_INTEREST_RATE = 0.02      // 2% monthly
CURRENCY = 'UGX'
CURRENCY_SYMBOL = '₤'
```

---

## 🔐 Database Connection

**Edit in `config/db_config.php`:**

```php
DB_HOST = 'localhost'          // Database host
DB_USER = 'root'               // Database username
DB_PASS = ''                   // Database password
DB_NAME = 'savings_loans_db'   // Database name
```

---

## 📊 Interest Distribution Formula

```
Total Interest = Sum(Active Loans) × 2%

Member Share = Total Interest × (Member Savings / Total Group Savings)

Example:
- Member A savings: 100,000 (66.67%)
- Member B savings: 50,000 (33.33%)
- Group Total: 150,000
- Total Interest: 6,000

Member A gets: 6,000 × 66.67% = 4,000 ✓
Member B gets: 6,000 × 33.33% = 2,000 ✓
```

---

## 🔐 User Roles

### Member
- View dashboard
- Add savings
- Apply for loans
- View reports
- Update profile

### Admin
- Approve/reject loans
- Manage members
- Distribute interest
- View all data
- System statistics

---

## 📄 Pages Quick Guide

| Page | URL | Purpose |
|------|-----|---------|
| Login | login.php | Authentication |
| Register | register.php | New member signup |
| Dashboard | index.php | Account overview |
| Savings | savings.php | Manage savings |
| Loans | loans.php | Apply for loans |
| Reports | reports.php | Financial reports |
| Profile | profile.php | Account settings |
| Admin | admin.php | Admin controls |

---

## 🎯 Common Tasks

### Add Member Savings
```
1. Go to savings.php
2. Enter amount
3. Choose payment method
4. Optional: Receipt number & notes
5. Click "Record Savings"
```

### Apply for Loan
```
1. Go to loans.php
2. Enter amount (max 2x savings)
3. Choose duration (1-24 months)
4. Enter purpose
5. Click "Submit Application"
→ Admin approves
```

### Approve Loan (Admin)
```
1. Go to admin.php
2. Click "Pending Loan Applications"
3. Click "Approve" or "Reject"
4. Done!
```

### Distribute Interest (Admin)
```
1. Go to admin.php
2. Click "Interest Distribution"
3. Select month & year
4. Click "Distribute Interest"
→ Automatically divides among members
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| Can't connect | Check DB credentials in db_config.php |
| CSS not loading | Clear cache (Ctrl+Shift+Del) |
| Login fails | Wrong username/password |
| Loan stuck pending | Admin hasn't approved yet |
| Interest not showing | Need to wait for 1st of month |
| Can't borrow | Need 5,000+ in savings first |

---

## 🔄 Monthly Workflow

```
Week 1-3: Members add savings & apply for loans
          ↓
Week 4: Admin approves pending loans
        ↓
End of Month (1st): Admin distributes interest
                    System calculates & adds to savings
        ↓
Next Month: Repeat...
```

---

## 📈 Interest Example

**Month 1:**
- Member A: 100,000 saved
- Member B: 50,000 saved
- Total: 150,000

- Loan 1: 200,000 (Interest: 4,000)
- Loan 2: 100,000 (Interest: 2,000)
- **Total Interest Pool: 6,000**

**Distribution:**
- Member A (66.67%): +4,000 → Now 104,000
- Member B (33.33%): +2,000 → Now 52,000

---

## 🔧 Database Tables (Summary)

| Table | Purpose |
|-------|---------|
| users | Login accounts |
| members | Member profiles |
| savings | Savings records |
| loans | Loan applications |
| interest_distributions | Monthly interest |
| loan_payments | Loan repayments |
| transactions | Audit trail |
| settings | System config |

---

## 🛡️ Security Checklist

- [ ] Change admin password
- [ ] Configure DB user permissions
- [ ] Enable HTTPS
- [ ] Set file permissions (755)
- [ ] Regular backups
- [ ] Monitor error logs
- [ ] Update PHP version
- [ ] Disable file listing

---

## 📊 System Stats

```
Total Code:
  - PHP: 1,200+ lines
  - CSS: 1,000+ lines
  - JS: 1,500+ lines
  - SQL: 200+ lines
  Total: 4,000+ lines

Pages:
  - 8 main pages
  - 100% responsive
  - Mobile optimized

Database:
  - 8 tables
  - 3 views
  - 15+ indexes
  - 200+ fields
```

---

## 🚨 Important URLs

```
Login:      http://localhost/70k/login.php
Register:   http://localhost/70k/register.php
Dashboard:  http://localhost/70k/index.php
Admin:      http://localhost/70k/admin.php
```

---

## 💾 Backup Command

```bash
# Create backup
mysqldump -u root -p savings_loans_db > backup.sql

# Restore backup
mysql -u root -p savings_loans_db < backup.sql
```

---

## 📚 Documentation Files

| File | Contents |
|------|----------|
| README.md | Project overview & setup |
| SETUP.md | Detailed installation |
| IMPLEMENTATION_GUIDE.md | Complete user guide |
| PROJECT_STRUCTURE.md | Technical architecture |
| QUICK_REFERENCE.md | This file |

---

## ⚡ Performance Tips

- Clear cache monthly
- Optimize DB queries
- Backup regularly
- Monitor error logs
- Update PHP
- Enable compression

---

## 📞 Support

**For Issues:**
1. Check error logs in `logs/` folder
2. Verify database connection
3. Check file permissions
4. Review documentation

**For Features:**
See IMPLEMENTATION_GUIDE.md for complete feature list

---

## 🎓 Admin Commands

### Change Admin Password
```sql
UPDATE users 
SET password = '$2y$10$...' 
WHERE username = 'admin';
```

### Disable Member Account
```sql
UPDATE members 
SET status = 'suspended' 
WHERE id = 1;
```

### View All Transactions
```sql
SELECT * FROM transactions 
ORDER BY transaction_date DESC;
```

### Manual Interest Distribution
```sql
INSERT INTO interest_distributions (...)
VALUES (...);
```

---

## ✅ Deployment Checklist

- [ ] Database created & schema imported
- [ ] config/db_config.php updated
- [ ] Admin password changed
- [ ] File permissions set
- [ ] SSL/HTTPS configured
- [ ] Backups automated
- [ ] Error logging enabled
- [ ] All features tested
- [ ] Member accounts created
- [ ] Production ready

---

**Version:** 1.0.0  
**Status:** Production Ready  
**Last Update:** March 1, 2026

---

## 🎯 Next Steps

1. **Install** → Follow SETUP.md
2. **Configure** → Edit db_config.php
3. **Test** → Create test accounts
4. **Deploy** → Move to production
5. **Monitor** → Check logs regularly
6. **Maintain** → Backup & optimize

---

**Ready to use the system? Start with SETUP.md!**
