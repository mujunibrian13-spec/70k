# Password Reset Guide - 70K Savings & Loans

## Overview

The system now supports two password reset methods:

1. **Member Self-Service**: Members can change their own password
2. **Admin Reset**: Admins can reset member passwords when needed

---

## For Members: Change Your Own Password

### How to Change Your Password

1. **Login** to your account
2. **Click your name** in the top right corner (dropdown menu)
3. **Click "Change Password"**
4. **Enter your information:**
   - Current password (for verification)
   - New password (at least 6 characters)
   - Confirm new password (must match)
5. **Click "Change Password"**
6. **See success message** - your password has been changed
7. **Login again** with your new password next time

### Password Requirements

✓ Minimum **6 characters** long  
✓ Must be **different** from current password  
✓ Both new password fields must **match**  
✓ Passwords are **case-sensitive**  

### What If It Fails?

| Error | Solution |
|-------|----------|
| "Current password is incorrect" | Make sure you typed your current password correctly |
| "New passwords do not match" | Type the new password the same way in both fields |
| "Password must be at least 6 characters" | Use longer password (at least 6 characters) |
| "Password must be different" | Choose a password different from your current one |

### Security Tips

- ✓ Choose a **strong password** (mix of letters, numbers, symbols if possible)
- ✓ **Don't share** your password with anyone
- ✓ **Change regularly** for better security
- ✓ Use **different passwords** for different accounts
- ✓ **Don't write down** your password or share it via email

### Forgot Your Current Password?

If you forgot your current password:
1. **Contact your administrator**
2. They can reset your password for you
3. You'll get a temporary password
4. Change it to something you remember (using change password feature)

---

## For Administrators: Reset Member Passwords

### When to Reset a Member Password

✓ Member forgot their password  
✓ Member cannot access their account  
✓ Account was compromised  
✓ Member requested password reset  
✓ First-time setup or password issue  

### How to Reset a Member Password

1. **Login** as admin
2. **Click "Reset Password"** in the navbar
3. **Select the member** from dropdown
   - Member details will display (email, phone, status)
4. **Enter new password**
   - Minimum 6 characters
   - Can be temporary password
5. **Confirm password** (must match)
6. **Click "Reset Password"**
7. **Confirm** in the dialog that appears
8. **See success message** - password has been reset
9. **Communicate** the new password to the member **securely**

### Best Practices for Admin Reset

✓ Use a **temporary password** so member changes it after login  
✓ **Verify** you have correct member selected  
✓ **Communicate** new password **securely** (not via email)  
✓ Ask member to **change password** after first login  
✓ **Document** password resets in your records  
✓ Consider using a **random password generator** for security  

### Example Temporary Passwords

Good temporary passwords (member changes after login):
- `Welcome123`
- `Temp2026!`
- `Reset2026#`
- `Access2026@`

Then member changes to their own password.

### Accessing the Password Reset Page

**Two ways to access:**

1. **From navbar**: Click "Reset Password" in admin navigation
2. **From dashboard**: Use the menu (if added as shortcut)

### Password Requirements

✓ Minimum **6 characters**  
✓ Can be any combination of letters, numbers, symbols  
✓ **Case-sensitive**  

### Security Considerations

⚠️ **Passwords are hashed** - admin cannot see old passwords  
⚠️ **All resets are logged** for audit trail  
⚠️ **Communicate securely** - never send passwords via email  
⚠️ **Inform member** to change password after reset  
⚠️ **Don't reset unnecessary** - only when member requests  

---

## Functions Available to Developers

### For Members (Self-Service)
```php
resetMemberPassword($conn, $user_id, $current_password, $new_password)
```

