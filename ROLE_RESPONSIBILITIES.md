# User Roles and Responsibilities

## Overview
The 70K Savings & Loans system has two main roles with different responsibilities:

---

## 🔐 Administrator Role

**Login Credentials:**
- Username: `admin`
- Password: `admin123`

**Dashboard:** Admin Dashboard (`admin.php`)

### Responsibilities:

1. **Loan Management**
   - View pending loan applications
   - Approve or reject loan requests from members
   - Track loan status (pending, approved, active, completed, rejected)

2. **Savings Management** ✨ **NEW**
   - Add savings for members
   - Update member savings contributions
   - Track one saving per week per member
   - View all members and their savings totals

3. **Interest Distribution**
   - Distribute monthly interest to all members
   - Calculate interest based on savings ratio
   - Generate interest reports

4. **Member Management**
   - View all registered members
   - Monitor member status (active, inactive, suspended)
   - View member financial summaries

5. **Financial Reports**
   - View group savings totals
   - Monitor outstanding loans
   - Track system statistics

---

## 👤 Member Role

**Login:** Via registration (NIN + email + password)

**Dashboard:** Member Dashboard (`index.php`)

### Responsibilities:

1. **Savings Management** 📝 **RESTRICTED**
   - ❌ **Cannot add their own savings**
   - ✅ Can view their current savings balance
   - ✅ Can view their savings history
   - ✅ Can view savings statistics
   - **Savings are added by administrator only**

2. **Loan Management**
   - Apply for loans (with maximum limit based on savings)
   - View loan history
   - Track loan status
   - View loan due dates

3. **Financial Reports**
   - View personal transactions
   - View interest earned (monthly)
   - View savings ratio in the group
   - Generate personal financial reports

4. **Profile Management**
   - Update personal profile
   - View personal information
   - Change password

---

## 📊 Workflow: How Savings are Added

### Old Workflow (Before)
1. Member logs in
2. Member goes to Savings page
3. Member adds savings themselves
4. System records the savings

### New Workflow (After)
1. **Admin logs in**
2. Admin goes to **Add Savings** page (navigation menu)
3. Admin selects a member from dropdown
4. Admin enters savings amount, payment method, notes
5. System records the savings for that member
6. **Member can view the added savings in their dashboard**

---

## 🔗 Navigation

### Admin Navigation
- Dashboard
- **Add Savings** ← New link
- Logout

### Member Navigation
- Dashboard
- Loans
- Reports
- Profile
- Logout
- ~~Savings~~ (Removed - now admin responsibility)

---

## 📋 Access Control

| Feature | Admin | Member |
|---------|-------|--------|
| View Admin Dashboard | ✅ | ❌ |
| View Member Dashboard | ❌ | ✅ |
| Add Savings | ✅ | ❌ |
| View Own Savings | ✅ | ✅ |
| Approve Loans | ✅ | ❌ |
| Apply for Loans | ❌ | ✅ |
| Distribute Interest | ✅ | ❌ |
| View All Reports | ✅ | ✅ |
| Manage Members | ✅ | ❌ |
| Change Own Password | ✅ | ✅ |

---

## 🚀 Implementation Details

### Technical Changes:
1. **savings.php** - Restricted to admin only
   - Checks if user is admin using `isAdmin()`
   - Redirects non-admin members to dashboard
   - Shows dropdown to select member
   - One saving per week per member rule still applies

2. **index.php** - Member dashboard
   - Removed "Add Savings" button
   - Added info message: "Savings are managed by the administrator"
   - Removed savings link from navigation

3. **admin.php** - Admin dashboard
   - Added "Add Savings" link in navigation menu

### Code Security:
```php
// Check if user is admin - only admins can add savings
if (!isAdmin()) {
    redirect('index.php');
}
```

---

## 📝 Notes

- Members still see their savings in the dashboard
- Members can view their savings history and reports
- Only **one saving entry per member per week** (enforced)
- Admin can update/edit member savings for the current week
- All transactions are logged for audit trail

---

## ✅ Testing Checklist

- [ ] Admin can login with admin/admin123
- [ ] Admin sees "Add Savings" in navigation
- [ ] Admin can select a member from dropdown
- [ ] Admin can add savings for a member
- [ ] Admin can update this week's savings for a member
- [ ] Member can login with their credentials
- [ ] Member does NOT see "Add Savings" link or button
- [ ] Member can see their savings in dashboard
- [ ] Member can view savings history
- [ ] Member can view reports
- [ ] Member gets error if they try to access savings.php directly

---

**Version:** 1.0  
**Date:** March 2026  
**Status:** ✅ Active