**Parameters:**
- `$conn` - Database connection
- `$user_id` - User ID (member's user ID)
- `$current_password` - Current password (for verification)
- `$new_password` - New password to set

**Returns:** Array with 'success' and 'message' keys

**Validates:**
- Current password matches existing password
- New password is at least 6 characters
- New password is different from current password
- Passwords are not empty

**Example:**
```php
$result = resetMemberPassword($conn, $_SESSION['user_id'], 
                              'current_pass', 'new_pass');
if ($result['success']) {
    echo $result['message'];
} else {
    echo $result['message'];
}
```

### For Admins (No Current Password Needed)
```php
adminResetMemberPassword($conn, $user_id, $new_password)
```

**Parameters:**
- `$conn` - Database connection
- `$user_id` - User ID (member's user ID)
- `$new_password` - New password to set

**Returns:** Array with 'success', 'message', and 'username' keys

**Validates:**
- User exists
- User is a member (not admin)
- Password is at least 6 characters
- Password is not empty

**Example:**
```php
$result = adminResetMemberPassword($conn, $member_user_id, 'TempPass123');
if ($result['success']) {
    echo "Password reset for " . $result['username'];
} else {
    echo $result['message'];
}
```

### Helper Function: Get Member ID from User ID
```php
getMemberIdByUserId($conn, $user_id)
```

**Returns:** Member ID or null if not found

---

## User Experience Flow

### Member Password Change

```
Member logs in
    ↓
Clicks name dropdown
    ↓
Selects "Change Password"
    ↓
Enters current password + new password
    ↓
Clicks "Change Password"
    ↓
Password validated
    ↓
Success message shows
    ↓
Member logs out (optional)
    ↓
Member logs back in with new password
```

### Admin Password Reset

```
Admin logs in
    ↓
Clicks "Reset Password" in navbar
    ↓
Selects member from dropdown
    ↓
Member details display
    ↓
Enters new password twice
    ↓
Clicks "Reset Password"
    ↓
Confirmation dialog appears
    ↓
Admin confirms
    ↓
Password updated
    ↓
Success message shows username
    ↓
Admin communicates password to member
```

---

## Security Features

✅ **Password Hashing**
- Passwords stored as secure hashes
- Cannot be reversed
- Highly secure

✅ **Audit Trail**
- All password resets logged
- Timestamp recorded
- Admin/member recorded
- Accessible for security review

✅ **Validation**
- Minimum 6 characters required
- Current password verification (member reset)
- Same password prevention
- Empty field validation

✅ **Session Security**
- Member needs to be logged in to change password
- Admin needs admin privileges to reset
- Session checks on every access

✅ **Database Security**
- Prepared statements prevent SQL injection
- Input sanitization
- Role-based access control

---

## Troubleshooting

### Member Cannot Change Password

**Problem**: Password change fails  
**Solution**: 
1. Verify current password is correct
2. Ensure new password is 6+ characters
3. Ensure passwords match
4. Contact admin if continues failing

### Admin Cannot Reset Password

**Problem**: "User not found" error  
**Solution**:
1. Verify member is selected
2. Ensure member is not deleted
3. Check member account status
4. Refresh page and try again

### Forgotten Password

**Member scenario**: Can't login
**Solution**:
1. Contact administrator
2. Admin resets password
3. Member gets temporary password
4. Member logs in and changes password

### Security Concern

**If password compromised**: Reset immediately
**If account hacked**: Contact admin right away
**If suspicious activity**: Report to admin

---

## Database Changes

### Table: `users`

**Columns used:**
- `id` - User ID
- `username` - Username for display
- `password` - Hashed password (updated on reset)
- `role` - User role (checked for admin vs member)
- `updated_at` - Last update timestamp (set on reset)

**Sample query to find password reset history:**
```sql
-- Find all users with recent updates (password resets)
SELECT id, username, full_name, updated_at 
FROM users 
WHERE role = 'member' 
ORDER BY updated_at DESC 
LIMIT 10;
```

### Table: `transactions`

**Password reset logged as:**
- `transaction_type` = 'password_reset'
- `amount` = 0
- `description` = 'Password reset by member' or 'Password reset by administrator'
- `transaction_date` = timestamp of reset

**Query to find password resets:**
```sql
SELECT member_id, transaction_date, description 
FROM transactions 
WHERE transaction_type = 'password_reset' 
ORDER BY transaction_date DESC;
```

---

## Configuration

### Minimum Password Length

**Current setting:** 6 characters

**To change:**
Edit `config/functions.php`, function `resetMemberPassword()`:
```php
if (strlen($new_password) < 6) {  // Change 6 to desired length
```

---

## Navigation Links

### Member Dropdown
From any member page, click name dropdown → "Change Password"

### Admin Navigation
From admin dashboard, click "Reset Password" in navbar

### Direct URLs
- Member password change: `/change_password.php`
- Admin reset password: `/admin_reset_member_password.php`

---

## Testing Checklist

### Member Self-Service Reset
- [ ] Member can access change password page
- [ ] Current password validation works
- [ ] New password requirements enforced
- [ ] Password confirmation validation works
- [ ] Success message displays
- [ ] Member can login with new password
- [ ] Old password no longer works

### Admin Reset
- [ ] Admin can access reset page
- [ ] Member dropdown shows all members
- [ ] Member details display correctly
- [ ] Password validation works
- [ ] Success message shows
- [ ] Password actually changes (test login)
- [ ] Audit log records change

### Error Cases
- [ ] Wrong current password rejected
- [ ] Non-matching new passwords rejected
- [ ] Too short password rejected
- [ ] Empty fields rejected
- [ ] Same password rejected (member)
- [ ] Non-member selected rejected (admin)

---

## Summary

**Two Password Reset Methods:**

| Feature | Member Self-Service | Admin Reset |
|---------|-------------------|-------------|
| Who can use | Only members | Only admins |
| Current password needed | Yes | No |
| Use case | Member wants new password | Member forgot password |
| Requirements | 6+ chars, different from current | 6+ chars |
| Logged | Yes | Yes |
| Access | User dropdown → "Change Password" | Navbar → "Reset Password" |

---

**System**: 70K Savings & Loans Management System  
**Feature**: Password Reset (Member & Admin)  
**Version**: 1.0  
**Date**: March 2026  
**Status**: Complete
